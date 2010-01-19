Widgets et Validateurs Personnalisés
====================================

*Par Thomas Rabaix*

Ce chapitre explique comment construire des widgets et validateurs personnalisés à utiliser dans le framework de formulaires. Il présentera les entrailles des classes `sfWidgetForm` et `sfValidator`, ainsi que la manière de créer des widgets simples et complexes.

Au Coeur des Widgets et Validateurs
-----------------------------------

### Présentation de la Classe `sfWidgetForm`

Un objet de la classe ~`sfWidgetForm`~ représente une implémentation visuelle de la manière dont les données relatives seront éditées. Une chaîne de caractères par exemple pourrait être éditée à partir d'un champ texte ou bien à l'aide d'un éditeur WYSIWIG avancé. Afin d'être entièrement configurable, la classe `sfWidgetForm` dispose de deux propriétés importantes : les `options` et les attributs (`attributes`).

 * Les `options` sont utilisées pour configurer le widget. Une option peut servir par exemple à recevoir une requête de base de données à utiliser afin d'alimenter une liste déroulante.
 
 * Les attributs (`attributes`) sont les attributs HTML ajoutés à l'élément lors du rendu.

De plus, la classe `sfWidgetForm` implémente deux méthodes importantes :

 * La méthode `configure()` définit les options *optionnelles* et celles qui sont *obligatoires*. Alors que la rédéfinition du constructeur ne constitue pas une bonne pratique en soit, la méthode `configure()` peut quant à elle être redéfinie en toute sécurité.
 
 * La méthode `render()` génère la sortie HTML du widget. Elle requiert un premier paramètre obligatoire, le nom du widget, et un second paramètre optionnel, sa valeur.

>**NOTE**
>Un objet `sfWidgetForm` ne sait absolument rien de son nom ou de sa valeur. Le 
>composant est seulement responsable du rendu du widget. Le nom et la valeur 
>sont gérés par un objet `sfFormFieldSchema` qui fait le lien entre les données 
>et les widgets.

### Présentation de la Classe `sfValidatorBase`

La classe ~`sfValidatorBase`~ est la classe de base pour tous les validateurs. Sa méthode ~`sfValidatorBase::clean()`~ est la plus importante dans la mesure où elle vérifie si la valeur est valide en fonction des options fournies.

A l'intérieur, la méthode `clean()` exécute plusieurs actions différentes :

 * supprimer les espaces blancs en début et fin de chaînes de caractères saisies, à condition que l'option `trim` soit spécifiée,
 * vérifier si la valeur est vide,
 * appeler la méthode `doClean()` du validateur.

La méthode `doClean()` est celle qui implémente la logique de validation principale. Ce n'est pas une bonne pratique de redéfinir la méthode `clean()`. En revanche, c'est toujours dans la méthode `doClean()` que doit être réalisée la logique de validation personnalisée.

Un validateur peut aussi être utilisé comme un composant indépendant pour vérifier l'intégrité d'une entrée. Par exemple, le validateur `sfValidatorEmail` contrôlera si l'email est valide ou non.

    [php]
    $v = new sfValidatorEmail();

    try
    {
      $v->clean($request->getParameter("email"));
    }
    catch(sfValidatorError $e)
    {
      $this->forward404();
    }

>**NOTE**
>Lorsqu'un formulaire est associé aux valeurs transmises dans la requête, 
>l'objet `sfForm` conserve les références aux valeurs teintées originales ainsi 
>qu'aux valeurs filtrées. Les valeurs originales sont utilisées lorsque le 
>formulaire est réaffiché, alors que les valeurs nettoyées sont employées par 
>l'application (par exemple pour hydrater et sauvegarder l'objet).

### L'Attribut `options`

Les deux objets `sfWidgetForm` et `sfValidatorBase` ont une variété d'options dont certaines sont optionnelles et d'autres obligatoires. Ces options sont définies à l'intérieur de chaque méthode `configure()` de chaque classe grâce aux méthodes suivantes.

 * `addOption($name, $value)` définit une option avec un nom et une valeur par défaut,
 * `addRequiredOption($name)` définit une option obligatoire.

Ces deux méthodes sont très utiles dans la mesure où elles s'assurent que les valeurs respectives sont correctement transmises au validateur ou au widget.

Construire un Widget et un Validateur Simples
---------------------------------------------

Cette section décrit comment construire un widget simple. Ce widget particulier sera intitulé widget "Trilean", et affichera une liste déroulante, de type `select`, composée de trois choix possibles : `No`, `Yes` et `Null`.

    [php]
    class sfWidgetFormTrilean extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
      {

        $this->addOption('choices', array(
          0 => 'No',
          1 => 'Yes',
          'null' => 'Null'
        ));
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        $value = $value === null ? 'null' : $value;

        $options = array();
        foreach ($this->getOption('choices') as $key => $option)
        {
          $attributes = array('value' => self::escapeOnce($key));
          if ($key == $value)
          {
            $attributes['selected'] = 'selected';
          }

          $options[] = $this->renderContentTag(
            'option',
            self::escapeOnce($option),
            $attributes
          );
        }

        return $this->renderContentTag(
          'select',
          "\n".implode("\n", $options)."\n",
          array_merge(array('name' => $name), $attributes
        ));
      }
    }

La méthode `configure()` définit la liste des valeurs des options grâce à l'option `choices`. Ce tableau peut être redéfini afin de modifier le label associé de chaque valeur par exemple. Il n'y a pas de limite au nombre d'options qu'un widget peut déclarer. Néanmoins, la classe de base des widgets déclare quelques options standards qui sont donc des options réservées de-facto.

 * `id_format` : le format des identifiants, `%s` par défaut ;

 * `is_hidden` : valeur booléenne qui définit si le widget est un champ caché,  utilisée notamment par la méthode `sfForm::renderHiddenFields()` pour générer tous les champs cachés en une seule fois ;

 * `needs_multipart`: valeur booléenne qui détermine si la balise du formulaire doit inclure l'option `multipart`. Cette option est nécessaire lorsqu'il s'agit de réaliser des envois de fichiers ;

 * `default`: la valeur par défaut qui doit être utilisée pour rendre le widget si aucune valeur n'a été fournie ;

 * `label`: le label par défaut du widget.

La méthode `render()` génère le HTML correspondant pour une liste déroulante `select`. Elle appelle la méthode interne `renderContentTag()` qui facilite la génération de balises HTML. Le widget est désormais complet et son validateur associé peut quant à lui être défini dès à présent.

    [php]
    class sfValidatorTrilean extends sfValidatorBase
    {
      protected function configure($options = array(), $messages = array())
      {
        $this->addOption('true_values', array('true', 't', 'yes', 'y', 'on', '1'));
        $this->addOption('false_values', array('false', 'f', 'no', 'n', 'off', '0'));
        $this->addOption('null_values', array('null', null));
      }

      protected function doClean($value)
      {
        if (in_array($value, $this->getOption('true_values')))
        {
          return true;
        }

        if (in_array($value, $this->getOption('false_values')))
        {
          return false;
        }

        if (in_array($value, $this->getOption('null_values')))
        {
          return null;
        }

        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }

      public function isEmpty($value)
      {
        return false;
      }
    }

Le validateur `sfValidatorTrilean` définit trois options dans sa méthode `configure()`. Chaque option est un jeu de valeurs possibles. Comme elles sont définies comme des options, le développeur a la possibilité de personnaliser les valeurs en fonction des spécifications techniques.

La méthode `doClean()` vérifie si la valeur fournie correspond à une valeur valide, puis retourne la bonne valeur filtrée parmi les trois. Si aucune valeur ne correspond alors la méthode lance une exception `sfValidatorError` qui correspond à la classe standard pour toute les exceptions de validation.

La dernière méthode, `isEmpty()`, est écrasée car le comportement par défaut est de retourner `true` si `null` est fourni en entrée. Comme le widget en cours autorise la valeur `null`, cette méthode doit toujours retourner `false` dans ce cas. 

>**Note**:
>Si `isEmpty()` retourne `true`, la méthode `doClean()` du validateur n'est 
>jamais appelée.

Ce premier widget était très simple à réaliser, cependant, il a introduit certaines bases importantes pour la suite. 

Le Widget Google Address Map
----------------------------

Dans cette partie, il s'agit d'expliquer comment créer un widget beaucoup plus complexe avec plusieurs champs et avec des interactions JavaScript. Le widget s'appellera "GMAW" pour "Google Map Address Widget".

Quel est le but de ce composant ? Le widget doit fournir une méthode simple pour que l'utilisateur final puisse ajouter une adresse en utilisant un champ texte et une carte du service "Google Map".

!["Google Map Address Widget" mashup](http://www.symfony-project.org/images/more-with-symfony/widgets-figure-01.png "Google Map Address Widget" mashup")

### Cas d'utilisation 1 :

 * L'utilisateur saisit une adresse.
 * L'utilisateur clique sur le bouton "rechercher".
 * Les champs cachés `longitude` et `latitude` sont mis à jour et un marqueur est ajouté sur la carte. Le marqueur est positionné sur la localisation de l'adresse. Si le service de géo-positionnement ne peut trouver cette adresse alors un message d'erreur doit apparaître.

### Cas d'utilisation 2 :

 * L'utilisateur clique sur la carte
 * La latitude et la longitude sont mises à jour.
 * Une demande d'adresse est envoyée au service de géo-positionnement.
 
*Les champs suivants ont besoin d'être envoyés et gérés par le formulaire* :

 * `latitude` : nombre à virgule flottante, entre 90 et -90 ;
 * `longitude` : nombre à virgule flottante, entre 180 et -180 ;
 * `address` : chaîne de caractères, texte seulement.

Les spécifications fonctionnelles sont maintenant définies, et voici la liste des éléments techniques utilisés dans la suite de ce chapitre :

 * Services Google Maps et Geocoding : affichent la carte et récupèrent les 
 informations d'une adresse,
 * jQuery : gère les interactions javascript entre le formulaire et la carte,
 * sfForm : rend la carte et les champs textes.

### Le Widget `sfWidgetFormGMapAddress`

Comme indiqué précédemment, un widget est seulement la représentation visuelle des données à éditer, la méthode `configure()` de ce widget doit avoir toutes les options nécessaires afin de configurer la carte Google ou bien pour modifier les styles de chaque élément.

L'option la plus importante est ici l'option `template.html` qui définit comment les éléments sont ordonnés. Lors de la création d'un widget il est très important de prévoir de la souplesse afin de réutiliser le composant sur d'autres pages.

Un autre point important à noter est la définition des médias externes utilisés par le widget. Là encore, le framework de formulaires permet d'implémenter deux méthodes :

 * `getJavascripts()` : retourne un tableau de fichiers JavaScript,
 
 * `getStylesheets()` : retourne un tableau de feuilles de style où la clé du tableau est le chemin du fichier et la valeur le type de media.

Pour fonctionner correctement, le widget a besoin de code JavaScript dans le but de gérer les interactions entre la carte Google et les champs du widget. Le widget doit donc seulement implémenter la méthode `getJavascript()`.

Le widget n'est pas responsable du chargement des services Google. Il est en effet de la responsabilité du développeur d'insérer les bonnes informations relatives à l'API Google. Le widget n'est pas forcément le seul élément sur une page à utiliser ces services, il faut donc découpler cette fonction du widget.

    [php]
    class sfWidgetFormGMapAddress extends sfWidgetForm
    {
      public function configure($options = array(), $attributes = array())
        {
        $this->addOption('address.options', array('style' => 'width:400px'));

        $this->setOption('default', array(
          'address' => '',
          'longitude' => '2.294359',
          'latitude' => '48.858205'
        ));

        $this->addOption('div.class', 'sf-gmap-widget');
        $this->addOption('map.height', '300px');
        $this->addOption('map.width', '500px');
        $this->addOption('map.style', "");
        $this->addOption('lookup.name', "Lookup");

        $this->addOption('template.html', '
          <div id="{div.id}" class="{div.class}">
            {input.search} <input type="submit" value="{input.lookup.name}"  id="{input.lookup.id}" /> <br />
            {input.longitude}
            {input.latitude}
            <div id="{map.id}" style="width:{map.width};height:{map.height};{map.style}"></div>
          </div>
        ');

         $this->addOption('template.javascript', '
          <script type="text/javascript">
            jQuery(window).bind("load", function() {
              new sfGmapWidgetWidget({
                longitude: "{input.longitude.id}",
                latitude: "{input.latitude.id}",
                address: "{input.address.id}",
                lookup: "{input.lookup.id}",
                map: "{map.id}"
              });
            })
          </script>
        ');
      }

      public function getJavascripts()
      {
        return array(
          '/sfFormExtraPlugin/js/sf_widget_gmap_address.js'
        );
      }

      public function render($name, $value = null, $attributes = array(), $errors = array())
      {
        // define main template variables
        $template_vars = array(
          '{div.id}'             => $this->generateId($name),
          '{div.class}'          => $this->getOption('div.class'),
          '{map.id}'             => $this->generateId($name.'[map]'),
          '{map.style}'          => $this->getOption('map.style'),
          '{map.height}'         => $this->getOption('map.height'),
          '{map.width}'          => $this->getOption('map.width'),
          '{input.lookup.id}'    => $this->generateId($name.'[lookup]'),
          '{input.lookup.name}'  => $this->getOption('lookup.name'),
          '{input.address.id}'   => $this->generateId($name.'[address]'),
          '{input.latitude.id}'  => $this->generateId($name.'[latitude]'),
          '{input.longitude.id}' => $this->generateId($name.'[longitude]'),
        );

        // vérifie si la valeur est valide
        $value = !is_array($value) ? array() : $value;
        $value['address']   = isset($value['address'])   ? $value['address'] : '';
        $value['longitude'] = isset($value['longitude']) ? $value['longitude'] : '';
        $value['latitude']  = isset($value['latitude'])  ? $value['latitude'] : '';

        // définit le widget pour le champ adresse
        $address = new sfWidgetFormInputText(array(), $this->getOption('address.options'));
        $template_vars['{input.search}'] = $address->render($name.'[address]', $value['address']);

        // définit les widgets pour les champs : longitude et latitude
        $hidden = new sfWidgetFormInputHidden;
        $template_vars['{input.longitude}'] = $hidden->render($name.'[longitude]', $value['longitude']);
        $template_vars['{input.latitude}']  = $hidden->render($name.'[latitude]', $value['latitude']);

        // assemble le modèle avec les valeurs
        return strtr(
          $this->getOption('template.html').$this->getOption('template.javascript'),
          $template_vars
        );
      }
    }

Le widget fait appel à la méthode `generateId()` pour générer le champ `id` de chaque élément. La variable `$name` est fournie par la classe `sfFormFieldSchema`. Par conséquent, le nom du widget est composé du nom du formulaire, des schémas de widgets imbriqués et finalement du nom du champ défini par la méthode `configure()`.

>**NOTE**
>Par exemple, si le nom du formulaire est `user`, le widget schéma imbriqué (via 
>un formulaire imbriqué) est `location`, et le nom du champ est `address`. Alors 
>le nom final du champ sera `user[location][address]` et l'`id` sera 
>`user_location_address`. Pour résumer, `$this->generateId($name.'[latitude]')` 
>générera un `id` valide et unique pour le champ `latitude`.

Les différentes valeurs des identifiants sont importantes car elles sont passées au bloc JavaScript par l'intermédiaire de la variable `template.js` afin que le  Javascript puisse gérer les différents éléments du widget.

La méthode `render()` initialise deux widgets : `sfWidgetFormInputText` pour le champ texte de l'adresse et `sfWidgetFormInputHidden` pour les champs cachés longitude et latitude. Le widget peut ainsi être rapidement testé à l'aide du code ci-dessous.

    [php]
    $widget = new sfWidgetFormGMapAddress();
    echo $widget->render('user[location][address]', array(
      'address' => '151 Rue montmartre, 75002 Paris',
      'longitude' => '2.294359',
      'latitude' => '48.858205'
    ));

Le résultat obtenu est le suivant :

    [html]
    <div id="user_location_address" class="sf-gmap-widget">
      <input style="width:400px" type="text" name="user[location][address][address]" value="151 Rue montmartre, 75002 Paris" id="user_location_address_address" />
      <input type="submit" value="Lookup"  id="user_location_address_lookup" /> <br />
      <input type="hidden" name="user[location][address][longitude]" value="2.294359" id="user_location_address_longitude" />
      <input type="hidden" name="user[location][address][latitude]" value="48.858205" id="user_location_address_latitude" />
      <div id="user_location_address_map" style="width:500px;height:300px;"></div>
    </div>

    <script type="text/javascript">
      jQuery(window).bind("load", function() {
        new sfGmapWidgetWidget({
          longitude: "user_location_address_longitude",
          latitude: "user_location_address_latitude",
          address: "user_location_address_address",
          lookup: "user_location_address_lookup",
          map: "user_location_address_map"
        });
      })
    </script>

La partie JavaScript du widget utilise les différents attributs `id` et les attache avec jQuery à des écouteurs, `listeners`, qui seront activés à l'occasion de différentes interactions utilisateur. Le JavaScript met à jour les différents champs tels que les valeurs de la longitude et de la latitude ainsi que l'adresse si l'utilisateur clique dessus. L'objet JavaScript dispose de trois méthodes intéressantes :

 * `init()` initialise les variables et les événements ;

 * `lookupCallback()`, méthode *statique* utilisée pour trouver la 
 longitude et la latitude en fonction de l'adresse ;

 * `reverseLookupCallback()`, méthode *statique* utilisée pour 
 retrouver l'adresse à partir de la longitude et de la latitude.

Le code JavaScript se trouve dans l'Annexe A. Toutes les informations complémentaires sur le fonctionnement des services Google peuvent être trouvées sur le site officiel de Google Maps [API](http://code.google.com/apis/maps/).

### Le Validateur `sfValidatorGMapAddress`

Le validateur doit vérifier plusieurs points importants. Pour rappel, il est impossible de faire confiance aux valeurs fournies par un utilisateur. C'est pourquoi le validateur doit s'assurer que la valeur du champ est correcte. Cette dernière doit être un tableau associatif contenant une longitude, une latitude et une adresse. C'est pour cette raison que le validateur principal instancie d'autres validateurs auxquels il délègue la validation de chaque élément du tableau.

    [php]
    class sfValidatorGMapAddress extends sfValidatorBase
    {
      protected function doClean($value)
      {
        if (!is_array($value))
        {
          throw new sfValidatorError($this, 'invalid');
        }

        try
        {
          $latitude = new sfValidatorNumber(array( 'min' => -90, 'max' => 90, 'required' => true ));
          $value['latitude'] = $latitude->clean(isset($value['latitude']) ? $value['latitude'] : null);

          $longitude = new sfValidatorNumber(array( 'min' => -180, 'max' => 180, 'required' => true ));
          $value['longitude'] = $longitude->clean(isset($value['longitude']) ? $value['longitude'] : null);

          $address = new sfValidatorString(array( 'min_length' => 10, 'max_length' => 255, 'required' => true ));
          $value['address'] = $address->clean(isset($value['address']) ? $value['address'] : null);
        }
        catch(sfValidatorError $e)
        {
          throw new sfValidatorError($this, 'invalid');
        }

        return $value;
      }
    }

>**NOTE**
>En cas d'erreur, un validateur lance toujours une exception de type 
>`sfValidatorError`. C'est pour cette raison que le code de validation de chaque 
>valeur du tableau est encapsulé dans un bloc `try/catch`. Une exception globale 
>de type `invalid` est ensuite retournée en cas d'erreur sur l'un des champs.

### Test du Validateur

Pourquoi tester ? Le validateur est le lien entre les valeurs saisies par l'utilisateur et l'application. Si le validateur n'est pas fiable, alors l'application est vulnérable. Heureusement, symfony est livré avec une librairie de test très facile à utiliser.

Comment peut-on tester un validateur ? Comme expliqué plus haut, un validateur jette une exception en cas d'erreur. Le test doit donc injecter des valeurs correctes et invalides au validateur et vérifier la présence de l'exception.

    [php]
    $t = new lime_test(7, new lime_output_color());

    $tests = array(
      array(false, '', 'empty value'),
      array(false, 'string value', 'string value'),
      array(false, array(), 'empty array'),
      array(false, array('address' => 'my awesome address'), 'incomplete address'),
      array(false, array('address' => 'my awesome address', 'latitude' => 'String', 'longitude' => 23), 'invalid values'),
      array(false, array('address' => 'my awesome address', 'latitude' => 200, 'longitude' => 23), 'invalid values'),
      array(true, array('address' => 'my awesome address', 'latitude' => '2.294359', 'longitude' => '48.858205'), 'valid value')
    );

    $v = new sfValidatorGMapAddress();

    $t->diag("Testing sfValidatorGMapAddress");

    foreach($tests as $test)
    {
      list($validity, $value, $message) = $test;

      try
      {
        $v->clean($value);
        $catched = false;
      }
      catch(sfValidatorError $e)
      {
        $catched = true;
      }

      $t->ok($validity != $catched, '::clean() '.$message);
    }

Quand la méthode `sfForm::bind()` est appelée, la méthode `clean()` de chaque validateur est alors invoquée. Par conséquent, il est facile de reproduire ce comportement en instanciant directement le validateur `sfValidatorGMapAddress`.

Conclusion
----------

L'erreur la plus courante lors de la création d'un widget réside dans le fait  d'être trop concentré sur la manière dont sont stockées les valeurs en base de données.

Cependant le framework de formulaires se comporte à la fois comme un conteneur et un validateur de données. Par conséquent, un widget doit seulement gérer ces informations. Si les données sont valides alors les différentes valeurs peuvent être utilisées dans un modèle de données ou dans le contrôleur.