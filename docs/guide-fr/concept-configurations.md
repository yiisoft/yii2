Configurations
==============

Les configurations sont très largement utilisées dans Yii lors de la création d'objets ou l'initialisation d'objets existants. Les configurations contiennent généralement le nom de la classe de l'objet en cours de création, et une liste de valeurs initiales qui doivent être assignées aux [propriétés](concept-properties.md) de l'objet. Elles peuvent aussi comprendre une liste de gestionnaires qui doivent être attachés aux [événements](concept-events.md) de l'objet et/ou une liste de [comportements](concept-behaviors.md) qui doivent être attachés à l'objet. 

Dans ce qui suit, une configuration est utilisée pour créer et initialiser une connexion à une base de données :

```php
$config = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

$db = Yii::createObject($config);
```

La méthode [[Yii::createObject()]] prend un tableau de configuration en tant qu'argument et crée un objet en instanciant la classe nommée dans la configuration. Lorsque l'objet est instancié, le reste de la configuration est utilisé pour initialiser les propriétés de l'objet, ses gestionnaires d'événement et ses comportements. 

Si vous disposez déjà d'un objet, vous pouvez utiliser la méthode [[Yii::configure()]] pour initialiser les propriétés de l'objet avec un tableau de configuration :

```php
Yii::configure($object, $config);
```

Notez bien que dans ce cas, le tableau de configuration ne doit pas contenir d'élément `class`.


## Format d'une configuration <span id="configuration-format"></span>

Le format d'une configuration peut être formellement décrit comme suit :

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

où

* L'élément `class` spécifie un nom de classe pleinement qualifié pour l'objet à créer.
* L'élément `propertyName` spécifie les valeurs initiales d'une propriété nommé property. Les clés sont les noms de propriété et les valeurs correspondantes les valeurs initiales. Seules les variables membres publiques et les [propriétés](concept-properties.md) définies par des méthodes d'obtention (*getters*) et/ou des méthodes d'assignation (*setters*) peuvent être configurées.
* Les éléments `on eventName` spécifient quels gestionnaires doivent être attachés aux [événements](concept-events.md) de l'objet. Notez que les clés du tableau sont formées en préfixant les noms d'événement par `on`. Reportez-vous à la section [événements](concept-events.md) pour connaître les formats des gestionnaires d'événement pris en charge.
* L'élément `as behaviorName` spécifie quels [comportements](concept-behaviors.md) doivent être attachés à l'objet. Notez que les clés du tableau sont formées en préfixant les noms de comportement par `as ` ; la valeur `$behaviorConfig` représente la configuration pour la création du comportement, comme une configuration normale décrite ici. 

Ci-dessous, nous présentons un exemple montrant une configuration avec des valeurs initiales de propriétés, des gestionnaires d'événement et des comportements.

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("Keyword searched: " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... property init values ...
    ],
]
```


## Utilisation des configurations <span id="using-configurations"></span>

Les configurations sont utilisées en de nombreux endroits dans Yii. Au début de cette section, nous avons montré comment créer un objet obéissant à une configuration en utilisant la méthode [[Yii::createObject()]]. Dans cette sous-section, nous allons décrire les configurations d'applications et les configurations d'objets graphiques (*widget*) – deux utilisations majeures des configurations.


### Configurations d'applications <span id="application-configurations"></span>

La configuration d'une [application](structure-applications.md) est probablement l'un des tableaux les plus complexes dans Yii. Cela est dû au fait que la classe [[yii\web\Application|application]] dispose d'un grand nombre de propriétés et événements configurables. De première importance, se trouve sa propriété [[yii\web\Application::components|components]] qui peut recevoir un tableau de configurations pour créer des composants qui sont enregistrés durant l'exécution de l'application. Ce qui suit est un résumé de la configuration de l'application du [modèle de projet *basic*](start-installation.md).

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
        'log' => [
            'class' => 'yii\log\Dispatcher',
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=stay2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
```

La configuration n'a pas de clé `class`. Cela tient au fait qu'elle est utilisée comme indiqué ci-dessous dans un [script d'entrée](structure-entry-scripts.md), dans lequel le nom de la classe est déjà donné :

```php
(new yii\web\Application($config))->run();
```

Plus de détails sur la configuration de la propriété `components` d'une application sont donnés dans la section [Applications](structure-applications.md) et dans la section [Localisateur de services](concept-service-locator.md).

Depuis la version 2.0.11, la configuration de l'application prend en charge la configuration du [Conteneur d'injection de dépendances](concept-di-container.md)
via la propriété `container`. Par exemple :

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'container' => [
        'definitions' => [
            'yii\widgets\LinkPager' => ['maxButtonCount' => 5]
        ],
        'singletons' => [
            // Configuration du singleton Dependency Injection Container
        ]
    ]
];
```

Pour en savoir plus sur les valeurs possibles des tableaux de configuration de   `definitions` et `singletons`  et avoir des exemples de la vie réelle, reportez-vous à la sous-section [Utilisation pratique avancée](concept-di-container.md#advanced-practical-usage) de l'article 
[Conteneur d'injection de dépendances](concept-di-container.md).

### Configurations des objets graphiques <span id="widget-configurations"></span>

Lorsque vous utilisez des [objets graphiques](structure-widgets.md), vous avez souvent besoin d'utiliser des configurations pour personnaliser les propriétés de ces objets graphiques. Les méthodes [[yii\base\Widget::widget()]] et [[yii\base\Widget::begin()]] peuvent toutes deux être utilisées pour créer un objet graphique. Elles acceptent un tableau de configuration, comme celui qui suit : 

```php
use yii\widgets\Menu;

echo Menu::widget([
    'activateItems' => false,
    'items' => [
        ['label' => 'Home', 'url' => ['site/index']],
        ['label' => 'Products', 'url' => ['product/index']],
        ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
    ],
]);
```

La configuration ci-dessus crée un objet graphique nommé `Menu` et initialise sa propriété `activateItems` à `false` (faux). La propriété `items` est également configurée avec les items de menu à afficher.

Notez que, comme le nom de classe est déjà donné, le tableau de configuration ne doit PAS contenir de clé `class`. 

## Fichiers de configuration <span id="configuration-files"></span>

Lorsqu'une configuration est très complexe, une pratique courante est de la stocker dans un ou plusieurs fichiers PHP appelés *fichiers de configuration*. Un fichier de configuration retourne un tableau PHP représentant la configuration. Par exemple, vous pouvez conserver une configuration d'application dans un fichier nommé `web.php`, comme celui qui suit :

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => require __DIR__ . '/components.php',
];
```

Parce que la configuration `components` et elle aussi complexe, vous pouvez la stocker dans un fichier séparé appelé `components.php` et requérir ce fichier dans `web.php` comme c'est montré ci-dessus. Le contenu de `components.php` ressemble à ceci :

```php
return [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
    ],
    'log' => [
        'class' => 'yii\log\Dispatcher',
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
            ],
        ],
    ],
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=stay2',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ],
];
```

Pour obtenir une configuration stockée dans un fichier de configuration, il vous suffit requérir ce fichier avec "require", comme ceci :

```php
$config = require 'path/to/web.php';
(new yii\web\Application($config))->run();
```


## Configurations par défaut <span id="default-configurations"></span>

La méthode [[Yii::createObject()]] est implémentée sur la base du [conteneur d'injection de dépendances](concept-di-container.md). Cela vous permet de spécifier un jeu de configurations dites *configurations par défaut* qui seront appliquées à TOUTES les instances des classes spécifiées lors de leur création en utilisant [[Yii::createObject()]]. Les configurations par défaut peuvent être spécifiées en appelant `Yii::$container->set()` dans le code d'[amorçage](runtime-bootstrapping.md).

Par exemple, si vous voulez personnaliser l'objet graphique [[yii\widgets\LinkPager]] de façon à ce que TOUS les fonctions de mise en page (pagers) affichent au plus 5 boutons de page (la valeur par défaut est 10), vous pouvez utiliser le code suivant pour atteindre ce but : 

```php
\Yii::$container->set('yii\widgets\LinkPager', [
   'maxButtonCount' => 5,
]);
```

Sans les configurations par défaut, vous devez configurer la propriété `maxButtonCount` partout où vous utilisez un pagineur.


## Constantes d'environment <span id="environment-constants"></span>

Les configurations varient souvent en fonction de l'environnement dans lequel les applications s'exécutent. Par exemple, dans l'environnement de développement, vous désirez peut être utiliser la base de données nommée `mydb_dev`, tandis que sur un serveur en production, vous désirez utiliser la base de données nommée `mydb_prod`. Pour faciliter le changement d'environnement, Yii fournit une constante nommée `YII_ENV` que vous pouvez définir dans le [script d'entrée](structure-entry-scripts.md) de votre application. Par exemple :

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

Vous pouvez assigner à `YII_ENV` une des valeurs suivantes :

- `prod`: environnement de production. La constante `YII_ENV_PROD` est évaluée comme étant `true` (vrai). C'est la valeur par défaut de `YII_ENV`.
- `dev`: environnement de développement. La constante `YII_ENV_DEV` est évaluée comme étant `true` (vrai).
- `test`: environnement de test. La constante `YII_ENV_TEST` est évaluée comme étant `true` (vrai).

Avec ces constantes d'environnement, vous pouvez spécifier les configurations en fonction de l'environnement courant. Par exemple, votre configuration d'application peut contenir le code suivant pour activer la [barre de débogage et le module de débogage](tool-debugger.md) dans l'environnement de développement seulement :

```php
$config = [...];

if (YII_ENV_DEV) {
    // ajustement de la configuration pour l'environnement 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
