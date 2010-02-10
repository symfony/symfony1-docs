Uso avanzato di Doctrine
========================

*Di Jonathan H. Wage*

Scrivere un comportamento per Doctrine
--------------------------------------

In questa sezione verrà mostrato come è possibile scrivere un comportamento
utilizzando Doctrine 1.2. Verrà creato un esempio che consenta di mantenere
facilmente in cache un contatore di relazioni in modo tale che non sia
necessario tutte le volte fare la query per ottenere il conteggio.

La funzionalità è molto semplice. Si vuole gestire un contatore per tutte le
relazioni e per fare ciò il comportamento aggiungerà al modello una colonna
dove memorizzare il conteggio corrente.

### Lo schema

Questo è lo schema che si userà per iniziare. Successivamente verrà modificato
aggiungendo la definizione actAs per il comportamento che si sta per scrivere:

    [yml]
    # config/doctrine/schema.yml
    Thread:
      columns:
        title:
          type: string(255)
          notnull: true

    Post:
      columns:
        thread_id:
          type: integer
          notnull: true
        body:
          type: clob
          notnull: true
      relations:
        Thread:
          onDelete: CASCADE
          foreignAlias: Posts

Ora si può creare tutto il necessario per tale schema:

    $ php symfony doctrine:build --all

### Il template

In primo luogo è necessario scrivere una classe figlia Doctrine_Template che sarà
responsabile di aggiungere le colonne al modello che memorizzerà i conteggi.


Si può inserire questo codice in una delle cartelle `lib/` del progetto e symfony
sarà in grado di caricarlo automaticamente

    [php]
    // lib/count_cache/CountCache.class.php
    class CountCache extends Doctrine_Template
    {
      public function setTableDefinition()
      {
      }

      public function setUp()
      {
      }
    }

Modificare il modello `Post` aggiungendo ActAs, con il comportamento `CountCache`:

    [yml]
    # config/doctrine/schema.yml
    Post:
      actAs:
        CountCache: ~
      # ...

Ora che il modello Post utilizza il comportamento CountCache, cerchiamo di
capire che cosa succede con il suo utilizzo.

Quando le informazioni di mapping per un modello sono istanziate, eventuali
comportamenti collegati ottengono l'invocazione dei metodi `setTableDefinition()`
e `setUp()`. Proprio come si ha nella classe `BasePost` in `lib/model/doctrine/base/BasePost.class.php`.
Questo permette di aggiungere cose a qualsiasi modello in uno stile plug n'play.
Queste "cose" possono essere colonne, relazioni, ascoltatori di eventi, ecc.

Ora che si è compreso un po' di più su quello che sta succedendo, bisogna fare
in modo che il comportamento `CountCache` faccia qualcosa:

    [php]
    class CountCache extends Doctrine_Template
    {
      protected $_options = array(
        'relations' => array()
      );

      public function setTableDefinition()
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          // costruisce il nome della colonna, se non fornito
          if (!isset($options['columnName']))
          {
            $this->_options['relations'][$relation]['columnName'] = 'num_'.Doctrine_Inflector::tableize($relation);
          }

          // aggiunge la colonna al modello relativo
          $columnName = $this->_options['relations'][$relation]['columnName'];
          $relatedTable = $this->_table->getRelation($relation)->getTable();
          $this->_options['relations'][$relation]['className'] = $relatedTable->getOption('name');
          $relatedTable->setColumn($columnName, 'integer', null, array('default' => 0));
        }
      }
    }

Il codice qui sopra aggiunge le colonne per mantenere il conteggio sul modello
collegato. Nel nostro caso si sta aggiungendo il comportamento al modello `Post`
per la relazione `Thread`. Si vuole memorizzare il numero di post che ha ogni
data istanza di `Thread`, in una colonna di nome `num_posts`. Ora è possibile
modificare lo schema YAML per definire le opzioni extra del comportamento:

    [yml]
    # ...

    Post:
      actAs:
        CountCache:
          relations:
            Thread:
              columnName: num_posts
              foreignAlias: Posts
      # ...

Ora il modello `Thread` ha una colonna `num_posts` che verrà tenuta aggiornata
con il numero di post che ha ogni thread.

### L'ascoltatore di eventi

Il passo successivo per costruire il comportamento, è quello di scrivere un
ascoltatore per registrare gli eventi, che sarà incaricato di tenere il
conteggio aggiornato quando si inserisce un nuovo record, si cancella un record
o si cancellano record con dei batch DQL:

    [php]
    class CountCache extends Doctrine_Template
    {
      // ...

      public function setTableDefinition()
      {
        // ...

        $this->addListener(new CountCacheListener($this->_options));
      }
    }

Prima di andare avanti bisogna definire la classe `CountCacheListener` che
estende `Doctrine_Record_Listener`. Essa accetta un array di opzioni che sono
semplicemente inoltrate all'ascoltatore dal template:

    [php]
    // lib/model/count_cache/CountCacheListener.class.php

    class CountCacheListener extends Doctrine_Record_Listener
    {
      protected $_options;

      public function __construct(array $options)
      {
        $this->_options = $options;
      }
    }

Ora è necessario utilizzare i seguenti eventi al fine di mantenere il conteggio
aggiornato:

 * **postInsert()**: Incrementa il conteggio quando viene inserito un nuovo oggetto;

 * **postDelete()**: Decrementa il conteggio quando viene cancellato un oggetto;

 * **postDqlUpdate()**:  Decrementa il conteggio quando i record sono cancellati per mezzo di
   una delete DQL.

In primo luogo definire il metodo `postInsert()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postInsert(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' + 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

Il codice sopra incrementerà di uno i conteggi per tutte le relazioni configurate
mediante una query DQL UPDATE quando un nuovo oggetto come il seguente è inserito:

    [php]
    $post = new Post();
    $post->thread_id = 1;
    $post->body = 'corpo del messaggio';
    $post->save();

Il `Thread` con un `id` di `1` otterrà che la colonna `num_posts` venga
incrementata di `1`.

Ora che i contatori sono incrementati all'inserimento di nuovi oggetti, è
necessario gestire il caso in cui gli oggetti vengono cancellati e decrementare
i contatori. Verrà implementato con il metodo `postDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postDelete(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' - 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

Il metodo `postDelete()` di cui sopra, è quasi identico a `postInsert()`,
l'unica differenza è che viene decrementata la colonna `num_posts` di `1`
invece di incrementarla. Gestisce il seguente codice, nel caso si volesse
cancellare il record `$post` che è stato salvato in precedenza:

    [php]
    $post->delete();

L'ultimo pezzo del puzzle, è quello di gestire il caso in cui i record vengono
cancellati usando un update DQL. Si può risolverlo utilizzando il metodo
`preDqlDelete()`:

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function preDqlDelete(Doctrine_Event $event)
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $q = clone $event->getQuery();
          $q->select($relation['foreign']);
          $ids = $q->execute(array(), Doctrine::HYDRATE_NONE);

          foreach ($ids as $id)
          {
            $id = $id[0];

            $table
              ->createQuery()
              ->update()
              ->set($options['columnName'], $options['columnName'].' - 1')
              ->where($relation['local'].' = ?', $id)
              ->execute();
          }
        }
      }
    }

Il codice sopra clona la query `DQL DELETE` e la trasforma in una `SELECT` che
permette di recuperare gli `ID` che verranno cancellati, in modo che sia
possibile aggiornare i contatori dei record che sono stati cancellati.

Ora si tiene conto del seguente scenario, decrementando i contatori:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('id = ?', 1)
      ->execute();

E anche se si dovessero cancellare record multipli, i contatori sarebbero
decrementati correttamente:

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

>**NOTE**
>Perché il metodo `preDqlDelete()` sia invocato, è necessario abilitare
>un attributo. Le callback DQL per impostazione predefinita sono a off a causa
>del loro (piccolo) costo extra. Quindi per utilizzarle è necessario abilitarle.
>
>     [php]
>     $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

Questo è tutto! Il comportamento è terminato. Non rimane che testarlo un po'!

### Test

Ora che il codice è stato implementato, bisogna caricare le fixture per i test
con i dati campione:

    [yml]
    # data/fixtures/data.yml

    Thread:
      thread1:
        title: Test Thread
        Posts:
          post1:
            body: This is the body of my test thread
          post2:
            body: This is really cool
          post3:
            body: Ya it is pretty cool

Si può ricostruire di nuovo tutto e caricare i dati con le fixture:

    $ php symfony doctrine:build --all --and-load

Ora è stato creato tutto e i dati con le fixture sono stati caricati; quindi
si può eseguire un test per vedere se i contatori vengono tenuti aggiornati:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '3'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '1'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is the body of my test thread'
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

Si vedrà che il modello `Thread` ha una colonna il cui valore è tre. Se
si vuole cancellare uno dei post con il seguente codice, verrà decrementato
il valore in automatico:

    [php]
    $post = Doctrine_Core::getTable('Post')->find(1);
    $post->delete();

Si può vedere che il record è stato cancellato e il contatore aggiornato:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '2'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

Funziona anche se vengono cancellati in batch i due record rimanenti, con una
query DQL delete:

    [php]
    Doctrine_Core::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

Ora sono stati cancellati tutti i post presenti e `num_posts` dovrebbe essere zero:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '0'
    doctrine -   Posts: {  }

Questo è tutto! La speranza è che questo capitolo possa aver insegnato qualcosa
sui comportamenti e che il comportamento stesso possa tornare utile! 

Utilizzo della cache nei risultati di Doctrine
----------------------------------------------

Nelle applicazioni web fortemente trafficate, è una necessità comune mettere in
cache le informazioni per risparmiare risorse nella CPU. Con Doctrine 1.2 sono
stati realizzati molti miglioramenti alla cache, che forniscono un maggior
controllo sulla cancellazione delle voci della cache, dai gestori della cache.
In precedenza non era possibile specificare la chiave di cache utilizzata per
memorizzare la cache in ingresso e quindi non era possibile identificare tale
voce al fine di cancellarla.

In questa sezione verrà mostrato un semplice esempio di come si possa utilizzare
il risultato di un set di cache, per mettere in cache tutte le query relative
all'utente, nonché utilizzare gli eventi per essere sicuri che siano correttamente
cancellati quando alcuni dati vengono cambiati.

### Lo schema

Per questo esempio, verrà usato il seguente schema:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username:
          type: string(255)
          notnull: true
          unique: true
        password:
          type: string(255)
          notnull: true

Ora verrà ricreato tutto dallo schema, con il seguente comando:

    $ php symfony doctrine:build --all

Una volta fatto questo, si dovrebbe essere generata la seguente classe `User`:

    [php]
    // lib/model/doctrine/User.class.php
    /**
     * User
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    class User extends BaseUser
    {
    }

Notare che più avanti con l'articolo, sarà necessario aggiungere del codice a
questa classe.

### Configurazione dei risultati della cache

Per poter utilizzare i risultati della cache, è necessario configurare un gestore
di cache per le query da usare. Questo può essere fatto impostando l'attributo
`ATTR_RESULT_CACHE`. Verrà usato il driver di cache APC in quanto è la scelta
migliore per un sito in produzione. Se non si ha a disposizione APC, a fini di
prova si può usare il driver `Doctrine_Cache_Db` o `Doctrine_Cache_Array`.

È possibile impostare questo attributo nella classe `ProjectConfiguration`.
Definire un metodo `configureDoctrine()`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, new Doctrine_Cache_Apc());
      }
    }

Ora che il gestore dei risultati della cache è configurato, si può iniziare a utilizzare
realmente questo driver, per mettere in cache i risultati delle query. 

### Esempio di query

Immaginare di avere nell'applicazione una certa quantità di query relative all'utente
e di volere cancellarle tutte le volte che alcuni dati utente sono cambiati.

Ecco una semplice query che si può utilizzare per ordinare alfabeticamente un elenco di utenti:

    [php]
    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->orderBy('u.username ASC');

Ora si è in grado di attivare il caching per questa query utilizzando il metodo `useResultCache()`:

    [php]
    $q->useResultCache(true, 3600, 'users_index');

>**NOTE**
>Notare il terzo parametro. Questa è la chiave che sarà usata per memorizzare la cache
>entrante per i risultati del gestore della cache. Questo permette di identificare facilmente
>la query e cancellarla dal gestore della cache.

Ora quando si fa la query, verrà eseguita nel database per ottenere i risultati
e verranno memorizzati nel gestore della cache sotto la chiave `users_index`.
Ogni richiesta successiva riceverà le informazioni dal gestore della cache,
invece che chiederle al database:

    [php]
    $users = $q->execute();

>**NOTE**
>Non solo il processo viene salvato nel database del server, ma scavalca anche
>l'intero processo di idratazione, poiché Doctrine salva i dati idratati. Ciò significa
>che si risparmieranno alcune elaborazioni sul server web.

Ora se si osserva il gestore della cache, si può vedere che c'è una voce denominata
`users_index`:

    [php]
    if ($cacheDriver->contains('users_index'))
    {
      echo 'la cache esiste';
    }
    else
    {
      echo 'la cache non esiste';
    }

### Cancellazione della cache

Ora che la query viene memorizzata nella cache, bisogna imparare come si può
cancellarla. È possibile cancellarla manualmente usando l'API del gestore della
cache o utilizzando alcuni eventi per cancellare automaticamente le voci della
cache quando un utente è inserito o modificato.

#### L'API del gestore della cache

Per il momento ci si limita a mostrare le API grezze del gestore della cache,
prima di implementarle in un evento.

>**TIP**
>Per poter accedere all'istanza con i risultati del gestore della cache, si può utilizzare 
>l'istanza della classe `Doctrine_Manager`.
>
>     [php]
>     $cacheDriver = $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
>
>Se non si ha già accesso alla variabile `$manager` si può
>recuperare l'istanza con il seguente codice.
>
>     [php]
>     $manager = Doctrine_Manager::getInstance();

Ora si può iniziare ad utilizzare l'API per cancellare le entrate nella cache:

    [php]
    $cacheDriver->delete('users_index');

Probabilmente si avrà più di un utente relativo alla query messa in cache e
la chiave prefissata con `users_`, quindi il metodo `delete()` in questo caso
non funziona correttamente. Allora si può usare il metodo `deleteByPrefix()`
per essere sicuri di cancellare la cache di tutte le query relative all'utente:

    [php]
    $cacheDriver->deleteByPrefix('users_');

Ci sono anche un altro paio di comodi metodi che si possono utilizzare per
cancellare le voci della cache se `deleteByPrefix()` non è sufficiente:

 * `deleteBySuffix($suffix)`: Cancella le voci della cache che hanno il suffisso passato;

 * `deleteByRegularExpression($regex)`:  Cancella le voci della cache che combaciano
   con l'espressione regolare passata;

 * `deleteAll()`: Cancella tutte le voci della cache.

### Cancellazione tramite eventi

Il modo ideale per svuotare la cache, sarebbe quello di cancellarla
automaticamente ogni volta che alcuni dati utente vengono modificati. Si può
fare questo grazie all'implementazione di un evento `postSave()` nella
definizione della classe del modello `User`.

Ci si ricorda della classe `User` di cui si è parlato in precedenza? Ora bisogna
aggiungere del codice ad essa, quindi la si apre nell'editor e aggiungendo il
seguente metodo `postSave()`:

    [php]
    // lib/model/doctrine/User.class.php

    class User extends BaseUser
    {
      // ...

      public function postSave($event)
      {
        $cacheDriver = $this->getTable()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $cacheDriver->deleteByPrefix('users_');
      }
    }

Ora se si vuole aggiornare un utente o inserirne uno nuovo si dovrebbe cancellare
la cache per tutte le query relative all'utente:

    [php]
    $user = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();

La prossima volta che le query vengono invocate, vedranno che la cache non esiste,
andranno a recuperare i dati dal database e li metteranno nuovamente in cache per
le richieste successive.

Anche se questo esempio è molto semplice, dovrebbe dimostrare bene come si può
usare queste funzionalità per implementare una cache tarata finemente sulle
query di Doctrine.

Scrivere un idratatore per Doctrine
-----------------------------------

Una delle caratteristiche chiave di Doctrine è la capacità di trasformare un
oggetto `Doctrine_Query` in vari tipi di strutture di risultati. Questo è il
lavoro dell'idratatore di Doctrine, ma fino a Doctrine 1.2, gli idratatori erano
tutti cablati nel codice e non utilizzabili dagli sviluppatori per personalizzarli.
Ora che questo è cambiato è possibile scrivere un idratatore personalizzato e
creare qualunque struttura dati che si desidera, in base ai dati del database
che si vogliono ottenere, quando si esegue una istanza di `Doctrine_Query`.

In questo esempio verrà costruito un idratatore estremamente semplice e facile da
capire, nonché molto utile. Esso consente di selezionare due colonne e idratare
i dati in un array dove la prima colonna selezionata è la chiave e la seconda colonna
selezionata è il valore.

### Lo schema e le fixture

Prima di iniziare è necessario uno schema su cui fare le prove. Basta usare
un semplice modello `User`:

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username: string(255)
        is_active: string(255)

C'è anche bisogno di alcune fixture per i dati, che sono riportate di seguito:

    [yml]
    # data/fixtures/data.yml
    User:
      user1:
        username: jwage
        password: changeme
        is_active: 1
      user2:
        username: jonwage
        password: changeme
        is_active: 0

Ora verrà creato tutto il necessario con il comando seguente:

    $ php symfony doctrine:build --all --and-load

### Scrivere un idratatore

Per scrivere un idratatore tutto quello che bisogna fare è scrivere una nuova
classe che estende `Doctrine_Hydrator_Abstract` e implementa un metodo
`hydrateResultSet($stmt)`. Questo riceve l'istanza `PDOStatement` usata per
eseguire la query. Si può utilizzare questa dichiarazione per ottenere i
risultati grezzi della query di PDO e quindi trasformarli nella struttura voluta.

Creareo una nuova classe chiamata `KeyValuePairHydrator` e metterla nella cartella `lib/`
in modo che symfony possa caricarla automaticamente:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        return $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
      }
    }

	
Il codice di cui sopra, per come è ora, restituirebbe i dati esattamente come
vengono forniti da PDO. Questo non è esattamente quello che si vuole. Si vogliono
trasformare i dati in una struttura di coppie chiave => valore. Quindi bisogna
modificare il metodo `hydrateResultSet()` per fargli fare quello di cui si ha bisogno:

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        $results = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
        $array = array();
        foreach ($results as $result)
        {
          $array[$result[0]] = $result[1];
        }

        return $array;
      }
    }

Bene è stato facile! Il codice dell'idratatore è finito e fa esattamente quello che
si voleva, ora non resta che usarlo!

### Utilizzare un idratatore

Per utilizzare e testare l'idratatore, prima è necessario registrarlo su Doctrine
in modo che quando vengono eseguite delle query, Doctrine sia a conoscenza della
classe idratatore che è stata scritta.

Per farlo, bisogna registrarlo nell'istanza `Doctrine_Manager` di `ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->registerHydrator('key_value_pair', 'KeyValuePairHydrator');
      }
    }

Ora che l'idratatore è registrato, si è in grado di utilizzarlo con le
istranze di `Doctrine_Query`. Ecco un esempio:

    [php]
    $q = Doctrine_Core::getTable('User')
      ->createQuery('u')
      ->select('u.username, u.is_active');

    $results = $q->execute(array(), 'key_value_pair');
    print_r($results);

L'esecuzione della query di cui sopra, con le fixture dei dati definiti in precedenza
ottiene il seguente risultato: 

    Array
    (
        [jwage] => 1
        [jonwage] => 0
    )

Bene questo è tutto! Abbastanza semplice vero? Si spera che quanto detto possa
tornare utile e che come risultato la comunità crei dei nuovi e interessanti
idratatori!
