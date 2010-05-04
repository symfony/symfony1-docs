Tasks
=====

The symfony framework comes bundled with a command line interface tool.
Built-in tasks allow the developer to perform a lot of fastidious and
recurrent tasks in the life of a project.

If you execute the `symfony` CLI without any arguments, a list of available
tasks is displayed:

    $ php symfony

By passing the `-V` option, you get some information about the version of
symfony and the path of the symfony libraries used by the CLI:

    $ php symfony -V

The CLI tool takes a task name as its first argument:

    $ php symfony list

A task name can be composed of an optional namespace and a name, separated by
a colon (`:`):

    $ php symfony cache:clear

After the task name, arguments and options can be passed:

    $ php symfony cache:clear --type=template

The CLI tool supports both long options and short ones, with or without
values.

The `-t` option is a global option to ask any task to output more debugging
information.

<div class="pagebreak"></div>

Available Tasks
---------------

 * Global tasks
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

The `help` task displays help for a task:

    $ php symfony help [--xml] [task_name]



| Argument | Default | Description
| -------- | ------- | -----------
| `task_name` | `help` | The task name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--xml` | `-` | To output help as XML


The `help` task displays help for a given task:

    ./symfony help test:all

You can also output the help as XML by using the `--xml` option:

    ./symfony help test:all --xml

### ~`list`~

The `list` task lists tasks:

    $ php symfony list [--xml] [namespace]



| Argument | Default | Description
| -------- | ------- | -----------
| `namespace` | `-` | The namespace name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--xml` | `-` | To output help as XML


The `list` task lists all tasks:

    ./symfony list

You can also display the tasks for a specific namespace:

    ./symfony list test

You can also output the information as XML by using the `--xml` option:

    ./symfony list --xml

`app`
-----

### ~`app::routes`~

The `app::routes` task displays current routes for an application:

    $ php symfony app:routes  application [name]



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `name` | `-` | A route name




The `app:routes` displays the current routes for a given application:

    ./symfony app:routes frontend

`cache`
-------

### ~`cache::clear`~

The `cache::clear` task clears the cache:

    $ php symfony cache:clear [--app[="..."]] [--env[="..."]] [--type[="..."]] 

*Alias(es)*: `cc`



| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--app` | `-` | The application name
| `--env` | `-` | The environment
| `--type` | `all` | The type


The `cache:clear` task clears the symfony cache.

By default, it removes the cache for all available types, all applications,
and all environments.

You can restrict by type, application, or environment:

For example, to clear the `frontend` application cache:

    ./symfony cache:clear --app=frontend

To clear the cache for the `prod` environment for the `frontend` application:

    ./symfony cache:clear --app=frontend --env=prod

To clear the cache for all `prod` environments:

    ./symfony cache:clear --env=prod

To clear the `config` cache for all `prod` environments:

    ./symfony cache:clear --type=config --env=prod

The built-in types are: `config`, `i18n`, `routing`, `module`
and `template`.


`configure`
-----------

### ~`configure::author`~

The `configure::author` task configure project author:

    $ php symfony configure:author  author



| Argument | Default | Description
| -------- | ------- | -----------
| `author` | `-` | The project author




The `configure:author` task configures the author for a project:

    ./symfony configure:author "Fabien Potencier <fabien.potencier@symfony-project.com>"

The author is used by the generates to pre-configure the PHPDoc header for each generated file.

The value is stored in [config/properties.ini].

### ~`configure::database`~

The `configure::database` task configure database DSN:

    $ php symfony configure:database [--env[="..."]] [--name[="..."]] [--class[="..."]] [--app[="..."]] dsn [username] [password]



| Argument | Default | Description
| -------- | ------- | -----------
| `dsn` | `-` | The database dsn
| `username` | `root` | The database username
| `password` | `-` | The database password


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--env` | `all` | The environment
| `--name` | `propel` | The connection name
| `--class` | `sfPropelDatabase` | The database class name
| `--app` | `-` | The application name


The `configure:database` task configures the database DSN
for a project:

    ./symfony configure:database mysql:host=localhost;dbname=example root mYsEcret

By default, the task change the configuration for all environment. If you want
to change the dsn for a specific environment, use the `env` option:

    ./symfony configure:database --env=dev mysql:host=localhost;dbname=example_dev root mYsEcret

To change the configuration for a specific application, use the `app` option:

    ./symfony configure:database --app=frontend mysql:host=localhost;dbname=example root mYsEcret

You can also specify the connection name and the database class name:

    ./symfony configure:database --name=main --class=ProjectDatabase mysql:host=localhost;dbname=example root mYsEcret

WARNING: The `propel.ini` file is also updated when you use a `Propel` database
and configure for `all` environments with no `app`.

`doctrine`
----------

### ~`doctrine::build`~

The `doctrine::build` task generate code based on your schema:

    $ php symfony doctrine:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-migrate] [--and-load[="..."]] [--and-append[="..."]] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--no-confirmation` | `-` | Whether to force dropping of the database
| `--all` | `-` | Build everything and reset the database
| `--all-classes` | `-` | Build all classes
| `--model` | `-` | Build model classes
| `--forms` | `-` | Build form classes
| `--filters` | `-` | Build filter classes
| `--sql` | `-` | Build SQL
| `--db` | `-` | Drop, create, and either insert SQL or migrate the database
| `--and-migrate` | `-` | Migrate the database
| `--and-load` | `-` | Load fixture data (multiple values allowed)
| `--and-append` | `-` | Append fixture data (multiple values allowed)


The `doctrine:build` task generates code based on your schema:

    ./symfony doctrine:build

You must specify what you would like built. For instance, if you want model
and form classes built use the `--model` and `--forms` options:

    ./symfony doctrine:build --model --forms

You can use the `--all` shortcut option if you would like all classes and
SQL files generated and the database rebuilt:

    ./symfony doctrine:build --all

This is equivalent to running the following tasks:

    ./symfony doctrine:drop-db
    ./symfony doctrine:build-db
    ./symfony doctrine:build-model
    ./symfony doctrine:build-forms
    ./symfony doctrine:build-filters
    ./symfony doctrine:build-sql
    ./symfony doctrine:insert-sql

You can also generate only class files by using the `--all-classes` shortcut
option. When this option is used alone, the database will not be modified.

    ./symfony doctrine:build --all-classes

The `--and-migrate` option will run any pending migrations once the builds
are complete:

    ./symfony doctrine:build --db --and-migrate

The `--and-load` option will load data from the project and plugin
`data/fixtures/` directories:

    ./symfony doctrine:build --db --and-migrate --and-load

To specify what fixtures are loaded, add a parameter to the `--and-load` option:

    ./symfony doctrine:build --all --and-load="data/fixtures/dev/"

To append fixture data without erasing any records from the database, include
the `--and-append` option:

    ./symfony doctrine:build --all --and-append

### ~`doctrine::build-db`~

The `doctrine::build-db` task creates database for current model:

    $ php symfony doctrine:build-db [--application[="..."]] [--env="..."] [database1] ... [databaseN]

*Alias(es)*: `doctrine:create-db`

| Argument | Default | Description
| -------- | ------- | -----------
| `database` | `-` | A specific database


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:build-db` task creates one or more databases based on
configuration in `config/databases.yml`:

    ./symfony doctrine:build-db

You can specify what databases to create by providing their names:

    ./symfony doctrine:build-db slave1 slave2

### ~`doctrine::build-filters`~

The `doctrine::build-filters` task creates filter form classes for the current model:

    $ php symfony doctrine:build-filters [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--generator-class="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--model-dir-name` | `model` | The model dir name
| `--filter-dir-name` | `filter` | The filter form dir name
| `--generator-class` | `sfDoctrineFormFilterGenerator` | The generator class


The `doctrine:build-filters` task creates form filter classes from the schema:

    ./symfony doctrine:build-filters

This task creates form filter classes based on the model. The classes are
created in `lib/doctrine/filter`.

This task never overrides custom classes in `lib/doctrine/filter`.
It only replaces base classes generated in `lib/doctrine/filter/base`.

### ~`doctrine::build-forms`~

The `doctrine::build-forms` task creates form classes for the current model:

    $ php symfony doctrine:build-forms [--application[="..."]] [--env="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--generator-class="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--model-dir-name` | `model` | The model dir name
| `--form-dir-name` | `form` | The form dir name
| `--generator-class` | `sfDoctrineFormGenerator` | The generator class


The `doctrine:build-forms` task creates form classes from the schema:

    ./symfony doctrine:build-forms

This task creates form classes based on the model. The classes are created
in `lib/doctrine/form`.

This task never overrides custom classes in `lib/doctrine/form`.
It only replaces base classes generated in `lib/doctrine/form/base`.

### ~`doctrine::build-model`~

The `doctrine::build-model` task creates classes for the current model:

    $ php symfony doctrine:build-model [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:build-model` task creates model classes from the schema:

    ./symfony doctrine:build-model

The task read the schema information in `config/doctrine/*.yml`
from the project and all enabled plugins.

The model classes files are created in `lib/model/doctrine`.

This task never overrides custom classes in `lib/model/doctrine`.
It only replaces files in `lib/model/doctrine/base`.

### ~`doctrine::build-schema`~

The `doctrine::build-schema` task creates a schema from an existing database:

    $ php symfony doctrine:build-schema [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:build-schema` task introspects a database to create a schema:

    ./symfony doctrine:build-schema

The task creates a yml file in `config/doctrine`

### ~`doctrine::build-sql`~

The `doctrine::build-sql` task creates SQL for the current model:

    $ php symfony doctrine:build-sql [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:build-sql` task creates SQL statements for table creation:

    ./symfony doctrine:build-sql

The generated SQL is optimized for the database configured in `config/databases.yml`:

    doctrine.database = mysql

### ~`doctrine::clean-model-files`~

The `doctrine::clean-model-files` task delete all generated model classes for models which no longer exist in your YAML schema:

    $ php symfony doctrine:clean-model-files [--no-confirmation] 

*Alias(es)*: `doctrine:clean`



| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--no-confirmation` | `-` | Do not ask for confirmation


The `doctrine:clean-model-files` task deletes model classes that are not
represented in project or plugin schema.yml files:

    ./symfony doctrine:clean-model-files

### ~`doctrine::create-model-tables`~

The `doctrine::create-model-tables` task drop and recreate tables for specified models.:

    $ php symfony doctrine:create-model-tables [--application[="..."]] [--env="..."] [models1] ... [modelsN]



| Argument | Default | Description
| -------- | ------- | -----------
| `models` | `-` | The list of models


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `frontend` | The application name
| `--env` | `dev` | The environment


The `doctrine:create-model-tables` Drop and recreate tables for specified models:

    ./symfony doctrine:create-model-tables User

### ~`doctrine::data-dump`~

The `doctrine::data-dump` task dumps data to the fixtures directory:

    $ php symfony doctrine:data-dump [--application[="..."]] [--env="..."] [target]



| Argument | Default | Description
| -------- | ------- | -----------
| `target` | `-` | The target filename


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:data-dump` task dumps database data:

    ./symfony doctrine:data-dump

The task dumps the database data in `data/fixtures/%target%`.

The dump file is in the YML format and can be reimported by using
the `doctrine:data-load` task.

    ./symfony doctrine:data-load

### ~`doctrine::data-load`~

The `doctrine::data-load` task loads YAML fixture data:

    $ php symfony doctrine:data-load [--application[="..."]] [--env="..."] [--append] [dir_or_file1] ... [dir_or_fileN]



| Argument | Default | Description
| -------- | ------- | -----------
| `dir_or_file` | `-` | Directory or file to load


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--append` | `-` | Don't delete current data in the database


The `doctrine:data-load` task loads data fixtures into the database:

    ./symfony doctrine:data-load

The task loads data from all the files found in `data/fixtures/`.

If you want to load data from specific files or directories, you can append
them as arguments:

    ./symfony doctrine:data-load data/fixtures/dev data/fixtures/users.yml

If you don't want the task to remove existing data in the database,
use the `--append` option:

    ./symfony doctrine:data-load --append

### ~`doctrine::delete-model-files`~

The `doctrine::delete-model-files` task delete all the related auto generated files for a given model name.:

    $ php symfony doctrine:delete-model-files [--no-confirmation] name1 ... [nameN]



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The name of the model you wish to delete all related files for.


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--no-confirmation` | `-` | Do not ask for confirmation


The `doctrine:delete-model-files` task deletes all files associated with certain
models:

    ./symfony doctrine:delete-model-files Article Author

### ~`doctrine::dql`~

The `doctrine::dql` task execute a DQL query and view the results:

    $ php symfony doctrine:dql [--application[="..."]] [--env="..."] [--show-sql] [--table] dql_query [parameter1] ... [parameterN]



| Argument | Default | Description
| -------- | ------- | -----------
| `dql_query` | `-` | The DQL query to execute
| `parameter` | `-` | Query parameter


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--show-sql` | `-` | Show the sql that would be executed
| `--table` | `-` | Return results in table format


The `doctrine:dql` task executes a DQL query and displays the formatted
results:

    ./symfony doctrine:dql "FROM User"

You can show the SQL that would be executed by using the `--show-sql` option:

    ./symfony doctrine:dql --show-sql "FROM User"

Provide query parameters as additional arguments:

    ./symfony doctrine:dql "FROM User WHERE email LIKE ?" "%symfony-project.com"

### ~`doctrine::drop-db`~

The `doctrine::drop-db` task drops database for current model:

    $ php symfony doctrine:drop-db [--application[="..."]] [--env="..."] [--no-confirmation] [database1] ... [databaseN]



| Argument | Default | Description
| -------- | ------- | -----------
| `database` | `-` | A specific database


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--no-confirmation` | `-` | Whether to force dropping of the database


The `doctrine:drop-db` task drops one or more databases based on
configuration in `config/databases.yml`:

    ./symfony doctrine:drop-db

You will be prompted for confirmation before any databases are dropped unless
you provide the `--no-confirmation` option:

    ./symfony doctrine:drop-db --no-confirmation

You can specify what databases to drop by providing their names:

    ./symfony doctrine:drop-db slave1 slave2

### ~`doctrine::generate-admin`~

The `doctrine::generate-admin` task generates a Doctrine admin module:

    $ php symfony doctrine:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `route_or_model` | `-` | The route name or the model class


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--module` | `-` | The module name
| `--theme` | `admin` | The theme name
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `doctrine:generate-admin` task generates a Doctrine admin module:

    ./symfony doctrine:generate-admin frontend Article

The task creates a module in the `%frontend%` application for the
`%Article%` model.

The task creates a route for you in the application `routing.yml`.

You can also generate a Doctrine admin module by passing a route name:

    ./symfony doctrine:generate-admin frontend article

The task creates a module in the `%frontend%` application for the
`%article%` route definition found in `routing.yml`.

For the filters and batch actions to work properly, you need to add
the `with_wildcard_routes` option to the route:

    article:
      class: sfDoctrineRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`doctrine::generate-migration`~

The `doctrine::generate-migration` task generate migration class:

    $ php symfony doctrine:generate-migration [--application[="..."]] [--env="..."] [--editor-cmd="..."] name



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The name of the migration


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--editor-cmd` | `-` | Open script with this command upon creation


The `doctrine:generate-migration` task generates migration template

    ./symfony doctrine:generate-migration AddUserEmailColumn

You can provide an `--editor-cmd` option to open the new migration class in your
editor of choice upon creation:

    ./symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

### ~`doctrine::generate-migrations-db`~

The `doctrine::generate-migrations-db` task generate migration classes from existing database connections:

    $ php symfony doctrine:generate-migrations-db [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:generate-migrations-db` task generates migration classes from
existing database connections:

    ./symfony doctrine:generate-migrations-db

### ~`doctrine::generate-migrations-diff`~

The `doctrine::generate-migrations-diff` task generate migration classes by producing a diff between your old and new schema.:

    $ php symfony doctrine:generate-migrations-diff [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:generate-migrations-diff` task generates migration classes by
producing a diff between your old and new schema.

    ./symfony doctrine:generate-migrations-diff

### ~`doctrine::generate-migrations-models`~

The `doctrine::generate-migrations-models` task generate migration classes from an existing set of models:

    $ php symfony doctrine:generate-migrations-models [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:generate-migrations-models` task generates migration classes
from an existing set of models:

    ./symfony doctrine:generate-migrations-models

### ~`doctrine::generate-module`~

The `doctrine::generate-module` task generates a Doctrine module:

    $ php symfony doctrine:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-doctrine-route] [--env="..."] [--actions-base-class="..."] application module model



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `module` | `-` | The module name
| `model` | `-` | The model class name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--theme` | `default` | The theme name
| `--generate-in-cache` | `-` | Generate the module in cache
| `--non-verbose-templates` | `-` | Generate non verbose templates
| `--with-show` | `-` | Generate a show method
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--route-prefix` | `-` | The route prefix
| `--with-doctrine-route` | `-` | Whether you will use a Doctrine route
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `doctrine:generate-module` task generates a Doctrine module:

    ./symfony doctrine:generate-module frontend article Article

The task creates a `%module%` module in the `%application%` application
for the model class `%model%`.

You can also create an empty module that inherits its actions and templates from
a runtime generated module in `%sf_app_cache_dir%/modules/auto%module%` by
using the `--generate-in-cache` option:

    ./symfony doctrine:generate-module --generate-in-cache frontend article Article

The generator can use a customized theme by using the `--theme` option:

    ./symfony doctrine:generate-module --theme="custom" frontend article Article

This way, you can create your very own module generator with your own conventions.

You can also change the default actions base class (default to sfActions) of
the generated modules:

    ./symfony doctrine:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`doctrine::generate-module-for-route`~

The `doctrine::generate-module-for-route` task generates a Doctrine module for a route definition:

    $ php symfony doctrine:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `route` | `-` | The route name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--theme` | `default` | The theme name
| `--non-verbose-templates` | `-` | Generate non verbose templates
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `doctrine:generate-module-for-route` task generates a Doctrine module for a route definition:

    ./symfony doctrine:generate-module-for-route frontend article

The task creates a module in the `%frontend%` application for the
`%article%` route definition found in `routing.yml`.

### ~`doctrine::insert-sql`~

The `doctrine::insert-sql` task inserts SQL for current model:

    $ php symfony doctrine:insert-sql [--application[="..."]] [--env="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment


The `doctrine:insert-sql` task creates database tables:

    ./symfony doctrine:insert-sql

The task connects to the database and creates tables for all the
`lib/model/doctrine/*.class.php` files.

### ~`doctrine::migrate`~

The `doctrine::migrate` task migrates database to current/specified version:

    $ php symfony doctrine:migrate [--application[="..."]] [--env="..."] [--up] [--down] [--dry-run] [version]



| Argument | Default | Description
| -------- | ------- | -----------
| `version` | `-` | The version to migrate to


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--up` | `-` | Migrate up one version
| `--down` | `-` | Migrate down one version
| `--dry-run` | `-` | Do not persist migrations


The `doctrine:migrate` task migrates the database:

    ./symfony doctrine:migrate

Provide a version argument to migrate to a specific version:

    ./symfony doctrine:migrate 10

To migration up or down one migration, use the `--up` or `--down` options:

    ./symfony doctrine:migrate --down

If your database supports rolling back DDL statements, you can run migrations
in dry-run mode using the `--dry-run` option:

    ./symfony doctrine:migrate --dry-run

`generate`
----------

### ~`generate::app`~

The `generate::app` task generates a new application:

    $ php symfony generate:app [--escaping-strategy="..."] [--csrf-secret="..."] app



| Argument | Default | Description
| -------- | ------- | -----------
| `app` | `-` | The application name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--escaping-strategy` | `1` | Output escaping strategy
| `--csrf-secret` | `1` | Secret to use for CSRF protection


The `generate:app` task creates the basic directory structure
for a new application in the current project:

    ./symfony generate:app frontend

This task also creates two front controller scripts in the
`web/` directory:

    web/%application%.php     for the production environment
    web/%application%_dev.php for the development environment

For the first application, the production environment script is named
`index.php`.

If an application with the same name already exists,
it throws a `sfCommandException`.

By default, the output escaping is enabled (to prevent XSS), and a random
secret is also generated to prevent CSRF.

You can disable output escaping by using the `escaping-strategy`
option:

    ./symfony generate:app frontend --escaping-strategy=false

You can enable session token in forms (to prevent CSRF) by defining
a secret with the `csrf-secret` option:

    ./symfony generate:app frontend --csrf-secret=UniqueSecret

You can customize the default skeleton used by the task by creating a
`%sf_data_dir%/skeleton/app` directory.

### ~`generate::module`~

The `generate::module` task generates a new module:

    $ php symfony generate:module  application module



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `module` | `-` | The module name




The `generate:module` task creates the basic directory structure
for a new module in an existing application:

    ./symfony generate:module frontend article

The task can also change the author name found in the `actions.class.php`
if you have configure it in `config/properties.ini`:

    [symfony]
      name=blog
      author=Fabien Potencier <fabien.potencier@sensio.com>

You can customize the default skeleton used by the task by creating a
`%sf_data_dir%/skeleton/module` directory.

The task also creates a functional test stub named
`%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php`
that does not pass by default.

If a module with the same name already exists in the application,
it throws a `sfCommandException`.

### ~`generate::project`~

The `generate::project` task generates a new project:

    $ php symfony generate:project [--orm="..."] [--installer="..."] name [author]



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The project name
| `author` | `Your name here` | The project author


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--orm` | `Doctrine` | The ORM to use by default
| `--installer` | `-` | An installer script to execute


The `generate:project` task creates the basic directory structure
for a new project in the current directory:

    ./symfony generate:project blog

If the current directory already contains a symfony project,
it throws a `sfCommandException`.

By default, the task configures Doctrine as the ORM. If you want to use
Propel, use the `--orm` option:

    ./symfony generate:project blog --orm=Propel

If you don't want to use an ORM, pass `none` to `--orm` option:

    ./symfony generate:project blog --orm=none

You can also pass the `--installer` option to further customize the
project:

    ./symfony generate:project blog --installer=./installer.php

You can optionally include a second `author` argument to specify what name to
use as author when symfony generates new classes:

    ./symfony generate:project blog "Jack Doe"

### ~`generate::task`~

The `generate::task` task creates a skeleton class for a new task:

    $ php symfony generate:task [--dir="..."] [--use-database="..."] [--brief-description="..."] task_name



| Argument | Default | Description
| -------- | ------- | -----------
| `task_name` | `-` | The task name (can contain namespace)


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--dir` | `lib/task` | The directory to create the task in
| `--use-database` | `doctrine` | Whether the task needs model initialization to access database
| `--brief-description` | `-` | A brief task description (appears in task list)


The `generate:task` creates a new sfTask class based on the name passed as
argument:

    ./symfony generate:task namespace:name

The `namespaceNameTask.class.php` skeleton task is created under the `lib/task/`
directory. Note that the namespace is optional.

If you want to create the file in another directory (relative to the project
root folder), pass it in the `--dir` option. This directory will be created
if it does not already exist.

    ./symfony generate:task namespace:name --dir=plugins/myPlugin/lib/task

If you want the task to default to a connection other than `doctrine`, provide
the name of this connection with the `--use-database` option:

    ./symfony generate:task namespace:name --use-database=main

The `--use-database` option can also be used to disable database
initialization in the generated task:

    ./symfony generate:task namespace:name --use-database=false

You can also specify a description:

    ./symfony generate:task namespace:name --brief-description="Does interesting things"

`i18n`
------

### ~`i18n::extract`~

The `i18n::extract` task extracts i18n strings from php files:

    $ php symfony i18n:extract [--display-new] [--display-old] [--auto-save] [--auto-delete] application culture



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `culture` | `-` | The target culture


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--display-new` | `-` | Output all new found strings
| `--display-old` | `-` | Output all old strings
| `--auto-save` | `-` | Save the new strings
| `--auto-delete` | `-` | Delete old strings


The `i18n:extract` task extracts i18n strings from your project files
for the given application and target culture:

    ./symfony i18n:extract frontend fr

By default, the task only displays the number of new and old strings
it found in the current project.

If you want to display the new strings, use the `--display-new` option:

    ./symfony i18n:extract --display-new frontend fr

To save them in the i18n message catalogue, use the `--auto-save` option:

    ./symfony i18n:extract --auto-save frontend fr

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application, use the 
`--display-old` option:

    ./symfony i18n:extract --display-old frontend fr

To automatically delete old strings, use the `--auto-delete` but
be careful, especially if you have translations for plugins as they will
appear as old strings but they are not:

    ./symfony i18n:extract --auto-delete frontend fr

### ~`i18n::find`~

The `i18n::find` task finds non "i18n ready" strings in an application:

    $ php symfony i18n:find [--env="..."] application



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--env` | `dev` | The environment


The `i18n:find` task finds non internationalized strings embedded in templates:

    ./symfony i18n:find frontend

This task is able to find non internationalized strings in pure HTML and in PHP code:

    <p>Non i18n text</p>
    <p><?php echo 'Test' ?></p>

As the task returns all strings embedded in PHP, you can have some false positive (especially
if you use the string syntax for helper arguments).

`log`
-----

### ~`log::clear`~

The `log::clear` task clears log files:

    $ php symfony log:clear  







The `log:clear` task clears all symfony log files:

    ./symfony log:clear

### ~`log::rotate`~

The `log::rotate` task rotates an application's log files:

    $ php symfony log:rotate [--history="..."] [--period="..."] application env



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `env` | `-` | The environment name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--history` | `10` | The maximum number of old log files to keep
| `--period` | `7` | The period in days


The `log:rotate` task rotates application log files for a given
environment:

    ./symfony log:rotate frontend dev

You can specify a `period` or a `history` option:

    ./symfony log:rotate frontend dev --history=10 --period=7

`plugin`
--------

### ~`plugin::add-channel`~

The `plugin::add-channel` task add a new PEAR channel:

    $ php symfony plugin:add-channel  name



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The channel name




The `plugin:add-channel` task adds a new PEAR channel:

    ./symfony plugin:add-channel symfony.plugins.pear.example.com

### ~`plugin::install`~

The `plugin::install` task installs a plugin:

    $ php symfony plugin:install [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] [-d|--install_deps] [--force-license] name



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The plugin name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--stability`<br />`(-s)` | `-` | The preferred stability (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | The preferred version
| `--channel`<br />`(-c)` | `-` | The PEAR channel name
| `--install_deps`<br />`(-d)` | `-` | Whether to force installation of required dependencies
| `--force-license` | `-` | Whether to force installation even if the license is not MIT like


The `plugin:install` task installs a plugin:

    ./symfony plugin:install sfGuardPlugin

By default, it installs the latest `stable` release.

If you want to install a plugin that is not stable yet,
use the `stability` option:

    ./symfony plugin:install --stability=beta sfGuardPlugin
    ./symfony plugin:install -s beta sfGuardPlugin

You can also force the installation of a specific version:

    ./symfony plugin:install --release=1.0.0 sfGuardPlugin
    ./symfony plugin:install -r 1.0.0 sfGuardPlugin

To force installation of all required dependencies, use the `install_deps` flag:

    ./symfony plugin:install --install-deps sfGuardPlugin
    ./symfony plugin:install -d sfGuardPlugin

By default, the PEAR channel used is `symfony-plugins`
(plugins.symfony-project.org).

You can specify another channel with the `channel` option:

    ./symfony plugin:install --channel=mypearchannel sfGuardPlugin
    ./symfony plugin:install -c mypearchannel sfGuardPlugin

You can also install PEAR packages hosted on a website:

    ./symfony plugin:install http://somewhere.example.com/sfGuardPlugin-1.0.0.tgz

Or local PEAR packages:

    ./symfony plugin:install /home/fabien/plugins/sfGuardPlugin-1.0.0.tgz

If the plugin contains some web content (images, stylesheets or javascripts),
the task creates a `%name%` symbolic link for those assets under `web/`.
On Windows, the task copy all the files to the `web/%name%` directory.

### ~`plugin::list`~

The `plugin::list` task lists installed plugins:

    $ php symfony plugin:list  







The `plugin:list` task lists all installed plugins:

    ./symfony plugin:list

It also gives the channel and version for each plugin.

### ~`plugin::publish-assets`~

The `plugin::publish-assets` task publishes web assets for all plugins:

    $ php symfony plugin:publish-assets [--core-only] [plugins1] ... [pluginsN]



| Argument | Default | Description
| -------- | ------- | -----------
| `plugins` | `-` | Publish this plugin's assets


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--core-only` | `-` | If set only core plugins will publish their assets


The `plugin:publish-assets` task will publish web assets from all plugins.

    ./symfony plugin:publish-assets

In fact this will send the `plugin.post_install` event to each plugin.

You can specify which plugin or plugins should install their assets by passing
those plugins' names as arguments:

    ./symfony plugin:publish-assets sfDoctrinePlugin

### ~`plugin::uninstall`~

The `plugin::uninstall` task uninstalls a plugin:

    $ php symfony plugin:uninstall [-c|--channel="..."] [-d|--install_deps] name



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The plugin name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--channel`<br />`(-c)` | `-` | The PEAR channel name
| `--install_deps`<br />`(-d)` | `-` | Whether to force installation of dependencies


The `plugin:uninstall` task uninstalls a plugin:

    ./symfony plugin:uninstall sfGuardPlugin

The default channel is `symfony`.

You can also uninstall a plugin which has a different channel:

    ./symfony plugin:uninstall --channel=mypearchannel sfGuardPlugin

    ./symfony plugin:uninstall -c mypearchannel sfGuardPlugin

Or you can use the `channel/package` notation:

    ./symfony plugin:uninstall mypearchannel/sfGuardPlugin

You can get the PEAR channel name of a plugin by launching the
`plugin:list` task.

If the plugin contains some web content (images, stylesheets or javascripts),
the task also removes the `web/%name%` symbolic link (on *nix)
or directory (on Windows).

### ~`plugin::upgrade`~

The `plugin::upgrade` task upgrades a plugin:

    $ php symfony plugin:upgrade [-s|--stability="..."] [-r|--release="..."] [-c|--channel="..."] name



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The plugin name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--stability`<br />`(-s)` | `-` | The preferred stability (stable, beta, alpha)
| `--release`<br />`(-r)` | `-` | The preferred version
| `--channel`<br />`(-c)` | `-` | The PEAR channel name


The `plugin:upgrade` task tries to upgrade a plugin:

    ./symfony plugin:upgrade sfGuardPlugin

The default channel is `symfony`.

If the plugin contains some web content (images, stylesheets or javascripts),
the task also updates the `web/%name%` directory content on Windows.

See `plugin:install` for more information about the format of the plugin name and options.

`project`
---------

### ~`project::clear-controllers`~

The `project::clear-controllers` task clears all non production environment controllers:

    $ php symfony project:clear-controllers  







The `project:clear-controllers` task clears all non production environment
controllers:

    ./symfony project:clear-controllers

You can use this task on a production server to remove all front
controller scripts except the production ones.

If you have two applications named `frontend` and `backend`,
you have four default controller scripts in `web/`:

    index.php
    frontend_dev.php
    backend.php
    backend_dev.php

After executing the `project:clear-controllers` task, two front
controller scripts are left in `web/`:

    index.php
    backend.php

Those two controllers are safe because debug mode and the web debug
toolbar are disabled.

### ~`project::deploy`~

The `project::deploy` task deploys a project to another server:

    $ php symfony project:deploy [--go] [--rsync-dir="..."] [--rsync-options[="..."]] server



| Argument | Default | Description
| -------- | ------- | -----------
| `server` | `-` | The server name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--go` | `-` | Do the deployment
| `--rsync-dir` | `config` | The directory where to look for rsync*.txt files
| `--rsync-options` | `-azC --force --delete --progress` | To options to pass to the rsync executable


The `project:deploy` task deploys a project on a server:

    ./symfony project:deploy production

The server must be configured in `config/properties.ini`:

    [production]
      host=www.example.com
      port=22
      user=fabien
      dir=/var/www/sfblog/
      type=rsync

To automate the deployment, the task uses rsync over SSH.
You must configure SSH access with a key or configure the password
in `config/properties.ini`.

By default, the task is in dry-mode. To do a real deployment, you
must pass the `--go` option:

    ./symfony project:deploy --go production

Files and directories configured in `config/rsync_exclude.txt` are
not deployed:

    .svn
    /web/uploads/*
    /cache/*
    /log/*

You can also create a `rsync.txt` and `rsync_include.txt` files.

If you need to customize the `rsync*.txt` files based on the server,
you can pass a `rsync-dir` option:

    ./symfony project:deploy --go --rsync-dir=config/production production

Last, you can specify the options passed to the rsync executable, using the
`rsync-options` option (defaults are `-azC --force --delete --progress`):

    ./symfony project:deploy --go --rsync-options=-avz

### ~`project::disable`~

The `project::disable` task disables an application in a given environment:

    $ php symfony project:disable  env [app1] ... [appN]



| Argument | Default | Description
| -------- | ------- | -----------
| `env` | `-` | The environment name
| `app` | `-` | The application name




The `project:disable` task disables an environment:

    ./symfony project:disable prod

You can also specify individual applications to be disabled in that
environment:

    ./symfony project:disable prod frontend backend

### ~`project::enable`~

The `project::enable` task enables an application in a given environment:

    $ php symfony project:enable  env [app1] ... [appN]



| Argument | Default | Description
| -------- | ------- | -----------
| `env` | `-` | The environment name
| `app` | `-` | The application name




The `project:enable` task enables a specific environment:

    ./symfony project:enable frontend prod

You can also specify individual applications to be enabled in that
environment:

    ./symfony project:enable prod frontend backend

### ~`project::optimize`~

The `project::optimize` task optimizes a project for better performance:

    $ php symfony project:optimize  application [env]



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `env` | `prod` | The environment name




The `project:optimize` optimizes a project for better performance:

    ./symfony project:optimize frontend prod

This task should only be used on a production server. Don't forget to re-run
the task each time the project changes.

### ~`project::permissions`~

The `project::permissions` task fixes symfony directory permissions:

    $ php symfony project:permissions  







The `project:permissions` task fixes directory permissions:

    ./symfony project:permissions

### ~`project::send-emails`~

The `project::send-emails` task sends emails stored in a queue:

    $ php symfony project:send-emails [--application[="..."]] [--env="..."] [--message-limit[="..."]] [--time-limit[="..."]] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--message-limit` | `0` | The maximum number of messages to send
| `--time-limit` | `0` | The time limit for sending messages (in seconds)


The `project:send-emails` sends emails stored in a queue:

    php symfony project:send-emails

You can limit the number of messages to send:

    php symfony project:send-emails --message-limit=10

Or limit to time (in seconds):

    php symfony project:send-emails --time-limit=10

### ~`project::validate`~

The `project::validate` task finds deprecated usage in a project:

    $ php symfony project:validate  







The `project:validate` task detects deprecated usage in your project.

    ./symfony project:validate

The task lists all the files you need to change before switching to
symfony 1.4.

`propel`
--------

### ~`propel::build`~

The `propel::build` task generate code based on your schema:

    $ php symfony propel:build [--application[="..."]] [--env="..."] [--no-confirmation] [--all] [--all-classes] [--model] [--forms] [--filters] [--sql] [--db] [--and-load[="..."]] [--and-append[="..."]] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--no-confirmation` | `-` | Whether to force dropping of the database
| `--all` | `-` | Build everything and reset the database
| `--all-classes` | `-` | Build all classes
| `--model` | `-` | Build model classes
| `--forms` | `-` | Build form classes
| `--filters` | `-` | Build filter classes
| `--sql` | `-` | Build SQL
| `--db` | `-` | Drop, create, and insert SQL
| `--and-load` | `-` | Load fixture data (multiple values allowed)
| `--and-append` | `-` | Append fixture data (multiple values allowed)


The `propel:build` task generates code based on your schema:

    ./symfony propel:build

You must specify what you would like built. For instance, if you want model
and form classes built use the `--model` and `--forms` options:

    ./symfony propel:build --model --forms

You can use the `--all` shortcut option if you would like all classes and
SQL files generated and the database rebuilt:

    ./symfony propel:build --all

This is equivalent to running the following tasks:

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

You can also generate only class files by using the `--all-classes` shortcut
option. When this option is used alone, the database will not be modified.

    ./symfony propel:build --all-classes

The `--and-load` option will load data from the project and plugin
`data/fixtures/` directories:

    ./symfony propel:build --db --and-load

To specify what fixtures are loaded, add a parameter to the `--and-load` option:

    ./symfony propel:build --all --and-load="data/fixtures/dev/"

To append fixture data without erasing any records from the database, include
the `--and-append` option:

    ./symfony propel:build --all --and-append

### ~`propel::build-all`~

The `propel::build-all` task generates Propel model and form classes, SQL and initializes the database:

    $ php symfony propel:build-all [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--connection` | `propel` | The connection name
| `--no-confirmation` | `-` | Do not ask for confirmation
| `--skip-forms`<br />`(-F)` | `-` | Skip generating forms
| `--classes-only`<br />`(-C)` | `-` | Do not initialize the database
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:build-all` task is a shortcut for five other tasks:

    ./symfony propel:build-all

The task is equivalent to:

    ./symfony propel:build-model
    ./symfony propel:build-forms
    ./symfony propel:build-filters
    ./symfony propel:build-sql
    ./symfony propel:insert-sql

See those tasks' help pages for more information.

To bypass confirmation prompts, you can pass the `no-confirmation` option:

    ./symfony propel:buil-all --no-confirmation

To build all classes but skip initializing the database, use the `classes-only`
option:

    ./symfony propel:build-all --classes-only

### ~`propel::build-all-load`~

The `propel::build-all-load` task generates Propel model and form classes, SQL, initializes the database, and loads data:

    $ php symfony propel:build-all-load [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [-F|--skip-forms] [-C|--classes-only] [--phing-arg="..."] [--append] [--dir="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `dev` | The environment
| `--connection` | `propel` | The connection name
| `--no-confirmation` | `-` | Do not ask for confirmation
| `--skip-forms`<br />`(-F)` | `-` | Skip generating forms
| `--classes-only`<br />`(-C)` | `-` | Do not initialize the database
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)
| `--append` | `-` | Don't delete current data in the database
| `--dir` | `-` | The directories to look for fixtures (multiple values allowed)


The `propel:build-all-load` task is a shortcut for two other tasks:

    ./symfony propel:build-all-load

The task is equivalent to:

    ./symfony propel:build-all
    ./symfony propel:data-load

See those tasks' help pages for more information.

To bypass the confirmation, you can pass the `no-confirmation`
option:

    ./symfony propel:buil-all-load --no-confirmation

### ~`propel::build-filters`~

The `propel::build-filters` task creates filter form classes for the current model:

    $ php symfony propel:build-filters [--connection="..."] [--model-dir-name="..."] [--filter-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--connection` | `propel` | The connection name
| `--model-dir-name` | `model` | The model dir name
| `--filter-dir-name` | `filter` | The filter form dir name
| `--application` | `1` | The application name
| `--generator-class` | `sfPropelFormFilterGenerator` | The generator class


The `propel:build-filters` task creates filter form classes from the schema:

    ./symfony propel:build-filters

The task read the schema information in `config/*schema.xml` and/or
`config/*schema.yml` from the project and all installed plugins.

The task use the `propel` connection as defined in `config/databases.yml`.
You can use another connection by using the `--connection` option:

    ./symfony propel:build-filters --connection="name"

The model filter form classes files are created in `lib/filter`.

This task never overrides custom classes in `lib/filter`.
It only replaces base classes generated in `lib/filter/base`.

### ~`propel::build-forms`~

The `propel::build-forms` task creates form classes for the current model:

    $ php symfony propel:build-forms [--connection="..."] [--model-dir-name="..."] [--form-dir-name="..."] [--application[="..."]] [--generator-class="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--connection` | `propel` | The connection name
| `--model-dir-name` | `model` | The model dir name
| `--form-dir-name` | `form` | The form dir name
| `--application` | `1` | The application name
| `--generator-class` | `sfPropelFormGenerator` | The generator class


The `propel:build-forms` task creates form classes from the schema:

    ./symfony propel:build-forms

The task read the schema information in `config/*schema.xml` and/or
`config/*schema.yml` from the project and all installed plugins.

The task use the `propel` connection as defined in `config/databases.yml`.
You can use another connection by using the `--connection` option:

    ./symfony propel:build-forms --connection="name"

The model form classes files are created in `lib/form`.

This task never overrides custom classes in `lib/form`.
It only replaces base classes generated in `lib/form/base`.

### ~`propel::build-model`~

The `propel::build-model` task creates classes for the current model:

    $ php symfony propel:build-model [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:build-model` task creates model classes from the schema:

    ./symfony propel:build-model

The task read the schema information in `config/*schema.xml` and/or
`config/*schema.yml` from the project and all installed plugins.

You mix and match YML and XML schema files. The task will convert
YML ones to XML before calling the Propel task.

The model classes files are created in `lib/model`.

This task never overrides custom classes in `lib/model`.
It only replaces files in `lib/model/om` and `lib/model/map`.

### ~`propel::build-schema`~

The `propel::build-schema` task creates a schema from an existing database:

    $ php symfony propel:build-schema [--application[="..."]] [--env="..."] [--connection="..."] [--xml] [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `cli` | The environment
| `--connection` | `-` | The connection name
| `--xml` | `-` | Creates an XML schema instead of a YML one
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:build-schema` task introspects a database to create a schema:

    ./symfony propel:build-schema

By default, the task creates a YML file, but you can also create a XML file:

    ./symfony --xml propel:build-schema

The XML format contains more information than the YML one.

### ~`propel::build-sql`~

The `propel::build-sql` task creates SQL for the current model:

    $ php symfony propel:build-sql [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:build-sql` task creates SQL statements for table creation:

    ./symfony propel:build-sql

The generated SQL is optimized for the database configured in `config/propel.ini`:

    propel.database = mysql

### ~`propel::data-dump`~

The `propel::data-dump` task dumps data to the fixtures directory:

    $ php symfony propel:data-dump [--application[="..."]] [--env="..."] [--connection="..."] [--classes="..."] [target]



| Argument | Default | Description
| -------- | ------- | -----------
| `target` | `-` | The target filename


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `cli` | The environement
| `--connection` | `propel` | The connection name
| `--classes` | `-` | The class names to dump (separated by a colon)


The `propel:data-dump` task dumps database data:

    ./symfony propel:data-dump > data/fixtures/dump.yml

By default, the task outputs the data to the standard output,
but you can also pass a filename as a second argument:

    ./symfony propel:data-dump dump.yml

The task will dump data in `data/fixtures/%target%`
(data/fixtures/dump.yml in the example).

The dump file is in the YML format and can be re-imported by using
the `propel:data-load` task.

By default, the task use the `propel` connection as defined in `config/databases.yml`.
You can use another connection by using the `connection` option:

    ./symfony propel:data-dump --connection="name"

If you only want to dump some classes, use the `classes` option:

    ./symfony propel:data-dump --classes="Article,Category"

If you want to use a specific database configuration from an application, you can use
the `application` option:

    ./symfony propel:data-dump --application=frontend

### ~`propel::data-load`~

The `propel::data-load` task loads YAML fixture data:

    $ php symfony propel:data-load [--application[="..."]] [--env="..."] [--append] [--connection="..."] [dir_or_file1] ... [dir_or_fileN]



| Argument | Default | Description
| -------- | ------- | -----------
| `dir_or_file` | `-` | Directory or file to load


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `cli` | The environment
| `--append` | `-` | Don't delete current data in the database
| `--connection` | `propel` | The connection name


The `propel:data-load` task loads data fixtures into the database:

    ./symfony propel:data-load

The task loads data from all the files found in `data/fixtures/`.

If you want to load data from specific files or directories, you can append
them as arguments:

    ./symfony propel:data-load data/fixtures/dev data/fixtures/users.yml

The task use the `propel` connection as defined in `config/databases.yml`.
You can use another connection by using the `--connection` option:

    ./symfony propel:data-load --connection="name"

If you don't want the task to remove existing data in the database,
use the `--append` option:

    ./symfony propel:data-load --append

If you want to use a specific database configuration from an application, you can use
the `application` option:

    ./symfony propel:data-load --application=frontend

### ~`propel::generate-admin`~

The `propel::generate-admin` task generates a Propel admin module:

    $ php symfony propel:generate-admin [--module="..."] [--theme="..."] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route_or_model



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `route_or_model` | `-` | The route name or the model class


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--module` | `-` | The module name
| `--theme` | `admin` | The theme name
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `propel:generate-admin` task generates a Propel admin module:

    ./symfony propel:generate-admin frontend Article

The task creates a module in the `%frontend%` application for the
`%Article%` model.

The task creates a route for you in the application `routing.yml`.

You can also generate a Propel admin module by passing a route name:

    ./symfony propel:generate-admin frontend article

The task creates a module in the `%frontend%` application for the
`%article%` route definition found in `routing.yml`.

For the filters and batch actions to work properly, you need to add
the `with_wildcard_routes` option to the route:

    article:
      class: sfPropelRouteCollection
      options:
        model:                Article
        with_wildcard_routes: true

### ~`propel::generate-module`~

The `propel::generate-module` task generates a Propel module:

    $ php symfony propel:generate-module [--theme="..."] [--generate-in-cache] [--non-verbose-templates] [--with-show] [--singular="..."] [--plural="..."] [--route-prefix="..."] [--with-propel-route] [--env="..."] [--actions-base-class="..."] application module model



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `module` | `-` | The module name
| `model` | `-` | The model class name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--theme` | `default` | The theme name
| `--generate-in-cache` | `-` | Generate the module in cache
| `--non-verbose-templates` | `-` | Generate non verbose templates
| `--with-show` | `-` | Generate a show method
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--route-prefix` | `-` | The route prefix
| `--with-propel-route` | `-` | Whether you will use a Propel route
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `propel:generate-module` task generates a Propel module:

    ./symfony propel:generate-module frontend article Article

The task creates a `%module%` module in the `%application%` application
for the model class `%model%`.

You can also create an empty module that inherits its actions and templates from
a runtime generated module in `%sf_app_cache_dir%/modules/auto%module%` by
using the `--generate-in-cache` option:

    ./symfony propel:generate-module --generate-in-cache frontend article Article

The generator can use a customized theme by using the `--theme` option:

    ./symfony propel:generate-module --theme="custom" frontend article Article

This way, you can create your very own module generator with your own conventions.

You can also change the default actions base class (default to sfActions) of
the generated modules:

    ./symfony propel:generate-module --actions-base-class="ProjectActions" frontend article Article

### ~`propel::generate-module-for-route`~

The `propel::generate-module-for-route` task generates a Propel module for a route definition:

    $ php symfony propel:generate-module-for-route [--theme="..."] [--non-verbose-templates] [--singular="..."] [--plural="..."] [--env="..."] [--actions-base-class="..."] application route



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `route` | `-` | The route name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--theme` | `default` | The theme name
| `--non-verbose-templates` | `-` | Generate non verbose templates
| `--singular` | `-` | The singular name
| `--plural` | `-` | The plural name
| `--env` | `dev` | The environment
| `--actions-base-class` | `sfActions` | The base class for the actions


The `propel:generate-module-for-route` task generates a Propel module for a route definition:

    ./symfony propel:generate-module-for-route frontend article

The task creates a module in the `%frontend%` application for the
`%article%` route definition found in `routing.yml`.

### ~`propel::graphviz`~

The `propel::graphviz` task generates a graphviz chart of current object model:

    $ php symfony propel:graphviz [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:graphviz` task creates a graphviz DOT
visualization for automatic graph drawing of object model:

    ./symfony propel:graphviz

### ~`propel::insert-sql`~

The `propel::insert-sql` task inserts SQL for current model:

    $ php symfony propel:insert-sql [--application[="..."]] [--env="..."] [--connection="..."] [--no-confirmation] [--phing-arg="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--application` | `1` | The application name
| `--env` | `cli` | The environment
| `--connection` | `-` | The connection name
| `--no-confirmation` | `-` | Do not ask for confirmation
| `--phing-arg` | `-` | Arbitrary phing argument (multiple values allowed)


The `propel:insert-sql` task creates database tables:

    ./symfony propel:insert-sql

The task connects to the database and executes all SQL statements
found in `config/sql/*schema.sql` files.

Before execution, the task will ask you to confirm the execution
as it deletes all data in your database.

To bypass the confirmation, you can pass the `--no-confirmation`
option:

    ./symfony propel:insert-sql --no-confirmation

The task read the database configuration from `databases.yml`.
You can use a specific application/environment by passing
an `--application` or `--env` option.

You can also use the `--connection` option if you want to
only load SQL statements for a given connection.

### ~`propel::schema-to-xml`~

The `propel::schema-to-xml` task creates schema.xml from schema.yml:

    $ php symfony propel:schema-to-xml  







The `propel:schema-to-xml` task converts YML schemas to XML:

    ./symfony propel:schema-to-xml

### ~`propel::schema-to-yml`~

The `propel::schema-to-yml` task creates schema.yml from schema.xml:

    $ php symfony propel:schema-to-yml  







The `propel:schema-to-yml` task converts XML schemas to YML:

    ./symfony propel:schema-to-yml

`symfony`
---------

### ~`symfony::test`~

The `symfony::test` task launches the symfony test suite:

    $ php symfony symfony:test [-u|--update-autoloader] [-f|--only-failed] [--xml="..."] [--rebuild-all] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--update-autoloader`<br />`(-u)` | `-` | Update the sfCoreAutoload class
| `--only-failed`<br />`(-f)` | `-` | Only run tests that failed last time
| `--xml` | `-` | The file name for the JUnit compatible XML log file
| `--rebuild-all` | `-` | Rebuild all generated fixture files


The `test:all` task launches the symfony test suite:

    ./symfony symfony:test

`test`
------

### ~`test::all`~

The `test::all` task launches all tests:

    $ php symfony test:all [-f|--only-failed] [--xml="..."] 





| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--only-failed`<br />`(-f)` | `-` | Only run tests that failed last time
| `--xml` | `-` | The file name for the JUnit compatible XML log file


The `test:all` task launches all unit and functional tests:

    ./symfony test:all

The task launches all tests found in `test/`.

If some tests fail, you can use the `--trace` option to have more
information about the failures:

    ./symfony test:all -t

Or you can also try to fix the problem by launching them by hand or with the
`test:unit` and `test:functional` task.

Use the `--only-failed` option to force the task to only execute tests
that failed during the previous run:

    ./symfony test:all --only-failed

Here is how it works: the first time, all tests are run as usual. But for
subsequent test runs, only tests that failed last time are executed. As you
fix your code, some tests will pass, and will be removed from subsequent runs.
When all tests pass again, the full test suite is run... you can then rinse
and repeat.

The task can output a JUnit compatible XML log file with the `--xml`
options:

    ./symfony test:all --xml=log.xml

### ~`test::coverage`~

The `test::coverage` task outputs test code coverage:

    $ php symfony test:coverage [--detailed] test_name lib_name



| Argument | Default | Description
| -------- | ------- | -----------
| `test_name` | `-` | A test file name or a test directory
| `lib_name` | `-` | A lib file name or a lib directory for wich you want to know the coverage


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--detailed` | `-` | Output detailed information


The `test:coverage` task outputs the code coverage
given a test file or test directory
and a lib file or lib directory for which you want code
coverage:

    ./symfony test:coverage test/unit/model lib/model

To output the lines not covered, pass the `--detailed` option:

    ./symfony test:coverage --detailed test/unit/model lib/model

### ~`test::functional`~

The `test::functional` task launches functional tests:

    $ php symfony test:functional [--xml="..."] application [controller1] ... [controllerN]



| Argument | Default | Description
| -------- | ------- | -----------
| `application` | `-` | The application name
| `controller` | `-` | The controller name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--xml` | `-` | The file name for the JUnit compatible XML log file


The `test:functional` task launches functional tests for a
given application:

    ./symfony test:functional frontend

The task launches all tests found in `test/functional/%application%`.

If some tests fail, you can use the `--trace` option to have more
information about the failures:

    ./symfony test:functional frontend -t

You can launch all functional tests for a specific controller by
giving a controller name:

    ./symfony test:functional frontend article

You can also launch all functional tests for several controllers:

    ./symfony test:functional frontend article comment

The task can output a JUnit compatible XML log file with the `--xml`
options:

    ./symfony test:functional --xml=log.xml

### ~`test::unit`~

The `test::unit` task launches unit tests:

    $ php symfony test:unit [--xml="..."] [name1] ... [nameN]



| Argument | Default | Description
| -------- | ------- | -----------
| `name` | `-` | The test name


| Option (Shortcut) | Default | Description
| ----------------- | ------- | -----------
| `--xml` | `-` | The file name for the JUnit compatible XML log file


The `test:unit` task launches unit tests:

    ./symfony test:unit

The task launches all tests found in `test/unit`.

If some tests fail, you can use the `--trace` option to have more
information about the failures:

    ./symfony test:unit -t

You can launch unit tests for a specific name:

    ./symfony test:unit strtolower

You can also launch unit tests for several names:

    ./symfony test:unit strtolower strtoupper

The task can output a JUnit compatible XML log file with the `--xml`
options:

    ./symfony test:unit --xml=log.xml



