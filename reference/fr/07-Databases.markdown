Le fichier de configuration databases.yml
====================================

Le fichier de configuration ~`databases.yml`~ permet la configuration de
la connexion à la base. Il est utilisé par les deux ORM livré avec symfony : Propel et
Doctrine.

Le fichier principal de configuration `databases.yml` pour le projet peut être trouvés
dans le répertoire `config/`.

>**NOTE**
>La plupart du temps, toutes les applications d'un projet partagent la même
>base. C'est pourquoi le principal fichier de configuration de base de données est dans le
>répertoire `config/` du projet. Vous pouvez bien entendu passer outre la configuration
>par défaut en définissant un fichier de configuration `databases.yml` dans vos
>répertoires de configuration d'application.

Comme indiqué dans l'introduction, le fichier `databases.yml` est
[**sensible à l'environnement**](#chapter_03_sensibilisation_a_l_environnement), bénéficie du
[**mécanisme de configuration en cascade**](#chapter_03_configuration_en_cascade)
et peut inclure [**des constantes**](#chapter_03_constantes).

Chaque connexion décrit dans `databases.yml` doit comprendre un nom, un nom de classe
de gestionnaire de base de données, et un ensemble de paramètres (`param`) utilisé pour configurer
l'objet de base de données :

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      param: { ARRAY OF PARAMETERS }

Le nom `class` doit étendre la classe de base `sfDatabase`.

Si la classe de gestionnaire de base de données ne peut être chargée automatiquement, le chemin `file` peut être
défini et sera automatiquement inclus avant que le factory soit créé :

    [yml]
    CONNECTION_NAME:
      class: CLASS_NAME
      file:  ABSOLUTE_PATH_TO_FILE

>**NOTE**
>Le fichier de configuration `databases.yml` est mis en cache dans un fichier PHP, le
>processus est automatiquement géré par [la classe](#chapter_14_config_handlers_yml) 
>~`sfDatabaseConfigHandler`~.

-

>**TIP**
>La configuration de la base de données peut également être configuré en utilisant la
>tâche `database:configure`. Cette tâche met à jour le `databases.yml`
>selon les arguments que vous lui passez.

Propel
------

*Configuration par défaut* :

    [yml]
    dev:
      propel:
        param:
          classname:  DebugPDO
          debug:
            realmemoryusage: true
            details:
              time:       { enabled: true }
              slow:       { enabled: true, threshold: 0.1 }
              mem:        { enabled: true }
              mempeak:    { enabled: true }
              memdelta:   { enabled: true }

    test:
      propel:
        param:
          classname:  DebugPDO

    all:
      propel:
        class:        sfPropelDatabase
        param:
          classname:  PropelPDO
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          encoding:   utf8
          persistent: true
          pooling:    true

Les paramètres suivants peuvent être personnalisés dans la section `param` :

 | Clé          | Description                              | Valeur par défaut |
 | ------------ | ---------------------------------------- | ----------------- |
 | `classname`  | La classe de l'adaptateur Propel         | `PropelPDO`       |
 | `dsn`        | Le DSN du PDO (obligatoire)              | -                 |
 | `username`   | L'utilisateur de la base                 | -                 |
 | `password`   | Le mot de passe de la base               | -                 |
 | `pooling`    | Pour activer le pooling                  | `true`            |
 | `encoding`   | Le jeu de caractères par défaut          | `UTF8`            |
 | `persistent` | Pour créer des connexions persistantes   | `false`           |
 | `options`    | Une série d'options Propel               | -                 |
 | `debug`      | Options pour la classe `DebugPDO`        | n/a               |

L'entrée `debug` définit toutes les options décrites dans la
[documentation](http://www.propelorm.org/docs/api/1.4/runtime/propel-util/DebugPDO.html#class_details) Propel.
Le YAML suivant montre toutes les options disponibles :

    [yml]
    debug:
      realmemoryusage: true
      details:
        time:
          enabled: true
        slow:
          enabled: true
          threshold: 0.001
        memdelta:
          enabled: true
        mempeak:
          enabled: true
        method:
          enabled: true
        mem:
          enabled: true
        querycount:
          enabled: true

Doctrine
--------

*Configuration par défaut* :

    [yml]
    all:
      doctrine:
        class:        sfDoctrineDatabase
        param:
          dsn:        mysql:dbname=##PROJECT_NAME##;host=localhost
          username:   root
          password:   
          attributes:
            quote_identifier: false
            use_native_enum: false
            validate: all
            idxname_format: %s_idx
            seqname_format: %s_seq
            tblname_format: %s

Les paramètres suivants peuvent être personnalisés dans la section `param` :

 | Clé          | Description                              | Valeur par défaut |
 | ------------ | ---------------------------------------- | ----------------- |
 | `dsn`        | Le DSN du PDO (obligatoire)              | -                 |
 | `username`   | L'utilisateur de la base                 | -                 |
 | `password`   | Le mot de passe de la base               | -                 |
 | `encoding`   | Le jeu de caractères par défaut          | `UTF8`            |
 | `attributes` | Une série d'attributs Doctrine           | -                 |

Les paramètres suivants peuvent être personnalisés dans la section `attributes` :

 | Clé                 | Description                                       | Valeur par défaut |
 | ------------------- | ------------------------------------------------- | ----------------- |
 | `quote_identifier`  | Pour envelopper les identifiants avec des quotes  | `false`           |
 | `use_native_enum`   | Pour utiliser les énumérations native             | `false`           |
 | `validate`          | Pour activer la validation des données            | `false`           |
 | `idxname_format`    | Format pour les noms d'index                      | `%s_idx`          |
 | `seqname_format`    | Format pour les noms de séquence                  | `%s_seq`          |
 | `tblname_format`    | Format pour les noms de table                     | `%s`              |
