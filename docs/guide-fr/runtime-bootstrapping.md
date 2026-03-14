Amorçage
=============

L'amorçage fait référence au processus de préparation de l'environnement avant qu'une application ne démarre, pour résoudre et traiter une requête d'entrée. L'amorçage se fait en deux endroits : le  [script d'entrée](structure-entry-scripts.md) et l'[application](structure-applications.md).

Dans le [script d'entrée](structure-entry-scripts.md), les classes de chargement automatique (*autoloaders*) pour différentes bibliothèques sont enregistrées. Cela inclut la classe de chargement automatique de Composer via son fichier `autoload.php` et la classe de chargement automatique de Yii via son fichier de classe `Yii`. Ensuite, le script d'entrée charge la [configuration](concept-configurations.md) de l'application et crée une instance d'[application](structure-applications.md).

Dans le constructeur de l'application, le travail d'amorçage suivant est effectué :

1. La méthode [[yii\base\Application::preInit()|preInit()]] est appelée. Elle configure quelques propriétés de haute priorité de l'application, comme  [[yii\base\Application::basePath|le chemin de base (*basePath*)]].
2. Le [[yii\base\Application::errorHandler|gestionnaire d'erreurs]] est enregistré.
3. Les propriétés qui utilisent la configuration de l'application sont initialisées.
4. La méthode [[yii\base\Application::init()|init()]] est appelée. À son tour elle appelle la méthode [[yii\base\Application::bootstrap()|bootstrap()]] pour exécuter les composants d'amorçage.
   - Le fichier de manifeste des extensions `vendor/yiisoft/extensions.php` est inclus.
   - Les[composants d'amorçage](structure-extensions.md#bootstrapping-classes) déclarés par les extensions sont créés et exécutés
   - Les [composants d'application(structure-application-components.md) et/ou les [modules](structure-modules.md) déclarés dans la [propriété bootstrap](structure-applications.md#bootstrap) de l'application sont créés et exécutés.

Comme le travail d'amorçage doit être fait avant *chacune* des requêtes, il est très important de conserver ce processus aussi léger et optimisé que possible. 

Évitez d'enregistrer trop de composants d'amorçage. Un composant d'amorçage est seulement nécessaire s'il doit participer à tout le cycle de vie de la prise en charge des requêtes. Par exemple,si un module a besoin d'enregistrer des règles d'analyse additionnelles, il doit être listé dans la [propriété bootstrap](structure-applications.md#bootstrap) afin que les nouvelles règles d'URL prennent effet avant qu'elles ne soient utilisées pour résoudre des requêtes.

Dans le mode production, activez un cache bytecode, tel que [PHP OPcache] ou [APC], pour minimiser le temps nécessaire à l'inclusion et à l'analyse des fichiers PHP.

[PHP OPcache]: https://www.php.net/manual/fr/book.opcache.php
[APC]: https://www.php.net/manual/fr/book.apcu.php

Quelques applications volumineuses ont des [configurations](concept-configurations.md) d'application très complexes qui sont divisées en fichiers de configuration plus petits. Si c'est le cas, envisagez de mettre tout le tableau de configuration en cache et de le charger directement à partir cache avant la création de l'instance d'application dans le script d'entrée. 
