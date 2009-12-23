Elementi deprecati e rimossi in 1.3
===================================

Questo documento elenca tutte le impostazioni, le classi, i metodi, le funzioni
ed i task che sono stati deprecati o rimossi in symfony 1.3.

Plugin del Core
---------------

I seguenti plugin del core sono stati deprecati in symfony 1.3 e saranno
rimossi in symfony 1.4:

  * `sfCompat10Plugin`: Deprecando questo plugin, si deprecano anche tutti gli
    altri elementi del framework che si basano su di esso (admin generator 1.0 e
    sistema dei form 1.0). Si include anche il tema predefinito per l'admin
    generator, che si trova in lib/plugins/sfPropelPlugin/data/generator/sfPropelAdmin`.

  * `sfProtoculousPlugin`: Gli helper forniti da questo plugin non supportano
    modalità unobtrusive e quindi non andrebbero più usati.

Metodi e Funzioni
-----------------

I seguenti metodi e funzioni sono stati deprecati in symfony 1.3 o precedenti
e saranno rimossi in symfony 1.4:

  * `sfToolkit::getTmpDir()`: Si possono sostituire tutte le occorrenze di
    questo metodo con `sys_get_temp_dir()`

  * `sfToolkit::removeArrayValueForPath()`,
    `sfToolkit::hasArrayValueForPath()` e `getArrayValueForPathByRef()`

  * `sfValidatorBase::setInvalidMessage()`: Può essere sostituito chiamando il
    nuovo metodo `sfValidatorBase::setDefaultMessage()`

  * `sfValidatorBase::setRequiredMessage()`: Può essere sostituito chiamando il
    nuovo metodo `sfValidatorBase::setDefaultMessage()`

  * `sfTesterResponse::contains()`: Si può usare il metodo `matches()`, più potente

  * I seguenti metodi di `sfTestFunctionalBase`: `isRedirected()`,
    `isStatusCode()`, `responseContains()`, `isRequestParameter()`,
    `isResponseHeader()`, `isUserCulture()`, `isRequestFormat()` e
    `checkResponseElement()`: Questi metodi sono stati deprecati dalla versione
    1.2 e sostituiti con le classi tester.

  * I seguenti metodi di `sfTestFunctional`: `isCached()`, `isUriCached()`: Questi
    metodi sono stati deprecati dalla versione 1.2 e sostituiti con le classi tester.

  * `sfFilesystem::sh()`: Si possono sostituire tutte le occorrenze di
    questo metodo con il nuovo metodo `sfFilesystem::execute()`. Si presti
    attenzione al fatto che il valore restituito da questo metodo è un array
    composto dall'output di `stdout` e da quello di `stderr`.

  * `sfAction::getDefaultView()`, `sfAction::handleError()`,
    `sfAction::validate()`: Questi metodi sono stati deprecati in symfony 1.1 e
    non sono molto utili. Da symfony 1.1, necessitano dell'impostazione
    `compat_10` impostata `on` per funzionare.

  * `sfComponent::debugMessage()`: Usare l'helper `log_message()` in sostituzione.

  * `sfApplicationConfiguration::loadPluginConfig()`: Usare
    `initializePlugins()` in sostituzione.

  * `sfLoader::getHelperDirs()` e `sfLoader::loadHelpers()`: Usare gli stessi
    metodi dell'oggetto `sfApplicationConfiguration`. Essendo tutti i metodi della
    classe `sfLoader` deprecati, la classe `sfLoader` sarà rimossa in symfony 1.4.

  * `sfController::sendEmail()`: Usare il nuovo mailer di symfony 1.3 in sostituzione.

  * `sfGeneratorManager::initialize()`: Non fa nulla.

  * `debug_message()`: Usare l'helper `log_message()` in sostituzione.

  * `sfWebRequest::getMethodName()`: Usare `getMethod()` in sostituzione.

  * `sfDomCssSelector::getTexts()` e `sfDomCssSelector::getElements()`

  * `sfDomCssSelector::getElements()`: Usare `matchAll()`

  * `sfVarLogger::getXDebugStack()`: Usare `sfVarLogger::getDebugBacktrace()`
    in sostituzione.

  * `sfVarLogger`: Il valore loggato `debug_stack` è deprecato a favore del
    valore `debug_backtrace`.

  * `sfContext::retrieveObjects()`: Il metodo è usato solo da ObjectHelper,
    che è deprecato

I seguenti metodi e le seguenti funzioni sono stati rimossi in symfony 1.3:

  * `sfApplicationConfiguration::checkSymfonyVersion()`: si veda più avanti per
    la spiegazione (impostazione `check_symfony_version`)

Classi
------

Le seguenti classi sono state deprecate in symfony 1.3 e saranno rimosse
in symfony 1.4:

  * `sfDoctrineLogger`: Usare `sfDoctrineConnectionProfiler` in sostituzione.

  * `sfNoRouting` e `sfPathInfoRouting`

  * `sfRichTextEditor`, `sfRichTextEditorFCK`, e `sfRichTextEditorTinyMCE`:
    Sono state sostituite dal sistema dei widget (si veda la sezione "Helper"
    più avanti)

  * `sfCrudGenerator`, `sfAdminGenerator`, `sfPropelCrudGenerator`,
    `sfPropelAdminGenerator`: Queste classi erano usate dall'admin
    generator 1.0

  * `sfPropelUniqueValidator`, `sfDoctrineUniqueValidator`: Queste classi erano
    usate dal sistema dei form 1.0

  * `sfLoader`: si veda la sesione "Metodi e Funzioni"

  * `sfConsoleRequest`, `sfConsoleResponse`, `sfConsoleController`

  * `sfDoctrineDataRetriever`, `sfPropelDataRetriever`: Queste classi sono usate
    solo da ObjectHelper, che è deprecato

  * `sfWidgetFormI18nSelectLanguage`, `sfWidgetFormI18nSelectCurrency` e
    `sfWidgetFormI18nSelectCountry`: Usare i corrispondenti widget `Choice`
    (`sfWidgetFormI18nChoiceLanguage`, `sfWidgetFormI18nChoiceCurrency` e
    `sfWidgetFormI18nChoiceCountry` respectively) che si comportano esattamente
    nello stesso modo, ma hanno più possibilità di personalizzazione

  * `sfWidgetFormChoiceMany`, `sfWidgetFormPropelChoiceMany`,
    `sfWidgetFormDoctrineChoiceMany`, `sfValidatorChoiceMany`,
    `sfValidatorPropelChoiceMany`, `sfValidatorPropelDoctrineMany`: Usare le
    stesse classi, ma senza `Many` finale ed impostando l'opzione `multiple` a
    `true`

  * `SfExtensionObjectBuilder`, `SfExtensionPeerBuilder`,
    `SfMultiExtendObjectBuilder`, `SfNestedSetBuilder`,
    `SfNestedSetPeerBuilder`, `SfObjectBuilder`, `SfPeerBuilder`: Le classi
    di build personalizzate di Propel sono state migrate al nuovo sistema di
    comportamenti di Propel 1.4

Le seguenti classi sono state deprecate in symfony 1.3:

  * `sfCommonFilter`: si veda la sezione "Rimozione del filtro common" del file
    UPGRADE_TO_1_3 per maggiori informazioni sulle conseguenze e su come migrare
    il proprio codice.

Helper
------

I seguenti gruppi di helper sono stati deprecati in symfony 1.3 e saranno
rimossi in symfony 1.4:

  * Tutti gli helper relativi al sistema dei form 1.0 forniti da
    `sfCompat10Plugin`: `DateForm`, `Form`, `ObjectAdmin`, `Object` e
    `Validation`

L'helper `form_tag()` del gruppo di helper `Form` è stato spostato nel gruppo
di helper `Url` ed è quindi ancora disponibile in symfony 1.4.

Il caricamento degli helper dal percorso di include di PHP è stato deprecato
nella 1.3 e rimosso nella 1.4. Gli helper devono essere collocati in una
cartella `lib/helper/` del progetto, dell'applicazione o del modulo.

Impostazioni
------------

Le seguenti impostazioni (gestite nel file di configurazione `settings.yml`) sono
state rimosse da symfony 1.3:

  * `check_symfony_version`: Questa impostazione era stata introdotta anni fa per
    consentire la pulizia automatica della cache in caso di cambio di versione di
    symfony. Era utile per configurazioni su host condivisi, in cui la versione
    di symfony era condivisa tra diversi clienti. Essendo una cattiva pratica da
    symfony 1.1 (occorre inserire la versione di symfony in ciascun progetto),
    l'impostazione non ha più senso. Inoltre, quando l'impostazione è impostata
    a `on`, la verifica aggiunge anche un piccolo overhead ad ogni richiesta,
    poiché ha bisogno di recuperare il contenuto di un file.
  
  * `max_forwards`: Questa impostazione controlla il numero di rinvii consentiti
    prima che symfony lanci un'eccezione. La configurabilità non ha valore.
    Se si necessita di più di 5 rinvii, si ha un problema sia di concetto che di
    performance.

  * `sf_lazy_cache_key`: Introdotta come una grossa miglioria alle prestazioni in
    symfony 1.2.6, questa impostazione consentiva di attivare la generazione di
    chiavi pigra per la cache delle viste. Pur ritenendo che il sistema pigro
    fosse una buona idea, alcuni potrebbero aver fatto affidamento sul fatto che
    `sfViewCacheManager::isCacheable()` fosse richiamata anche quando non si
    poteva mettere in cache l'azione. Da symfony 1.3, il comportamento è lo
    stesso, come `sf_lazy_cache_key` fosse impostato a `true`.

  * `strip_comments`: `strip_comments` è stata introdotta per consentire di
    disabilitare l'eliminazione dei commenti a causa di alcuni bug nel
    tokenizer di alcune versioni di PHP 5.0.X. Era usato anche più avanti,
    per evitare un grande consumo di memoria quando l'estensione Tokenizer
    non era compilata con PHP. Il primo problema non è più rilevante, essendo
    la versione minima di PHP richiesta la 5.2, mentre il secondo problema è
    stato già risolto rimuovendo l'espressione regolare che simulava
    l'eliminazione dei commenti.

  * `lazy_routes_deserialize`: Questa opzione non serve più.

Le seguenti opzioni sono state deprecate in symfony 1.3 e saranno rimosse
in symfony 1.4:

  * `calendar_web_dir`, `rich_text_js_dir`: Queste impostazioni erano usate dal
    gruppo di helper Form, che è deprecato in symfony 1.3.

  * `validation_error_prefix`, `validation_error_suffix`,
    `validation_error_class`, `validation_error_id_prefix`: Queste impostazioni
    erano usate dal gruppo di helper Validation, che è deprecato in symfony 1.3.

  * `is_internal` (in `module.yml`): Il flag `is_internal` era usato per
    impedire alle azioni di essere chiamate da browser. Era stato aggiunto
    per proteggere dall'invio di email in symfony 1.0. Poiché il supporto alle
    email non richiede più questo trucco, il flag sarà rimosso e non più
    verificato nel codice base di symfony.

Task
----

I seguenti task sono stati rimossi in symfony 1.3:

  * `project:freeze` e `project:unfreeze`: Questi task servivano ad includere
    la versione di symfony usata dal progetto nel progetto stesso. Non servono
    più, perché la cosa migliore già da tempo è includere symfony nel progetto.
    Inoltre, cambiando versione di symfony è ora molto semplice, dati che basta
    cambiare il percorso nella classe `ProjectConfiguration`. Includere
    symfony a mano è anche molto semplice, poiché basta copiare l'intera
    cartella di symfony da qualche parte nel progetto (si raccomanda di farlo
    in `lib/vendor/symfony/`).

I seguenti task sono deprecati in symfony 1.3 e saranno rimossi in
symfony 1.4:

  * Tutti gli alias dei task di symfony 1.0.

  * `propel:init-admin`: Questo task generava i moduli per l'admin generator
    in symfony 1.0.

I seguenti task di Doctrine sono stati fusi in `doctrine:build` e saranno
rimossi in symfony 1.4:

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

Varie
-----

I seguenti comportamenti sono stati deprecati in symfony 1.3 e saranno rimossi
symfony 1.4:

  * Per i metodi `sfParameterHolder::get()`, `sfParameterHolder::has()`,
    `sfParameterHolder::remove()`, `sfNamespacedParameterHolder::get()`,
    `sfNamespacedParameterHolder::has()` e
    `sfNamespacedParameterHolder::remove()`, il supporto per la notazione
    array (`[]`) è deprecato e non sarà disponibile in symfony 1.4
    (migliora le prestazioni).

L'interfaccia a linea di comandi di symfony non accetta più l'opzione globale
`--dry-run`, non essendo usata da nessun task di symfony. Se uno dei propri
task si basava su tale opzione, la si può aggiungere come opzione locale nella
classe del task.

I template di Propel per l'admin generator 1.0 ed il CRUD 1.0 saranno rimossi
in symfony 1.4 (`plugins/sfPropelPlugin/data/generator/sfPropelAdmin/`).

Il "Dynarch calendar" (che si trova in data/web/calendar/) sarà rimosso in
symfony 1.4, essendo usato solo dal gruppo di helper Form, che sarà a sua volta
rimosso in symfony 1.4.

Da symfony 1.3, la pagina non disponibile sarà cercata sono nelle cartelle
`%SF_APP_CONFIG_DIR%/` e `%SF_CONFIG_DIR%/`. Se la si tiene ancora in
`%SF_WEB_DIR%/errors/`, la si deve spostare prima di migrare a symfony 1.4.

La cartella `doc/` di un progetto non viene più generate, poiché non era usata
da symfony stesso. Anche la relativa variabile `sf_doc_dir` è stata rimossa.

L'impostazione `sfDoctrinePlugin_doctrine_lib_path`, precedentemente usata
per specificare una cartella personalizzata per le librerie di Doctrine, è
stata deprecata nella 1.3 e rimossa nella 1.4. Si usi l'impostazione
`sf_doctrine_dir` al suo posto.

Tutte le classi generate `Base*` non sono contrassegnate come `abstract`.
