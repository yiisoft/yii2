Qu'est ce que Yii ?
=================

Yii est un framework PHP hautes performances à base de composants qui permet de développer rapidement des applications Web modernes.
Le nom Yii (prononcer `Yee` ou `[ji:]`) signifie "simple et évolutif" en Chinois. Il peut également 
être considéré comme un acronyme de **Yes It Is**!


Pour quel usage Yii est il optimal ?
-----------------------------------

Yii est un framework Web générique, c'est à dire qu'il peut être utilisé pour développer tous types
d'applications Web basées sur PHP. De par son architecture à base de composants et son système de cache sophistiqué,
il est particulièrement adapté au développement d'applications à forte audience telles que des portails, des forums,
des systèmes de gestion de contenu (CMS), des sites e-commerce, des services Web RESTFul, etc.


Comment se positionne Yii vis-à-vis des autres Frameworks ? 
----------------------------------------------------------

- Comme la plupart des frameworks PHP, Yii est basé sur le modèle de conception MVC (Modèle-Vue-Contrôleur) et encourage à une
organisation du code basée sur ce modèle.
- Yii repose sur l'idée que le code doit être écrit de façon simple et élégante. Il ne sera jamais question de
compliquer le code de Yii uniquement pour respecter un modèle de conception.
- Yii est un framework complet offrant de nombreuses caractéristiques éprouvées et prêtes à l'emploi, telles que:
constructeur de requêtes et ActiveRecord, à la fois pour les bases de données relationnelles et NoSQL; prise en charge RESTful API;
prise en charge de caches multi-niveaux; et plus. 
- Yii est extrêmement flexible. Vous pouvez personnaliser ou remplacer presque chaque partie du code du noyau. Vous pouvez également 
profiter de son architecture extensible solide, afin d'utiliser ou développer des extensions distribuables. 
- La haute performance est toujours un des principaux objectifs de Yii.

Yii n'est pas un one-man show, il est soutenu par une [solide équipe de développement du noyau][yii_team] ainsi que d'une grande communauté 
avec de nombreux professionnels qui contribuent constamment au développement de Yii. L'équipe de développeurs de Yii 
garde un œil attentif sur les dernières tendances en développement Web, et sur les meilleures pratiques et caractéristiques 
trouvées dans d'autres frameworks ou projets. Les meilleures pratiques et caractéristiques les plus pertinentes trouvées ailleurs sont régulièrement intégrées dans le code du noyau et utilisables
via des interfaces simples et élégantes.

[yii_team]: https://www.yiiframework.com/team

Versions de Yii
---------------

Yii est actuellement disponible en deux versions majeures : 1.1 et 2.0. La version 1.1, l'ancienne génération, est désormais en mode maintenance. La version 2.0 est une réécriture complète de Yii, adoptant les dernières technologies et protocoles, y compris Composer, PSR, les espaces de noms, les traits, et ainsi de suite. La version 2.0 est la dernière génération du framework et recevra nos principaux efforts de développement dans les prochaines années. 
Ce guide est principalement pour la version 2.0.


Configuration nécessaire
------------------------

Yii 2.0 nécessite PHP 5.4.0 ou plus. Vous pouvez trouver plus de détails sur la configuration requise pour chaque fonctionnalité
en utilisant le script de test de la configuration inclus dans chaque distribution de Yii.

Utiliser Yii requiert des connaissances de base sur la programmation objet (OOP), en effet Yii est un framework basé sur ce type de programmation.
Yii 2.0 utilise aussi des fonctionnalités récentes de PHP, telles que les [espaces de noms](https://www.php.net/manual/fr/language.namespaces.php) et les [traits](https://www.php.net/manual/fr/language.oop5.traits.php).
Comprendre ces concepts vous aidera à mieux prendre en main Yii.

