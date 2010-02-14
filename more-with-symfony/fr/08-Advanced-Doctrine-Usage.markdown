Techniques Avancées avec Doctrine
=================================

*Par Jonathan H. Wage*

Ecrire un Comportement Doctrine
-------------------------------

L'objectif de ce chapitre est de découvrir comment écrire un comportement (~`behavior`~) pour Doctrine 1.2. Il s'agira de créer un exemple simple qui permet de maintenir à jour un compteur de relations en cache dans un champ d'une table. Cette fonctionnalité permettra ainsi d'éviter d'avoir à demander le résultat d'un dénombrement à chaque appel d'une méthode en réalisant des requêtes supplémentaires.

Ce type de fonctionnalité est relativement simple à mettre en oeuvre. Pour chaque relation dont on souhaite conserver à jour le résultat d'un compteur, le comportement se chargera d'ajouter une colonne supplémentaire au modèle afin de stocker la valeur courante du dénombrement.

### Le Schéma de Données

Le listing ci-dessous décrit le modèle de données utilisé pour commencer. Puis, il sera modifié au fil du chapitre afin d'enregistrer le comportement développé dans la section `actAs` du modèle.

    [yml]
    # config/doctrine/schema.yml
    Thread:
      columns:
        title:
          type: string(255)
          notnull: true

    Post:
      columns:
        thread_id:
          type: integer
          notnull: true
        body:
          type: clob
          notnull: true
      relations:
        Thread:
          onDelete: CASCADE
          foreignAlias: Posts

Le schéma de données établi, il ne reste plus qu'à construire tout le modèle de données associé à l'aide de la tâche `doctrine:build`.

    $ php symfony doctrine:build --all

### Le Template Doctrine

La première étape consiste à écrire une classe basique qui étend la classe  `Doctrine_Template`. Cette classe sera responsable de l'ajout de la colonne qui stocke la valeur courante du compteur dans la classe de modèle associée. Pour ce faire, il suffit de créer le fichier `CountCache.class.php` dans l'un des répertoires `lib/` du projet afin que symfony puisse le charger automatiquement.

    [php]
    // lib/count_cache/CountCache.class.php
    class CountCache extends Doctrine_Template
    {
      public function setTableDefinition()
      {
      }

      public function setUp()
      {
      }
    }

A présent, il est temps de modifier la définition du modèle `Post` afin que  l'objet implémente (`actAs`) le comportement `CountCache`.

    [yml]
    # config/doctrine/schema.yml
    Post:
      actAs:
        CountCache: ~
      # ...

Le modèle `Post` est désormais prêt à utiliser le comportement `CountCache`, bien que quelques explications complémentaires à son sujet soient les bienvenues. Dès lors que la définition du modèle est instanciée, n'importe quel comportement attaché à ce dernier voit ses méthodes `setTableDefinition()` et `setUp()` invoquées comme celles qui se trouvent dans la classe `BasePost` du fichier `lib/model/doctrine/base/BasePost.class.php`. Ce mécanisme permet d'ajouter des choses supplémentaires à n'importe quel modèle à la manière "plug and play". Ces dernières peuvent être aussi bien des colonnes, des relations, des écouteurs d'évènements, etc.

Le fonctionnement général des comportements a été éclairci. Par conséquent, il convient de faire en sorte que le comportement `CountCache` satisfasse réellement un besoin fonctionnel.

    [php]
    class CountCache extends Doctrine_Template
    {
      protected $_options = array(
        'relations' => array()
      );

      public function setTableDefinition()
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          // Build column name if one is not given
          if (!isset($options['columnName']))
          {
            $this->_options['relations'][$relation]['columnName'] = 'num_'.Doctrine_Inflector::tableize($relation);
          }

          // Add the column to the related model
          $columnName = $this->_options['relations'][$relation]['columnName'];
          $relatedTable = $this->_table->getRelation($relation)->getTable();
          $this->_options['relations'][$relation]['className'] = $relatedTable->getOption('name');
          $relatedTable->setColumn($columnName, 'integer', null, array('default' => 0));
        }
      }
    }

Le code ci-dessus ajoute désormais des colonnes pour maintenir à jour le compteur du modèle associé. Ainsi, dans l'étude de cas courante, le comportement est attaché au modèle `Post` sur la relation `Thread` associée. L'objectif est de maintenir le nombre de messages (posts) quelle que soit l'instance de la classe `Thread` dans une colonne nommée `num_posts`. Le modèle de données YAML peut alors être modifié comme ci-après afin de définir l'option complémentaire du comportement.

    [yml]
    # ...

    Post:
      actAs:
        CountCache:
          relations:
            Thread:
              columnName: num_posts
              foreignAlias: Posts
      # ...

Désormais, le modèle `Thread` contient une colonne `num_posts` dont la valeur sera conservée à jour avec le nombre de messages que chaque sujet de discussion possède.

### Ecouter les Evénements

L'étape suivante de la construction du comportement consiste à écrire un écouteur d'événements pour l'enregistrement en cours. Cet écouteur est responsable de la bonne conservation de la valeur du compteur lorsque de nouveaux enregistrements sont insérés en base de données, ou bien lorsqu'un (ou plusieurs) enregistrement(s) est (sont) supprimé(s en DQL).

    [php]
    class CountCache extends Doctrine_Template
    {
      // ...

      public function setTableDefinition()
      {
        // ...

        $this->addListener(new CountCacheListener($this->_options));
      }
    }

Avant d'aller plus loin, la classe `CountCacheListener` doit être définie et étendre `Doctrine_Record_Listener`. Cette classe accepte un tableau d'options transmis simplement du template à l'écouteur.

    [php]
    // lib/model/count_cache/CountCacheListener.class.php
    class CountCacheListener extends Doctrine_Record_Listener
    {
      protected $_options;

      public function __construct(array $options)
      {
        $this->_options = $options;
      }
    }

A présent, les évènements suivants doivent être initialisés dans le but de garder les compteurs à jour en permanence.

 * **postInsert()** incrémente le compteur lorsqu'un nouvel objet est inséré ;

 * **postDelete()** décrémente le compteur lorsqu'un objet est supprimé ;

 * **preDqlDelete()** décrémente les compteurs lorsque les enregistrements 
   sont supprimés à partir d'un ordre DQL DELETE.

Le listing ci-dessous définit tout d'abord la méthode `postInsert()` :

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postInsert(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' + 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

Le code ci-dessus incrémente les compteurs d'une unité pour toutes les relations configurées à l'aide d'une requête DQL UPDATE à chaque fois qu'un objet similaire à celui ci-dessous est inséré.

    [php]
    $post = new Post();
    $post->thread_id = 1;
    $post->body = 'body of the post';
    $post->save();

Le `Thread` ayant `1` pour `id` verra sa colonne `num_posts` augmentée de `1`. Les compteurs sont à présent bien incrémentés lorsque de nouveaux objets sont insérés. Il convient maintenant de gérer la décrémentation des compteurs lorsque les objets sont supprimés en implémentant la méthode `postDelete()` suivante.

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function postDelete(Doctrine_Event $event)
      {
        $invoker = $event->getInvoker();
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $table
            ->createQuery()
            ->update()
            ->set($options['columnName'], $options['columnName'].' - 1')
            ->where($relation['local'].' = ?', $invoker->$relation['foreign'])
            ->execute();
        }
      }
    }

La méthode `postDelete()` ci-dessus est presque identique à la méthode `postInsert()` puisque la seule différence qui les oppose est la décrémentation de `1` de la colonne `num_posts` au lieu de l'incrémentation. Cela se traduit par le code ci-dessous si l'enregistrement `$post` sauvegardé précédemment est supprimé.

    [php]
    $post->delete();

La dernière pièce du puzzle consiste à gérer la mise à jour des compteurs à l'aide d'une requête `DQL DELETE` lorsque plusieurs objets sont supprimés d'un coup. Pour résoudre ce problème, il suffit d'implémenter la méthode `preDqlDelete()`.

    [php]
    class CountCacheListener extends Doctrine_Record_Listener
    {
      // ...

      public function preDqlDelete(Doctrine_Event $event)
      {
        foreach ($this->_options['relations'] as $relation => $options)
        {
          $table = Doctrine::getTable($options['className']);
          $relation = $table->getRelation($options['foreignAlias']);

          $q = clone $event->getQuery();
          $q->select($relation['foreign']);
          $ids = $q->execute(array(), Doctrine::HYDRATE_NONE);

          foreach ($ids as $id)
          {
            $id = $id[0];

            $table
              ->createQuery()
              ->update()
              ->set($options['columnName'], $options['columnName'].' - 1')
              ->where($relation['local'].' = ?', $id)
              ->execute();
          }
        }
      }
    }

Le code ci-dessus clone la requête `DQL DELETE` et la transforme en un `SELECT` qui permet de retrouver la liste des `ID`s qui seront supprimés. Par conséquent, les compteurs peuvent être mis à jour en fonction des enregistrements supprimés.

Le scénario suivant est à présent pris en charge et les compteurs seront décrémentés si le code suivant était exécuté.

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('id = ?', 1)
      ->execute();

Ou bien si plusieurs enregistrements devaient être supprimés, les compteurs seraient eux aussi correctement décrémentés.

    [php]
    Doctrine::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

>**NOTE**
>L'invocation de la méthode `preDqlDelete()` est soumise à l'activation d'un 
>attribut. Les DQL de rappel (`DQL callbacks`) sont désactivés par défaut car 
>ils ont un coût supplémentaire sur les performances. Par conséquent, il doivent 
>être explicitement activés afin de pouvoir les utiliser.
>
>     [php]
>     $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

C'est tout ! Le comportement Doctrine est terminé, mais la dernière chose qui reste à faire consiste à le tester un peu.

### Tester le Comportement

Le code est désormais implémenté et peut donc être testé avec quelques jeux de données de test.

    [yml]
    # data/fixtures/data.yml

    Thread:
      thread1:
        title: Test Thread
        Posts:
          post1:
            body: This is the body of my test thread
          post2:
            body: This is really cool
          post3:
            body: Ya it is pretty cool

Il ne reste plus qu'à tout reconstruire et charger les données de test.

    $ php symfony doctrine:build --all --and-load

Maintenant que l'ensemble est reconstruit et que les données de test sont chargées, un test peut être exécuté afin de s'assurer que les compteurs ont bien été mis à jour:

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '3'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '1'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is the body of my test thread'
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

La colonne `num_posts` de la classe de modèle `Thread` dispose bien de la valeur trois. Si l'un des posts est amené à être supprimé avec le code suivant, alors il décrémentera automatiquement le compteur associé de l'objet `Thread`.

    [php]
    $post = Doctrine_Core::getTable('Post')->find(1);
    $post->delete();

Le listing ci-dessous prouve que l'enregistrement est bien supprimé et que le compteur a bien été mis à jour.

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '2'
    doctrine -   Posts:
    doctrine -     -
    doctrine -       id: '2'
    doctrine -       thread_id: '1'
    doctrine -       body: 'This is really cool'
    doctrine -     -
    doctrine -       id: '3'
    doctrine -       thread_id: '1'
    doctrine -       body: 'Ya it is pretty cool'

Cela fonctionne de la même manière si les deux enregistrements restants sont supprimés en même temps à l'aide d'une requête DQL.

    [php]
    Doctrine_Core::getTable('Post')
      ->createQuery()
      ->delete()
      ->where('body LIKE ?', '%cool%')
      ->execute();

A présent, tous les posts associés au sujet de discussion ont été supprimés et la colonne `num_posts` devrait ainsi conserver la valeur zéro.

    $ php symfony doctrine:dql "FROM Thread t, t.Posts p"
    doctrine - executing: "FROM Thread t, t.Posts p" ()
    doctrine -   id: '1'
    doctrine -   title: 'Test Thread'
    doctrine -   num_posts: '0'
    doctrine -   Posts: {  }

C'est tout! J'espère que cet article vous a été utile dans le sens où vous avez appris quelque chose de nouveau au sujet des comportements. De plus, j'espère que ce comportement vous sera également utile.

Utiliser le Cache des Résultats Doctrine
----------------------------------------

Dans la plupart des applications web à fort trafic, il est commun de cacher de l'information afin d'économiser des ressources CPU. Avec la dernière version de Doctrine 1.2, de nombreuses améliorations ont été réalisées au niveau du cache des jeux de résultats afin d'offrir au développeur davantage de contrôle. En effet, le développeur a désormais plus de contrôle lorsqu'il s'agit de supprimer des entrées du cache depuis les gestionnaires de cache.

Autrefois, il était impossible de spécifier la clé de cache utilisée pour stocker une entrée dans le cache. Par conséquent, l'entrée cachée ne pouvait être véritablement identifiée en vue de la supprimer.

Cette section présentera, à partir d'un exemple simple, comment utiliser le cache de jeux de résultats afin de mettre en cache toutes les requêtes relatives à l'utilisateur courant. Cette mise en cache sera réalisée de la même manière qu'en utilisant des événements afin de s'assurer qu'elles sont correctement nettoyées lorsque des données évoluent.

### Le Modèle de Données

Pour cet exemple, le schéma suivant est utilisé.

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username:
          type: string(255)
          notnull: true
          unique: true
        password:
          type: string(255)
          notnull: true

Une fois le schéma recopié, l'ensemble du projet peut alors être reconstruit à l'aide de la commande suivante.

    $ php symfony doctrine:build --all

Ceci étant fait, la classe `User` ci-après devrait avoir été générée par Doctrine.

    [php]
    // lib/model/doctrine/User.class.php
    /**
     * User
     *
     * This class has been auto-generated by the Doctrine ORM Framework
     *
     * @package    ##PACKAGE##
     * @subpackage ##SUBPACKAGE##
     * @author     ##NAME## <##EMAIL##>
     * @version    SVN: $Id: Builder.php 6508 2009-10-14 06:28:49Z jwage $
     */
    class User extends BaseUser
    {
    }

Il est important de noter que cette classe accueillera du code supplémentaire plus loin dans cet article.

### Configurer le Cache de Résultats

Afin de pouvoir mettre en oeuvre le cache de résultats, un gestionnaire de cache doit d'abord être configuré pour les requêtes utilisées. Cette étape se réalise très simplement en configurant l'attribut `ATTR_RESULT_CACHE`. 

Dans cet article, c'est le gestionnaire de cache APC qui a été retenu dans la mesure où c'est le meilleur choix pour l'environnement de production. Si APC n'est pas disponible sur le serveur de développement, alors celui-ci pourra aussi bien se contenter des pilotes `Doctrine_Cache_Db` ou bien `Doctrine_Cache_Array` pour des besoins de test.

Cet attribut est configurable dans la classe de configuration du projet, `ProjectConfiguration`. Il suffit pour cela de déclarer une méthode `configureDoctrine()` comme expliqué ci-dessous.

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, new Doctrine_Cache_Apc());
      }
    }

Maintenant que le gestionnaire de cache des résultats est configuré, il peut désormais être testé pour cacher les jeux de résultats des requêtes exécutées.

### Exemples de Requêtes

Supposons que l'application dispose d'un certain nombre de requêtes relatives à l'utilisateur courant, et qu'elles doivent être nettoyées à chaque fois que les données de l'utilisateur sont modifiées. Le code ci-dessous présente une requête simple qui pourrait servir à rendre la liste des utilisateurs triés par ordre alphabétique.

    [php]
    $q = Doctrine_Core::getTable('User')
        ->createQuery('u')
        ->orderBy('u.username ASC');

A présent, le cache peut être activé pour cette requête en utilisant la méthode `useResultCache`.

    [php]
    $q->useResultCache(true, 3600, 'users_index');

>**NOTE**
>Notez le troisième argument. Il s'agit de la clé qui sera utilisée pour stocker 
>l'entrée de cache des résultats dans le gestionnaire de cache. Cela permet 
>ainsi d'identifier clairement cette requête afin de la supprimer du 
>gestionnaire de cache.

Désormais, lorsque la requête est exécutée, elle interroge tout d'abord la base de données pour obtenir les résultats. Puis, elle stocke ces derniers dans le gestionnaire de cache à la clé `users_index`, et ainsi, toutes les requêtes ultérieures iront chercher l'information dans le gestionnaire au lieu d'attaquer directement la base de données:

    [php]
    $users = $q->execute();

>**NOTE**
>Non seulement ce système fait économiser du traitement au serveur de base de 
>données, il contourne également le processus complet d'hydratation puisque 
>Doctrine sauvegarde les données déjà hydratées. Cela signifie que le serveur 
>web en sera d'autant plus soulagé.

A présent, si l'on contrôle le gestionnaire de cache, on découvrira une entrée nommée `users_index`.

    [php]
    if ($cacheDriver->contains('users_index'))
    {
      echo 'cache exists';
    }
    else
    {
      echo 'cache does not exist';
    }

### Supprimer le Cache

A ce stade, la requête est cachée, et il est temps d'en apprendre un peu plus au sujet de la suppression du cache. Le cache peut être supprimé manuellement en utilisant l'API du gestionnaire de cache ou bien en invoquant quelques événements pour nettoyer l'entrée de cache automatiquement lorsqu'un utilisateur est ajouté ou modifié.

#### L'API du Gestionnaire de Cache

Tout d'abord, il s'agit de présenter l'API brute du gestionnaire de cache avant de lui faire implémenter un nouvel événement.

>**TIP**
>Pour avoir accès à l'instance du gestionnaire de cache des résultats, il suffit 
>de faire appel à l'instance de la classe `Doctrine_Manager`.
>
>     [php]
>     $cacheDriver = $manager->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
>
>Si l'accès à la variable `$manager` est impossible, l'instance reste disponible 
>à l'aide du code suivant:
>
>     [php]
>     $manager = Doctrine_Manager::getInstance();

Il ne reste alors plus qu'à utiliser l'API du gestionnaire de cache pour supprimer des entrées du cache.

    [php]
    $cacheDriver->delete('users_index');

Cependant, il est probable qu'il y ait plus d'une requête préfixée par `users_`, c'est pourquoi il convient de supprimer le cache de résultats pour toutes ces requêtes. Dans cet exemple, la méthode `delete()` actuelle ne fonctionnera pas. Par résoudre ce problème, Doctrine fournit une méthode nommée `deleteByPrefix()` qui supprime n'importe quelle entrée du cache qui contient le préfixe passé en paramètre comme le montre l'exemple suivant.

    [php]
    $cacheDriver->deleteByPrefix('users_');

Il existe d'autres méthodes très utiles facilitant la suppression des entrées du cache si la méthode `deleteByPrefix()` ne suffit pas à elle-même.

 * `deleteBySuffix($suffix)` supprime les entrées du cache enregistrées avec le 
   suffixe passé en paramètre ;

 * `deleteByRegex($regex)` supprime les entrées du cache qui 
   correspondent à l'expression régulière passée en paramètre ;

 * `deleteAll()` supprime toutes les entrées du cache.

### Supprimer des Entrées du Cache à l'Aide des Evénements

La méthode idéale pour nettoyer le cache serait de le faire à chaque fois que les données de l'utilisateur sont modifiées. Pour y parvenir, il suffit d'implémenter un événement `postSave()` dans la classe de définition du modèle `User`.

Souvenez-vous de la classe `User` déclarée au tout début de ce chapitre. L'étape suivante consiste à lui implémenter la méthode `postSave()` ci-dessous afin de régénérer le cache des résultats à chaque fois que l'objet est modifié.

    [php]
    // lib/model/doctrine/User.class.php

    class User extends BaseUser
    {
      // ...

      public function postSave($event)
      {
        $cacheDriver = $this->getTable()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $cacheDriver->deleteByPrefix('users_');
      }
    }

Grâce à ces quelques lignes, le cache des requêtes spécifiques à l'utilisateur sera nettoyé à chaque fois que ce dernier sera mis à jour ou ajouté dans la base de données.

    [php]
    $user = new User();
    $user->username = 'jwage';
    $user->password = 'changeme';
    $user->save();

La prochaine fois que les requêtes seront exécutées, Doctrine se chargera de récupérer les données à jour en provenance de la base de données dans la mesure où le cache n'existe pas encore. Ce n'est qu'après la récupération des enregistrements que ces derniers seront mis en cache pour toutes les requêtes ultérieures.

Développer un Hydrator Doctrine
--------------------------------

L'une des principales fonctionnalités de Doctrine est sa capacité à transformer un objet `Doctrine_Query` en différents types de jeux de résultats. Cette transformation est assurée par les hydrators Doctrine.

Jusqu'à Doctrine 1.2, les hydrators étaient codés en dur et figés, ce qui empêchait les développeurs d'écrire et d'utiliser les leurs. Heureusement, cette contrainte n'est plus et il désormais possible d'écrire des hydrators personnalisés. Par conséquent, n'importe quelle structure de données peut être créée afin de formater les résultats de la base de données lorsque une instance de la classe `Doctrine_Query` est exécutée.

L'exemple présenté plus loin explique comment développer un hydrator à la fois simple et facile à comprendre, mais s'avère néanmoins très utile. Cet objet permettra de sélectionner deux valeurs et d'hydrater les données dans un tableau associatif dont la première colonne sera la clé et la seconde la valeur.

### Le Modèle de Données et les Données de Test

Avant de débuter, il est nécessaire d'avoir un modèle de données épuré avec lequel seront exécutés quelques tests. Pour y parvenir, un simple modèle `User` suffit comme le présente le listing ci-dessous.

    [yml]
    # config/doctrine/schema.yml
    User:
      columns:
        username: string(255)
        is_active: string(255)

Afin de pouvoir tester le fonctionnement de l'hydrator, le modèle `User` doit disposer de quelques jeux de tests sommaires. Le listing ci-dessous fait état de deux objets `User`.

    [yml]
    # data/fixtures/data.yml
    User:
      user1:
        username: jwage
        password: changeme
        is_active: 1
      user2:
        username: jonwage
        password: changeme
        is_active: 0

Ces données de tests peuvent désormais être chargées dans la base de données à l'aide de la commande suivante.

    $ php symfony doctrine:build --all --and-load

### Développer l'Hydrator

L'écriture d'un hydrator personnalisé nécessite de déclarer une nouvelle classe dérivée de la classe abstraite `Doctrine_Hydrator_Abstract`, puis d'implémenter une méthode `hydrateResultSet($stmt)`. Cette méthode reçoit en argument une instance de la classe `PDOStatement` utilisée pour exécuter la requête SQL. Par conséquent, cet objet peut être utilisé pour obtenir les résultats bruts de la requête grâce à PDO, puis de les transformer en une structure personnalisée.

Pour y parvenir, il suffit de créer une nouvelle classe `KeyValuePairHydrator` dans le répertoire `lib` du projet afin que symfony puisse la charger automatiquement.

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        return $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
      }
    }

En l'état, le code ci-dessus retourne les données brutes grâce à PDO, ce qui ne correspond pas vraiment aux spécifications techniques. Il s'agit donc de transformer ces données en une structure personnalisée de paires clé => valeur. Un modification mineure de la méthode `hydrateResultSet()` permet d'y parvenir.

    [php]
    // lib/KeyValuePairHydrator.class.php
    class KeyValuePairHydrator extends Doctrine_Hydrator_Abstract
    {
      public function hydrateResultSet($stmt)
      {
        $results = $stmt->fetchAll(Doctrine_Core::FETCH_NUM);
        $array = array();
        foreach ($results as $result)
        {
          $array[$result[0]] = $result[1];
        }

        return $array;
      }
    }

Ce fut facile, n'est-ce pas ? Le code de l'objet hydrator est désormais terminé et il répond parfaitement aux besoins. Il ne reste donc plus qu'à le tester pour s'assurer qu'il fonctionne correctement.

### Utiliser l'Hydrator

Pour utiliser et tester l'hydrator, il est impératif de l'enregistrer afin que Doctrine ait connaissance de la classe d'hydrator précédemment écrite lorsque les requêtes sont exécutées. Pour y parvenir, elle doit être enregistrée grâce à l'instance `Doctrine_Manager` depuis la classe `ProjectConfiguration`.

    [php]
    // config/ProjectConfiguration.class.php

    // ...
    class ProjectConfiguration extends sfProjectConfiguration
    {
      // ...

      public function configureDoctrine(Doctrine_Manager $manager)
      {
        $manager->registerHydrator('key_value_pair', 'KeyValuePairHydrator');
      }
    }

L'hydrator est à présent enregistré et peut être utilisé avec des instances de la classe `Doctrine_Query` comme le montre l'exemple ci-dessous.

    [php]
    $q = Doctrine_Core::getTable('User')
      ->createQuery('u')
      ->select('u.username, u.is_active');

    $results = $q->execute(array(), 'key_value_pair');
    print_r($results);

L'exécution de ce code avec les jeux de données de tests définis plus haut provoque le résultat suivant.

    Array
    (
        [jwage] => 1
        [jonwage] => 0
    )

Il n'en faut pas plus pour réaliser un hydrator aussi simplement. J'espère donc qu'il vous sera utile et que la communauté n'hésitera pas à contribuer en retour en développant de nouveaux hydrators pour Doctrine.