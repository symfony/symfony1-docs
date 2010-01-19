Introduction
============

*Par Fabien Potencier*

A l'heure où ces lignes sont écrites, le projet symfony a célébré une étape significative : son [quatrième anniversaire](http://trac.symfony-project.org/changeset/1). En seulement quatre ans, le framework symfony a grandi pour devenir l'un des frameworks PHP les plus populaires dans le monde et sur lequel s'appuient des sites à fort trafic comme [Delicious](http://sf-to.org/delicious),
[Yahoo Bookmarks](http://sf-to.org/bookmarks)
et 
[Daily Motion](http://sf-to.org/dailymotion). Néanmoins, c'est la sortie récente de symfony 1.4 en novembre 2009 qui marque la fin d'un cycle. Cet ouvrage est le meilleur moyen de terminer ce cycle, et de ce fait, vous vous apprêtez à lire le tout dernier livre officiel de la branche 1.x de symfony, publié par l'équipe du projet symfony. Le prochain livre à paraître se focalisera quant à lui autour de Symfony 2.0, et sera dévoilé à la fin de l'année 2010.

Pour cette raison, et bien d'autres que j'expliquerai dans cette introduction, ce livre est un peu spécial pour nous tous.

Pourquoi avoir écrit un nouvel ouvrage ?
----------------------------------------

Nous avons récemment publié deux autres livres sur symfony 1.3 et 1.4:  "[Practical symfony](http://books.sensiolabs.com/book/9782918390169)" et 
"[The symfony reference guide](http://books.sensiolabs.com/book/9782918390145)". Le livre pratique est un excellent moyen de démarrer l'apprentissage de symfony dans la mesure où vous apprenez toutes les bases du framework à travers le développement d'un projet réel, étape par étape. Le dernier est un guide de référence qui agrège la plupart des informations de configuration dont vous pourriez avoir besoin au quotidien pour vos développements.

"Plus loin avec symfony" est un ouvrage à propos de sujets plus avancés de symfony. Cet ouvrage n'est pas le tout premier que vous devriez lire à propos de symfony, mais il s'avèrera être une aide ultime pour tous ceux qui ont déjà fait leurs preuves sur plusieurs petits projets réalisés avec le framework. Si vous avez déjà voulu savoir comment symfony fonctionne à l'intérieur, ou bien si vous souhaitez étendre le framework de diverses manières en vue de le faire fonctionner pour des besoins spécifiques, alors ce livre est fait pour vous. Dans ce cas précis, "More with symfony" est tout ce dont il faut savoir pour faire évoluer vos compétences symfony au niveau supérieur.

Comme cet ouvrage est un recueil de tutoriels à propos de sujets divers, n'hésitez pas à lire les chapitres qui vont suivre dans l'ordre que vous souhaitez, en vous basant sur ce que vous êtes entrain d'essayer d'accomplir avec le framework.

A propos de ce livre
--------------------

Cet ouvrage est un peu spécial parce qu'il s'agit *d'un livre écrit par la communauté* pour la communauté. Plus d'une douzaine de personnes ont contribué à cet ouvrage : des auteurs, aux traducteurs, en passant par les relecteurs, c'est en réalité un incroyable concentré d'efforts qui a été mis en oeuvre pour parvenir à ce livre.

Ce livre a été publié simultanément dans pas moins de cinq langues (anglais, français, italien, espagnol et japonais). Tout cela n'aurait été possible sans le courageux travail bénévole des équipes de traduction.

Cet ouvrage a été rendu possible grâce à *l'esprit Open-Source* et c'est aussi pourquoi il est publié sous une licence Open-Source. Ce point à lui seul change tout, car il signifie que personne n'a été payé pour travailler sur ce recueil : tous les contributeurs ont travaillé dur pour y parvenir et c'est surtout parce qu'ils voulaient tous y arriver.

Au cours de cette aventure, chaque contributeur a souhaité à la fois partager ses connaissances, contribuer en retour à la communauté, participer à l'évangélisation de symfony et, bien sûr, de prendre du plaisir et devenir célèbre.

"Plus loin avec symfony" a été écrit par dix auteurs qui utilisent symfony au quotidien en poste de développeurs ou de chefs de projets. Ces personnes ont toutes une connaissance particulièrement étendue du framework, et ont souhaité partager leur savoir et leur expérience dans ces chapitres.

Remerciements
-------------

Lorsque j'ai commencé à réfléchir comment écrire un nouveau livre à propos de symfony en août 2009, j'ai immédiatement eu une idée folle : pourquoi ne pas écrire un livre en deux mois seulement et le publier simultanément en cinq langues !

Il va de soi qu'impliquer la communauté dans un projet d'une telle ampleur fut presque obligatoire. J'ai commencé par parler de cette idée au Japon pendant la conférence PHP, et en quelques heures seulement, l'équipe de traduction japonaise était déjà prête à travailler. C'était incroyable ! La réponse des auteurs et des traducteurs était elle aussi très encourageante, et peu de temps après, "Plus loin avec symfony" était né.

Je souhaite remercie toutes les personnes qui ont participé d'une manière ou d'une autre à la création de cet ouvrage, et notamment, sans ordre particulier, les personnes ci-dessous :

Ryan Weaver, Geoffrey Bachelet, Hugo Hamon, Jonathan Wage, Thomas Rabaix,
Fabrice Bernhard, Kris Wallsmith, Stefan Koopmanschap, Laurent Bonnet, Julien
Madelin, Franck Bodiot, Javier Eguiluz, Nicolas Ricci, Fabrizio Pucci,
Francesco Fullone, Massimiliano Arione, Daniel Londero, Xavier Briand,
Guillaume Bretou, Akky Akimoto, Hidenori Goto, Hideki Suzuki, Katsuhiro Ogawa,
Kousuke Ebihara, Masaki Kagaya, Masao Maeda, Shin Ohno, Tomohiro Mitsumune,
et Yoshihiro Takahara.

Avant de commencer
------------------

Ce livre a été écrit pour les versions 1.3 et 1.4 de symfony. Comme écrire un livre unique pour deux versions différentes d'un logiciel est une tâche peu commune, cette section explique quelles sont les principales différences entre les deux versions, et comment faire le meilleur choix pour vos projets.

Les versions 1.3 et 1.4 de symfony ont toutes les deux été publiées à peu près au même moment (à la fin de l'année 2009). Par conséquent, elles ont toutes les deux le **même jeu de fonctionnalités**. La seule différence qui les oppose concerne le support de la rétro-compatibilité avec les versions plus anciennes de symfony.

Symfony 1.3 est la distribution que vous souhaiterez utiliser si vous avez besoin de migrer un projet ancien qui repose sur une version antérieure de symfony (1.0, 1.1 ou 1.2). Cette version intègre une couche de compatibilité rétrograde et toutes les fonctionnalités qui ont été rendues obsolètes pendant le cycle de développement de la branche 1.3 restent disponibles. Par conséquent, cela signifie aussi que mettre à jour un projet est facile, simple et sûr.

En revanche, si vous démarrez un nouveau projet aujourd'hui, vous devriez utiliser directement symfony 1.4. Cette version a exactement le même jeu de fonctionnalités que symfony 1.3 à la différence que les fonctionnalités obsolètes, y compris la couche entière de compatibilité rétrograde, ont été supprimées. 

Cette version est donc plus saine et aussi légèrement plus rapide que symfony 1.3. Un autre avantage non négligeable d'utiliser symfony 1.4 est son support à plus long terme. En devenant une version supportée à long terme (LTS - Long Time Support), symfony 1.4 sera maintenue par l'équipe de développement pendant trois ans, jusqu'en novembre 2012.

Bien évidemment, vous pouvez migrer vos projets vers symfony 1.3 et puis mettre à jour votre code petit à petit afin de supprimer les fonctionnalités obsolètes, et éventuellement poursuivre la migration vers symfony 1.4 pour bénéficier de son support à long terme. Vous avez encore tout le temps de planifier votre migration vers symfony 1.3 puisque cette dernière sera supportée pendant un an, jusqu'en novembre 2010.

Enfin, comme cet ouvrage ne présente aucune fonctionnalité obsolète, tous les exemples que vous découvrirez fonctionneront exactement de la même manière sur les deux versions.