Quelle version symfony ?
======================

Ce livre a été écrit pour les deux versions (symfony 1.3 et symfony 1.4). Bien que l'écriture
d'un livre unique pour deux versions différentes d'un logiciel est assez inhabituel, cette
section explique quelles sont les principales différences entre les deux versions, et
comment faire le meilleur choix pour vos projets.

Symfony 1.3 et symfony 1.4 ont été livrées à peu près au même
moment (à la fin de 2009). En fait, ils ont tous les deux
**exactement les mêmes fonctionnalités**. La seule différence entre les deux versions est sur la manière dont ils
soutiennent la compatibilité descendante avec les anciennes versions de symfony.

Symfony 1.3 est la version à utiliser si vous devez mettre à jour un
projet qui utilise une ancienne version de symfony (1.0, 1.1 ou 1.2). Elle a une
couche de compatibilité ascendante et toutes les fonctionnalités qui ont été dépréciées
lors de la période du développement 1.3 sont encore disponibles. Cela signifie que la mise à niveau
est facile, simple et sûr.

Mais si vous démarrez un nouveau projet aujourd'hui, vous devez utiliser symfony 1.4. Cette version
possède les mêmes fonctionnalités que symfony 1.3, mais toutes les fonctionnalités dépréciées et
la couche de compatibilité a été supprimée. Cette version est plus propre et aussi un peu plus
rapide que symfony 1.3. Un autre gros avantage de l'utilisation de symfony 1.4 est son
plus long support. Avoir une version supportée à long terme, c'est un maintien par l'équipe
de symfony pendant trois ans (jusqu'en Novembre
2012).

Bien sûr, vous pouvez migrer vos projets vers symfony 1.3 et puis, lentement, mettre à jour
votre code pour supprimer les fonctionnalités obsolètes et finir par passer à symfony 1.4
pour bénéficier du support à long terme. Vous avez beaucoup plus de temps pour planifier
le changement de symfony 1.3, qui est pris en charge pour une année (jusqu'en Novembre 2010).

Comme ce livre ne décrit pas les fonctionnalités dépréciées, tous les exemples fonctionnent aussi
bien sur les deux versions.