Quoi de neuf dans symfony 1.3/1.4 ?
===================================

Ce tutoriel est une rapide introduction technique pour symfony 1.3/1.4.
Il est destiné aux développeurs qui ont déjà travaillé avec symfony 1.2
et qui veulent apprendre rapidement les nouvelles fonctionnalités de symfony 1.3/1.4.

Tout d'abord, merci de noter que symfony 1.3/1.4 est compatible avec PHP 5.2.4 ou ultérieur.

Si vous souhaitez mettre à niveau la 1.2, merci de lire le fichier
[UPGRADE](http://www.symfony-project.org/tutorial/1_4/fr/upgrade)
qui se trouve dans la distribution symfony.
Vous avez toutes les informations nécessaires pour mettre à niveau
en toute sécurité vos projets en symfony 1.3/1.4.

Logiciel de messagerie
------

A partir de symfony 1.3/1.4, il existe par défaut un nouveau logiciel de messagerie basé sur le projet SwiftMailer 4.1.

L'envoi d'un courriel est aussi simple que d'utiliser la méthode `composeAndSend()` à partir
d'une action :

    [php]
    $this->getMailer()->composeAndSend('from@example.com', 'to@example.com', 'Subject', 'Body');

Si vous avez besoin d'avoir plus de flexibilité, vous pouvez également utiliser la méthode `compose()`
et l'envoyer par la suite. Voici par exemple, comment ajouter une pièce jointe
au message :

    [php]
    $message = $this->getMailer()->
      compose('from@example.com', 'to@example.com', 'Subject', 'Body')->
      attach(Swift_Attachment::fromPath('/path/to/a/file.zip'))
    ;
    $this->getMailer()->send($message);

Comme le logiciel de messagerie est assez conséquent, reportez vous à la documentation
pour plus d'informations.

Sécurité
--------

Lorsqu'une nouvelle application est créée avec la tâche `generate:app`, les paramètres
de sécurité sont maintenant activés par défaut :

  * `escaping_strategy`: La valeur par défaut est maintenant à `true` (elle peut être
    désactivée avec l'option `--escaping-strategy`).

  * `csrf_secret`: Un mot de passe aléatoire est généré par défaut, et donc, la
    protection CSRF est activée par défaut (elle peut être désactivée avec
    l'option `--csrf-secret`). Il est fortement recommandé de modifier le
    mot de passe généré par défaut, en éditant le fichier de configuration `settings.yml`
    ou en utilisant l'option `--csrf-secret`.

Les widgets
-------

### Labels par défaut

Lorsqu'un label est généré automatiquement à partir du nom du champ, les suffixes `_id` sont maintenant
supprimées :

  * `first_name` => First name (comme avant)
  * `author_id` => Author (c'était "Author id" avant)

### `sfWidgetFormInputText`

La classe `sfWidgetFormInput` est maintenant abstraite. Les champs `text input` sont maintenant
créés avec la classe `sfWidgetFormInputText`. Ce changement a été fait pour faciliter
l'introspection des classes du formulaire.

### Les widgets I18n

Les widgets suivants ont été ajoutés :

  * `sfWidgetFormI18nChoiceLanguage`
  * `sfWidgetFormI18nChoiceCurrency`
  * `sfWidgetFormI18nChoiceCountry`
  * `sfWidgetFormI18nChoiceTimezone`

Les trois premiers remplacent les widgets maintenant dépréciés :
`sfWidgetFormI18nSelectLanguage`, `sfWidgetFormI18nSelectCurrency`, et
`sfWidgetFormI18nSelectCountry`.

### Fluent Interface

Les widgets sont désormais implémentés d'une interface fluide pour les méthodes suivantes :

  * `sfWidgetForm`: `setDefault()`, `setLabel()`, `setIdFormat()`,
    `setHidden()`

  * `sfWidget`: `addRequiredOption()`, `addOption()`, `setOption()`,
    `setOptions()`, `setAttribute()`, `setAttributes()`

  * `sfWidgetFormSchema`: `setDefault()`, `setDefaults()`,
    `addFormFormatter()`, `setFormFormatterName()`, `setNameFormat()`,
    `setLabels()`, `setLabel()`, `setHelps()`, `setHelp()`, `setParent()`

  * `sfWidgetFormSchemaDecorator`: `addFormFormatter()`,
    `setFormFormatterName()`, `setNameFormat()`, `setLabels()`, `setHelps()`,
    `setHelp()`, `setParent()`, `setPositions()`

Validators
----------

### `sfValidatorRegex`

Le `sfValidatorRegex` a une nouvelle option : `must_match`. Si elle est mise à false, la
regex ne doit pas correspondre au validator à passer.

L'option `pattern` de `sfValidatorRegex` doit maintenant être une instance de
`sfCallable` qui retourne la regex quand elle est appelée.

### `sfValidatorUrl`

Le `sfValidatorUrl` a une nouvelle option : `protocols`. Cela vous permet de spécifier
quels sont les protocoles autorisés :

    [php]
    $validator = new sfValidatorUrl(array('protocols' => array('http', 'https')));

Les protocoles suivants sont autorisés par défaut :

 * `http`
 * `https`
 * `ftp`
 * `ftps`

### `sfValidatorSchemaCompare`

La classe `sfValidatorSchemaCompare` a 2 nouveaux comparateurs :

 * `IDENTICAL`, qui est équivalent à `===`;
 * `NOT_IDENTICAL`, qui est équivalent à `!==`;

### `sfValidatorChoice`, `sfValidatorPropelChoice`, `sfValidatorDoctrineChoice`

Les validators `sfValidatorChoice`, `sfValidatorPropelChoice`,
`sfValidatorDoctrineChoice` ont deux nouvelles options qui sont activées
seulement si l'option `multiple` est à `true`:

 * `min` Le nombre minimum de valeurs qui doivent être sélectionnées
 * `max` Le nombre maximum de valeurs qui doivent être sélectionnées

### Les validators I18n

Le validator suivant a été ajouté :

 * `sfValidatorI18nTimezone`

### Messages d'erreur par défaut

Vous pouvez maintenant définir globalement les messages d'erreurs en utilisant la
méthode `sfValidatorBase::setDefaultMessage()` :

    [php]
    sfValidatorBase::setDefaultMessage('required', 'This field is required.');

Le code précédent écrase le message par défaut 'Required.' pour tous les
validators. Notez que les messages par défaut doivent être définis avant la
création du validator (la classe de configuration est un bon endroit).

>**NOTE**
>Les méthodes `setRequiredMessage()` et `setInvalidMessage()` sont
>dépréciées et veuillez appeler la nouvelle méthode `setDefaultMessage()`.

Quand symfony affiche une erreur, le message d'erreur à utiliser est déterminé de la
manière suivante :

 * Symfony cherche un message transmis lorsque le validator a été créé (par le
   second argument du constructeur du validator);
   
 * Si il n'est pas défini, il recherche un message par défaut défini avec
   la méthode `setDefaultMessage()`;

 * Si il n'est pas défini, il revient au message par défaut défini par le
   validator lui-même (lorsque le message a été ajouté avec la méthode
   `addMessage()`).

### Fluent Interface

Les validators sont désormais implémentés d'une interface fluide pour les méthodes suivantes :

  * `sfValidatorSchema`: `setPreValidator()`, `setPostValidator()`

  * `sfValidatorErrorSchema`: `addError()`, `addErrors()`

  * `sfValidatorBase`: `addMessage()`, `setMessage()`, `setMessages()`,
    `addOption()`, `setOption()`, `setOptions()`, `addRequiredOption()`

### `sfValidatorFile`

Une exception est levée lors de la création d'une instance de sfValidatorFile si
`file_uploads` est désactivé dans `php.ini`.

Formulaires
-----

### `sfForm::useFields()`

La nouvelle méthode `sfForm::useFields()` supprime tous les champs non-cachés d'un formulaire,
sauf ceux indiqués en argument. Il est parfois plus facile de donner explicitement
les champs que vous souhaitez laisser dans le formulaire, au lieu de déclarer tous les
champs inutiles. Par exemple, lors de l'ajout de nouveaux champs à un formulaire de base, ils n'apparaîtront
pas automatiquement dans votre formulaire tant que vous ne les ajouterez pas explicitement (pensez à un modèle
de formulaire où vous ajoutez une nouvelle colonne liée à une table).

    [php]
    class ArticleForm extends BaseArticleForm
    {
      public function configure()
      {
        $this->useFields(array('title', 'content'));
      }
    }

Par défaut, le tableau des champs est aussi utilisé pour changer l'ordre des champs. Vous
pouvez passer le second argument de `useFields()` à `false` pour désactiver
la réorganisation automatique.
 
### `sfForm::getEmbeddedForm($name)`

Vous pouvez désormais accéder à un formulaire imbriqué en particulier en utilisant
la méthode `->getEmbeddedForm()`.

### `sfForm::renderHiddenFields()`

La méthode `->renderHiddenFields()` rend désormais les champs masqués des formulaires
imbriqués. Un argument a été ajouté pour désactiver la récursivité, c'est utile si vous rendez
des formulaires imbriqués utilisant un formateur.

    [php]
    // Rend tous les champs cachés, y compris ceux des formulaires imbriqués
    echo $form->renderHiddenFields();

    // Rend tous les champs cachés sans récursivité
    echo $form->renderHiddenFields(false);

### `sfFormSymfony`

La nouvelle classe `sfFormSymfony` introduit le dispatcher d'événement aux formulaires
de symfony. Vous pouvez accéder au dispatcher à l'intérieur de vos classes de formulaire
par `self::$dispatcher`. Les événements de formulaire suivants sont à présent notifiés par symfony :

  * `form.post_configure`:   Cet événement est notifié après chaque formulaire
                             configuré
  * `form.filter_values`:    Cet événement filtre les paramètres fusionnés, corrompus
                             et les tableaux de fichiers juste avant la liaison
  * `form.validation_error`: Cet événement est notifié quand la validation du formulaire
                             échoue
  * `form.method_not_found`: Cet événement est notifié quand une méthode inconnue
                             est appelé

### `BaseForm`

Chaque nouveau projet symfony 1.3/1.4 inclut la classe `BaseForm` que vous pouvez utiliser pour
étendre l'élément du formulaire ou ajouter des fonctionnalités spécifiques au projet. Les formulaires
générés par `sfDoctrinePlugin` et `sfPropelPlugin` sont automatiquement étendus de cette
classe. Si vous créez des classes de formulaire supplémentaires, elles doivent maintenant étendre `BaseForm`
plutôt que `sfForm`.

### `sfForm::doBind()`

Le nettoyage des paramètres contaminés a été isolé dans une méthode
conviviale, `->doBind()`, qui reçoit un tableau fusionné de paramètre et de fichier
à partir de `->bind()`.

### `sfForm(Doctrine|Propel)::doUpdateObject()`

Les classes de formulaire de Doctrine et Propel incluent maintenant une méthode
conviviale `->doUpdateObject()`. Cette méthode recoit un tableau de valeurs de
`->updateObject()` qui a déjà été traité par `->processValues()`.

### `sfForm::enableLocalCSRFProtection()` et `sfForm::disableLocalCSRFProtection()`

En utilisant les méthodes `sfForm::enableLocalCSRFProtection()` et
`sfForm::disableLocalCSRFProtection()`, vous pouvez désormais facilement configurer
la protection CSRF de la méthode `configure()` de vos classes de formulaire.

Pour désactiver la protection CSRF pour un formulaire, ajoutez les lignes suivantes dans sa
méthode `configure()` :

    [php]
    $this->disableLocalCSRFProtection();

En appelant `disableLocalCSRFProtection()`, la protection CSRF sera
désactivée, même si vous passez un secret CSRF lorsque vous créez une instance de formulaire.

### Fluent Interface

Plusieurs méthodes `sfForm` sont maintenant implémentées "fluent interface" : `addCSRFProtection()`,
`setValidators()`, `setValidator()`, `setValidatorSchema()`, `setWidgets()`,
`setWidget()`, `setWidgetSchema()`, `setOption()`, `setDefault()`, et
`setDefaults()`.

Autoloaders
-----------

Tous les chargeurs automatiques de symfony sont maintenant insensibles à la casse. PHP est insensible à la casse, maintenant
c'est symfony.

### `sfAutoloadAgain` (EXPERIMENTAL)

Un chargeur automatique spécial a été ajouté juste pour une utilisation en mode débogage. La
nouvelle classe `sfAutoloadAgain` rechargera le chargeur automatique standard de symfony et
cherchera le fichier système pour la classe en question. L'effet net est que vous n'avez plus
à lancer `symfony cc` après l'ajout d'une nouvelle classe à un projet.

Tests
-----

### Accélérer les tests

Quand vous avez une grande série de tests, cela peut prendre beaucoup de temps pour lancer
tous les tests à chaque fois que vous effectuez un changement, surtout si certains tests échouent. C'est
parce que chaque fois que vous corriger un test, vous devez exécuter de nouveau la série de tests
pour vous assurer que vous n'avez pas cassé autre chose. Mais tant que ces tests ne sont pas
corrigés, il est inutile de ré-exécuter toutes les autres tests. A partir de
symfony 1.3/1.4, les tâches `test:all et `symfony:test` ont l'option `--only-failed`
(`-f` comme raccourci) qui force la tâche à seulement ré-exécuter les tests qui
ont échoués au cours de l'exécution précédente :

    $ php symfony test:all --only-failed

Voici comment cela fonctionne: la première fois, tous les tests sont exécutés comme d'habitude. Mais
pour les séries de tests suivants, seuls les tests qui ont échoués la dernière fois, seront exécutés. Comme vous
corriger votre code, certains tests vont passer, et seront supprimés à partir des séries précédentes.
Lorsque tous les tests passent correctement, tous les tests sont exécutés ... vous pouvez les répéter
à volonté.

### Tests fonctionnels

Quand une requête génère une exception, la méthode `debug()` du testeur de réponse affiche
maintenant une représentation de texte lisible de l'exception, au lieu de
l'affichage habituelle en HTML. Elle rend le débogage plus facile.

`sfTesterResponse` a une nouvelle méthode `matches()` qui exécute un regex sur l'ensemble
du contenu d'une réponse. Il est d'une grande aide sur des réponses non-XML, la où
`checkElement()` n'est pas utilisable. Il remplace aussi la méthode
moins puissante `contains()` :

    [php]
    $browser->with('response')->begin()->
      matches('/Il a \d+ pommes/')->    // il prend un regex comme argument
      matches('!/Il a \d+ pommes/')->   // Le ! au début signifie que le regex ne doit pas correspondre
      matches('!/Il a \d+ pommes/i')->  // Vous pouvez également ajouter des modificateurs de regex
    end();

### Sortie XML compatible JUnit

Les tâches de test sont désormais en mesure de produire un fichier XML compatible JUnit en utilisant
l'option `--xml` :

    $ php symfony test:all --xml=log.xml

### Le débogage facile

Pour faciliter le débogage lorsqu'une batterie de test rapporte des erreurs, vous pouvez maintenant
passer l'option `--trace` pour avoir une sortie détaillée sur les échecs :

    $ php symfony test:all -t

### La colorisation des résultats de lime

A partir de symfony 1.3/1.4, lime fait ce qu'il faut quand la colorisation est
concernée. Cela signifie que vous pouvez presque toujours omettre le deuxième argument
`lime_test` du constructeur de lime :

    [php]
    $t = new lime_test(1);

### `sfTesterResponse::checkForm()`

Le testeur de réponse inclut désormais une méthode permettant de vérifier facilement que tous
les champs d'un formulaire ont été rendus à la réponse :

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm')->
    end();

Ou, si vous préférez, vous pouvez passer un objet de formulaire :

    [php]
    $browser->with('response')->begin()->
      checkForm($browser->getArticleForm())->
    end();

Si la réponse comprend plusieurs formulaires, vous avez la possibilité de fournir un sélecteur CSS
pour identifier quelle partie du DOM est à tester :

    [php]
    $browser->with('response')->begin()->
      checkForm('ArticleForm', '#articleForm')->
    end();

### `sfTesterResponse::isValid()`

Vous pouvez maintenant vérifier si une réponse est au bon format XML avec la
méthode de testeur de réponse `->isValid()` :

    [php]
    $browser->with('response')->begin()->
      isValid()->
    end();

Vous validez également à nouveau le type de document de la réponse en passant `true` comme
argument :

    [php]
    $browser->with('response')->begin()->
      isValid(true)->
    end(); 

Alternativement, si vous avez un schéma XSD ou RelaxNG à valider à nouveau, vous
pouvez fournir le chemin de ce fichier :

    [php] 
    $browser->with('response')->begin()-> 
      isValid('/path/to/schema.xsd')->
    end();

### Ecouter `context.load_factories`

Vous pouvez maintenant ajouter des écouteurs, pour vos tests fonctionnels, pour l'événement
`context.load_factories`. Cela n'était pas possible dans les versions précédentes de symfony.

    [php]
    $browser->addListener('context.load_factories', array($browser, 'listenForNewContext'));

### Un meilleur `->click()`

Vous pouvez maintenant passer un sélecteur CSS à la méthode `->click()`, ceci permet de 
cibler beaucoup plus facilement l'élément que vous voulez sémantiquement.

    [php]
    $browser
      ->get('/login')
      ->click('form[action$="/login"] input[type="submit"]')
    ;

Tâches
-----

Le CLI de symfony tente maintenant de détecter la largeur de votre fenêtre de terminal et
adapte le format des lignes. Si la détection est impossible, la CLI par défaut est de 78
colonnes de large.

### `sfTask::askAndValidate()`

Il y a une nouvelle méthode `sfTask::askAndValidate()` pour poser une question à l'utilisateur
et valider son entrée :

    [php]
    $answer = $this->askAndValidate('Quel est votre email?', new sfValidatorEmail());

La méthode accepte également un tableau d'options (voir la doc de l'API pour plus
d'informations).

### `symfony:test`

De temps en temps, les développeurs ont besoin d'exécuter une suite de test pour vérifier que
symfony fonctionne bien sur leur plate-forme spécifique. Jusqu'à présent, ils devaient connaître le
script `prove.php` livré avec symfony pour le faire. A partir de symfony 1.3/1.4, c'est
une tâche intégrée, `symfony:test` lance une suite de test du noyau de symfony
depuis la ligne de commande, comme les autres tâches :

    $ php symfony symfony:test

Si vous utilisiez le php `php test/bin/prove.php`, vous devez maintenant exécuter le
php `php data/bin/symfony symfony:test` équivalent à la commande.

### `project:deploy`

La tâche `project:deploy` a été légèrement améliorée. Elle affiche maintenant la
progression du transfert des fichiers en temps réel, mais seulement si l'option `-t` est
passée. Sinon, la tâche est silencieuse, sauf bien sûr pour les erreurs. En parlant
d'erreurs, si l'une se produit, la sortie est sur un fond rouge pour faciliter
l'identification du problème.

### `generate:project`

A partir de symfony 1.3/1.4, Doctrine est l'ORM par défaut configuré lors de l'exécution de
la tâche `generate:project` :

    $ php /path/to/symfony generate:project foo

Pour générer un projet pour Propel, utilisez l'option `--orm` :

    $ php /path/to/symfony generate:project foo --orm=Propel

Si vous ne souhaitez pas utiliser Propel ou Doctrine, vous pouvez passer `none`
à l'option `--orm` :

    $ php /path/to/symfony generate:project foo --orm=none

La nouvelle option `--installer` vous permet de passer un script PHP qui permet également
de personnaliser le projet nouvellement créé. Le script est exécuté dans le contexte
de la tâche, et peut donc utiliser toutes ses méthodes. Les plus utilisées sont les
suivantes : `installDir()`, `runTask()`, `ask()`, `askConfirmation()`,
`askAndValidate()`, `reloadTasks()`, `enablePlugin()`, et `disablePlugin()`.

Plus d'informations peuvent être trouvées dans ce
[sujet](http://www.symfony-project.org/blog/2009/06/10/new-in-symfony-1-3-project-creation-customization)
du blog officiel de symfony.

Vous pouvez également inclure un second argument "author" lors de la génération d'un projet,
qui spécifie une valeur à utiliser pour le doc tag `@author` quand symfony
génère de nouvelles classes.

    $ php /path/to/symfony generate:project foo "Joe Schmo"

### `sfFileSystem::execute()`

Les méthodes `sfFileSystem::execute()` remplacent la méthode `sfFileSystem::sh()`
avec des fonctionnalités plus puissantes. Elles prennent des callbacks pour le traitement en temps réel
des sorties `stdout` et `stderr`. Elles renvoient également les deux sorties sous forme de tableau.
Vous trouverez un exemple de son utilisation dans la classe `sfProjectDeployTask`.

### `task.test.filter_test_files`

Les tâches `test:*` filtrent maintenant les fichiers de test à l'aide de l'événement
`task.test.filter_test_files` avant de les exécuter. Cet événement inclut les
paramètres `arguments` et `options`.

### Amélioration de `sfTask::run()`

Vous pouvez désormais passer un tableau associatif d'arguments et d'options pour
`sfTask::run()` :

    [php]
    $task = new sfDoctrineConfigureDatabaseTask($this->dispatcher, $this->formatter);
    $task->run(
      array('dsn' => 'mysql:dbname=mydb;host=localhost'),
      array('name' => 'master')
    );

La version précédente fonctionne encore :

    [php]
    $task->run(
      array('mysql:dbname=mydb;host=localhost'),
      array('--name=master')
    );

### `sfBaseTask::setConfiguration()`

Lorsque vous appelez une tâche qui étend la tâche `sfBaseTask` en PHP, vous n'avez plus à
passer les options `--application` et `--env` à `->run()`. Au lieu de cela, il vous suffit
de définir la configuration des objets directement en appelant `->setConfiguration()`.

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->setConfiguration($this->configuration);
    $task->run();

La version précédente fonctionne encore :

    [php]
    $task = new sfDoctrineLoadDataTask($this->dispatcher, $this->formatter);
    $task->run(array(), array(
      '--application='.$options['application'],
      '--env='.$options['env'],
    ));

### `project:disable` et `project:enable`

Vous pouvez maintenant désactiver ou activer un environnement complet en utilisant les
tâches `project:disable` et `project:enable` :

    $ php symfony project:disable prod
    $ php symfony project:enable prod

Vous pouvez également spécifier les applications à désactiver dans cet environnement :

    $ php symfony project:disable prod frontend backend
    $ php symfony project:enable prod frontend backend

Ces tâches ont une comptabilité arrière avec leur signature précédente :

    $ php symfony project:disable frontend prod
    $ php symfony project:enable frontend prod

### `help` et `list`

Les tâches `help` et `list` peuvent maintenant afficher leurs informations en XML:

    $ php symfony list --xml
    $ php symfony help test:all --xml

Les sorties sont basées sur la nouvelle méthode `sfTask::asXml()`, qui retourne une
représentation XML de l'objet de la tâche.

La sortie XML est surtout utile pour des outils tiers comme les IDE.

### `project:optimize`

L'exécution de cette tâche réduit le nombre de lectures effectuées sur le disque pendant l'exécution de
la mise en cache de l'emplacement des fichiers templates de votre application. Cette tâche devrait
être seulement utilisée sur un serveur de production. N'oubliez pas de ré-exécuter la tâche à chaque
modification du projet.

    $ php symfony project:optimize frontend

### `generate:app`

La tâche `generate:app` vérifie maintenant un répertoire squelette dans le répertoire
`data/skeleton/app` de votre projet avant de fournir par défaut un squelette dans le
noyau.

### Envoyer un email depuis une tâche

Vous pouvez désormais envoyer facilement un email depuis une tâche en utilisant la méthode
`getMailer()`.

### Utiliser le routage dans une tâche

Vous pouvez désormais récupérer facilement un objet de routage depuis une tâche en utilisant la
méthode `getRouting()`.

Exceptions
----------

### Chargement automatique

Quand une exception est levée pendant le chargement automatique, symfony la capture et
affiche l'erreur à l'utilisateur. Cela devrait résoudre certaines pages du style
«écran blanc de la mort".

### Barre d'outils web de débogage

Si cela est possible, la barre d'outils web de débogage est maintenant également affichée sur les pages
d'exception dans l'environnement de développement.

Intégration de Propel
------------------ 

Propel a été mis à niveau vers la version 1.4. Merci de visiter le site de Propel pour plus
d'informations sur sa mise à jour
(http://propel.phpdb.org/trac/wiki/Users/Documentation/1.4).

### Comportements de Propel

Les classes de constructeur personnalisé de symfony réliées à l'extension Propel ont été
portées vers le nouveau système de' comportement de Propel 1.4.

### `propel:insert-sql`

Avant de supprimer toutes les données d'une base de données, `propel:insert-sql` demande une
confirmation. Comme cette tâche ne peut supprimer des données de plusieurs bases de données, il affiche désormais
aussi le nom des connexions des bases de données liées.
 
### `propel:generate-module`, `propel:generate-admin`, `propel:generate-admin-for-route` 

Les tâches `propel:generate-module`, `propel:generate-admin`, et
`propel:generate-admin-for-route` prennent désormais l'option `--actions-base-class` qui permet
la configuration de la classe des actions de la base pour les modules générés.

### Propel Behaviors

Propel 1.4 introduit une implémentation des comportements dans le code de base de Propel.
Les constructeurs personnalisés de symfony ont été portés dans ce nouveau système.

Si vous souhaitez ajouter des comportements natifs à vos modèles Propel, vous pouvez le faire
dans le `schema.yml` :

    classes:
      Article:
        propel_behaviors:
          timestampable: ~

Ou, si vous utilisez l'ancienne syntaxe de `schema.yml` :

    propel:
      article:
        _propel_behaviors:
          timestampable: ~

### Désactiver la génération du formulaire

Vous pouvez maintenant désactiver la génération de formulaire sur certains modèles en passant des paramètres au
comportement Propel de symfony :

    classes:
      UserGroup:
        propel_behaviors:
          symfony:
            form: false
            filter: false

Notez que vous devez reconstruire le modèle avant que ce paramètre soit effectif,
car le comportement est attaché au modèle et celui-ci existe seulement après
la reconstruction.

### Utilisation d'une version différente de Propel

L'utilisation d'une version différente de Propel est facile à paramètrer avec les variables de configuration
`sf_propel_runtime_path` et `sf_propel_generator_path` dans
`ProjectConfiguration`:

    [php]
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfPropelPlugin');

      sfConfig::set('sf_propel_runtime_path', '/path/to/propel/runtime');
      sfConfig::set('sf_propel_generator_path', '/path/to/propel/generator');
    }

Routage
-------

### Les conditions par défaut

La condition par défaut `\d+` s'applique maintenant uniquement à
`sfObjectRouteCollection` lorsque l'option `column` a la valeur par défaut `id`. Cela
signifie que vous n'avez plus à fournir une condition de rechange quand une
colonne non-numérique est spécifiée (par exemple `slug`).

### Les options de `sfObjectRouteCollection`

Une nouvelle option `default_params` a été ajoutée à `sfObjectRouteCollection`. Elle
permet pour les paramètres par défaut d'être enregistrés pour chaque routage généré :

    [yml] 
    forum_topic: 
      class: sfDoctrineRouteCollection 
      options: 
        default_params: 
          section: forum

CLI
---

### Colorisation de la sortie

Symfony essaie de deviner si votre console supporte les couleurs lorsque vous utilisez
l'outil CLI de symfony. Mais parfois, symfony devine à tort, par exemple lorsque vous
utilisez Cygwin (parce que la colorisation est toujours éteint sur la plateforme
Windows).

A partir de symfony 1.3/1.4, vous pouvez forcer l'utilisation des couleurs pour la sortie en
passant l'option globale `--color`.

I18N
----

### Mise à jour des données

Les données utilisées pour toutes les opérations I18N ont été mises à jour pour le `ICU project`.
Symfony posséde maintenant environ 330 fichiers `locale`, ceci représente une augmentation d'environ
70 par rapport à symfony 1.2. Merci de noter que les données mises à jour peuvent être légèrement
différentes de celle d'avant, donc par exemple dans le cas d'un test pour vérifier
le dixième élément dans la liste d'une langue, celui-ci pourrait échouer.

### Tri selon la localisation des utilisateurs

Tous les tris dépendants de la localisation sont désormais également effectué par rapport à la localisation.
`sfCultureInfo->sortArray()` peut être utilisé pour cela.

Plugins
-------

Avant symfony 1.3/1.4, tous les plugins étaient activés par défaut, sauf pour
`sfDoctrinePlugin` et `sfCompat10Plugin` :

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // Pour la compatibilité / supprime et active uniquement les plugins que vous voulez
        $this->enableAllPluginsExcept(array('sfDoctrinePlugin', 'sfCompat10Plugin'));
      }
    }

Pour les projets fraîchement créés avec symfony 1.3/1.4, les plugins doivent être explicitement
activés dans la classe `ProjectConfiguration` pour pouvoir les utiliser :

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins('sfDoctrinePlugin');
      }
    }

La tâche `plugin:install` active automatiquement le(s) plugin(s) à installer (et
`plugin:uninstall` les désactive). Si vous installer un plugin via Subversion, vous
avez encore besoin de l'activer à la main.

Si vous voulez utiliser un core-plugin, comme `sfProtoculousPlugin` ou
`sfCompat10Plugin`, vous devez juste ajouter la déclaration correspondante `enablePlugins()`
dans la classe `ProjectConfiguration`.

>**NOTE**
>Si vous mettez à niveau un projet à partir de la 1.2, l'ancien comportement sera encore
>actif car la tâche de mise à niveau ne modifie pas le fichier `ProjectConfiguration`.
>Le changement de comportement est seulement pour les nouveaux projets symfony 1.3/1.4.

### `sfPluginConfiguration::connectTests()`

Vous pouvez connecter les tests d'un plugin à des tâches `test:*` en appelant la méthode
de configuration du plugin `->connectTests()` dans la nouvelle méthode `setupPlugins()` :

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setupPlugins()
      {
        $this->pluginConfigurations['sfExamplePlugin']->connectTests();
      }
    }

Paramètres
--------

### `sf_file_link_format`

Symfony 1.3/1.4 formate les chemins des fichiers sous forme de liens cliquables chaque fois que c'est possible (par exemple
le template du débogueur d'exception). Le `sf_file_link_format` est utilisé à cette fin,
si elle est définie, sinon symfony va chercher la valeur de configuration de PHP de
`xdebug.file_link_format`.

Par exemple, si vous voulez ouvrir des fichiers dans TextMate, ajoutez ceci à
`settings.yml` :

    [yml]
    all:
      .settings:
        file_link_format: txmt://open?url=file://%f&line=%l

L'espace réservé `%f` sera remplacé par le chemin absolu du fichier et l'espace
réservé `%l` sera remplacé par le numéro de ligne.

Intégration de Doctrine
--------------------

Doctrine a été mis à jour avec la version 1.2. Visitez, s'il vous plaît, le site de Doctrine pour plus
d'informations sur leur mise à jour
(http://www.doctrine-project.org/documentation/1_2/en).

### Génération des classes de formulaire

Il est maintenant possible de spécifier des options supplémentaires pour symfony dans vos fichiers
de schéma Doctrine en YAML. Nous avons ajouté quelques options pour désactiver la génération des classes
de formulaire et de filtre.

Par exemple, dans un modèle de référence type many-to-many, vous n'avez pas besoin générer des formulaires
ou des classes de formulaire de filtre. Ainsi, vous pouvez désormais effectuer les opérations suivantes :

    UserGroup:
      options:
        symfony:
          form: false
          filter: false
      columns:
        user_id:
          type: integer
          primary: true
        group_id:
          type: integer
          primary: true

### Héritage des classes de formulaire

Lorsque vous générez des formulaires à partir de vos modèles, vos modèles contiennent l'héritage.
Les classes filles générées respecteront l'héritage et généreront des formulaires
qui suivent la même structure d'héritage.

### Nouvelles tâches

Nous avons introduit quelques nouvelles tâches pour vous aider lors du développement avec Doctrine.

#### Création d'une table du modèle

Vous pouvez maintenant créer individuellement les tables pour un tableau spécifique de modèles. Il
supprimera les tables en premier, puis les recréera pour vous. Ceci est utile si vous
développez de nouveaux modèles dans un projet/base de données existant et que vous ne voulez pas
démolir la base de données entière et mais juste reconstruire un sous-ensemble de tables.

    $ php symfony doctrine:create-model-tables Model1 Model2 Model3

#### Suppression des fichiers du modèle

Souvent, vous allez changer vos modèles dans vos fichiers de schéma YAML, en renommant les choses,
en supprimant des modèles inutilisés, etc. Quand vous faites cela, vous avez alors des classes
de modèle, des formulaire et des filtres orphelins. Vous pouvez maintenant nettoyer manuellement les fichiers générés liés
à un modèle en utilisant la tâche `doctrine:delete-model-files`.

    $ php symfony doctrine:delete-model-files ModelName

La tâche ci-dessus va trouver tous les fichiers générés relatifs et vous les affichera
avant de vous demander de confirmer si vous souhaitez supprimer les fichiers ou non.

#### Nettoyage des fichiers du modèle

Vous pouvez automatiser le processus ci-dessus et découvrir quels modèles existent sur le disque
mais ne figurent pas dans vos fichiers schéma YAML grâce à la tâche
`doctrine:clean-model-files`.

    $ php symfony doctrine:clean-model-files

La commande ci-dessus permettra de comparer vos fichiers schéma YAML avec les modèles et les fichiers
qui ont été générés et déterminera ce qui doit être enlevé. Ces modèles seront
ensuite transmis à la tâche `doctrine:delete-model-files`. Il vous sera demandé une
confirmation pour la suppression de tous les fichiers avant de supprimer quoi que ce soit.

#### Rechargement des données

Il s'agit d'un besoin commun de vouloir vider complètement les bases de données puis recharger
vos données de tests. La tâche `doctrine:build-all-reload` fait cela mais il faut aussi
faire un tas d'autres travaux, la génération des modèles, des formulaires, des filtres, etc, et cela
peut nécessiter beaucoup de temps dans un grand projet. Maintenant vous pouvez simplement utiliser
la tâche `doctrine:reload-data`.

La commande suivante :

    $ php symfony doctrine:reload-data

Est équivalent à l'exécution de ces commandes :

    $ php symfony doctrine:drop-db
    $ php symfony doctrine:build-db
    $ php symfony doctrine:insert-sql
    $ php symfony doctrine:data-load

#### Construire ce que vous voulez

La nouvelle tâche `doctrine:build` vous permet de spécifier exactement ce que vous voulez
construire avec symfony et Doctrine. Cette tâche reproduit les fonctionnalités
d'un grand nombre de combinaison de tâche existante, qui ont tous été déconseillées en
faveur de cette solution plus souple.

Voici quelques utilisations possibles de `doctrine:build` :

    $ php symfony doctrine:build --db --and-load

Cela va supprimer (`:drop-db`) et créer (`:build-db`) la base de donnée, créer
les tables configurées dans `schema.yml` (`:insert-sql`) et charger les données de tests
(`:data-load`).

    $ php symfony doctrine:build --all-classes --and-migrate

Cela va construire le modèle (`:build-model`), les formulaires (`:build-forms`), les formulaires
de filtres (`:build-filters`) et exécuter n'importe quelle migration en attente (`:migrate`).

    $ php symfony doctrine:build --model --and-migrate --and-append=data/fixtures/categories.yml

Cela va contruire le modèle (`:build-model`), migrer la base de donnée (`:migrate`)
et ajouter les données de test `categories` (`:data-load --append --dir=data/fixtures/categories.yml`).

Pour plus d'informations, voir la page d'aide de la tâche `doctrine:build`.

#### Nouvelle option : `--migrate`

Les tâches suivantes incluent désormais l'option `--migrate`, qui remplacera la
tâche `doctrine:insert-sql` avec `doctrine:migrate`.

  * `doctrine:build-all`
  * `doctrine:build-all-load`
  * `doctrine:build-all-reload`
  * `doctrine:build-all-reload-test-all`
  * `doctrine:rebuild-db`
  * `doctrine:reload-data`

#### `doctrine:generate-migration --editor-cmd`

La tâche `doctrine:generate-migration` inclut désormais l'option `--editor-cmd`
qui s'exécutera une fois que la classe de migration sera créée pour une édition facile.

    $ php symfony doctrine:generate-migration AddUserEmailColumn --editor-cmd=mate

Cet exemple va générer la classe de la nouvelle migration et ouvrir le nouveau fichier dans
TextMate.

#### `doctrine:generate-migrations-diff`

Cette nouvelle tâche va automatiquement générer pour vous les classes d'une migration complète,
en fonction de vos anciens et nouveaux schémas.

#### Créer ou supprimer des connexions spécifiques

Vous pouvez maintenant spécifiez des connections spécifiques de base de données en lançant `doctrine:build-db`
et `doctrine:drop-db` :

    $ php symfony doctrine:drop-db master slave1 slave2

### Setters et Getters pour les dates

Nous avons ajouté deux nouvelles méthodes pour récupérer la valeurs de `date` ou de `timestamp` de Doctrine
comme des instances d'objet PHP DateTime.

    [php]
    echo $article->getDateTimeObject('created_at')
      ->format('m/d/Y');

Vous pouvez également définir une valeur d'une date en appelant simplement la méthode `setDateTimeObject`
et en passant une instance `DateTime` valide.

    [php]
    $article->setDateTimeObject('created_at', new DateTime('09/01/1985'));

### `doctrine:migrate --down` 

Le `doctrine:migrate` inclut maintenant les options `up` et `down` qui vont migrer
votre schéma d'une étape dans la direction demandée.

    $ php symfony doctrine:migrate --down 

### `doctrine:migrate --dry-run` 

Si votre base de données supporte les instructions rollback DDL (MySQL ne le fait pas), vous
pouvez profiter de la nouvelle option `dry-run`.

    $ php symfony doctrine:migrate --dry-run

### L'affichage de la tâche DQL comme un tableau de donnée

Lorsque vous exécutiez auparavant la commande `doctrine:dql`, elle pouvait juste afficher les
données en YAML. Nous avons ajouté la nouvelle option `--table`. Cette option permet de vous
afficher les données dans un tableau, semblable à l'affichage dans la ligne de commande MySQL.

Maintenant l'exemple suivant est possible.

    $ ./symfony doctrine:dql "FROM Article a" --table
    >> doctrine  executing dql query
    DQL: FROM Article a
    +----+-----------+----------------+---------------------+---------------------+
    | id | author_id | is_on_homepage | created_at          | updated_at          |
    +----+-----------+----------------+---------------------+---------------------+
    | 1  | 1         |                | 2009-07-07 18:02:24 | 2009-07-07 18:02:24 |
    | 2  | 2         |                | 2009-07-07 18:02:24 | 2009-07-07 18:02:24 |
    +----+-----------+----------------+---------------------+---------------------+
    (2 results)

### Passer des paramètres de query à `doctrine:dql`

La tâche `doctrine:dql` a aussi été améliorée pour accepter des paramètres de query comme 
arguments :

    $ php symfony doctrine:dql "FROM Article a WHERE name LIKE ?" John% 

### Débogage des requêtes dans les tests fonctionnels

La classe `sfTesterDoctrine` inclut désormais la méthode `->debug()`. Cette méthode
affichera l'information sur les requêtes qui ont été exécutées dans le contexte
actuel.

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug()
    ;

Vous pouvez consulter que les dernières requêtes exécutées, en passant un nombre entier à la
méthode, ou d'afficher que les requêtes qui contiennent une sous-chaîne, ou correspondant à une expression
régulière en passant une chaîne.

    [php]
    $browser->
      get('/articles')->
      with('doctrine')->debug('/from articles/i')
    ;

### `sfFormFilterDoctrine`

La classe `sfFormFilterDoctrine` peut maintenat être en tête de série d'un objet `Doctrine_Query`
via l'option `query` :

    [php]
    $filter = new ArticleFormFilter(array(), array(
      'query' => $table->createQuery()->select('title, body'),
    ));

La méthode de la table spécifiée via `->setTableMethod()` (ou maintenant par le biais
de l'option `table_method`) n'est plus nécessaire pour retourner l'objet d'une query.
Les exemples suivants sont valides pour les méthodes de table de `sfFormFilterDoctrine` :

    [php]
    // fonctionne dans symfony >= 1.2
    public function getQuery()
    {
      return $this->createQuery()->select('title, body');
    }

    // fonctionne dans symfony >= 1.2
    public function filterQuery(Doctrine_Query $query)
    {
      return $query->select('title, body');
    }

    // fonctionne dans symfony >= 1.3
    public function modifyQuery(Doctrine_Query $query)
    {
      $query->select('title, body');
    }

La personnalisation d'un filtre de formulaire est désormais plus facile. Pour ajouter un champ de filtrage, tout ce que vous
avez à faire est d'ajouter le widget et une méthode pour la traiter.

    [php]
    class UserFormFilter extends BaseUserFormFilter
    {
      public function configure()
      {
        $this->widgetSchema['name'] = new sfWidgetFormInputText();
        $this->validatorSchema['name'] = new sfValidatorString(array('required' => false));
      }

      public function addNameColumnQuery($query, $field, $value)
      {
        if (!empty($value))
        {
          $query->andWhere(sprintf('CONCAT(%s.f_name, %1$s.l_name) LIKE ?', $query->getRootAlias()), $value);
        }
      }
    }

Dans les versions antérieures, vous auriez besoin d'étendre `getFields()` et en plus de
créer un widget et une méthode pour que cela fonctionne.

### Configuration de Doctrine

Vous pouvez maintenant écouter les événements `doctrine.configure` et
`doctrine.configure_connection` pour configurer Doctrine. Cela signifie que la configuration
de Doctrine peut être facilement personnalisés à partir d'un plugin, aussi longtemps que le plugin est
activé pour `sfDoctrinePlugin`.

### `doctrine:generate-module`, `doctrine:generate-admin`, `doctrine:generate-admin-for-route` 

Les tâches `doctrine:generate-module`, `doctrine:generate-admin`, et 
`doctrine:generate-admin-for-route` prend désormais une option `--actions-base-class` qui permet
la configuration de la classe de base des actions pour les modules générés.

### La méthode magique des doc tags

Les méthodes magiques getter et setter de symfony ajoutés à votre modèle de Doctrine sont
maintenant représenté dans un entête de doc de chaque classe de base générée. Si votre IDE
supporte la complétion de code, vous devriez maintenant voir ces méthodes `getFooBar()` et
`setFooBar()` apparaitre en haut des objets du modèle, où FooBar est un nom de champ
noté en CamelCase.
 
### Utilisation d'une version différente de Doctrine 

L'utilisation d'une version différente de Doctrine est facile à paramètrer avec
`sf_doctrine_dir` dans `ProjectConfiguration` :

    [php] 
    // config/ProjectConfiguration.class.php
    public function setup()
    {
      $this->enablePlugins('sfDoctrinePlugin');

      sfConfig::set('sf_doctrine_dir', '/path/to/doctrine/lib');
    }



La barre d'outil web de débogage
-----------------

### `sfWebDebugPanel::setStatus()`

Chaque panneau dans la barre d'outils web de débogage peut spécifier un statut qui affectera
la couleur de fond de son titre. Par exemple, la couleur de fond du titre du panneau log
change si aucun message avec une priorité plus grande que `sfLogger::INFO`
sont enregistrés.

### `sfWebDebugPanel` request parameter

Vous pouvez maintenant spécifier qu'un panneau peut être ouverte sur le chargement d'une page en ajoutant
un paramètre `sfWebDebugPanel` à l'URL. Par exemple, en ajoutant
`?sfWebDebugPanel=config`, ceci permet de rendre la barre d'outils de débogage avec
le panneau de configuration ouvert.

Les panneaux peuvent aussi inspecter les paramètres de la requête en accédant à l'option web
de débogage `request_parameters` :

    [php]
    $requestParameters = $this->webDebug->getOption('request_parameters');

Partials
--------

### Améliorations des slots

Les helpers `get_slot()` et `include_slot()` acceptent maintenant un deuxième paramètre pour
spécifier le contenu par défaut du slot à retourner si aucun n'est fournit par le slot :

    [php]
    <?php echo get_slot('foo', 'bar') // affichera 'bar' si le slot 'foo' n'est pas défini ?>
    <?php include_slot('foo', 'bar') // affichera 'bar' si le slot 'foo' n'est pas défini ?>

Pagers
------

Les méthodes `sfDoctrinePager` et `sfPropelPager` implémentent maintenant les interfaces `Iterator`
et `Countable`.

    [php]
    <?php if (count($pager)): ?>
      <ul>
        <?php foreach ($pager as $article): ?>
          <li><?php echo link_to($article->getTitle(), 'article_show', $article) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No results.</p>
    <?php endif; ?>

Cache de la vue
----------

Le manager de cache de la vue acceptent maintenant des paramètres dans factories.yml.
La génération de la clé du cache pour une vue a été remaniée dans différentes méthodes pour
faciliter l'extension de la classe.

Deux paramètres sont disponibles dans `factories.yml` :

  * `cache_key_use_vary_headers` (par défaut : `true`): précise si les clés du cache
    doivent être inclus dans la partie des entêtes qui varient. En pratique, cela veut dire que le
    cache de la page doit être dépendant de l'entête HTTP, comme spécifié dans le
    paramètre du cache `vary`.

  * `cache_key_use_host_name` (par défaut : `true`): précise si les clés du cache
    doivent être inclus dans la partie du nom de l'hôte. En pratique, cela veut dire que le
    cache doit être dépendant du nom de l'hôte.

### Plus de mise en cache
 
Le manager de cache de la vue ne refuse plus de mettre en cache s'il y a des
valeurs dans les tableaux de `$_GET` ou `$_POST`. La logique maintenant confirme simplement la
requête courante de la méthode GET avant de vérifier le `cache.yml`. Cela signifie que
les pages suivantes peuvent à présent être mis en cache :

  * `/js/my_compiled_javascript.js?cachebuster123`
  * `/users?page=3`

Requête
-------

### `getContent()`

Le contenu de la requête est désormais accessible via la méthode `getContent()`.

### Paramètres de `PUT` et `DELETE`

Lorsqu'une requête arrive soit avec `PUT` ou soit avec une méthode `DELETE` HTTP dont
le type de contenu est `application/x-www-form-urlencoded`, symfony analyse maintenant
le raw du body et rend accessible les paramètres comme des paramètres normaux de `POST`.

Actions
-------

### `redirect()`

La famille de méthode `sfAction::redirect()` est maintenant compatible avec la signature `url_for()`
introduit dans symfony 1.2 :

    [php]
    // symfony 1.2
    $this->redirect(array('sf_route' => 'article_show', 'sf_subject' => $article));

    // symfony 1.3/1.4
    $this->redirect('article_show', $article);

Cette amélioration a également été appliquée à `redirectIf()` et `redirectUnless()`.

Helpers
-------

### `link_to_if()`, `link_to_unless()`

Les helpers `link_to_if()` et `link_to_unless()` sont maintenant compatibles avec la signature
`link_to()` introduit dans symfony 1.2 :

    [php]
    // symfony 1.2
    <?php echo link_to_unless($foo, '@article_show?id='.$article->getId()) ?>

    // symfony 1.3/1.4
    <?php echo link_to_unless($foo, 'article_show', $article) ?>

Contexte
-------

Vous pouvez maintenant écouter `context.method_not_found` pour ajouter dynamiquement des méthodes à
`sfContext`. Ceci est utile si vous avez ajouté un traitement de chargement paresseux, peut-être
d'un plugin.

    [php]
    class myContextListener
    {
      protected
        $factory = null;

      public function listenForMethodNotFound(sfEvent $event)
      {
        $context = $event->getSubject();

        if ('getLazyLoadingFactory' == $event['method'])
        {
          if (null === $this->factory)
          {
            $this->factory = new myLazyLoadingFactory($context->getEventDispatcher());
          }

          $event->setReturnValue($this->factory);

          return true;
        }
      }
    }
