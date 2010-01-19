Tirer Profit de l'Héritage de Table avec Doctrine
=================================================

*Par Hugo Hamon*

~Doctrine~ est officiellement devenu la bibliothèque d'ORM par défaut à partir de symfony 1.3 alors que le développement de Propel avait ralenti depuis quelques mois. Le projet ~Propel~ reste toutefois supporté dans symfony et continue de s'améliorer grâce notamment aux efforts des membres de la communauté symfony.

Le projet Doctrine 1.2 est devenu le nouvel ORM par défaut de symfony pour deux raisons principales. En effet, Doctrine est beaucoup plus simple à utiliser que Propel et parce qu'il fournit de nombreuses fonctionnalités intéressantes telles que les comportements (behaviors), les requêtes DQL simplifiées, les migrations ou bien l'héritage de table.

Ce chapitre explique ce qu'est l'héritage de table et comment cette fonctionnalité est désormais parfaitement intégrée dans symfony 1.3. C'est à l'aide d'exemples concrets réels que ce chapitre illustrera comment tirer parti de l'héritage de table Doctrine afin de rendre le code plus flexible et mieux organisé.

L'Héritage de Table Doctrine
----------------------------

Bien qu'il ne soit pas encore très connu des développeurs et peu utilisé, l'héritage de table est probablement l'une des fonctionnalités les plus intéressantes de Doctrine. L'héritage de table permet au développeur de créer des tables, dans une base de données, qui héritent d'autres plus génériques de la même manière que des classes en étendent d'autres dans un langage de programmation orienté objet. En effet, cette fonctionnalité offre une manière simple et efficace pour partager des données entre deux ou plusieurs tables dans une même table maîtresse plus générique. Le diagramme ci-dessous explique ce principe de l'héritage de table.

![Schéma d'explication de l'héritage de table Doctrine](http://www.symfony-project.org/images/more-with-symfony/01_table_inheritance.png "Schéma de principe de l'héritage de table Doctrine")

Doctrine intègre trois stratégies différentes pour gérer les héritages de table selon les besoins de l'applications en terme de performance, d'atomicité, d'efficacité ou bien encore de simplicité, etc. Ces trois stratégies natives sont l'héritage simple, l'héritage par agrégation de colonnes et 
l'héritage concret. Bien que que toutes ces stratégies soient présentées dans le [livre Doctrine](http://www.doctrine-project.org/documentation/1_2/en), des informations complémentaires aideront à mieux comprendre chacune de leurs options et dans quelles circonstances elles sont particulièrement utiles.

### La Stratégie de l'Héritage Simple

La stratégie de l'héritage simple est, comme son nom l'indique, la plus simple de toutes dans la mesure où elle stocke toutes les colonnes, y compris celles des tables filles, dans la table maîtresse. Par exemple, si le schéma descriptif du modèle de données ressemble au code YAML ci-après, alors Doctrine génèrera une seule table `Person`, dans laquelle seront fusionnées les colonnes des tables `Professor` et `Student`.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             simple
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true


Avec l'héritage de table simple, toutes les colonnes supplémentaires (`specialty`, `graduation` et `promotion`) sont automatiquement remontées au niveau supérieur dans le modèle `Person`, bien que Doctrine génère une classe de de modèle pour chacune des tables `Student` et `Person`.

![Schéma d'explication de l'héritage de table simple](http://www.symfony-project.org/images/more-with-symfony/02_simple_tables_inheritance.png "Schéma d'explication de l'héritage de table simple")

Cette stratégie a un inconvénient majeur dans la mesure où la table maîtresse `Person` ne fournit aucune colonne pour identifier le type de chaque enregistrement. En d'autres termes, il n'y a absolument aucun moyen de retrouver des objets de type `Professor` ou `Student` distincts. Par conséquent, l'exécution du code Doctrine ci-dessous retourne un objet `Doctrine_Collection` qui contient tous les enregistrements de la table (`Student` et `Professor` ensemble).

    [php]
    $professors = Doctrine_Core::getTable('Professor')->findAll();

Il en résulte que la stratégie par héritage de table simple ne s'avère pas pratique pour des cas concrets d'application. En effet, la plupart des applications requièrent de sélectionner et d'hydrater des objets de type spécifiques. Par conséquent, cette stratégie est d'ores et déjà abandonnée pour la suite de ce chapitre.

### La Stratégie de l'Héritage de Table par Agrégation de Colonnes

La stratégie de l'héritage par agrégation de colonnes est similaire à la stratégie d'héritage simple à la différence qu'elle inclut automatiquement une colonne `type` pour identifier les différents types d'enregistrements. Par conséquent, lorsqu'un un enregistrement est persisté en base de données, une valeur est affectée à cette colonne afin de déterminer à quelle classe il appartient.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         1
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             column_aggregation
        extends:          Person
        keyField:         type
        keyValue:         2
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

Dans le schéma de données ci-dessus, la stratégie d'héritage a été changée en 
~`column_aggregation`~ et deux nouveaux attributs ont aussi fait leur apparition. Le premier, `keyField`, indique le nom de la colonne qui doit être créée pour stocker l'information concernant le type de l'enregistrement. L'attribut `keyField` est un entier obligatoire nommé `type` et est aussi le nom de la colonne par défaut s'il n'est pas explicitement spécifié dans le schéma de données. Le second attribut définit quant à lui la valeur à affecter pour chaque enregistrement qui appartient aux classes `Professor` et `Student`.

![Schéma d'explication de la stratégie par agrégation de colonnes](http://www.symfony-project.org/images/more-with-symfony/03_columns_aggregation_tables_inheritance.png "Schéma d'explication de la stratégie par agrégation de colonnes")

La stratégie de l'agrégation de colonnes est une bonne méthode d'héritage de table dans la mesure où elle ne crée finalement qu'une seule table (`Person`) contenant tous les champs fusionnés, auxquels s'ajoute le champ `type`. De cette manière, il n'y a pas besoin de créer plusieurs tables et de les joindre par des requêtes SQL lorsqu'il s'agit de récupérer des données.

Le listing ci-dessous montre quelques exemples d'interrogation des tables, ainsi que les types de résultats retournés.

    [php]
    // Returns a Doctrine_Collection of Professor objects
    $professors = Doctrine_Core::getTable('Professor')->findAll();

    // Returns a Doctrine_Collection of Student objects
    $students = Doctrine_Core::getTable('Student')->findAll();

    // Returns a Professor object
    $professor = Doctrine_Core::getTable('Professor')->findOneBySpeciality('physics');

    // Returns a Student object
    $student = Doctrine_Core::getTable('Student')->find(42);

    // Returns a Student object
    $student = Doctrine_Core::getTable('Person')->findOneByIdAndType(array(42, 2));

Lorsqu'il s'agit de récupérer des données depuis une classe fille (`Professor` ou `Student`), Doctrine se charge d'ajouter automatiquement la clause `WHERE` à la requête avec la bonne valeur de filtre pour la colonne `type`.

Toutefois, il existe quelques inconvénients quant à l'usage de la stratégie par agrégation de colonnes dans certains cas. Tout d'abord, l'agrégation de colonnes empêche tous les champs de chaque table fille d'être définis comme obligatoires. Par conséquent, selon le nombre de champs définis, la table `Person` pourra contenir des enregistrements pour lesquels plusieurs champs resteront vides.

Le second inconvénient réside quant à lui dans le nombre de tables et de champs dérivés. Si le schéma de données déclare un nombre important de tables filles, qui elles mêmes définissent beaucoup de champs, alors il en résultera que la table maîtresse sera un large jeu de colonnes. Par conséquent, cette dernière risque de devenir moins performante et plus difficile à maintenir.

### La Stratégie de l'Héritage de Table Concret

La stratégie de l'héritage de table concret est un bon compromis entre les avantages de la stratégie par agrégation de colonnes, les performances et la maintenabilité. En effet, cette stratégie crée des tables indépendantes pour chaque table fille, et réplique toutes les colonnes (colonnes partagées et colonnes spécifiques) à l'intérieur de celles-ci.

    [yml]
    Person:
      columns:
        first_name:
          type:           string(50)
          notnull:        true
        last_name:
          type:           string(50)
          notnull:        true

    Professor:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        specialty:
          type:           string(50)
          notnull:        true

    Student:
      inheritance:
        type:             concrete
        extends:          Person
      columns:
        graduation:
          type:           string(20)
          notnull:        true
        promotion:
          type:           integer(4)
          notnull:        true

Ainsi, pour le schéma de données précédent, la table `Professor` générée contiendra l'ensemble des champs suivants : `id`, `first_name`, `last_name` et `specialty`.

![Schéma d'explication de la stratégie par héritage de table concret](http://www.symfony-project.org/images/more-with-symfony/04_concrete_tables_inheritance.png "Schéma d'explication de la stratégie par héritage de table concret")

Cette approche a de nombreux avantages par rapport aux stratégies précédentes. La première, c'est que toutes les tables sont désormais isolées et demeurent indépendantes les unes par rapport aux autres. De plus, cela a pour effet immédiat d'éliminer tous les champs vides ainsi que la colonne additionnelle `type`. De ce fait, chaque table est désormais plus légère et isolée des autres.

>**NOTE**
>Le fait que les champs partagés soient dupliqués dans les tables dérivées est 
>un gain en termes de performance et de scalabilité. En effet, Doctrine n'a plus 
>besoin de créer des jointures automatiques vers la table maîtresse lorsqu'il 
>s'agit de récupérer des informations partagées par les enregistrements des 
>tables filles.

Les deux seuls inconvénients notables de la stratégie d'héritage concret sont la duplication des champs (bien que la réplication est généralement la clé vers les performances) et le fait que la table maîtresse générée demeurera toujours vide. En effet, Doctrine a généré une table `Person` alors qu'elle ne sera jamais remplie ni référencée par aucune requête SQL. Aucune requête ne sera exécutée sur cette table dans la mesure où toute l'information est sauvegardée dans les tables dérivées.

Cette première partie du chapitre a permis de prendre le temps nécessaire pour introduire les trois types de stratégies d'héritage de table avec Doctrine. Cependant, il n'a pas encore été question de les mettre en pratique avec symfony dans des cas concrets issus de problématiques réelles. La section suivante du chapitre explique et présente comment profiter de la puissance de l'héritage de table Doctrine dans symfony 1.3, particulièrement au niveau du modèle et du framework de formulaires.

Intégration de l'Héritage de Table avec Symfony
-----------------------------------------------

Avant symfony 1.3, l'héritage de table Doctrine n'était pas complètement supporté par le framework dans la mesure où les classes de formulaires et de filtres n'héritaient pas de leur classe mère respective. Par conséquent, les développeurs qui avaient besoin d'utiliser l'héritage de table dans leurs projets, étaient contraints de réajuster les formulaires et les filtres. Ils étaient également forcés de redéfinir de nombreuses méthodes pour parvenir à reproduire le comportement de l'héritage, au détriment d'un temps perdu non négligeable...

Heureusement, grâce aux retours d'utilisation de la communauté, l'équipe de développement de symfony a pu améliorer les classes de formulaires et de filtres dans le but de supporter facilement et entièrement l'héritage de table dans symfony 1.3.

Le reste de ce chapitre explique comment utiliser l'héritage de table de Doctrine et comment en profiter dans plusieurs situations à travers le modèle, les formulaires, les filtres et le générateur d'administration. Pour ce faire, 
des exemples issus de problématiques réelles aideront à mieux comprendre comment l'héritage fonctionne avec symfony, afin de pouvoir facilement l'utiliser pour vos propres besoins.

### Introduction aux Etudes de Cas Concrètes

Tout au long de ce chapitre, plusieurs cas concrets seront étudiés afin de dévoiler les principaux avantages de l'héritage de table Doctrine à plusieurs niveaux : modèle, formulaires, filtres et générateur d'administration.

Le premier exemple est tiré d'une application réelle développée chez Sensio pour un client grand compte français. Il explique en quoi l'héritage de table Doctrine est une excellente solution pour gérer une douzaine de jeux de référentiels de données identiques qui partagent des propriétés et méthodes similaires. L'objectif consistera ici à éviter la duplication du code partagé.

Le deuxième exemple explique comment tirer profit de la stratégie de l'héritage de table concret au niveau des formulaires en créant un modèle de données simple destiné à la gestion de fichiers numériques.

Enfin, le troisième et dernier exemple montrera comment tirer parti de l'héritage de table avec l'Admin Generator et comment le rendre plus flexible.

### Héritage de Table au Niveau du Modèle

Comme avec la programmation orientée objet, l'héritage de table favorise le partage de données. En effet, l'héritage de table permet de partager des propriétés et des méthodes lorsqu'il s'agit de travailler avec des modèles générés. L'héritage de table de Doctrine est un bon moyen de partager et de redéfinir des actions propres aux objets hérités. La section suivante explique ce concept à l'aide d'un exemple pratique concret.

#### La Problématique ####

De nombreuses applications web fonctionnent à l'aide de jeux de données particuliers, appelés "référentiels". Un référentiel est généralement un jeu de données représenté par une simple table contenant au moins deux champs : `id` et `label`. Cependant, dans certains cas, le référentiel contient davantage d'informations comme par exemple des drapeaux `is_active` ou `is_default`. Ce fut exactement le cas chez Sensio pour un projet client.

Le client souhaitait pouvoir gérer de multiples jeux de données servant à alimenter les formulaires et les vues majeures de l'application. Pour ce projet, toutes les tables de référentiels ont été construites selon le même modèle de base et incluent toutes par extension les colonnes suivantes : `id`, `label`, `position` et `is_default`.

Le champ `position` sert ici à ordonner les enregistrements les uns par rapport aux autres grâce à un comportement Ajax de glisser / déposer (drag and drop). Le champ `is_default` quant à lui représente un marqueur qui indique si oui ou non l'enregistrement doit être sélectionné par défaut lorsqu'il alimente une liste déroulante HTML.

#### La Solution ####

Gérer de manière équivalente plus de deux tables similaires est l'une des meilleures problématiques solutionnables grâce à l'héritage de table. Pour résoudre la problématique expliquée précédemment, c'est la stratégie de l'héritage de table concret qui a été retenue afin de satisfaire les besoins fonctionnels de l'application, et partager les méthodes communes des objets dans la même classe de base. Le code YAML ci-dessous décrit un modèle de données simplifié pour illustrer la problématique.

    [yml]
    sfReferential:
      columns:
        id:
          type:        integer(2)
          notnull:     true
        label:
          type:        string(45)
          notnull:     true
        position:
          type:        integer(2)
          notnull:     true
        is_default:
          type:        boolean
          notnull:     true
          default:     false

    sfReferentialContractType:
      inheritance:
        type:          concrete
        extends:       sfReferential

    sfReferentialProductType:
      inheritance:
        type:          concrete
        extends:       sfReferential

L'héritage de table concret fonctionne parfaitement dans ce cas dans la mesure où il génère des tables séparées et isolées les unes des autres. C'est d'autant plus important ici du fait du l'existence du champ `position` qui doit impérativement être géré pour des enregistrements de même type.

Dès lors que le modèle de données est établi, l'étape suivante consiste à générer les classes de modèle correspondantes avant de les étudier. Pour ce schéma, Doctrine et symfony génèrent trois tables SQL distinctes et six classes de modèle dans le répertoire `lib/model/doctrine`:

  * `sfReferential` gère les enregistrements de la table `sf_referential` ;
  * `sfReferentialTable` gère la table `sf_referential` ;
  * `sfReferentialContractType` gère les enregistrements de la table  `sf_referential_contract_type` ;
  * `sfReferentialContractTypeTable` gère la table `sf_referential_contract_type` ;
  * `sfReferentialProductType` gère les enregistrements de la table `sf_referential_product_type` ;
  * `sfReferentialProductTypeTable` gère la table `sf_referential_product_type`.

Etudier les classes générées et leur héritage révèle que les deux classes de base des modèles `sfReferentialContractType` et `sfReferentialProductType` héritent de la classe `sfReferential`. Ainsi, toutes les méthodes publiques et protégées (y compris les propriétés) définies dans la classe `sfReferential` seront partagées jusque dans les classes les plus basses, et pourront être redéfinies si nécessaire.

C'est exactement le but recherché et la classe `sfReferential` peut désormais encapsuler des méthodes pour gérer toutes les données des référentiels. Par exemple :

    [php]
    // lib/model/doctrine/sfReferential.class.php
    class sfReferential extends BasesfReferential
    {
      public function promote()
      {
        // move up the record in the list
      }

      public function demote()
      {
        // move down the record in the list
      }

      public function moveToFirstPosition()
      {
        // move the record to the first position
      }

      public function moveToLastPosition()
      {
        // move the record to the last position
      }

      public function moveToPosition($position)
      {
        // move the record to a given position
      }

      public function makeDefault($forceSave = true, $conn = null)
      {
        $this->setIsDefault(true);

        if ($forceSave)
        {
          $this->save($conn);
        }
      }
    }

Grâce à l'héritage de table concret de Doctrine, tout le code est partagé à la même place. Le code devient ainsi plus facile à déboguer, à maintenir, à faire évoluer et à tester unitairement.

C'est le principal réel avantage de l'héritage de table. De plus, grâce à cette approche, les objets de modèle peuvent être utilisés pour centraliser le code des actions dans une classe spécifique. La classe `sfBaseReferentialActions` ci-dessous est une classe générique d'actions, dérivée par chaque contrôleur de chaque module de gestion des référentiels.

    [php]
    // lib/actions/sfBaseReferentialActions.class.php
    class sfBaseReferentialActions extends sfActions
    {
      /**
       * Ajax action that saves the new position as a result of the user
       * using a drag and drop in the list view.
       *
       * This action is linked thanks to an ~sfDoctrineRoute~ that
       * eases single referential object retrieval.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveToPosition(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());

        $referential = $this->getRoute()->getObject();

        $referential->moveToPosition($request->getParameter('position', 1));

        return sfView::NONE;
      }
    }

Que se passerait-il si le schéma de données n'utilisait pas de l'héritage de table ? Le code aurait alors besoin d'être dupliqué dans chaque classe de modèle de référentiel. Cette approche ne serait pas non plus très "DRY" (Don't Repeat Yourself), particulièrement lorsqu'il s'agit de travailler avec une douzaine de tables de référentiels identiques.

### Héritage de Table au Niveau des Formulaires ###

Le visite guidée des avantages de l'héritage de table Doctrine continue. La section précédente a montré combien cette fonctionnalité pouvait s'avérer utile pour partager des méthodes et des propriétés communes entre plusieurs modèles. La suite de ce chapitre explique comment l'héritage de table se comporte avec les formulaires générés par symfony.

#### Le Modèle de Données Etudié ####

Le schéma de données YAML ci-dessous décrit un modèle destiné à la gestion de documents numériques. L'objectif de cette étude de cas consiste à sauvegarder des informations génériques dans le modèle `File` et les données spécifiques dans des tables filles telles que `Vidéo` et `PDF`.

    [yml]
    File:
      columns:
        filename:
          type:            string(50)
          notnull:         true
        mime_type:
          type:            string(50)
          notnull:         true
        description:
          type:            clob
          notnull:         true
        size:
          type:            integer(8)
          notnull:         true
          default:         0

    Video:
      inheritance:
        type:              concrete
        extends:           File
      columns:
        format:
          type:            string(30)
          notnull:         true
        duration:
          type:            integer(8)
          notnull:         true
          default:         0
        encoding:
          type:            string(50)

    PDF:
      tableName:           pdf
      inheritance:
        type:              concrete
        extends:           File
      columns:
        pages:
          type:            integer(8)
          notnull:         true
          default:         0
        paper_size:
          type:            string(30)
        orientation:
          type:            enum
          default:         portrait
          values:          [portrait, landscape]
        is_encrypted:
          type:            boolean
          default:         false
          notnull:         true

Les deux tables `PDF` et `Video` partagent la même table `File`, qui contient les informations générales des documents numériques téléchargés. Le modèle `Vidéo` encapsule les données relatives aux fichiers vidéos telles que le `format` (4/3, 16/9...) ou la durée (`duration`), tandis que le modèle `PDF` contient le nombre de `pages` ou l'`orientation` du document. La commande `doctrine:build` ci-dessous construit le modèle de données et les formulaires correspondants.

    $ php symfony doctrine:build --all

La section suivante décrit comment tirer profit de l'héritage de table dans les classes de formulaires grâce à la nouvelle méthode ~`setupInheritance()`~.

#### A la Découverte de la Méthode ~setupInheritance()~ ###

Comme attendu, Doctrine a généré six classes de formulaires dans les répertoires `lib/form/doctrine/base` et `lib/form/doctrine`.

  * `BaseFileForm`
  * `BaseVideoForm`
  * `BasePDFForm`

  * `FileForm`
  * `VideoForm`
  * `PDFForm`

Il suffit d'ouvrir les trois classes préfixées par `Base` pour découvrir quelque chose de nouveau dans la méthode ~`setup()`~. Une nouvelle méthode ~`setupInheritance()`~, vide par défaut, a été ajoutée depuis symfony 1.3.

Il est très important de remarquer que l'héritage du formulaire est conservé dans la mesure où les deux classes `BaseVideoForm` et `BasePDFForm` héritent des classes `FileForm` et `BaseFileForm`. Par conséquent, chacune d'entre elles dérivent la classe `File` et peut également partager les mêmes méthodes de base.

Le listing suivant redéfinit la méthode `setupInheritance()` et configure la classe `FileForm` afin qu'elle puisse être réutilisée plus efficacement dans un autre sous-formulaire.

    [php]
    // lib/form/doctrine/FileForm.class.php
    class FileForm extends BaseFileForm
    {
      protected function setupInheritance()
      {
        parent::setupInheritance();

        $this->useFields(array('filename', 'description'));

        $this->widgetSchema['filename']    = new sfWidgetFormInputFile();
        $this->validatorSchema['filename'] = new sfValidatorFile(array(
          'path' => sfConfig::get('sf_upload_dir')
        ));
      }
    }

La méthode `setupInheritance()`, appelée par les deux sous-classes `VideoForm` et `PDFForm`, supprime tous les champs à l'exception de `filename` et `description`. Le widget du champ `filename` a été redéfini en un widget de téléchargement de fichier et son validateur correspondant a été remplacé par un objet ~`sfValidatorFile`~. De cette manière, l'utilisateur sera capable de transmettre un fichier et de le sauvegarder sur le serveur.

![Personnaliser les formulaires hérités avec la méthode setupInheritance()](http://www.symfony-project.org/images/more-with-symfony/05_table_inheritance_forms.png "Héritage de table Doctrine dans les formulaires")

#### Définir le Type Mime et la Taille du Fichier

Tous les formulaires sont désormais prêts et personnalisés. Il reste néanmoins  une toute dernière chose à configurer avant de pouvoir les utiliser. Comme les widgets `mime_type` et `size` ont été retirés de l'objet `FileForm`, ils doivent être définis par programmation. Le meilleur endroit pour y parvenir est de passer par une nouvelle méthode `generateFilenameFilename` dans la classe `File`.

    [php]
    // lib/model/doctrine/File.class.php
    class File extends BaseFile
    {
      /**
       * Generates a filename for the current file object.
       *
       * @param sfValidatedFile $file
       * @return string
       */
      public function generateFilenameFilename(sfValidatedFile $file)
      {
        $this->setMimeType($file->getType());
        $this->setSize($file->getSize());

        return $file->generateFilename();
      }
    }

Cette nouvelle méthode a pour rôle de générer un nom de fichier personnalisé pour le fichier avant de le sauvegarder sur le système de fichiers. Bien que la méthode `generateFilenameFilename()` retourne un nom de fichier auto-généré par défaut, elle sert aussi à définir les propriétés `mime_type` et `size` à la volée en s'appuyant sur l'objet ~`sfValidatedFile`~ passé en premier argument.

Depuis que symfony 1.3 supporte entièrement l'héritage de table Doctrine, les formulaires sont maintenant capables de sauver un objet ainsi que ses valeurs héritées. Le support natif de l'héritage permet ainsi de créer des formulaires puissants et fonctionnels avec seulement quelques lignes de code personnalisé.

L'exemple ci-dessous aurait pu être largement et facilement amélioré grâce à l'héritage de classe. Par exemple, les deux classes `VideoForm` et `PDFForm` peuvent redéfinir le validateur du champ `filename` par un validateur plus spécifique tel que `sfValidatorVideo` ou bien `sfValidatorPDF`. Il suffit pour cela de créer les deux classes, de leur faire étendre `sfValidatorFile` et de spécialiser leur option `mime_type` respective.

### L'Héritage de Table au Niveau des Filtres

Parce que les filtres sont aussi des formulaires, ils héritent eux aussi des propriétés et des méthodes des formulaires de filtres parents. Par conséquent, les objets `VideoFormFilter` et `PDFFormFilter` étendent la classe `FileFormFilter` et peuvent ainsi être personnalisés en utilisant la méthode ~`setupInheritance()`~.

De la même manière, les deux classes `VideoFormFilter` et `PDFFormFilter` peuvent partager les mêmes méthodes personnalisées de la classe `FileFormFilter`.

### L'Héritage de Table avec l'Admin Generator

Il est maintenant l'heure de découvrir comment tirer profit de l'héritage de table Doctrine dans l'Admin Generator grâce à l'une de ses nouvelles fonctionnalités : la définition d'une __classe d'actions de base__ commune. Il faut savoir que l'Admin Generator est l'une des fonctionnalités les plus appréciées des développeurs depuis symfony 1.0.

En novembre 2008, le framework symfony 1.2 s'est doté d'un tout nouveau système de génération de modules d'administration. Cet outil est livré par défaut avec de nombreuses fonctionnalités prêtes à l'emploi telles les opérations CRUD de base, le filtrage et la pagination de la liste de résultats, la suppression massive et bien plus encore... L'Admin Generator est un outil puissant qui facilite et accélère considérablement la génération et la personnalisation d'interfaces d'administration pour tout développeur.

#### Introduction à l'Exemple Pratique

L'objectif de la dernière partie de ce chapitre consiste à illustrer comment tirer parti de l'héritage de table Doctrine dans l'Admin Generator. Pour y parvenir, une petite interface d'administration sera bâtie. Cette dernière contiendra deux modules de gestion de tables qui contiennent toutes les deux des données qui peuvent être ordonnées et priorisées les unes par rapport aux autres.

Comme la ligne de conduite de symfony est de ne pas réinventer la roue à chaque fois, le modèle de données Doctrine s'appuiera sur le plugin [~`csDoctrineActAsSortablePlugin`~](http://www.symfony-project.org/plugins/csDoctrineActAsSortablePlugin "Page officielle du plugin csDoctrineActAsSortablePlugin") qui fournit toute l'API nécessaire à l'ordonnancement d'objets. Ce plugin est développé et maintenu par CentreSource, l'une des sociétés les plus actives dans l'écosystème de symfony.

Le modèle de données est particulièrement simple pour cet exemple. Il consiste en trois classes de modèle, `sfItem`, `sfTodoItem` et `sfShoppingItem`, qui servent à gérer une liste de choses à faire et une liste de courses. Chaque enregistrement de ces deux listes est ordonnable afin de permettre aux objets d'être priorisés les uns par rapport aux autres.

    [yml]
    sfItem:
      actAs:             [Timestampable]
      columns:
        name:
          type:          string(50)
          notnull:       true

    sfTodoItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        priority:
          type:          string(20)
          notnull:       true
          default:       minor
        assigned_to:
          type:          string(30)
          notnull:       true
          default:       me

    sfShoppingItem:
      actAs:             [Sortable]
      inheritance:
        type:            concrete
        extends:         sfItem
      columns:
        quantity:
          type:          integer(3)
          notnull:       true
          default:       1

Le schéma ci-dessus décrit le modèle de données séparé en trois classes de modèle. Les deux classes filles, `sfTodoItem` et `sfShoppingItem`, utilisent toutes les deux les comportements `Sortable` et `Timestampable` par héritage pour ce dernier. Le comportement `Sortable` est fourni par le plugin `csDoctrineActAsSortableBehaviorPlugin` qui ajoute une colonne supplémentaire `position` de type entier pour chaque table. Les deux classes étendent la classe de base `sfItem`, qui elle-même contient les colonnes `id` et `name`.

L'étape suivante consiste à ajouter quelques données de test sur lesquelles l'interface d'administration pourra agir. Les jeux de données de test sont, comme toujours, situés dans le fichier `data/fixtures.yml` du projet symfony.

    [yml]
    sfTodoItem:
      sfTodoItem_1:
        name:           "Write a new symfony book"
        priority:       "medium"
        assigned_to:    "Fabien Potencier"
      sfTodoItem_2:
        name:           "Release Doctrine 2.0"
        priority:       "minor"
        assigned_to:    "Jonathan Wage"
      sfTodoItem_3:
        name:           "Release symfony 1.4"
        priority:       "major"
        assigned_to:    "Kris Wallsmith"
      sfTodoItem_4:
        name:           "Document Lime 2 Core API"
        priority:       "medium"
        assigned_to:    "Bernard Schussek"

    sfShoppingItem:
      sfShoppingItem_1:
        name:           "Apple MacBook Pro 15.4 inches"
        quantity:       3
      sfShoppingItem_2:
        name:           "External Hard Drive 320 GB"
        quantity:       5
      sfShoppingItem_3:
        name:           "USB Keyboards"
        quantity:       2
      sfShoppingItem_4:
        name:           "Laser Printer"
        quantity:       1

Une fois le plugin `csDoctrineActAsSortablePlugin` correctement installé et le modèle de données prêt, le nouveau plugin requiert d'être activé dans la classe de configuration ~`ProjectConfiguration`~ du fichier `config/ProjectConfiguration.class.php`.

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        $this->enablePlugins(array(
          'sfDoctrinePlugin',
          'csDoctrineActAsSortablePlugin'
        ));
      }
    }

Ensuite, la base de données, le modèle, les formulaires et les filtres doivent être générés et les données initiales chargées dans la base afin d'alimenter les nouvelles tables créées. Cette tâche est accomplie en une fois grâce à la commande ~`doctrine:build`~ de symfony.

    $ php symfony doctrine:build --all --no-confirmation

Le cache de symfony doit enfin être nettoyé pour finaliser le processus, et les ressources web des plugins doivent être publiées sous la racine `web`.

    $ php symfony cache:clear
    $ php symfony plugin:publish-assets

La section suivante explique enfin comment construire pas à pas les modules d'administration avec les outils de l'Admin Generator, et comment profiter des classes d'actions de base personnalisées.

#### Construire le Backend

Cette partie du chapitre décrit les étapes nécessaires pour configurer une nouvelle application d'administration. Celle-ci contiendra les deux modules générés qui gèrent les listes de courses et de tâches. Par conséquent, la première chose à réaliser est de générer une application `backend` pour héberger ces futurs modules.

    $ php symfony generate:app backend

Avant symfony 1.3, et bien que l'Admin Generator soit un outil très abouti, les développeurs étaient néanmoins contraints de dupliquer du code commun entre les différents modules générés. Or, aujourd'hui, la tâche ~`doctrine:generate-admin`~ introduit une nouvelle option ~`--actions-base-class`~ qui permet au développeur de définir une classe d'actions de base pour chaque module généré.

Comme les deux modules à générer sont sensiblement les mêmes, ils auront certainement besoin de partager quelques actions communes. Le code de ces dernières peut ainsi être localisé dans une classe d'actions plus générique située dans le répertoire `lib/actions` du projet. Le code de cette classe maîtresse est présentée ci-dessous.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {

    }

Après que la nouvelle classe `sfSortableModuleActions` ait été créée et le cache de symfony nettoyé, les deux modules de l'application peuvent alors être générés dans l'application `backend`.

    $ php symfony doctrine:generate-admin --module=shopping --actions-base-class=sfSortableModuleActions backend sfShoppingItem

-

    $ php symfony doctrine:generate-admin --module=todo --actions-base-class=sfSortableModuleActions backend sfTodoItem

L'Admin Generator génère des modules dans deux répertoires séparés. Le premier répertoire conteneur est bien sûr `apps/backend/modules` bien qu'en réalité la majorité des modules générés se trouve dans le répertoire `cache/backend/dev/modules`. Tous les fichiers localisés dans ce répertoire sont régénérés à chaque fois que le cache est nettoyé ou bien lorsque la configuration d'un module change.

>**Note**
>Parcourir les fichiers mis en cache est une excellente manière de mieux 
>comprendre comment symfony et l'Admin Generator fonctionnent ensemble sous le 
>capot. Par conséquent, les nouvelles classes dérivées de 
>`sfSortableModuleActions` peuvent être retrouvées respectivement dans les 
>fichiers `cache/backend/dev/modules/autoShopping/actions/actions.class.php` et 
>`cache/backend/dev/modules/autoTodo/actions/actions.class.php`. Par défaut, 
>symfony aurait généré ces classes en les faisant hériter de la classe 
>~`sfActions`~.

![Interface d'administration par défaut de gestion de la liste de tâches](http://www.symfony-project.org/images/more-with-symfony/06_table_inheritance_backoffice_todo_1.png "Interface d'administration par défaut de gestion de la liste de tâches")

![Interface d'administration par défaut de gestion de la liste de courses](http://www.symfony-project.org/images/more-with-symfony/07_table_inheritance_backoffice_shopping_1.png "Interface d'administration par défaut de gestion de la liste de courses")

Les deux modules d'administration sont à présent prêts à être utilisés et personnalisés. La personnalisation des modules d'administration ne constitue pas l'objet de ce chapitre car cette tâche est particulièrement bien documentée, à commencer par le livre de référence : [symfony Reference Book](http://www.symfony-project.org/reference/1_3/en/06-Admin-Generator).

#### Modifier la Position d'un Enregistrement

La section précédente a décrit comment construire deux modules d'administration entièrement fonctionnels et qui héritent de la même classe d'actions. L'objectif suivant est de créer une action partagée qui offre au développeur la possibilité d'ordonner les objets d'une liste les uns par rapport aux autres. Satisfaire ce besoin fonctionnel est une tâche aisée dans la mesure où le plugin installé fournit une API complète pour manipuler les changements de position des objets.

La première étape à réaliser pour y parvenir consiste tout d'abord à créer deux nouvelles routes capables de déplacer un enregistrement vers le haut ou vers le bas dans la liste. Comme le générateur d'administration s'appuie sur une route ~`sfDoctrineRouteCollection`~, les nouvelles routes peuvent être déclarées et attachées à la collection grâce au fichier de configuration `config/generator.yml` de chaque module:

    [yml]
    # apps/backend/modules/shopping/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfShoppingItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_shopping_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, quantity]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Les modifications apportées dans le fichier précédent doivent être répétées pour le fichier de configuration du module `todo` comme le montre le code ci-dessous.

    [yml]
    # apps/backend/modules/todo/config/generator.yml
    generator:
      class: sfDoctrineGenerator
      param:
        model_class:           sfTodoItem
        theme:                 admin
        non_verbose_templates: true
        with_show:             false
        singular:              ~
        plural:                ~
        route_prefix:          sf_todo_item
        with_doctrine_route:   true
        actions_base_class:    sfSortableModuleActions

        config:
          actions: ~
          fields:  ~
          list:
            max_per_page:      100
            sort:              [position, asc]
            display:           [position, name, priority, assigned_to]
            object_actions:
              moveUp:          { label: "move up", action: "moveUp" }
              moveDown:        { label: "move down", action: "moveDown" }
              _edit:      ~
              _delete:    ~
          filter:  ~
          form:    ~
          edit:    ~
          new:     ~

Ces deux fichiers YAML décrivent la configuration pour les deux modules `shopping` et `todo`. Chacun d'entre eux a été personnalisé afin de convenir aux besoins de l'utilisateur final. Tout d'abord, la vue liste est à présent triée par ordre ascendant suivant la colonne `position`. Ensuite, le nombre maximum d'enregistrements par page a été augmenté jusqu'à la valeur 100 afin d'empêcher une pagination trop hâtive.

Enfin, le nombre de colonnes affichées a été réduit à l'ensemble suivant : `position`, `name`, `priority`, `assigned_to` et `quantity`. De plus, chaque module dispose désormais de deux nouvelles actions : `moveUp` et `moveDown`. Le rendu final de ces deux modules devrait ressembler aux captures d'écran ci-après.

![Interface personnalisée de gestion d'une liste de tâches](http://www.symfony-project.org/images/more-with-symfony/09_table_inheritance_backoffice_todo_2.png "Interface personnalisée de gestion d'une liste de tâches")

![Interface personnalisée de gestion d'une liste de courses](http://www.symfony-project.org/images/more-with-symfony/08_table_inheritance_backoffice_shopping_2.png "Interface personnalisée de gestion d'une liste de courses")

Ces deux nouvelles actions ont été déclarées mais ne réalisent encore rien pour le moment. Chacune d'entre elles doit être explicitement créée dans la classe d'actions partagées, `sfSortableModuleActions`, comme le montre le listing ci-dessous. Le plugin ~`csDoctrineActAsSortablePlugin`~ fournit deux méthodes supplémentaires pour chaque objet de modèle : `promote()` et `demote()`. Ces deux méthodes sont ici respectivement utilisées dans les actions `moveUp` et `moveDown`.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Moves an item up in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveUp(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->promote();

        $this->redirect($this->getModuleName());
      }

      /**
       * Moves an item down in the list.
       *
       * @param sfWebRequest $request
       */
      public function executeMoveDown(sfWebRequest $request)
      {
        $this->item = $this->getRoute()->getObject();

        $this->item->demote();

        $this->redirect($this->getModuleName());
      }
    }

Grâce à ces deux actions partagées, les deux listes de courses et de tâches sont désormais ordonnables. Par ailleurs, elles sont beaucoup plus faciles à maintenir et à tester à l'aide de tests fonctionnels. N'hésitez pas à améliorer l'interface et l'expérience utilisateur de chaque module en redéfinissant les templates d'actions d'objet afin de supprimer le premier lien `moveUp` et le dernier lien `moveDown` de chaque liste.

#### Bonus: Améliorer l'Expérience Utilisateur

Avant de clore ce chapitre, il convient de parfaire les deux listes en améliorant l'expérience utilisateur. Tout le monde s'accorde à dire que déplacer un enregistrement vers le haut (ou vers le bas) en cliquant sur un lien n'est pas si intuitif qu'il n'y parait pour l'utilisateur final.

Une bien meilleure approche consiste définitivement à inclure des comportements JavaScript et Ajax pour rendre les lignes du tableau déplaçables. Une fois de plus, il n'est pas question de réinventer la roue, c'est pourquoi ces comportements Ajax seront traités à l'aide du plugin jQuery ~`Table Drag and Drop`~. Un appel Ajax sera exécuté à chaque fois que l'utilisateur déplacera une ligne du tableau HTML à une autre position dans celui-ci.

La première étape de cette nouvelle série d'améliorations consiste à récupérer et installer le framework jQuery dans le répertoire `web/js`. Puis il s'agit de répéter cette même opération pour le plugin `Table Drag and Drop`, dont les sources sont hébergées dans un dépôt Subversion [Google Code](http://code.google.com/p/tablednd/). Il ne reste plus qu'à inclure ces deux scripts JavaScript dans le layout de l'application ou bien dans le fichier de configuration `view.yml`.

Pour fonctionner, la vue liste de chaque module doit inclure un petit morceau de code JavaScript et les deux tableaux HTML doivent également posséder un attribut `id`. Comme tous les templates et les partiels de l'Admin Generator sont surchargeables, le fichier `_list.php` (situé dans le cache par défaut) devrait alors être copié dans chaque module.

Cependant, copier le fichier `_list.php` dans le répertoire `templates/` de chaque module n'est pas tout à fait DRY. Il suffit en effet de copier le fichier `cache/backend/dev/modules/autoShopping/templates/_list.php` dans le répertoire `apps/backend/templates`, puis le renommer en `_table.php`. Ensuite, le contenu actuel de ce nouveau fichier doit être remplacé par le code suivant.

    [php]
    <div class="sf_admin_list">
      <?php if (!$pager->getNbResults()): ?>
        <p><?php echo __('No result', array(), 'sf_admin') ?></p>
      <?php else: ?>
        <table cellspacing="0" id="sf_item_table">
          <thead>
            <tr>
              <th id="sf_admin_list_batch_actions"><input id="sf_admin_list_batch_checkbox" type="checkbox" onclick="checkAll();" /></th>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_th_tabular',
                array('sort' => $sort)
              ) ?>
              <th id="sf_admin_list_th_actions">
                <?php echo __('Actions', array(), 'sf_admin') ?>
              </th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th colspan="<?php echo $colspan ?>">
                <?php if ($pager->haveToPaginate()): ?>
                  <?php include_partial(
                    $sf_request->getParameter('module').'/pagination',
                    array('pager' => $pager)
                  ) ?>
                <?php endif; ?>
                <?php echo format_number_choice(
                  '[0] no result|[1] 1 result|(1,+Inf] %1% results', 
                  array('%1%' => $pager->getNbResults()),
                  $pager->getNbResults(), 'sf_admin'
                ) ?>
                <?php if ($pager->haveToPaginate()): ?>
                  <?php echo __('(page %%page%%/%%nb_pages%%)', array(
                    '%%page%%' => $pager->getPage(), 
                    '%%nb_pages%%' => $pager->getLastPage()), 
                    'sf_admin'
                  ) ?>
                <?php endif; ?>
              </th>
            </tr>
          </tfoot>
          <tbody>
          <?php foreach ($pager->getResults() as $i => $item): ?>
            <?php $odd = fmod(++$i, 2) ? 'odd' : 'even' ?>
            <tr class="sf_admin_row <?php echo $odd ?>">
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_batch_actions',
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item,
                  'helper' => $helper
              )) ?>
              <?php include_partial(
                $sf_request->getParameter('module').'/list_td_tabular', 
                array(
                  'sf_'. $sf_request->getParameter('module') .'_item' => $item
              )) ?>
                <?php include_partial(
                  $sf_request->getParameter('module').'/list_td_actions',
                  array(
                    'sf_'. $sf_request->getParameter('module') .'_item' => $item, 
                    'helper' => $helper
                )) ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      </div>
      <script type="text/javascript">
        /* <![CDATA[ */
        function checkAll() {
          var boxes = document.getElementsByTagName('input'); 
          for (var index = 0; index < boxes.length; index++) { 
            box = boxes[index]; 
            if (
              box.type == 'checkbox' 
              && 
              box.className == 'sf_admin_batch_checkbox'
            ) 
            box.checked = document.getElementById('sf_admin_list_batch_checkbox').checked 
          }
          return true;
        }
        /* ]]> */
      </script>

Enfin, il ne reste plus qu'à créer un nouveau fichier `_list.php` à l'intérieur de chaque répertoire `templates/` des deux modules, et d'ajouter les codes suivants dans chacun d'eux.

    [php]
    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 5
    )) ?>
    
-

    // apps/backend/modules/shopping/templates/_list.php
    <?php include_partial('global/table', array(
      'pager' => $pager,
      'helper' => $helper,
      'sort' => $sort,
      'colspan' => 8
    )) ?>

Pour changer la position d'une ligne, les deux modules ont besoin d'implémenter une nouvelle action qui se charge de traiter la requête ajax à venir. Comme il l'a été montré précédemment dans ce chapitre, la nouvelle action `executeMove()` trouve naturellement sa place dans la classe d'actions communes `sfSortableModuleActions`.

    [php]
    // lib/actions/sfSortableModuleActions.class.php
    class sfSortableModuleActions extends sfActions
    {
      /**
       * Performs the Ajax request, moves an item to a new position.
       *
       * @param sfWebRequest $request
       */
      public function executeMove(sfWebRequest $request)
      {
        $this->forward404Unless($request->isXmlHttpRequest());
        $this->forward404Unless($item = Doctrine_Core::getTable($this->configuration->getModel())->find($request->getParameter('id')));

        $item->moveToPosition((int) $request->getParameter('rank', 1));

        return sfView::NONE;
      }
    }

L'action `executeMove()` fait appel à une méthode `getModel()` sur l'objet de configuration de l'Admin Generator. Cette nouvelle méthode doit donc être implémentée dans les deux classes de configuration `todoGeneratorConfiguration` et `shoppingGeneratorConfiguration` comme expliqué ci-dessous.

    [php]
    // apps/backend/modules/shopping/lib/shoppingGeneratorConfiguration.class.php
    class shoppingGeneratorConfiguration extends BaseShoppingGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfShoppingItem';
      }
    }

-

    // apps/backend/modules/todo/lib/todoGeneratorConfiguration.class.php
    class todoGeneratorConfiguration extends BaseTodoGeneratorConfiguration
    {
      public function getModel()
      {
        return 'sfTodoItem';
      }
    }

Il reste encore une toute dernière opération à réaliser. En effet, pour l'instant, les lignes des deux tableaux ne sont pas déplaçables et aucune requête ajax n'est exécutée lorsqu'une ligne du tableau est relâchée. Pour y parvenir, les deux modules ont besoin chacun d'une route spécifique pour accéder à leur action `move` respective. Par conséquent, le fichier `apps/backend/config/routing.yml` doit accueillir les deux nouvelles routes ci-dessous.

    [php]
    <?php foreach (array('shopping', 'todo') as $module) : ?>

    <?php echo $module ?>_move:
      class: sfRequestRoute
      url: /<?php echo $module ?>/move
      param:
        module: "<?php echo $module ?>"
        action: move
      requirements:
        sf_method: [get]

    <?php endforeach ?>

Afin d'éviter la duplication de code, les deux routes sont générées dynamiquement à l'intérieur d'une boucle `foreach`, et leur identifiant repose sur le nom du module afin de pouvoir les retrouver facilement dans la vue. 

Enfin, il ne reste plus que le fichier `apps/backend/templates/_table.php` qui doit implémenter le code JavaScript nécessaire à l'initialisation du comportement de glisser / déposer, et les requêtes Ajax correspondantes.

    [php]
    <script type="text/javascript" charset="utf-8">
      $().ready(function() {
        $("#sf_item_table").tableDnD({
          onDrop: function(table, row) {
            var rows = table.tBodies[0].rows;

            // Get the moved item's id
            var movedId = $(row).find('td input:checkbox').val();

            // Calculate the new row's position
            var pos = 1;
            for (var i = 0; i<rows.length; i++) {
              var cells = rows[i].childNodes;
              // Perform the ajax request for the new position
              if (movedId == $(cells[1]).find('input:checkbox').val()) {
                $.ajax({
                  url:"<?php echo url_for('@'. $sf_request->getParameter('module').'_move') ?>?id="+ movedId +"&rank="+ pos,
                  type:"GET"
                });
                break;
              }
              pos++;
            }
          },
        });
      });
    </script>

Le tableau HTML est à présent entièrement fonctionnel. Les lignes sont glissables et déposables, et la nouvelle position d'une ligne déplacée est automatiquement sauvée à l'aide d'un appel Ajax. Avec seulement quelques lignes de code, l'usabilité de l'interface d'administration a été grandement améliorée afin d'offrir une meilleure expérience utilisateur. L'Admin Generator est suffisamment flexible pour être étendu et personnalisé, et il fonctionne de plus parfaitement avec l'héritage de table Doctrine.

N'hésitez pas à améliorer ces deux modules en retirant les actions `moveUp` et `moveDown` obsolètes, ou bien en ajoutant d'autres personnalisations qui conviennent à vos besoins.

Conclusion
----------

Ce chapitre a décrit combien l'héritage de table de Doctrine est une puissante  fonctionnalité qui aide le développeur à coder plus vite, mais aussi à améliorer significativement l'organisation de son code.

Cet outil de Doctrine est entièrement intégré à plusieurs niveaux dans symfony. Ainsi, les développeurs sont désormais fortement encouragés à l'utiliser et en tirer parti dans le but d'augmenter leur efficacité, d'améliorer l'organisation de leur code et bien sûr de parfaire la productivité de leurs projets.