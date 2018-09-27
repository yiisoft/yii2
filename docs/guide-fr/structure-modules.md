Modules
=======

Les modules sont des unités logicielles auto-suffisantes constituées de [modèles](structure-models.md), [vues](structure-views.md),
[contrôleurs](structure-controllers.md) et autres composants de prise en charge. L'utilisateur final peut accéder aux contrôleurs dans un module lorsqu'il est installé dans une [application](structure-applications.md). Pour ces raisons, les modules sont souvent regardés comme de mini-applications. Les modules diffèrent des [applications](structure-applications.md) par le fait que les modules ne peuvent être déployés seuls et doivent résider dans une application. 

## Création de modules <span id="creating-modules"></span>

Un module est organisé comme un dossier qui est appelé le [[yii\base\Module::basePath|dossier de base (*basePath*)]] du module. Dans ce dossier, se trouvent des sous-dossiers, tels que `controllers`, `models` et `views`, qui contiennent les contrôleurs, les modèles , les vues et d'autres parties de code, juste comme une application. L'exemple suivant présente le contenu d'un module : 

```
forum/
    Module.php                   le fichier de classe du module 
    controllers/                 contient les fichiers de classe des contrôleurs
        DefaultController.php    le fichier de classe de contrôleur par défaut
    models/                      contient les fichiers de classe des modèles
    views/                       contient les fichiers de contrôleur, de vue et de disposition 
        layouts/                 contient les fichiers de diposition
        default/                 contient les fichiers de vues pour le contrôleur par défaut
            index.php            le fichier de vue index 
```


### Classes de module <span id="module-classes"></span>

Chacun des modules doit avoir une classe unique de module qui étend [[yii\base\Module]]. La classe doit être située directement dans le [[yii\base\Module::basePath|dossier de base]] du module et doit être [auto-chargeable](concept-autoloading.md). Quand un module est accédé, une instance unique de la classe de module correspondante est créée. Comme les [instances d'application](structure-applications.md), les instances de module sont utilisées pour partager des données et des composants.

L'exemple suivant montre à quoi une classe de module peut ressembler :

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  other initialization code ...
    }
}
```

La méthode `init()` contient un code volumineux pour initialiser les propriétés du module. Vous pouvez également les sauvegarder sous forme de [configuration](concept-configurations.md) et charger cette configuration avec le code suivant dans la méthode `init()`:

```php
public function init()
{
    parent::init();
    // initialise le module à partir de la configuration chargée depuis config.php
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

où le fichier de configuration `config.php` peut avoir le contenu suivant, similaire à celui d'une [configuration d'application](structure-applications.md#application-configurations).

```php
<?php
return [
    'components' => [
        // liste des configurations de composant
    ],
    'params' => [
        // liste des paramètres
    ],
];
```


### Contrôleurs dans les modules <span id="controllers-in-modules"></span>

Lorsque vous créez des contrôleurs dans un module, une convention est de placer les classes de contrôleur dans le sous-espace de noms `controllers` dans l'espace de noms de la classe du module.  Cela signifie également que les fichiers de classe des contrôleur doivent être placés dans le dossier `controllers` dans le [[yii\base\Module::basePath|dossier de base]] du module. Par exemple, pour créer un contrôleur `post` dans le module `forum` présenté dans la section précédente, vous devez déclarer la classe de contrôleur comme ceci :

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

Vous pouvez personnaliser l'espace de noms des classes de contrôleur en configurant la propriété [[yii\base\Module::controllerNamespace]]. Dans le cas où certains contrôleurs sont en dehors de cet espace de noms, vous pouvez les rendre accessibles en configurant la propriété  [[yii\base\Module::controllerMap]] comme vous le feriez dans une [application](structure-applications.md#controllerMap).


### Vues dans les modules <span id="views-in-modules"></span>

Les vues dans les modules doivent être placées dans le dossier `views` du [[yii\base\Module::basePath|dossier de base (*base path*)]] du module. Quant aux vues rendues par un contrôleur du module, elles doivent être placées dans le dossier `views/ControllerID`, où `ControllerID` fait référence à l'[identifiant du contrôleur](structure-controllers.md#routes). Par exemple, si la classe du contrôleur est `PostController`, le dossier doit être `views/post` dans le [[yii\base\Module::basePath|dossier de base]] du module.

Un module peut spécifier une [disposition](structure-views.md#layouts) qui s'applique aux vues rendues par les contrôleurs du module. La disposition doit être mise dans le dossier `views/layouts` par défaut, et vous devez configurer la propriété [[yii\base\Module::layout]] pour qu'elle pointe sur le nom de la disposition. Si vous ne configurez pas la propriété `layout` c'est la disposition de l'application qui est utilisée à sa place.


### Commande de console dans les modules <span id="console-commands-in-modules"></span>

Votre module peut aussi déclarer des commandes, qui sont accessibles via le mode [Console](tutorial-console.md).

Afin que l'utilitaire de ligne de commande reconnaisse vos commandes, vous devez changer la propriété [[yii\base\Module::controllerNamespace (espace de noms du contrôleur)]] lorsque Yii est exécuté en mode console, et le diriger sur votre espace de noms de commandes. 

Une manière de réaliser cela est de tester le type d'instance de l'application Yii dans la méthode `init` du module :

```php
public function init()
{
    parent::init();
    if (Yii::$app instanceof \yii\console\Application) {
        $this->controllerNamespace = 'app\modules\forum\commands';
    }
}
```

Vos commandes seront disponibles en ligne de commande en utilisant la route suivante :

```
yii <module_id>/<command>/<sub_command>
```

## Utilisation des modules <span id="using-modules"></span>

Pour utiliser un module dans une  application, il vous suffit de configurer l'application en listant le module dans la propriété [[yii\base\Application::modules|modules]] de l'application. Le code qui suit dans la [configuration de l'application](structure-applications.md#application-configurations) permet l'utilisation du module `forum` :

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... autres éléments de  configuration pour le module ...
        ],
    ],
]
```

La propriété [[yii\base\Application::modules|modules]] accepte un tableau de configurations de module. Chaque clé du tableau représente un *identifiant de module* qui distingue ce module parmi les autres modules de l'application, et la valeur correspondante est une  [configuration](concept-configurations.md) pour la création du module.


### Routes <span id="routes"></span>

Les [routes](structure-controllers.md#routes) sont utilisées pour accéder aux contrôleurs d'un module comme elles le sont pour accéder aux contrôleurs d'une application. Une route, pour un contrôleur d'un module, doit commencer par l'identifiant du module, suivi de l'[identifiant du contrôleur](structure-controllers.md#controller-ids) et de [identifiant de l'action](structure-controllers.md#action-ids). Par exemple, si une application utilise un module nommé `forum`, alors la route `forum/post/index` représente l'action `index` du contrôleur `post` du module. Si la route ne contient que l'identifiant du module, alors la propriété [[yii\base\Module::defaultRoute]], dont la valeur par défaut est `default`, détermine quel contrôleur/action utiliser. Cela signifie que la route `forum` représente le contrôleur `default` dans le module `forum`.

Le gestionnaire d'URL du module doit être ajouté avant que la fonction [[yii\web\UrlManager::parseRequest()]] ne soit exécutée. Cela siginifie que le faire dans la fonction `init()` du module ne fonctionne pas parce que le module est initialisé après que les routes ont été résolues. Par conséquent, les règles doivent être ajoutées à l'[étape d'amorçage](structure-extensions.md#bootstrapping-classes). C'est également une bonne pratique d'empaqueter les règles d'URL du module dans [[\yii\web\GroupUrlRule]].

Dans le cas où un module est utilisé pour [versionner une API](rest-versioning.md), ses règles d'URL doivent être ajoutées directement dans la section `urlManager` de la configuration de l'application.

### Accès aux modules <span id="accessing-modules"></span>

Dans un module, souvent, il arrive que vous ayez besoin d'une instance de la [classe du module](#module-classes) de façon à pouvoir accéder à l'identifiant du module, à ses paramètres, à ses composants, etc. Vous pouvez le faire en utilisant l'instruction suivante :

```php
$module = MyModuleClass::getInstance();
```

dans laquelle `MyModuleClass` fait référence au nom de la classe du module qui vous intéresse. La méthode `getInstance()` retourne l'instance  de la classe du module actuellement requis. Si le module n'est pas requis, la méthode retourne `null`. Notez que vous n'avez pas besoin de créer manuellement une nouvelle instance de la classe du module parce que celle-ci serait différente de celle créée par Yii en réponse à la requête.

> Info: lors du développement d'un module, vous ne devez pas supposer que le module va utiliser un identifiant fixe. Cela tient au fait qu'un module peut être associé à un identifiant arbitraire lorsqu'il est utilisé dans une application ou dans un autre module. Pour obtenir l'identifiant du module, vous devez utiliser l'approche ci-dessus pour obtenir d'abord une instance du module, puis obtenir l'identifiant via `$module->id`.

Vous pouvez aussi accéder à l'instance d'un module en utilisant les approches suivantes :

```php
// obtenir le module fils dont l'identifiant est "forum"
$module = \Yii::$app->getModule('forum');

// obtenir le  module auquel le contrôleur actuellement requis appartient 
$module = \Yii::$app->controller->module;
```

La première approche n'est utile que lorsque vous connaissez l'identifiant du module, tandis que la seconde est meilleure lorsque vous connaissez le contrôleur actuellement requis. 

Une fois que vous disposez de l'instance du module, vous pouvez accéder aux paramètres et aux composants enregistrés avec le module. Par exemple :

```php
$maxPostCount = $module->params['maxPostCount'];
```


### Modules faisant partie du processus d'amorçage <span id="bootstrapping-modules"></span>

Il se peut que certains modules doivent être exécutés pour chacune des requêtes. Le module [[yii\debug\Module|debug]] en est un exemple. Pour que des modules soit exécutés pour chaque requête, vous devez les lister dans la propriété [[yii\base\Application::bootstrap|bootstrap]] de l'application. 

Par exemple, la configuration d'application suivante garantit que le module `debug` est chargé à chaque requête : 

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```


## Modules imbriqués <span id="nested-modules"></span>

Les modules peuvent être imbriqués sur un nombre illimité de niveaux. C'est à dire qu'un module pour contenir un autre module qui contient lui-même un autre module. Nous parlons alors de *module parent* pour le module englobant, et de *module enfant* pour le module contenu. Les modules enfants doivent être déclarés dans la propriété [[yii\base\Module::modules|modules]] de leur module parent. Par exemple :

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // Vous devriez envisager l'utilisation d'un espace de noms plus court ici !
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

La route vers un contrôleur inclus dans un module doit inclure les identifiants de tous ses modules ancêtres. Par exemple, la route `forum/admin/dashboard/index` représente l'action `index` du contrôleur `dashboard` dans le module `admin` qui est un module enfant du module `forum`.

> Info: la méthode [[yii\base\Module::getModule()|getModule()]] ne retourne que le module enfant appartenant directement à son parent.  La propriété [[yii\base\Application::loadedModules]] tient à jour une liste des modules chargés, y compris les enfant directs et les enfants des générations suivantes, indexée par le nom de classe. 

## Accès aux composants depuis l'intérieur des modules 

Depuis la version 2.0.13, les modules prennent en charge la [traversée des arbres](concept-service-locator.md#tree-traversal). Cela permet aux développeurs de modules de faire référence à des composants (d'application) via le localisateur de services qui se trouve dans leur module. 
Cela signifie qu'il est préférable d'utiliser `$module->get('db')` plutôt que `Yii::$app->get('db')`.
L'utilisateur d'un module est capable de spécifier un composant particulier pour une utilisation dans le module dans le cas où une configuration différente du composant est nécessaire. 

Par exemple, considérons cette partie de la configuration d'une application :


```php
'components' => [
    'db' => [
        'tablePrefix' => 'main_',
        'class' => Connection::class,
        'enableQueryCache' => false
    ],
],
'modules' => [
    'mymodule' => [
        'components' => [
            'db' => [
                'tablePrefix' => 'module_',
                'class' => Connection::class
            ],
        ],
    ],
],
```

Les tables de base de données de l'application seront préfixées par `main_`, tandis que les tables de tous les modules seront préfixées par `module_`.
Notez cependant que la configuration ci-dessus n'est pas fusionnée; le composant des modules par exemple aura le cache de requêtes activé puisque c'est la valeur par défaut.



## Meilleures pratiques <span id="best-practices"></span>

L'utilisation des modules est préférable dans les grosses applications dont les fonctionnalités peuvent être réparties en plusieurs groupes, consistant chacun en un jeu de fonctionnalités liées d'assez près. Chacune de ces fonctionnalités peut être conçue comme un module développé et maintenu par un développeur ou une équipe spécifique. 

Les modules sont aussi un bon moyen de réutiliser du code au niveau des groupes de fonctionnalités. Quelques fonctionnalité d'usage courant, telles que la gestion des utilisateurs, la gestion des commentaires, etc. peuvent être développées en tant que modules ce qui facilite leur réutilisation dans les projets suivants. 
