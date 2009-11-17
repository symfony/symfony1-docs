Gli ambienti
============

Guardando nella directory `web/`, ci saranno due file PHP:
`index.php` e `frontend_dev.php`. Questi file sono chiamati **front controller**
e vengono utilizzati per gestire tutte le richieste fatte all'applicazione. 
Ma perché avere due front controller per ogni applicazione?

Entrambi i file puntano alla stessa applicazione, ma per **ambienti** differenti.
Quando viene sviluppata un'applicazione, ad eccezione di quelle sviluppate
direttamente sul server di produzione, diversi ambienti sono necessari:

  * L'**ambiente di sviluppo**: Questo ambiente è utilizzato dagli
    **sviluppatori web** quando devono lavorare sull'applicazione per
    aggiungere nuove funzionalità, correggere problemi, etc..
  * L'**ambiente di test**: Questo ambiente è usato per testare
    automaticamente l'applicazione.
  * L'**ambiente di stage**: Questo ambiente è utilizzato dal
    **cliente** per provare l'applicazione e per comunicare eventuali
    problemi o mancanza di funzionalità.
  * L'**ambiente di produzione**: Quest'ultimo ambiente è quello che
    verrà usato dagli **utenti finali**.

Cosa rende ogni ambiente differente? Ad esempio nell'ambiente di sviluppo, 
l'applicazione deve registrare tutti dettagli delle richieste per rendere 
più facile il debug del codice, contemporaneamente deve avere la cache 
disabilitata per permettere di vedere subito i risultati del proprio lavoro. 
In sostanza l'ambiente di sviluppo deve essere ottimizzato per gli sviluppatori.
Il miglior esempio che si può dare è quello delle eccezioni del framework. Per 
aiutare lo sviluppatore a controllare il proprio codice velocemente, symfony 
mostra l'eccezione per la richiesta fatta, con tutte le informazioni ad essa 
correlate, direttamente all'interno del browser:

![Una eccezione nell'ambiente di sviluppo](http://www.symfony-project.org/images/jobeet/1_2/01/exception_dev.png)

Ma nell'ambiente di produzione la cache deve essere attivata e, ovviamente,
l'applicazione deve mostrare i messaggi di errore personalizzati invece
che lo stack delle eccezioni. Quindi, l'ambiente di produzione deve essere
ottimizzato per le prestazioni e per la user experience.

![Una eccezione nell'ambiente di produzione](http://www.symfony-project.org/images/jobeet/1_2/01/exception_prod.png)

>**NOTE**
>Se vengono aperti i file di front controller, si potrà notare che il contenuto 
>degli stessi è uguale ad eccezione dei parametri che definiscono l'ambiente:
>
>     [php]
>     // web/index.php
>     <?php
>
>     require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');
>
>     $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
>     sfContext::createInstance($configuration)->dispatch();

La toolbar di web debug è un altro grande esempio dell'utilizzo degli ambienti. 
Questa è presente in tutte le pagine dell'ambiente di sviluppo e dà accesso a 
molte informazioni cliccando sulle differenti aree, ad esempio: i parametri di configurazione 
dell'attuale applicazione, i log per la richiesta http corrente, le richieste SQL 
eseguite sul database, informazioni sulla memoria e sul tempo di esecuzione.
