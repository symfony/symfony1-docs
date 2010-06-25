Développer pour Facebook
========================

*Par Fabrice Bernhard*

Facebook, avec plus de 350 millions de membres aujourd'hui, est devenu le réseau social de référence sur Internet. L'une de ses principales qualités est la mise à disposition de la plate-forme Facebook, une API qui permet aux développeurs de créer des applications directement dans le site Facebook mais aussi de connecter d'autres sites Internet avec le système d'authentification et le graphe social de Facebook.

Comme le site Facebook est écrit en PHP, il n'est pas étonnant que la librairie client officielle qui permette d'utiliser l'API soit aussi développée en PHP. Cela fait de facto de symfony un choix logique pour développer rapidement et proprement des applications Facebook ou bien des sites Facebook Connect. Mais les nombreuses fonctionnalités de symfony permettent de s'adapter particulièrement bien à la programmation pour Facebook et de gagner ainsi en temps et en qualité.

Ce chapitre couvre les spécificités de la programmation Facebook avec symfony. Après un rapide résumé de l'API Facebook et de son utilisation, les sections suivantes expliqueront comment utiliser symfony au mieux en développant pour Facebook et comment bénéficier des contributions de la communauté au travers du plugin `sfFacebookConnectPlugin`.

Il s'agira d'illustrer les notions acquises au travers d'une simple application "Hello you!", avant de dévoiler quelques astuces pour résoudre les problèmes les plus courants de développement avec Facebook.

Développer pour Facebook
------------------------

Bien que l'API soit globalement la même, il existe deux cas d'utilisation très différents. Le premier concerne la création d'une application Facebook dans le site Facebook.com tandis que la seconde consiste à implémenter Facebook Connect sur un site web externe.

### Les Applications Facebook

Les applications Facebook sont des applications web contenues dans Facebook. Leur principal atout réside dans le fait qu'elles sont directement incluses dans le site communautaire Facebook et son réseau social de plus de 300 millions de personnes, permettant ainsi à n'importe quelle application virale de se propager à une vitesse incroyable.

Farmville est l'exemple le plus récent et le plus impressionnant, avec plus de 60 millions d'utilisateurs actifs chaque mois et plus de 2 millions de fans convertis en quelques mois ! C'est l'équivalent de la population française qui revient chaque mois sur cette application pour travailler sur leur ferme virtuelle.

Les applications Facebook interagissent avec le site Facebook et son réseau social sous différentes formes. Voici un bref aperçu des endroits où l'application Facebook pourra apparaître.

#### Le Canevas (Canvas)

Le canevas est habituellement la partie principale de l'application. C'est un petit site intégré dans le cadre du site Facebook.

#### L'Onglet de Profil (Profile Tab)

L'application peut aussi être contenue dans un onglet du profil d'un utilisateur ou d'une page de fan. Les principales contraintes sont alors les suivantes :

 * une page seulement. Il est impossible de définir des liens directs vers d'éventuelles sous-pages de l'onglet.
 
 * pas d'interaction dynamique au démarrage, que ce soit flash ou JavaScript. Pour proposer des fonctionnalités dynamiques, l'application doit attendre une interaction de l'utilisateur, un clic sur un lien ou un bouton par exemple.

#### La Boîte de Profil (Profile Box)

C'est un reste de l'ancienne version de Facebook, qui est devenue quelque peu obsolète. La boîte de profil permet d'afficher quelques informations dans un widget rectangulaire que l'on retrouve dans l'onglet "Boîtes" du profil d'un utilisateur.

#### L'Onglet Informations

Certaines informations statiques relatives à l'utilisateur et l'application peuvent être affichées dans l'onglet "Informations" du profil. Ces informations apparaitront juste sous l'âge de l'utilisateur, son adresse et son court CV.

#### Publication dans le Flux d'Actualités

L'application peut publier des actualités, liens, photos, vidéos dans le flux d'actualités, sur le mur d'un ami ou bien directement modifier le statut de l'utilisateur.

#### La Page d'Informations

C'est la page de profil de l'application, créée automatiquement par Facebook. C'est ici que le créateur de l'application peut interagir avec ses utilisateurs. Cette page concerne plus particulièrement l'équipe marketing que l'équipe de développement.

### Facebook Connect

Facebook Connect permet à n'importe quel site web d'apporter quelques-unes des puissantes fonctionnalités de Facebook à ses propres utilisateurs. Les sites
web qui en bénéficient se reconnaissent déjà à la présence d'un gros bouton bleu "Connect with Facebook". Parmi les plus connus : digg.com, cnet.com, netvibes.com, yelp.com, etc.

La section suivante donne la liste des quatre principales raisons d'implémenter Facebook Connect sur un site existant.

#### Système d'Authentification en un Clic

Tout comme OpenID, Facebook Connect permet aux sites web de proposer à ses utilisateurs une connexion automatique à partir de leur session Facebook. Une fois la "connexion" entre le site web et Facebook approuvée par l'utilisateur, la session Facebook est automatiquement transmise au site, permettant d'épargner à l'utilisateur une énième procédure d'enregistrement et de mot de passe à mémoriser.

#### Obtenir plus d'Informations sur l'Utilisateur

Une autre fonctionnalité clef de Facebook Connect est la quantité d'informations apportées. Alors qu'un utilisateur ne délivrera habituellement qu'un minimum d'informations le concernant sur un nouveau site, Facebook Connect permet quant à lui d'obtenir facilement des informations additionnelles telles que l'âge, le sexe, la localisation, la photo de profil, etc. enrichissant d'autant plus le site.

Les conditions d'utilisation de Facebook Connect rappellent clairement que la conservation des informations personnelles est interdite sans l'accord explicite de l'utilisateur. Mais l'information disponible peut être utilisée pour pré-remplir des formulaires et demander confirmation en un clic. Le site web peut aussi se contenter des informations publiques telles que le prénom et le nom sans avoir besoin de les enregistrer.

#### Communication Virale grâce au Flux d'Actualités

La possibilité d'interagir avec le flux d'actualités de l'utilisateur, d'inviter des amis ou de bien de publier sur le mur d'un ami permet au site web d'utiliser pleinement le potentiel viral de Facebook pour communiquer.

En effet, n'importe quel site avec une composante communautaire peut ainsi véritablement bénéficier de cette fonctionnalité, tant que l'information publiée sur Facebook a un intérêt social qui peut intéresser des amis et des amis d'amis.

#### Tirer Parti du Graphe Social Existant

Pour un site web dont le service dépend d'un graphe social (un réseau d'amis ou de connaissances), le coût pour démarrer une première communauté, avec suffisamment de liens entre les utilisateurs pour leur permettre d'interagir et de bénéficier du service, est colossal.

En donnant un accès facile à la liste des amis Facebook d'un utilisateur, Facebook Connect réduit considérablement ce coût, en évitant à l'utilisateur de devoir chercher ses "amis déjà enregistrés".

Configurer un Premier Projet avec le Plugin `sfFacebookConnectPlugin`
---------------------------------------------------------------------

### Créer l'Application sur Facebook

Pour commencer, un compte Facebook est nécessaire, avec l'application 
["Developer"](http://www.facebook.com/developers) installée. Pour créer l'application, la seule information nécessaire dans un premier temps est le nom de l'application.

### Installer et Configurer `sfFacebookConnectPlugin`

La prochaine étape est de lier les utilisateurs Facebook avec les utilisateurs `sfGuard`. C'est la principale fonction du `sfFacebookConnectPlugin`, que j'ai créé et auquel d'autres développeurs symfony ont rapidement contribué. Une fois ce plugin installé, il y a une étape de configuration simple mais nécessaire. Les paramètres `API key`, `application secret`, et `application ID` doivent être précisés dans le fichier `app.yml` de l'application symfony.

    [yml]
    # default values
    all:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx
        redirect_after_connect: false
        redirect_after_connect_url: ''
        connect_signin_url: 'sfFacebookConnectAuth/signin'
        app_url: '/my-app'
        guard_adapter: ~
        js_framework: none # none, jQuery or prototype.

      sf_guard_plugin:
        profile_class: sfGuardUserProfile
        profile_field_name: user_id
        profile_facebook_uid_name: facebook_uid # WARNING this column must be of type varchar! 100000398093902 is a valid uid for example!
        profile_email_name: email
        profile_email_hash_name: email_hash

      facebook_connect:
        load_routing:     true
        user_permissions: []

>**TIP**
>Avec les versions de symfony antérieures à 1.2, l'option `load_routing` doit 
>être définie à la valeur `false`, car elle utilise le nouveau système de 
>routing `sfRouting`.

### Configurer une Application Facebook

Si le projet est une application Facebook et pas un site Facebook Connect, le seul autre paramètre important est `app_url` qui précise l'adresse relative de l'application dans Facebook. Par exemple pour l'application `http://apps.facebook.com/my-app` la valeur du paramètre `app_url` sera `/my-app`.

### Configurer un Site Facebook Connect

Si le projet est un site Facebook Connect, les valeurs par défaut des autres paramètres pourront être conservées la plupart du temps :

 * `redirect_after_connect` permet de modifier le comportement du plugin après un clic sur le bouton "Connect with Facebook". Par défaut le plugin reproduit le comportement de `sfGuardPlugin` après la création d'un nouveau compte.

 * `js_framework` permet de préciser l'utilisation d'un framework JavaScript. Il est fortement recommandé d'en utiliser un, tel que jQuery par exemple, sur les sites Facebook Connect. En effet, l'API JavaScript de Facebook est relativement  lourde et peut entraîner des erreurs fatales (!) sur IE6 si le chargement du fichier a lieu en cours de rendu du DOM.

 * `user_permissions` est le tableau des permissions qui seront affectées à un nouvel utilisateur Facebook.

### Connecter sfGuard avec Facebook

Le lien entre un utilisateur Facebook et le système d'authentification de `sfGuardPlugin` est réalisé assez logiquement en utilisant une colonne `facebook_uid` dans la table `Profile`.

Le plugin part du principe que le lien entre l'objet `sfGuardUser` et son profil est obtenu en utilisant la méthode `getProfile()`. C'est le comportement par défaut avec `sfPropelGuardPlugin` mais doit être configuré spécifiquement avec
`sfDoctrineGuardPlugin`. Voici un `schema.yml` type :

Pour Propel :

    [yml]
    sf_guard_user_profile:
      _attributes: { phpName: UserProfile }
      id:
      user_id:            { type: integer, foreignTable: sf_guard_user, foreignReference: id, onDelete: cascade }
      first_name:         { type: varchar, size: 30 }
      last_name:          { type: varchar, size: 30 }
      facebook_uid:       { type: varchar, size: 20 }
      email:              { type: varchar, size: 255 }
      email_hash:         { type: varchar, size: 255 }
      _uniques:
        facebook_uid_index: [facebook_uid]
        email_index:        [email]
        email_hash_index:   [email_hash]

Pour Doctrine :

    [yml]
    sfGuardUserProfile:
      tableName:     sf_guard_user_profile
      columns:
        user_id:          { type: integer(4), notnull: true }
        first_name:       { type: string(30) }
        last_name:        { type: string(30) }
        facebook_uid:     { type: string(20) }
        email:            { type: string(255) }
        email_hash:       { type: string(255) }
      indexes:
        facebook_uid_index:
          fields: [facebook_uid]
          unique: true
        email_index:
          fields: [email]
          unique: true
        email_hash_index:
          fields: [email_hash]
          unique: true
      relations:
        sfGuardUser:
          type: one
          foreignType: one
          class: sfGuardUser
          local: user_id
          foreign: id
          onDelete: cascade
          foreignAlias: Profile

>**TIP**
>Si le projet utilise Doctrine mais que la propriété `foreignAlias` n'est pas 
>définie à `Profile`, alors le plugin ne fonctionnera pas. Mais une simple 
>méthode `getProfile()`dans la classe `sfGuardUser` qui pointe vers la table 
>`Profile` suffit à contourner le problème !

Il faut bien faire attention au type de la colonne `facebook_uid` qui doit être un `varchar` car les nouveaux profils Facebook ont des `uids` supérieurs à `10^15`.

Il convient de rester prudent en utilisant une colonne `varchar` indexée plutôt que de vouloir tenter l'utilisation d'une colonne `bigint` dont le comportement n'est pas toujours le même selon le SGBD ou l'ORM utilisé.

Les deux autres colonnes sont moins importantes : `email` et `email_hash` ne servent que dans le cas où Facebook Connect est utilisé sur un site web qui possède déjà des utilisateurs. Dans ce cas Facebook propose un traitement assez complexe pour tenter d'associer les comptes existants avec les utilisateurs qui essaieraient ensuite Facebook Connect en utilisant un hash de l'email du compte existant. Bien sûr, le processus est simplifié par une tâche fournie dans le plugin `sfFacebookConnectPlugin` décrite plus tard dans ce chapitre.

### Choisir entre FBML et XFBML : Problème Résolu par symfony

Maintenant que tout est correctement configuré, le développement de l'application peut commencer. Facebook propose plusieurs balises spécifiques qui permettent d'afficher de véritables fonctionnalités, comme par exemple un formulaire d'invitation d'amis ou un système entier de commentaires.

Ces balises sont des tags FBML ou XFBML. Les balises FBML et XFBML sont assez similaires, mais le choix entre l'un ou l'autre des formats dépend en fait de l'affichage ou non de l'application par Facebook. Si le projet est un site Facebook Connect, alors il n'y a qu'un choix possible : XFBML. Si c'est une application Facebook, il y a deux choix :

 * Inclure l'application dans Facebook au travers d'une IFrame et utiliser du XFBML dans cette IFrame ;

 * Laisser Facebook se charger du rendu directement dans leur page, et utiliser le FBML.

Facebook encourage les développeurs à utiliser leur "inclusion transparente" ou en d'autres termes le FBML. En effet elle dispose de certains avantages :

 * Pas d'IFrame, qui restent toujours difficiles à gérer, car il faut en permanence se souvenir si le lien concerne l'IFrame ou bien la fenêtre entière ;

 * Les balises FBML sont interprétées directement par le serveur Facebook et permettent ainsi d'afficher des informations privées concernant l'utilisateur sans avoir à communiquer au préalable avec le serveur Facebook. Cela constitue  donc un aller / retour en moins entre les deux serveurs ;

 * Pas besoin de transmettre les informations de session Facebook de page en page.

Mais le FBML a des inconvénients certains :

 * Tout le JavaScript est automatiquement inclus dans une sandbox, rendant impossible toute utilisation de librairie extérieure, comme une Google Maps, jQuery ou tout autre système de statistiques tels que Google Analytics qui est supporté officiellement par Facebook ;

 * Le FBML est censé être plus rapide car certains appels au serveur sont économisés. Cependant si l'application n'est pas particulièrement lourde, l'héberger sur son propre serveur sera beaucoup plus rapide ;

 * C'est plus difficile à déboguer, particulièrement pour les erreurs 500 qui sont attrapées par Facebook et remplacées par une erreur standard.

Donc quel est le choix recommandé ? La bonne nouvelle c'est qu'avec symfony et le plugin `sfFacebookConnectPlugin`, il n'y a pas de choix à faire ! Il est possible d'écrire des applications agnostiques et passer indifféremment de l'IFrame à l'inclusion directe dans Facebook ou au site externe Facebook Connect avec le même code. Ceci est possible car, techniquement, la principale différence entre ces trois environnements réside dans le layout... qui est très facile à interchanger dans symfony. Voici deux exemples de layout :

Le layout d'une application FBML :

    [html]
    <?php sfConfig::set('sf_web_debug', false); ?>
    <fb:title><?php echo sfContext::getInstance()->getResponse()->getTitle() ?></fb:title>
    <?php echo $sf_content ?>

Le layout d'une application XFBML ou d'un site Facebook Connect :

    [html]
    <?php use_helper('sfFacebookConnect')?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
      <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <script type="text/javascript" src="/sfFacebookConnectPlugin/js/animation/animation.js"></script>
      </head>
      <body>
        <?php echo $sf_content ?>
        <?php echo include_facebook_connect_script() ?>
      </body>
    </html>

Pour passer de l'un à l'autre automatiquement, il suffit d'ajouter le code suivant dans le fichier `actions.class.php` :

    [php]
    public function preExecute()
    {
      if (sfFacebook::isInsideFacebook())
      {
        $this->setLayout('layout_fbml');
      }
      else
      {
        $this->setLayout('layout_connect');
      }
    }

>**NOTE**
>Il y a une petite différence entre une balise FBML et XFBML qui ne se trouve
>pas dans le layout : les balises FBML peuvent être fermées, contrairement aux 
>balises XFBML. Il suffit donc de toujours remplacer les tags de la forme :
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400" />
>
>par :
>
>      [html]
>      <fb:profile-pic uid="12345" size="normal" width="400"></fb:profile-pic>

Bien sûr, il faut aussi configurer l'onglet Facebook Connect de l'application dans les paramètres développeur de l'application de Facebook, même si le but final n'est que de faire du FBML. Cependant, l'énorme avantage de faire ceci est de pouvoir tester l'application localement. Si l'application Facebook utilise des balises FBML, ce qui est quasiment inévitable, la seule façon de visualiser le résultat final consisterait logiquement à mettre le code en ligne afin de le tester directement dans Facebook !

Heureusement grâce à Facebook Connect, les balises XFBML peuvent être rendues en dehors du domaine facebook.com. Et, comme les portions de code précédentes l'ont montré, la seule différence entre XFBML et FBML concerne le layout.

Cette solution permet donc de visualiser les balises FBML en local, à condition bien sûr d'être connecté à Internet. De plus, avec un environnement de développement visible sur Internet, tel qu'un serveur web ou un simple ordinateur avec un port 80 ouvert par exemple, même les parties de l'application qui dépendent du système d'authentification de Facebook fonctionneront en dehors du domaine facebook.com. C'est en effet grâce à Facebook Connect une fois de plus. L'application peut donc être entièrement testée avant d'être mise en ligne sur Facebook.

### L'Application de Démonstration "Hello You"

Avec le code suivant dans la vue du module `home`, l'application "Hello You" est terminée :

    [php]
    <?php $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession(); ?>
    Hello <fb:name uid="<?php echo $sfGuardUser ? $sfGuardUser->getProfile()->getFacebookUid() : '' ?>"></fb:name>

`sfFacebookConnectPlugin` convertit automatiquement le visiteur Facebook
en un utilisateur `sfGuard`. Cela permet une intégration très facile avec du code symfony existant qui repose sur `sfGuardPlugin`.

Facebook Connect
----------------

### Comment Facebook Connect fonctionne et Différentes Stratégies d'Intégration

Pour faire simple, Facebook Connect partage sa session avec celle du site. Cette opération est réalisée au travers de la copie du cookie d'authentification de Facebook vers le site web en ouvrant successivement une IFrame dans le site qui pointe vers une page Facebook. Cette même page Facebook ouvre elle aussi une IFrame qui pointe vers le site.

Pour ce faire, Facebook Connect a besoin d'avoir accès au site ce qui empêche d'utiliser ou de tester l'authentification Facebook Connect en local. Le point d'entrée sur le site est le fichier `xd_receiver.htm` que `sfFacebookConnectPlugin` installe automatiquement. Bien sûr il ne faut pas oublier d'utiliser la commande `symfony plugin:publish-assets` pour rendre le fichier accessible publiquement.

Lorsque cette opération est terminée, la librairie officielle de Facebook permet d'utiliser la session Facebook. Le plugin `sfFacebookConnectPlugin` crée en plus un utilisateur `sfGuard` lié à cette session Facebook, qui s'intègre donc sans souci avec le site web existant. C'est pour cette raison que le plugin redirige automatiquement l'utilisateur vers l'action `sfFacebookConnectAuth/signIn` par défaut, une fois le bouton Facebook Connect cliqué et la connexion validée.

Le plugin cherche d'abord si un utilisateur existe avec le même UID Facebook ou bien le même hash d'email (voir la section "Connecter les Utilisateurs Existants avec leur Compte Facebook" à la fin de ce chapitre). Si aucun n'est trouvé, un utilisateur vierge est alors créé.

Une autre stratégie d'intégration classique consiste à ne pas créer l'utilisateur directement, mais de le rediriger d'abord vers un formulaire d'enregistrement spécifique. De là, il est éventuellement possible d'utiliser la session Facebook afin de pré-remplir des informations, par exemple, en ajoutant la fonction suivante dans le formulaire d'enregistrement :

    [php]
    public function setDefaultsFromFacebookSession()
    {
      if ($fb_uid = sfFacebook::getAnyFacebookUid())
      {
        $ret = sfFacebook::getFacebookApi()->users_getInfo(
          array(
            $fb_uid
          ),
          array(
            'first_name',
            'last_name',
          )
        );

        if ($ret && count($ret)>0)
        {
          if (array_key_exists('first_name', $ret[0]))
          {
            $this->setDefault('first_name',$ret[0]['first_name']);
          }

          if (array_key_exists('last_name', $ret[0]))
          {
            $this->setDefault('last_name',$ret[0]['last_name']);
          }
        }
      }

Pour utiliser cette deuxième stratégie, souvent recommandée, il suffit de spécifier deux paramètres dans le fichier `app.yml`. Le premier paramètre, `redirect_after_connect` indique qu'il faille rediriger après la connexion Facebook Connect tandis que le second, `redirect_after_connect_url` précise la route à utiliser pour réaliser cette redirection.

    [yml]
    # default values
    all:
      facebook:
        redirect_after_connect: true
        redirect_after_connect_url: '@register_with_facebook'

### Le Filtre Facebook Connect

Une chose importante à savoir, c'est que les utilisateurs de Facebook sont très souvent connectés à Facebook lorsqu'ils sont sur Internet, c'est un avantage indéniable. Par conséquent, le filtre `sfFacebookConnectRememberMeFilter` peut se révéler très utile.

Si un utilisateur utilise le site web alors qu'il est déjà connecté à Facebook, le filtre `sfFacebookConnectRememberMeFilter` va automatiquement le connecter sur le site comme le ferait le filtre classique "Remember me" de symfony.

    [php]
    $sfGuardUser = sfFacebook::getSfGuardUserByFacebookSession();
    if ($sfGuardUser)
    {
      $this->getContext()->getUser()->signIn($sfGuardUser, true);
    }

Malheureusement, il subsiste un inconvénient majeur à ce filtre : les utilisateurs ne peuvent plus se déconnecter du site, car tant qu'ils sont connectés sur Facebook, ils seront automatiquement reconnectés sur le site. Cette fonctionnalité est donc à manier avec parcimonie et précaution.

### Implémentation Propre afin d'Eviter l'Erreur Fatale d'IE

Un des pires bugs que l'on puisse rencontrer sur un site Internet est l'erreur "Operation aborted" sur IE, qui fait tout simplement planter le rendu du site... côté client !

C'est en effet dû à la mauvaise qualité du moteur de rendu d'IE6 et d'IE7 qui peuvent tous deux planter si des éléments DOM sont ajoutés à l'élément `body` depuis un script qui n'est pas directement fils de l'élément `body`.

Malheureusement, c'est souvent le cas si du JavaScript Facebook est appelé sans faire attention. Il faut donc prendre garde et bien l'insérer directement dans le `body` à la fin du document. C'est d'autant plus facile à respecter avec symfony grâce notamment aux `slots`. Un `slot` dédié au script Facebook Connect  est utilisé dans la template si nécessaire et est inclus à la fin du layout, juste avant la balise de fermeture `</body>`.

    [php]
    // in a template that uses a XFBML tag or a Facebook Connect button
    slot('fb_connect');
      include_facebook_connect_script();
    end_slot();

    // just before </body> in the layout to avoid problems in IE
    if (has_slot('fb_connect'))
    {
      include_slot('fb_connect');
    }

Bonnes Pratiques pour les Applications Facebook
-----------------------------------------------

Grâce au plugin `sfFacebookConnectPlugin`, l'intégration avec le plugin `sfGuardPlugin` est simplifiée et le choix entre FBML, IFrame ou un site Facebook Connect peut attendre la dernière minute.

Afin d'aller plus loin et de créer une véritable application utilisant plus de fonctionnalités Facebook, il convient de donner quelques pratiques importantes qui profitent de toute la puissance de symfony.

### Configurer Plusieurs Serveurs Facebook Connect de Test

Un point important de la philosophie symfony concerne le débogage rapide et efficace de l'application. Développer sur Facebook peut rendre la tâche particulièrement difficile, car beaucoup de fonctionnalités nécessitent une connexion Internet (pour communiquer avec le serveur Facebook), ainsi qu'un port 80 ouvert pour échanger les cookies d'authentification.

De plus, il existe une contrainte supplémentaire : une application Facebook Connect ne peut être reliée qu'à un seul hôte. C'est un véritable problème si l'application est développée sur une machine, puis testée sur une autre, mise en pré-production sur une troisième et utilisée finalement sur une quatrième.

Dans ce cas la solution la plus simple consiste à créer une application par serveur dans les paramètres développeur de Facebook, et de créer un environnement symfony pour chacune d'elles. C'est trivial à réaliser dans symfony puisqu'il suffit d'un simple copier-coller du fichier `frontend_dev.php` en son équivalent `frontend_preprod.php`. Il ne reste alors qu'à éditer le nouveau fichier en remplaçant l'environnement `dev` par un nouvel environnement `preprod`.

    [php]
    $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'preprod', true);

L'étape suivante consiste à modifier le fichier `app.yml` afin de configurer les différentes applications Facebook correspondantes aux différents environnements.

    [yml]
    prod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    dev:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

    preprod:
      facebook:
        api_key: xxx
        api_secret: xxx
        api_id: xxx

Désormais, chaque application est testable sur chaque serveur distinct en utilisant le point d'entrée `frontend_xxx.php` correspondant.

### Utiliser le Système de Log de symfony pour Déboguer le FBML

Interchanger facilement le layout permet à la fois de tester et de développer une application FBML quasiment entièrement en dehors de Facebook. Cependant, le test final dans Facebook peut malgré tout résulter en un message d'erreur particulièrement obscur.

En effet, le principal problème lorsqu'il s'agit de visualiser le FBML directement dans Facebook vient du fait que les erreurs 500 sont remplacées par un message d'erreur complètement inutile.

De plus, la web debug toolbar, à laquelle les développeurs symfony sont rapidement accrocs, ne peut être utilisée correctement dans une application FBML. Heureusement l'excellent système de log de symfony est là pour nous sauver. Le plugin `sfFacebookConnectPlugin` loggue déjà automatiquement les actions les plus importantes et il est facile de rajouter des lignes dans le fichier partout dans l'application.

    [php]
    if (sfConfig::get('sf_logging_enabled'))
    {
      sfContext::getInstance()->getLogger()->info($message);
    }

### Eviter les Mauvaises Redirections Facebook avec un Proxy

Un bug étrange de Facebook est qu'une fois Facebook Connect configuré pour une application, le serveur hébergeant l'application est considéré comme page d'accueil par défaut de l'application. Bien qu'il soit possible de préciser une page d'accueil, elle doit figurer dans le domaine du serveur hébergeur. Cela peut paraître gênant si c'est une application Facebook dont la page d'accueil se trouve dans le domaine apps.facebook.com ! Aucune autre solution n'existe que de se rendre et configurer la page d'accueil vers une action symfony très simple qui redirige à un endroit désiré. Le code suivant redirige vers la page d'accueil de Facebook :

    [php]
    public function executeRedirect(sfWebRequest $request)
    {
      return $this->redirect('http://apps.facebook.com'.sfConfig::get('app_facebook_app_url'));
    }

### Utiliser le Helper `fb_url_for()` dans les Applications Facebook

Pour garder une application agnostique et utilisable jusqu'à la dernière minute autant en FBML dans Facebook qu'en XFBML dans une IFrame, un problème important persiste : le routage.

 * Pour une application FBML, les liens dans l'application doivent pointer vers  `/app-name/symfony-route` ;

 * pour une application IFrame, il est important de passer l'information de session Facebook d'une page à une autre.

Le plugin `sfFacebookConnectPlugin` fournit pour cela un helper dédié qui permet de faire exactement les deux automatiquement : `fb_url_for()`.

### Rediriger dans une Application Facebook

Les développeurs symfony s'habituent rapidement à rediriger après une requête `post` réussie. C'est en effet une bonne pratique de développement web qui empêche entre autre le double post.

Rediriger dans une application FBML, cependant, ne fonctionne pas comme attendu. A la place, une balise FBML spécifique est nécessaire afin d'informer Facebook de faire la redirection. Toujours dans un ultime but précis de rester agnostique, une méthode statique spéciale, `redirect()`, existe dans la classe `sfFacebook`, qui peut être utilisée par exemple dans l'action de sauvegarde d'un formulaire.

    [php]
    if ($form->isValid())
    {
      $form->save();

      return sfFacebook::redirect($url);
    }

### Connecter des Utilisateurs Existants avec leur Compte Facebook

L'un des principaux buts de Facebook Connect consiste à faciliter le processus d'enregistrement pour les nouveaux utilisateurs. Cependant, une autre utilisation intéressante concerne aussi la connexion des utilisateurs existants à partir de leur compte Facebook. Cette fonctionnalité est utile soit pour obtenir plus d'informations sur eux, ou bien communiquer dans leur feed mais aussi pour leur proposer une authentification en un seul clic. Cette tâche est réalisable de deux manières différentes.

 * Inciter les utilisateurs `sfGuard` existants à cliquer sur le bouton "Connect with Facebook".

L'action `sfFacebookConnectAuth/signIn` ne créera pas un nouvel utilisateur sfGuard si elle détecte un utilisateur déjà authentifié, mais reliera en revanche cet utilisateur avec l'UID de la session Facebook reconnue.

 * Utiliser le système de reconnaissance d'email de Facebook.

Lorsqu'un nouvel utilisateur utilise Facebook Connect sur un site, Facebook est capable de fournir un hash spécifique de ses emails, qui peuvent ensuite être comparés aux hashes des emails existants en base. L'objectif est ainsi de reconnaître un utilisateur existant.

Mais, vraisemblablement, pour des raisons de sécurité, il est impossible d'obtenir les hashes des emails d'un utilisateur si ces derniers n'ont pas été soumis préalablement à l'API de Facebook. C'est pourquoi il est utile d'enregistrer les hashes d'email de tous les nouveaux utilisateurs régulièrement, afin de les reconnaître ultérieurement.

C'est exactement le besoin que remplit la tâche `registerUsers`, qui a été migrée sur symfony 1.2 par Damien Alexandre. Dans l'idéal, cette tâche devrait être exécutée au moins toutes les nuits afin d'enregistrer les nouvelles créations de comptes, ou bien, juste après la création d'un nouveau compte avec la méthode `registerUsers` de `sfFacebookConnect`.

      [php]
      sfFacebookConnect::registerUsers(array($sfGuardUser));

Aller Plus Loin
---------------

J'espère que cet article a rempli son objectif ; aider et inciter les développeurs à démarrer le développement d'une application Facebook sous symfony, et expliquer comment bénéficier de toute la puissance de symfony tout au long de ce développement.

Néanmoins, le plugin `sfFacebookConnectPlugin` ne remplace pas l'API originale de Facebook, et pour apprendre à utiliser toutes les fonctionnalités du développement sur la plate-forme Facebook, il faudra visiter son
[site](http://developers.facebook.com/).

Pour conclure, je tiens à remercier toute la communauté symfony pour sa qualité et sa générosité, et particulièrement tous ceux qui ont déjà contribué au plugin `sfFacebookConnectPlugin` au travers de leurs commentaires et patches : Damien Alexandre, Thomas Parisot, Maxime Picaud, Alban Creton et désolé pour ceux que j'aurais pu oublier. Bien sûr si vous pensez qu'il manque quelque chose dans le plugin, il est toujours possible de contribuer aussi !