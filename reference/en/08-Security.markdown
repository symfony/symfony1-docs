The security.yml Configuration File
===================================

The ~`security.yml`~ configuration file describes the authentication and
authorization rules for a symfony application.

>**TIP**
>The configuration information from the `security.yml` file is used by
>the [`user`](#chapter_05_user) factory class (`sfBasicSecurityUser` by
>default). The enforcement of the authentication and authorization is
>done by the `security` [filter](#chapter_12_security).

When an application is created, symfony generates a default `security.yml`
file in the application `config/` directory which describes the security for
the whole application (under the `default` key):

    [yml]
    default:
      is_secure: false

As discussed in the introduction, the `security.yml` file benefits from
the [**configuration cascade mechanism**](#chapter_03_configuration_cascade),
and can include [**constants**](#chapter_03_constants).

The default application configuration can be overridden for a module by
creating a `security.yml` file in the `config/` directory of the module. The
main keys are action names without the `execute` prefix (`index` for the
`executeIndex` method for instance).

To determine if an action is secure or not, symfony looks for the information
in the following order:

  * a configuration for the specific action in the module configuration file
    if it exists;

  * a configuration for the whole module in the module configuration file if
    it exists (under the `all` key);

  * the default application configuration (under the `default` key).

The same precedence rules are used to determine the credentials needed to
access an action.

>**NOTE**
>The `security.yml` configuration file is cached as a PHP file; the
>process is automatically managed by the ~`sfSecurityConfigHandler`~
>[class](#chapter_14_config_handlers_yml).

~Authentication~
----------------

The default configuration of `security.yml`, installed by default for each
application, authorizes access to anybody:

    [yml]
    default:
      is_secure: false

By setting the ~`is_secure`~ key to `true` in the application `security.yml`
file, the entire application will require authentication for all users.

>**NOTE**
>When an un-authenticated user tries to access a secured action, symfony
>forwards the request to the `login` action configured in `settings.yml`.

To modify authentication requirements for a module, create a `security.yml`
file in the `config/` directory of the module and define an `all` key:

    [yml]
    all:
      is_secure: true

To modify authentication requirements for a single action of a module, create
a `security.yml` file in the `config/` directory of the module and define a
key after the name of the action:

    [yml]
    index:
      is_secure: false

>**TIP**
>It is not possible to secure the login action. This is to avoid infinite
>recursion.

~Authorization~
---------------

When a user is authenticated, the access to some actions can be even more
restricted by defining *~credentials~*. When credentials are defined, a user
must have the required credentials to access the action:

    [yml]
    all:
      is_secure:   true
      credentials: admin

The credential system of symfony is simple and powerful. A credential is a
string that can represent anything you need to describe the application
security model (like groups or permissions).

The `credentials` key supports Boolean operations to describe complex
credential requirements by using the notation array.

If a user must have the credential A **and** the credential B, wrap the
credentials with square brackets:

    [yml]
    index:
      credentials: [A, B]

If a user must have credential the A **or** the credential B, wrap them with
two pairs of square brackets:

    [yml]
    index:
      credentials: [[A, B]]

You can also mix and match brackets to describe any kind of Boolean expression
with any number of credentials.
