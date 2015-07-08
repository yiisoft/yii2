服务定位器
===============

服务定位器是一个了解如何提供各种应用所需的服务（或组件）的对象。在服务定位器中，每个组件都只有一个单独的实例，并通过ID 唯一地标识。用这个 ID 就能从服务定位器中得到这个组件。

在 Yii 中，服务定位器是 [[yii\di\ServiceLocator]] 或其子类的一个实例。

最常用的服务定位器是**application（应用）**对象，可以通过 `\Yii::$app` 访问。它所提供的服务被称为**application components（应用组件）**，比如：`request`、`response`、`urlManager` 组件。可以通过服务定位器所提供的功能，非常容易地配置这些组件，或甚至是用你自己的实现替换掉他们。

除了 application 对象，每个模块对象本身也是一个服务定位器。

要使用服务定位器，第一步是要注册相关组件。组件可以通过 [[yii\di\ServiceLocator::set()]] 方法进行注册。以下的方法展示了注册组件的不同方法：

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// 通过一个可用于创建该组件的类名，注册 "cache" （缓存）组件。
$locator->set('cache', 'yii\caching\ApcCache');

// 通过一个可用于创建该组件的配置数组，注册 "db" （数据库）组件。
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// 通过一个能返回该组件的匿名函数，注册 "search" 组件。
$locator->set('search', function () {
    return new app\components\SolrService;
});

// 用组件注册 "pageCache" 组件
$locator->set('pageCache', new FileCache);
```

一旦组件被注册成功，你可以任选以下两种方式之一，通过它的 ID 访问它：

```php
$cache = $locator->get('cache');
// 或者
$cache = $locator->cache;
```

如上所示， [[yii\di\ServiceLocator]] 允许通过组件 ID 像访问一个属性值那样访问一个组件。当你第一次访问某组件时，[[yii\di\ServiceLocator]] 会通过该组件的注册信息创建一个该组件的实例，并返回它。之后，如果再次访问，则服务定位器会返回同一个实例。

你可以通过 [[yii\di\ServiceLocator::has()]] 检查某组件 ID 是否被注册。若你用一个无效的 ID 调用 [[yii\di\ServiceLocator::get()]]，则会抛出一个异常。

因为服务定位器，经常会在创建时附带[配置信息](concept-configurations.md)，因此我们提供了一个可写的属性，名为 [[yii\di\ServiceLocator::setComponents()|components]]，这样就可以配置该属性，或一次性注册多个组件。下面的代码展示了如何用一个配置数组，配置一个应用并注册"db"，"cache" 和 "search" 三个组件：
```php
return [
    // ...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],
        'cache' => 'yii\caching\ApcCache',
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
];
```
