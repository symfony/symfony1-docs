Le fichier de configuration security.yml
===================================

Le fichier de configuration ~`security.yml`~ décrit les règles d'authentification et
d'autorisation pour une application symfony.

>**TIP**
>Les informations de configuration du fichier `security.yml` sont utilisés par
>la classe du factory [`user`](#chapter_05_user) (`sfBasicSecurityUser` par
>défaut). L'exécution de l'authentification et l'autorisation est
> effectuée par le [filtre](#chapter_12_security) `security`.

Lorsqu'une application est créée, symfony génère un fichier par défaut `security.yml`
dans le répertoire `config/` de l'application qui décrit la sécurité pour
toute l'application (sous une clé par défaut) :

    [yml]
    default:
      is_secure: false

Comme indiqué dans l'introduction, le fichier `security.yml` bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

La configuration de l'application par défaut peut être substituée pour un module en
créant un fichier `security.yml` dans le répertoire `config/` du module. Les
clés principales sont les noms des actions sans le préfixe `execute` (`index` pour la
méthode `executeIndex` par exemple).

Pour déterminer si une action est sécurisé ou non, symfony regarde pour l'information
dans l'ordre suivant :

  * une configuration pour l'action spécifique dans le fichier de configuration du module,
    si elle existe;

  * une configuration pour l'ensemble du module dans le fichier de configuration du module si
    elle existe (sous la clé `all`);

  * l'application de configuration par défaut (sous la clé `default`).

Les mêmes règles de priorité sont utilisées pour déterminer les credentials nécessaires
pour accéder à une action.

>**NOTE**
>Le fichier de configuration `security.yml` est mis en cache dans un fichier PHP; le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml)
>~`sfSecurityConfigHandler`~.

~Authentication~
----------------

La configuration par défaut de `security.yml`, installé par défaut pour chaque
application, autorise l'accès à n'importe qui :

    [yml]
    default:
      is_secure: false

Quand le paramètre ~`is_secure`~ sera à `true` dans le fichier `security.yml` de
l'application, l'application entière demandera une authentification pour tous les utilisateurs.

>**NOTE**
>Quand un utilisateur non-authentifié tente d'accéder à une action sécurisée, symfony
>transmet la requête à l'action `login` configurée dans `settings.yml`.

Pour modifier les conditions d'authentification pour un module, créez un fichier `security.yml`
dans le répertoire `config/` du module et définissez la clé `all` :

    [yml]
    all:
      is_secure: true

Pour modifier les conditions d'authentification pour une action individuelle d'un module, créez
un fichier `security.yml` dans le répertoire `config/` du module et définissez une
clé après le nom de l'action :

    [yml]
    index:
      is_secure: false

>**TIP**
>Il n'est pas possible de sécuriser l'action login. Il s'agit d'éviter une récursion
>infinie.

~Authorization~
---------------

Lorsqu'un utilisateur est authentifié, l'accès à certaines actions peuvent être encore
plus limité en définissant les *~credentials~*. Quand les credentials sont définis, l'utilisateur
doit disposer des credentials nécessaires pour accéder à l'action :

    [yml]
    all:
      is_secure:   true
      credentials: admin

Le système des credentials de symfony est simple et puissant. Un credential est une
chaîne qui peut représenter tout ce dont vous avez besoin pour décrire le modèle
de sécurité des applications (comme des groupes ou des autorisations).

Les clés `credentials` supportent les opérations booléennes pour décrire les exigences
complexes des credentials en utilisant le tableau de notation.

Si un utilisateur doit disposer du credential A **et** du credential B, entourez les
credentials avec des crochets :

    [yml]
    index:
      credentials: [A, B]

Si un utilisateur doit disposer du credential A **ou** du credential B, entourez les
credentials avec deux paires de crochets :

    [yml]
    index:
      credentials: [[A, B]]

Vous pouvez également mélanger les crochets pour décrire tout type d'expression booléenne
avec n'importe quel nombre de credential.
