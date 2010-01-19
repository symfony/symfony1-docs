Formulaires Avancés
===================

*Par Ryan Weaver, Fabien Potencier*

Le framework de formulaires de symfony équipe le développeur des outils nécessaires à l'affichage et à la validation des données dans un problème orienté objet. Grâce aux classes ~`sfFormDoctrine`~ et ~`sfFormPropel`~ proposées par chaque ORM, le framework de formulaires peut facilement afficher et sauvegarder des formulaires liés au modèle de données. 

Toutefois, des situations courantes demandent au développeur de personnaliser et d'étendre des formulaires. Ce chapitre présentera et résoudra quelques uns des problèmes complexes récurrents liés aux formulaires. L'objet ~`sfForm`~ sera quant à lui disséqué afin de lever une partie du mystère.

Mini-Projet : Produits et Photos
--------------------------------

Le premier problème concerne l'édition d'un produit individuel et d'un nombre de photos illimité pour ce produit. L'utilisateur doit pouvoir modifier le produit et ses photos associées sur le même formulaire. Il s'agit de permettre à l'utilisateur d'envoyer jusqu'à deux photos du produit à la fois. Le modèle de données ci-dessous présente une implémentation potentielle pour résoudre ce problème.

    [yml]
    Product:
      columns:
        name:           { type: string(255), notnull: true }
        price:          { type: decimal, notnull: true }

    ProductPhoto:
      columns:
        product_id:     { type: integer }
        filename:       { type: string(255) }
        caption:        { type: string(255), notnull: true }
      relations:
        Product:
          alias:        Product
          foreignType:  many
          foreignAlias: Photos
          onDelete:     cascade

Lorsqu'il sera terminé, le formulaire ressemblera à la capture d'écran ci-après. 

![Formulaire d'ajout de produit et de photos](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_01.png "Formulaire Product et ProductPhoto embarqués")

Apprendre par l'Exemple
-----------------------

Le meilleur moyen d'apprendre les techniques avancées d'usage des formulaires est bien entendu de suivre le déroulement de ce chapitre, et de tester les exemples présentés étape par étape.

Grâce à la fonctionnalité `--installer` de [symfony](#chapter_03), le framework offre la possibilité de créer un projet fonctionnel accompagné d'une base de données SQLite prête à l'emploi. Ce projet intègre un modèle de base de données Doctrine, quelques données de test, une application `frontend` et un module `product` pour travailler. Le [script](http://www.symfony-project.org/images/more-with-symfony/advanced_form_installer.php.src) d'installation est disponible en téléchargement et s'exécute à l'aide de la commande suivante afin de générer la base du projet symfony.

    $ php symfony generate:project advanced_form --installer=/path/to/advanced_form_installer.php

Cette commande crée un projet complet et fonctionnel à partir du schéma de base de données étudié dans la section précédente.

>**NOTE**
>Dans ce chapitre, les chemins des fichiers correspondent à un projet symfony 
>utilisant Doctrine dans la mesure où il a été généré par la commande 
>précédente.

Configuration de Base des Formulaires
-------------------------------------

Puisque les besoins entraînent des changements sur deux modèles différents, `Product` et `ProductPhoto`, la solution oblige à contenir deux formulaires symfony (`ProductForm` et `ProductPhotoForm`). Heureusement, le framework de formulaires peut facilement combiner plusieurs formulaires en un seul via la méthode ~`sfForm::embedForm()`~. Il s'agit tout d'abord de configurer la classe `ProductPhotoForm`. Dans cet exemple, c'est le champ `filename` qui est utilisé comme champ d'envoi de fichiers.

    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setWidget('filename', new sfWidgetFormInputFile());
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
      )));
    }

Pour ce formulaire, les deux champs `caption` et `filename` sont requis par défaut, mais pour des raisons différentes. Le champ `caption` est obligatoire car la colonne relative en base de données a été définie avec la propriété `notnull` à `true`. Le champ `filename` est quant à lui obligatoire par défaut car un objet validateur a toujours l'option `required` à `true` par défaut.

>**NOTE**
>~`sfForm::useFields()`~ est une nouvelle méthode de symfony 1.3 qui permet au 
>développeur de spécifier exactement les champs que le formulaire devra utiliser 
>et l'ordre dans lequel ils seront affichés. Tous les autres champs non affichés
>seront retirés du formulaire.

Jusqu'à présent, rien de particulier n'a été réalisé si ce n'est une configuration ordinaire du formulaire. Il s'agit maintenant de combiner les formulaires en un seul.

Imbriquer les Formulaires
-------------------------

En invoquant la méthode ~`sfForm::embedForm()`~, les formulaires indépendants `ProductForm` et `ProductPhotoForms` peuvent être combinés très facilement. Le travail est effectué dans le formulaire *principal*, `ProductForm` dans cet exemple. Les besoins fonctionnels spécifient que l'utilisateur final doit être capable d'envoyer jusqu'à deux photos d'un même produit à la fois. Pour ce faire, deux objets `ProductPhotoForm` seront embarqués dans l'objet `ProductForm`.

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $subForm = new sfForm();
      for ($i = 0; $i < 2; $i++)
      {
        $productPhoto = new ProductPhoto();
        $productPhoto->Product = $this->getObject();

        $form = new ProductPhotoForm($productPhoto);

        $subForm->embedForm($i, $form);
      }
      $this->embedForm('newPhotos', $subForm);
    }

En accédant directement au module `product` depuis un navigateur, l'utilisateur a désormais la possibilité de soumettre deux objets `ProductPhoto` mais également de modifier l'objet `Product` lui-même. Symfony sauvegarde automatiquement les nouveaux objets `ProductPhoto` et les relie à l'objet `Product` correspondant. L'envoi de fichiers défini dans la classe `ProductPhotoForm` fonctionne lui aussi normalement.

A ce stade, il s'agit de vérifier que les enregistrements ont été correctement sauvegardés en base de données.

    $ php symfony doctrine:dql --table "FROM Product"
    $ php symfony doctrine:dql --table "FROM ProductPhoto"

Il est intéressant de remarquer les noms des photos dans la table `ProductPhoto`. Tout fonctionne comme prévu à condition de trouver des fichiers avec les mêmes noms que ceux de la base de données dans le répertoire `web/uploads/products/`.

>**NOTE**
>Etant donnés que les champs `filename` et `caption` sont requis dans 
>`ProductPhotoForm`, la validation du formulaire principal échouera tout le 
>temps tant que l'utilisateur n'enverra pas deux nouvelles photos. La suite de 
>ce chapitre explique comment résoudre ce problème.

Remaniement
-----------

Bien que le formulaire précédent se comporte comme prévu, il serait néanmoins plus judicieux de factoriser le code afin de faciliter l'écriture de tests. De plus, cette pratique permet de réutiliser le code plus aisément.

Tout d'abord, il s'agit de créer un nouveau formulaire qui représente une collection d'objets `ProductPhotoForm` en s'appuyant sur le code écrit jusqu'à maintenant.

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    class ProductPhotoCollectionForm extends sfForm
    {
      public function configure()
      {
        if (!$product = $this->getOption('product'))
        {
          throw new InvalidArgumentException('You must provide a product object.');
        }

        for ($i = 0; $i < $this->getOption('size', 2); $i++)
        {
          $productPhoto = new ProductPhoto();
          $productPhoto->Product = $product;

          $form = new ProductPhotoForm($productPhoto);

          $this->embedForm($i, $form);
        }
      }
    }

Ce formulaire nécessite deux options : 

 * `product` : le produit pour lequel la collection d'objets `ProductPhotoForm` doit être créée ;
 * `size`: le nombre d'objets `ProductPhotoForm` à créer, deux par défaut.

La méthode `configure()` de la classe `ProductForm` peut être alors être modifiée comme suit.

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      $form = new ProductPhotoCollectionForm(null, array(
        'product' => $this->getObject(),
        'size'    => 2,
      ));

      $this->embedForm('newPhotos', $form);
    }

Dissection de l'Objet sfForm
----------------------------

En réalité, un formulaire web est une collection de champs qui sont affichés et envoyés au serveur. Dans le même esprit, l'objet ~`sfForm`~ est essentiellement un tableau de *champs* de formulaire. Alors que ~`sfForm`~ s'occupe du processus, les champs individuels sont responsables de définir comment chacun doit s'afficher et être validé. 

Dans symfony, chaque *champ* de formulaire est défini à l'aide de deux objets distincts : 

  * Un *widget* qui affiche le code XHTML du champ ;
  
  * Un *validateur* qui nettoie et valide les données envoyées.

>**TIP**
>Dans symfony, un *widget* est défini comme n'importe quel objet dont la seule 
>finalité est d'afficher du code XHTML. Bien qu'ils soient couramment utilisés 
>dans les formulaires, les widgets peuvent être créés pour afficher n'importe 
>quelle balise.

### Un Formulaire est un Tableau

Pour rappel, un objet ~`sfForm`~ est essentiellement un "tableau de champs de formulaires". Pour être plus précis, l'objet `sfForm` abrite un tableau de widgets et un tableau de validateurs pour tous les champs du formulaire. Ces deux tableaux, appelés `widgetSchema` et`validatorSchema`, sont des propriétés de la classe `sfForm`.

Pour ajouter un champ au formulaire, il suffit d'ajouter simplement le widget du champ dans le tableau `widgetSchema` et le validateur du champ dans le tableau `validatorSchema`. Par exemple, le code suivant déclare un champ `email` dans le formulaire.

    [php]
    public function configure()
    {
      $this->widgetSchema['email'] = new sfWidgetFormInputText();
      $this->validatorSchema['email'] = new sfValidatorEmail();
    }

>**NOTE**
>Les tableaux `widgetSchema` et `validatorSchema` sont en réalité des classes 
>spéciales appelées ~`sfWidgetFormSchema`~ et ~`sfValidatorSchema`~ qui 
>implémentent l'interface `ArrayAccess`.

### Dissection de la Classe `ProductForm`

Comme la classe `ProductForm` étend fatalement la classe `sfForm`, elle abrite tous les widgets et validateurs dans les tableaux `widgetSchema` et `validatorSchema`. Le listing ci-dessous décrit l'organisation générale de chaque tableau dans un objet `ProductForm` entièrement élaboré.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [filename]    => sfWidgetFormInputFile,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [filename]    => sfValidatorFile,
          [caption]     => sfValidatorString,
        ),
      ),
    )

>**TIP**
>Comme les objets `widgetSchema` et `validatorSchema` se comportent tels des
>tableaux, les tableaux ci-dessus définis par les clés `newPhotos`, `0` et `1` 
>sont aussi des objets `sfWidgetSchema` et `sfValidatorSchema`.

Comme prévu, les champs basiques (`id`, `name` et `price`) sont représentés au premier niveau de chaque tableau. Dans un formulaire sans formulaire imbriqué, les tableaux `widgetSchema` et `validatorSchema` ont un seul niveau qui représente les champs de base du formulaire. Les widgets et validateurs de n'importe quel formulaire embarqué sont représentés comme des sous-tableaux dans `widgetSchema` et `validatorSchema` comme cela a été démontré précédemment. La méthode qui s'occupe de ce processus est expliquée après. 

### La Méthode ~`sfForm::embedForm()`~ en Coulisses

Il convient de garder à l'esprit qu'un formulaire est composé d'un tableau de widgets et d'un tableau de validateurs. Embarquer un formulaire dans un autre signifie essentiellement que les tableaux des widgets et des validateurs d'un formulaire seront ajoutés dans les tableaux des widgets et des validateurs du formulaire principal. Cette tâche est entièrement effectuée par la méthode  `sfForm::embedForm()`. Le résultat est toujours une addition multidimensionnelle des tableaux `widgetSchema` et `validatorSchema`.

Maintenant, c'est au tour de la configuration de l'objet `ProductPhotoCollectionForm` d'être étudiée car c'est elle qui lie les objets `ProductPhotoForm`. Ce formulaire du milieu agit comme un formulaire d'adaptation et aide à son organisation. Il convient alors de commencer par l'étude du code suivant de la méthode `ProductPhotoCollectionForm::configure()`.

    [php]
    $form = new ProductPhotoForm($productPhoto);
    $this->embedForm($i, $form);

Le formulaire `ProductPhotoCollectionForm` commence lui-même comme un nouvel objet `sfForm`. En tant que tels, les tableaux `widgetSchema` et `validatorSchema` sont vides.

    [php]
    widgetSchema    => array()
    validatorSchema => array()

Cependant, l'objet `ProductPhotoForm` est déjà préparé avec trois champs (`id`, `filename` et `caption`), et trois entrées correspondantes dans ses tableaux `widgetSchema` et `validatorSchema`.

    [php]
    widgetSchema    => array
    (
      [id]            => sfWidgetFormInputHidden,
      [filename]      => sfWidgetFormInputFile,
      [caption]       => sfWidgetFormInputText,
    )

    validatorSchema => array
    (
      [id]            => sfValidatorDoctrineChoice,
      [filename]      => sfValidatorFile,
      [caption]       => sfValidatorString,
    )

La méthode ~`sfForm::embedForm()`~ ajoute simplement les tableaux `widgetSchema` et `validatorSchema` de chaque `ProductPhotoForm` aux tableaux `widgetSchema` et `validatorSchema` de l'objet `ProductPhotoCollectionForm` vide.

Une fois terminés, les tableaux `widgetSchema` et `validatorSchema` du formulaire d'adaptation (`ProductPhotoCollectionForm`) deviennent des tableaux multi-dimensionnels contenant les widgets et les validateurs des deux objets `ProductPhotoForm`.

    [php]
    widgetSchema    => array
    (
      [0]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
      [1]             => array
      (
        [id]            => sfWidgetFormInputHidden,
        [filename]      => sfWidgetFormInputFile,
        [caption]       => sfWidgetFormInputText,
      ),
    )

    validatorSchema => array
    (
      [0]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
      [1]             => array
      (
        [id]            => sfValidatorDoctrineChoice,
        [filename]      => sfValidatorFile,
        [caption]       => sfValidatorString,
      ),
    )

Dans la dernière étape du processus, le formulaire d'adaptation résultant, `ProductPhotoCollectionForm` est embarqué directement dans l'objet `ProductForm`. Cela se produit dans la méthode `ProductForm::configure()` qui tire profit de tout le travail réalisé dans l'objet `ProductPhotoCollectionForm`.

    [php]
    $form = new ProductPhotoCollectionForm(null, array(
      'product' => $this->getObject(),
      'size'    => 2,
    ));

    $this->embedForm('newPhotos', $form);

Ceci établit la dernière structure des tableaux `widgetSchema` et `validatorSchema` vus ci-dessus. A noter que la méthode `embedForm()` est très proche du simple fait de la combinaison manuelle des tableaux `widgetSchema` et `validatorSchema`.

    [php]
    $this->widgetSchema['newPhotos'] = $form->getWidgetSchema();
    $this->validatorSchema['newPhotos'] = $form->getValidatorSchema();

Afficher des Formulaires Imbriqués dans la Vue
----------------------------------------------

Le template actuel `_form.php` du module `product` ressemble sensiblement au code ci-dessous :

    [php]
    // apps/frontend/module/product/templates/_form.php
    <!-- ... -->

    <tbody>
      <?php echo $form ?>
    </tbody>

    <!-- ... -->

La ligne `<?php echo $form ?>` est à la fois la façon la plus simple d'afficher un formulaire, et la plus compliquée. Elle est d'une grande utilité lorsqu'il s'agit de réaliser un prototype. Or, dès qu'un changement de l'agencement est nécessaire, elle doit être remplacée par un code spécifique à l'affichage désiré. Elle peut alors être supprimée dans la mesure où elle sera de toute manière modifiée dans cette section.

La chose la plus importante à comprendre lorsqu'il s'agit d'afficher un formulaire imbriqué dans la vue est l'organisation du tableau multidimensionnel `widgetSchema` expliquée dans la section précédente. Pour cet exemple, l'objectif consiste à commencer par afficher les champs de base `name` et `price` de l'objet `ProductForm` dans la vue.

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php echo $form['name']->renderRow() ?>

    <?php echo $form['price']->renderRow() ?>

    <?php echo $form->renderHiddenFields() ?>

Comme son nom l'indique, la méthode `renderHiddenFields()` génère tous les
champs cachés du formulaire.

>**NOTE**
>Le code des actions n'a pas été exposé volontairement car il ne nécessite pas 
>d'attention particulière. Il suffit de regarder le fichier d'actions 
>`apps/frontend/modules/product/actions/actions.class.php` pour s'en persuader. 
>Il ressemble en effet à n'importe quel CRUD et peut être généré automatiquement 
>à l'aide de la tâche `doctrine:generate-module`.

La classe `sfForm` abrite désormais les tableaux `widgetSchema` et `validatorSchema` qui définissent les champs. De plus, la classe `sfForm` implémente la classe native `ArrayAccess` de PHP 5, ce qui signifie que les champs du formulaire sont directement accessibles par l'intermédiaire de la syntaxe des clés de tableaux vue précédemment.

L'affichage des champs un par un nécessite d'accéder à un champ de manière unique en appelant sa méthode `renderRow()`. Mais quel est le type de l'objet `$form['name']` ? Alors que la réponse se pourrait d'être le widget `sfWidgetFormInputText` pour le champ `name`, elle est en réalité sensiblement différente.

### Afficher chaque Champ du Formulaire avec ~`sfFormField`~

En utilisant les tableaux `widgetSchema` et `validatorSchema` définis dans chaque classe de formulaire, `sfForm` génère automatiquement un troisième tableau appelé `sfFormFieldSchema`. Ce tableau contient un objet spécial pour chaque champ qui agit comme une classe helper responsable de l'affichage du champ. L'objet, de type ~`sfFormField`~, est une combinaison d'un widget et d'un validateur pour chaque champ, et est créé automatiquement. 

    [php]
    <?php echo $form['name']->renderRow() ?>

Dans le morceau de code précédent, `$form['name']` est un objet de type `sfFormField` qui abrite la méthode `renderRow()` avec plusieurs autres fonctions de rendu utiles.

### Les Méthodes de Rendu de sfFormField

Chaque objet de type `sfFormField` peut être utilisé pour générer le rendu de tous les aspects du champ qu'il représente. Par exemple, le champ lui même, le  label, les messages d'erreurs etc. Voici quelques méthodes utiles de l'objet `sfFormField`. Les autres peuvent être trouvées via [l'API en ligne de symfony 1.3](http://www.symfony-project.org/api/1_3/sfFormField).

 * `sfFormField->render()` génère le champ du formulaire (par exemple
   `input`, `select`) avec les bonnes valeurs en utilisant l'objet widget du
   champ ;
   
 * `sfFormField->renderError()` génère toutes les erreurs de validation sur
    le champ en utilisant l'objet validateur du champ ;
   
 * `sfFormField->renderRow()` est une méthode englobante qui affiche le label, 
   le champ du formulaire, l'erreur et le message d'aide.

>**NOTE**
>En réalité, chaque méthode d'affichage de la classe `sfFormField` utilise 
>également des informations de la propriété `widgetSchema` du formulaire. C'est 
>le cas par exemple de l'objet `sfWidgetFormSchema` qui abrite tous les widgets 
>du formulaire. Cette classe aide à la génération des attributs `name` et `id` 
>de chaque champ, garde une trace du label pour chaque champ et définit la 
>balise XHTML utilisée avec `renderRow()`.

Il est important de noter que le tableau `formFieldSchema` reflète toujours la structure des tableaux `widgetSchema` et `validatorSchema` du formulaire. Par exemple, le tableau `formFieldSchema` d'un objet `ProductForm` complet aura la structure suivante, qui est la clé du rendu de chaque champ dans la vue.

    [php]
    formFieldSchema    => array
    (
      [id]          => sfFormField
      [name]        => sfFormField,
      [price]       => sfFormField,
      [newPhotos]   => array(
        [0]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
        [1]           => array(
          [id]          => sfFormField,
          [filename]    => sfFormField,
          [caption]     => sfFormField,
        ),
      ),
    )

### Rendu d'un Nouveau `ProductForm`

En utilisant le tableau ci-dessus comme carte, il est facile d'afficher les champs du formulaire embarqué `ProductPhotoForm` dans la vue en localisant et en affichant l'objet `sfFormField`.

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['newPhotos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow() ?>
    <?php endforeach; ?>

Le bloc de code ci-dessus itère à deux reprises : une fois pour le tableau à l'index `0` et une seconde fois pour le tableau à l'index `1`. Comme l'illustrait le diagramme ci-dessus, les objets sous-jacents de chaque tableau sont de type `sfFormField`, qui peuvent donc être affichés comme n'importe quel autre champ.

Sauvegarder des Formulaires d'Objets
------------------------------------

Dans la plupart des cas, un formulaire repose directement sur une ou plusieurs tables de la base de données, et entraîne des changements sur les données dans ces tables en fonction des valeurs envoyées. Symfony génère automatiquement un objet de formulaire pour chaque modèle du schéma, qui étend soit `sfFormDoctrine` ou `sfFormPropel` en fonction de l'ORM choisi. Chaque classe de formulaire est similaire et permet finalement aux valeurs transmises de rester en base de données.

>**NOTE**
>~`sfFormObject`~ est une nouvelle classe ajoutée dans symfony 1.3 pour gérer 
>toutes les tâches communes de `sfFormDoctrine` et `sfFormPropel`. Chaque classe 
>étend `sfFormObject`, qui s'occupe maintenant du processus de sauvegarde du 
>formulaire décrit ci-dessous.

### Le Processus de Sauvegarde du Formulaire 

Dans cet exemple, symfony sauvegarde automatiquement les informations de l'objet `Product` et des nouveaux objets `ProductPhoto` sans autre intervention du développeur. C'est la méthode ~`sfFormObject::save()`~ qui exécute une multitude de méthodes en arrière plan. La compréhension de ce processus est la clé pour étendre ce traitement à des cas plus complexes. 

Le processus de sauvegarde du formulaire est une suite de méthodes exécutées en interne, qui se déclenchent après l'appel de la méthode  ~`sfFormObject::save()`~. La  majorité du travail est située dans la méthode  ~`sfFormObject::updateObject()`~ qui est appelée récursivement sur tous les formulaires imbriqués. 

![Processus de sauvegarde du formulaire](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_06.png "Processus détaillé de sauvegarde du formulaire")

>**NOTE**
>La majorité du processus de sauvegarde intervient dans la méthode 
~`sfFormObject::doSave()`~, qui est appelée par `sfFormObject::save()` et 
>entourée par une transaction. Si le processus de sauvegarde lui-même doit être 
>surchargé, c'est alors dans la méthode `sfFormObject::doSave()` que ce travail 
>doit être réalisé.

Ignorer les Formulaires Imbriqués
---------------------------------

L'implémentation actuelle de `ProductForm` a un inconvénient majeur. Etant donnés que les champs `filename` et `caption` sont nécessaires dans `ProductPhotoForm`, la validation du formulaire principal échouera à chaque fois tant que l'utilisateur n'enverra pas deux nouvelles photos. En d'autres termes, l'utilisateur ne peut alors changer le prix du produit sans envoyer deux nouvelles photos. 

![Echec de la validation du formulaire du produit](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_04.png "Echec de la validation du formulaire du produit")

Les champs obligatoires du formulaire doivent être redéfinis afin d'inclure les suivants. Si l'utilisateur laisse vides tous les champs du formulaire `ProductPhotoForm`, ce formulaire sera alors complètement ignoré. Cependant, si au moins un champ possède des données (par exemple `caption` ou `filename`), le formulaire devra être validé et sauvegardé normalement. Pour ce faire, le formulaire a besoin d'une technique avancée nécessitant l'utilisation d'un post validateur personnalisé.

La première étape consiste à modifier le formulaire `ProductPhotoForm` afin de rendre les champs `caption` et `filename` optionnels.


    [php]
    // lib/form/doctrine/ProductPhotoForm.class.php
    public function configure()
    {
      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Dans le code ci-dessus, la valeur de l'option `required` a été modifiée à `false`, en surchargeant la valeur par défaut du validateur pour le champ `filename`. De plus, la valeur de l'option `required` du champ `caption` a été explicitement configurée à `false`.

Le code ci-dessous se charge ensuite d'ajouter un post validateur à l'objet  `ProductPhotoCollectionForm`.

    [php]
    // lib/form/doctrine/ProductPhotoCollectionForm.class.php
    public function configure()
    {
      // ...

      $this->mergePostValidator(new ProductPhotoValidatorSchema());
    }

Un post validateur est un type de validateur particulier qui exécute une validation sur toutes les données soumises après le processus de validation classique. Il s'oppose à la validation valeur par valeur de chaque champ. L'un des post validateurs les plus courants est `sfValidatorSchemaCompare` qui vérifie, par exemple, que la valeur d'un certain champ est inférieure à celle d'un autre.

### Création d'un Validateur Personnalisé

Heureusement, la création d'un validateur personnalisé est en fait simple. Il suffit de créer un nouveau fichier `ProductPhotoValidatorSchema.class.php` et de le placer dans le répertoire `lib/validator`. La création de ce répertoire est à la charge du lecteur.

    [php]
    // lib/validator/ProductPhotoValidatorSchema.class.php
    class ProductPhotoValidatorSchema extends sfValidatorSchema
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addMessage('caption', 'The caption is required.');
        $this->addMessage('filename', 'The filename is required.');
      }

      protected function doClean($values)
      {
        $errorSchema = new sfValidatorErrorSchema($this);

        foreach($values as $key => $value)
        {
          $errorSchemaLocal = new sfValidatorErrorSchema($this);

          // filename is filled but no caption
          if ($value['filename'] && !$value['caption'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'caption');
          }

          // caption is filled but no filename
          if ($value['caption'] && !$value['filename'])
          {
            $errorSchemaLocal->addError(new sfValidatorError($this, 'required'), 'filename');
          }

          // no caption and no filename, remove the empty values
          if (!$value['filename'] && !$value['caption'])
          {
            unset($values[$key]);
          }

          // some error for this embedded-form
          if (count($errorSchemaLocal))
          {
            $errorSchema->addError($errorSchemaLocal, (string) $key);
          }
        }

        // throws the error for the main form
        if (count($errorSchema))
        {
          throw new sfValidatorErrorSchema($this, $errorSchema);
        }

        return $values;
      }
    }

>**TIP**
>Tous les validateurs étendent la classe abstraite `sfValidatorBase` qui les 
>oblige à implémenter la méthode `doClean()`, déclarée abstraite. La méthode 
>`configure()` peut également être utilisée pour ajouter des options ou messages 
>d'erreur au validateur. Dans l'exemple précédent, deux messages ont été ajoutés 
>au validateur. D'autres options peuvent également être définies à l'aide de la 
>méthode `addOption()`.

La méthode `doClean()` est responsable du nettoyage et de la validation des valeurs envoyées. La logique du validateur est quant à elle triviale.

 * Si une photo est envoyée uniquement avec un fichier ou une légende, alors 
   une erreur est générée (`sfValidatorErrorSchema`) avec le message approprié ;

 * Si une photo est soumise sans fichier et sans légende, alors les valeurs sont 
   supprimées afin d'éviter de sauvegarder une photo vide ;
   
 * Si aucune erreur de validation n'a été produite, la méthode retourne un
   tableau de valeurs nettoyées.

>**TIP**
>Dans cette situation, étant donné que le validateur personnalisé doit être 
>utilisé comme un validateur global, la méthode `doClean()` attend un tableau 
>des valeurs soumises et retourne un tableau des valeurs nettoyées. Cependant 
>les validateurs personnalisés peuvent être créés pour des champs individuels. 
>Dans ce cas, la méthode `doClean()` n'attendra qu'une seule valeur (la valeur 
>du champ) et ne retournera qu'une seule valeur nettoyée.

La dernière étape consiste à surcharger la méthode `saveEmbeddedForms()` de la classe `ProductForm` afin de supprimer les formulaires de photos vides, et ainsi éviter de sauvegarder une photo vide en base de données. Une exception serait en effet levée car le champ `caption` est requis.

    [php]
    public function saveEmbeddedForms($con = null, $forms = null)
    {
      if (null === $forms)
      {
        $photos = $this->getValue('newPhotos');
        $forms = $this->embeddedForms;
        foreach ($this->embeddedForms['newPhotos'] as $name => $form)
        {
          if (!isset($photos[$name]))
          {
            unset($forms['newPhotos'][$name]);
          }
        }
      }

      return parent::saveEmbeddedForms($con, $forms);
    }

Imbriquer Facilement des Formulaires Doctrine 
---------------------------------------------

Une nouveauté de symfony 1.3 est la méthode ~`sfFormDoctrine::embedRelation()`~ qui offre au développeur la possibilité d'imbriquer automatiquement des relations n-à-plusieurs dans un formulaire. Dans l'exemple de ce chapitre, il serait alors intéressant de permettre à l'utilisateur de pouvoir à la fois télécharger deux nouvelles photos, mais aussi de le rendre capable de modifier les objets `ProductPhoto` existants rattachés à l'objet `Product`.

Pour ce faire, il suffit d'utiliser la méthode `embedRelation()` afin d'ajouter un objet `ProductPhotoForm` additionnel pour chaque objet `ProductPhoto` existant.

    [php]
    // lib/form/doctrine/ProductForm.class.php
    public function configure()
    {
      // ...

      $this->embedRelation('Photos');
    }

En interne, ~`sfFormDoctrine::embedRelation()`~ fait quasiment la même chose 
que le processus décrit plus tôt pour imbriquer deux nouveaux objets `ProductPhotoForm`. Si deux relations `ProductPhoto` existent déjà, alors les objets `widgetSchema` et `validatorSchema` résultants seront de la forme suivante.

    [php]
    widgetSchema    => array
    (
      [id]          => sfWidgetFormInputHidden,
      [name]        => sfWidgetFormInputText,
      [price]       => sfWidgetFormInputText,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
        [1]           => array(
          [id]          => sfWidgetFormInputHidden,
          [caption]     => sfWidgetFormInputText,
        ),
      ),
    )

    validatorSchema => array
    (
      [id]          => sfValidatorDoctrineChoice,
      [name]        => sfValidatorString,
      [price]       => sfValidatorNumber,
      [newPhotos]   => array(...)
      [Photos]      => array(
        [0]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
        [1]           => array(
          [id]          => sfValidatorDoctrineChoice,
          [caption]     => sfValidatorString,
        ),
      ),
    )

![Formulaire produit avec 2 photos existantes](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_03.png "Formulaire produit avec 2 photos existantes")

L'étape qui suit consiste à ajouter du code dans la vue pour afficher les formulaires *Photo* imbriqués.

    [php]
    // apps/frontend/module/product/templates/_form.php
    <?php foreach ($form['Photos'] as $photo): ?>
      <?php echo $photo['caption']->renderRow() ?>
      <?php echo $photo['filename']->renderRow(array('width' => 100)) ?>
    <?php endforeach; ?>

Ce morceau de code est exactement le même que celui qui a été utilisé plus tôt pour embarquer les nouveaux formulaires de photos. Enfin, la dernière étape consiste à modifier le champ d'envoi de fichier par un widget qui permet à l'utilisateur de visualiser la photo courante et de la remplacer par une nouvelle (`sfWidgetFormInputFileEditable`).

    [php]
    public function configure()
    {
      $this->useFields(array('filename', 'caption'));

      $this->setValidator('filename', new sfValidatorFile(array(
        'mime_types' => 'web_images',
        'path' => sfConfig::get('sf_upload_dir').'/products',
        'required' => false,
      )));

      $this->setWidget('filename', new sfWidgetFormInputFileEditable(array(
        'file_src'    => '/uploads/products/'.$this->getObject()->filename,
        'edit_mode'   => !$this->isNew(),
        'is_image'    => true,
        'with_delete' => false,
      )));

      $this->validatorSchema['caption']->setOption('required', false);
    }

Les Evénements de Formulaire
----------------------------

Une autre nouveauté de symfony 1.3 sont les évènements de formulaires qui peuvent être utilisés pour étendre n'importe quel objet de formulaire de n'importe où dans le code. Symfony propose les quatre évènements de formulaire suivants par défaut.

 * `form.post_configure` est notifié après chaque configuration de formulaire ;
 * `form.filter_values` filtre les paramètres fusionnés teintés et les tableaux de fichiers juste avant l'association avec le formulaire ;
 * `form.validation_error` est notifié dès que la validation du formulaire échoue ;
 * `form.method_not_found` est notifié dès qu'une méthode inconnue est appelée.

### Enregistrement d'Erreurs Personnalisées via `form.validation_error`

En utilisant les évènements de formulaires, il est possible d'ajouter des logs personnalisés pour les erreurs de validation sur tous les formulaires du projet. Ces outils peuvent s'avérer utiles pour identifier les champs des formulaires qui entraînent des conflits pour les utilisateurs.

Pour ce faire, il convient d'enregistrer un nouvel écouteur à partir de l'expéditeur d'événements, event dispatcher, pour l'événement `form.validation_error` en ajoutant le code suivant à la méthode `setup()` de la classe `ProjectConfiguration`. Cette dernière se trouve à l'intérieur du répertoire `config/` du projet.

    [php]
    public function setup()
    {
      // ...

      $this->getEventDispatcher()->connect(
        'form.validation_error',
        array('BaseForm', 'listenToValidationError')
      );
    }

La classe `BaseForm`, qui se trouve dans le répertoire `lib/form`, est une classe spéciale de formulaires dont toutes les autres classes de formulaire héritent. `BaseForm` est essentiellement une classe utilitaire servant à partager du code et de la logique métier communs à tous les objets de formulaire du projet. Pour activer le log des erreurs de validation, il suffit simplement d'ajouter le code suivant à la classe `BaseForm`.

    [php]
    public static function listenToValidationError($event)
    {
      foreach ($event['error'] as $key => $error)
      {
        self::getEventDispatcher()->notify(new sfEvent(
          $event->getSubject(),
          'application.log',
          array (
            'priority' => sfLogger::NOTICE,
            sprintf('Validation Error: %s: %s', $key, (string) $error)
          )
        ));
      }
    }

![Enregistrement des erreurs de validation](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_05.png "Barre de débogage avec les erreurs de validation")

Styles Personnalisés des Erreurs d'un Champ de Formulaire
---------------------------------------------------------

En guise de dernier exercice, il s'agit d'aborder un sujet légèrement plus sobre  concernant la personnalisation des éléments du formulaire. C'est tout à fait le cas, par exemple, lorsqu'il s'agit d'appliquer un style spécial au design de la  page `Product` pour tous les champs du formulaire dont la validation a échoué.

![Formulaire généré avec des  erreurs](http://www.symfony-project.org/images/more-with-symfony/advanced_forms_02.png "Formulaire de produit avec un style d'erreur personnalisé")

Si l'on admet que le designer a déjà implémenté les feuilles de styles qui permettent d'appliquer un style d'erreur personnalisé à n'importe quel champ `input` dans une `div` avec la classe `form_error_row`. Comment ajouter simplement la classe `form_row_error` aux champs erronés ?

La réponse se trouve dans un objet spécial appelé *form schema formatter*. Chaque formulaire symfony utilise un *form schema formatter* pour déterminer le code HTML adéquat à utiliser lors de l'affichage des éléments du formulaire. Par défaut, symfony utilise un formateur de formulaire qui s'appuie sur les balises HTML `<table>`.

Tout d'abord, il s'agit de créer une nouvelle classe de formatage de formulaire qui utilise juste quelques balises pour l'affichage du formulaire. Pour ce faire, il convient de créer un nouveau fichier `sfWidgetFormSchemaFormatterAc2009.class.php` dans le répertoire `lib/widget/`. La création de ce dernier est à la charge du lecteur.

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        $errorRowFormat  = "<div>%errors%</div>",
        $helpFormat      = '<div class="form_help">%help%</div>',
        $decoratorFormat = "<div>\n  %content%</div>";
    }

Bien que le format de cette classe paraisse étrange, l'idée générale est que la méthode `renderRow()` fasse usage de la variable `$rowFormat` afin de procéder à l'affichage. Une classe de formatage de formulaire offre d'autres options de formatage qui ne sont pas détaillées ici. Pour plus d'informations à ce sujet, [l'API de symfony 1.3](http://www.symfony-project.org/api/1_3/sfWidgetFormSchemaFormatter) est disponible.

Ajouter le code suivant à la classe `ProjectConfiguration` suffit à utiliser le nouveau formateur de formulaires dans tous les objets de formulaire du projet.

    [php]
    class ProjectConfiguration extends sfProjectConfiguration
    {
      public function setup()
      {
        // ...

        sfWidgetFormSchema::setDefaultFormFormatterName('ac2009');
      }
    }

L'objectif est ici d'attribuer une classe `form_row_error` à l'élément `div` `form_row` seulement si un champ échoue à la validation. Pour ce faire, il suffit d'inclure un jeton `%row_class%` à la propriété `$rowFormat`, puis de  surcharger la méthode ~`sfWidgetFormSchemaFormatter::formatRow()`~ comme suit. 

    [php]
    class sfWidgetFormSchemaFormatterAc2009 extends sfWidgetFormSchemaFormatter
    {
      protected
        $rowFormat       = "<div class=\"form_row%row_class%\">
                            %label% \n %error% <br/> %field%
                            %help% %hidden_fields%\n</div>\n",
        // ...

      public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null)
      {
        $row = parent::formatRow(
          $label,
          $field,
          $errors,
          $help,
          $hiddenFields
        );

        return strtr($row, array(
          '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
        ));
      }
    }

Avec ce code, chaque élément affiché via la méthode `renderRow()` sera automatiquement décoré d'une balise `div` avec une classe `form_row_error` si la validation du champ échoue.

Conclusion
----------

Le framework de formulaires est à la fois le composant le plus puissant et le plus complexe de symfony. Le compromis pour une validation minutieuse, une protection CSRF, et les objets de formulaire peut très vite s'avérer être une tâche redoutable lorsqu'il s'agit d'étendre le framework.

En revanche, la connaissance en profondeur du système de formulaires est la clé pour révéler tout son potentiel. Les développements futurs du framework de formulaires se focaliseront sur la conservation de la puissance de cet outil, et sur la réduction de la complexité en offrant plus de flexibilité au développeur. Le framework de formulaires n'en ait finalement qu'à ses débuts...