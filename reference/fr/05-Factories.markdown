Le fichier de configuration factories.yml
====================================

Les factories sont des objets du noyau nécessaires pour le framework au cours de la vie de toutes les
requêtes. Ils sont configurés dans le fichier de configuration `factories.yml` et
toujours accessible via l'objet `sfContext` :

    [php]
    // Récupère le factory de l'utilisateur
    sfContext::getInstance()->getUser();

Le fichier principal de configuration `factories.yml` pour une application se trouve dans
le répertoire `apps/APP_NAME/config/`.

Comme indiqué dans l'introduction, le fichier `factories.yml` est
[**sensible à l'environnement**](#chapter_03_sensibilisation_a_l_environnement), bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

Le fichier de configuration `factories.yml` contient une liste de factory nommés :

    [yml]
    FACTORY_1:
      # definition de factory 1

    FACTORY_2:
      # definition de factory 2

    # ...

Les noms de factory supportés sont : `controller`, `logger`, `i18n`, `request`,
`response`, `routing`, `storage`, `user`, `view_cache`, et
`view_cache_manager`.

Lorsque le `sfContext` initialise les factories, il lit le fichier `factories.yml`
pour le nom de la classe du factory (`class`) et les paramètres (`param`)
utilisés pour configurer l'objet factory :

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

Être en mesure de personnaliser les factories signifie que vous pouvez utiliser une classe personnalisée
pour les objets du noyau de symfony à la place de celui par défaut. Vous pouvez également modifier
le comportement par défaut de ces classes en personnalisant les paramètres qui lui sont envoyés.

Si la classe du factory ne peut pas être chargées automatiquement, un chemin du fichier peut être défini et
sera automatiquement inclus avant la création du factory :

    [yml]
    FACTORY_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>Le fichier de configuration `factories.yml` est mis en cache dans un fichier PHP. Le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfFactoryConfigHandler`~.

<div class="pagebreak"></div>

Factories
---------

 * [`mailer`](#chapter_05_mailer)

  * [`charset`](#chapter_05_sub_charset)
  * [`delivery_address`](#chapter_05_sub_delivery_address)
  * [`delivery_strategy`](#chapter_05_sub_delivery_strategy)
  * [`spool_arguments`](#chapter_05_sub_spool_arguments)
  * [`spool_class`](#chapter_05_sub_spool_class)
  * [`transport`](#chapter_05_sub_transport)

 * [`request`](#chapter_05_request)

   * [`formats`](#chapter_05_sub_formats)
   * [`path_info_array`](#chapter_05_sub_path_info_array)
   * [`path_info_key`](#chapter_05_sub_path_info_key)
   * [`relative_url_root`](#chapter_05_sub_relative_url_root)

 * [`response`](#chapter_05_response)

   * [`charset`](#chapter_05_sub_charset)
   * [`http_protocol`](#chapter_05_sub_http_protocol)
   * [`send_http_headers`](#chapter_05_sub_send_http_headers)

 * [`user`](#chapter_05_user)

   * [`default_culture`](#chapter_05_sub_default_culture)
   * [`timeout`](#chapter_05_sub_timeout)
   * [`use_flash`](#chapter_05_sub_use_flash)

 * [`storage`](#chapter_05_storage)

   * [`auto_start`](#chapter_05_sub_auto_start)
   * [`database`](#chapter_05_sub_options_de_la_base_de_donnees_specifique_de_stockage)
   * [`db_table`](#chapter_05_sub_options_de_la_base_de_donnees_specifique_de_stockage)
   * [`db_id_col`](#chapter_05_sub_options_de_la_base_de_donnees_specifique_de_stockage)
   * [`db_data_col`](#chapter_05_sub_options_de_la_base_de_donnees_specifique_de_stockage)
   * [`db_time_col`](#chapter_05_sub_options_de_la_base_de_donnees_specifique_de_stockage)
   * [`session_cache_limiter`](#chapter_05_sub_session_cache_limiter)
   * [`session_cookie_domain`](#chapter_05_sub_les_parametres_de_session_set_cookie_params)
   * [`session_cookie_httponly`](#chapter_05_sub_les_parametres_de_session_set_cookie_params)
   * [`session_cookie_lifetime`](#chapter_05_sub_les_parametres_de_session_set_cookie_params)
   * [`session_cookie_path`](#chapter_05_sub_les_parametres_de_session_set_cookie_params)
   * [`session_cookie_secure`](#chapter_05_sub_les_parametres_de_session_set_cookie_params)
   * [`session_name`](#chapter_05_sub_session_name)

 * [`view_cache_manager`](#chapter_05_view_cache_manager)

   * [`cache_key_use_vary_headers`](#chapter_05_sub_cache_key_use_vary_headers)
   * [`cache_key_use_host_name`](#chapter_05_sub_cache_key_use_host_name)

 * [`view_cache`](#chapter_05_view_cache)
 * [`i18n`](#chapter_05_i18n)

   * [`cache`](#chapter_05_sub_cache)
   * [`debug`](#chapter_05_sub_debug)
   * [`source`](#chapter_05_sub_source)
   * [`untranslated_prefix`](#chapter_05_sub_untranslated_prefix)
   * [`untranslated_suffix`](#chapter_05_sub_untranslated_suffix)

 * [`routing`](#chapter_05_routing)

   * [`cache`](#chapter_05_sub_cache)
   * [`extra_parameters_as_query_string`](#chapter_05_sub_extra_parameters_as_query_string)
   * [`generate_shortest_url`](#chapter_05_sub_generate_shortest_url)
   * [`lazy_routes_deserialize`](#chapter_05_sub_lazy_routes_deserialize)
   * [`lookup_cache_dedicated_keys`](#chapter_05_sub_lookup_cache_dedicated_keys)
   * [`load_configuration`](#chapter_05_sub_load_configuration)
   * [`segment_separators`](#chapter_05_sub_segment_separators)
   * [`suffix`](#chapter_05_sub_suffix)
   * [`variable_prefixes`](#chapter_05_sub_variable_prefixes)

 * [`logger`](#chapter_05_logger)

   * [`level`](#chapter_05_sub_level)
   * [`loggers`](#chapter_05_sub_loggers)

 * [`controller`](#chapter_05_controller)

<div class="pagebreak"></div>

`mailer`
--------

*sfContext Accessor*: `$context->getMailer()`

*Configuration par défaut* :

    [yml]
    mailer:
      class: sfMailer
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        delivery_strategy: realtime
        transport:
          class: Swift_SmtpTransport
          param:
            host:       localhost
            port:       25
            encryption: ~
            username:   ~
            password:   ~

*Configuration par défaut pour l'environnement `test`* :

    [yml]
    mailer:
      param:
        delivery_strategy: none

*Configuration par défaut pour l'environnement `dev`* :

    [yml]
    mailer:
      param:
        delivery_strategy: none

### ~`charset`~

L'option `charset` définit le jeu de caractères à utiliser pour les messages électroniques. Par
défaut, il utilise le paramètre `charset` de `settings.yml`.

### ~`delivery_strategy`~

L'option `delivery_strategy` définit comment les messages e-mail sont livrés par le
mailer. Quatre stratégies sont disponibles par défaut, ce qui devrait convenir à tous les
besoins communs :

 * `realtime`:       Les messages sont envoyés en temps réel.

 * `single_address`: Les messages sont envoyés à une seule adresse.

 * `spool`:          Les messages sont stockés dans une file d'attente.

 * `none`:           Les messages sont tout simplement ignorés.

### ~`delivery_address`~

L'option `delivery_address` définit le bénéficiaire de tous les messages lorsque le
`delivery_strategy` est à `single_address`.

### ~`spool_class`~

L'option `spool_class` définit la classe de spool à utiliser lorsque le
`delivery_strategy` est à `spool`:

  * ~`Swift_FileSpool`~: Les messages sont stockés sur le système de fichiers.

  * ~`Swift_DoctrineSpool`~: Les messages sont stockés dans un modèle de Doctrine.

  * ~`Swift_PropelSpool`~: Les messages sont stockés dans un modèle de Propel.

>**NOTE**
>Lorsque le spool est instancié, l'option ~`spool_arguments`~ est utilisée comme les
>arguments du constructeur.

### ~`spool_arguments`~

L'option `spool_arguments` définit les arguments du constructeur du spool.
Voici les options disponibles pour les classes intégrées des files d'attente :

 * `Swift_FileSpool`:

    * Le chemin absolu du répertoire de file d'attente (les messages sont stockés dans
      ce répertoire)

 * `Swift_DoctrineSpool`:

    * Le modéle de Doctrine à utiliser pour stocker les messages (`MailMessage` par
      défaut)

    * Le nom de la colonne à utiliser pour le stockage de messages (`message` par défaut)

    * La méthode à appeler pour récupérer les messages à envoyer (facultatif).

 * `Swift_PropelSpool`:

    * Le modèle de Propel à utiliser pour stocker les messages (`MailMessage` par défaut)

    * Le nom de la colonne à utiliser pour le stockage de messages (`message` par défaut)

    * La méthode à appeler pour récupérer les messages à envoyer (facultatif). Il
      reçoit le Criteria courant comme un argument.

La configuration ci-dessous montre une configuration typique pour un spool de Doctrine :

    [yml]
    # configuration in factories.yml
    mailer:
      class: sfMailer
      param:
        delivery_strategy: spool
        spool_class:       Swift_DoctrineSpool
        spool_arguments:   [ MailMessage, message, getSpooledMessages ]

### ~`transport`~

L'option `transport` définit le transport à utiliser pour envoyer effectivement des messages
électroniques.

Le paramètre `class` peut être n'importe quelle classe qui implémente `Swift_Transport`,
et trois sont fournis par défaut :

  * ~`Swift_SmtpTransport`~: Utilise un serveur SMTP pour envoyer des messages.

  * ~`Swift_SendmailTransport`~: Utilise `sendmail` pour envoyer des messages.

  * ~`Swift_MailTransport`~: Utilise la fonction native PHP `mail()` pour envoyer
    des messages.

 Vous pouvez configurer le transport en définissant le paramètre `param`. La
section ["Transport Types"](http://swiftmailer.org/docs/transport-types) de
la documentation officielle de Swift Mailer décrit tout ce que vous devez savoir sur
les classes intégrées dans les transports et leurs différents paramètres.

`request`
---------

*sfContext Accessor*: `$context->getRequest()`

*Configuration par défaut* :

    [yml]
    request:
      class: sfWebRequest
      param:
        logging:           %SF_LOGGING_ENABLED%
        path_info_array:   SERVER
        path_info_key:     PATH_INFO
        relative_url_root: ~
        formats:
          txt:  text/plain
          js:   [application/javascript, application/x-javascript, text/javascript]
          css:  text/css
          json: [application/json, application/x-json]
          xml:  [text/xml, application/xml, application/x-xml]
          rdf:  application/rdf+xml
          atom: application/atom+xml

### ~`path_info_array`~

L'option `path_info_array` définit le tableau global PHP qui sera utilisée pour
récupérer des informations. Sur certaines configurations, vous voudrez changer la
valeur par défaut `SERVER` par `ENV`.

### ~`path_info_key`~

L'option `path_info_key` définit la clé sous laquelle l'information `PATH_INFO`
peut être trouvée.

Si vous utilisez ~IIS~ avec un module de réécriture comme `IIFR` ou `ISAPI`, vous devez
changer cette valeur par `HTTP_X_REWRITE_URL`.

### ~`formats`~

L'option `formats` définit un tableau des extensions de fichiers et leur
`Content-Type` correspondant. Il est utilisé par le framework pour gérer automatiquement
le `Content-Type` de la réponse, basée sur l'extension de l'URI de la requête.

### ~`relative_url_root`~

L'option `relative_url_root` d2finit la partie de l'URL avant que le contrôleur
frontal. La plupart du temps, il est automatiquement détecté par le framework
et n'a pas besoin d'être changée.

`response`
----------

*sfContext Accessor*: `$context->getResponse()`

*Configuration par défaut* :

    [yml]
    response:
      class: sfWebResponse
      param:
        logging:           %SF_LOGGING_ENABLED%
        charset:           %SF_CHARSET%
        send_http_headers: true

*Configuration par défaut pour l'environnement `test`* :

    [yml]
    response:
      class: sfWebResponse
      param:
        send_http_headers: false

### ~`send_http_headers`~

L'option `send_http_headers` spécifie si la réponse devrait envoyer des entêtes de réponse HTTP
ainsi que le contenu des réponses. Ce réglage est surtout
utile pour les tests, les entêtes sont envoyées avec la fonction PHP `header()` qui
envoie des avertissements si vous essayez d'envoyer des entêtes, après des sorties.

### ~`charset`~

L'option `charset` définit le jeu de caractères à utiliser pour la réponse. Par défaut,
il utilise le paramètre `charset` de `settings.yml`, qui est ce que vous voulez la plupart
du temps.

### ~`http_protocol`~

L'option `http_protocol` définit la version du protocole HTTP à utiliser pour
la réponse. Par défaut, il vérifie la valeur de `$_SERVER['SERVER_PROTOCOL']` si elle est
disponible ou par défaut à `HTTP/1.0`.

`user`
------

*sfContext Accessor*: `$context->getUser()`

*Configuration par défaut* :

    [yml]
    user:
      class: myUser
      param:
        timeout:         1800
        logging:         %SF_LOGGING_ENABLED%
        use_flash:       true
        default_culture: %SF_DEFAULT_CULTURE%

>**NOTE**
>Par défaut, la classe `myUser` hérite de `sfBasicSecurityUser`,
>qui peut être configurée dans le fichier de configuration
>[`security.yml`](#chapter_08).

### ~`timeout`~

L'option `timeout` définit le timeout pour l'authentification des utilisateurs. Il n'est pas
lié au timeout de la session. Le réglage par défaut dés-authentifie
automatiquement un utilisateur au bout de 30 minutes d'inactivité.

Ce paramètre n'est utilisé que par les classes d'utilisateurs qui héritent de la
classe de base `sfBasicSecurityUser`, ce qui est le cas de la classe générée
`myUser`.

>**NOTE**
>Pour éviter un comportement inattendu, la classe utilisateur force automatiquement la
>durée de vie maximale pour le ramasse-miettes de session (`session.gc_maxlifetime`)
>à une valeur plus grande que le timeout.

### ~`use_flash`~

L'option `use_flash` active ou désactive le composant flash.

### ~`default_culture`~

L'option `default_culture` définit la culture à utiliser par défaut pour un utilisateur qui
arrive sur le site pour la première fois. Par défaut, il utilise le
paramètre `default_culture` de `settings.yml`, qui est votre choix la plupart
du temps.

>**CAUTION**
>Si vous changer le paramètre ~`default_culture`~ dans `factories.yml` ou dans
>`settings.yml`, vous devez effacer les cookies dans votre navigateur pour vérifier
>le résultat.

`storage`
---------

Le factory storage est utilisé par le factory user pour maintenir les données utilisateur entre
les requêtes HTTP.

*sfContext Accessor*: `$context->getStorage()`

*Configuration par défaut* :

    [yml]
    storage:
      class: sfSessionStorage
      param:
        session_name: symfony

*Configuration par défaut pour l'environnement `test`* :

    [yml]
    storage:
      class: sfSessionTestStorage
      param:
        session_path: %SF_TEST_CACHE_DIR%/sessions

### ~`auto_start`~

L'option `auto_start` active ou désactive la fonctionnalité de PHP
d'auto-démarrage de session (via la fonction `session_start()`).

### ~`session_name`~

L'option `session_name` définit le nom du cookie utilisé par symfony pour
stocker la session utilisateur. Par défaut, le nom est `symfony`, ce qui signifie que
toutes vos applications stockeront dans le même cookie (ainsi que l'authentification
et les autorisations correspondantes).

### Les paramètres de `session_set_cookie_params()`

Le factory `storage` appelle la fonction
[`session_set_cookie_params()`](http://www.php.net/session_set_cookie_params)
avec la valeur des options suivantes :

 * ~`session_cookie_lifetime`~: durée de vie du cookie de session, défini en
                                secondes.
 * ~`session_cookie_path`~:   Chemin sur le domaine où le cookie va fonctionner.
                              Utilisez un simple slash (`/`)  pour tous les chemins sur le
                              domaine.
 * ~`session_cookie_domain`~: domaine de cookie, par exemple `www.php.net`. Pour
                              faire des cookies visible sur tous les sous-domaines alors
                              le domaine doit être préfixé avec un point comme `.php.net`.
 * ~`session_cookie_secure`~: Si c'est à `true` alors le cookie ne sera envoyée que sur des connexions
                              sécurisées.
 * ~`session_cookie_httponly`~: Si c'est à `true` alors PHP tentera d'envoyer le flag
                                `httponly` lors du paramétrage du cookie de session.

>**NOTE**
>La description de chaque option provient de la description de la fonction
>`session_set_cookie_params()` sur le site de PHP

### ~`session_cache_limiter`~

Si l'option `session_cache_limiter` est mise, la fonction PHP
[`session_cache_limiter()`](http://www.php.net/session_cache_limiter)
est appelée et la valeur de l'option est passée en argument.

### Options de la base de données spécifique de stockage

Lorsque vous utilisez un stockage qui hérite de la classe `sfDatabaseSessionStorage`,
plusieurs options supplémentaires sont disponibles:

 * ~`database`~:     Le nom de la base de données (obligatoire)
 * ~`db_table`~:     Le nom de la table (obligatoire)
 * ~`db_id_col`~:    Le nom de la colonne de la clé primaire (`sess_id` par défaut)
 * ~`db_data_col`~:  Le nom de la colonne donnée (`sess_data` par défaut)
 * ~`db_time_col`~:  Le nom de la colonne temps (`sess_time` par défaut)

`view_cache_manager`
--------------------

*sfContext Accessor*: `$context->getViewCacheManager()`

*Configuration par défaut* :

    [yml]
    view_cache_manager:
      class: sfViewCacheManager
      param:
        cache_key_use_vary_headers: true
        cache_key_use_host_name:    true

>**CAUTION**
>Ce factory est créé si le paramètre [`cache`](#chapter_04_sub_cache)
>est à `true`.

La plupart de la configuration de ce factory se fait via le factory `view_cache`, qui
définit l'objet du cache sous-jacent utilisé par le gestionnaire de cache la vue.

### ~`cache_key_use_vary_headers`~

L'option `cache_key_use_vary_headers` précise si les clés du cache doivent être
inclus dans la partie des entêtes qui varient. En pratique, cela veut dire que le cache de la page doit
être dépendant de l'entête HTTP, comme spécifié dans le paramètre du cache `vary` (valeur
par défaut : `true`).

### ~`cache_key_use_host_name`~

L'option `cache_key_use_host_name` précise si les clés du cache doivent être
inclus dans la partie du nom de l'hôte. En pratique, cela veut dire que le cache doit être
dépendant du nom de l'hôte (valeur par défaut : `true`).

`view_cache`
------------

*sfContext Accessor*: none (utilisé directement par le factory `view_cache_manager`)

*Configuration par défaut* :

    [yml]
    view_cache:
      class: sfFileCache
      param:
        automatic_cleaning_factor: 0
        cache_dir:                 %SF_TEMPLATE_CACHE_DIR%
        lifetime:                  86400
        prefix:                    %SF_APP_DIR%/template

>**CAUTION**
>Ce factory est uniquement défini si le paramètre [`cache`](#chapter_04_sub_cache)
>est à `true`.

Le factory `view_cache` définit une classe de cache qui doit hériter de
`sfCache` (Voir la section Cache pour plus d'information).

`i18n`
------

*sfContext Accessor*: `$context->getI18N()`

*Configuration par défaut* :

    [yml]
    i18n:
      class: sfI18N
      param:
        source:               XLIFF
        debug:                false
        untranslated_prefix:  "[T]"
        untranslated_suffix:  "[/T]"
        cache:
          class: sfFileCache
          param:
            automatic_cleaning_factor: 0
            cache_dir:                 %SF_I18N_CACHE_DIR%
            lifetime:                  31556926
            prefix:                    %SF_APP_DIR%/i18n

>**CAUTION**
>Ce factory est uniquement défini si le paramètre [`i18n`](#chapter_04_sub_i18n)
>est à `true`.

### ~`source`~

L'option `source` définit le type de conteneur pour les traductions.

*Conteneur intégrés* : `XLIFF`, `SQLite`, `MySQL`, et `gettext`.

### ~`debug`~

L'option `debug` définit le mode de débogage. S'il est défini à `true`, les messages
non-traduits sont décorées avec un préfixe et un suffixe (voir ci-dessous).

### ~`untranslated_prefix`~

Le `untranslated_prefix` définit un préfixe à utiliser pour les messages non-traduits.

### ~`untranslated_suffix`~

Le `untranslated_suffix` définit un suffixe à utiliser pour les messages non-traduits.

### ~`cache`~

L'option `cache` définit un factory de cache anonyme pour être utilisé pour la mise
en cache des données i18n (voir la section cache pour plus d'informations).

`routing`
---------

*sfContext Accessor*: `$context->getRouting()`

*Configuration par défaut* :

    [yml]
    routing:
      class: sfPatternRouting
      param:
        load_configuration:               true
        suffix:                           ''
        default_module:                   default
        default_action:                   index
        debug:                            %SF_DEBUG%
        logging:                          %SF_LOGGING_ENABLED%
        generate_shortest_url:            false
        extra_parameters_as_query_string: false
        cache:                            ~

### ~`variable_prefixes`~

*Par défaut* : `:`

L'option `variable_prefixes` définit la liste des caractêres qui débutent
le nom d'une variable dans un modèle de route.

### ~`segment_separators`~

*Par défaut* : `/` et `.`

L'option `segment_separators` définit la liste des séparateurs de segment de la route.
La plupart du temps, vous ne voulez pas réécrire cette option pour l'ensemble
du routage, mais pour des routes spécifiques.

### ~`generate_shortest_url`~

*Par défaut* : `true` pour les nouveaux projets, `false` pour les projets mis à niveau

Si elle est à `true`, l'option `generate_shortest_url` dira au système de
routage de générer la route la plus courte possible. Réglez-le à `false` si vous voulez
que vos routes soient compatibles avec symfony 1.0 et 1.1.

### ~`extra_parameters_as_query_string`~

*Par défaut* : `true` pour les nouveaux projets, `false` pour les projets mis à niveau

Lorsque certains paramètres ne sont pas utilisés dans la génération d'une route,
l'`extra_parameters_as_query_string` permet à ces paramètres supplémentaires d'être
convertie en une chaîne de caractêre d'une query. Réglez-le à `false` pour revenir sur le comportement
de symfony 1.0 ou 1.1. Dans ces versions, les paramètres supplémentaires étaient tout simplement ignorés
par le système de routage.

### ~`cache`~

*Par défaut* : none

L'option `cache`  L'option cache définit un factory de cache anonyme qui est utilisé pour la mise en cache
de la configuration du routage et des données (voir la section cache pour plus d'informations).

### ~`suffix`~

*Par défaut* : none

La valeur par défaut du suffixe à utiliser pour toutes les routes. Cette option est obsolète et n'est
plus utile.

### ~`load_configuration`~

*Par défaut* : `true`

L'option `load_configuration` définit si les fichiers `routing.yml` doivent
être automatiquement chargés et analysés. Réglez le à `false` si vous souhaitez utiliser le
système de routage de symfony à l'extérieur d'un projet symfony.

### ~`lazy_routes_deserialize`~

*Par défaut* : `false`

Si le paramètre `lazy_routes_deserialize` est à `true`, il permet une relecture
paresseuse du cache de routage. Il peut améliorer les performances de vos
applications si vous avez un grand nombre de route et si la plupart des routes
correspondent aux premières. Il est fortement conseillé de tester le paramètre avant
de le déployer en production, car il peut nuire sur les performances dans certaines
circonstances.

### ~`lookup_cache_dedicated_keys`~

*Par défaut* : `false`

Le paramètre `lookup_cache_dedicated_keys` détermine comment le cache de routage est
construit. Lorsqu'il est positionné à `false`, le cache est stocké comme une grande valeur. Lorsqu'il
est positionné à `true` chaque route a son propre stockage de cache. Ce paramètre est un
paramètre d'optimisation de performance.

En règle générale, la valeur `false` est mieux lorsque vous utilisez une classe de cache
basée sur un fichier (`sfFileCache` par exemple), et la valeur `true` est mieux
lorsque vous utilisez une classe de cache basée sur la mémoire (`sfAPCCache` par exemple). 

`logger`
--------

*sfContext Accessor*: `$context->getLogger()`

*Configuration par défaut* :

    [yml]
    logger:
      class: sfAggregateLogger
      param:
        level: debug
        loggers:
          sf_web_debug:
            class: sfWebDebugLogger
            param:
              level: debug
              condition:       %SF_WEB_DEBUG%
              xdebug_logging:  true
              web_debug_class: sfWebDebug
          sf_file_debug:
            class: sfFileLogger
            param:
              level: debug
              file: %SF_LOG_DIR%/%SF_APP%_%SF_ENVIRONMENT%.log

*Configuration par défaut pour l'environnement de `prod`* :

    [yml]
    logger:
      class:   sfNoLogger
      param:
        level:   err
        loggers: ~

>**CAUTION**
>Ce factory est toujours défini, mais la journalisation se produit que si
>le paramètre `logging_enabled` est à `true`.

### ~`level`~

L'option `level` définit le niveau du journal.

*Valeurs possibles*: `EMERG`, `ALERT`, `CRIT`, `ERR`, `WARNING`, `NOTICE`,
`INFO`, or `DEBUG`.

### ~`loggers`~

L'option `loggers` définit la liste des journaux à utiliser. La liste est un tableau de
factory de journaux anonymes.

*Les classes de journaux intégrées*: `sfConsoleLogger`, `sfFileLogger`, `sfNoLogger`,
`sfStreamLogger`, et `sfVarLogger`.

`controller`
------------

*sfContext Accessor*: `$context->getController()`

*Configuration par défaut* :

    [yml]
    controller:
      class: sfFrontWebController

Les factories de cache anonyme
-------------------------

Plusieurs factories (`view_cache`, `i18n`, et `routing`) peuvent profiter
d'un objet du cache s'il est défini dans leur configuration. La configuration
de l'objet du cache est similaire pour toutes les factories. La clé de `cache` définit
un factory de cache anonyme. Comme tout factory, il prend une `class` et une
entrée `param`. L'entrée `param` peut prendre n'importe quelle option disponible pour la
classe du cache.

L'option `prefix` est la plus importante car elle permet de partager ou de
séparer un cache entre différents environnements/applications/projets.

*Les classes de cache intégrées*: `sfAPCCache`, `sfEAcceleratorCache`, `sfFileCache`,
`sfMemcacheCache`, `sfNoCache`, `sfSQLiteCache`, et `sfXCacheCache`.
