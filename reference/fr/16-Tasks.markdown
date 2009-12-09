Tasks
=====

Le framework symfony est livré avec un outil en ligne de commande.
Les tâches intégrées permettent au développeur d'effectuer une foule de tâches fastidieuses et
récurrentes dans la vie d'un projet.

Si vous exécutez la CLI `symfony` sans aucun argument, une liste des tâches disponibles
est affichée :

    $ php symfony

En passant l'option `-V`, vous obtenez quelques informations sur la version de
symfony et le chemin des bibliothèques symfony utilisé par le CLI :

    $ php symfony -V

L'outil CLI prend un nom de tâche pour son premier argument :

    $ php symfony list

Un nom de tâche peut être composé d'un espace de nommage facultatif et un nom, séparés par
un deux-points (`:`) :

    $ php symfony cache:clear

Après le nom de la tâche, les arguments et les options peuvent être passés :

    $ php symfony cache:clear --type=template

L'outil CLI prend en charge les options longues et courtes, avec ou sans
valeurs.

L'option `-t` est une option globale pour demander à n'importe quelle tâche d'afficher plus
d'informations pour déboguer.

<div class="pagebreak"></div>

Tâches disponibles
---------------

 * Tâches globales
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

La tâche `help` affiche l'aide pour une tâche :

    $ php symfony help [--xml] [task_name]

*Alias(es)*: `h`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `task_name` | `help` | Le nom de la tâche


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--xml` | `-` | Pour sortir l'aide en XML


La tâche `help` affiche l'aide pour une tâche donnée :

    ./symfony help test:all

Vous pouvez aussi sortir l'aide sous le format XML en utilisant l'option `--xml` :

    ./symfony help test:all --xml

### ~`list`~

La tâche `list` liste les tâches :

    $ php symfony list [--xml] [namespace]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `namespace` | `-` | Le nom de l'espace nommé


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--xml` | `-` | Pour sortir l'aide en XML


La tâche `list` liste toutes les tâches :

    ./symfony list

Vous pouvez aussi afficher les tâches pour un espace de nommage spécifique :

    ./symfony list test

Vous pouvez aussi sortir les informations sous le format XML en utilisant l'option `--xml` :

    ./symfony list --xml

`app`
-----

### ~`app::routes`~

La tâche `app::routes` affiche les routes actuelles pour l'application :

    $ php symfony app:routes  application [name]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `name` | `-` | Le nom de la route




La tâche `app:routes` affiche les routes actuelles pour l'application donnée :

    ./symfony app:routes frontend

`cache`
-------

### ~`cache::clear`~

La tâche `cache::clear` vide le cache :

    $ php symfony cache:clear [--app[="..."]] [--env[="..."]] [--type[="..."]] 

*Alias(es)*: `cc, clear-cache`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--app` | `-` | Le nom de l'application
| `--env` | `-` | L'environnement
| `--type` | `all` | Le type


La tâche `cache:clear` vide le cache symfony.

Par défaut, cela supprime le cache de tous les types disponibles, de toutes les applications,
et de tous les environnements.

Vous pouvez restreindre par type, par application, ou par environnement :

Par exemple, pour vider le cache de l'application `frontend` :

    ./symfony cache:clear --app=frontend

Pour vider le cache de l'environnement `prod` de l'application `frontend` :

    ./symfony cache:clear --app=frontend --env=prod

Pour vider le cache de tous les environnements `prod` :

    ./symfony cache:clear --env=prod

Pour vider le cache `config` pour tous les environnements `prod` :

    ./symfony cache:clear --type=config --env=prod

Les types intégrés sont : `config`, `i18n`, `routing`, `module`
et `template`.


`configure`
-----------

### ~`configure::author`~

La tâche `configure::author` configure l'auteur du projet :

    $ php symfony configure:author  author



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `author` | `-` | L'auteur du projet




La tâche `configure:author` configure l'auteur pour le projet :

    ./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"

L'auteur est utilisé par la génération des entêtes PHPDoc pour préconfigurer chaque fichier généré.

La valeur est socké dans [config/properties.ini].

### ~`configure::database`~

La tâche `configure::database` configure la base de données DSN :

    $ php symfony configure:database [--env[="..."]] [--name[="..."]] [--class[="..."]] [--app[="..."]] dsn [username] [password]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `dsn` | `-` | Le dsn de la base de données
| `username` | `root` | L'utilisateur de la base de données
| `password` | `-` | Le mot de passe de la base de données


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--env` | `all` | L'environnement
| `--name` | `propel` | Le nom de la connexion
| `--class` | `sfPropelDatabase` | Le nom de la classe de la base de données
| `--app` | `-` | Le nom de l'application


La tâche `configure:database` configure la base de données DSN
pour le projet :

    ./symfony configure:database mysql:host=localhost;dbname=example root mYsEcret

Par défaut, la tâche change la configuration pour tous les environnements. Si vous voulez
changer le dsn pour un environnement spécifique, utilisez l'option `env` :

    ./symfony configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret

Pour changer la configuration pour une application spécifique, utilisez l'option `app` :

    ./symfony configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret

Vous pouvez aussi spécifiez le nom de la connexion et le nom de la classe de la base de données :

    ./symfony configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret

WARNING: Le fichier `propel.ini` est aussi mis à jour lorsque vous utilisez la base de données `Propel`
et configure pour les environnements `all` sans l'option `app`.

`doctrine`
----------

### ~`doctrine::build`~

La tâche `doctrine::build` génère le code basé sur votre schéma :

    $ php symfony doctrine:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-migrate] [--and-load[="..."]] [--and-append[="..."]] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | S'il faut forcer la suppression de la base de données
| `--all` | `-` | Tout générer et réinitialiser la base de données
| `--all-classes` | `-` | Générer toutes les classes
| `--model` | `-` | Générer les classes du modèle
| `--forms` | `-` | Générer les classes du formulaire
| `--filters` | `-` | Générer les classes du filtre
| `--sql` | `-` | Générer SQL
| `--db` | `-` | Supprimer, créer, et insérer du SQL ou migrer la base de données
| `--and-migrate` | `-` | Migrer la base de données
| `--and-load` | `-` | Charger les données de test (plusieurs valeurs sont permises)
| `--and-append` | `-` | Insérer les données de test (plusieurs valeurs sont permises)


La tâche `doctrine::build` génère le code basé sur votre schéma :

    ./symfony doctrine:build

Vous devez spécifier ce que vous voulez construire. Par exemple, si vous voulez le modèle
et les classes du formulaire soient construits, utiliser les options `--model` et `--forms` :

    ./symfony doctrine:build --model --forms

Vous pouvez utiliser l'option de raccourci `--all` si vous voulez que toutes les classes et
les fichiers SQL files soient générés et que la base de données soit reconstruite :

    ./symfony doctrine:build --all

C'est équivalent à l'exécution des tâches suivantes :

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:build-sql
    ./symfony doctrine:insert-sql

Vous pouvez aussi générer un unique fichier de classe en utilisant l'option raccourci
`--all-classes`. Lorsque cette option est utilisée seule, la base de données ne sera pas modifiée.

    ./symfony doctrine:build --all-classes

L'option `--and-migrate` exécutera n'importe quelles migrations en attente une fois que les contructions
seront terminées :

    ./symfony doctrine:build --db --and-migrate

L'option `--and-load` chargera les données des répertoires `data/fixtures/` du
projet et des plugins :

    ./symfony doctrine:build --db --and-migrate --and-load

Pour spécifier quelles données de test doivent être chargées, ajoutez le paramètre à l'option `--and-load` :

    ./symfony doctrine:build --all --and-load="data/fixtures/dev/"

Pour ajouter des données de test sans supprimer aucun enregistrement de la base de données, ajoutez
l'option `--and-append` :

    ./symfony doctrine:build --all --and-append

### ~`doctrine::build-all`~

La tâche `doctrine::build-all` génère le modèle Doctrine, SQL et initialise la base de données :

    $ php symfony doctrine:build-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] 

*Alias(es)*: `doctrine-build-all`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données


La tâche `doctrine:build-all` est un raccourci de quatre autres tâches :

    ./symfony doctrine:build-all

Cette tâche est équivalent à :

    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:insert-sql

Regardez les pages d'aides de ces quatre tâches pour plus d'information.

Pour contourner la confirmation, vous pouvez passer l'option
`no-confirmation` :

    ./symfony doctrine:buil-all-load --no-confirmation

Ajoutez l'option `--migrate` si vous voulez exécuter les migrations de
votre projet plutôt que d'insérer le code SQL Doctrine.

    ./symfony doctrine:build-all --migrate

C'est équivalent à :

    ./symfony doctrine:build-model
    ./symfony doctrine:build-sql
    ./symfony doctrine:build-forms
    ./symfony doctrine:migrate

### ~`doctrine::build-all-load`~

La tâche `doctrine::build-all-load` génère le modèle Doctrine, SQL, initialise la base de données, et charge les données de test :

    $ php symfony doctrine:build-all-load [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*Alias(es)*: `doctrine-build-all-load`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données
| `--dir` | `-` | Les répertoires à regarder pour les données de test (plusieurs valeurs sont permises)
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données


La tâche `doctrine:build-all-load` est un raccourci de deux autres tâches :

    ./symfony doctrine:build-all-load

La tâche est équivalent à :

    ./symfony doctrine:build-all
    ./symfony doctrine:data-load

La tâche prend l'argument application à cause de la tâche `doctrine:data-load`.
Voir la page d'aide de `doctrine:data-load` pour plus d'information.

Pour contourner la confirmation, vous pouvez passer l'option
`no-confirmation` :

    ./symfony doctrine:build-all-load --no-confirmation

Ajoutez l'option `--migrate` si vous voulez exécuter les migrations de
votre projet plutôt que d'insérer le code SQL Doctrine.

    ./symfony doctrine:build-all-load --migrate

### ~`doctrine::build-all-reload`~

La tâche `doctrine::build-all-reload` génère le modèle Doctrine, SQL, initialise la base de données, et charge les données :

    $ php symfony doctrine:build-all-reload [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*Alias(es)*: `doctrine-build-all-reload`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données
| `--dir` | `-` | Les répertoires à regarder pour les données de test (plusieurs valeurs sont permises)
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données


La tâche `doctrine:build-all-reload` est un raccourci de cinq autres tâches :

    ./symfony doctrine:build-all-reload

La tâche est équivalent à :

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load

Ajoutez l'option `--migrate` si vous voulez exécuter les migrations de
votre projet plutôt que d'insérer le code SQL Doctrine.

    ./symfony doctrine:build-all-reload --migrate

### ~`doctrine::build-all-reload-test-all`~

La tâche `doctrine::build-all-reload-test-all` génère le modèle Doctrine, SQL, initialise la base de données, charge les données et exécute tous les tests :

    $ php symfony doctrine:build-all-reload-test-all [--application[="..."]] [--env="..."] [--no-confirmation] [-F|--skip-forms] [--migrate] [--dir="..."] [--append] 

*Alias(es)*: `doctrine-build-all-reload-test-all`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données
| `--dir` | `-` | Les répertoires à regarder pour les données de test (plusieurs valeurs sont permises)
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données


La tâche `doctrine:build-all-reload` est un raccourci de six autres tâches :

    ./symfony doctrine:build-all-reload-test-all frontend

La tâche est équivalent à :
  
    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load
    ./symfony test-all

La tâche prend l'argument application à cause de la tâche `doctrine:data-load`.
Voir la page d'aide de `doctrine:data-load` pour plus d'information.

Ajoutez l'option `--migrate` si vous voulez exécuter les migrations de
votre projet plutôt que d'insérer le code SQL Doctrine.

    ./symfony doctrine:build-all-reload-test-all --migrate

### ~`doctrine::build-db`~

La tâche `doctrine::build-db` crée la base de données pour le modèle actuel :

    $ php symfony doctrine:build-db [--application[="..."]] [--env="..."] [database1] ... [databaseN]

*Alias(es)*: `doctrine-build-db, doctrine:create-db`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `database` | `-` | Une base de données spécifique


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:build-db` crée une ou plusieurs bases de données basées sur la
configuration dans `config/databases.yml` :

    ./symfony doctrine:build-db

Vous pouvez spécifier les bases de données à créer en précisant leurs noms :

    ./symfony doctrine:build-db slave1 slave2

### ~`doctrine::build-filters`~

La tâche `doctrine::build-filters` crée les classes de formulaire de filtre du modèle actuel :

    $ php symfony doctrine:build-filters [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--generator-class="..."]





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--model-dir-name` | `model` | Le nom du répertoire du modèle
| `--filter-dir-name` | `filter` | Le nom du répertoire du formulaire du filtre
| `--generator-class` | `sfDoctrineFormFilterGenerator` | La classe génératrice


La tâche `doctrine:build-filters` crée les classes de formulaire de filtre à partir du schéma :

    ./symfony doctrine:build-filters

Cette tâche crée les classes de formulaire de filtre basées sur le modèle. Les classes sont
créées dans `lib/doctrine/filter`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/doctrine/filter`.
Elle remplace uniquement les classes de base générées dans `lib/doctrine/filter/base`.

### ~`doctrine::build-forms`~

La tâche `doctrine::build-forms` crée les classes du formulaire du modèle actuel :

    $ php symfony doctrine:build-forms [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--generator-class="..."] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--model-dir-name` | `model` | Le nom du répertoire du modèle
| `--form-dir-name` | `form` | Le nom du répertoire du formulaire
| `--generator-class` | `sfDoctrineFormGenerator` | La classe génératrice


La tâche `doctrine:build-forms` crée les classes du formulaire à partir du schéma :

    ./symfony doctrine:build-forms

Cette tâche crée les classes du formulaire basées sur le modèle. Les classes sont créées
dans `lib/doctrine/form`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/doctrine/form`.
Elle remplace uniquement les classes de base générées dans `lib/doctrine/form/base`.

### ~`doctrine::build-model`~

La tâche `doctrine::build-model` crée les classes du modèle actuel :

    $ php symfony doctrine:build-model [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-build-model`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:build-model` crée les classes à partir du schéma :

    ./symfony doctrine:build-model

La tâche lit les informations du schéma dans `config/doctrine/*.yml`
du projet et dans tous les plugins activés.

Les fichiers des classes du modèle sont crés dans `lib/model/doctrine`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/model/doctrine`.
Elle remplace uniquement les fichiers dans `lib/model/doctrine/base`.

### ~`doctrine::build-schema`~

La tâche `doctrine::build-schema` crée un schéma à partir d'une base de données existante :

    $ php symfony doctrine:build-schema [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-build-schema`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:build-schema` inspecte une base de données pour créer le schéma :

    ./symfony doctrine:build-schema

La tâche crée un fichier yml dans `config/doctrine`

### ~`doctrine::build-sql`~

La tâche `doctrine::build-sql` crée un SQL pour le modèle actuel :

    $ php symfony doctrine:build-sql [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-build-sql`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:build-sql` crée des instructions SQL pour la création de table :

    ./symfony doctrine:build-sql

Le SQL généré est optimisé pour la base de données configurée dans `config/databases.yml` :

    doctrine.database = mysql

### ~`doctrine::clean-model-files`~

La tâche `doctrine::clean-model-files` supprime toutes les classes du modèle générées pour des modèles qui n'existent plus dans votre schéma YAML :

    $ php symfony doctrine:clean-model-files [--no-confirmation] 

*Alias(es)*: `doctrine:clean`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--no-confirmation` | `-` | Ne pas demander de confirmation


La tâche `doctrine:clean-model-files` supprime les classes du modèle qui ne sont pas
presentes dans le projet ou dans les fichiers schema.yml des plugins :

    ./symfony doctrine:clean-model-files

### ~`doctrine::create-model-tables`~

La tâche `doctrine::create-model-tables` supprime et recrée les tables pour les modèles spécifiés :

    $ php symfony doctrine:create-model-tables [--application[="..."]] [--env="..."] [models1] ... [modelsN]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `models` | `-` | La liste des modèles


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `frontend` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:create-model-tables` supprime et recrée les tables pour les modèles spécifiés :

    ./symfony doctrine:create-model-tables User

### ~`doctrine::data-dump`~

La tâche `doctrine::data-dump` décharge les données dans le répertoire fixtures :

    $ php symfony doctrine:data-dump [--application[="..."]] [--env="..."] [target]

*Alias(es)*: `doctrine-dump-data`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `target` | `-` | Le nom du fichier cible


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:data-dump` décharge les données de la base de données :

    ./symfony doctrine:data-dump

La tâche décharge les données de la base de données dans `data/fixtures/%target%`.

Le fichier déchargé est au format YML et peut être réimporté en utilisant
la tâche `doctrine:data-load`.

    ./symfony doctrine:data-load

### ~`doctrine::data-load`~

La tâche `doctrine::data-load` charge les données de test au format YAML :

    $ php symfony doctrine:data-load [--application[="..."]] [--env="..."] [--append] [dir_or_file1] ... [dir_or_fileN]

*Alias(es)*: `doctrine-load-data`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `dir_or_file` | `-` | Répertoire ou fichier à charger


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données


La tâche `doctrine:data-load` charge les données de test dans la base de données :

    ./symfony doctrine:data-load

La tâche charge les données à partir de tous les fichiers trouvés dans `data/fixtures/`.

Si vous voulez charger des données à partir de fichier spécifique ou d'un répertoire, vous pouvez les ajouter
comme arguments :

    ./symfony doctrine:data-load data/fixtures/dev data/fixtures/users.yml

Si vous ne voulez pas que la tâche supprime les données existantes dans la base de données,
utilisez l'option `--append` :

    ./symfony doctrine:data-load --append

### ~`doctrine::delete-model-files`~

La tâche `doctrine::delete-model-files` supprime tous les fichiers auto-générés relatif à un nom de modèle donné :

    $ php symfony doctrine:delete-model-files [--no-confirmation] name1 ... [nameN]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du modèle dont vous souhaitez supprimer tous les fichiers liés


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--no-confirmation` | `-` | Ne pas demander de confirmation


La tâche `doctrine:delete-model-files` supprime tous les fichiers associés à certains
modèles:

    ./symfony doctrine:delete-model-files Article Author

### ~`doctrine::dql`~

La tâche `doctrine::dql` exécute une query DQL et affiche les résultats :

    $ php symfony doctrine:dql [--application[="..."]] [--env="..."] [--show-sql] [--table] dql_query [parameter1] ... [parameterN]

*Alias(es)*: `doctrine-dql`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `dql_query` | `-` | La query DQL à exécuter
| `parameter` | `-` | Paramètre de query


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--show-sql` | `-` | Voir le sql qui sera exécuté
| `--table` | `-` | Renvoie les résultats sous forme de tableau


La tâche `doctrine:dql` exécute une query DQL et affiche les résultats
mis en forme :

    ./symfony doctrine:dql "FROM User"

Vous pouvez voir le SQL qui sera exécuté en utilisant l'option `--show-sql` :

    ./symfony doctrine:dql --show-sql "FROM User"

Fournir des paramètres de query comme arguments supplémentaires :

    ./symfony doctrine:dql "FROM User WHERE email LIKE ?" "%symfony-project.com"

### ~`doctrine::drop-db`~

La tâche `doctrine::drop-db` supprime la base de données du modèle actuel :

    $ php symfony doctrine:drop-db [--application[="..."]] [--env="..."] [--no-confirmation] 

*Alias(es)*: `doctrine-drop-db`

| Argument | Par défaut | Description 
| -------- | ---------- | ----------- 
| `database` | `-` | La base de données spécifique


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | S'il faut forcer la suppression de la base de données


La tâche `doctrine:drop-db` supprime une ou plusieurs bases de données basées sur
la configuration dans `config/databases.yml`:

    ./symfony doctrine:drop-db

Vous serez invité à confirmer avant que les bases de données soient supprimées sauf
si vous précisez l'option `--no-confirmation` :

    ./symfony doctrine:drop-db --no-confirmation

Vous pouvez spécifier quelles sont bases de données qui doivent être supprimées en fournissant leurs noms :

    ./symfony doctrine:drop-db slave1 slave2

### ~`doctrine::generate-admin`~

La tâche `doctrine::generate-admin` génère le module admin de Doctrine :

    $ php symfony doctrine:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `route_or_model` | `-` | Le nom de la route ou la classe du modèle


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--module` | `-` | Le nom du module
| `--theme` | `admin` | Le nom du thème
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `doctrine:generate-admin` génère le module admin de Doctrine :

    ./symfony doctrine:generate-admin frontend Article

La tâche crée un module dans l'application `%frontend%` pour le
module `%Article%`.

La tâche crée une route pour vous dans l'application `routing.yml`.

Vous pouvez aussi générer un module admin de Doctrine en passant le nom de la route :

    ./symfony doctrine:generate-admin frontend article

La tâche crée un module dans l'application `%frontend%` pour la
définition de la route `%article%` trouvée dans `routing.yml`.

Pour les filtres et les actions Batch afin de travailler correctement, vous devez ajouter
l'option `with_wildcard_routes` à la route :

    article:
      class: sfDoctrineRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`doctrine::generate-migration`~

La tâche `doctrine::generate-migration` génère la classe de migration :

    $ php symfony doctrine:generate-migration [--application[="..."]] [--env="..."] [--editor-cmd="..."] name

*Alias(es)*: `doctrine-generate-migration`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom de la migration


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--editor-cmd` | `-` | Ouvrir le script avec cette commande lors de la création


La tâche `doctrine:generate-migration` génère le Template de migration

    ./symfony doctrine:generate-migration AddUserEmailColumn

Vous pouvez fournir l'option `--editor-cmd` pour ouvrir la classe de la nouvelle migration dans
l'éditeur de votre choix lors de la création :

    ./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

### ~`doctrine::generate-migrations-db`~

La tâche `doctrine::generate-migrations-db` génère des classes de migration à partir des connexions de bases de données existantes :

    $ php symfony doctrine:generate-migrations-db [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-generate-migrations-db, doctrine-gen-migrations-from-db`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:generate-migrations-db` génère des classes de migration à partir
des connexions de bases de données existantes :

    ./symfony doctrine:generate-migrations-db

### ~`doctrine::generate-migrations-diff`~

La tâche `doctrine::generate-migrations-diff` génère des classes de migration en produisant un diff entre votre ancien et votre nouveau schéma :

    $ php symfony doctrine:generate-migrations-diff [--application[="..."]] [--env="..."] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:generate-migrations-diff` génère des classes de migration en
produisant un diff entre votre ancien et votre nouveau schéma.

    ./symfony doctrine:generate-migrations-diff

### ~`doctrine::generate-migrations-models`~

La tâche `doctrine::generate-migrations-models` génère des classes de migration à partir d'une série existante de modèles :

    $ php symfony doctrine:generate-migrations-models [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-generate-migrations-models, doctrine-gen-migrations-from-models`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:generate-migrations-models` génère des classes de migration
à partir d'une série existante de modèles :

    ./symfony doctrine:generate-migrations-models

### ~`doctrine::generate-module`~

La tâche `doctrine::generate-module` génère le module Doctrine :

    $ php symfony doctrine:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-doctrine-route] [--env="..."] [--actions-base-class="..."] application module model

*Alias(es)*: `doctrine-generate-crud, doctrine:generate-crud`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `module` | `-` | Le nom du module
| `model` | `-` | Le nom de la classe modèle


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--theme` | `default` | Le nom du thème
| `--generate-in-cache` | `-` | Générer le module dans le cache
| `--non-verbose-templates` | `-` | Générer des Templates non-bavard
| `--with-show` | `-` | Générer  la méthode show
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--route-prefix` | `-` | Le préfixe de la route
| `--with-doctrine-route` | `-` | Si vous allez utiliser une route Doctrine
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `doctrine:generate-module` génère le module Doctrine :

    ./symfony doctrine:generate-module frontend article Article

La tâche crée le module `%module%` dans l'application `%application%`
pour la classe modèle `%model%`.

Vous pouvez également créer un module vide qui hérite de ses actions et ses Templates à partir
d'un module runtime généré dans `%sf_app_cache_dir%/modules/auto%module%` en
utilisant l'option `--generate-in-cache` :

    ./symfony doctrine:generate-module --generate-in-cache frontend article Article

Le générateur peut utiliser un thème personnalisé en utilisant l'option `--theme` :

    ./symfony doctrine:generate-module --theme="custom" frontend article Article

De cette façon, vous pouvez créer votre propre générateur de modules avec vos propres conventions.

Vous pouvez également modifier la classe des actions de base par défaut (par défaut sfActions) des
modules générés :

    ./symfony doctrine:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`doctrine::generate-module-for-route`~

La tâche `doctrine::generate-module-for-route` génère un module Doctrine pour une définition de la route :

    $ php symfony doctrine:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `route` | `-` | Le nom de la route


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--theme` | `default` | Le nom du thème
| `--non-verbose-templates` | `-` | Générer des Templates non-bavard
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `doctrine:generate-module-for-route` génère un module Doctrine pour une définition de la route :

    ./symfony doctrine:generate-module-for-route frontend article

La tâche crée un module dans l'application `%frontend%` pour la
définition de la route `%article%` trouvée dans `routing.yml`.

### ~`doctrine::insert-sql`~

La tâche `doctrine::insert-sql` insère du SQL pour le modèle actuel :

    $ php symfony doctrine:insert-sql [--application[="..."]] [--env="..."] 

*Alias(es)*: `doctrine-insert-sql`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement


La tâche `doctrine:insert-sql` crée des tables pour la base de données :

    ./symfony doctrine:insert-sql

La tâche se connecte à la base de données et crée des tables pour tous les fichiers
`lib/model/doctrine/*.class.php`.

### ~`doctrine::migrate`~

La tâche `doctrine::migrate` migre la base de données avec la version actuelle ou précisée :

    $ php symfony doctrine:migrate [--application[="..."]] [--env="..."] [--up] [--down] [--dry-run] [version]

*Alias(es)*: `doctrine-migrate`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `version` | `-` | La version à migrer


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--up` | `-` | Monter la migration d'une version
| `--down` | `-` | Descendre la migration d'une version
| `--dry-run` | `-` | Ne pas continuer les migrations


La tâche `doctrine:migrate` migre la base de données :

    ./symfony doctrine:migrate

Fournissez une version comme argument pour migrer vers une version spécifique :

    ./symfony doctrine:migrate 10

Pour monter ou descendre d'une version, utilisez les options `--up` ou `--down` :

    ./symfony doctrine:migrate --down

Si votre base de données supporte les instructions rolling back DDL, vous pouvez exécuter les migrations
en mode dry-run en utilisant l'option `--dry-run` :

    ./symfony doctrine:migrate --dry-run

### ~`doctrine::rebuild-db`~

La tâche `doctrine::rebuild-db` crée une base de données pour le modèle actuel :

    $ php symfony doctrine:rebuild-db [--application[="..."]] [--env="..."] [--no-confirmation] [--migrate] 

*Alias(es)*: `doctrine-rebuild-db`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | S'il faut forcer la suppression de la base de données
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données


La tâche `doctrine:rebuild-db` crée une base de données :

    ./symfony doctrine:rebuild-db

La tâche lit l'information de la connexion dans `config/databases.yml` :

Ajoutez l'option `--migrate` si vous voulez exécuter les migrations de
votre application plutôt que d'insérer le code SQL Doctrine.

    ./symfony doctrine:rebuild-db --migrate

### ~`doctrine::reload-data`~

La tâche `doctrine::reload-data` recharge la base de données et les données de tests pour votre projet :

    $ php symfony doctrine:reload-data [--application[="..."]] [--env="..."] [--no-confirmation] [--dir="..."] [--migrate] [--append] 

*Alias(es)*: `doctrine-reload-data`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--dir` | `-` | Les répertoires à regarder pour les données de test (plusieurs valeurs sont permises)
| `--migrate` | `-` | Migrer au lieu de réinitialiser la base de données
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données


La tâche `doctrine:reload-data` supprime la base de données, la recrée et charge
les données de test :

    php symfony doctrine:reload-data
  
La tâche est équivalente à :

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:insert-sql
    ./symfony doctrine:data-load`  

`generate`
----------

### ~`generate::app`~

La tâche `generate::app` génère une nouvelle application :

    $ php symfony generate:app [--escaping-strategy="..."] [--csrf-secret="..."] app

*Alias(es)*: `init-app`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `app` | `-` | Le nom de l'application


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--escaping-strategy` | `1` | La stratégie de l'échappement de sortie
| `--csrf-secret` | `1` | Secret à utiliser pour la protection CSRF


La tâche `generate:app` crée la structure de répertoire de base
pour la nouvelle application dans le projet courant :

    ./symfony generate:app frontend

La tâche crée deux scripts de contrôleurs frontaux dans le
répertoire `web/` :

    web/%application%.php`     pour l'environnement de production
    web/%application%_dev.php` pour l'environnement de développement

Pour la première application, le script de l'environnement de production est nommé
`index.php`.

Si une application existe déjà avec le même nom,
cela lève une `sfCommandException`.

Par défaut, l'échappement de sortie est activé (pour éviter des XSS), et un secret
aléatoire est également généré pour éviter les CSRF.

Vous pouvez désactiver l'échappement de sortie en utilisant l'option
`escaping-strategy` :

    ./symfony generate:app frontend --escaping-strategy=false

Vous pouvez activer le jeton de session dans les formulaires (pour éviter les CSRF), en définissant
un secret avec l'option `csrf-secret` :

    ./symfony generate:app frontend --csrf-secret=UniqueSecret

Vous pouvez personnaliser le squelette par défaut utilisé par la tâche en créant
un répertoire `%sf_data_dir%/skeleton/app`.

### ~`generate::module`~

La tâche `generate::module` génère un nouveau module :

    $ php symfony generate:module  application module

*Alias(es)*: `init-module`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `module` | `-` | Le nom du module




La tâche `generate:module` crée la structure de répertoire de base
pour le nouveau module dans l'application existante :

    ./symfony generate:module frontend article

La tâche peut aussi changer le nom de l'auteur trouvé dans `actions.class.php`,
si vous l'avez configuré dans `config/properties.ini`:

    [symfony]
      name=blog
      author=Fabien Potencier <fabien.potencier@sensio.com>

Vous pouvez personnaliser le squelette par défaut utilisé par la tâche en créant
un répertoire `%sf_data_dir%/skeleton/module`.

La tâche crée également un talon de test fonctionnel nommé
`%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php`
qui ne passe pas par défaut.

Si un module existe déjà avec le même nom dans l'application,
cela lève une `sfCommandException`.

### ~`generate::project`~

La tâche `generate::project` génère un nouveau projet :

    $ php symfony generate:project [--orm="..."] [--installer="..."] name [author]

*Alias(es)*: `init-project`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du projet
| `author` | `Your name here` | L'auteur du projet


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--orm` | `Doctrine` | L'ORM à utiliser par défaut
| `--installer` | `-` | Un script d'installation à exécuter


La tâche `generate:project` crée la structure de répertoire de base
pour le nouveau projet dans le répertoire actuel :

    ./symfony generate:project blog

Si le répertoire actuel contient déjà un projet symfony,
cela lève une `sfCommandException`.

Par défaut, la tâche configure Dostrine comme ORM. Si vous voulez utiliser
Propel, utilisez l'option `--orm` :

    ./symfony generate:project blog --orm=Propel

Si vous ne voulez pas utiliser un ORM, passez `none` à l'option `--orm` :

    ./symfony generate:project blog --orm=none

Vous pouvez aussi passer l'option `--installer` pour personnaliser davantage le
projet :

    ./symfony generate:project blog --installer=./installer.php

Vous pouvez éventuellement inclure un second argument `author` pour spécifier le nom à
utiliser comme auteur quand symfony génère de nouvelles classes :

    ./symfony generate:project blog "Jack Doe"

### ~`generate::task`~

La tâche `generate::task` crée une classe squelette pour une nouvelle tâche :

    $ php symfony generate:task [--dir="..."] [--use-database="..."] [--brief-description="..."] task_name



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `task_name` | `-` | Le nom de la tâche (peut contenir des espaces de nommages)


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--dir` | `lib/task` | Le répertoire dans lequel sera créé la tâche
| `--use-database` | `doctrine` | Si la tâche a besoin de l'initialisation de modèles pour accéder à la base de données
| `--brief-description` | `-` | Une brève description de la tâche (figure dans la liste des tâches)


La tâche `generate:task` crée une nouvelle classe sfTask class basée sur le nom passé en
argument :

    ./symfony generate:task namespace:name

Le squelette de la tâche `namespaceNameTask.class.php` est créé sous le répertoire
`lib/task/`. Notez que l'espace de nom est facultative.

Si vous voulez créer le fichier dans un autre répertoire (par rapport au dossier
racine du projet), passez-le dans l'option `--dir`. Ce répertoire sera créé
s'il n'existe pas déjà.

    ./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task

Si vous souhaitez que la tâche se connecte par défaut à une autre connexion que `doctrine`, indiquez
le nom de cette connexion avec l'option `--use-database` :

    ./symfony generate:task namespace:name --use-database=main

L'option `--use-database` peut aussi être utilisée pour désactiver l'initialisation
de la base de données dans la tâche générée :

    ./symfony generate:task namespace:name --use-database=false

Vous pouvez aussi spécifier une description :

    ./symfony generate:task namespace:name --brief-description="Does interesting things"

`i18n`
------

### ~`i18n::extract`~

La tâche `i18n::extract` extrait les chaines i18n à partir des fichiers php :

    $ php symfony i18n:extract [--display-new] [--display-old] [--auto-save] [--auto-delete] application culture



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `culture` | `-` | La culture cible


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--display-new` | `-` | Extrait toutes les nouvelles chaines
| `--display-old` | `-` | Extrait toutes les anciennes chaines
| `--auto-save` | `-` | Sauve les nouvelles chaines
| `--auto-delete` | `-` | Supprime les anciennes chaines


La tâche `i18n:extract` extrait les chaines i18n à partir des fichiers de votre projet
pour l'application donnée et la culture cible :

    ./symfony i18n:extract frontend fr

Par défaut, la tâche affiche uniquement le nombre de nouvelles et d'anciennes chaînes
qu'elle a trouvées dans le projet actuel.

Si vous souhaitez afficher les nouvelles chaînes de caractères, utilisez l'option `--display-new` :

    ./symfony i18n:extract --display-new frontend fr

Pour les sauver dans le catalogue de message i18n, utilisez l'option `--auto-save` :

    ./symfony i18n:extract --auto-save frontend fr

Si vous voulez afficher des chaînes qui sont présentes dans le catalogue de message
i18n, mais qui ne se trouvent plus dans l'application, utiliser
l'option `--display-old` :

    ./symfony i18n:extract --display-old frontend fr

Pour supprimer automatiquement les anciennes chaînes, utilisez `--auto-delete`, mais
faites attention, surtout si vous avez des traductions pour les plugins, car elles
apparaissent comme des anciennes chaînes alors qu'elles ne le sont pas :

    ./symfony i18n:extract --auto-delete frontend fr

### ~`i18n::find`~

La tâche `i18n::find` trouve les chaines non "prêtes i18n" dans une application :

    $ php symfony i18n:find [--env="..."] application



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--env` | `dev` | L'environnement


La tâche `i18n:find` trouve les chaines non internationalisées intégrées dans les Templates :

    ./symfony i18n:find frontend

Cette tâche est en mesure de trouver des chaînes non internationalisées dans du pur HTML et dans le code PHP :

    <p>Non i18n text</p>
    <p><?php echo 'Test' ?></p>

Comme la tâche retourne toutes les chaînes intégrées en PHP, vous pouvez avoir quelques positifs à tort (en particulier
si vous utilisez la syntaxe de la chaîne pour des arguments de Helper).

`log`
-----

### ~`log::clear`~

La tâche `log::clear` vide les fichiers de journalisation :

    $ php symfony log:clear  

*Alias(es)*: `log-purge`





La tâche `log:clear` vide tous les fichiers de journalisation de symfony :

    ./symfony log:clear

### ~`log::rotate`~

La tâche `log::rotate` fait une rotation des fichiers de journalisation de l'application :

    $ php symfony log:rotate [--history="..."] [--period="..."] application env

*Alias(es)*: `log-rotate`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `env` | `-` | Le nom de l'environnement


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--history` | `10` | Le nombre maximum d'anciens fichiers de journalisation à garder
| `--period` | `7` | La période en jour


La tâche `log:rotate` fait une rotation des fichiers de journalisation pour un
environnement donné :

    ./symfony log:rotate frontend dev

Vous pouvez spécifier l'option `period` ou l'option `history` :

    ./symfony log:rotate frontend dev --history=10 --period=7

`plugin`
--------

### ~`plugin::add-channel`~

La tâche `plugin::add-channel` ajoute un nouveau canal PEAR :

    $ php symfony plugin:add-channel  name



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du canal




La tâche `plugin:add-channel` ajoute un nouveau canal PEAR :

    ./symfony plugin:add-channel symfony.plugins.pear.example.com

### ~`plugin::install`~

La tâche `plugin::install` installe un plugin :

    $ php symfony plugin:install [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] [-d|--install_deps] [--force-license] name

*Alias(es)*: `plugin-install`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du plugin


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--stability`<br />`(-s)` | `-` | La stabilité préférée (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | La version préférée
| `--channel`<br />`(-c)` | `-` | Le nom du canal PEAR
| `--install_deps`<br />`(-d)` | `-` | S'il faut forcer l'installation des dépendances requises
| `--force-license` | `-` | S'il faut forcer l'installation même si la licence n'est pas MIT


La tâche `plugin:install` installe un plugin :

    ./symfony plugin:install sfGuardPlugin

Pa r défaut, cela installe la dernière version `stable`.

Si vous voulez installer un plugin qui n'est pas encore stable,
utilisez l'option `stability` :

    ./symfony plugin:install --stability=beta sfGuardPlugin
    ./symfony plugin:install -s beta sfGuardPlugin

Vous pouvez aussi forcer l'installation d'une version spécifique :

    ./symfony plugin:install --release=1.0.0 sfGuardPlugin
    ./symfony plugin:install -r 1.0.0 sfGuardPlugin

Pour forcer l'installation de les dépendances requises, utilisez l'option `install_deps` :

    ./symfony plugin:install --install-deps sfGuardPlugin
    ./symfony plugin:install -d sfGuardPlugin

Par défaut, le canal PEAR utilisé est `symfony-plugins`
(plugins.symfony-project.org).

Vous pouvez spécifier un autre canal avec l'option `channel` :

    ./symfony plugin:install --channel=mypearchannel sfGuardPlugin
    ./symfony plugin:install -c mypearchannel sfGuardPlugin

Vous pouvez également installer les paquets PEAR hébergé sur un site web :

    ./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz

Ou des paquets PEAR en local :

    ./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz

Si le plugin contient une partie du contenu Web (images, feuilles de style ou javascripts),
la tâche crée un lien symbolique `%name%` de ces actifs sous `web/`.
Sur Windows, la tâche copie tous les fichiers sur le répertoire `web/%name%`.

### ~`plugin::list`~

La tâche `plugin::list` liste les plugins installés :

    $ php symfony plugin:list  

*Alias(es)*: `plugin-list`





La tâche `plugin:list` liste les plugins installés :

    ./symfony plugin:list

Cela donne également le canal et la version pour chaque plugin.

### ~`plugin::publish-assets`~

La tâche `plugin::publish-assets` publie les actifs web pour tous les plugins :

    $ php symfony plugin:publish-assets [--core-only] [plugins1] ... [pluginsN]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `plugins` | `-` | Publier les actifs de ce plugin


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--core-only` | `-` | S'il est défini, seuls les plugins du noyau publieront leurs actifs


La tâche `plugin:publish-assets` publiera les actifs web de tous les plugins.

    ./symfony plugin:publish-assets

En fait, cela enverra l'événement `plugin.post_install` à chaque plugin.

Vous pouvez spécifier quel(s) plugin(s) doivent installer leurs actifs en passant
les noms de ces plugins comme arguments :

    ./symfony plugin:publish-assets sfDoctrinePlugin

### ~`plugin::uninstall`~

La tâche `plugin::uninstall` désinstalle un plugin :

    $ php symfony plugin:uninstall [-c|--channel="..."] [-d|--install_deps] name

*Alias(es)*: `plugin-uninstall`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du plugin


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--channel`<br />`(-c)` | `-` | Le nom du canal PEAR
| `--install_deps`<br />`(-d)` | `-` | S'il faut forcer l'installation des dépendances


La tâche `plugin:uninstall` désinstalle un plugin :

    ./symfony plugin:uninstall sfGuardPlugin

Le canal par défaut est `symfony`.

Vous pouvez aussi désinstaller un plugin qui a un canal différent :

    ./symfony plugin:uninstall --channel=mypearchannel sfGuardPlugin

    ./symfony plugin:uninstall -c mypearchannel sfGuardPlugin

Ou vous pouvez utiliser la notation `canal/paquet` :

    ./symfony plugin:uninstall mypearchannel/sfGuardPlugin

Vous pouvez obtenir le nom du canal PEAR d'un plugin en lançant la
tâche `plugin:list`.

Si le plugin contient une partie du contenu Web (images, feuilles de style ou javascripts),
la tâche supprime également le lien symbolique `web/%name%` ( sous *nix)
ou le répertoire (sous Windows).

### ~`plugin::upgrade`~

La tâche `plugin::upgrade` met à jour le plugin :

    $ php symfony plugin:upgrade [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] name

*Alias(es)*: `plugin-upgrade`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du plugin


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--stability`<br />`(-s)` | `-` | La stabilité préférée (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | La version préférée
| `--channel`<br />`(-c)` | `-` | Le nom du canal PEAR


La tâche `plugin:upgrade` essaie de mettre à jour le plugin :

    ./symfony plugin:upgrade sfGuardPlugin

Le canal par défaut est `symfony`.

Si le plugin contient une partie de contenu Web (images, feuilles de style ou javascripts),
la tâche met également à jour le contenu du répertoire `web/%name%` sous Windows.

Voir `plugin:install` pour plus d'informations sur le format du nom du plugin et les options.

`project`
---------

### ~`project::clear-controllers`~

La tâche `project::clear-controllers` efface tous les contrôleurs qui ne sont pas de l'environnement de production :

    $ php symfony project:clear-controllers  

*Alias(es)*: `clear-controllers`





La tâche `project:clear-controllers`efface tous les contrôleurs qui ne sont pas de l'environnement
de production :

    ./symfony project:clear-controllers

Vous pouvez utiliser cette tâche sur un serveur de production pour supprimer tous les
scripts de contrôleurs frontaux, sauf ceux de production.

Si vous avez deux applications nommées `frontend` et `backend`,
vous avez quatre scripts de contrôleur par défaut dans `web/` :

    index.php
    frontend_dev.php
    backend.php
    backend_dev.php

Après l'exécution de la tâche `project:clear-controllers`, deux scripts
de contrôleurs frontaux sont laissés dans `web/` :

    index.php
    backend.php

Ces deux contrôleurs sont sans danger parce que le mode débogage et la barre d'outil
de débogage sont désactivés.

### ~`project::deploy`~

La tâche `project::deploy` déploie un projet vers un autre serveur :

    $ php symfony project:deploy [--go] [--rsync-dir="..."] [--rsync-options[="..."]] server

*Alias(es)*: `sync`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `server` | `-` | Le nom du serveur


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--go` | `-` | Faire le déploiement
| `--rsync-dir` | `config` | Le répertoire où l'on peut voir les fichiers rsync*.txt
| `--rsync-options` | `-azC --force --delete --progress` | Les options à passer à l'exécutable rsync


La tâche `project:deploy` déploie un projet vers un autre serveur :

    ./symfony project:deploy production

Le serveur doit être configuré dans `config/properties.ini`:

    [production]
      host=www.example.com
      port=22
      user=fabien
      dir=/var/www/sfblog/
      type=rsync

Pour automatiser le déploiement, la tâche utilise rsync sur SSH.
Vous devez configurer l'accès SSH avec une clé ou configurer le mot de passe
dans `config/properties.ini`.

Par défaut, la tâche est en mode test. Pour effectuer un déploiement réel, vous
devez passer l'option `--go` :

    ./symfony project:deploy --go production

Les fichiers et les répertoires configuré dans `config/rsync_exclude.txt` ne sont
pas déployés :

    .svn
    /web/uploads/*
    /cache/*
    /log/*

Vous pouvez aussi créer les fichiers `rsync.txt` et `rsync_include.txt`.

Si vous avez besoin de personnaliser les fichiers `rsync*.txt` basé sur le serveur,
vous pouvez passer l'option `rsync-dir` :

    ./symfony project:deploy --go --rsync-dir=config/production production

Enfin, vous pouvez spécifier les options passées à l'exécutable rsync, en utilisant
l'option `rsync-options` (par défaut les options sont `-azC --force --delete --progress`) :

    ./symfony project:deploy --go --rsync-options=-avz

### ~`project::disable`~

La tâche `project::disable` désactive une application dans un environnement donné :

    $ php symfony project:disable  env [app1] ... [appN]

*Alias(es)*: `disable`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `env` | `-` | Le nom de l'environnement
| `app` | `-` | Le nom de l'application




La tâche `project:disable` désactive un environnement :

    ./symfony project:disable prod

Vous pouvez également spécifier individuellement les applications à désactiver dans cet
environnement :

    ./symfony project:disable prod frontend backend

### ~`project::enable`~

La tâche `project::enable` active une application dans un environnement donné :

    $ php symfony project:enable  env [app1] ... [appN]

*Alias(es)*: `enable`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `env` | `-` | Le nom de l'environnement
| `app` | `-` | Le nom de l'application




La tâche `project:enable` active un environnement spécifique :

    ./symfony project:enable frontend prod

Vous pouvez également spécifier individuellement les applications à activer dans cet
environnement :

    ./symfony project:enable prod frontend backend

### ~`project::optimize`~

La tâche `project::optimize` optimise le projet pour de meilleures performances :

    $ php symfony project:optimize  application [env]



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `env` | `prod` | Le nom de l'environnement




La tâche `project:optimize` optimise le projet pour de meilleures performances :

    ./symfony project:optimize frontend prod

Cette tâche ne devrait être utilisé que sur un serveur de production. N'oubliez pas de ré-exécuter
la tâche à chaque fois que le projet change.

### ~`project::permissions`~

La tâche `project::permissions` corrige les permissions des répertoires de symfony :

    $ php symfony project:permissions  

*Alias(es)*: `permissions, fix-perms`





La tâche `project:permissions` corrige les permissions des répertoires de symfony :

    ./symfony project:permissions

### ~`project::send-emails`~

La tâche `project::send-emails` envoie les emails stockés dans une file d'attente :

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] [--message-limit[="..."]] [--time-limit[="..."]]





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--message-limit` | `0` | Le nombre maximum de message à envoyer
| `--time-limit` | `0` | La limite de temps pour l'envoi des messages (en secondes)


La tâche `project:send-emails` envoie des emails stockés dans une file d'attente :

    php symfony project:send-emails

Vous pouvez limiter le nombre de messages à envoyer : 

    php symfony project:send-emails --message-limit=10 

Ou la limite de temps (en secondes) : 

    php symfony project:send-emails --time-limit=10

### ~`project::upgrade1.3`~

La tâche `project::upgrade1.3` met à jour le projet symfony dans la version 1.3 (depuis la 1.2):

    $ php symfony project:upgrade1.3  







La tâche `project:upgrade1.3` met à jour le projet symfony basé sur la version 1.2
vers la version symfony 1.3.

    ./symfony project:upgrade1.3

Merci de lire le fichier UPGRADE_TO_1_3 pour savoir ce que fait cette tâche.
 
### ~`project::validate`~

La tâche `project::validate` trouve les utilisations dépréciées dans le projet :

    $ php symfony project:validate







La tâche `project:validate` détecte les utilisations dépréciées dans votre projet.

    ./symfony project:validate

La tâche liste tous les fichiers qui ont besoin d'être changés avant de passer à
symfony 1.4.

`propel`
--------

### ~`propel::build`~

La tâche `propel::build` génère le code basé sur votre schéma :

    $ php symfony propel:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-load[="..."]] [--and-append[="..."]] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--no-confirmation` | `-` | S'il faut forcer la suppression de la base de données
| `--all` | `-` | Tout générer et réinitialiser la base de données
| `--all-classes` | `-` | Générer toutes les classes
| `--model` | `-` | Générer les classes modèle
| `--forms` | `-` | Générer les classes formulaire
| `--filters` | `-` | Générer les classes filtre
| `--sql` | `-` | Générer SQL
| `--db` | `-` | Supprimer, créer, et insérer SQL
| `--and-load` | `-` | Charger les données de test (plusieurs valeurs sont permises)
| `--and-append` | `-` | Insérer les données de test (plusieurs valeurs sont permises)


La tâche `propel:build` génère le code basé sur votre schéma :

    ./symfony propel:build

Vous devez spécifier ce que vous voulez construire. Par exemple, si vous voulez le modèle
et les classes du formulaire soient construits, utiliser les options `--model` et `--forms` :

    ./symfony propel:build --model --forms

Vous pouvez utiliser l'option de raccourci `--all` si vous voulez que toutes les classes et
les fichiers SQL files soient générés et que la base de données soit reconstruite :

    ./symfony propel:build --all

C'est équivalent à l'exécution des têches suivantes :

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

Vous pouvez aussi générer un unique fichier de classe en utilisant l'option raccourci
`--all-classes`. Lorsque cette option est utilisée seule, la base de données ne sera pas modifiée.

    ./symfony propel:build --all-classes

L'option `--and-load` chargera les données des répertoires `data/fixtures/` du
projet et des plugins :

    ./symfony propel:build --db --and-load

Pour spécifier quelles données de test doivent être chargées, ajoutez le paramètre à l'option `--and-load` :

    ./symfony propel:build --all --and-load="data/fixtures/dev/"

Pour ajouter des données de test sans supprimer aucun enregistrement de la base de données, ajoutez
l'option `--and-append` :

    ./symfony propel:build --all --and-append

### ~`propel::build-all`~

La tâche `propel::build-all` génère le modèle Propel et les classes de formulaire, SQL et initialise la base de données :

    $ php symfony propel:build-all [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] 

*Alias(es)*: `propel-build-all`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--connection` | `propel` | Le nom de la connexion
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--classes-only`<br />`(-C)` | `-` | Ne pas initialiser la base de données
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:build-all` est un raccourci de cinq autres tâches :

    ./symfony propel:build-all

La tâche est équivalent à :

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

Voir les pages d'aide de ces tâches pour plus d'informations.

Pour contourner la confirmation, vous pouvez passer l'option `no-confirmation` :

    ./symfony propel:buil-all --no-confirmation

Pour générer toutes les classes en contournant l'initialisation de la base de données, utilisez l'option
`classes-only` :

    ./symfony propel:build-all --classes-only

### ~`propel::build-all-load`~

La tâche `propel::build-all-load` génère le modèle Propel et les classes de formulaire, SQL, initialise la base de données et charge les données :

    $ php symfony propel:build-all-load [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] [--append] [--dir="..."] 

*Alias(es)*: `propel-build-all-load`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `dev` | L'environnement
| `--connection` | `propel` | Le nom de la connexion
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--skip-forms`<br />`(-F)` | `-` | Passer la génération des formulaires
| `--classes-only`<br />`(-C)` | `-` | Ne pas initialiser la base de données
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données
| `--dir` | `-` | Les répertoires à regarder pour les données de test (plusieurs valeurs sont permises)


La tâche `propel:build-all-load` est un raccourci de deux autres tâches :

    ./symfony propel:build-all-load

La tâche est équivalent à :

    ./symfony propel:build-all
    ./symfony propel:data-load

Voir les pages d'aide de ces tâches pour plus d'informations.

Pour contourner la confirmation, vous pouvez passer l'option
`no-confirmation` :

    ./symfony propel:buil-all-load --no-confirmation

### ~`propel::build-filters`~

La tâche `propel::build-filters` crée les classes de formulaire de filtre pour le modèle actuel :

    $ php symfony propel:build-filters [--connection="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--connection` | `propel` | Le nom de la connexion
| `--model-dir-name` | `model` | Le nom du répertoire du modèle
| `--filter-dir-name` | `filter` | Le nom du répertoire du formulaire de filtre
| `--application` | `1` | Le nom de l'application
| `--generator-class` | `sfPropelFormFilterGenerator` | La classe génératrice


La tâche `propel:build-filters` crée les classes de formulaire de filtre depuis le schéma :

    ./symfony propel:build-filters

La tâche lit l'information du schéma dans `config/*schema.xml` et/ou
`config/*schema.yml` du le projet et de tous les plugins installés.

La tâche utilise la connexion `propel` définie dans `config/databases.yml`.
Vous pouvez utiliser une autre connexion en utilisant l'option `--connection` :

    ./symfony propel:build-filters --connection="name"

Les fichiers des classes du formulaire de filtre du modèle sont créés dans `lib/filter`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/filter`.
Elle remplace uniquement les classes de base générées dans `lib/filter/base`.

### ~`propel::build-forms`~

La tâche `propel::build-forms` crée les classes de formulaire pour le modèle actuel :

    $ php symfony propel:build-forms [--connection="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--connection` | `propel` | Le nom de la connexion
| `--model-dir-name` | `model` | Le nom du répertoire du modèle
| `--form-dir-name` | `form` | Le nom du répertoire du formulaire
| `--application` | `1` | Le nom de l'application
| `--generator-class` | `sfPropelFormGenerator` | La classe génératrice


La tâche `propel:build-forms` crée les classes de formulaire depuis le schéma :

    ./symfony propel:build-forms

La tâche lit l'information du schéma dans `config/*schema.xml` et/ou
`config/*schema.yml` du projet et de tous les plugins installés.

La tâche utilise la connexion `propel` définie dans `config/databases.yml`.
Vous pouvez utiliser une autre connexion en utilisant l'option `--connection` :

    ./symfony propel:build-forms --connection="name"

Les fichiers des classes du formulaire du modèle sont créés dans `lib/form`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/form`.
Elle remplace uniquement les classes de base générées dans `lib/form/base`.

### ~`propel::build-model`~

La tâche `propel::build-model` crée les classes pour le modèle actuel :

    $ php symfony propel:build-model [--phing-arg="..."] 

*Alias(es)*: `propel-build-model`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:build-model` crée les classes modèle depuis le schéma:

    ./symfony propel:build-model

La tâche lit l'information du schéma dans `config/*schema.xml` et/ou
`config/*schema.yml` du projet et de tous les plugins installés.

Vous mélangez et faites correspondre les fichiers de schéma YML et XML. La tâche vous permet de convertir
les YML en XML avant d'appeler la tâche Propel.

Les fichiers des classes du modèle sont créés dans `lib/model`.

Cette tâche ne remplace jamais les classes personnalisées dans `lib/model`.
Elle remplace uniquement les fichiers dans `lib/model/om` et dans `lib/model/map`.

### ~`propel::build-schema`~

La tâche `propel::build-schema` crée le schéma depuis la base de données existante :

    $ php symfony propel:build-schema [--application[="..."]] [--env="..."] [--connection="..."] [--xml] [--phing-arg="..."] 

*Alias(es)*: `propel-build-schema`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `cli` | L'environnement
| `--connection` | `-` | Le nom de la connexion
| `--xml` | `-` | Créer un schéma XML à la place d'un YML
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:build-schema` scrute la base de données pour créer le schéma :

    ./symfony propel:build-schema

Par défaut, la tâche crée un fichier YML, mais vous pouvez créer un fichier XML :

    ./symfony --xml propel:build-schema

Le format XML contient plus d'informations qu'un YML.

### ~`propel::build-sql`~

La tâche `propel::build-sql` crée le SQL pour le modèle actuel :

    $ php symfony propel:build-sql [--phing-arg="..."] 

*Alias(es)*: `propel-build-sql`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:build-sql` crée les instructions SQL pour la création des tables :

    ./symfony propel:build-sql

Le SQL généré est optimisé pour la base de données configurée dans `config/propel.ini`:

    propel.database = mysql

### ~`propel::data-dump`~

La tâche `propel::data-dump` décharge les données vers le répertoire fixtures :

    $ php symfony propel:data-dump [--application[="..."]] [--env="..."] [--connection="..."] [--classes="..."] [target]

*Alias(es)*: `propel-dump-data`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `target` | `-` | Le nom du fichier cible


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `cli` | L'environnement
| `--connection` | `propel` | Le nom de la connexion
| `--classes` | `-` | Les noms des classes à décharger (séparés par des virgules)


La tâche `propel:data-dump` décharge les données de la base de données :

    ./symfony propel:data-dump > data/fixtures/dump.yml

Par défaut, la tâche extrait les données dans un format standard,
mais vous pouvez passer un nom de fichier comme second argument :

    ./symfony propel:data-dump dump.yml

La tâche déchargera les données dans `data/fixtures/%target%`
(data/fixtures/dump.yml dans l'exemple).

Le fichier déchargé est dans le format YML et peut être ré-importer en utilisant
la tâche `propel:data-load`.

Par défaut, la tâche utilise la connexion `propel` définie dans `config/databases.yml`.
Vous pouvez utiliser une autre connexion en utilisant l'option `connection` :

    ./symfony propel:data-dump --connection="name"

Si vous voulez seulement décharger certaines classes, utilisez l'option `classes` :

    ./symfony propel:data-dump --classes="Article,Category"

Si vous voulez utiliser une configuration de base de données spécifique pour une application, vous pouvez utiliser
l'option `application` :

    ./symfony propel:data-dump --application=frontend

### ~`propel::data-load`~

La tâche `propel::data-load` charge les données de test YAML :

    $ php symfony propel:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [dir_or_file1] ... [dir_or_fileN]

*Alias(es)*: `propel-load-data`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `dir_or_file` | `-` | Répertoire ou fichier à charger


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `cli` | L'environnement
| `--append` | `-` | Ne pas supprimer les données actuelles dans la base de données
| `--connection` | `propel` | Le nom de la connexion


La tâche `propel:data-load` charge les données de tests dans la base de données :

    ./symfony propel:data-load

La tâche charge les données depuis tous les fichiers trouvés dans `data/fixtures/`.

Si vous voulez charger les données depuis un répertoire ou un fichier spécifique, vous pouvez les ajoutez
comme arguments :

    ./symfony propel:data-load data/fixtures/dev data/fixtures/users.yml

La tâche utilise la connexion `propel` définie dans `config/databases.yml`.
Vous pouvez utiliser une autre connexion en utilisant l'option `--connection` :

    ./symfony propel:data-load --connection="name"

Si vous ne voulez pas que la tâche supprime les données existantes dans la base de données,
utiliser l'option `--append` :

    ./symfony propel:data-load --append

Si vous voulez utiliser une configuration de base de données spécifique d'une application, vous pouvez utiliser
l'option `application` :

    ./symfony propel:data-load --application=frontend

### ~`propel::generate-admin`~

La tâche `propel::generate-admin` génère le module de l'administration Propel :

    $ php symfony propel:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `route_or_model` | `-` | Le nom de la route ou la classe modèle


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--module` | `-` | Le nom du module
| `--theme` | `admin` | Le nom du thème
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `propel:generate-admin` génère le module de l'administration Propel :

    ./symfony propel:generate-admin frontend Article

La tâche crée un module dans l'application `%frontend%` pour
le modèle `%Article%`.

La tâche crée une route pour vous dans l'application `routing.yml`.

Vous pouvez aussi générer un module d'administration Propel en passant le nom de la route :

    ./symfony propel:generate-admin frontend article

La tâche crée un module dans l'application `%frontend%` pour la
définition de la route `%article%` trouvée dans `routing.yml`.

Pour travailler proprement avec les filtres et les actions Batch, vous avez besoin d'ajouter
l'option `with_wildcard_routes` à la route :

    article:
      class: sfPropelRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`propel::generate-module`~

La tâche `propel::generate-module` génère le module Propel :

    $ php symfony propel:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-propel-route] [--env="..."] [--actions-base-class="..."] application module model

*Alias(es)*: `propel-generate-crud, propel:generate-crud`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `module` | `-` | Le nom du module
| `model` | `-` | Le nom de la classe modèle


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--theme` | `default` | Le nom du thème
| `--generate-in-cache` | `-` | Générer le module dans le cache
| `--non-verbose-templates` | `-` | Générer des Templates non-bavard
| `--with-show` | `-` | Générer la méthode show
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--route-prefix` | `-` | Le préfixe de la route
| `--with-propel-route` | `-` | Si vous utilisez la route Propel
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `propel:generate-module` génère le module Propel :

    ./symfony propel:generate-module frontend article Article

La tâche crée le module `%module%` dans l'application `%application%`
pour la classe modèle `%model%`.

Vous pouvez également créer un module vide qui hérite de ses actions et ses Templates à partir
d'un module runtime généré dans `%sf_app_cache_dir%/modules/auto%module%` en
utilisant l'option `--generate-in-cache` :

    ./symfony propel:generate-module --generate-in-cache frontend article Article

Le générateur peut utiliser un thème personnalisé en utilisant l'option `--theme` :

    ./symfony propel:generate-module --theme="custom" frontend article Article

De cette façon, vous pouvez créer votre propre générateur de modules avec vos propres conventions.

Vous pouvez également modifier la classe des actions de base par défaut (par défaut à sfActions) des
modules générés :

    ./symfony propel:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`propel::generate-module-for-route`~

La tâche `propel::generate-module-for-route` génère le module Propel pour la définition d'une route :

    $ php symfony propel:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `route` | `-` | Le nom de la route


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--theme` | `default` | Le nom du thème
| `--non-verbose-templates` | `-` | Générer des Templates non-bavard
| `--singular` | `-` | Le nom au singulier
| `--plural` | `-` | Le nom au pluriel
| `--env` | `dev` | L'environnement
| `--actions-base-class` | `sfActions` | La classe de base pour les actions


La tâche `propel:generate-module-for-route` génère le module Propel pour la définition d'une route :

    ./symfony propel:generate-module-for-route frontend article

La tâche crée un module dans l'application `%frontend%` pour
la définition de la route `%article%` trouvée dans `routing.yml`.

### ~`propel::graphviz`~

La tâche `propel::graphviz` génère un graphique graphviz du modèle objet actuel :

    $ php symfony propel:graphviz [--phing-arg="..."] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:graphviz` crée une visualisation d'un
graphviz DOT pour le dessin graphique automatique du modèle d'objet :

    ./symfony propel:graphviz

### ~`propel::init-admin`~

La tâche `propel::init-admin` initialise un module admin Propel :

    $ php symfony propel:init-admin [--theme="..."] application module model

*Alias(es)*: `propel-init-admin`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `module` | `-` | Le nom du module
| `model` | `-` | Le nom de la classe modèle


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--theme` | `default` | Le nom du thème


La tâche `propel:init-admin` génère un module admin Propel :

    ./symfony propel:init-admin frontend article Article

La tâche crée un module `%module%` dans l'application `%application%`
pour la classe modèle `%model%`.

Le module créé est un module vide qui hérite de ses actions et de ses Templates depuis
un module généré runtime dans `%sf_app_cache_dir%/modules/auto%module%`.

Le générateur peut utiliser un thème personnalisé en utilisant l'option `--theme` :

    ./symfony propel:init-admin --theme="custom" frontend article Article

### ~`propel::insert-sql`~

La tâche `propel::insert-sql` insère du SQL pour le modèle actuel :

    $ php symfony propel:insert-sql [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [--phing-arg="..."] 

*Alias(es)*: `propel-insert-sql`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--application` | `1` | Le nom de l'application
| `--env` | `cli` | L'environnement
| `--connection` | `-` | Le nom de la connexion
| `--no-confirmation` | `-` | Ne pas demander de confirmation
| `--phing-arg` | `-` | Argument de phing arbitraire (plusieurs valeurs sont permises)


La tâche `propel:insert-sql` crée les tables de la base de données :

    ./symfony propel:insert-sql

La tâche se connecte à la base de données et exécute toutes les instructions SQL
trouvés dans le fichier `config/sql/*schema.sql`.

Avant l'exécution, la tâche vous demandera de confirmer l'exécution
de la suppression de toutes les données dans votre base de données.

Pour contourner la confirmation, vous pouvez passer l'option
`--no-confirmation` :

    ./symfony propel:insert-sql --no-confirmation

La tâche lit la configuration de la base de données depuis `databases.yml`.
Vous pouvez utiliser une application ou un environnement spécifique en passant
l'option `--application` ou `--env`.

Vous pouvez aussi utiliser l'option `--connection` si vous voulez
charger uniquement les instructions SQL pour une connexion donnée.

### ~`propel::schema-to-xml`~

La tâche `propel::schema-to-xml` crée un schema.xml depuis un schema.yml :

    $ php symfony propel:schema-to-xml  

*Alias(es)*: `propel-convert-yml-schema`





La tâche `propel:schema-to-xml` convertit un schéma YML en XML :

    ./symfony propel:schema-to-xml

### ~`propel::schema-to-yml`~

La tâche `propel::schema-to-yml` task crée un schema.yml depuis un schema.xml :

    $ php symfony propel:schema-to-yml  

*Alias(es)*: `propel-convert-xml-schema`





La tâche `propel:schema-to-yml` convertit un schéma XML en YML :

    ./symfony propel:schema-to-yml

`symfony`
---------

### ~`symfony::test`~

La tâche `symfony::test` lance une série de test symfony :

    $ php symfony symfony:test [-u|--update-autoloader] [-f|--only-failed] [--xml="..."] [--rebuild-all] 





| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--update-autoloader`<br />`(-u)` | `-` | Modifier la classe sfCoreAutoload
| `--only-failed`<br />`(-f)` | `-` | Exécuter seulement les tests qui ont échoué la dernière fois
| `--xml` | `-` | Le nom du fichier pour le fichier de journalisation JUnit compatible XML
| `--rebuild-all` | `-` | Reconstruire tous les fichiers générés de donnés de test


La tâche `test:all` lance une série de test symfony :

    ./symfony symfony:test

`test`
------

### ~`test::all`~

La tâche `test::all` lance tous les tests :

    $ php symfony test:all [-f|--only-failed] [--xml="..."] 

*Alias(es)*: `test-all`



| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--only-failed`<br />`(-f)` | `-` | Exécuter seulement les tests qui ont échoué la dernière fois
| `--xml` | `-` | Le nom du fichier pour le fichier de journalisation JUnit compatible XML


La tâche `test:all` lance tous les tests unitaires et fonctionnels :

    ./symfony test:all

La tâche lance tous les tests trouvés dans `test/`.

Si certains tests échouent, vous pouvez utiliser l'option `--trace` pour avoir plus
d'informations sur les erreurs :

      `./symfony test:all -t

Ou vous pouvez aussi essayer de corriger les problèmes en les lançant à la main ou avec
la tâche `test:unit` et la tâche `test:functional`.

Utilisez l'option `--only-failed` pour forcer la tâche à exécuter seulement les tests
qui ont échoué durant la précédente exécution :

      `./symfony test:all --only-failed

Voici comment cela fonctionne : la première fois, tous les tests sont exécutés comme d'habitude. Mais pour
les séries de tests ultérieurs, seuls les tests qui ont échoué la dernière fois sont exécutés. Comme vous
corriger votre code, certains tests vont passer, et seront supprimés lors des exécutions ultérieures.
Lorsque tous les tests passent à nouveau, la suite de test complet est exécutée ... vous pouvez ensuite
nettoyer et recommencer.

La tâche peut sortir un fichier de journalisation XML compatible JUnit avec
l'option `--xml` :

    ./symfony test:all --xml=log.xml

### ~`test::coverage`~

La tâche `test::coverage` sort la couverture de code des tests :

    $ php symfony test:coverage [--detailed] test_name lib_name



| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `test_name` | `-` | Un nom de fichier de tests ou un répertoire de test
| `lib_name` | `-` | Un nom de fichier lib ou un répertoire lib pour lequel vous voulez connaître la couverture


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--detailed` | `-` | Sortir une information détaillée


La tâche `test:coverage` sort la couverture de code
d'un fichier de test ou un répertoire de test donné
et un fichier lib ou un répertoire lib pour lequel vous voulez connaître la couverture
du code :

    ./symfony test:coverage test/unit/model lib/model

Pour sortir les lignes non couvertes, passez l'option `--detailed` :

    ./symfony test:coverage --detailed test/unit/model lib/model

### ~`test::functional`~

La tâche `test::functional` lance les tests fonctionnels :

    $ php symfony test:functional [--xml="..."] application [controller1] ... [controllerN]

*Alias(es)*: `test-functional`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `application` | `-` | Le nom de l'application
| `controller` | `-` | Le nom du contrôleur


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--xml` | `-` | Le nom du fichier pour  le fichier de journalisation XML compatible JUnit


La tâche `test:functional` lance les tests fonctionnels pour
une application donnée :

    ./symfony test:functional frontend

La tâche lance tous les tests trouvés dans `test/functional/%application%`.

Si certains tests échouent, vous pouvez utiliser l'option `--trace` pour avoir plus
d'informations sur les échecs :

      `./symfony test:functional frontend -t

Vous pouvez lancer tous les tests fonctionnels pour un contrôleur spécifique en
donnant le nom du contrôleur :

    ./symfony test:functional frontend article

Vous pouvez aussi lancer tous les tests fonctionnels pour plusieurs contrôleurs :

    ./symfony test:functional frontend article comment

La tâche peut sortir un fichier de journalisation XML compatible JUnit avec
l'option `--xml` :

    ./symfony test:functional --xml=log.xml

### ~`test::unit`~

La tâche `test::unit` lance les tests unitaires :

    $ php symfony test:unit [--xml="..."] [name1] ... [nameN]

*Alias(es)*: `test-unit`

| Argument | Par défaut | Description
| -------- | ---------- | -----------
| `name` | `-` | Le nom du test


| Option (raccourci) | Par défaut | Description
| ------------------ | ---------- | -----------
| `--xml` | `-` | Le nom du fichier pour le fichier de journalisation XML compatible JUnit


La tâche `test:unit` lance les tests unitaires :

    ./symfony test:unit

La tâche lance tous les tests trouvés dans `test/unit`.

Si certains tests échouent, vous pouvez utiliser l'option `--trace` pour avoir plus
d'informations sur les échecs :

      `./symfony test:unit -t

Vous pouvez lancer les tests unitaires pour un nom spécifique :

    ./symfony test:unit strtolower

Vous pouvez aussi lancer les tests unitaires pour plusieurs noms :

    ./symfony test:unit strtolower strtoupper

La tâche peut sortir un fichier de journalisation XML compatible JUnit avec
l'option `--xml` :

    ./symfony test:unit --xml=log.xml



