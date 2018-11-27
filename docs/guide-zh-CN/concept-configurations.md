配置（Configurations）
====================

在 Yii 中，创建新对象和初始化已存在对象时广泛使用配置。
配置通常包含被创建对象的类名和一组将要赋值给对象
[属性](concept-properties.md)的初始值。
还可能包含一组将被附加到对象[事件](concept-events.md)上的句柄。
和一组将被附加到对象上的[行为](concept-behaviors.md)。

以下代码中的配置被用来创建并初始化一个数据库连接：

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

[[Yii::createObject()]] 方法接受一个配置数组并根据数组中指定的类名创建对象。
对象实例化后，剩余的参数被用来初始化对象的属性，
事件处理和行为。

对于已存在的对象，可以使用 [[Yii::configure()]] 方法根据配置去初始化其属性，
就像这样：

```php
Yii::configure($object, $config);
```

请注意，如果配置一个已存在的对象，那么配置数组中不应该包含指定类名的 `class` 元素。


## 配置的格式（Configuration Format） <span id="configuration-format"></span>

一个配置的格式可以描述为以下形式：

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

其中

* `class` 元素指定了将要创建的对象的完全限定类名。
* `propertyName` 元素指定了对象属性的初始值。键名是属性名，值是该属性对应的初始值。
  只有公共成员变量以及通过 getter/setter 定义的
  [属性](concept-properties.md)可以被配置。
* `on eventName` 元素指定了附加到对象[事件](concept-events.md)上的句柄是什么。
  请注意，数组的键名由 `on ` 前缀加事件名组成。
  请参考[事件](concept-events.md)章节了解事件句柄格式。
* `as behaviorName` 元素指定了附加到对象的[行为](concept-behaviors.md)。
  请注意，数组的键名由 `as ` 前缀加行为名组成。`$behaviorConfig` 
  值表示创建行为的配置信息，格式与我们之前描述的配置格式一样。

下面是一个配置了初始化属性值，事件句柄和行为的示例：

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("搜索的关键词： " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... 初始化属性值 ...
    ],
]
```


## 使用配置（Using Configurations） <span id="using-configurations"></span>

Yii 中的配置可以用在很多场景。本章开头我们展示了如何使用 [[Yii::creatObject()]] 
根据配置信息创建对象。本小节将介绍配置的两种
主要用法 —— 配置应用与配置小部件。


### 应用的配置（Application Configurations） <span id="application-configurations"></span>

[应用](structure-applications.md)的配置可能是最复杂的配置之一。
因为 [[yii\web\Application|application]] 类拥有很多可配置的属性和事件。
更重要的是它的 [[yii\web\Application::components|components]] 
属性可以接收配置数组并通过应用注册为组件。
以下是一个针对[基础应用模板](start-installation.md)的应用配置概要：

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

配置中没有 `class` 键的原因是这段配置应用在下面的入口脚本中，
类名已经指定了。

```php
(new yii\web\Application($config))->run();
```

更多关于应用 `components` 属性配置的信息可以查阅[应用](structure-applications.md)
以及[服务定位器](concept-service-locator.md)章节。

自版本 2.0.11 开始，系统配置支持使用 `container` 属性来配置[依赖注入容器](concept-di-container.md)
例如：

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
            // 依赖注入容器单例配置
        ]
    ]
];
```

请参考 [依赖注入容器](concept-di-container.md) 下面的 [高级应用实例](concept-di-container.md#advanced-practical-usage)
获取更多 `definitions` 和 `singletons` 配置项和实际使用的例子。


### 小部件的配置（Widget Configurations） <span id="widget-configurations"></span>

使用[小部件](structure-widgets.md)时，常常需要配置以便自定义其属性。
[[yii\base\Widget::widget()]] 和  [[yii\base\Widget::begin()]] 方法都可以用来创建小部件。
它们可以接受配置数组：

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

上述代码创建了一个小部件 `Menu` 并将其 `activateItems` 属性初始化为 false。
`item` 属性也配置成了将要显示的菜单条目。

请注意，代码中已经给出了类名 `yii\widgets\Menu`，配置数组**不应该**再包含 `class` 键。


## 配置文件（Configuration Files） <span id="configuration-files"></span>

当配置的内容十分复杂，通用做法是将其存储在一或多个 PHP 文件中，
这些文件被称为*配置文件*。一个配置文件返回的是 PHP 数组。
例如，像这样把应用配置信息存储在名为 `web.php` 的文件中：

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => require __DIR__ . '/components.php',
];
```

鉴于 `components` 配置也很复杂，上述代码把它们存储在单独的 `components.php` 文件中，并且包含在 `web.php` 里。
`components.php` 的内容如下：

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

仅仅需要 “require”，就可以取得一个配置文件的配置内容，像这样：

```php
$config = require 'path/to/web.php';
(new yii\web\Application($config))->run();
```


## 默认配置（Default Configurations） <span id="default-configurations"></span>

[[Yii::createObject()]] 方法基于[依赖注入容器](concept-di-container.md)实现。
使用 [[Yii::creatObject()]] 创建对象时，可以附加一系列**默认配置**到指定类的任何实例。
默认配置还可以在[入口脚本](runtime-bootstrapping.md)
中调用 `Yii::$container->set()` 来定义。

例如，如果你想自定义 [[yii\widgets\LinkPager]] 小部件，以便让分页器最多只显示 5 个翻页按钮（默认是 10 个），
你可以用下述代码实现：

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

不使用默认配置的话，你就得在任何使用分页器的地方，
都配置 `maxButtonCount` 的值。


## 环境常量（Environment Constants） <span id="environment-constants"></span>

配置经常要随着应用运行的不同环境更改。例如在开发环境中，
你可能使用名为 `mydb_dev` 的数据库，
而生产环境则使用 `mydb_prod` 数据库。
为了便于切换使用环境，Yii 提供了一个定义在入口脚本中的 `YII_ENV` 常量。
如下：

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

你可以把 `YII_ENV` 定义成以下任何一种值：

- `prod`：生产环境。常量 `YII_ENV_PROD` 将被看作 true。
  如果你没修改过，这就是 `YII_ENV` 的默认值。
- `dev`：开发环境。常量 `YII_ENV_DEV` 将被看作 true。
- `test`：测试环境。常量 `YII_ENV_TEST` 将被看作 true。

有了这些环境常量，你就可以根据当下应用运行环境的不同，进行差异化配置。
例如，应用可以包含下述代码只在开发环境中开启
[调试工具](tool-debugger.md)。

```php
$config = [...];

if (YII_ENV_DEV) {
    // 根据 `dev` 环境进行的配置调整
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
