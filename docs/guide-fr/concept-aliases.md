Les Alias
=========
Les alias sont utilisés pour représenter des chemins de fichier ou des URLs de sorte que vous n'ayez pas à spécifier des chemins ou des URLs explicitement dans votre projet. Un alias doit commencer par le caractère `@` de façon à le différencier des chemins de fichiers habituels et des URLs. Yii dispose déjà d'un nombre important d'alias prédéfinis. Par exemple, l'alias `@yii` représéente le chemin d'installation du framework Yii; `@web` représente l'URL de base pour l'application web courante. 



Définir des alias <span id="defining-aliases"></span>
-----------------

Vous pouvez définir un alias soit pour un chemin de fichier ou pour une URL en appelant [[Yii::setAlias()]]:

```php
// un alias pour un chemin de fichier
Yii::setAlias('@foo', '/path/to/foo');

// un alias pour une URL
Yii::setAlias('@bar', 'http://www.example.com');
```
> Note: le chemin de fichier ou l'URL cible de l'alias *ne* doit *pas* nécessairement référencer un fichier ou une ressource existante.

Etant donné un alias défini, il est possible de faire dériver un nouvel alias (sans appeler la commande [[Yii::setAlias()]]) en ajoutant une barre oblique `/` suivi d'un ou de plusieurs segments de chemin de fichier. Les alias définis via la commande [[Yii::setAlias()]] sont des *alias racines*, les alias qui en dérivent sont des *alias dérivés*. Par example, `@foo` est un alias racine, tandis que `@foo/bar/file.php` est un alias dérivé.

Il est possible de définir une alias en utilisant un autre alias (qu'il soit racine ou dérivé): 

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Les alias racines sont habituellement définit pendant l'étape d'[amorçage](runtime-bootstrapping.md). Vous pouvez par exemple appeler la commande [[Yii::setAlias()]] dans le [script d'entrée](structure-entry-scripts.md). Pour plus de commodité, [Application](structure-applications.md) propose une propriété modifiable appelée `aliases` que vous pouvez définir dans la [configuration](concept-configurations.md) de l'application:

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/chemin/vers/foo',
        '@bar' => 'http://www.example.com',
    ],
];
```

Résolution des alias <span id="resolving-aliases"></span>
--------------------

Vous pouvez appeler la méthode [[Yii::getAlias()]] pour obtenir le chemin de fichier ou l'URL qu'un alias représente. La même méthode peut aussi convertir des alias dérivés dans leur chemin de fichier ou URL correspondants: 

```php
echo Yii::getAlias('@foo');               // displays: /path/to/foo
echo Yii::getAlias('@bar');               // displays: http://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // displays: /path/to/foo/bar/file.php
```

Le chemin/URL représenté par un alias dérivé est déterminé en renplaçant la partie alias racine avec son chemain/URL correspondant dans l'alias dérivé.
> Note: La méthode [[Yii::getAlias()]] ne vérifie pas si le chemin/URL obtenu représente un fichier ou une ressource existante.

Un alias racine peut également conctenir des barres obliques `/`. La méthode [[Yii::getAlias()]] est suffisement intelligeante pour déterminer quelle part de l'alias est un alias racine et donc détermine correctement le chemin de fichier ou l'url correspondant:

```php
Yii::setAlias('@foo', '/chemin/vers/foo');
Yii::setAlias('@foo/bar', '/chemin2/bar');
Yii::getAlias('@foo/test/file.php');  // affiche /chemin/vers/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // affiche /chemin2/bar/file.php
```

Si `@foo/bar` n'est pas défini comme un alias racine, le dernier exemple affichierait  `/chemin/vers/foo/bar/file.php`.


Utilisation des  alias <span id="using-aliases"></span>
----------------------

Les alias sont reconnus en de nombreux endroits de Yii sans avoir besoin d'appeler la méthode [[Yii::getAlias()]] pour les convertir en chemin ou URLs. A titre d'exemple, la méthode [[yii\caching\FileCache::cachePath]] accepte aussi bien un chemin de fichier et un alias représentant un chemin de fichier, grâce au préfixe `@` qui permet de différencier le chemin de fichier d'un alias. 

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```
Merci de porter attention à la documentation de l'API pour vérifier si une propriété ou un paramètre d'une méthode supporte les alias.


Alias prédéfinis <span id="predefined-aliases"></span>
----------------
Yii définit une série d'alias pour faciliter le référencement des chemins de fichier et URLs souvent utilisés: 

- `@yii`, le répertoire où se situe le fichier `BaseYii.php` (aussi appelé le répertoire framework).
- `@app`, le [[yii\base\Application::basePath|chemin de base]] de l'application courante.
- `@runtime`, le [[yii\base\Application::runtimePath|le chemin d'exécution]] de l'application courante. Valeur par défaut: `@app/runtime`.
- `@webroot`, La répertoire web racine de l'application web courante.  It is determined based on the directory
  containing the [entry script](structure-entry-scripts.md).
- `@web`, l'url de base de l'application courante. Cet alias a la même valeur que la propriété [[yii\web\Request::baseUrl]].
- `@vendor`, le [[yii\base\Application::vendorPath|Le répertoire vendor de Composer]]. Valeur par défaut: `@app/vendor`.
- `@bower`, le répertoire racine qui contient [les paquets bower](http://bower.io/). Valeur par  défaut: `@vendor/bower`.
- `@npm`, le répertoire racine qui contient [les paquets npm](https://www.npmjs.org/). Valeur par défaut: `@vendor/npm`.

L'alias `@yii` est défini quand le fichier `Yii.php`est inclu dans votre [script d'entrée](structure-entry-scripts.md). Le reste des alias sont définit dans le constructeur de l'application au moment ou la [configuration](concept-configurations.md) de cette dernière est appliquée

Alias d'extension <span id="extension-aliases"></span>
-----------------

Un alias est automatiquement définit pour chaque [extension](structure-extensions.md) installée via Composer.
Chacun de ces alias est nommé par l'espace de nom (namespace) racine de l'extension tel que déclaré dans son fichier `composer.json`, et chacun pointe sur le répertoire racine du paquet. Par exemple, si vous installez l'extension `yiisoft/yii2-jui`, vous obtiendrez automatiquement un alias `@yii/jui` défini pendant la [phase d'amorçage](runtime-bootstrapping.md), équivalent à 

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```