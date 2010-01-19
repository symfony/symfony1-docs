Sviluppare su Facebook
=======================

*di Fabrice Bernhard*

Facebook, con i suoi quasi 300 milioni di iscritti, è diventato lo standard dei
siti sociali su Internet. Una delle sue caratteristiche più interessanti è la
"piattaforma Facebook", una API che consente agli sviluppatori sia di creare applicazioni
all'interno di Facebook che di sfruttarne il sistema di autenticazione (Facebook Connect) e grafico
da siti esterni.

Dal momento che l'interfaccia di Facebook è in PHP, non c'è da meravigliarsi che la libreria ufficiale
per questa API sia stata scritta nello stesso linguaggio. Questo fatto rende symfony una logica
soluzione per lo sviluppo rapido e pulito di applicazioni per Facebook, o Facebook Connect. 
Inoltre, sviluppare applicazioni per Facebook mostra realmente come
sia possibile sfruttare le funzionalità di symfony per guadagnare tempo prezioso, mantenendo
elevati standard di qualità. In questo capitolo verranno affrontati i seguenti temi: dopo una
breve sintesi su cos'è l'API di Facebook e di come può essere utilizzata, verrà mostrato
come utilizzare symfony al meglio nello sviluppo di applicazioni per Facebook,
come trarre vantaggio dagli sforzi della comunità e del plugin `sfFacebookConnectPlugin`,
che verrà usato per sviluppare una semplice applicazione "Ciao Mondo!" e, infine, daremo consigli
e trucchi per risolvere i problemi più comuni. 

Sviluppare su Facebook
-----------------------

Nonostante le API siano fondamentalmente le stesse in entrambi i casi, ci sono due
diversi casi d'uso: la creazione di un'Applicazione Facebook che funziona all'interno di Facebook 
stesso e lo sviluppo di una applicazione che tramite Facebook Connect funziona su un sito esterno.

### Applicazioni Facebook

Le Applicazioni Facebook sono applicazioni web che funzionano all'interno del social software.
La loro principale qualità è quella di essere inclusa nella piattaforma social utilizzata da oltre 300
milioni di utenti, e di conseguenza ogni applicazione virale sviluppata cresce a velocità incredibile.
Farmville è uno dei più importanti ed ultimi esempi, con oltre 60 milioni di utenti attivi ogni mese e 
2 milioni di fan racimolati in pochi mesi! Un numero che potrebbe rappresentare l'equivalente della 
popolazione Francese che torna alle proprie fattorie virtuali ogni mese! Le Applicazioni Facebook 
interagiscono con il sito Facebook ed il suo social graph in diversi modi. Qui di seguito una spiegazione su 
dove una applicazione può apparire:

#### Il Canvas

Il canvas è per la maggior parte del tempo la principale parte dell'applicazione. È essenzialmente un piccolo
sito web incluso all'interno dell'interfaccia di Facebook.

#### Dentro una scheda del profilo

Una applicazione può anche risiedere all'interno di una scheda del profilo dell'utente o di una fan page.
Le principali limitazioni sono:

 * solo una pagina. Non è possibile definire link a sotto pagine della tab.

 * niente flash dinamico o JavaScript durante il caricamento. Per offrire funzionalità
   dinamiche, l'applicazione dovrà aspettare che l'utente interagisca con la pagine cliccando su un 
   link od un bottone.

#### Dentro un Box del profilo

Questo è più che altro un pezzo del vecchio Facebook, che probabilmente nessuno usa veramente ancora.
L'applicazione viene mostrata all'interno di un box che può essere trovato all'interno della scheda "Box" 
del profilo.

#### Aggiunta alla scheda informazioni

Alcune informazioni statiche, associate ad uno specifico utente o ad una applicazione, possono
essere mostrate in una scheda informazioni del profilo utente. Subito sotto l'età, 
l'indirizzo ed il curriculum.

#### Pubblicare avvisi all'interno dello Stream

L'applicazione può pubblicare notizie, link, immagini e video all'interno dello stream delle notizie, o
nel wall dell'utente o cambiando lo stato di quest'ultimo.

#### La pagina delle informazioni

Questa è la pagina del profilo dell'applicazione, generata automaticamente da Facebook.
Qui l'applicazione sarà in grado di interagire con gli utenti nel classico modo di Facebook, 
anche se è più uno strumento per il reparto marketing che per gli sviluppatori.

### Facebook Connect

Facebook Connect consente a qualsiasi sito web di portare alcune delle grandi
funzionalità di Facebook ai propri utenti. I siti già "connessi" possono
essere riconosciuto dalla presenza di un pulsante blu "Connect with Facebook".
I più famosi siti che utilizzano questa tecnica sono digg.com, cnet.com, netvibes.com, yelp.com, ecc. 
Qui di seguito la lista dei quattro principali motivi per usare Facebook 
Connect con un sito esistente.

#### One-click authentication system

Proprio come OpenID, Facebook Connect offre ai siti web la possibilità di fornire
login automatico utilizzando la stessa sessione di Facebook. Una volta che la 
"connessione" tra il sito web e Facebook è stato approvata da parte dell'utente, 
la sessione Facebook viene automaticamente data al sito che toglie poi l'onere 
all'utente di creare un nuovo login o ricordarsi la nuova password.

#### Recuperare maggiori informazioni sull'utente

Un altro elemento fondamentale di Facebook Connect è la quantità di informazioni
gestite. Mentre un utente può generalmente caricare un insieme minimo di 
informazioni su un nuovo sito web, in fase di registrazione, Facebook Connect 
offre l'opportunità di ottenere rapidamente interessanti ulteriori informazioni
come il nome, età, sesso, luogo, foto del profilo, ecc. per arricchire il sito. 
I termini di uso di Facebook Connect spiegano chiaramente che non si dovrebbero 
memorizzare tutte le informazioni personali dell'utente senza il suo esplicito 
consenso, ma le informazioni fornite possono essere utilizzate per
compilare i moduli e chiedere conferma in un click, o il sito può utilizzarle
per mostrarle direttamente, come nel caso del nome e della foto del profilo, 
senza necessariamente doverle salvare.

#### Comunicazione virale usando i feed

La capacità di interagire con i feed di un utente, invitare amici o pubblicare sul
Wall, fornisce al sito web tutte le potenzialità virali di Facebook per 
comunicare. Qualsiasi sito web con qualche componente sociale può beneficiare
di questa caratteristica, fintanto che le informazioni pubblicate nel feed Facebook 
hanno un valore sociale che potrebbero interessare amici e amici di amici.

#### Sfruttare un social graph pre-esistente

Per un sito web il cui servizio si basa su un social graph (come una rete di
amici o conoscenti), il costo per costruire una prima comunità, con sufficienti
collegamenti tra gli utenti per interagire e fruire del servizio è veramente alto.
Dando un facile accesso alla lista di amici di un utente, Facebook Connect
riduce drasticamente questo costo, eliminando la necessità di cercare "gli amici già
registrati".

Configurare un progetto usando `sfFacebookConnectPlugin`
----------------------------------------------------------

### Creare l'applicazione Facebook

Per iniziare è necessario avere un account con l'applicazione 
["Developer"](http://www.facebook.com/developers) installata. Poi, per
creare l'applicazione, l'unica informazione necessaria è il nome. Una volta inserito, non sarà 
necessaria nessun'altra configurazione.

### Installare e configurare `sfFacebookConnectPlugin`

Il passo successivo è collegare l'utente Facebook's con un utente `sfGuard`. Questa è una
delle principali funzionalità del plugin `sfFacebookConnectPlugin` che ho realizzato e che ha
ricevuto il contributo di altri sviluppatori symfony. Una volta che il plugin è stato installato,
bisognerà fare un semplice, ma necessario passo di configurazione. Bisogna configurare la chiave API,
la "application secret" e l'ID dell'applicazione all'interno del file `app.yml`:

    [yml]
    # valori predefiniti
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url: ''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery o prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # ATTENZIONE questa colonna deve essere di tipo varchar! 100000398093902 è, ad esempio, un uid valido!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing:     true
        user_permissions: []

>**TIP**
>Con le vecchie versioni di symfony, è importante ricordarsi di impostare l'opzione "load_routing"
>a false, in quanto utilizza il nuovo sistema di routing.

### Configurare una applicazione Facebook

Se il progetto è una applicazione Facebook, gli altri parametri importanti
sono `app_url`, che punta al percorso relativo dell'applicazione su 
Facebook. Ad esempio, per l'applicazione `http://apps.facebook.com/my-app`
il valore del parametro `app_url` sarà `/my-app`.

### Configurare un sito per Facebook Connect

Se il progetto è un sito che usa Facebook Connect website, i parametri di configurazione
possono essere utilizzati, per la maggior parte delle volte, con i valori standard:

 * `redirect_after_connect` abilita il comportamento che l'applicazione utilizzerà dopo
   che un utente ha cliccato sul pulsante "Connect with Facebook". Se non impostato il 
   plugin riproduce il comportamento del metodo `sfGuardPlugin` dopo la registrazione.

 * `js_framework` può essere utilizzato per specificare qual'è il framework JS da usare. È
   particolarmente raccomandato l'uso di un framework javascript come jQuery su un sito Facebook Connect
   in quanto il javascript utilizzato da Facebook Connect è pesante e può causare, se non istanziato al momento giusto, 
   errori fatali (!) ad IE6.

 * `user_permissions` è un array di permessi che sarà associato al nuovo utente 
   Facebook Connect.

### Connettere sfGuard a Facebook

La connessione tra un utente Facebook e un utente di `sfGuardPlugin` è fatta abbastanza
logicamente usando una colonna `facebook_uid` nella tabella `Profile`. Il plugin
presuppone che il legame tra l'utente `sfGuardUser` ed il suo profilo sia fatto utilizzando
il metodo `getProfile()`. Questo è il comportamento predefinito di
`sfPropelGuardPlugin`, ma deve essere configurato come tale in
`sfDoctrineGuardPlugin`. Qui di seguito un possibile file `schema.yml`:

Per Propel:

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id:            { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name:         { type: varchar, size: 30 }
      last_name:          { type: varchar, size: 30 }
      facebook_uid:       { type: varchar, size: 20 }
      email:              { type: varchar, size: 255 }
      email_hash:         { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index:        [email]
        email_hash_index:   [email_hash]

Per Doctrine:

    [yml]
    sfGuardUserProfile:
      tableName:     sf_guard_user_profile
      columns:
        user_id:          { type: integer(4), notnull: true }
        first_name:       { type: string(30) }
        last_name:        { type: string(30) }
        facebook_uid:     { type: string(20) }
        email:            { type: string(255) }
        email_hash:       { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile


>**TIP**
> Che cosa succede se il progetto utilizza Doctrine e la `foreignAlias` non è nel `Profile`. In
> questo caso il plugin non funzionerà. Ma un semplice metodo `getProfile()` nella
> classe `sfGuardUser.class.php` che punta alla tabella `Profile`, risolverà il problema!

Bisogna notare che la colonna `facebook_uid` deve essere di tipo `varchar`, perché i nuovi
profili su Facebook sono rappresentati da `uid` superiore a `10^15`. Meglio andare sul sicuro con una
colonna `varchar` indicizzata che provare ad utilizzare un tipo `bigint` che può avere comportamenti 
differenti sui vari ORM.

Le altre due colonne sono meno importanti: `email` ed `email_hash` sono solo
necessarie nel caso si stia sviluppando un sito web con Facebook Connect che ha già utenti esistenti. In
questo caso, Facebook prevede un processo complicato per cercare di associare account esistenti
con quelli di Facebook Connect usando l'hash dell'email. Fortunatamente questo 
processo di autenticazione è reso semplice da `sfFacebookConnectPlugin` ed è descritto nell'ultima parte di questo
articolo.

### Scegliere tra FBML e XFBML: un problema risolto da symfony

Ora che tutto è configurato, possiamo cominciare a scrivere il codice dell'applicazione.
Facebook offre molti tag speciali che possono visualizzare una funzionalità, come la form
di "invito agli amici" o il sistema di commenti. Questi tag sono
chiamati FBML e XFBML. FBML e XFBML sono abbastanza simili, ma la scelta di uno o dell'altro 
dipende se l'applicazione viene visualizzata all'interno di Facebook o no. 
Se il progetto è un sito con Facebook Connect, c'è solo una scelta: XFBML.
Mentre, se si tratta di una applicazione Facebook, le scelte possibili sono due:

 * Includere l'applicazione all'interno di un Iframe all'interno di una pagina applicazione, ed usare
   quindi XFBML all'interno di questo Iframe;

 * Lasciare a Facebook il compito di visualizzare l'applicazione, trasparentemente, 
   al suo interno usando FBML.

Facebook incoraggia i programmatori ad usare la seconda soluzione, denominata "embed trasparente" o 
"applicazione FBML". Infatti, ha alcune caratteristiche interessanti:

 * Nessun Iframe, che è sempre complicato da gestire, in quanto è necessario ricordarsi 
   se il vostro link riguardano l'iframe o la finestra padre;

 * Tag speciali chiamati tag FBML che vengono interpretati automaticamente dal server Facebook
   e che consentono di visualizzare le informazioni private riguardanti l'utente
   senza dover interagire ulteriormente con il server;

 * Non c'è bisogno di passare la sessione di Facebook da pagina a pagina manualmente.

Ma l'utilizzo di FBML ha anche alcuni svantaggi notevoli:

 * Ogni javascript è eseguito all'interno di una sandbox, rendendo impossibile l'uso
   di librerie esterne come quelle di Google Maps, jQuery o un qualsiasi sistema di statistiche
   diverso da Google Analytics, ufficialmente supportato da Facebook;

 * Sostiene di essere più veloce in quanto alcune chiamate API possono essere sostituite da tag FBML.
   Tuttavia, se l'applicazione è leggera, basterà ospitarla su un proprio server per renderla più veloce;

 * E più difficile fare il debug, in particolare per l'errore HTTP 500, che Facebook
   sostituisce con messaggi di errore standard.

Allora, qual è la scelta consigliata? La buona notizia è che con symfony ed il plugin
`sfFacebookConnectPlugin` non bisogna scegliere nulla! E possibile scrivere
applicazioni agnostiche e passare indifferentemente da un iframe ad una applicazione embedded
per un sito con Facebook Connect utilizzando lo stesso codice. Ciò è possibile
perché tecnicamente la differenza principale è in realtà nel layout... che è
molto facile modificare in symfony. Ecco gli esempi dei due differenti
layout:

Il layout per una applicazione FBML:

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

Il layout per una applicazione XFBML, o per un sito con Facebook Connect:

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
      </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

Per passare da un layout all'altro, basta aggiungere al file `actions.class.php` il seguente codice:

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTA**
>C'è una piccola differenza tra FBML e XFBML che non dipende però dal layout:
>i tag FBML possono essere chiusi, mentre quelli XFBML no. Quindi è importante
>correggere i tag seguenti:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400" />
>
>con:
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

Naturalmente, per fare ciò l'applicazione deve essere configurata anche per
Facebook Connect nelle impostazioni dello sviluppatore, anche se è
destinata esclusivamente ad usare FBML. Ma l'enorme vantaggio di fare questo, è
la possibilità di provare l'applicazione anche a livello locale. Se si sta creando una
applicazione Facebook e si è pianificato l'utilizzo dei tag FBML, che è quasi inevitabile,
l'unica soluzione per vedere il risultato è quello di mettere online il codice e vedere il
risultato direttamente in Facebook! Fortunatamente, grazie a Facebook Connect, i
tag XFBML possono essere visualizzati al di fuori di facebook.com. E come è stato appena descritto,
l'unica differenza tra FBML e tag XFBML è nel layout del template.
Pertanto, questa soluzione permette di visualizzare i tag FBML localmente, sempre che si sia 
connessi ad Internet. Inoltre, con un ambiente di sviluppo visibile
su Internet, come ad esempio un server o un semplice computer con la porta 80 aperta e 
raggiungibile dall'esterno, la maggior parte delle funzionalità saranno effettivamente 
utilizzabili, grazie a Facebook Connect.

### Una semplice applicazione "Ciao mondo"

Con il seguente codice nel template della homepage, l'applicazione "Ciao mondo" si può dire conclusa:

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Hello <fb:name uid="<?php echo $sfGuardUser?$sfGuardUser->getProfile()->getFacebookUid():'' ?>"></fb:name>

Il plugin `sfFacebookConnectPlugin` automaticamente converte l'utente Facebook in un utente `sfGuard`. 
Questo permette poi una semplice integrazione con il codice symfony pre-esistente che sfrutta `sfGuardPlugin`.

Facebook Connect
----------------

### Funzionamento di Facebook Connect e strategie di integrazione

Facebook Connect condivide sostanzialmente la sessione con quella del sito. Questo è
fatto copiando i cookie di autenticazione da Facebook al sito attraverso l'apertura
sul sito web di un IFrame che punta a una pagina di Facebook, che, a sua volta, apre un
IFrame verso il sito. Per fare questo, Facebook Connect ha bisogno di avere accesso al 
sito web, e ciò rende impossibile l'utilizzo o il test Facebook Connect su un
server locale od in una rete Intranet. Il punto di ingresso è il file `xd_receiver.htm`
che il plugin `sfFacebookConnectPlugin` prevede, ma è importante ricordarsi di usare il
task `plugin:publish-assets` per pubblicarlo e renderlo accessibile.

Una volta fatto questo, la libreria ufficiale di Facebook è in grado di utilizzare la 
sessione di Facebook. Quello che il plugin `sfFacebookConnectPlugin` fa oltre a questo, 
è quello di creare un utente `sfGuard` collegato alla sessione di Facebook per integrarsi con
il sito di symfony esistente. Questo è il motivo per cui il plugin reindirizza di default verso
la rotta `sfFacebookConnectAuth/signIn` una volta che il pulsante di Facebook Connect è stato
cliccato e la sessione Facebook Connect è stata convalidata. Il plugin cerca per prima cosa
un utente esistente con lo stesso UID di Facebook, o con lo stesso hash dell'e-mail (vedi 
"Connettere gli utenti esistenti con il loro account di Facebook" alla fine dell'articolo) 
e se non viene trovato nulla, viene creato un nuovo utente.

Un'altra strategia comune è quella di creare l'utente reindirizzandolo ad una
specifica  form di registrazione. Dove, usando la sessione di Facebook si
possono pre-compilare alcune informazioni delle form, come viene fatto ad
esempio aggiungendo il seguente codice nel metodo di configurazione della form
di iscrizione:

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );
        
        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }
          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

Per usare la seconda strategia, basta specificare nel file `app.yml` a quale azione 
fare redirect dopo l'esecuzione di Facebook Connect:

    [yml]
    # default values
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### Il filtro per Facebook Connect

Un'altra importante caratteristica di Facebook Connect è che gli utenti di Facebook sono 
spesso connessi su Facebook durante la navigazione in Internet. In questo caso
il filtro `sfFacebookConnectRememberMeFilter` si dimostra molto utile, in quanto fa 
il log-in automatico sul sito. Esattamente come se ci fosse una funzionalità "Ricordati di me" 
tra Facebook Connect ed il sito.

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

Tuttavia questo può anche rivelarsi un serio svantaggio: gli utenti non possono 
fare il logout dal sito, dal momento che, fino a quando questi sono connessi su 
Facebook, il filtro provvederà a riconnetterli automaticamente. 
Bisogna usarlo quindi con cautela.

### Una implementazione pulita per evitare un errore irreversibile di IE con javascript

Uno dei bug più temibili che è possibile avere su un sito web è quello per IE che esegue una "Operazione
interrotta", cioé l'errore può bloccare il rendering del sito... client-side! 
Ciò è dovuto alla cattiva qualità del motore di rendering di IE6 ed IE7, 
che può mandare in crash il browser, se si aggiungono elementi DOM per il tag `body` da uno
script che non è direttamente un figlio del tag `body`. E questo è esattamente 
il caso del javascript di Facebook Connect quando viene caricato senza fare attenzione al 
completo caricamento del documento. 
Fortunatamente questo bug può essere risolto con symfony utilizzando gli slot. Infatti è possibile utilizzare
uno slot per inserire lo script di Facebook Connect ogni volta che è necessario nel template, e caricarlo 
nel layout alla fine del documento, prima della chiusura del tag `</ body>`:

    [php]
    // in un template che usa i tag XFBML o il bottone per Facebook Connect
    slot('fb_connect');
    include_facebook_connect_script();
    end_slot();

    // Subito prima il </body> per evitare problemi con IE
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Buone pratiche per sviluppare applicazioni Facebook
---------------------------------------------------

Grazie al plugin `sfFacebookConnectPlugin`, l'integrazione con `sfGuardPlugin`
è resa semplice a prescindere dal fatto che l'applicazione sfrutti FBML, un IFrame
o Facebook Connect. Andando oltre, per creare una vera applicazione che sfrutti
tutte le funzionalità di Facebook, bisogna seguire alcuni suggerimenti per usare 
al meglio le funzionalità di symfony.

### Usare gli ambienti di symfony per configurare diversi server di test per Facebook Connect

Un aspetto molto importante della filosofia symfony è dato dalla possibilità di fare debug
velocemente e di assicurarsi, tramite test funzionali, il corretto funzionamento dell'applicazione.
Utilizzando Facebook questo può rivelarsi complesso in quanto è necessaria una connessione ad Internet
per comunicare con il server di Facebook, inoltre la porta 80 della nostra macchina di sviluppo deve essere 
raggiungibile da remoto al fine di poter scambiare i cookie di autenticazione.
Inoltre c'è un altro vincolo: una applicazione Facebook Connect può essere collegata ad un solo host. 
Questo è un problema spinoso se l'applicazione è sviluppata su una macchina, testata su un'altra, 
messa in pre-produzione su un terzo server ed utilizzata, infine, su un quarto. 
In tal caso la soluzione più semplice è quella di creare effettivamente una applicazione per ogni server 
e creare quindi un ambiente symfony per ciascuno di essi. 
Questo è molto semplice in symfony: basta fare un semplice copia e incolla del file `frontend_dev.php`, 
o di un suo equivalente, in `frontend_preprod.php` e modificarne la riga mostrata qui sotto, per usare al 
posto dell'ambiente `dev` uno nuovo chiamato `preprod`:

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

Poi bisognerà modificare il file `app.yml` per configurare le differenti applicazioni Facebook 
inserendo i corretti parametri associati ad ogni ambiente:

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

Adesso l'applicazione potrà essere testata in ogni possibile server usando il corretto 
front-controller `frontend_xxx.php`.

### Usare il sistema di log di symfony per il debug del FBML

La possibilità di cambiare il layout consente di sviluppare e testare quasi tutta una
applicazione FBML al di fuori del sito Facebook. Tuttavia, la prova finale all'interno
di Facebook può talvolta restituire messaggi di errore oscuri.
Infatti, il problema principale di visualizzare FBML direttamente in Facebook è che 
l'errore 500 è catturato e sostituito da un errore standard e non molto utile al fine 
del debug. Oltre a quello, la web debug toolbar, tanto amata dagli sviluppatori di symfony,
non può essere utilizzata nell'interfaccia di Facebook. Fortunatamente l'ottimo 
sistema di log di symfony è lì per salvarci. Il plugin `sfFacebookConnectPlugin` 
registra già molte azioni importanti ed è facile aggiungere nuovi commenti nel file di log 
da qualsiasi punto dell'applicazione:

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### Usare un proxy per evitare redirezioni errate da Facebook

Uno strano comportamento di Facebook è che, una volta che Facebook Connect è configurato 
nell'applicazione, il server di Facebook Connect è considerato come homepage 
dell'applicazione stessa. E, anche se la homepage può essere configurata, deve essere all'interno
del dominio di Facebook. Quindi non c'è altra soluzione che la resa e configurare la propria home 
come una semplice action di symfony che rediriga tutto quello che è necessario verso l'applicazione
Facebook così come nell'esempio successivo:

    [php]
    public function executeRedirect(sfWebRequest $request)
    {

      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Usare l'helper `fb_url_for()` nell'applicazione Facebook

Per mantenere l'applicazione agnostica, affinché possa utilizzare FBML dentro Facebook o XFBML
in un iframe, una importante problematica da gestire è il routing:

 * Per una applicazione FBML, i link all'interno dell'applicazione devono puntare al corretto routing
   `/app-name/symfony-route`;

 * per una applicazione contenuta in un IFrame, è importante che sia passata ad ogni pagina
   l'informazione riguardante la sessione di Facebook.

Per risolvere questo problema, il plugin `sfFacebookConnectPlugin`, espone uno speciale helper, 
chiamato `fb_url_for()`, che fa entrambe le cose.

### Redirezionare all'interno di una applicazione FBML

Gli sviluppatori symfony sono abituati a fare un redirect subito dopo ad una azione di POST, 
è una buona pratica nello sviluppo web per evitare possibili doppi POST. Fare una redirezione in
una applicazione FBML tuttavia non funziona tanto semplicemente, bisogna infatti usare un tag FBML 
speciale chiamato `<fb:redirect>` per dire a Facebook di eseguire la redirezione. Per utilizzare
il miglior sistema in base al contesto di esecuzione, che sia il tag FBML o il redirect di symfony, 
è presente nella classe `sfFacebook` una speciale funzione reindirizzamento, che
può essere utilizzata in una form di salvataggio così come illustrato di seguito:

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Connettere gli utenti esistenti con il loro account di Facebook

Uno degli scopi di Facebook Connect è quello di facilitare il processo di
registrazione di nuovi utenti. Tuttavia, sembra interessante anche
connettere utenti esistenti con i loro account di Facebook, sia per
ottenere maggiori informazioni su di essi, come l'immagine del profilo
o la lista degli amici, sia per comunicare nei loro feed. Lo si può
fare in due modi:

 * Spingere gli utenti esistenti di sfGuard a cliccare sul pulsante
   "Connect with Facebook". L'azione `sfFacebookConnectAuth/signIn` non
   creerà un nuovo utente in sfGuard, se troverà un utente già
   autenticato, ma semplicemente salverà l'utente appena connesso
   a Facebook con l'utente attuale di sfGuard. È così facile.

 * Usare il sistema di riconoscimento dell'email di Facebook. Quando un
   utente usa Facebook Connect su un sito, Facebook può fornire un hash
   speciale delle sue email, che può essere confrontato con gli hash delle
   email nel database esistente per riconoscere un account che appartiene
   all'utente precedentemente creato. Tuttavia, probabilmente per motivi
   di sicurezza, Facebook fornisce tali hash solo se le email sono state
   già registrare in precedenza usando le loro API! Quindi è importante
   registrare tutte le email dei nuovi utenti con regolarità, per poter
   riconoscerle successivamente. Questo è ciò che viene fatto dal task
   `registerUsers`, migrato alla 1.2 da Damien Alexandre. Questo task
   dovrebbe essere eseguito almeno ogni notte, per registrare i nuovi
   utenti, oppure dopo che un nuovo utente è stato creato, usando il
   metodo `registerUsers` di `sfFacebookConnect`:

    [php]
    sfFacebookConnect::registerUsers(array($sfGuardUser)); 

Considerazioni finali
---------------------

Spero che questo articolo sia riuscito nel suo scopo: aiutare
ad iniziare lo sviluppo di un'applicazione per Facebook usando symfony
e far capire come sfruttare symfony durante lo sviluppo su Facebook. Comunque, il
plugin `sfFacebookConnectPlugin` non sostituisce le API di Facebook e
per imparare ad usare a pieno la potenza della piattaforma di sviluppo di
Facebbok occorre visitare il suo [sito](http://developers.facebook.com/).

Un ringraziamento alla comunità di symfony per la sua qualità e la sua
generosità, specialmente a quelli che hanno già contribuito a
`sfFacebookConnectPlugin` con commenti e patch: Damien Alexandre, Thomas Parisot,
Maxime Picaud, Alban Creton e tutti quelli che potrei aver dimenticato
(scusatemi). Se vi sembra che manchi qualcosa nel plugin, non esitate
ad inviare una patch :-)
