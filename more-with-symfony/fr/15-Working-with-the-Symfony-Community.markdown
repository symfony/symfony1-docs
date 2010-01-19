Travailler avec la Communauté symfony
=====================================

*Par Stefan Koopmanschap*

Choisir de travailler avec un projet open-source peut être motivé par différentes raisons : la gratuité ou l'accès au code source par exemple. Mais la raison principale réside bien souvent dans sa communauté.

Dans le monde de l'open-source, il existe autant de communautés que de projets open-source. Concernant symfony, sa communauté est à ce jour très ouverte et conviviale. Mais comment bénéficier au mieux de cette communauté ? Et comment chacun peut y apporter sa propre contribution ?

Ce chapitre présente la communauté symfony et les différents moyens d'y collaborer. Les entreprises comme les développeurs trouveront ainsi leur propre façon de participer.

Profiter au Mieux de la Communauté
----------------------------------

Il existe diverses façons de profiter de la communauté symfony. Le simple fait d'utiliser le framework est un bénéfice en soit, car même si, à l'origine, il a été développé et porté par Sensio, symfony ne serait pas là où il en est aujourd'hui sans la forte implication de sa communauté.

### Le Support

Tous les développeurs, et plus particulièrement les débutants, se sont un jour ou l'autre déjà retrouvés bloqués sans savoir comment résoudre un problème. Heureusement, symfony possède une communauté active et accueillante qui se fera un plaisir de répondre à toutes les questions que vous pourriez vous poser.

#### Avant de Poser une Question

Avant de poser une question sur les différents moyens mis à votre disposition,  prenez le temps de chercher une réponse par vous même. Vous pouvez bien sûr effectuer des recherches à l'aide de [Google](http://www.google.com/), mais il est recommandé de concentrer vos recherches sur les différentes listes de diffusion de symfony comme les archives de [symfony-users](http://groups.google.com/group/symfony-users/topics).

#### Poser une Question

Cela peut paraître trivial mais il est important de savoir comment poser une question. Réfléchissez bien à ce que vous êtes sur le point de demander. Vérifiez tout d'abord que la réponse ne se trouve pas déjà dans la documentation officielle. Voici quelques conseils qui vous aideront à obtenir des réponses plus pertinentes :

  * Réfléchissez à votre question. Assurez-vous de la formuler clairement. 
    Expliquez ce que vous faites (ou bien essayez de le faire) et ce que vous
    n'arrivez pas à faire. N'oubliez pas d'indiquer les éventuelles erreurs que
    vous obtenez.

  * Expliquez vos tentatives en indiquant les éléments sur lesquels vous vous 
    êtes appuyés et les pistes dont vous disposez.

#### Les Listes de Diffusion

Plusieurs [Groupes Google](http://groups.google.com) existent autour de symfony. 
Ces groupes sont le meilleur moyen d'entrer en contact avec les utilisateurs et 
les développeurs de symfony. Si vous êtes utilisateur de symfony, le groupe 
[symfony users](http://groups.google.com/group/symfony-users) est le premier endroit pour rechercher de l'aide. Cette liste de diffusion regroupe à la fois des utilisateurs de symfony, mais aussi des débutants et la plupart des membres de la core team du framework. Par conséquent, il y aura toujours quelqu'un capable de répondre à votre question. Il existe aussi d'autres groupes destinés à d'autres sujets :

  * [symfony-devs](http://groups.google.com/group/symfony-devs) pour les 
    discussions concernant le développement du framework (*pas de support !*) ;

  * [symfony-docs](http://groups.google.com/group/symfony-docs) pour les 
    discussions concernant la documentation de symfony ;

  * [symfony-community](http://groups.google.com/group/symfony-community) pour 
    les sujets traitant des initiatives de la communauté.

Gardez bien à l'esprit que la liste de diffusion est un mode de communication indirect et bien moins rapide que ne peut l'être IRC par exemple. Par conséquent, vous devrez attendre quelques heures parfois plusieurs jours avant d'obtenir une réponse appropriée. Il est donc important d'être réactif aux questions que pourrait susciter la vôtre. De manière générale, il convient de rester patient en permanence.

Contrairement à IRC, vous devrez accompagner votre demande d'un maximum d'informations. Indiquez votre configuration, l'ORM que vous utilisez, votre système d'exploitation, les solutions que vous avez essayées et ce qui n'a pas fonctionné. N'hésitez pas à ajouter du code d'exemple car plus vous expliciterez le contexte, et les réponses seront pertinentes.

#### IRC

Par nature, IRC est la façon la plus rapide d'obtenir une réponse. Symfony possède son propre canal - #symfony - sur le réseau [Freenode](http://freenode.net/). Bien qu'une centaine de personnes puissent être connectées simultanément, il n'en demeure pas moins qu'elles sont certainement au travail. Par conséquent, vous devrez probablement faire face à un peu de patience avant d'obtenir une réponse de leur part.

IRC se prête mal à l'affichage de gros blocs de code. Si le cas se présente durant une discussion, des services comme pastebin.com vous permettent de formater votre code sur une page web. Vous pouvez ainsi communiquer l'URL de cette page sur IRC. En réalité, poster un bloc de code sur IRC revient généralement à s'attirer les foudres des autres participants, ce qui ne jouera pas en votre faveur.

Une fois votre question posée sur le canal IRC, prêtez attention à toutes les réponses que vous obtenez. Soyez réactif à d'éventuelles questions complémentaires. Certaines personnes remettront votre approche du 
problème en question. Parfois elles auront raison, et d'autres fois elles auront une vision erronée de votre problème.

Dans tous les cas, répondez à toutes les questions que l'on pourrait vous poser afin d'aider les gens à se faire une idée de votre problématique et de son contexte. Si des personnes font de mauvaises suppositions c'est qu'ils n'ont généralement pas assez de détails. Ne vous sentez en aucun cas offensé car les participants sont là pour vous aider.

En cas d'affluence, et dans un soucis de clarté, veillez à préfixer vos réponses avec le nom de votre interlocuteur.

### Corrections et Nouvelles Fonctionnalités

L'ensemble du code de symfony est le produit de la communauté. Bien que Sensio et Fabien y aient consacré beaucoup de leur temps, leur travail n'en demeure pas moins une production communautaire.

En effet, en choisissant de rendre symfony open-source, ils ont prouvé leur attachement à la communauté ! De même que les nombreux autres utilisateurs qui ont développé de nouvelles fonctionnalités ou bien corrigé des bogues. En somme  lorsque vous travaillez avec symfony (et cela vaut aussi pour tous les autres projets open-source), soyez conscient que c'est grâce aux efforts de la communauté.

### Plugins

Symfony possède un système de plugins qui facilitent l'ajout de plugins externes aux projets symfony. Ce système de plugins est construit autour du framework PEAR, ce qui fait de lui un outil très flexible de-facto. En plus des plugins internes au framework, il existe un certain nombre d'autres greffons développés et maintenus par la communauté.

Ces derniers sont disponibles sur le [site des plugins](http://www.symfony-project.org/plugins/) et classés par catégories. N'hésitez pas à effectuer des recherches parmi eux en les triant à l'aide des filtres de catégories, ORM et versions de symfony supportées. Vous pouvez également saisir des mots clefs pour réduire votre recherche. Grâce à la communauté, un grand nombre de fonctionnalités communes aux applications web actuelles sont librement disponibles.

### Conférences et Événements

A côté de toutes ces interactions numériques, vous pouvez aussi prendre le temps de rencontrer les membres de la communauté à l'occasion de conférences et d'évènements. La plupart des conférences PHP accueillent généralement des membres de la communauté symfony, qu'ils soient spectateurs ou participants. Vous pourrez ainsi apprendre du travail de vos paires. Il existe aussi des évènements dédiés à symfony tels que le [Symfony Live](http://www.symfony-live.com/), le [SymfonyDay](http://www.symfonyday.com/) et le [SymfonyCamp](http://www.symfonycamp.com/). Toutes ces manifestations sont soutenues par une entreprise mais la majorité du travail est réalisée par la communauté.

En participant à ce genre d'évènements, vous en apprendrez davantage sur symfony et vous nouerez des liens avec des membres reconnus de la communauté. Ils pourront peut-être vous prêter main forte plus tard en cas de coup dur. Si vous avez l'opportunité de participer à l'un de ces évènements, n'hésitez surtout pas car cela en vaut la peine.

En plus des conférences « officielles », il existe un nombre croissant de groupes d'utilisateurs symfony de par le monde. Ces groupes sont généralement indépendants et ne bénéficient pas de l'aide d'une entreprise. Il s'agit en réalité de simples rassemblements d'utilisateurs. Il est très facile d'y participer puisqu'il suffit généralement de s'y rendre. Ces réunions permettent de vous constituer un réseau de contacts qui vous aideront sur des problèmes liés à symfony. Ces groupes vous permettront également de trouver du travail ou bien de recruter de nouveaux développeurs en cas de besoin.

### Réputation

Participer à la communauté, rencontrer ses membres, communiquer, et aussi devenir un membre actif, voilà ce qui vous permettra de construire votre propre réputation. Au début, ce travail peut paraître inutile, hormis pour son propre ego, mais il peut aussi s'avérer très intéressant. Par exemple, lorsqu'il s'agit de rechercher un nouvel emploi. En effet, avec une bonne réputation, les offres seront plus nombreuses et plus intéressantes.

De la même manière, si vous êtes à la recherche de développeurs, une bonne réputation vous permettra d'intéresser de nombreux candidats. I se pourrait même que votre offre intéresse quelques grands noms de la communauté.

Participer à la Communauté
--------------------------

Toutes les communautés sont fondées sur un principe d'échange. S'il n'y avait personne pour offrir quelque chose à la communauté, il n'y aurait rien à en retirer en retour non plus. De ce fait, si vous avez bénéficié de la communauté, vous pouvez aussi lui offrir quelque chose en retour. Voyons comment vous pouvez aider la communauté à se renforcer et à s'aggrandir.

### Le Forum et les Listes de Diffusion

Comme expliqué plus haut, le forum et les listes de diffusion sont des excellents candidats pour obtenir de l'aide. Vous y trouverez des réponses à vos questions, des suggestions pour résoudre vos problèmes et des retours d'expérience sur des problématiques récurrentes.

Même si vous débutez avec symfony, l'expérience que accumulerez avec le temps vous permettra de répondre à votre tour à des questions d'autres utilisateurs. Et plus vous acquerrez d'expérience, plus vous serrez en mesure de répondre à des questions complexes. Le simple fait de suggérer certaines pistes à des utilisateurs peut également les aider dans leurs propres recherches. Étant déjà abonné à ces listes, il est très facile d'aider les autres.

### IRC
Au même titre que les listes de diffusion, si vous êtes connectés sur le canal IRC de symfony, vous pouvez de temps à autres répondre à des questions. Il est nul besoin de rester en permanence devant votre client IRC, car la plupart des participants ne le font pas. Par contre, dès qu'ils ont besoin de faire une pause dans leurs travaux, ils consultent le canal, regardent les discussions en cours, donnent des coups de pouce (ou des solutions) à des problèmes ou bien encore discutent d'autres sujets.

Être présent sur le canal peut aussi permettre aux autres participants de vous contacter en spécifiant votre pseudonyme. La plupart des clients IRC vous notifieront cette prise de contact et vous pourrez y répondre. Cela vous permet d'être plus accessible au cas où des membres de la communauté se poseraient une question dont ils savent que vous pourrez y répondre. Ainsi, même en ne faisant rien, vous aiderez des personnes en vous rendant simplement disponible.

### Contribuer au Code

La plupart des utilisateurs de symfony sont des développeurs. Par conséquent,  contribuer directement au code du framework reste pour eux la façon la moins compliquée de s'investir dans la communauté. La section suivante explique comment y parvenir.

#### Proposer des Patches

Il peut bien évidement arriver que vous trouviez un bogue dans symfony. Vous pouvez aussi avoir besoin de réaliser une fonctionnalité qui n'est pas implémentée dans le framework. Comme il n'est pas recommandé de modifier sa copie du framework car cela vous poserait des problèmes à chaque nouvelle mise à jour, il vous est plutôt recommandé de contacter directement les développeurs de symfony afin de leur exposer votre problème et votre solution.

Tout d'abord, vous devez modifier votre copie de symfony afin de corriger le bogue ou d'ajouter un nouveau comportement. Ensuite, vous devez générer un différentiel des fichiers modifiés. Par exemple, avec Subversion, cela se réalise grâce à cette commande :

    $ svn diff > my_feature_or_bug_fix.patch

Vous devez utiliser cette commande à la racine de votre copie de travail pour que tous les changements soient correctement inclus dans votre patch.

Enfin, rendez-vous sur le [bugtracker de symfony](http://trac.symfony-project.org), et après la phase d'identification, créez un nouveau ticket. Remplissez le plus de champs possible afin de faciliter la reproduction de votre bogue. Vous pouvez aussi détailler les parties de symfony qui sont affectées par vos modifications.

Dans le champ "Ticket Properties", choisissez la version de symfony pour laquelle vous avez créé le patch. Quand cela est possible, sélectionnez le composant de symfony que le patch modifie. S'il y en a plusieurs, sélectionnez celui qui est le plus concerné.

Veuillez aussi à préfixer le contenu du champ "Short Summary" par [PATCH], puis cochez la case indiquant qu'il y a un patch à attacher à ce ticket, le cas échéant.

#### Contribuer aux Plugins

Améliorer le framework n'est pas à la porté de tous. Mais tous les développeurs qui utilisent symfony implémentent des fonctionnalités propres à leurs projets. Certaines de ces fonctionnalités sont trop spécifiques pour intéresser d'autres développeurs, mais la plupart le peuvent. Vous savez certainement que les « bonnes pratiques » recommandent de mettre la logique de l'application dans des plugins afin de faciliter la réutilisation du code ultérieurement, pour vous et/ou pour votre entreprise. Vous pouvez alors faire le choix de rendre ces plugins open-source et de les mettre à la disposition des autres utilisateurs de 
symfony.

Développer un plugin symfony est une tâche simple. il suffit en effet de commencer par [lire la documentation](http://www.symfony-project.org/jobeet/1_3/Doctrine/en/20#chapter_20_contributing_a_plugin) concernant la création de plugins. Le site de symfony met à votre disposition un ensemble d'outils qui vous permettent de publier vos créations via le canal PEAR des plugins symfony et d'héberger vos sources sur le dépôt Subversion des plugins. Cette solution avantageuse vous permet de ne pas avoir à configurer vous même votre serveur Subversion, votre serveur PEAR et documenter l'ensemble.

De plus, si vous ajoutez votre plugin au système de plugins de symfony, idevient instantanément disponible à l'ensemble de la communauté, sans plus de configuration de votre part. Mais après tout, vous êtes libre de faire comme bon vous semble.

### Documenter

La documentation est l'un des points forts de symfony. Elle fut initialement rédigée par la Core Team mais la communauté y a aussi beaucoup contribué. Il existe également des travaux conjoints entre la communauté et la Core Team comme le tutoriel Jobeet par exemple.

Dans tous les cas, une bonne documentation doit à la fois aider les nouveaux utilisateurs et servir continuellement de référence aux développeurs plus expérimentés. Les sections suivantes exposent les différentes façons de contribuer à cette abondante documentation.

#### Rédiger des Billets de Blog

Partager votre expérience et vos connaissances au sujet de symfony apporte beaucoup plus à la communauté. Particulièrement lorsque vous traitez de sujets complexes. Bien référencés, ces billets aideront d'autres développeurs à résoudre leur propres problématiques en prenant exemple sur votre expérience.

Par conséquent, ne vous limitez pas à rédiger des billets présentant symfony dans ses grandes lignes. Vous pouvez partager votre expérience, les problèmes que vous avez rencontrés ou encore présenter les toutes dernières fonctionnalités de la nouvelle version de symfony.

Tous ceux qui écrivent des articles sur symfony sont invités à laisser l'adresse de leur blog dans la [liste des bloggeurs symfony](http://trac.symfony-project.org/wiki/SymfonyBloggers). Tout le contenu de ces blogs est mis en avant sur la [page symfony de la communauté](http://www.symfony-project.org/community). Pour voir figurer vos propres billets, vous devez créer un flus RSS contenant exclusivement du contenu relatif à symfony. Merci ne pas ajouter de flux autre que votre blog (pas de flux twitter par exemple).

#### Écrire des Articles

Les développeurs les plus prolifiques peuvent creuser encore plus profond. Partout dans le monde, de nombreux magazines traitant de PHP proposent de publier des articles. Ces articles doivent être idéalement plus avancés, mieux organisés et de bien meilleure qualité qu'un simple billet de blog, mais ils ont l'avantage d'être lus par plus de monde.

Ces magazines ont chacun leur propre façon de gérer les publications externes. Par conséquent, reportez-vous au magazine ou sur son site web pour connaître les modalités de publication.

Les magazines internet comme les groupes d'utilisateurs PHP ou symfony, les sites de développement web, etc... peuvent aussi être intéressés par vos articles.

#### Traduire la Documentation

Généralement les développeurs PHP sont à l'aise avec l'anglais. Cependant, pour beaucoup, l'anglais n'est pas leur langue natale, ce qui rend parfois difficile la lecture de la documentation technique. La communauté symfony encourage la traduction de sa documentation en donnant les droits d'écriture sur le dépôt de la documentation à tous les traducteurs et en publiant ces traductions sur le site officiel de symfony.

Ces traductions sont coordonnées sur la [liste de diffusion symfony docs](http://groups.google.com/group/symfony-docs). Si vous 
êtes disposés à traduire des articles, prenez le temps d'y faire un bref passage afin d'éviter les traductions en double ou ce genre d'erreurs.

#### Ajouter du Contenu au Wiki

Le wiki est la façon la plus libre de rédiger de la documentation sur n'importe quel sujet. Symfony possède son propre [wiki](http://trac.symfony-project.org/wiki) où ses utilisateurs peuvent ajouter librement de la documentation. Nous vous encourageons à poster vos propres oeuvres éditoriales, mais il est également possible de parfaire les articles existants en les corrigeant ou en les améliorant. Il existe aussi de vieux articles dont les contenus sont aujourd'hui obsolètes. Faire le ménage dans ces articles est aussi une bonne façon de faciliter la recherche d'autres utilisateurs.

Si vous souhaitez vous faire une idée du genre d'articles que peut contenir le wiki ou si vous avez besoin d'inspiration pour écrire vos propres articles, vous 
pouvez accéder à la [page d'accueil](http://trac.symfony-project.org/wiki) du 
wiki et consulter son contenu.

### Présentations

Écrire des articles est un bon moyen de partager votre savoir et votre expérience. De plus, par internet ils deviennent disponibles à tous et figurent dans les réponses des moteurs de recherches. Cependant, il existe d'autres façons de partager votre expérience. Vous pouvez par exemple réaliser des présentations à l'occasion d'événements.

  * aux conférences PHP / symfony ;
 
  * dans des réunions locales de développeurs ;
 
  * dans votre entreprise (pour vos collègues ou vos décideurs).

Suivant le lieu et l'audience, vous aurez sûrement à adapter votre présentation. Alors que les décideurs ne seront pas intéressés par les détails techniques, les participants d'une conférence sur symfony attendront plus qu'une présentation sommaire du framework. De ce fait, prenez le temps de choisir convenablement votre sujet et préparer votre présentation. Faites relire vos diapositives et si possible faites des essais devant des personnes qui pourront vous critiquer de manière constructive.

Vous pouvez toujours trouver de l'aide pour votre présentation sur la 
[liste de diffusion de la communauté symfony](http://groups.google.com/group/symfony-community), où de nombreuses personnes sont déjà intervenues lors de conférences. De même, si vous ne savez pas comment réaliser votre présentation, abonnez-vous à cette liste de diffusion afin de recevoir les appels à participation de nombreuses conférences et / ou obtenir des contacts avec des groupes ou des associations locales.

### Organiser un Evénement

En plus des présentations aux conférences déjà existantes, vous pouvez organiser vos propres événements. Peu importe votre ambition et votre cible. Vous pouvez même organiser des événements au sein d'autres événements.

Prenons comme exemple le « symfony update meeting » qui s'est tenu lors de la conférence PHPNW en 2008. Tout a commencé sur twitter et sur IRC. Plusieurs utilisateurs de symfony avaient des questions sur ce que serait symfony 1.2. Le jour de la conférence, durant l'une des pauses entre deux sessions, une dizaine de personnes se sont rassemblées dans une pièce fournie par l'organisation et ont eu un exposé sur symfony 1.2.

Cet événement fut court et pour peu de personnes, mais celles-ci repartirent toutes avec une bonne idée de ce que serait, à l'époque, la nouvelle version de symfony.

Un autre bon exemple est l'organisation de conférences pour la communauté comme les [SymfonyCamp](http://www.symfonycamp.com/) ou le [SymfonyDay Cologne](http://www.symfonyday.com/). Ces deux conférences symfony ont été organisées par des entreprises utilisant symfony qui veulent collaborer avec la communauté. Toutes ces conférences ont remporté un vif succès, notamment grâce à des intervenants passionnants.

### Devenir Actif Localement

Comme il l'a été expliqué plus haut, tout le monde n'est pas capable de comprendre la documentation ou bien les articles écris en anglais. La communication par internet peut aussi avoir ses limites. Par conséquent, vous pouvez également vous investir localement pour symfony.

Les meilleurs exemples sont les associations d'utilisateurs symfony. Depuis quelques années, de nombreuses initiatives de ce genre ont vu le jour et la plupart ont déjà organisé des rassemblements, le plus souvent informels et gratuits.

La [liste de diffusion de la communauté symfony](http://groups.google.com/group/symfony-community) est un bon endroit pour trouver des groupes ou associations dans votre région ou, à défaut, créer votre propre groupe. Vous trouverez sur cette liste des membres et des organisateurs d'autres groupes qui seront capables de vous apporter leur aide pour monter votre propre association.

Outre de véritables rassemblements locaux, vous pouvez aussi promouvoir symfony dans votre langue sur internet. Lancer un portail symfony est un bon exemple. C'est le cas du site [Spanish symfony portal](http://www.symfony.es/), qui informe les visiteurs des nouveaux articles en espagnol sur symfony. Ce site met aussi à la disposition des visiteurs une importante documentation en espagnol qui permet aux développeurs espagnols d'apprendre symfony, et de se tenir au courant de ses évolutions.

### Intégrer la Core Team

La Core Team fait aussi partie de la communauté symfony. Tous les membres de la Core Team ont commencé comme simples utilisateurs du framework. Ils se sont ensuite vus intégrés la Core Team en raison de leur forte implication dans la communauté. Symfony est une [méritocratie](http://fr.wikipedia.org/wiki/Méritocratie), ce qui signifie que si vous faites preuve de talent et de compétences, vous aurez peut être la chance d'intégrer cette Core Team au même titre que n'importe lequel de ses membres.

Prenez l'exemple de [la nomination de Bernhard Schussek](http://www.symfony-project.org/blog/2009/08/27/bernhard-schussek-joins-the-core-team). Bernhard a rejoint la Core Team après son remarquable travail sur la seconde version du framework de tests unitaires Lime et ses nombreuses propositions de patches.

### Par Où Commencer ?

Maintenant que vous savez comment bénéficier de la communauté mais aussi comment y contribuer, vous découvrirez dans les lignes suivantes les points de départ qui vous permettront, selon vos possibilités et vos envies, de vous impliquer dans la communauté symfony.

#### La Liste de Diffusion de la Communauté symfony

La [liste de diffusion de la communauté symfony](http://groups.google.com/group/symfony-community) offre aux membres un moyen de discuter des initiatives de la communauté et de les rejoindre. Elle leur permet également d'échanger sur tous les autres sujets ayant trait à la 
communauté.

Si vous souhaitez rejoindre l'une de ces initiatives, répondez simplement à la discussion relative à ce projet. Si vous avez une idée qui peut servir la
communauté symfony, n'hésitez pas à la soumettre sur cette liste. De la même façon, vous pouvez y poser toutes vos questions concernant la communauté ou les différentes façons d'interagir avec ses membres.

#### La page "How to contribute to symfony"

Depuis un certain temps maintenant, le wiki du site symfony possède un page spéciale intitulée [How to contribute to symfony](http://trac.symfony-project.org/wiki/HowToContributeToSymfony). Cette page liste de manière exhaustive les différentes façons de vous impliquer afin d'aider symfony et sa communauté, quelles que soient vos compétences. C'est bien entendu un point de passage obligatoire pour toute personnes voulant s'impliquer dans la communauté symfony.

### Autres Communautés

Grâce au travail de nombreuses personnes, un certain nombre de projets ont vu le jour concernant symfony et ses utilisateurs. Deux projets méritent tout particulièrement de les mentionner.

#### Symfonians

[Symfonians](http://www.symfonians.net/) est un site web communautaire qui liste les développeurs et les entreprises qui utilisent symfony au quotidien, ainsi que leurs projets symfony respectifs. Il permet aussi aux entreprises de publier des offres d'emploi ou de stages.

Vous pouvez bien sûr entrer en contact avec les autres développeurs et les entreprises, et vous disposez également d'un annuaire des applications symfony. Ce dernier est une importante galerie qui présente l'ensemble des possibilités offertes par symfony. La diversité de ces applications rend leur exploration très intéressante et donne une bonne vision de ce que l'on peut accomplir avec ce framework.

Comme ce site est communautaire, vous pouvez y ouvrir un compte et créer votre profil, mais aussi celui de votre entreprise et commencer à ajouter les applications que vous avez réalisées ainsi que des offres d'emploi.

#### Le Groupe LinkedIn symfony 

En tant que développeur PHP, vous avez certainement eu vent du site Internet LinkedIn sur lequel avez probablement un profil détaillé. Pour ceux qui ne connaissent pas encore LinkedIn, il s'agit d'un site sur lequel vous avez la possibilité de construire votre propre réseau social professionnel et entrer en contact avec ses membres. LinkedIn offre également la possibilité de créer des groupes de discussion, de publier des actualités et des offres d'emploi.

Symfony possède [son propre groupe](http://www.linkedin.com/groups?gid=29205&trk=myg_ugrp_ovr) (identification nécessaire) et en devenant membre, vous pourrez discuter de sujets relatifs à symfony, suivre les actualités du framework et aussi publier / consulter des offres d'emploi relatives à symfony.

Conclusion
----------

Désormais, vous devriez avoir une bonne idée de ce que vous pouvez attendre de la communauté symfony et de ce qu'elle peut attendre de vous. Gardez bien à l'esprit que tout projet open-source repose sur la mobilisation de sa communauté. Ce soutien peut prendre un grand nombre de formes, à commencer par les réponses aux questions des débutants, la proposition de patches, en passant par le développement des plugins et la promotion du projet. Alors, qu'attendez-vous pour nous rejoindre ?