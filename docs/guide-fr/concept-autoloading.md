Chargement automatique des classes
==================================

Yii compte sur le [mécanisme de chargement automatique des classes](http://www.php.net/manual/en/language.oop5.autoload.php) pour localiser et inclure tous les fichiers de classes requis. Il fournit un chargeur automatique de classes de haute performance qui est conforme à la [norme PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md). Le chargeur automatique est installé lorsque vous incluez le fichier `Yii.php`.

> Note: pour simplifier la description, dans cette section, nous ne parlerons que du chargement automatique des classes. Néanmoins, gardez présent à l'esprit que le contenu que nous décrivons ici s'applique aussi au chargement automatique des interfaces et des traits. 


Utilisation du chargeur automatique de Yii <span id="using-yii-autoloader"></span>
------------------------------------------

Pour utiliser le chargeur automatique de classes de Yii, vous devez suivre deux règles simples lorsque vous créez et nommez vos classes :

* Chaque classe doit être placée sous un [espace de noms](http://php.net/manual/en/language.namespaces.php) (p. ex. `foo\bar\MyClass`)
* Chaque classe doit être sauvegardée sous forme d'un fichier individuel dont le chemin est déterminé par l'algorithme suivant : 

```php
// $className est un nom de classe pleinement qualifié sans la barre oblique inversée de tête
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```

For exemple, si le nom de classe et l'espace de noms sont `foo\bar\MyClass`, l'[alias](concept-aliases.md) pour le chemin du fichier de classe correspondant est `@foo/bar/MyClass.php`. Pour que cet alias puisse être résolu en un chemin de fichier, soit `@foo`, soit `@foo/bar` doit être un [alias racine](concept-aliases.md#defining-aliases).

Lorsque vous utilisez le [modèle de projet *basic*](start-installation.md), vous pouvez placer vos classes sous l'espace de noms de niveau le plus haut `app` afin qu'elles puissent être chargées automatiquement par Yii sans avoir besoin de définir un nouvel alias. Cela est dû au fait que `@app` est un [alias prédéfini](concept-aliases.md#predefined-aliases), et qu'un nom de classe comme `app\components\MyClass` peut être résolu en le fichier de classe `AppBasePath/components/MyClass.php`, en appliquant l'algorithme précédemment décrit. 

Dans le [modèle de projet avancé](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), chaque niveau possède son propre alias. Par exemple, le niveau « interface utilisateur » a l'alias `@frontend`, tandis que le niveau « interface d'administration » a l'alias `@backend`. En conséquence, vous pouvez mettre les classes de l'interface utilisateur sous l'espace de noms `frontend`, tandis que les classes de l'interface d'administration sont sous l'espace de noms `backend`. Cela permet à ces classes d'être chargées automatiquement par le chargeur automatique de Yii. 

Table de mise en correspondance des classes <span id="class-map"></span>
-------------------------------------------

Le chargeur automatique de classes de Yii prend en charge la fonctionnalité *table de mise en correspondance des classes*, qui met en correspondance les noms de classe avec les chemins de classe de fichiers. Lorsque le chargeur automatique charge une classe, il commence par chercher si la classe existe dans la table de mise en correspondance. Si c'est le cas, le chemin de fichier correspondant est inclus directement sans plus de recherche. Cela rend le chargement des classes très rapide. En fait, toutes les classes du noyau de Yii sont chargées de cette manière. 

Vous pouvez ajouter une classe à la table de mise en correspondance des classes, stockée dans `Yii::$classMap`, avec l'instruction :

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

Les [alias](concept-aliases.md) peuvent être utilisés pour spécifier des chemins de fichier de classe. Vous devez définir la table de mise en correspondance dans le processus d'[amorçage](runtime-bootstrapping.md) afin qu'elle soit prête avant l'utilisation de vos classes. 


Utilisation d'autres chargeurs automatiques <span id="using-other-autoloaders"></span>
-------------------------------------------

Comme Yii utilise Composer comme gestionnaire de dépendances de paquets, il est recommandé que vous installiez aussi le chargeur automatique de Composer. Si vous utilisez des bibliothèques de tierces parties qui ont besoin de leurs propres chargeurs, vous devez installer ces chargeurs également. 

Lors de l'utilisation conjointe du chargeur automatique de Yii et d'autres chargeurs automatiques, vous devez inclure le fichier `Yii.php` *après* que tous les autres chargeurs automatiques sont installés. Cela fait du chargeur automatique de Yii le premier à répondre à une requête de chargement automatique de classe. Par exemple, le code suivant est extrait du [script d'entrée](structure-entry-scripts.md) du [modèle de projet *basic*](start-installation.md). La première ligne installe le chargeur automatique de Composer, tandis que la seconde installe le chargeur automatique de Yii :

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

Vous pouvez utiliser le chargeur automatique de Composer seul sans celui de Yii. Néanmoins, en faisant de cette manière, la performance de chargement de vos classes est dégradée et vous devez appliquer les règles de Composer pour que vos classes puissent être chargées automatiquement. 

> Info: si vous voulez ne pas utiliser le chargeur automatique de Yii, vous devez créer votre propre version du fichier `Yii.php` et l'inclure dans votre [script d'entrée](structure-entry-scripts.md).


Chargement automatique des classes d'extension <span id="autoloading-extension-classes"></span>
----------------------------------------------

Le chargeur automatique de Yii est capable de charger automatiquement des classes d'[extension](structure-extensions.md). La seule exigence est que cette extension spécifie la section `autoload` correctement dans son fichier `composer.json`. Reportez-vous à la [documentation de Composer](https://getcomposer.org/doc/04-schema.md#autoload) pour plus de détails sur la manière de spécifier `autoload`.

Dans le cas où vous n'utilisez pas le chargeur automatique de Yii, le chargeur automatique de Composer peut toujours charger les classes d'extensions pour vous. 
