Estendere la Web Debug Toolbar
==============================

*di Ryan Weaver*

La web debug toolbar di symfony contiene già molti strumenti che assistono
lo sviluppatore nel debug, nel miglioramento delle prestazioni e in altro
ancora. La web debug toolbar è composta da diversi strumenti, chiamati
*pannelli di debug*, che riguardano cache, configurazione, log, uso
della memoria, versione di symfony e tempi di esecuzione. Inoltre,
symfony 1.3 introduce due *pannelli di debug* aggiuntivi per le
informazioni sulla vista (`view`) e per il debug della posta (`mail`).

![Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/web_debug_01.png "La web debug toolbar con gli elementi predefiniti in symfony 1.3")

Da symfony 1.2, gli sviluppatori possono creare facilmente i propri
*pannelli di debug* e aggiungerli alla web debug toolbar. In questo
capitolo creeremo alcuni *pannelli di web debug* e giocheremo un po'
con i vari strumenti e con le personalizzazioni disponibili.
Inoltre il plugin [ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
contiene molti pannelli di debug utili e interessanti, che usano
alcune tecniche analizzate in questo capitolo.

Creare un Nuovo Pannello di Debug
---------------------------------

I singoli componenti della debug toolbar sono noti come *pannelli di debug*
e sono classi speciali che estendono la classe ~`sfWebDebugPanel`~.
Creare un nuovo pannello è molto facile. Basta creare un file che si chiama
`sfWebDebugPanelDocumentation.class.php` nella cartella `lib/debug/` del
proprio progetto (la cartella deve essere creata):

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    class acWebDebugPanelDocumentation extends sfWebDebugPanel
    {
      public function getTitle()
      {
        return '<img src="/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
      }

      public function getPanelTitle()
      {
        return 'Documentation';
      }
      
      public function getPanelContent()
      {
        $content = 'Placeholder Panel Content';
        
        return $content;
      }
    }

Come minimo, tutti i pannelli di debug devono implementare i metodi `getTitle()`,
`getPanelTitle()` e `getPanelContent()`.

 * ~`sfWebDebugPanel::getTitle()`~: Stabilisce come il pannello apparirà nella
   toolbar stessa. Come la maggior parte dei pannelli, il nostro pannello
   personalizzato include una piccola icona e un breve nome per il pannello.

 * ~`sfWebDebugPanel::getPanelTitle()`~: Usato come testo del tag `h1` che
   apparirà in cima al contenuto del panello. È anche usato come attributo
   `title` del collegamento dell'icona nella toolbar e, in quanto tale, *non*
   deve includere alcun codice html.

 * ~`sfWebDebugPanel::getPanelContent()`~: Genera il contenuto html grezzo che
   sarà mostrato quando si clicca sull'icona del pannello.

L'ultimo passo che rimane è notificare all'applicazione che si vuole includere
il nuovo pannello nella toolbar. Per farlo, aggiungere un ascoltatore dell'evento
`debug.web.load_panels`, che viene notificato quando la web debug toolbar mette
insieme i potenziali pannelli. Innanzitutto, modificare il file
`config/ProjectConfiguration.class.php` per ascoltare l'evento:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      // ...

      $this->dispatcher->connect('debug.web.load_panels', array(
        'acWebDebugPanelDocumentation',
        'listenToLoadDebugWebPanelEvent'
      ));
    }

Ora, aggiungiamo la funzione ascoltatrice `listenToLoadDebugWebPanelEvent()`
a `acWebDebugPanelDocumentation.class.php`, per poter aggiungere il pannello
alla toolbar:

    [php]
    // lib/debug/sfWebDebugPanelDocumentation.class.php
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->setPanel(
        'documentation',
        new self($event->getSubject())
      );
    }

Ecco fatto! Aggiornare il browser per vedere il risultato immediatamente.

![Web Debug Toolbar](http://www.symfony-project.org/images/more-with-symfony/web_debug_02.png "La web debug toolbar con un nuovo pannello personalizzato")

>**TIP**
>Da symfony 1.3, si può usare un parametro dell'url `sfWebDebugPanel` per
>aprire automaticamente uno specifico pannello al caricamento della pagina.
>Ad esempio, aggiungendo `?sfWebDebugPanel=documentation` alla fine dell'url,
>si aprirà automaticamente il pannello appena aggiunto. Questo può
>risultare molto pratico durante la costruzione di pannelli personalizzati.

I Tre Tipi di Pannelli di Debug
-------------------------------

Dietro le quinte, ci sono tre tipi molto diversi di pannelli di debug.

### Il Tipo di Pannello *Solo-Icona*

Il tipo più elementare di pannello è quello che mostra una icona e un testo
nella toolbar, niente di più. Il classico esempio è il pannello `memory`,
che mostra l'uso della memoria, ma non fa nulla se viene cliccato. Per creare
un pannello *solo-icona*, si imposti semplicemente il pannello per restituire
una stringa vuota. L'unico output prodotto dal pannello proviene dal metodo
`getTitle()`:

    [php]
    public function getTitle()
    {
      $totalMemory = sprintf('%.1f', (memory_get_peak_usage(true) / 1024));

      return '<img src="'.$this->webDebug->getOption('image_root_path').'/memory.png" alt="Memory" /> '.$totalMemory.' KB';
    }

    public function getPanelContent()
    {
      return;
    }

### Il Tipo di Pannello *Collegamento*

Come il pannello *solo-icona*, un pannello *collegamento* non ha contenuto.
Tuttavia, diversamente dal pannello *solo-icona*, il clic sul pannello
*collegamento* porterà ad un url specificato tramite il metodo `getTitleUrl()`
del pannello. Per creare un pannello *collegamento*, si imposti
`getPanelContent()` per restituire una stringa vuota e si aggiunga un metodo
`getTitleUrl()` alla classe.

    [php]
    public function getTitleUrl()
    {
      // link ad un uri esterno
      return 'http://www.symfony-project.org/api/1_3/';

      // o link ad una rotta dell'applicazione
      return url_for('homepage');
    }

    public function getPanelContent()
    {
      return;
    }

### Il Tipo di Pannello *Contenuto*

Il pannello di gran lunga più comune è il pannello *contenuto*. Tale pannello
ha un corpo pieno di contenuto html, che viene mostrato al click del
pannello nella toolbar. Per creare questo tipo di pannello, si faccia in modo
che `getPanelContent()` restituisca qualcosa che non sia una stringa vuota.

Personalizzare il Contenuto del Pannello
----------------------------------------

Ora che abbiamo creato e aggiunto un pannello personalizzato alla toolbar,
l'aggiunta di contenuto può essere fatta facilmente con il metodo
`getPanelContent()`. Symfony fornisce diversi metodi che aiutano a
rendere questo contenuto ricco e usabile.

### ~`sfWebDebugPanel::setStatus()`~

Di default, ogni pannello sulla web debug toolbar viene mostrato con uno sfondo
grigio. Il colore può essere modificato in arancione o rosso, nel caso in cui
si abbia bisogno di richiamare l'attenzione su un contenuto interno al pannello.

![Web Debug Toolbar con Errori](http://www.symfony-project.org/images/more-with-symfony/web_debug_05.png "La web debug toolbar mostra uno stato di errore nei log")

Per cambiare il colore di sfondo del pannello, si usi il metodo `setStatus()`.
Questo metodo accetta una costante `priority` della classe
[sfLogger](http://www.symfony-project.org/api/1_3/sfLogger). In particolare,
ci sono tre diversi livelli di stato, che corrispondono ai tre diversi
colori di sfondo del pannello (grigio, arancione e rosso). Di solito,
il metodo `setStatus()` viene richiamato da dentro il metodo
`getPanelContent()` quando si verifica una condizione che necessita di una
particolare attenzione.

    [php]
    public function getPanelContent()
    {
      // ...

      // imposta lo sfondo grigio (il colore predefinito)
      $this->setStatus(sfLogger::INFO);

      // imposta lo sfondo arancione
      $this->setStatus(sfLogger::WARNING);

      // imposta lo sfondo rosso
      $this->setStatus(sfLogger::ERR);
    }

### ~`sfWebDebugPanel::getToggler()`~

Una delle caratteristiche più comuni tra i pannelli esistenti è il toggler:
un elemento visuale a forma di freccia, che nasconde e mostra un contenitore
di contenuto quando viene cliccato.

![Web Debug Toggler](http://www.symfony-project.org/images/more-with-symfony/web_debug_03.png "Il toggler in azione")

Questa funzionalità può essere usata facilmente in un pannello personalizzato,
tramite la funzione `getToggler()`. Ad esempio, supponiamo di voler mostrare
una lista di contenuti in un pannello:

    [php]
    public function getPanelContent()
    {
      $listContent = '<ul id="debug_documentation_list" style="display: none;">
        <li>List Item 1</li>
        <li>List Item 2</li>
      </ul>';

      $toggler = $this->getToggler('debug_documentation_list', 'Toggle list');

      return sprintf('<h3>Elementi della Lista %s</h3>%s',  $toggler, $listContent);
    }

Il metodo `getToggler` accetta due paramentri: l'`id` del DOM dell'elemento
da mostrare/nascondere e un titolo `title` da attribuire al collegamento.
Occorre creare a mano sia l'attributo `id` che qualsiasi eventuale etichetta
descrittiva (come ad esempio "Elementi della Lista").

### ~`sfWebDebugPanel::getToggleableDebugStack()`~

Simile a `getToggler()`, `getToggleableDebugStack()` mostra una freccia
cliccabile che alterna la visualizzazione di un insieme di contenuti. In
questo caso, l'insieme di contenuti è una stack trace di debug. Questa
funzione è utile se si ha bisogno di mostrare i risultati di un log
di una classe personalizzata. Ad esempio, supponiamo di eseguire un log
personalizzato per una classe chiamata `myCustomClass`:

    [php]
    class myCustomClass
    {
      public function doSomething()
      {
        $dispatcher = sfApplicationConfiguration::getActive()
          ->getEventDispatcher();

        $dispatcher->notify(new sfEvent($this, 'application.log', array(
          'priority' => sfLogger::INFO,
          'Inizio esecuzione di myCustomClass::doSomething()',
        )));
      }
    }

Come esempio, mostriamo una lista di messaggi di log relativi a
`myCustomClass`, completi di stack trace di debug per ciascuno.

    [php]
    public function getPanelContent()
    {
      // recupera tutti i messaggi di log per la richiesta corrente
      $logs = $this->webDebug->getLogger()->getLogs();

      $logList = '';
      foreach ($logs as $log)
      {
        if ($log['type'] == 'myCustomClass')
        {
          $logList .= sprintf('<li>%s %s</li>',
            $log['message'],
            $this->getToggleableDebugStack($log['debug_backtrace'])
          );
        }
      }

      return sprintf('<ul>%s</ul>', $logList);
    }

![Web Debug Debug alternabile](http://www.symfony-project.org/images/more-with-symfony/web_debug_04.png "Il debug alternato della web debug toolbar in azione")

>**NOTE**
>Anche senza creare un pannello personalizzato, i messaggi di log per la
>classe `myCustomClass` sarebbero mostrati nel pannello dei log. Il vantaggio
>qui è semplicemente quello di raccogliere un sottoinsieme di messaggi di
>log in un posto e controllare il loro output.

### ~`sfWebDebugPanel::formatFileLink()`~

Da symfony 1.3 c'è la possibilità di cliccare su nomi di file nella web
debug toolbar per aprirli nel proprio editor di testo preferito. Per
ulteriori informazioni si veda l'articolo
["What's new"](http://www.symfony-project.org/tutorial/1_3/it/whats-new) per
symfony 1.3.

Per attivare questa caratteristica per un particolare percorso di file, si
deve usare `formatFileLink()`. Oltre al file stesso, si può puntare ad una
riga specifica. Ad esempio, il codice seguente punterà alla riga 15 del
file `config/ProjectConfiguration.class.php`:

    [php]
    public function getPanelContent()
    {
      $content = '';

      // ...

      $path = sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $content .= $this->formatFileLink($path, 15, 'Project Configuration');

      return $content;
    }

Sia il secondo parametro (il numero di riga), che il terzo (il testo del
collegamento), sono facoltativi. Se non viene specificato nessun testo per
il collegamento, al suo posto verrà mostrato il percorso del file.

>**NOTE**
>Prima di testare, ci si assicuri di aver configurato la nuova opzione del
>collegamento dei file. Questa opzione può essere impostata tramite la
>chiave `sf_file_link_format` in `settings.yml` oppure tramite l'impostazione
>`file_link_format` in
>[xdebug](http://xdebug.org/docs/stack_trace#file_link_format). Quest'ultimo
>modo assicura che il progetto non sia legato ad uno specifico IDE.

Altri Trucchi per la Web Debug Toolbar
--------------------------------------

Per la maggior parte, la magia dei pannelli personalizzati sarà dentro al
contenuto e alle informazioni che si sceglie di mostrare. Tuttavia, ci sono
alcuni altri trucchi da analizzare.

### Rimuovere i Pannelli Predefiniti

Di default, symfony carica diversi pannelli di debug nella web debug toolbar.
Usando l'evento `debug.web.load_panels`, questi pannelli predefiniti possono
anche essere facilmente rimossi. Si usi la stessa funzione ascoltatrice
dichiarata sopra, ma sostituendone il corpo con la funzione `removePanel()`.
Il codice seguente rimuoverà il pannello `memory` dalla toolbar:

    [php]
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
      $event->getSubject()->removePanel('memory');
    }

### Accedere ai Parametri della Richiesta da un Pannello

Una delle esigenze più comuni dentro un pannello di debug, è quella di
accedere ai parametri della richiesta. Se ad esempio si vogliono
mostrare informazioni sul database riguardo ad un oggetto `Event` del
database, che si basa su un parametro di richiesta `event_id`:

    [php]
    $parameters = $this->webDebug->getOption('request_parameters');
    if (isset($parameters['event_id']))
    {
      $event = Doctrine::getTable('Event')->find($parameters['event_id']);
    }

### Nascondere Dinamicamente un Pannello

A volte un pannello potrebbe non avere alcuna informazione utile da mostrare.
In queste situazioni, si può scegliere di nascondere il pannello. Si supponga,
nel precedente esempio, che il pannello personalizzato non mostri alcuna
informazione, a meno che il parametro di richiesta `event_id` non sia
presente. Per nascondere il pannello, basta non restituire alcun
contenuto dal metodo `getTitle()`:

    [php]
    public function getTitle()
    {
      $parameters = $this->webDebug->getOption('request_parameters');
      if (!isset($parameters['event_id']))
      {
        return;
      }

      return '<img src="/acWebDebugPlugin/images/documentation.png" alt="Documentation Shortcuts" height="16" width="16" /> docs';
    }

Considerazioni Finali
---------------------

La web debug toolbar esiste per rendere più facile la vita dello sviluppatore,
ma è più di un indicatore passivo di informazioni. Aggiungendo dei pannelli
personalizzati, il potenziale della web debug toolbar ha come unico limite
l'immaginazione degli sviluppatori. Il plugin
[ac2009WebDebugPlugin](http://www.symfony-project.org/plugins/ac2009WebDebugPlugin)
include solo alcuni dei pannelli che potrebbero essere creati. Create pure
i vostri.
