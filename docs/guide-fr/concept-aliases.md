Alias
=====

Les alias sont utilisés  pour représenter des chemins de fichier ou des URL de façon à ce que vous n'ayez pas besoin d'écrire ces chemins ou ces URL en entier dans votre code. Un alias doit commencer par le caractère arobase `@` pour être différentié des chemins de fichier et des URL normaux. Les alias définis sans ce caractère de tête `@` sont automatiquement préfixés avec ce dernier.

Yii possèdent de nombreux alias pré-définis déjà disponibles. Par exemple, l'alias `@yii` représente le chemin d'installation de la base structurée de développement PHP (*framework*), Yii ; L'alias `@web` représente l'URL de base de l'application Web en cours d'exécution.

Définition des alias <span id="defining-aliases"></span>
--------------------

Vous pouvez définir un alias pour un chemin de fichier ou pour une URL en appelant [[Yii::setAlias()]]:

```php
// un alias pour un chemin de fichier
Yii::setAlias('@foo', '/path/to/foo');

// un alias pour une URL
Yii::setAlias('@bar', 'https://www.example.com');


// un alias de fichier concrêt qui contient une classe  \foo\Bar
Yii::setAlias('@foo/Bar.php', '/definitely/not/foo/Bar.php');
```

> Note: le chemin de fichier ou l'URL pour qui un alias est créé peut *ne pas* nécessairement faire référence à un fichier ou une ressource existante.

Étant donné un alias, vous pouvez dériver un autre alias – sans faire appel à [[Yii::setAlias()]]) – en y ajoutant une barre oblique de division `/` suivi d'un ou plusieurs segments de chemin. Les alias définis via [[Yii::setAlias()]] sont des *alias racines*, tandis que les alias qui en dérivent sont des *alias dérivés*. Par exemple, `@foo` est un alias racine, alors que `@foo/bar/file.php` est un alias dérivé.

Vous pouvez définir un alias en utilisant un autre alias (qu'il soit racine ou dérivé) :

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Les alias racines sont ordinairement définis pendant l'étape d'[amorçage](runtime-bootstrapping.md). Par exemple, vous pouvez appeler [[Yii::setAlias()]] dans le [script d'entrée](structure-entry-scripts.md). Pour commodité, la classe [Application](structure-applications.md) fournit une propriété nommée `aliases` que vous pouvez configurer dans la [configuration](concept-configurations.md) de l'application :

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'https://www.example.com',
    ],
];
```


Résolution des alias <span id="resolving-aliases"></span>
--------------------

Vous pouvez appeler [[Yii::getAlias()]] pour résoudre un alias racine en le chemin de fichier ou l'URL qu'il représente. La même méthode peut aussi résoudre un alias dérivé en le chemin de fichier ou l'URL correspondant :

```php
echo Yii::getAlias('@foo');               // affiche : /path/to/foo
echo Yii::getAlias('@bar');               // affiche : https://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // affiche : /path/to/foo/bar/file.php
```

Le chemin ou l'URL que représente un alias dérivé est déterminé en remplaçant l'alias racine par le chemin ou l'URL qui lui correspond dans l'alias dérivé.

> Note: la méthode [[Yii::getAlias()]] ne vérifie pas que le chemin ou l'URL qui en résulte fait référence à un fichier existant ou à une ressource existante.


Un alias racine peut également contenir des barres obliques de division `/`. La méthode [[Yii::getAlias()]] est suffisamment intelligente pour dire quelle partie d'un alias est un alias racine et, par conséquent, déterminer correctement le chemin de fichier ou l'URL qui correspond : 

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // affiche : /path/to/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // affiche : /path2/bar/file.php
```

Si `@foo/bar` n'est pas défini en tant qu'alias racine, la dernière instruction affiche `/path/to/foo/bar/file.php`.


Utilisation des alias <span id="using-aliases"></span>
---------------------

Les alias sont reconnus en différents endroits dans Yii sans avoir besoin d'appeler [[Yii::getAlias()]] pour les convertir en chemin ou URL. Par exemple, [[yii\caching\FileCache::cachePath]] accepte soit un chemin de fichier, soit un alias représentant un chemin de fichier, grâce au préfixe `@` qui permet de différentier un chemin de fichier d'un alias.

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

Reportez-vous à la documentation de l'API pour savoir si une propriété ou une méthode prend en charge les alias.


Alias prédéfinis  <span id="predefined-aliases"></span>
----------------

Yii prédéfinit un jeu d'alias pour faire référence à des chemins de fichier ou à des URL d'utilisation courante :

- `@yii`, le dossier où le fichier `BaseYii.php` se trouve – aussi appelé dossier de la base structurée de développement PHP (*framework*).
- `@app`, le  [[yii\base\Application::basePath|chemin de base]] de l'application en cours d'exécution. 
- `@runtime`, le [[yii\base\Application::runtimePath|chemin du dossier runtime]] de l'application en cours d'exécution. Valeur par défaut `@app/runtime`.
- `@webroot`, le dossier Web racine de l'application en cours d'exécution. Il est déterminé en se basant sur le dossier qui contient le [script d'entrée](structure-entry-scripts.md).
- `@web`, l'URL de base de l'application en cours d'exécution. Cet alias a la même valeur que  [[yii\web\Request::baseUrl]].
- `@vendor`, le [[yii\base\Application::vendorPath|dossier vendor de Composer]]. Valeur par défaut `@app/vendor`.
- `@bower`, le dossier racine des [paquets bower](https://bower.io/). Valeur par défaut `@vendor/bower`.
- `@npm`, le dossier racine des [paquets npm](https://www.npmjs.com/). Valeur par défaut `@vendor/npm`.

L'alias `@yii` est défini lorsque vous incluez le fichier `Yii.php` dans votre [script d'entrée](structure-entry-scripts.md). Les alias restants sont définis dans le constructeur de l'application au moment où la [configuration](concept-configurations.md) de l'application est appliquée.
 .


Alias d'extension <span id="extension-aliases"></span>
-----------------

Un alias est automatiquement défini par chacune des [extensions](structure-extensions.md) qui sont installées par Composer. Chaque alias est nommé d'après le nom de l'extension déclaré dans le fichier `composer.json`. Chaque alias représente le dossier racine du paquet. Par exemple, si vous installez l'extension `yiisoft/yii2-jui`, vous obtiendrez automatiquement l'alias `@yii/jui` défini durant l'étape d'[amorçage](runtime-bootstrapping.md), et équivalent à :

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
