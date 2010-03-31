Task
====

Il framework symfony comprende uno strumento per l'interfaccia a riga di comando.
Task già presenti mettono in grado allo sviluppatore di eseguire molti compiti ricorrenti
e fastidiosi nel ciclo di vita di un progetto.

Se si esegue la CLI (Command Line Interface) di `symfony` senza nessun parametro, viene visualizzato
un elenco dei task disponibili:

    $ php symfony

Passando l'opzione `-V`, si ottengono alcune informazioni sulla versione di
symfony e il percorso delle librerie di symfony usate dalla CLI:

    $ php symfony -V

Lo strumento della CLI accetta il nome di un task come primo parametro:

    $ php symfony list

Il nome di un task può essere formato da uno spazionomi opzionale e un nome, separato da
due punti (`:`):

    $ php symfony cache:clear

Dopo il nome del task, possono essere passati parametri e opzioni:

    $ php symfony cache:clear --type=template

Lo strumento della CLI supporta sia le opzioni lunghe che quelle corte, con o senza
valori.

L'opzione `-t` è un'opzione globale per chiedere a qualunque task di mostrare più informazioni
per il debug.

<div class="pagebreak"></div>

I task disponibili
------------------

 * Task globali
   * [`help`](#chapter_16_sub_help)
   * [`list`](#chapter_16_sub_list)
 * [`app`](#chapter_16_app)
   * [`app::routes`](#chapter_16_sub_app_routes)
 * [`cache`](#chapter_16_cache)
   * [`cache::clear`](#chapter_16_sub_cache_clear)
 * [`configure`](#chapter_16_configure)
   * [`configure::author`](#chapter_16_sub_configure_author)
   * [`configure::database`](#chapter_16_sub_configure_database)
 * [`doctrine`](#chapter_16_doctrine)
   * [`doctrine::build`](#chapter_16_sub_doctrine_build)
   * [`doctrine::build-all`](#chapter_16_sub_doctrine_build_all)
   * [`doctrine::build-all-load`](#chapter_16_sub_doctrine_build_all_load)
   * [`doctrine::build-all-reload`](#chapter_16_sub_doctrine_build_all_reload)
   * [`doctrine::build-all-reload-test-all`](#chapter_16_sub_doctrine_build_all_reload_test_all)
   * [`doctrine::build-db`](#chapter_16_sub_doctrine_build_db)
   * [`doctrine::build-filters`](#chapter_16_sub_doctrine_build_filters)
   * [`doctrine::build-forms`](#chapter_16_sub_doctrine_build_forms)
   * [`doctrine::build-model`](#chapter_16_sub_doctrine_build_model)
   * [`doctrine::build-schema`](#chapter_16_sub_doctrine_build_schema)
   * [`doctrine::build-sql`](#chapter_16_sub_doctrine_build_sql)
   * [`doctrine::clean-model-files`](#chapter_16_sub_doctrine_clean_model_files)
   * [`doctrine::create-model-tables`](#chapter_16_sub_doctrine_create_model_tables)
   * [`doctrine::data-dump`](#chapter_16_sub_doctrine_data_dump)
   * [`doctrine::data-load`](#chapter_16_sub_doctrine_data_load)
   * [`doctrine::delete-model-files`](#chapter_16_sub_doctrine_delete_model_files)
   * [`doctrine::dql`](#chapter_16_sub_doctrine_dql)
   * [`doctrine::drop-db`](#chapter_16_sub_doctrine_drop_db)
   * [`doctrine::generate-admin`](#chapter_16_sub_doctrine_generate_admin)
   * [`doctrine::generate-migration`](#chapter_16_sub_doctrine_generate_migration)
   * [`doctrine::generate-migrations-db`](#chapter_16_sub_doctrine_generate_migrations_db)
   * [`doctrine::generate-migrations-diff`](#chapter_16_sub_doctrine_generate_migrations_diff)
   * [`doctrine::generate-migrations-models`](#chapter_16_sub_doctrine_generate_migrations_models)
   * [`doctrine::generate-module`](#chapter_16_sub_doctrine_generate_module)
   * [`doctrine::generate-module-for-route`](#chapter_16_sub_doctrine_generate_module_for_route)
   * [`doctrine::insert-sql`](#chapter_16_sub_doctrine_insert_sql)
   * [`doctrine::migrate`](#chapter_16_sub_doctrine_migrate)
   * [`doctrine::rebuild-db`](#chapter_16_sub_doctrine_rebuild_db)
   * [`doctrine::reload-data`](#chapter_16_sub_doctrine_reload_data)
 * [`generate`](#chapter_16_generate)
   * [`generate::app`](#chapter_16_sub_generate_app)
   * [`generate::module`](#chapter_16_sub_generate_module)
   * [`generate::project`](#chapter_16_sub_generate_project)
   * [`generate::task`](#chapter_16_sub_generate_task)
 * [`i18n`](#chapter_16_i18n)
   * [`i18n::extract`](#chapter_16_sub_i18n_extract)
   * [`i18n::find`](#chapter_16_sub_i18n_find)
 * [`log`](#chapter_16_log)
   * [`log::clear`](#chapter_16_sub_log_clear)
   * [`log::rotate`](#chapter_16_sub_log_rotate)
 * [`plugin`](#chapter_16_plugin)
   * [`plugin::add-channel`](#chapter_16_sub_plugin_add_channel)
   * [`plugin::install`](#chapter_16_sub_plugin_install)
   * [`plugin::list`](#chapter_16_sub_plugin_list)
   * [`plugin::publish-assets`](#chapter_16_sub_plugin_publish_assets)
   * [`plugin::uninstall`](#chapter_16_sub_plugin_uninstall)
   * [`plugin::upgrade`](#chapter_16_sub_plugin_upgrade)
 * [`project`](#chapter_16_project)
   * [`project::clear-controllers`](#chapter_16_sub_project_clear_controllers)
   * [`project::deploy`](#chapter_16_sub_project_deploy)
   * [`project::disable`](#chapter_16_sub_project_disable)
   * [`project::enable`](#chapter_16_sub_project_enable)
   * [`project::optimize`](#chapter_16_sub_project_optimize)
   * [`project::permissions`](#chapter_16_sub_project_permissions)
   * [`project::send-emails`](#chapter_16_sub_project_send_emails)
   * [`project::upgrade1.3`](#chapter_16_sub_project_upgrade1_3)
   * [`project::validate`](#chapter_16_sub_project_validate)
 * [`propel`](#chapter_16_propel)
   * [`propel::build`](#chapter_16_sub_propel_build)
   * [`propel::build-all`](#chapter_16_sub_propel_build_all)
   * [`propel::build-all-load`](#chapter_16_sub_propel_build_all_load)
   * [`propel::build-filters`](#chapter_16_sub_propel_build_filters)
   * [`propel::build-forms`](#chapter_16_sub_propel_build_forms)
   * [`propel::build-model`](#chapter_16_sub_propel_build_model)
   * [`propel::build-schema`](#chapter_16_sub_propel_build_schema)
   * [`propel::build-sql`](#chapter_16_sub_propel_build_sql)
   * [`propel::data-dump`](#chapter_16_sub_propel_data_dump)
   * [`propel::data-load`](#chapter_16_sub_propel_data_load)
   * [`propel::generate-admin`](#chapter_16_sub_propel_generate_admin)
   * [`propel::generate-module`](#chapter_16_sub_propel_generate_module)
   * [`propel::generate-module-for-route`](#chapter_16_sub_propel_generate_module_for_route)
   * [`propel::graphviz`](#chapter_16_sub_propel_graphviz)
   * [`propel::init-admin`](#chapter_16_sub_propel_init_admin)
   * [`propel::insert-sql`](#chapter_16_sub_propel_insert_sql)
   * [`propel::schema-to-xml`](#chapter_16_sub_propel_schema_to_xml)
   * [`propel::schema-to-yml`](#chapter_16_sub_propel_schema_to_yml)
 * [`symfony`](#chapter_16_symfony)
   * [`symfony::test`](#chapter_16_sub_symfony_test)
 * [`test`](#chapter_16_test)
   * [`test::all`](#chapter_16_sub_test_all)
   * [`test::coverage`](#chapter_16_sub_test_coverage)
   * [`test::functional`](#chapter_16_sub_test_functional)
   * [`test::unit`](#chapter_16_sub_test_unit)


<div class="pagebreak"></div>

### ~`help`~

Il task `help` mostra l'aiuto per un task:

    $ php symfony help [--xml] [task_name]

*Alias*: `h`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome_task` | `aiuto` | Il nome del task


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--xml`               | `-`         | Mostra l'aiuto come XML


Il task `help` mostra l'aiuto per un dato task:

    ./symfony help test:all

È anche possibile visualizzare l'aiuto in formato XML utilizzando l'opzione `--xml`:

    ./symfony help test:all --xml

### ~`list`~

Il task `list` elenca i task:

    $ php symfony list [--xml] [namespace]



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `spazionomi` | `-` | Il nome dello spazionomi


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--xml`               | `-`         | Mostra l'aiuto come XML


Il task `list` elenca tutti i task:

    ./symfony list

È anche possibile visualizzare i task per uno spazionomi specifico:

    ./symfony list test

È anche possibile visualizzare l'informazione in XML utilizzando l'opzione `--xml`:

    ./symfony list --xml

`app`
-----

### ~`app::routes`~

Il task `app::routes` visualizza le rotte correnti per una applicazione:

    $ php symfony app:routes  applicazione [nome]



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `nome` | `-` | Il nome di una rotta




`app:routes` visualizza le rotte correnti per una data applicazione:

    ./symfony app:routes frontend

`cache`
-------

### ~`cache::clear`~

Il task `cache::clear` pulisce la cache:

    $ php symfony cache:clear [--app[="..."]] [--env[="..."]] [--type[="..."]] 

*Alias*: `cc, clear-cache`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--app` | `-` | Il nome dell'applicazione
| `--env` | `-` | L'ambiente
| `--type` | `all` | Il tipo


Il task `cache:clear` pulisce la cache di symfony.

Per impostazione predefinita, rimuove la cache per tutti i tipi disponibili, tutte le applicazioni
e tutti gli ambienti.

È possibile restringere per tipo, applicazione o ambiente:

Per esempio, per pulire la cache dell'applicazione `frontend`:

    ./symfony cache:clear --app=frontend

Per pulire la cache nell'ambiente `prod` per l'applicazione `frontend`:

    ./symfony cache:clear --app=frontend --env=prod

Per pulire la cache per tutti gli ambienti `prod`:

    ./symfony cache:clear --env=prod

Per pulire la cache `config` per tutti gli ambienti `prod`:

    ./symfony cache:clear --type=config --env=prod

I tipi predefiniti sono: `config`, `i18n`, `routing`, `module`
e `template`.


`configure`
-----------

### ~`configure::author`~

Il task `configure::author` configura l'autore del progetto:

    $ php symfony configure:author  autore



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `autore` | `-` | L'autore del progetto




Il task `configure:author` configura l'autore per un progetto:

    ./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"

L'autore è usato per pre-configurare l'intestazione dei PHPDoc per ciascun file generato.

Il valore è memorizzato in [config/properties.ini].

### ~`configure::database`~

Il task `configure::database` configura il DSN del database:

    $ php symfony configure:database [--env[="..."]] [--name[="..."]] [--class[="..."]] [--app[="..."]] dsn [nomeutente] [password]



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `dsn` | `-` | Il dsn del database
| `nomeutente` | `root` | Il nome utente per il database
| `password` | `-` | La password per il database


| Opzione (Abbreviazione) | Predefinito | Descrizione
| ----------------------- | ----------- | -----------
| `--env` | `all` | L'ambiente
| `--name` | `propel` | Il nome della connessione
| `--class` | `sfPropelDatabase` | Il nome della classe per il database
| `--app` | `-` | Il nome dell'applicazione


Il task `configure:database` configura il DSN del database
per un progetto:

    ./symfony configure:database mysql:host=localhost;dbname=example root mYsEcret

Per impostazione predefinita, il task cambia la configurazione per tutti gli ambienti. Se si vuole
cambiare il dsn per un ambiente specifico, usare l'opzione `env`:

    ./symfony configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret

Per cambiare la configurazione di una specifica applicazione, usare l'opzione `app`:

    ./symfony configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret

È anche possibile specificare il nome della connessione e il nome della classe per il database:

    ./symfony configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret

ATTENZIONE: Il file `propel.ini` è aggiornato anche quando si utilizza un database `Propel`
e si configura con `all` per gli ambienti, senza l'opzione `app`.

`doctrine`
----------

### ~`doctrine::build`~

Il task `doctrine::build` genera il codice basato sullo schema:

    $ php symfony doctrine:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-migrate] [--and-load[="..."]] [--and-append[="..."]] 





| Opzione (Abbreviazione) | Predefinito | Descrizione
| ----------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Per forzare  l'eliminazione del database
| `--all` | `-` | Crea tutto e reinizializza il database
| `--all-classes` | `-` | Crea tutte le classi
| `--model` | `-` | Crea le classi dei modelli
| `--forms` | `-` | Crea le classi dei form
| `--filters` | `-` | Crea le classi dei filtri
| `--sql` | `-` | Crea l'SQL
| `--db` | `-` | Cancella, crea e inserisce l'SQL o migra il database
| `--and-migrate` | `-` | Migra il database
| `--and-load` | `-` | Carica i dati delle fixture (sono consentiti più valori)
| `--and-append` | `-` | Appende i dati delle fixture (sono consentiti più valori)


Il task `doctrine:build` genera il codice basato sullo schema:

    ./symfony doctrine:build

È necessario specificare cosa si vuole creare. Per esempio, se si vogliono
creare le classi dei modelli e dei form, usare le opzioni `--model` e `--forms`:

    ./symfony doctrine:build --model --forms

È possibile usare l'opzione abbreviata `--all` se si vogliono generare tutte le classi e
i file SQL e ricreare il database:

    ./symfony doctrine:build --all

Questo è equivalente a lanciare i seguenti task:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:build-sql
    ./symfony doctrine:insert-sql

È inoltre possibile generare solo i file delle classi usando l'opzione abbreviata
`--all-classes`. Quando questa opzione è usata da sola, il database non sarà modificato.

    ./symfony doctrine:build --all-classes

L'opzione `--and-migrate` eseguirà le migrazioni in attesa, una volta che la creazione
è terminata:

    ./symfony doctrine:build --db --and-migrate

L'opzione `--and-load` caricherà i dati del progetto e del plugin dalle
cartelle `data/fixtures/`:

    ./symfony doctrine:build --db --and-migrate --and-load

Per specificare quali fixture caricare, aggiungere un parametro all'opzione `--and-load`:

    ./symfony doctrine:build --all --and-load="data/fixtures/dev/"

Per appendere i dati delle fixture senza cancellare nessun record dal database, includere
l'opzione `--and-append`:

    ./symfony doctrine:build --all --and-append

### ~`doctrine::build-all`~

Il task `doctrine::build-all` genera i modelli per Doctrine, l'SQL e inizializza il database:

    $ php symfony doctrine:build-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] 

*Alias*: `doctrine-build-all`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Non chiedere conferma
| `--skip-forms`<br />`(-F)` | `-` | Salta la generazione dei form
| `--migrate` | `-` | Migra invece di reinizializzare il database


Il task `doctrine:build-all` è una scorciatoia per quattro altri task:

    ./symfony doctrine:build-all

Il task è equivalente a:

    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:insert-sql

Vedere l'aiuto relativo a questi tre task per maggiori informazioni.

Per saltare la conferma, si può passare l'opzione
`no-confirmation`:

    ./symfony doctrine:buil-all-load --no-confirmation

Utilizzare l'opzione `--migrate` se si vogliono eseguire le migrazioni
del progetto piuttosto che l'inserimento dell'SQL di Doctrine.

    ./symfony doctrine:build-all --migrate

Questo è equivalente a:

    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:migrate

### ~`doctrine::build-all-load`~

Il task `doctrine::build-all-load` genera i modelli per Doctrine, l'SQL, inizializza il database e carica i dati con le fixture:

    $ php symfony doctrine:build-all-load [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*Alias*: `doctrine-build-all-load`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Non chiede conferma
| `--skip-forms`<br />`(-F)` | `-` | Saltare la generazione dei form
| `--migrate` | `-` | Migra invece di reinizializzare il database
| `--dir` | `-` | Le cartelle in cui guardare per le fixture (sono ammessi più valori)
| `--append` | `-` | Non cancella i dati presenti nel database


Il task `doctrine:build-all-load` è una scorciatoia per sette altri task:

    ./symfony doctrine:build-all-load

Il task è equivalente a:

    ./symfony doctrine:build-all
    ./symfony doctrine:data-load

Il task accetta un parametro application a causa del task
`doctrine:data-load`. Vedere la pagina di aiuto di `doctrine:data-load` per maggiori informazioni.

Per saltare la conferma, è possibile passare l'opzione
`no-confirmation`:

    ./symfony doctrine:build-all-load --no-confirmation

Aggiungere l'opzione `--migrate` se sul progetto si vuole lanciare la migrazione
invece dell'inserimento dell'SQL di Doctrine.

    ./symfony doctrine:build-all-load --migrate

### ~`doctrine::build-all-reload`~

Il task `doctrine::build-all-reload` genera i modelli per Doctrine, l'SQL, inizializza il database e carica i dati:

    $ php symfony doctrine:build-all-reload [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*Alias*: `doctrine-build-all-reload`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Non chiedere conferma
| `--skip-forms`<br />`(-F)` | `-` | Saltare la generazione dei form
| `--migrate` | `-` | Migra invece di reinizializzare il database
| `--dir` | `-` | Le cartelle in cui guardare per le fixture (sono ammessi più valori)
| `--append` | `-` | Non cancella i dati presenti nel database


Il task `doctrine:build-all-reload` è una scorciatoia per otto altri task:

    ./symfony doctrine:build-all-reload

Il task è equivalente a:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load

Aggiungere l'opzione `--migrate` se sul progetto si vuole lanciare la migrazione
invece dell'inserimento dell'SQL di Doctrine.

    ./symfony doctrine:build-all-reload --migrate

### ~`doctrine::build-all-reload-test-all`~

Il task `doctrine::build-all-reload-test-all` genera modelli per Doctrine, l'SQL, inizializza il database, carica i datie lancia tutti i test:

    $ php symfony doctrine:build-all-reload-test-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append]

*Alias*: `doctrine-build-all-reload-test-all`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Do not ask for confirmation
| `--skip-forms`<br />`(-F)` | `-` | Salta la creazione dei form
| `--migrate` | `-` | Migra invece di reinizializzare il database
| `--dir` | `-` | Le cartelle dove cercare le fixture (sono ammessi più valori)
| `--append` | `-` | Non cancella i dati presenti nel database


Il task `doctrine:build-all-reload-test-all` è una scorciatoia per nove altri task:

    ./symfony doctrine:build-all-reload-test-all frontend

Il task è equivalente a:
  
    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load
    ./symfony test-all

Il task accetta una applicazione come parametro per via del task
`doctrine:data-load`. Vedere la pagina di aiuto di `doctrine:data-load` per maggiori informazioni.

Aggiungere l'opzione `--migrate` se sul progetto si vuole lanciare la migrazione
invece dell'inserimento dell'SQL di Doctrine.

    ./symfony doctrine:build-all-reload-test-all --migrate

### ~`doctrine::build-db`~

Il task `doctrine::build-db` crea il database per il modello corrente:

    $ php symfony doctrine:build-db [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-build-db`, doctrine:create-db`

| Parametro | Predefinito | Descrizione
| -------- | ------- | -----------
| `database` | `-` | Uno specifico database


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:build-db` crea uno o più database, a seconda della
configurazione presente in `config/databases.yml`:

    ./symfony doctrine:build-db

Si può specificare quali database creare, fornendo i loro nomi:

    ./symfony doctrine:build-db slave1 slave2

### ~`doctrine::build-filters`~

Il task `doctrine::build-filters` crea le classi per il filtro dei form per il corrente modello:

    $ php symfony doctrine:build-filters [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--filter-dir-name="..."] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--model-dir-name` | `model` | Il nome della cartella per il modello
| `--filter-dir-name` | `filter` | Il nome della cartella per il filtro del form
| `--generator-class` | `sfDoctrineFormFilterGenerator` | La classe generatrice


Il task `doctrine:build-filters` crea le classi per i filtri del form dallo schema:

    ./symfony doctrine:build-filters

Questo task crea le classi dei filtri in base al modello. Le classi sono create in
`lib/doctrine/filter`.

Questo task non sovrascrive mai le classi personalizzate presenti in `lib/doctrine/filter`.
Sostituisce solo le classi base generate in `lib/doctrine/filter/base`.

### ~`doctrine::build-forms`~

Il task `doctrine::build-forms` crea le classi dei form per il corrente modello:

    $ php symfony doctrine:build-forms [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--form-dir-name="..."]  





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--model-dir-name` | `model` | Il nome della cartella per il modello
| `--form-dir-name` | `form` | Il nome della cartella per il filtro del form
| `--generator-class` | `sfDoctrineFormGenerator` | La classe generatrice


Il task `doctrine:build-forms` crea le classi per i form dallo schema:

    ./symfony doctrine:build-forms

Questo task crea le classi dei form in base al modello. Le classi sono create in
`lib/doctrine/form`.

Questo task non sovrascrive mai le classi personalizzate presenti in `lib/doctrine/form`.
Sostituisce unicamente le classi base generate in `lib/doctrine/form/base`.

### ~`doctrine::build-model`~

Il task `doctrine::build-model` crea le classi per il corrente modello:

    $ php symfony doctrine:build-model [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-build-model`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome della connessione
| `--env` | `dev` | L'ambiente


Il task `doctrine:build-model` crea le classi per il modello dallo schema:

    ./symfony doctrine:build-model

Il task legge le informazioni dello schema dal progetto in `config/doctrine/*.yml`
e da tutti i plugin abilitati.

I file con le classi dei modelli sono create in `lib/model/doctrine`.

Questo task non sovrascrive le classi personalizzate presenti in `lib/model/doctrine`.
Sostituisce unicamente i file in `lib/model/doctrine/base`.

### ~`doctrine::build-schema`~

Il task `doctrine::build-schema` crea uno schema da un database esistente:

    $ php symfony doctrine:build-schema [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-build-schema`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:build-schema` esamina un database per creare uno schema:

    ./symfony doctrine:build-schema

Il task crea un file yml in `config/doctrine`

### ~`doctrine::build-sql`~

Il task `doctrine::build-sql` crea l'SQL per il modello corrente:

    $ php symfony doctrine:build-sql [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-build-sql`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:build-sql` crea il codice SQL per la creazione delle tabelle:

    ./symfony doctrine:build-sql

L'SQL generato è ottimizzato per il database configurato in `config/databases.yml`:

    doctrine.database = mysql

### ~`doctrine::clean-model-files`~

Il task `doctrine::clean-model-files` cancella tutte le classi dei modelli generate,
per i modelli che non esistono più nello schema YAML:

    $ php symfony doctrine:clean-model-files [--no-confirmation] 

*Alias(es)*: `doctrine:clean`


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--no-confirmation` | `-` | Non chiede la conferma


Il task `doctrine:clean-model-files` cancella le classi dei modelli che non sono
presenti nei file schema.yml del progetto o dei plugin:

    ./symfony doctrine:clean-model-files

### ~`doctrine::create-model-tables`~

Il task `doctrine::create-model-tables` cancella e ricrea le tabelle per i modelli specificati:

    $ php symfony doctrine:create-model-tables [--application[="..."]] [--env="..."] [modello1] ... [modelloN]



| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `models`  | `-`         | L'elenco dei modelli


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `frontend` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


`doctrine:create-model-tables` cancella e ricrea le tabelle per i modelli specificati:

    ./symfony doctrine:create-model-tables User

### ~`doctrine::data-dump`~

Il task `doctrine::data-dump` copia i dati nella cartella delle fixture:

    $ php symfony doctrine:data-dump [--application[="..."]] [--env="..."] [target]

*Alias*: `doctrine-dump-data`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `target` | `-` | Il nome file di destinazione


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:data-dump` salva i dati del database:

    ./symfony doctrine:data-dump

Il task salva i dati del database in `data/fixtures/%target%`.

Il file salvato è in formato YML e può essere reimportato usando
il task `doctrine:data-load`.

    ./symfony doctrine:data-load frontend

### ~`doctrine::data-load`~

Il task `doctrine::data-load` carica i dati delle fixture YAML:

    $ php symfony doctrine:data-load [--application[="..."]] [--env="..."] [--append] [cartella_o_file1] ... [cartella_o_fileN] 

*Alias*: `doctrine-load-data`

| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `dir_or_file` | `-` | Cartelal o file da caricare


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--append` | `-` | Non cancella i dati presenti nel database


Il task `doctrine:data-load` carica i dati delle fixture nel database:

    ./symfony doctrine:data-load

Il task carica dati da tutti i file trovati in `data/fixtures/`.

Se si vuole caricare dati da file specifici 0 cartelle, si può aggiungerle
come argomento:

    ./symfony doctrine:data-load data/fixtures/dev data/fixtures/users.yml

Se non si vuole che il task rimuova i dati esistenti nel database,
usare l'opzione `--append`:

    ./symfony doctrine:data-load --append

### ~`doctrine::delete-model-files`~

Il task `doctrine::delete-model-files` cancella tutti i relativi file generati automaticamente
per un determinato nome del modello:

    $ php symfony doctrine:delete-model-files [--no-confirmation] name1 ... [nameN]


| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `name` | `-` | Il nome del modello per cui si desidera cancellare tutti i file correlati


| Opzione (Scorciatoia) | Predefinito | Description
| --------------------- | ----------- | -----------Descrizione
| `--no-confirmation` | `-` | Non chiede la conferma


Il task `doctrine:delete-model-files` cancella tutti i file associati
con alcuni modelli:

    ./symfony doctrine:delete-model-files Article Author

### ~`doctrine::dql`~

Il task `doctrine::dql` esegue una query DQL e visualizza i risultati:

    $ php symfony doctrine:dql [--application[="..."]] [--env="..."] [--show-sql] [--table] dql_query [parameter1] ... [parameterN]

*Alias*: `doctrine-dql`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `dql_query` | `-` | La query DQL da eseguire
| `parameter` | `-` | Parametro della query


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--show-sql` | `-` | Mostra l'sql che dovrebbe essere eseguito
| `--table` | `-` | Restisuice i risultati in formato tabulare


Il task `doctrine:dql` esegue una query DQL e visualizza i risultati formattati:

    ./symfony doctrine:dql "FROM User"

Si può visualizzare l'SQL che dovrebbe essere eseguito, utilizzando l'opzione `--dir`:

    ./symfony doctrine:dql --show-sql "FROM User u"

Fornire parametri alla query come parametri addizionali:

    ./symfony doctrine:dql "FROM User WHERE email LIKE ?" "%symfony-project.com"

### ~`doctrine::drop-db`~

Il task `doctrine::drop-db` elimina il database per il modello corrente:

    $ php symfony doctrine:drop-db [--application[="..."]] [--env="..."] [--no-confirmation] 

*Alias*: `doctrine-drop-db`

| Parametro | Predefinito | Descrizione
| -------- | ------- | -----------
| `database` | `-` | Uno specifico database


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Per forzare l'eliminazione del database


Il task `doctrine:drop-db` elimina uno o più database, a seconda
della configurazione in `config/databases.yml`:

    ./symfony doctrine:drop-db

Sarà chiesta conferma prima di ogni cancellazione, a meno di non usare
l'opzione `--no-confirmation`:

    ./symfony doctrine:drop-db --no-confirmation

Si possono specificare i database da cancellare, fornendo i loro nomi:

    ./symfony doctrine:drop-db slave1 slave2


### ~`doctrine::generate-admin`~

Il task `doctrine::generate-admin` genera un modulo di Doctrine per l'admin:

    $ php symfony doctrine:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `application` | `-` | Il nome dell'applicazione
| `route_or_model` | `-` | Il nome della rotta o della classe per il modello


| Opzione (Scorciatoia) | Predefinito | Descrizione
| ----------------- | ------- | -----------
| `--module` | `-` | Il nome del modulo
| `--theme` | `admin` | Il nome del tema
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classe base per le azioni


Il task `doctrine:generate-admin` genera un modulo di Doctrine per l'admin:

    ./symfony doctrine:generate-admin frontend Article

Il task crea un modulo nell'applicazione `%frontend%` per il
modello `%Article%`.

Il task crea una rotta nel file `routing.yml` dell'applicazione.

È possibile anche generare un modulo admin di Doctrine passando un nome di rotta:

    ./symfony doctrine:generate-admin frontend article

Il task crea un modulo nell'applicazione `%frontend%` per la
definizione di rotta `%article%` trovata in `routing.yml`.

Per fare funzionare correttamente i filtri e le azioni batch, è necessario aggiungere
l'opzione `with_wildcard_routes` alla rotta:

    article:
      class: sfDoctrineRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`doctrine::generate-migration`~

Il task `doctrine::generate-migration` genera la classe di migrazione:

    $ php symfony doctrine:generate-migration [--application[="..."]] [--env="..."] name

*Alias*: `doctrine-generate-migration`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `name` | `-` | Il nome della migrazione


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--editor-cmd` | `-` | Apre lo script con questo comando al momento della creazione


Il task `doctrine:generate-migration` genera il modello di migrazione

    ./symfony doctrine:generate-migration AddUserEmailColumn

È possibile aggiungere l'opzione `--editor-cmd` per aprire la nuova classe di migrazione
nell'editor preferito al momento della creazione:

    ./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

### ~`doctrine::generate-migrations-db`~

Il task `doctrine::generate-migrations-db` genera le classi di migrazione per le esistenti connessioni al database:

    $ php symfony doctrine:generate-migrations-db [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-generate-migrations-db, doctrine-gen-migrations-from-db`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:generate-migration-db` genera classi di migrazione dalle
connessioni al database esistenti:

    ./symfony doctrine:generate-migration-db

### ~`doctrine::generate-migrations-diff`~

Il task `doctrine::generate-migrations-diff` genera classi di migrazione realizzando
una differenza tra il vecchio e il nuovo schema.:

    $ php symfony doctrine:generate-migrations-diff [--application[="..."]] [--env="..."] 




| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:generate-migrations-diff` genera classi di migrazione realizzando
una differenza tra il vecchio e il nuovo schema.

    ./symfony doctrine:generate-migrations-diff

### ~`doctrine::generate-migrations-models`~

Il task `doctrine::generate-migrations-models` genera classi di migrazione da un esistente insieme di modelli:

    $ php symfony doctrine:generate-migrations-models [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-generate-migrations-models, doctrine-gen-migrations-from-models`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:generate-migration-models` genera classi di migrazione
da un esistente insieme di modelli

    ./symfony doctrine:generate-migration

### ~`doctrine::generate-module`~

Il task `doctrine::generate-module` genera un modulo di Doctrine:

    $ php symfony doctrine:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-doctrine-route] [--env="..."] [--actions-base-class="..."] application module model

*Alias*: `doctrine-generate-crud, doctrine:generate-crud`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `application` | `-` | Il nome dell'applicazione
| `module` | `-` | Il nome del modulo
| `model` | `-` | Il nome della classe per il modello


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--theme` | `default` | Il nome del tema
| `--generate-in-cache` | `-` | Generare il modulo in cache
| `--non-verbose-templates` | `-` | Generare modelli non verbosi
| `--with-show` | `-` | Generare un metodo show 
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--route-prefix` | `-` | Il prefisso della rotta
| `--with-doctrine-route` | `-` | Se si userà una rotta di Doctrine
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classe base per le azioni


Il task `doctrine:generate-module` genera un modulo Doctrine:

    ./symfony doctrine:generate-module frontend article Article

Il task crea un modulo `%module%` nell'applicazione `%application%`
per la classe del modello `%model%`.

Si può anche creare un modulo vuoto che eredita le azioni e i modelli da
un modulo generato "al volo" in `%sf_app_cache_dir%/modules/auto%module%`
utilizzando l'opzione `--generate-in-cache`:

    ./symfony doctrine:generate-module --generate-in-cache frontend article Article

Il generatore può utilizzare un tema personalizzato utilizzando l'opzione `--theme`:

    ./symfony doctrine:generate-module --theme="custom" frontend article Article

In questo modo, è possibile creare il proprio generatore di moduli con le proprie convenzioni.

Si può anche cambiare la classe base predefinita per le azioni (predefinita come sfActions) dei
moduli generati:

    ./symfony doctrine:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`doctrine::generate-module-for-route`~

Il task `doctrine::generate-module-for-route` genera  un modulo Doctrine per una definizione di rotta:

    $ php symfony doctrine:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `application` | `-` | Il nome dell'applicazione
| `route` | `-` | Il nome della rotta


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--theme` | `default` | Il nome del tema
| `--non-verbose-templates` | `-` | Generare modelli non verbosi
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classe base per le azioni


Il task `doctrine:generate-module-for-route` genera un modulo Doctrine per una definizione di rotta:

    ./symfony doctrine:generate-module-for-route frontend article

Il task crea un modulo nell'applicazione `%frontend%`
per la definizione di rotta `%article%` trovata in `routing.yml`.

### ~`doctrine::insert-sql`~

Il task `doctrine::insert-sql` inserisce SQL per il modello corrente:

    $ php symfony doctrine:insert-sql [--application[="..."]] [--env="..."] 

*Alias*: `doctrine-insert-sql`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente


Il task `doctrine:insert-sql` crea le tabelle del database:

    ./symfony doctrine:insert-sql

Il task si collega al database e crea le tabelle per tutti i
file `lib/model/doctrine/*.class.php`.

### ~`doctrine::migrate`~

Il task `doctrine::migrate` esegue la migrazione del database alla versione corrente/specificata

    $ php symfony doctrine:migrate [--application[="..."]] [--env="..."] [--up] [--down] [--dry-run] [version]

*Altro nomi*: `doctrine-migrate`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `version` | `-` | La versione verso cui migrare


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--up` | `-` | Migra avanti di una versione
| `--down` | `-` | Migra indietro di una versione
| `--dry-run` | `-` | Non persistono le migrazioni


Il task `doctrine:migrate` migra il database:

    ./symfony doctrine:migrate

Fornire un numero di versione per migrare a una versione specifica:

    ./symfony doctrine:migrate 10

Per migrare avanti e indietro di una versione, usare le opzioni `--up` o `--down`:

    ./symfony doctrine:migrate --down

Se il database supporta il rollback di istruzioni DDL, è possibile eseguire le migrazioni
in modalità dry-run usando l'opzione `--dry-run`:

    ./symfony doctrine:migrate --dry-run

### ~`doctrine::rebuild-db`~

Il task `doctrine::rebuild-db` crea il database per il modello corrente:

    $ php symfony doctrine:rebuild-db [--application[="..."]] [--env="..."] [--no-confirmation] [--migrate] 

*Alias*: `doctrine-rebuild-db`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Per non chiedere conferma alla eliminazione del database
| `--migrate` | `-` | Migra invece di reinizializzare il database


Il task `doctrine:rebuild-db` crea il database:

    ./symfony doctrine:rebuild-db

Il task legge le informazioni di connessione in `config/databases.yml`:

Aggiungere l'opzione `--migrate`se si vuole lanciare la migrazione sull'applicazione
invece che inserire l'SQL di Doctrine.

    ./symfony doctrine:rebuild-db --migrate

### ~`doctrine::reload-data`~

Il task `doctrine::reload-data` cancella il databse, lo ricrea e carica le fixture:

    $ php symfony doctrine:reload-data [--application[="..."]] [--env="..."] [--no-confirmation] [--dir="..."] [--migrate] [--append] 

*Alias(es)*: `doctrine-reload-data`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | non chiede la conferma
| `--dir` | `-` | Le cartelle dove cercare le fixture (sono ammessi valori multipli)
| `--migrate` | `-` | Migra invece di reinizializzare il database
| `--append` | `-` | Non cancella i dati presenti nel database


Il task `doctrine:reload-data` cancella il database, lo ricrea e carica le fixture
Si chiama con:

    php symfony doctrine:reload-data
  
Il task è equivalente a:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load` 

`generate`
----------

### ~`generate::app`~

Il task `generate::app` genera una nuova applicazione:

    $ php symfony generate:app [--escaping-strategy="..."] [--csrf-secret="..."] application

*Alias*: `init-app`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `application` | `-` | Il nome dell'applicazione


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--escaping-strategy` | `1` | Strategia per l'escape in uscita
| `--csrf-secret` | `1` | Stringa segreta da usare per la protezione CSRF


Il task `generate:app` la struttura di base delle cartelle
per una nuova applicazione nel progetto corrente:

    ./symfony generate:app frontend

Questo task crea anche due script per il front controller nella
cartella `web/`:

    web/%application%.php`     per l'ambiente di produzione
    web/%application%_dev.php` per l'ambiente di sviluppo

Per la prima applicazione, lo script nell'ambiente di produzione è chiamato
`index.php`.

Se esiste già una applicazione con lo stesso nome,
viene lanciata una eccezione `sfCommandException`.

Per impostazione predefinita, l'escape dell'output è abilitato (per prevenire attacchi XSS),
ed è anche generata una una stringa casuale segreta per prevenire CSRF.

Si può disabilitare l'escape dell'output utilizzando l'opzione `escaping-strategy`:

    ./symfony generate:app frontend --escaping-strategy=false


Si può abilitare il token di sessione nei form (per prevenire CSRF) definendo
una stringa segreta con l'opzione `csrf-secret`:

    ./symfony generate:app frontend --csrf-secret=UniqueSecret

Si può personalizzare lo scheletro predefinito usato dal task creando una
cartella `%sf_data_dir%/skeleton/app`.

### ~`generate::module`~

Il task `generate::module` genera un nuovo modulo:

    $ php symfony generate:module  application module

*Alias*: `init-module`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `application` | `-` | Il nome dell'applicazione
| `module` | `-` | Il nome del modulo




Il task `generate:module` crea la struttura di base delle cartelle
per un nuovo modulo, in una applicazione esistente:

    ./symfony generate:module frontend article

Il task può anche cambiare il nome dell'autore trovato in `actions.class.php`
se è stato configurato in `config/properties.ini`:

    [symfony]
      name=blog
      author=Fabien Potencier <fabien.potencier@sensio.com>

Si può personalizzare lo scheletro predefinito usato dal task creando una
cartella `%sf_data_dir%/skeleton/module`.

Il task crea anche uno scheletro di test funzionale chiamato
`%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php`
che per impostazione predefinita non passa.

Se nell'applicazione esiste già un modulo con lo stesso nome,
viene lanciata una eccezione `sfCommandException`.

### ~`generate::project`~

Il task `generate::project` genera un nuovo progetto:

    $ php symfony generate:project [--orm="..."] [--installer="..."] nome [autore]

*Alias*: `init-project`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del progetto
| `autore` | `Your name here` | L'autore del progetto


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--orm` | `Doctrine` | L'ORM da usare per impostazione predefinita
| `--installer` | `-` | Uno script di installazione da eseguire


Il task `generate:project` crea nella cartella corrente 
la struttura di base delle cartelle per un nuovo progetto:

    ./symfony generate:project blog

Se la cartella corrente contiene già un progetto di symfony,
viene lanciata una eccezione `sfCommandException`.

Per impostazione predefinita, il task configura Doctrine come ORM. Se si vuole utilizzare
Propel, usare l'opzione `--orm`:

    ./symfony generate:project blog --orm=Propel

Se non si vuole utilizzare un ORM, passare `none` all'opzione `--orm`:

    ./symfony generate:project blog --orm=none

È anche possibile passare l'opzione `--installer` per personalizzare ulteriormente
il progetto:

    ./symfony generate:project blog --installer=./installer.php

Opzionalmente si può includere un secondo argomento `autore` per specificare qual'è
il nome da utilizzare come autore quando symfony genera nuove classi:

    ./symfony generate:project blog "Jack Doe"

### ~`generate::task`~

Il task `generate::task` crea lo scheletro di una classe per un nuovo task:

    $ php symfony generate:task [--dir="..."] [--use-database="..."] [--brief-description="..."] nome_task



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome_task` | `-` | Il nome del task (può contenere namespace)


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--dir` | `lib/task` | La cartella dove creare il task
| `--use-database` | `doctrine` | Se il task necessita di inizializzazione del modello per accedere al database
| `--brief-description` | `-` | Una breve descrizione del task (appare nell'elenco dei task)


`generate:task` crea una nuova classe sfTask basata sul nome passato come
parametro:

    ./symfony generate:task namespace:name

Lo scheletro del task `namespaceNameTask.class.php` è creato sotto la cartellas
`lib/task/`. Notare che il namespace è opzionale.

Se si vuole creare il file in un'altra cartella (relativa alla cartella
radice del progetto), passarla con l'opzione `--dir`. Se non esiste già
questa cartella sarà creata.

    ./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task

Se si vuole il valore predefinito del task a una connessione diversa da `doctrine`,
fornire il nome di questa connessione con l'opzione `--use-database`:

    ./symfony generate:task namespace:name --use-database=main

L'opzione `--use-database` può anche essere usata per disabilitare l'inizializzazione
del database nel task generato:

    ./symfony generate:task namespace:name --use-database=false

Si può anche specificare una descrizione:

    ./symfony generate:task namespace:name --brief-description="Fa cose interessanti"

`i18n`
------

### ~`i18n::extract`~

Il task `i18n::extract` estrae le stringhe i18n dai file php:

    $ php symfony i18n:extract [--display-new] [--display-old] [--auto-save] [--auto-delete] application culture



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `cultura` | `-` | La cultura in obiettivo


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--display-new` | `-` | Visualizza tutte le nuove stringhe trovate
| `--display-old` | `-` | Visualizza tutte le stringhe vecchie
| `--auto-save` | `-` | Salva le nuove stringhe
| `--auto-delete` | `-` | Cancella le vecchie stringhe


Il task `i18n:extract` estrae le stringhe i18n dai file del progetto
per una data applicazione e cultura obiettivo:

    ./symfony i18n:extract frontend fr

Per impostazione predefinita, il task visualizza solo il numero di stringhe nuove e vecchie
che trova nel progetto corrente.

Se si vogliono visualizzare le stringhe nuove, usare l'opzione `--display-new`:

    ./symfony i18n:extract --display-new frontend fr

Per salvarle nel catalogo delle frasi i18n, usare l'opzione `--auto-save`:

    ./symfony i18n:extract --auto-save frontend fr

Se si vogliono visualizzare le stringhe che sono presenti nel catalogo
delle frasi i18n ma che non sono state trovate nell'applicazione, usare 
l'opzione `--display-old`:

    ./symfony i18n:extract --display-old frontend fr

Per cancellare automaticamente le stringhe vecchie, usare `--auto-delete` ma
fare attenzione, soprattutto se si hanno traduzioni per plugin dal momento che
appariranno come stringhe vecchie ma così non è:

    ./symfony i18n:extract --auto-delete frontend fr

### ~`i18n::find`~

Il task `i18n::find` trova le stringhe non "pronte per i18n" nell'applicazione:

    $ php symfony i18n:find [--env="..."] applicazione



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--env` | `dev` | L'ambiente


Il task `i18n:find` trova le stringhe non internazionalizzate incorporate nei template:

    ./symfony i18n:find frontend

Questo task è capace di trovare le stringhe non internazionalizzate nell'HTML puro e nel codice PHP:

    <p>Testo non i18n</p>
    <p><?php echo 'Test' ?></p>

Essendo che il task restituisce tutte le stringhe incorporate nel PHP, si possono avere alcuni falsi positivi (soprattutto
se si usa la sintassi stringa per i parametri degli helper).

`log`
-----

### ~`log::clear`~

Il task `log::clear` pulisce i file di log:

    $ php symfony log:clear  

*Alias*: `log-purge`





Il task `log:clear` pulisce tutti i file di log di symfony:

    ./symfony log:clear

### ~`log::rotate`~

Il task `log::rotate` ruota i file di log di una applicazione:

    $ php symfony log:rotate [--history="..."] [--period="..."] applicazione amb

*Alias*: `log-rotate`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `amb` | `-` | Il nome dell'ambiente


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--history` | `10` | Il massimo numero di file di log vecchi da tenere
| `--period` | `7` | Il periodo in giorni


Il task `log:rotate` ruota i file di log di un'applicazione per un dato
ambiente:

    ./symfony log:rotate frontend dev

Si può specificare un'opzione `period` o `history` option:

    ./symfony log:rotate frontend dev --history=10 --period=7

`plugin`
--------

### ~`plugin::add-channel`~

Il task `plugin::add-channel` aggiunge un nuovo canale PEAR:

    $ php symfony plugin:add-channel  nome



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del canale




Il task `plugin:add-channel` aggiunge un nuovo canale PEAR:

    ./symfony plugin:add-channel symfony.plugins.pear.example.com

### ~`plugin::install`~

Il task `plugin::install` installa un plugin:

    $ php symfony plugin:install [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] [-d|--install_deps] [--force-license] name

*Alias*: `plugin-install`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del plugin


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--stability`<br />`(-s)` | `-` | La stabilità preferita (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | La versione preferita
| `--channel`<br />`(-c)` | `-` | Il nome del canale PEAR
| `--install_deps`<br />`(-d)` | `-` | Per forzare l'installazione delle dipendenze richieste
| `--force-license` | `-` | Per forzare l'installazione anche se la licenza non è simile alla MIT


Il task `plugin:install` installa un plugin:

    ./symfony plugin:install sfGuardPlugin

Per impostazione predefinita, installa l'ultima versione `stabile`.

Se si vuole installare un plugin che non è ancora stabile,
usare l'opzione `stability`:

    ./symfony plugin:install --stability=beta sfGuardPlugin
    ./symfony plugin:install -s beta sfGuardPlugin

Si può anche forzare l'installazione di una versione specifica:

    ./symfony plugin:install --release=1.0.0 sfGuardPlugin
    ./symfony plugin:install -r 1.0.0 sfGuardPlugin

Per forzare l'installazione di tutte le dipendenze richieste, usare il flag `install_deps`:

    ./symfony plugin:install --install-deps sfGuardPlugin
    ./symfony plugin:install -d sfGuardPlugin

Per impostazione predefinita, il canale PEAR usato è `symfony-plugins`
(plugins.symfony-project.org).

Si può specificare un altro canale con l'opzione `channel`:

    ./symfony plugin:install --channel=mypearchannel sfGuardPlugin
    ./symfony plugin:install -c mypearchannel sfGuardPlugin

Si possono anche installare i pacchetti PEAR ospitati su un sito web:

    ./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz

Oppure installare pacchetti PEAR in locale:

    ./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz

Se il plugin racchiude dei contenuti web (immagini, fogli di stile o javascript),
il task crea un link simbolico `%name%` per questi elementi sotto `web/`.
Su Windows, il task copia tutti i file nella cartella `web/%name%`.

### ~`plugin::list`~

Il task `plugin::list` elenca i plugin installati:

    $ php symfony plugin:list  

*Alias*: `plugin-list`





Il task `plugin:list` elenca tutti i plugin installati:

    ./symfony plugin:list

Fornisce anche il canale e la versione per ciascun plugin.

### ~`plugin::publish-assets`~

Il task `plugin::publish-assets` pubblica gli elementi web per tutti i plugin:

    $ php symfony plugin:publish-assets [--core-only] [plugin1] ... [pluginN]


| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `plugins` | `-` | Pubblica gli elementi web di questa plugin


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--core-only` | `-` | Se assegnato verranno pubblicati gli elementi web solo per i core plugin


Il task `plugin:publish-assets` pubblicherà gli elementi web per tutti i plugin.

    ./symfony plugin:publish-assets

In realtà questo invierà l'evento `plugin.post_install` per ciascun plugin.

È possibile specificare quale/i plugin dovrebbero installare i loro elementi web, passando
i nomi delle plugin come argomenti:

    ./symfony plugin:publish-assets sfDoctrinePlugin


### ~`plugin::uninstall`~

Il task `plugin::uninstall` disinstalla un plugin:

    $ php symfony plugin:uninstall [-c|--channel="..."] [-d|--install_deps] name

*Alias*: `plugin-uninstall`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del plugin


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--channel`<br />`(-c)` | `-` | Il nome del canale PEAR
| `--install_deps`<br />`(-d)` | `-` | Per forzare l'installazione delle dipendenze


Il task `plugin:uninstall` disinstalla un plugin:

    ./symfony plugin:uninstall sfGuardPlugin

Il canale predefinito è `symfony`.

Si può anche disinstallare un plugin che ha un canale diverso:

    ./symfony plugin:uninstall --channel=miocanalepear sfGuardPlugin

    ./symfony plugin:uninstall -c miocanalepear sfGuardPlugin

Oppure usando la notazione `canale/pacchetto`:

    ./symfony plugin:uninstall miocanalepear/sfGuardPlugin

Si può recuperare il nome del canale  PEAR di un plugin lanciando
il task `plugin:list`.

Se il plugin ha dei contenuti web (immagini, fogli di stile o javascript),
il task rimuove anche i `web/%name%` link simbolici (sotto *nix)
o le cartelle (sotto Windows).

### ~`plugin::upgrade`~

Il task `plugin::upgrade` aggiorna un plugin:

    $ php symfony plugin:upgrade [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] name

*Alias*: `plugin-upgrade`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del plugin


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--stability`<br />`(-s)` | `-` | La stabilità preferita (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | La versione preferita
| `--channel`<br />`(-c)` | `-` | Il nome del canale PEAR


Il `plugin:upgrade` prova ad aggiornare un plugin:

    ./symfony plugin:upgrade sfGuardPlugin

Il canale predefinito è `symfony`.

Se il plugin ha dei contenuti web (immagini, fogli di stile o javascript),
il task aggiorna anche il contenuto della cartella `web/%name%` su Windows.

Vedere `plugin:install` per maggiori informazioni sul formato del nome dei plugin e le opzioni.

`project`
---------

### ~`project::clear-controllers`~

Il task `project::clear-controllers` pulisce tutti i controllori degli ambienti non in produzione:

    $ php symfony project:clear-controllers  

*Alias*: `clear-controllers`





Il task `project:clear-controllers` pulisce tutti i controllori degli ambienti non in
produzione:

    ./symfony project:clear-controllers

Si può usare questo task su un server in produzione per rimuovere gli script di tutti i front
controller eccetto quelli in produzione.

Se si hanno due applicazioni chiamate `frontend` e `backend`,
si hanno quattro script predefiniti per i controllori in `web/`:

    index.php
    frontend_dev.php
    backend.php
    backend_dev.php

Dopo l'esecuzione del task `project:clear-controllers`, rimangono
solo due script controllori in `web/`:

    index.php
    backend.php

Questi due controllori sono sicuri perché la modalità debug e la web debug
toolbar sono disabilitati.

### ~`project::deploy`~

Il task `project::deploy` copia i file di un progetto su un altro server:

    $ php symfony project:deploy [--go] [--rsync-dir="..."] [--rsync-options[="..."]] server

*Altro nome*: `sync`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `server` | `-` | Il nome del name


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--go` | `-` | Esegue l'invio dei file
| `--rsync-dir` | `config` | La cartella dove cercare i file rsync*.txt
| `--rsync-options` | `-azC --force --delete --progress` | Opzioni da passare all'eseguibile rsync


Il task `project:deploy` copia i file di un progetto su un server:

    ./symfony project:deploy production

Il server deve essere configurato in `config/properties.ini`:

    [production]
      host=www.example.com
      port=22
      user=fabien
      dir=/var/www/sfblog/
      type=rsync

Per automatizzare l'invio dei file, il task usa rsync su SSH.
Bisogna configurare l'accesso SSH con una chiave o configurare la password
in `config/properties.ini`.

Per impostazione predefinita, il task è in dry-mode. Per eseguire un invio reale,
bisogna passare l'opzione `--go`:

    ./symfony project:deploy --go production

File e cartelle configurate in `config/rsync_exclude.txt` non
vengono inviati:

    .svn
    /web/uploads/*
    /cache/*
    /log/*

Si possono anche creare dei file `rsync.txt` e `rsync_include.txt`.

Se è necessario personalizzare i file `rsync*.txt` basati su un server,
si può passare l'opzione `rsync-dir`:

    ./symfony project:deploy --go --rsync-dir=config/production production

In ultimo, è possibile specificare le opzioni passate all'eseguibile rsync, usando
l'opzione `rsync-options` (il valore predefinito è `-azC --force --delete --progress`):

    ./symfony project:deploy --go --rsync-options=-avz

### ~`project::disable`~

Il task `project::disable` disabilita un'applicazione in un dato ambiente:

    $ php symfony project:disable  amb [app1] ... [appN]

*Alias*: `disable`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `amb` | `-` | Il nome dell'ambiente
| `app` | `-` | Il nome dell'applicazione



Il task `project:disable` disabilita un ambiente:

    ./symfony project:disable prod

È anche possibile specificare applicazioni individuali da disabilitare in questo
ambiente:

    ./symfony project:disable prod frontend backend


### ~`project::enable`~

Il task `project::enable` abilita una applicazione in un dato ambiente:

    $ php symfony project:enable  amb [app1] ... [appN]

*Alias*: `enable`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `amb` | `-` | Il nome dell'ambiente
| `app` | `-` | Il nome dell'applicazione




Il task `project:enable` abilita uno specifico ambiente:

    ./symfony project:enable frontend prod

È possibile anche specificare applicazioni individuali da abilitare in questo
ambiente:

    ./symfony project:enable prod frontend backend

### ~`project::optimize`~

Il task `project::optimize` ottimizza un progetto per migliorare le performance:

    $ php symfony project:optimize  applicazione [ambiente]



| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `ambiente` | `prod` | Il nome dell'ambiente





`project:optimize` ottimizza un progetto per migliorare le performance:

    ./symfony project:optimize frontend prod

Questo task deve essere utilizzato solo su un server di produzione. Non dimenticare
di rilanciare il task ogni volta che il progetto subisce delle modifiche.

### ~`project::permissions`~

Il task `project::permissions` corregge i permessi delle cartelle di symfony:

    $ php symfony project:permissions  

*Alias*: `permissions, fix-perms`





Il task `project:permissions` corregge i permessi delle cartelle:

    ./symfony project:permissions


### ~`project::send-emails`~

Il task `project::send-emails` invia le email memorizzate in una coda:

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] [--message-limit[="..."]] [--time-limit[="..."]] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--message-limit` | `0` | Il massimo numero di messaggi da inviare
| `--time-limit` | `0` | Il termine di tempo per l'invio dei messaggi (in secondi)


`project:send-emails` invia le email memorizzate in una coda:

    php symfony project:send-emails

È possibile limitare il numero di messaggi da inviare:

    php symfony project:send-emails --message-limit=10

O limitare il tempo (in secondi):

    php symfony project:send-emails --time-limit=10

### ~`project::upgrade1.3`~

Il task `project:upgrade1.3` aggiorna un progetto symfony basato sulla versione 1.2
alla versione di symfony 1.3.

    ./symfony project:upgrade1.3  


Si prega di leggere il file UPGRADE_TO_1_3 per avere informazioni su cosa fa questo task.

### ~`project::validate`~

Il task `project::validate` trova gli elementi deprecati in un progetto:

    $ php symfony project:validate  

Il task elenca tutti i file che si devono cambiare prima del passaggio a
symfony 1.4.

`propel`
--------

### ~`propel::build`~

Il task `propel::build` genera il codice basandosi sullo schema:

    $ php symfony propel:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-load[="..."]] [--and-append[="..."]] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--no-confirmation` | `-` | Se forzare la cancellazione del database
| `--all` | `-` | Ricrea tutto e reinizializza il database
| `--all-classes` | `-` | Crea tutte le classi
| `--model` | `-` | Crea le classi dei modelli
| `--forms` | `-` | Crea le classi dei form
| `--filters` | `-` | Crea le classi dei filtri
| `--sql` | `-` | Crea l'SQL
| `--db` | `-` | Cancella, crea e inserisce l'SQL
| `--and-load` | `-` | Carica i dati delle fixture (sono ammessi valori multipli)
| `--and-append` | `-` | Aggiunge i dati delle fixture (sono ammessi valori multipli)


Il task `propel:build` genera il codice basandosi sullo schema:

    ./symfony propel:build

È necessario specificare cosa si vuole creare. Ad esempio, se si vogliono
creare le classi dei modelli e dei form  si usano le opzioni `--model` e `--forms`:

    ./symfony propel:build --model --forms

È possibile usare l'opzione scorciatoia `--all` se si vogliono generare tutte le classi,
i file SQL e ricreare il database:

    ./symfony propel:build --all

Questo è equivalente a lanciare i seguenti task:

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

Si possono generare solo i file delle classi usando l'opzione scorciatoia `--all-classes`.
Quando questa opzione è usata da sola, il database non sarà modificato.

    ./symfony propel:build --all-classes

L'opzione `--and-load` caricherà i dati dalle cartelle `data/fixtures/`
del progetto e dei plugin:

    ./symfony propel:build --db --and-load

Per specificare quali fixtures vanno caricate, aggiungere un parametro all'opzione `--and-load`:

    ./symfony propel:build --all --and-load="data/fixtures/dev/"

Per aggiungere i dati delle fixture senza cancellare nessun record dal database, aggiungere
l'opzione `--and-append`:

    ./symfony propel:build --all --and-append

### ~`propel::build-all`~

Il task `propel::build-all` genera modelli per Propel classi per i form, l'SQL e inizializza il database:

    $ php symfony propel:build-all [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] 

*Alias*: `propel-build-all`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--connection` | `propel` | Il nome della connessione
| `--no-confirmation` | `-` | Non chiede conferma
| `--skip-forms`<br />`(-F)` | `-` | Salta la generazione dei form
| `--classes-only`<br />`(-C)` | `-` | Non inizializza il database
| `--phing-arg` | `-` | Parametro arbitrario phing (più valori ammessi)


Il task `propel:build-all` è una scorciatoia per cinque altri task:

    ./symfony propel:build-all

Il task è equivalente a:

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

Vedere le pagine di aiuto di questi task per avere maggiori informazioni.

Per saltare il prompt di conferma, si può passare l'opzione `no-confirmation`:

    ./symfony propel:buil-all --no-confirmation

Per creare tutte le classi ma saltare l'inizializzazione del database, usare l'opzione 
`classes-only`:

    ./symfony propel:build-all --classes-only

### ~`propel::build-all-load`~

Il task `propel::build-all-load` genera modelli per Propel e classi per i form, l'SQL, inizializza il database e carica i dati:

    $ php symfony propel:build-all-load [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] [--append] [--dir="..."] 

*Alias*: `propel-build-all-load`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `dev` | L'ambiente
| `--connection` | `propel` | Il nome della connessione
| `--no-confirmation` | `-` | Non chiede conferma
| `--skip-forms`<br />`(-F)` | `-` | Salta la generazione dei form
| `--classes-only`<br />`(-C)` | `-` | Non inizializza il database
| `--phing-arg` | `-` | Parametro arbitrario phing (più valori ammessi)
| `--append` | `-` | Non elimina i dati già presenti nel database
| `--dir` | `-` | Le cartelle da utilizzare per cercare delle fixture (più valori ammessi)


Il task `propel:build-all-load` è una scorciatoia per due altri task:

    ./symfony propel:build-all-load

Il task è equivalente a:

    ./symfony propel:build-all
    ./symfony propel:data-load

Vedere le pagine di aiuto di questi tasks per avere maggiori informazioni.

Per saltare la conferma, si può passare l'opzion
`no-confirmation`:

    ./symfony propel:buil-all-load --no-confirmation

### ~`propel::build-filters`~

Il task `propel::build-filters` crea classi di filtri per i form per il modello corrente:

    $ php symfony propel:build-filters [--connection="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--application[="..."]] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--connection` | `propel` | Il nome della connessione
| `--model-dir-name` | `model` | Il nome della cartella del modello
| `--filter-dir-name` | `filter` | Il nome della cartella del filtro dei form
| `--application` | `1` | Il nome dell'applicazione


Il task `propel:build-filters` crea classi di filtri per i form dallo schema:

    ./symfony propel:build-filters

Il task legge le informazioni dello schema in `config/*schema.xml` e/o
`config/*schema.yml` dal progetto e da tutti i plugin installati.

Il task usa la connessione `propel` così come è definita in `config/databases.yml`.
È possibile usare un'altra connessione utilizzando l'opzione `--connection`:

    ./symfony propel:build-filters --connection="name"

I file con le classi dei modelli per i filtri dei form sono creati in `lib/filter`.

Questo task non sovrascriverà mai le classi personalizzate in `lib/filter`.
Sostituisce soltanto le classi base generate in `lib/filter/base`.


### ~`propel::build-forms`~

Il task `propel::build-forms` crea classi per i form per il modello corrente:

    $ php symfony propel:build-forms [--connection="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--application[="..."]] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--connection` | `propel` | Il nome della connessione
| `--model-dir-name` | `model` | Il nome della cartella del modello
| `--form-dir-name` | `form` | Il nome della cartella del form
| `--application` | `1` | Il nome dell'applicazione


Il task `propel:build-forms` crea classi per i form dallo schema:

    ./symfony propel:build-forms

Il task legge le informazioni dello schema in `config/*schema.xml` e/o
`config/*schema.yml` dal progetto e da tutti i plugin installati.

Il task usa la connessione `propel` così come definita in `config/databases.yml`.
È possibile usare un'altra connessione utilizzando l'opzione `--connection`:

    ./symfony propel:build-forms --connection="name"

I file con le classi dei modelli dei form sono create in `lib/form`.

Questo task non sovrascriverà mai le classi personalizzate in `lib/form`.
Sostituisce soltanto le classi base generate in `lib/form/base`.


### ~`propel::build-model`~

Il task `propel::build-model` crea classi per il modello corrente:

    $ php symfony propel:build-model [--phing-arg="..."] 

*Alias*: `propel-build-model`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--phing-arg` | `-` | Parametro arbitrario phing (più valori ammessi)


Il task `propel:build-model` crea classi di modelli per lo schema:

    ./symfony propel:build-model

Il task legge le informazioni dello schema in `config/*schema.xml` e/o
`config/*schema.yml` dal progetto e da tutti i plugin installati.

Si possono mischiare file dello schema YML e XML. Il task convertirà
quelli YML in XML prima di chiamare il task Propel.

Il file con le classi dei moelli sono creati in `lib/model`.

Questo task non sovrascriverà mai le classi personalizzate in  `lib/model`.
Sostituisce soltanto i file presenti in `lib/model/om` e `lib/model/map`.

### ~`propel::build-schema`~

Il task `propel::build-schema` crea uno schema da un database esistente:

    $ php symfony propel:build-schema [--application[="..."]] [--env="..."] [--connection="..."] [--xml] [--phing-arg="..."] 

*Alias*: `propel-build-schema`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `cli` | L'ambiente
| `--connection` | `-` | Il nome della connessione
| `--xml` | `-` | Crea uno schema XML invece di quello YML
| `--phing-arg` | `-` | Parametro arbitrario phing (più valori ammessi)


Il task `propel:build-schema` analizza un database per creare uno schema:

    ./symfony propel:build-schema

Per impostazione predefinita, il task crea un file YML, si può anche creare un file XML:

    ./symfony --xml propel:build-schema

Il formato XML contiene più informazioni di quello in YML.

### ~`propel::build-sql`~

Il task `propel::build-sql` crea l'SQL per il modello corrente:

    $ php symfony propel:build-sql [--phing-arg="..."] 

*Alias*: `propel-build-sql`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--phing-arg` | `-` | Parametro arbitrario phing (più valori ammessi)


Il task `propel:build-sql` crea istruzioni SQL per la creazione delle tabelle:

    ./symfony propel:build-sql

L'SQL generato è ottimizzato per il database configurato in `config/propel.ini`:

    propel.database = mysql

### ~`propel::data-dump`~

Il task `propel::data-dump` copia i dati nella cartella delle fixture:

    $ php symfony propel:data-dump [--application[="..."]] [--env="..."] [--connection="..."] [--classes="..."] [target]

*Alias*: `propel-dump-data`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `target` | `-` | Il nome file di destinazione


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `cli` | L'ambiente
| `--connection` | `propel` | Il nome della connessione
| `--classes` | `-` | I nomi delle classi da copiare (separate da una virgola)


Il task `propel:data-dump` copia i dati del database:

    ./symfony propel:data-dump > data/fixtures/dump.yml

Per impostazione predefinita, il task visualizza i dati nell'output standard,
ma si può anche passare un nome file come secondo parametro:

    ./symfony propel:data-dump dump.yml

Il task copierà i dati in `data/fixtures/%target%`
(data/fixtures/dump.yml nell'esempio).

Il file copiato è in formato YML e può essere re-importato utilizzando
il task `propel:data-load`.

Per impostazione predefinita, il task usa la connessione `propel` così come è definita in `config/databases.yml`.
È possibile usare un'altra connessione utilizzando l'opzione `connection`:

    ./symfony propel:data-dump --connection="name"

Se si vogliono solo copiare alcune classi, usare l'opzione `classes`:

    ./symfony propel:data-dump --classes="Article,Category"

Se si vuole usare una specifica configurazione del database da una applicazione, si può usare
l'opzione `application`:

    ./symfony propel:data-dump --application=frontend

### ~`propel::data-load`~

Il task `propel::data-load` carica i dati delle fixture YAML:

    $ php symfony propel:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [dir_or_file1] ... [dir_or_fileN] 

*Alias*: `propel-load-data`

| Argomento | Predefinito | Descrizione
| --------- | ----------- | -----------
| `dir_or_file` | `-` | Cartella o file da caricare


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `cli` | L'ambiente
| `--append` | `-` | Non cancellare i dati presenti nel database
| `--connection` | `propel` | Il nome della connessione


Il task `propel:data-load` carica le fixture con i dati nel database:

    ./symfony propel:data-load

Il task carica i dati da tutti i file trovati in `data/fixtures/`.

Se si vogliono caricare dati da file specifici o cartelle, si può aggiungerle
come argomento:

    ./symfony propel:data-load data/fixtures/dev data/fixtures/users.yml

Il task usa la connessione `propel` così come è definita in `config/databases.yml`.
Si può usare un'altra connessione utilizzando l'opzione `--connection`:

    ./symfony propel:data-load --connection="name"

Se non si vuole che il task rimuova i dati presenti nel database,
utilizzare l'opzione `--append`:

    ./symfony propel:data-load --append

Se si vuole utilizzare una specifica configurazione del database da una applicazione, su può usare
l'opzione `application`:

    ./symfony propel:data-load --application=frontend

### ~`propel::generate-admin`~

Il task `propel::generate-admin` genera un modulo admin di Propel:

    $ php symfony propel:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] applicazione rotta_o_modello



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `rotta_o_modello` | `-` | Il nome della rotta o la classe del modello


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--module` | `-` | Il nome del modulo
| `--theme` | `admin` | Il nome del tema
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classe base per le azioni


Il task `propel:generate-admin` genera un modulo admin di Propel:

    ./symfony propel:generate-admin frontend Article

Il task crea un module nell'applicazione `%frontend%` per il
modello `%Article%`.

Il task crea una rotta nel file `routing.yml` dell'applicazione.

Si può anche generare un modulo admin di Propel passando il nome di una rotta:

    ./symfony propel:generate-admin frontend article

Il task crea un modulo nell'applicazione `%frontend%` per la
definizione di rotta `%article%` trovata in `routing.yml`.

Perché i filtri e le azioni funzionino correttamente, è necessario
aggiungere l'opzione `wildcard` alla rotta:

    article:
      class: sfPropelRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`propel::generate-module`~

Il task `propel::generate-module` genera un modulo per Propel:

    $ php symfony propel:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-propel-route] [--env="..."] applicazione modulo modello

*Alias*: `propel-generate-crud, propel:generate-crud`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `modulo` | `-` | Il nome del modulo
| `modello` | `-` | Il nome della classe per il modello


| Opzione (Scrociatoia) | Predefinito | Descrizione
| ------------------ | ----------- | -----------
| `--theme` | `default` | Il nome del tema
| `--generate-in-cache` | `-` | Genera il modulo in cache
| `--non-verbose-templates` | `-` | Genera modelli non verbosi
| `--with-show` | `-` | Genera un metodo `show`
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--route-prefix` | `-` | Il prefisso della rotta
| `--with-propel-route` | `-` | Se verrà usata una rotta di Propel
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classi basi per le azioni


Il task `propel:generate-module` genera un modulo Propel:

    ./symfony propel:generate-module frontend article Article

Il task crea un modulo `%module%` nell'applicazione `%application%`
per la classe del modello `%model%`.

Si può anche creare un modulo vuoto che eriditi le sue azioni e modelli da
un modulo generato a runtime in `%sf_app_cache_dir%/modules/auto%module%`
utilizzando l'opzione `--generate-in-cache` option:

    ./symfony propel:generate-module --generate-in-cache frontend article Article

Il generatore può usare un tema personalizzato utilizzando l'opzione `--theme`:

    ./symfony propel:generate-module --theme="custom" frontend article Article

In questo modo, è possibile creare il propriogeneratore di moduli con le proprie convenzioni.

È anche possibile cambiare la classe base predefinita per le azioni (predefinita come sfActions) dei
moduli generati:

    ./symfony propel:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`propel::generate-module-for-route`~

Il task `propel::generate-module-for-route` genera un modulo di Propel per una definizione di rotta:

    $ php symfony propel:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] applicazione rotta



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `rotta` | `-` | Il nome della rotta


| Opzione (Scrociatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--theme` | `default` | Il nome del tema
| `--non-verbose-templates` | `-` | Generamodelli non verbosi
| `--singular` | `-` | Il nome singolare
| `--plural` | `-` | Il nome plurale
| `--env` | `dev` | L'ambiente
| `--actions-base-class` | `sfActions` | La classe base per le azioni


Il task `propel:generate-module-for-route` genera un modulo di Propel per una definizione di rotta:

    ./symfony propel:generate-module-for-route frontend article

Il task crea un modulo nell'applicazione `%frontend%` per la
definizione di rotta `%article%` trovata in `routing.yml`.

### ~`propel::graphviz`~

Il task `propel::graphviz` genera un grafico graphviz del corrente modello di oggetti:

    $ php symfony propel:graphviz [--phing-arg="..."] 





| Opzione (Scrociatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


Il task `propel:graphviz` crea una visualizzazione DOR graphviz
per disegnare in automatico il grafico del modello di oggetto:

    ./symfony propel:graphviz

### ~`propel::init-admin`~

Il task `propel::init-admin` inizializza un modulo admin di Propel:

    $ php symfony propel:init-admin [--theme="..."] applicazione modulo modello

*Alias*: `propel-init-admin`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `modulo` | `-` | Il nome del modulo
| `modello` | `-` | Il nome del modello della classe


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--theme` | `default` | Il nome del tema


Il task `propel:init-admin` genera un modulo admin di Propel:

    ./symfony propel:init-admin frontend article Article

Il task crea un modulo `%module%` nell'applicazione `%application%`
pe la classe del modello `%model%`.

Il modulo creato è vuoto ed eredita le sue azioni e i template da
un modulo generato "al volo" in `%sf_app_cache_dir%/modules/auto%module%`.

Il generatore può usare un tema personalizzato utilizzando l'opzione `--theme`:

    ./symfony propel:init-admin --theme="custom" frontend article Article

### ~`propel::insert-sql`~

Il task `propel::insert-sql` inserisce l'SQL per il modello corrente:

    $ php symfony propel:insert-sql [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [--phing-arg="..."] 

*Alias*: `propel-insert-sql`



| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--application` | `1` | Il nome dell'applicazione
| `--env` | `cli` | L'ambiente
| `--connection` | `-` | Il nome della connessione
| `--no-confirmation` | `-` | Non chiede una conferma
| `--phing-arg` | `-` | Parametro arbitrario phing (sono ammessi più valori)


Il task `propel:insert-sql` crea le tabelle del database:

    ./symfony propel:insert-sql

Il task si connette al database ed esegue tutte le istruzioni SQL
trovate nei file `config/sql/*schema.sql`.

Prima dell'esecuzione, il task chiederà di confermare l'esecuzione
dal momento che cancellerà tutti i dati presenti nel database.

Per saltare la conferma, è possibile passare l'opzione 
`--no-confirmation`:

    ./symfony propel:insert-sql --no-confirmation

Il task legge la configurazione del database dal file `databases.yml`.
Si possono usare applicazione/ambiente specifici passando
l'opzione `--application` o l'opzione `--env`.

Si può anche usare l'opzione `--connection` se si vuole
solo caricare le istruzioni SQL per una certa connessione.

### ~`propel::schema-to-xml`~

Il task `propel::schema-to-xml` crea il file schema.xml dal file schema.yml:

    $ php symfony propel:schema-to-xml  

*Alias*: `propel-convert-yml-schema`





Il task `propel:schema-to-xml` converte schemi in formato YML al formato XML:

    ./symfony propel:schema-to-xml

### ~`propel::schema-to-yml`~

Il task `propel::schema-to-yml` crea il file schema.yml dal file schema.xml:

    $ php symfony propel:schema-to-yml  

*Alias*: `propel-convert-xml-schema`





Il task `propel:schema-to-yml` converte schemi XML in YML:

    ./symfony propel:schema-to-yml

`symfony`
---------

### ~`symfony::test`~

Il task `symfony::test` lancia la suite test di symfony:

    $ php symfony symfony:test [-u|--update-autoloader] [-f|--only-failed] [--xml="..."] [--rebuild-all] 





| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--update-autoloader`<br />`(-u)` | `-` | Aggiorna la classe sfCoreAutoload
| `--only-failed`<br />`(-f)` | `-` | Lancia solo i test che l'ultima volta sono falliti
| `--xml` | `-` | Il nome del file di log XML compatibile con JUnit
| `--rebuild-all` | `-` | Ricarica tutti i file di fixture generati


Il task `test:all` lancia la suite test di symfony:

    ./symfony symfony:test

`test`
------

### ~`test::all`~

Il task `test::all` lancia tutti i test:

    $ php symfony test:all [-f|--only-failed] [--xml="..."] 

*Alias*: `test-all`


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--only-failed`<br />`(-f)` | `-` | Lancia solo i test che l'ultima volta sono falliti
| `--xml` | `-` | Il nome del file di log XML compatibile con JUnit


Il test `test:all` lancia tutti i test unitari e funzionali:

    ./symfony test:all

Il task lancia tutti i test trovati in `test/`.


Se alcuni test falliscono, si può usare l'opzione `--trace` per avere più
informazioni sul problema:

      `./symfony test:all -t

Oppure si può provare a risolvere il problema lanciandoli
a mano o con i task `test:unit` e `test:functional`.

Usare l'opzione `--only-failed` per forzare il task a eseguire solo i test
che sono falliti durante la precedente esecuzione:

      `./symfony test:all --only-failed

Ecco come funziona: la prima volta, tutti i test vengono lanciati normalmente. Ma per
le successive esecuzioni dei test, solo i test che sono falliti l'ultima volta vengono eseguiti. Appena
viene corretto il codice, alcuni test passeranno e saranno rimossi dalla sequenza di esecuzione.
Quando tutti i test passano, viene lanciata l'intera suite di test... si può quindi proseguire
e ripetere.

Il task può generare un file di log XML compatibile con JUnit, utilizzando l'opzione `--xml`:

    ./symfony test:all --xml=log.xml


### ~`test::coverage`~

Il task `test::coverage` mostra la copertura del codice testato:

    $ php symfony test:coverage [--detailed] nome_test nome_lib



| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome_test` | `-` | Un nome file del test o una cartella con i test
| `nome_lib` | `-` | Un nome di file della libreria o una cartella con la libreria 
|            |     | per la quale si desidera conoscere la copertura


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--detailed` | `-` | Mostra informazioni dettagliate


Il task `test:coverage` mostra la copertura del codice
dato un file di test o una cartella di test
e un file di libreria o una cartella di libreria per le quali si vuole
la copertura del codice:

    ./symfony test:coverage test/unit/model lib/model

Per mostrare le linee non coperte, passare l'opzione `--detailed`:

    ./symfony test:coverage --detailed test/unit/model lib/model

### ~`test::functional`~

Il task `test::functional` lancia i test funzionali:

    $ php symfony test:functional [--xml="..."] applicazione [controller1] ... [controllerN]

*Alias*: `test-functional`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `applicazione` | `-` | Il nome dell'applicazione
| `controller` | `-` | Il nome del controller


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--xml` | `-` | Il nome per il file di log XML compatibile con JUnit


Il task `test:functional` lancia i test funzionali per una
data applicazione:

    ./symfony test:functional frontend

Il task lancia tutti i test trovati in `test/functional/%application%`.

Se alcuni test falliscono, è possibile usare l'opzione `--trace` per avere più
informazioni sul problema:

      `./symfony test:functional frontend -t

È possibile lanciare tutti i test funzionali per uno specifico controller
fornendo il nome del controller:

    ./symfony test:functional frontend article

Si possono anche lanciare tutti i test funzionali per diversi controller:

    ./symfony test:functional frontend article comment

Il task può generare un file di log XML compatibile con JUnit, utilizzando l'opzione `--xml`:

    ./symfony test:functional --xml=log.xml

### ~`test::unit`~

Il task `test::unit` lancia i test unitari:

    $ php symfony test:unit [--xml="..."] [nome1] ... [nomeN]

*Alias*: `test-unit`

| Parametro | Predefinito | Descrizione
| --------- | ----------- | -----------
| `nome` | `-` | Il nome del test


| Opzione (Scorciatoia) | Predefinito | Descrizione
| --------------------- | ----------- | -----------
| `--xml` | `-` | Il nome per il file di log XML compatibile con JUnit


Il task `test:unit` lancia i test unitari:

    ./symfony test:unit

Il task lancia tutti i test trovati in `test/unit`.

Se alcuni test falliscono, è possibile usare l'opzione `--trace` per avere più
informazioni sul problema:

      `./symfony test:unit -t

Si possono lanciare test unitari con un nome specifico

    ./symfony test:unit strtolower

Si possono anche lanciare test unitari per più test:

    ./symfony test:unit strtolower strtoupper

Il task può generare un file di log XML compatibile con JUnit, utilizzando l'opzione `--xml`:

    ./symfony test:unit --xml=log.xml
