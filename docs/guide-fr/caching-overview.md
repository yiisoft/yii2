Mise en cache
=============

La mise en cache est un moyen peu coûteux et efficace d'améliorer la performance d'une application Web. En stockant des données relativement statiques en cache et en les servant à partir de ce cache lorsqu'elles sont demandées, l'application économise le temps qu'il aurait fallu pour générer ces données à partir de rien à chaque demande.

La mise en cache se produit à différents endroits et à différents niveaux dans une application Web. Du côté du serveur, au niveau le plus bas, le cache peut être utilisé pour stocker des données de base, telles qu'une liste des informations sur des articles recherchée dans une base de données ; et à un niveau plus élevé, il peut être utilisé pour stocker des fragments ou l'intégralité de pages Web, telles que le rendu des articles les plus récents. 

Du côté client, la mise en cache HTTP peut être utilisée pour conserver le contenu des pages visitées les plus récentes dans le cache du navigateur.

Yii prend en charge tous ces mécanismes de mise en cache :

* [Mise en cache de données](caching-data.md)
* [Mise en cache de fragments](caching-fragment.md)
* [Mise en cache de pages](caching-page.md)
* [Mise en cache HTTP](caching-http.md)
