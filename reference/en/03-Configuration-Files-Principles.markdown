Configuration File Principles
=============================

Symfony configuration files are based on a common set of principles and share
some common properties. This section describes them in detail, and acts as a
reference for other sections describing YAML configuration files.

Cache
-----

All configuration files in symfony are cached to PHP files by configuration
handler classes. When the `is_debug` setting is set to `false` (for instance
for the `prod` environment), the YAML file is only accessed for the very first
request; the PHP cache is used for subsequent requests. This means that the
"heavy" work is done only once, when the YAML file is parsed and interpreted
the first time.

>**TIP**
>In the `dev` environment, where `is_debug` is set to `true` by default,
>the compilation is done whenever the configuration file changes (symfony
>checks the file modification time).

The parsing and caching of each configuration file is done by specialized
configuration handler classes, configured in
[`config_handler.yml`](#chapter_14_config_handlers_yml).

In the following sections, when we talk about the "compilation", it means the
first time when the YAML file is converted to a PHP file and stored in the
cache.

>**TIP**
>To force the configuration cache to be reloaded, you can use the
>`cache:clear` task:
>
>     $ php symfony cache:clear --type=config

Constants
---------

*Configuration files*: `core_compile.yml`, `factories.yml`, `generator.yml`,
`databases.yml`, `filters.yml`, `view.yml`, `autoload.yml`

Some configuration files allow the usage of pre-defined constants. Constants
are declared with placeholders using the `%XXX%` notation (where XXX is an
uppercase key) and are replaced by their actual value at "compilation" time.

### Configuration Settings

A constant can be any setting defined in the `settings.yml` configuration
file. The placeholder key is then an upper-case setting key name prefixed with
`SF_`:

    [yml]
    logging: %SF_LOGGING_ENABLED%

When symfony compiles the configuration file, it replaces all occurrences of
the `%SF_XXX%` placeholders by their value from `settings.yml`. In the above
example, it will replace the `SF_LOGGING_ENABLED` placeholder with the value of
the `logging_enabled` setting defined in `settings.yml`.

### Application Settings

You can also use settings defined in the `app.yml` configuration file by
prefixing the key name with `APP_`.

### Special Constants

By default, symfony defines four constants according to the current front
controller:

 | Constant               | Description                     | Configuration method |
 | ---------------------- | ------------------------------- | -------------------- |
 | ~`SF_APP`~             | The current application name    | `getApplication()`   |
 | ~`SF_ENVIRONMENT`~     | The current environment name    | `getEnvironment()`   |
 | ~`SF_DEBUG`~           | Whether debug is enabled or not | `isDebug()`          |
 | ~`SF_SYMFONY_LIB_DIR`~ | The symfony libraries directory | `getSymfonyLibDir()` |

### Directories

Constants are also very useful when you need to reference a directory or a
file path without hardcoding it. Symfony defines a number of constants for
common project and application directories.

At the root of the hierarchy is the project root directory, `SF_ROOT_DIR`. All
other constants are derived from this root directory.

The project directory structure is defined as follows:

 | Constants          | Default Value        |
 | ------------------ | -------------------- |
 | ~`SF_APPS_DIR`~    | `SF_ROOT_DIR/apps`   |
 | ~`SF_CONFIG_DIR`~  | `SF_ROOT_DIR/config` |
 | ~`SF_CACHE_DIR`~   | `SF_ROOT_DIR/cache`  |
 | ~`SF_DATA_DIR`~    | `SF_ROOT_DIR/data`   |
 | ~`SF_DOC_DIR`~     | `SF_ROOT_DIR/doc`    |
 | ~`SF_LIB_DIR`~     | `SF_ROOT_DIR/lib`    |
 | ~`SF_LOG_DIR`~     | `SF_ROOT_DIR/log`    |
 | ~`SF_PLUGINS_DIR`~ | `SF_ROOT_DIR/plugins`|
 | ~`SF_TEST_DIR`~    | `SF_ROOT_DIR/test`   |
 | ~`SF_WEB_DIR`~     | `SF_ROOT_DIR/web`    |
 | ~`SF_UPLOAD_DIR`~  | `SF_WEB_DIR/uploads` |

The application directory structure is defined under the
`SF_APPS_DIR/APP_NAME` directory:

 | Constants               | Default Value          |
 | ----------------------- | ---------------------- |
 | ~`SF_APP_CONFIG_DIR`~   | `SF_APP_DIR/config`    |
 | ~`SF_APP_LIB_DIR`~      | `SF_APP_DIR/lib`       |
 | ~`SF_APP_MODULE_DIR`~   | `SF_APP_DIR/modules`   |
 | ~`SF_APP_TEMPLATE_DIR`~ | `SF_APP_DIR/templates` |
 | ~`SF_APP_I18N_DIR`~     | `SF_APP_DIR/i18n`      |

Eventually, the application cache directory structure is defined as follows:

 | Constants                 | Default Value                    |
 | ------------------------- | -------------------------------- |
 | ~`SF_APP_BASE_CACHE_DIR`~ | `SF_CACHE_DIR/APP_NAME`          |
 | ~`SF_APP_CACHE_DIR`~      | `SF_CACHE_DIR/APP_NAME/ENV_NAME` |
 | ~`SF_TEMPLATE_CACHE_DIR`~ | `SF_APP_CACHE_DIR/template`      |
 | ~`SF_I18N_CACHE_DIR`~     | `SF_APP_CACHE_DIR/i18n`          |
 | ~`SF_CONFIG_CACHE_DIR`~   | `SF_APP_CACHE_DIR/config`        |
 | ~`SF_TEST_CACHE_DIR`~     | `SF_APP_CACHE_DIR/test`          |
 | ~`SF_MODULE_CACHE_DIR`~   | `SF_APP_CACHE_DIR/modules`       |

environment-awareness
---------------------

*Configuration files*: `settings.yml`, `factories.yml`, `databases.yml`,
`app.yml`

Some symfony configuration files are environment-aware -- their interpretation
depends on the current symfony environment. These files have different
sections that define the configuration should vary for each environment. When
creating a new application, symfony creates sensible configuration for the
three default symfony environments: `prod`, `test`, and `dev`:

    [yml]
    prod:
      # Configuration for the `prod` environment

    test:
      # Configuration for the `test` environment

    dev:
      # Configuration for the `dev` environment

    all:
      # Default configuration for all environments

When symfony needs a value from a configuration file, it merges the
configuration found in the current environment section with the `all`
configuration. The special `all` section describes the default configuration
for all environments. If the environment section is not defined, symfony falls
back to the `all` configuration.

Configuration Cascade
---------------------

*Configuration files*: `core_compile.yml`, `autoload.yml`, `settings.yml`,
`factories.yml`, `databases.yml`, `security.yml`, `cache.yml`, `app.yml`,
`filters.yml`, `view.yml`

Some configuration files can be defined in several `config/` sub-directories
contained in the project directory structure.

When the configuration is compiled, the values from all the different files
are merged according to a precedence order:

  * The module configuration (`PROJECT_ROOT_DIR/apps/APP_NAME/modules/MODULE_NAME/config/XXX.yml`)
  * The application configuration (`PROJECT_ROOT_DIR/apps/APP_NAME/config/XXX.yml`)
  * The project configuration (`PROJECT_ROOT_DIR/config/XXX.yml`)
  * The configuration defined in the plugins (`PROJECT_ROOT_DIR/plugins/*/config/XXX.yml`)
  * The default configuration defined in the symfony libraries (`SF_LIB_DIR/config/XXX.yml`)

For instance, the `settings.yml` defined in an application directory inherits
from the configuration set in the main `config/` directory of the project, and
eventually from the default configuration contained in the framework itself
(`lib/config/config/settings.yml`).

>**TIP**
>When a configuration file is environment-aware and can be defined in
>several directories, the following priority list applies:
>
> 1. Module
> 2. Application
> 3. Project
> 4. Specific environment
> 5. All environments
> 6. Default
