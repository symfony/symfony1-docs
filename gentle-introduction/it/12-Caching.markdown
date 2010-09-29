Capitolo 12 - Cache
===================

Uno dei metodi per velocizzare un'applicazione è quello di memorizzare porzioni di codice HTML o anche pagine intere, per soddisfare in modo rapido richieste future. 
Tale tecnica prende il nome di cache, e può essere utilizzata sia lato server che lato client.

Symfony offre un sistema di cache lato server molto flessibile. Permette di salvare l'intera risposta (response), il risultato di un'azione, un partial, un segmento di template in un file, attraverso una configurazione 
molto intuitiva basata su file YAML. Se i dati dell'applicativo subissero un cambiamento, è possibile eliminare facilmente parti della cache tramite linea di comando o per mezzo di un'azione designata a tale scopo. 
Symfony fornisce anche un semplice controllo della cache lato client tramite gli header HTTP 1.1. 
Questo capitolo affronterà queste tematiche, e proporrà alcuni suggerimenti per monitorare i miglioramenti che la cache può portare in un'applicazione.

Cache della risposta
--------------------

Il principio della cache HTML è piuttosto semplice: porzioni o tutto il codice HTML inviato all'utente dopo una richiesta (request) può essere riutilizzato per analoghe richieste successive. 
Tale codice HTML verrà memorizzato in una cartella particolare (in symfony nella cartella `cache/`), dove il front controller effettuerà un controllo prima di eseguire un'azione. 
Se venisse trovata una versione in cache, essa verrà inviata senza che l'azione sia eseguita, in modo tale da velocizzarne in modo considerevole il processo di costruzione della risposta da inviare all'utente. 
Se non venisse trovata alcuna versione, l'azione verrà eseguita e il risultato (la vista) verrà memorizzato nella cartella di cache per richieste future.

Symfony gestisce tre tipologie di cache HTML:

  * Cache di un'azione (con o senza layout)
  * Cache di un parziale, di un component oppure un component slot
  * Cache di un frammento

Le prima due tipologie sono gestite tramite file YAML. La terza, cache di un frammento, viene gestita tramite chiamate nei template di un helper specializzato.

### Impostazioni globali della cache

Per ogni applicazione di un progetto, il meccanismo di cache HTML può essere abilitato o disabilitato (impostazione predefinita), per ambiente, nell'impostazione `cache` del file `settings.yml`.
Il Listato 12-1 mostra come abilitarla.

Listato 12-1 - Attivazione della cache, in `frontend/config/settings.yml`

    dev:
      .settings:
        cache: true

### Cache di un'azione

Le azioni che mostrano informazioni statiche (dati non dipendenti dal database o dalla sessione) o azioni che leggono dati da un database ma non modificano i dati in esso contenuti (tipicamente richieste GET) sono spesso candidati ideali 
per essere memorizzati nella cache. La Figura 12-1 mostra quali elementi della pagina verranno messi in cache in questo caso: il risultato di un'azione (il template annesso) o il risultato insieme al layout.

Figura 12-1 - Cache di un'azione

![Cache di un'azione](http://www.symfony-project.org/images/book/1_4/F1201.png  "Cache di un'azione")

Ad esempio: si consideri un'azione `user/list` che restituisce la lista degli utenti di un sito. 
A meno che un utente venga modificato, aggiunto o rimosso (e questo argomento sarà discusso in seguito nella sezione "Rimuovere oggetti dalla cache"), la lista sarà sempre la stessa, 
per cui è una buona candidata per essere memorizzata in cache.

L'attivazione e le impostazioni della cache, azione per azione, sono definite nel file cache.yml, all'interno della cartella `config/` del modulo. Il Listato 12-2 ne mostra un esempio.

Listato 12-2 - Attivare la cache per un'azione, in frontend/modules/user/config/cache.yml

    list:
      enabled:     true
      with_layout: false   # valore predefinito
      lifetime:    86400   # valore predefinito

La configurazione appena mostrata definisce che la cache sarà attiva per l'azione `list`, e il layout non verrà incluso (comportamento predefinito).
Ciò significa che anche se una versione dell'azione venisse trovata in cache, il layout (con partial e component) verrà eseguito ugualmente. Se l'opzione `with_layout` venisse impostata a `true`,
anche il layout verrebbe messo in cache con l'azione e non sarebbe eseguito.

Per testare le impostazioni della cache, si deve richiamare l'azione dal browser con l'applicativo in modalità di ambiente di sviluppo:

    http://myapp.example.com/frontend_dev.php/user/list

Si puo'notare un bordo colorato attorno all'area dell'azione nella pagina. La prima volta tale area avrà un header blu: significa che la pagina non proviene dalla cache. 
Aggiornando la pagina si noterà che l'header è di colore giallo: significa che questa volta proviene dalla cache (con un significativo decremento del tempo di risposta). 
Più avanti in questo capitolo saranno approfonditi i metodi per testare e monitorare la cache.

>**NOTE**
>Gli slot sono parte dei template, quindi effettuare la cache di un'azione significa anche memorizzare il valore dello slot definito dal template dell'azione. Di conseguenza la cache funziona nativamente per gli slot.

Il sistema di cache funziona anche per le pagine con argomenti. Il modulo `user` potrebbe avere, ad esempio, un'azione `show` che si aspetta un `id` per poter mostrare i dettagli di un utente. 
Per ottenere questo comportamento occorre modificare il file `cache.yml` in modo tale da abilitare la cache anche per questa casistica, come mostrato nel Listato 12-3.

Per organizzare i vari file `cache.yml`, sarà sufficiente ragruppare le impostazioni comuni a tutte le azioni di un modulo sotto la chiave `all:`, anch'essa mostrata nel Listato 12-3.

Listato 12-3 - Esempio di `cache.yml, in frontend/modules/user/config/cache.yml`

    list:
      enabled:    true
    show:
      enabled:    true

    all:
      with_layout: false    # valore predefinito
      lifetime:    86400    # valore predefinito

In questo modo ogni chiamata all'azione `user/show` con un `id` diverso produrrà nuovi record nella cache. Per cui la cache generata dall'URL:

    http://myapp.example.com/user/show/id/12

sarà diversa da quella dell'URL:

    http://myapp.example.com/user/show/id/25

>**CAUTION**
>Azioni chiamate in POST o GET non vengono memorizzate in cache.

L'impostazione `with_layout` merita qualche parola in più. 
Essa determina effettivamente che tipo di dati debbano essere memorizzati nella cache. 
Per cache senza layout, viene memorizzato solo il risultato dell'esecuzione di un template e le variabili dell'azione. 
Per cache con layout, tutta la risposta viene memorizzata. Ciò significa che la cache con il layout è molto più veloce di quella senza.

Se funzionalmente è applicabile (ovvero se il layout non si basa sulla sessione) si dovrebbe scegliere sempre la cache con layout. 
Sfortunatamente, il layout contiene spesso elementi dinamici (ad esempio, il nome dell'utente connesso), per cui la cache senza layout è l'impostazione più comune. 
Comunque, i feed RSS, i pop-up, e le pagine che non dipendono dai cookie possono essere messe in cache con il rispettivo layout.

### Cache di un partial, component, o component slot

Il capitolo 7 è stato mostrato come riutilizzare frammenti di codice in diversi template, utilizzando l'helper `include_partial()`. 
Un partial è facile da mettere in cache quanto un'azione, e l'attivazione segue le stesse regole, come mostrato in Figura 12-2.

Figura 12-2 - Cache di un partial, component, o component slot

![Cache di un partial, component, o component slot](http://www.symfony-project.org/images/book/1_4/F1202.png "Cache di un partial, component, o component slot")

Ad esempio, il Listato 12-4 mostra come modificare il file `cache.yml` per abilitare la cache di un partial `_my_partial.php` del modulo `user`. 
Da notare che l'opzione `with_layout` non ha senso in questo caso.

Listato 12-4 - Cache di un partial, in `frontend/modules/user/config/cache.yml`

    _my_partial:
      enabled:    true
    list:
      enabled:    true
    ...

In questo modo tutti i template che utilizzino questo partial non eseguiranno effettivamente il codice PHP, bensì utilizzeranno la versione in cache.

    [php]
    <?php include_partial('user/my_partial') ?>

Come avviene per le azioni, anche la cache dei partial diventa importante quando il risultato di tale partial è dipendente da parametri. Il sistema di cache memorizzerà tante versioni del template quanti sono i parametri.

    [php]
    <?php include_partial('user/my_other_partial', array('foo' => 'bar')) ?>

>**TIP**
>La cache di un'azione è più potente di quella di un partial, in quanto quando un'azione viene messa in cache il template non viene eseguito; se il template contenesse chiamate ai partial, tali chiamate non verranno eseguite. 
>Perciò, la cache dei partial diventa utile quando non vengono messe in cache l'azione chiamante o i partial inclusi nel layout.

Un piccolo promemoria dal capitolo 7: un component è una leggera azione situata all'inizio del partial e un component slot è un component per il quale l'azione cambia a seconda delle azioni chiamanti.
Questi due tipi di inclusioni sono molto simili ai partial, e supportano la cache allo stesso modo. Ad esempio, se il layout globale includesse un component chiamato `day` 
con `include_component('general/day')` per mostrare la data odierna, per abilitarne la cache sarà sufficiente impostare il file `cache.yml` del modulo `general` nel seguente modo:

    _day:
      enabled: true

Nel momento in cui venisse effettuata la cache di un component o un partial, bisognerà decidere se memorizzare una singola versione per tutti i template chiamanti oppure una versione per ognuno di essi. 
Come impostazione predefinita, un component è memorizzato indipendentemente dal template invocante. 
Ma component contestuali, ad esempio quelli che visualizzano una barra laterale differente a ogni azione, dovrebbero essere memorizzati tante volte quanti sono i template che li invocano. 
Il sistema di cache gestisce questo caso, impostando il parametro `contextual` a `true`, nel modo seguente:

    _day:
      contextual: true
      enabled:    true

>**NOTE**
>I componenti globali (quelli situati nella cartella `templates/` dell'applicazione) possono essere messi in cache, dichiarando le impostazioni nel file `cache.yml` dell'applicazione.


### Cache di un frammento di template

La cache delle azioni si applica solo a un loro sottoinsieme. 
Per le altre, ovvero quelle che aggiornano dati o mostrano nel template informazioni dipendenti dalla sessione, c'è ancora spazio per miglioramenti nell'utilizzo del sistema di cache, ma in modo diverso.
Symfony mette a disposizione un terzo tipo di cache, dedicato ai frammenti dei template e abilitato direttamente al loro interno. In questa modalità, l'azione verrebbe sempre eseguita, 
 e il template verrebbe diviso in frammento ed essi messi successivamente in cache, come mostrato dalla Figura 12-3.

Figura 12-3 - Cache di un frammento

![Cache di un frammento di template](http://www.symfony-project.org/images/book/1_4/F1203.png  "Cache di un frammento di template")

Ad esempio, si potrebbe avere una lista di utenti che mostrano un link all'utente che ha effettuato l'accesso per ultimo, e tale informazione è dinamica.
L'helper `cache()` definisce le parti di una template che devono memorizzate in cache. Si veda il Listato 12-5 per dettagli sulla sintassi.

Listato 12-5 - Usare l'helper `cache()`, in `frontend/modules/user/templates/listSuccess.php`

    [php]
    <!-- Codice eseguito tutte le volte -->
    <?php echo link_to('last accessed user', 'user/show?id='.$last_accessed_user_id) ?>

    <!-- Codice in cache -->
    <?php if (!cache('users')): ?>
      <?php foreach ($users as $user): ?>
        <?php echo $user->getName() ?>
      <?php endforeach; ?>
      <?php cache_save() ?>
    <?php endif; ?>

Il funzionamento dell'helper:

  * Se venisse trovata in cache una versione del frammento '`users`', verrebbe sostituita al codice compreso tra le linee `<?php if (!cache($unique_fragment_name)): ?>` e `<?php endif; ?>`.
  * Altrimenti, il codice tra tali linee verrebbe processato e salvato in cache,  identificato con il nome `$unique_fragment_name`.

Il codice non incluso tra tali linee verrebbe sempre processato e mai salvato in cache.

>**CAUTION**
>L'azione (`list`nell'esempio) non deve avere la cache abilitata, altrimenti l'intero template verrebbe ignorato e la dichiarazione di cache del frammento ignorata.

L'aumento di velocità dovuto alla cache dei frammento non è significativo quanto quello dovuto alla cache delle azioni, dato che l'azione verrebbe sempre eseguita, 
il template parzialmente processato e il layout sempre usato per la presentazione.

È possibile dichiarare frammento addizionali nello stesso template, però occorre attribuire a ognuno di essi un nome univoco, in modo che il sistema di cache riesca a identificarli in seguito.

Come per le azioni e i component, anche i frammento in cache possono accettare un tempo di vita come secondo parametro, specificato in secondi, per l'helper `cache()`:

    [php]
    <?php if (!cache('users', 43200)): ?>

Se non ne venisse specificato alcun valore, verrà utilizzato il valore di default (86400 secondi, ovvero un giorno).

>**TIP**
>Una modalità alternativa per rendere un'azione memorizzabile in cache è inserire delle variabili che la rendano dinamica nell'annesso pattern di routing. 
>Ad esempio, se una pagina mostrasse il nome dell'utente connesso, essa non potrebbe essere messa in cache a meno che l'URL non ne contenga il nome utente. 
>Un altro esempio è per le applicazioni internazionalizzate: se si volesse abilitare la cache di una pagina contenente diverse traduzioni, il codice della lingua deve in qualche modo essere incluso nell'URL. 
>Questa scorciatoia moltiplicherà il numero delle pagine in cache, ma può essere di grande aiuto per velocizzare applicazioni pesantemente interattive.

### Configurazione dinamica della cache

Il file `cache.yml` rappresenta una modalità per definire le impostazioni della cache, ma ha l'inconveniente di essere fisso. 
Detto ciò, come accade spesso in symfony, è possibile utilizzare PHP al posto di YAML, e questo permette di configurare la cache dinamicamente.

Perché mai si vorrebbe poter cambiare la cache dinamicamente? Un buon esempio è rappresentato da una pagina il cui contenuto varia a seconda che un utente sia autenticato o meno, ma la sua URL rimane la stessa. 
Si immagini una pagina `article/show` con un sistema di votazione per gli articoli. Tale funzionalità di votazione sarebbe disabilitata per gli utenti non autenticati; per loro, il link della votazione porterebbe
alla pagina di login. Questa versione della pagina potrebbe essere messa in cache. D'altra parte, per gli utenti autenticati, cliccare sul link di votazione scatenerebbe una richiesta in POST e creerebbe un nuovo voto. 
In questo caso la cache deve essere disabilitata, in modo che symfony possa costruirla dinamicamente.


Il posto giusto per definire le impostazioni della cache dinamica è in un filtro eseguito prima di `sfCacheFilter`. 
Infatti, in symfony la cache non è altro che un filtro, proprio come la web debug toolbar e le funzionalità di sicurezza. 
Per abilitare la cache per la pagina `article/show` solo se l'utente non è autenticato, è sufficiente creare un `conditionalCacheFilter` nella cartella `lib/` dell'applicazione, come mostrato nel Listato 12-6.

Listato 12-6 - Configurare la cache in PHP, in `frontend/lib/conditionalCacheFilter.class.php`


    [php]
    class conditionalCacheFilter extends sfFilter
    {
      public function execute($filterChain)
      {
        $context = $this->getContext();
        if (!$context->getUser()->isAuthenticated())
        {
          foreach ($this->getParameter('pages') as $page)
          {
            $context->getViewCacheManager()->addCache($page['modulè], $page['action'], array('lifeTimè => 86400));
          }
        }

        // Eseguire il filtro successivo
        $filterChain->execute();
      }
    }

Occorre registrare questo filtro nel file `filters.yml` prima di `sfCacheFilter`, come mostrato nel Listato 12-7.

Listato 12-7 - Registrare un filtro personalizzato, in `frontend/config/filters.yml`

    ...
    security: ~

    conditionalCache:
      class: conditionalCacheFilter
      param:
        pages:
          - { module: article, action: show }

    cache: ~
    ...

Rimuovendo i file in cache (per caricare il nuovo filtro) la cache condizionale è pronta. Essa abiliterà la cache delle pagine definite nel parametro `pages` solo per gli utenti non autenticati.

Il metodo `addCache()` dell'oggetto `sfViewCacheManager` si aspetta il nome di un modulo, di un'azione e un array associativo con gli stessi parametri che verrebbero definiti in un file `cache.yml`. 
Ad esempio, se si volesse definire che l'azione `article/show` debba essere messa in cache con il layout e un tempo di 3600 secondi:

    [php]
    $context->getViewCacheManager()->addCache('articlè, 'show', array(
      'withLayout' => true,
      'lifeTimè   => 3600,
    ));

>**SIDEBAR**
>Sistema di memorizzazione della cache alternativo
>
>Come impostazione predefinita, symfony memorizza i dati in file sul disco rigido del web server. 
>è possibile voler immagazzinare i dai in memoria (ad esempio tramite `memcache`) oppure in un database 
>(specialmente se si volesse condividere la cache tra più server o velocizzarne la rimozione). Tale impostazione è modificabile facilmente in quanto è definita nel file `factories.yml`.
>
>
>Il sistema di memorizzazione di default è la classe `sfFileCache`:
>
>     view_cache:
>         class: sfFileCache
>         param:
>           automaticCleaningFactor: 0
>           cacheDir:                %SF_TEMPLATE_CACHE_DIR%
>
>è possibile sostituire il valore del parametro `class` con il sistema di memorizzazione personalizzato o con una delle classi alternative di symfony (inclusi `sfAPCCache`, `sfEAcceleratorCache`, `sfMemcacheCache`, e `sfSQLiteCache`). 
>I parametri definiti al di sotto del parametro `param` vengono passati al costruttore della classe di cache come array associativo. 
>Ogni metodo alternativo di memorizzazione deve implementare tutti i metodi definiti nella classe astratta `sfCache`. Per maggiori informazioni su questo argomento consultare il capitolo 19.

> Configurazione di un backend memcache che utilizza due server memcache:
>     view_cache:
>       class: sfMemcacheCache
>       param:
>         servers:
>           server_1:
>             host: 192.168.1.10
>           server_2:
>             host: 192.168.1.11

### Utilizzare la cache super veloce

Anche una pagina in cache coinvolge l'esecuzione di codice PHP. Per tali pagine, symfony carica la configurazione, costruisce la risposta e così via. 
Se si avesse la certezza che una pagina non subirà cambiamenti per un certo periodo di tempo, è possibile ignorare completamente symfony completamente mettendone il codice HTML risultante direttamente all'interno della cartella `web/`.
Questo funziona grazie alle impostazioni `mod_rewrite` di Apache, supposto che le regole di routing specifichino pattern senza suffisso o terminanti con `.html`.

ciò è realizzabile pagina per pagina, con una semplice chiamata a linea di comando:

    $ curl http://myapp.example.com/user/list.html > web/user/list.html

Dopo aver effettuato ciò, ogni volta che venisse richiesta l'azione `user/list`, Apache troverebbe la corrispondente pagina `list.html` e  symfony verrebbe ignorato completamente. 
L'altra faccia della medaglia nell'utilizzo della tecnica appena descritta è che non è più possibile controllare la cache della pagina tramite symfony (lifetime, delegazione automatica e così via), ma il guadagno in velocità è sarebbe veramente impressionante.

In alternativa, è possibile utilizzare il plugin di symfony `sfSuperCache`, che automatizza questo processo e supporta lifetime e pulizia della cache. 
Consultare il capitolo 17 per maggiori informazioni sui plugin.

>**SIDEBAR**
>Altre tattiche di velocizzazione
>
>In aggiunta alla cache HTML, symfony possiede altri due meccanismi di cache, che sono completamente automatici e trasparenti allo sviluppatore. 
>Nell'ambiente di produzione, la configurazione e le traduzioni dei template sono memorizzate nelle cartelle `myproject/cache/config/` e `myproject/cache/i18n/` senza alcun intervento.
>
>Gli acceleratori PHP (eAccelerator, APC, XCache e così via), chiamati anche moduli di cache opcode, incrementano le performance degli script PHP mettendoli in cache in uno stato 
>compilato, in modo che l'overhead dovuto all'analisi e alla compilazione venga quasi completamente eliminato. Questo è particolarmente efficiente per le classi ORM, 
>che contengono una grande quantità di codice. Questi acceleratori sono compatibili con symfony e possono facilmente triplicare la velocità di un'applicazione.
>Essi sono raccomandati in ambienti di produzione per qualsiasi applicazione symfony che generi alto traffico.
>
>Con un acceleratore PHP, è possibile immagazzinare manualmente dati persistenti in memoria, evitando così lo stesso processo a ogni richiesta, tramite la classe `sfProcessCache`. 
>E se si volesse memorizzare in cache il risultato di un'operazione molto impegnativa per la CPU, è consigliabile l'utilizzo dell'oggetto `sfFunctionCache`. 
>Consultare il capitolo 18 per maggiori informazioni su questi meccanismi.


Rimuovere elementi dalla cache
------------------------------

Se gli script o i dati dell'applicazione cambiassero, la cache conterrà informazioni scadute. 
Per evitare incoerenze e bug, è possibile eliminare elementi dalla cache in diversi modi, a seconda delle esigenze.

### Eliminare l'intera cache

Il task `cache:clear` della linea di comando di symfony elimina l'intera cache (HTML, configurazione e i18N). 
è possibile passare degli argomenti per eliminare solo alcune parti, come mostrato dal Listato 12-8. Deve essere invocato solo dalla root di un progetto symfony.

Listato 12-8 - Eliminare la cache

    // Eliminare l'intera cache
    $ php symfony cache:clear

    // Sintassi abbreviata
    $ php symfony cc

    // Eliminare solo la cache dell'applicazione frontend
    $ php symfony cache:clear --app=frontend

    // Eliminare solo la cache HTML dell'applicazione frontend
    $ php symfony cache:clear --app=frontend --type=template


    // Eliminare solo la configurazione in cache dell'applicazione frontend
    // I tipi possibili sono config, i18n, routing, e template.
    $ php symfony cache:clear --app=frontend --type=config --env=prod

### Eliminare parti specifiche della cache

Qualora un database venisse aggiornato, la cache delle azioni relative ai dati modificati deve essere cancellata. 
è possibile eliminare l'intera cache, ma ciò rappresenterebbe uno spreco per tutte le azioni non relative alle modifiche del modello. 
è questo il caso dove il metodo `remove()` dell'oggetto `sfViewCacheManager` viene in aiuto. 
Esso si aspetta come argomento una URI interna (lo stesso tipo di parametro che passeresti a `link_to()`), ed elimina la relativa azione dalla cache.


Ad esempio, si ipotizzi che l'azione `update` del modulo `user` modifichi le colonne dell'oggetto `User`. 
La versione in cache delle azioni `list` e `show` avrebbero bisogno di essere eliminate, altrimenti esse, con dati erronei, verrebbero visualizzate. 
Per gestire questo caso, è necessario il metodo `remove()`, come mostrato nel Listato 12-9.

Listato 12-9 - Eliminare la cache per una data azione, in `modules/user/actions/actions.class.php`

    [php]
    public function executeUpdate($request)
    {
      // Aggiornamento di un utente
      $user_id = $request->getParameter('id');
      $user = UserPeer::retrieveByPk($user_id);
      $this->forward404Unless($user);
      $user->setName($request->getParameter('namè));
      ...
      $user->save();

      // Eliminazione  della cache per le azioni relative a tale utente
      $cacheManager = $this->getContext()->getViewCacheManager();
      $cacheManager->remove('user/list');
      $cacheManager->remove('user/show?id='.$user_id);
      ...
    }

Eliminare partial, component e component slot è leggermente più complesso. 
Dato che è possibile passare qualsiasi tipo di parametro (inclusi oggetti), è quasi impossibile modificare la rispettiva versione in cache. 
Di seguito verrà illustrato il meccanismo sui partial, la spiegazione vale anche per gli altri componenti del template. 
Symfony identifica un partial in cache con un prefisso speciale (`sf_cache_partial`), il nome del modulo e il nome del partial, più un hash di tutti i parametri utilizzato per chiamarlo, come segue:

    [php]
    // Un partial chiamato da
    <?php include_partial('user/my_partial', array('user' => $user) ?>

    // È identificato nella cache come
    @sf_cache_partial?module=user&action=_my_partial
      ➥ &sf_cache_key=bf41dd9c84d59f3574a5da244626dcc8

Teoricamente è possibile rimuovere un partial in cache tramite il metodo `remove()` conoscendo il valore dei parametri hash usati per identificarlo, ma ciò è veramente poco praticabile. 
Fortunatamente, aggiungendo un parametro `sf_cache_key` alla chiamata dell'helper `include_partial()`, è possibile identificare il partial in cache con tale chiave. 
Il Listato 12-10 mostra come eliminare la cache di un singolo partial (ad esempio per eliminare i dati in cache relativi a uno `User` modificato) diventi una semplice operazione.

Listato 12-10 - Rimozione di un partial dalla cache

    [php]
    <?php include_partial('user/my_partial', array(
      'user'         => $user,
      'sf_cache_key' => $user->getId()
    ) ?>

    // Viene identificato in cache come
    @sf_cache_partial?module=user&action=_my_partial&sf_cache_key=12

    // Elimina _my_partial per uno specifico utente in cache con
    $cacheManager->remove('@sf_cache_partial?module=user&action=_my_partial
     ➥ &sf_cache_key='.$user->getId());

Per eliminare dalla cache frammento di template, viene utilizzato lo stesso metodo `remove()`. 
La chiave che identifica il frammento nella cache è lo stesso prefisso `sf_cache_partial`, il nome del modulo, quello dell'azione e il parametro `sf_cache_key`. 
Il Listato 12-11 ne mostra un esempio.

Listato 12-11 - Eliminare frammento dalla cache

    [php]
    <!-- Codice in cache -->
    <?php if (!cache('users')): ?>
      ... // Whatever
      <?php cache_save() ?>
    <?php endif; ?>

    // Viene identificato in cache come
    @sf_cache_partial?module=user&action=list&sf_cache_key=users

     // Eliminato con
    $cacheManager->remove('@sf_cache_partial?module=user&action=list
     ➥ &sf_cache_key=users');

>**SIDEBAR**
>L'eliminazione selettiva della cache potrebbe risultare un'operazione delirante per lo sviluppatore 
>
>La parte più complessa dell'operazione di pulizia della cache è capire quali azioni sono influenzate da un aggiornamento dei dati.
>
>Ad esempio, si immagini che l'applicazione corrente abbia un modulo `publication` dove le pubblicazioni vengano elencate (azione `list`) e descritte singolarmente (azione `show`), 
>insieme a qualche dettaglio sull'autore (istanza della classe `User`). Modificare un utente coinvolgerà tutte le descrizioni e anche l'elenco. 
>Ciò significa che bisogna aggiungere all'azione `update` del modulo `user` qualcosa come:
>
>
>     [php]
>     $c = new Criteria();
>     $c->add(PublicationPeer::AUTHOR_ID, $request->getParameter('id'));
>     $publications = PublicationPeer::doSelect($c);
>
>     $cacheManager = sfContext::getInstance()->getViewCacheManager();
>     foreach ($publications as $publication)
>     {
>       $cacheManager->remove('publication/show?id='.$publication->getId());
>     }
>     $cacheManager->remove('publication/list');
>
>Iniziando a utilizzare la cache HTML, si deve avere una visione chiara delle dipendenze tra modello e azioni, in modo che non accadano errori dovuti a incomprensioni.
>Bisogna anche ricordare che tutte le azioni che modificano il modello dovrebbero contenere una manciata di chiamate al metodo `remove()`, se la cache HTML è usata da qualche parte nell'applicazione.
>
>Per non impazzire con analisi troppo complicate, è sempre possibile svuotare l'intera cache ogni volta che il modello viene aggiornato.

### Eliminazione di diverse parti di cache

Il metodo `remove()` accetta parametri con caratteri jolly. Permette di rimuovere diverse parti di cache con una sola chiamata. Per esempio:

    [php]
    $cacheManager->remove('user/show?id=*');    // Rimuove tutti i record relativi

Un altro buon esempio è la gestione di applicazioni con diverse lingue, dove i codici delle lingue appaiono in tutte le URL. L'URL della pagina di un profilo utente dovrebbe essere così:

    http://www.myapp.com/en/user/show/id/12

Per rimuovere un profilo utente in cache con id 12 in tutte le lingue, è sufficiente:

    [php]
    $cache->remove('user/show?sf_culture=*&id=12');

Questo funziona anche per i partial:

    [php]
    $cacheManager->remove('@sf_cache_partial?module=user&action=_my_partial
     ➥ &sf_cache_key=*'); // Rimozione per tutte le chiavi

Il metodo `remove()` accetta due parametri in più, consentendo di definire quali host e header `Vary` si vogliono rimuovere dalla cache. 
Questo perché symfony mantiene una versione di cache per ogni host e header `Vary`, quindi due applicazioni che condividano lo stesso codice ma non lo stesso host utilizzerebbero cache diverse.
Questo può essere molto utile, ad esempio, quando un'applicazione interpreta il sotto-dominio come parametro di richiesta (come `http://php.askeet.com` e `http://life.askeet.com`). 
Se non si volesse passare gli ultimi due parametri, symfony rimuoverà la cache per l'host corrente e per tutti gli header `Vary`. 
Alternativamente, se si volesse pulire la cache per un altro host, basterebbe richiamare `remove()` come segue:

    [php]
    $cacheManager->remove('user/show?id=*');                     // Rimozione dei record  dell'host e l'utente corrente
    $cacheManager->remove('user/show?id=*', 'life.askeet.com');  // Rimozione dei record di tutti gli utenti e dell'host life.askeet.com 
    $cacheManager->remove('user/show?id=*', '*');                // Rimozione dei record di tutti gli utenti e di tutti gli host 

Il metodo `remove()` funziona in tutte le strategie di cache che puoi definire in `factories.yml` (non solo `sfFileCache`, ma anche `sfAPCCache`, `sfEAcceleratorCache`, `sfMemcacheCache`, `sfSQLiteCache`, e `sfXCacheCache`).

### Eliminazione della cache tra applicazioni

Per esempio, se l'amministratore modificasse un record nella tabella utenti nell'applicazione `backend`, tutte le azioni dipendenti da quell'utente nell'applicazione `frontend` avrebbero bisogno di essere rimosse dalla cache. 
Ma il gestore di cache `view` disponibile nell'applicazione `backend` non è a conoscenza delle regole di routing dell'applicazione `frontend` (le applicazioni sono isolate tra loro). 
Quindi non è possibile scrivere il seguente codice in `backend`:

    [php]
    $cacheManager = sfContext::getInstance()->getViewCacheManager(); // Richiamo dell'oggetto cache manager
    $cacheManager->remove('user/show?id=12');                        // Lo schema non verrebbe trovato, in quanto il template è memorizzato nella cache del frontend

La soluzione è inizializzare un oggetto `sfCache` a mano, con le stesse impostazioni del gestore di cache del frontend. 
Fortunatamente, tutte le classi della cache di symfony forniscono un metodo `removePattern` che fornisce lo stesso servizio del `remove` del gestore di cache di `view`.

Per esempio, se l'applicazione `backend` avesse bisogno di rimuovere la cache per l'azione `user/show` nell'applicazione `frontend` per l'utente con id 12, è possibile agire nel seguente modo:

    [php]
    $frontend_cache_dir = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'frontend'.
     ➥ DIRECTORY_SEPARATOR.sfConfig::get('sf_environment').DIRECTORY_SEPARATOR.'template';
    $cache = new sfFileCache(array('cache_dir' => $frontend_cache_dir)); // Utilizza la stessa configurazione definita in factories.yml del frontend
    $cache->removePattern('user/show?id=12');

Per diverse strategie di cache, è necessario solamente cambiare l'inizializzazione dell'oggetto `cache`, ma il processo di eliminazione resta lo stesso: 

    [php]
    $cache = new sfMemcacheCache(array('prefix' => 'frontend'));
    $cache->removePattern('user/show?id=12');

Testare e monitorare la cache
-----------------------------

La cache HTML, se non gestita correttamente, può creare inconsistenze nella visualizzazione dei dati. Ogni volta che la cache viene disabilitata per un elemento, è necessario testarlo estensivamente e controllare la velocità di caricamento.

### Creazione di un ambiente di stage

Il sistema di cache è incline a errori nell'ambiente di produzione, che non appaiono in quello di sviluppo, in quanto come impostazione predefinita la cache non è abilitata nell'ambiente di sviluppo. 
Se si abilitasse la cache HTML per qualche azione, sarebbe necessario aggiungere un nuovo ambiente, chiamato di stage in questa sezione, con le stesse impostazioni dell'ambiente `prod` (quindi, con la cache abilitata), 
ma con `web_debug` impostato a `true`.

Per impostarlo, occorre modificare il file `settings.yml` della applicazione aggiungendo all'inizio del file il codice del Listato 12-12.

Listato 12-12 - Impostazione di un ambiente di `stage`, in `frontend/config/settings.yml`

    stage:
      .settings:
        web_debug:  true
        cache:      true

In aggiunta, si deve creare un nuovo front controller copiando quello di produzione (probabilmente `myproject/web/index.php`) con nome `frontend_stage.php`.
Successivamente è necessario modificare i valori di `SF_ENVIRONMENT` e `SF_DEBUG` come segue:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'stage', true);

Tutto ciò è quello che serve per avere un nuovo ambiente da utilizzare aggiungendo il nome del front controller dopo il nome del dominio:

    http://myapp.example.com/frontend_stage.php/user/list

### Monitorare le prestazioni

Il capitolo 16 affronterà nel dettaglio la web debug toolbar e le sue funzioni. 
Comunque, dato che la toolbar offre informazioni importanti riguardanti la cache, seguono alcune informazioni sulle sue funzioni relative.

Durante la navigazione di una pagina che contiene elementi adatti a essere memorizzati in cache (azioni, partial, frammento e così via) 
la web debug toolbar (nell'angolo in alto a destra) mostrerà un pulsante che permette di ignorare la cache (una freccetta verde arrotondata), 
come mostrato in Figura 12-4. Tale pulsante ricaricherà la pagina ignorando gli elementi in cache. Questa operazione non svuoterà la cache.

L'ultimo numero sul lato destro rappresenterà la durata dell'esecuzione della richiesta. Abilitando la cache in una pagina, questo numero 
dovrebbe diminuire il numero dei secondi necessari al suo caricamento, dato che symfony userà i dati della cache invece di riprocessare gli script. 
è possibile monitorare facilmente le prestazioni della cache con questo indicatore.

Figura 12-4 - La web debug toolbar per pagine che usano la cache

![Web debug toolbar per pagine che utilizzano la cache](http://www.symfony-project.org/images/book/1_4/F1204.png "Web debug toolbar per pagine che utilizzano la cache")

La debug toolbar mostrerà anche il numero di query eseguite durante il processo della richiesta, e i dettagli delle durate per categoria
(cliccando sul totale della durata per visualizzarne i dettagli). Monitorare tali dati, insieme alla durata totale, aiuterà a comprendere i miglioramenti dovuti alla cache.

### Benchmark

La modalità di debug decrementa notevolmente la velocità della tua applicazione, dato che vengono loggati molto dati e resi disponibili alla toolbar. 
Per cui il tempo di calcolo visualizzato quando navighi l'ambiente `stage` non è rappresentativo per quello di produzione, dove la modalità di debug è `off`.

Questi strumenti permettono il test del carico e forniscono 2 importanti tipi di informazioni: la media del tempo di caricamento di una singola pagina e la capacità massima del server sul quale è presente l'applicazione. 
La media del tempo di caricamento è molto utile per monitorare i miglioramenti delle performance dovuti all'utilizzo della cache.

### Identificare parti della cache

Quando la web debug toolbar è abilitata, gli elementi in cache sono individuati in una pagina con un bordo rosso, ognuno avente un piccolo riquadro di informazione sull'angolo in alto a sinistra, 
come mostrato in Figura 12-5. Il riquadro ha uno sfondo blu se l'elemento verrà eseguito, o giallo se sarà presente nella cache. 
Cliccando sul link sarà possibile vedere l'identificatore dell'elemento in cache, il suo tempo di vita, 
ed il tempo trascorso dall'ultima modifica. Questo aiuterà a identificare i problemi nella gestione di elementi fuori dal contesto, per vedere quale elemento è stato creato e quale parte di una template
possa effettivamente essere messo in cache.

Figura 12-5 - Identificazione di un elemento in cache

![Identificazione nella pagina di un elemento proveniente dalla cache](http://www.symfony-project.org/images/book/1_4/F1205.png "Identificazione nella pagina di un elemento proveniente dalla cache")

HTTP 1.1 e cache lato client
----------------------------

Il protocollo HTTP 1.1 definisce una manciata di header che possono essere di grande utilizzo per incrementare la velocità di un applicazione controllando il sistema di cache del browser.

Le specifiche HTTP 1.1 del World Wide Web Consortium (W3C, [http://www. w3.org/Protocols/rfc2616/rfc2616-sec14.html]) descrivono tali header in dettaglio. 
Se un'azione ha la cache abilitata e usa l'opzione `with_layout`, può utilizzare uno o più meccanismi descritti in questa sezione.

Anche se alcuni browser degli utenti che utilizzassero l'applicazione non supportassero HTTP 1.1, non vi è alcun rischio nell'utilizzo  delle funzionalità di cache del protocollo. 
Un browser ignorerebbe header che conosce, perciò è consigliato l'utilizzo di meccanismi di cache HTTP 1.1.

Inoltre, gli header HTTP 1.1 sono compresi anche dai proxy e dai server di cache. 
Anche se il browser di un utente non comprendesse il protocollo, ci potrebbe essere sul percorso fino a esso un server che se potrebbe trarre avvantaggia.

### Aggiungere un header ETag per evitare di rispedire contenuto non modificato

Quando la funzionalità ETag è abilitata, il web server aggiungerà alla risposta un header speciale che contiene la firma della risposta stessa

    ETag: "1A2Z3E4R5T6Y7U"

Il browser dell'utente memorizzerebbe tale firma, e la spedirebbe insieme alla richiesta successiva.
Se la nuova firma mostrasse che la pagina non è cambiata rispetto alla richiesta precedente, il browser non rimanderà indietro la risposta. 
Invierebbe invece un header `304: Not modified`, operazione che fa risparmiare tempo di CPU (ad esempio se la compressione gzip fosse abilitata) e banda (trasferimento della pagina) al server, 
e tempo (trasferimento della pagina) al client. Soprattutto, le pagine in cache con ETag sono più veloci da caricare di quelle senza.

In symfony, è possibile abilitare la funzionalità ETag per l'intera applicazione nel file `settings.yml`. Di seguito l'impostazione di default:

    all:
      .settings:
        etag: true

Per azioni in cache con layout, la risposta viene presa direttamente dalla cartella `cache/`, operazione che velocizza maggiormente l'intero processo.

### Aggiungere un header Last-Modified per evitare di rispedire contenuto ancora valido

Quando il server spedisce la risposta al browser, potrà aggiungere un header che specifichi quando il contenuto della pagina è stato modificato l'ultima volta:

    Last-Modified: Sat, 23 Nov 2010 13:27:31 GMT

I browser comprendono tale header, e quando richiederanno nuovamente la stessa pagina, aggiungeranno un `If-Modified` di conseguenza:

    If-Modified-Since: Sat, 23 Nov 2010   13:27:31 GMT

Il server potrà quindi confrontare il valore del client e quello restituito dalla propria applicazione. Se i due corrispondessero, il server restituirebbe l'header `304: Not modified`, 
risparmiando proprio come con ETag tempo di CPU e banda.

In symfony, è possibile impostare l'header di risposta `Last-Modified` proprio come si farebbe per un altro header. Ad esempio, è possibile utilizzarlo in un'azione nel seguente modo:

    [php]
    $this->getResponse()->setHttpHeader('Last-Modified', $this->getResponse()->getDate($timestamp));

Questa data potrà essere effettivamente quella dell'ultimo aggiornamento dei dati della pagina, ricavata del database o dal filesystem. 
Il metodo `getDate()` dell'oggetto `sfResponse` convertirà un timestamp in una data nel formato di cui necessiti nell'header `Last-Modified` (RFC1123).

### Aggiungere l'header  Vary per permettere versioni differenti di una pagina in cache

Un altro header HTTP 1.1 è `Vary`. Esso definisce da quali parametri dipende una pagina, ed è utilizzato da browser e proxy per costruire chiavi di cache. 
Ad esempio, se il contenuto di una pagina dipendesse da cookie, è possibile impostare l'header `Vary` come segue:

    Vary: Cookie

Molto spesso è difficile abilitare la cache sulle azioni a causa del fatto che la pagina potrebbe cambiare a seconda dei cookie, della lingua o qualcos'altro. 
Se non ci fossero problemi di spazio per contenere una cache di grosse dimensioni, è sufficiente impostare correttamente l'header `Vary`. 
Ciò può essere fatto per l'intera applicazione o per azione, utilizzando il file di configurazione `cache.yml` o il relativo metodo `sfResponse` come segue:

    [php]
    $this->getResponse()->addVaryHttpHeader('Cookiè);
    $this->getResponse()->addVaryHttpHeader('User-Agent');
    $this->getResponse()->addVaryHttpHeader('Accept-Languagè);

Symfony memorizzerà diverse versioni della pagina in cache per ogni valore di tali parametri.
Questo aumenterà significativamente la dimensione della cache, ma ogni qualvolta il server riceverà una richiesta corrispondente a tali header, la risposta sarà presa dalla cache invece di essere processata.
Si tratta di un grande strumento che aumenta le prestazioni delle pagine che variano solo in base agli header di richiesta.
  
### Aggiungere un header Cache-Control per abilitare la cache lato client

Finora, anche aggiungendo gli header visti, il browser continuerà a spedire richieste al server anche possedendo una versione in cache di una pagina. 
Tale comportamento si può evitare aggiungendo gli header `Cache-Control` ed `Expires` alla risposta. 
Questi header per default sono disabilitati in PHP, ma symfony può farne l'override in modo da evitare richieste non necessarie al server.

Come al solito, è possibile scatenare tale comportamento chiamando un metodo dell'oggetto `sfResponse`. 
In un'azione, sarà necessario definire il tempo massimo in secondi per i quali una pagina debba essere  mantenuta in cache:

    [php]
    $this->getResponse()->addCacheControlHttpHeader('max_age=60');

È possibile anche specificare sotto quali condizioni una pagina potrà essere messa in cache, in modo da non lasciare memorizzati dati privati (come ad esempio un numero di conto corrente) in cache:

    [php]
    $this->getResponse()->addCacheControlHttpHeader('private=Truè);

Utilizzando la direttiva HTTP `Cache-Control`, si protrà regolare i diversi meccanismi di cache tra il server e il browser client.
Per maggiori dettagli su tali direttive, consultare le specifiche W3C di `Cache-Control`.

Un ultimo header può essere spedito tramite symfony: `Expires`.

    [php]
    $this->getResponse()->setHttpHeader('Expires', $this->getResponse()->getDate($timestamp));

>**CAUTION**
>La conseguenza principale dell'abilitazione del meccanismo `Cache-Control` è che il server non mostrerà tutte le richieste eseguite dagli utenti, 
>ma solo quelle ricevute effettivamente. Se le performance migliorassero, l'apparente popolarità del sito potrebbe diminuire nelle statistiche.

Sommario
--------

Il sistema di cache fornisce accelerazioni variabili delle performance a seconda del tipo di cache scelta. Dal maggior guadagno al minimo, i tipi di cache sono i seguenti:

  * Super cache
  * Cache di azioni con layout
  * Cache di azioni senza layout
  * Cache di frammento nelle template

Inoltre è possibile mettere in cache anche partial e component.

Se il cambiamento di dati nel modello o nella sessione obbligasse a svuotare la cache per una questione di coerenza, 
è possibile farlo con fine granularità per ottimizzare le prestazioni, ovvero eliminando solo gli elementi che hanno subito un cambiamento e mantenendo gli altri inalterati.

È da tener presente che sarà necessario testare con maggior attenzione le pagine con cache abilitata, dato che potrebbero apparire nuovi bug mettendo in cache gli elementi sbagliati o dimenticando di eliminarli quando verranno aggiornati i dati sottostanti. 
Un ambiente di stage, dedicato al test della cache, è di grande utilità a questo scopo.

Infine, si consiglia di trarre il meglio dagli header del protocollo HTTP 1.1 grazie alle funzionalità avanzate di symfony, 
con il coinvolgimento del client nelle operazioni di cache si otterrà un ulteriore incremento delle prestazioni.
