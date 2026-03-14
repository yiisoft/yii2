依赖注入容器（Dependency Injection Container）
===========================================

依赖注入（Dependency Injection，DI）容器就是一个对象，它知道怎样初始化并配置对象及其依赖的所有对象。
[Martin 的文章](https://martinfowler.com/articles/injection.html) 已经解释了 DI 容器为什么很有用。
这里我们主要讲解 Yii 提供的 DI 容器的使用方法。


依赖注入（Dependency Injection） <span id="dependency-injection"></span>
-----------------------------

Yii 通过 [[yii\di\Container]] 类提供 DI 容器特性。
它支持如下几种类型的依赖注入：

* 构造方法注入;
* 方法注入;
* Setter 和属性注入;
* PHP 回调注入.


### 构造方法注入（Constructor Injection） <span id="constructor-injection"></span>

在参数类型提示的帮助下，DI 容器实现了构造方法注入。当容器被用于创建一个新对象时，
类型提示会告诉它要依赖什么类或接口。
容器会尝试获取它所依赖的类或接口的实例，
然后通过构造器将其注入新的对象。例如：

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// 上面的代码等价于：
$bar = new Bar;
$foo = new Foo($bar);
```


### 方法注入（Method Injection） <span id="method-injection"></span>

通常，类的依赖关系传递给构造函数，并且在整个生命周期中都可以在类内部使用。
通过方法注入，可以提供仅由类的单个方法需要的依赖关系，
并将其传递给构造函数可能不可行，或者可能会在大多数用例中导致太多开销。

类方法可以像下面例子中的 `doSomething()` 方法一样定义：

```php
class MyClass extends \yii\base\Component
{
    public function __construct(/*Some lightweight dependencies here*/, $config = [])
    {
        // ...
    }

    public function doSomething($param1, \my\heavy\Dependency $something)
    {
        // do something with $something
    }
}
```

你可以自己通过一个实例 `\my\heavy\Dependency` 调用这个方法或使用 [[yii\di\Container::invoke()]] 如下：

```php
$obj = new MyClass(/*...*/);
Yii::$container->invoke([$obj, 'doSomething'], ['param1' => 42]); // $something will be provided by the DI container
```

### Setter 和属性注入（Setter and Property Injection） <span id="setter-and-property-injection"></span>

Setter 和属性注入是通过[配置](concept-configurations.md)提供支持的。
当注册一个依赖或创建一个新对象时，你可以提供一个配置，
该配置会提供给容器用于通过相应的 Setter 或属性注入依赖。
例如：

```php
use yii\base\BaseObject;

class Foo extends BaseObject
{
    public $bar;

    private $_qux;

    public function getQux()
    {
        return $this->_qux;
    }

    public function setQux(Qux $qux)
    {
        $this->_qux = $qux;
    }
}

$container->get('Foo', [], [
    'bar' => $container->get('Bar'),
    'qux' => $container->get('Qux'),
]);
```

> Info: [[yii\di\Container::get()]] 方法将其第三个参数作为配置数组应用于正在创建的对象。
  如果该类实现 [[yii\base\Configurable]] 接口（例如
  [[yii\base\BaseObject]]），则配置数组将作为最后一个参数传递给类构造函数；
  否则，将在创建对象*后*应用该配置。


### PHP 回调注入（PHP Callable Injection） <span id="php-callable-injection"></span>

在这种情况下，容器将使用已注册的 PHP 回调来构建类的新实例。
每次调用 [[yii\di\Container::get()]] ，相应的回调将被调用。
调用方负责解析依赖项，并适当地将它们注入到新创建的对象中。
例如,

```php
$container->set('Foo', function () {
    $foo = new Foo(new Bar);
    // ... 其他初始化 ...
    return $foo;
});

$foo = $container->get('Foo');
```

要省略构建新对象的复杂逻辑，可以使用静态类方法作为可调用的方法。例如，

```php
class FooBuilder
{
    public static function build()
    {
        $foo = new Foo(new Bar);
        // ... 其他初始化 ...
        return $foo;
    }
}

$container->set('Foo', ['app\helper\FooBuilder', 'build']);

$foo = $container->get('Foo');
```

这样做的话，想要配置 `Foo` 类的人不再需要知道它是如何构建的。


注册依赖关系（Registering Dependencies） <span id="registering-dependencies"></span>
------------------------------------

可以用 [[yii\di\Container::set()]] 注册依赖关系。注册会用到一个依赖关系名称和一个依赖关系的定义。
依赖关系名称可以是一个类名，一个接口名或一个别名。
依赖关系的定义可以是一个类名，一个配置数组，或者一个 PHP 回调。

```php
$container = new \yii\di\Container;

// 注册一个同类名一样的依赖关系，这个可以省略。
$container->set('yii\db\Connection');

// 注册一个接口
// 当一个类依赖这个接口时，
//相应的类会被初始化作为依赖对象。
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// 注册一个别名。
// 你可以使用 $container->get('foo') 创建一个 Connection 实例
$container->set('foo', 'yii\db\Connection');

// 通过配置注册一个类
// 通过 get() 初始化时，配置将会被使用。
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// 通过类的配置注册一个别名
// 这种情况下，需要通过一个 “class” 元素指定这个类
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// 注册一个 PHP 回调
// 每次调用 $container->get('db') 时，回调函数都会被执行。
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// 注册一个组件实例
// $container->get('pageCache') 每次被调用时都会返回同一个实例。
$container->set('pageCache', new FileCache);
```

> Tip: 如果依赖关系名称和依赖关系的定义相同，
  则不需要通过 DI 容器注册该依赖关系。

通过 `set()` 注册的依赖关系，在每次使用时都会产生一个新实例。
可以使用 [[yii\di\Container::setSingleton()]] 
注册一个单例的依赖关系：

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


解决依赖关系（Resolving Dependencies） <span id="resolving-dependencies"></span>
----------------------------------

注册依赖关系后，就可以使用 DI 容器创建新对象了。容器会自动解决依赖关系，
将依赖实例化并注入新创建的对象。依赖关系的解决是递归的，
如果一个依赖关系中还有其他依赖关系，
则这些依赖关系都会被自动解决。

可以使用 [[yii\di\Container::get()]] 创建新的对象。
该方法接收一个依赖关系名称，它可以是一个类名，
一个接口名或一个别名。依赖关系名或许是通过 `set()` 或 `setSingleton()` 注册的。
你可以随意地提供一个类的构造器参数列表和一个
[configuration](concept-configurations.md) 用于配置新创建的对象。

例如：

```php
// "db" 是前面定义过的一个别名
$db = $container->get('db');

// 等价于： $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

代码背后，DI 容器做了比创建对象多的多的工作。
容器首先将检查类的构造方法，找出依赖的类或接口名，
然后自动递归解决这些依赖关系。

如下代码展示了一个更复杂的示例。`UserLister` 类依赖一个实现了 `UserFinderInterface` 接口的对象；
`UserFinder` 类实现了这个接口，并依赖于一个 `Connection` 对象。
所有这些依赖关系都是通过类构造器参数的类型提示定义的。
通过属性依赖关系的注册，DI 容器可以自动解决这些依赖关系并能通过一个
简单的 `get('userLister')` 调用创建一个新的 `UserLister` 实例。

```php
namespace app\models;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends BaseObject implements UserFinderInterface
{
    public $db;

    public function __construct(Connection $db, $config = [])
    {
        $this->db = $db;
        parent::__construct($config);
    }

    public function findUser()
    {
    }
}

class UserLister extends BaseObject
{
    public $finder;

    public function __construct(UserFinderInterface $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
}

$container = new Container;
$container->set('yii\db\Connection', [
    'dsn' => '...',
]);
$container->set('app\models\UserFinderInterface', [
    'class' => 'app\models\UserFinder',
]);
$container->set('userLister', 'app\models\UserLister');

$lister = $container->get('userLister');

// 等价于:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


实践中的运用（Practical Usage） <span id="practical-usage"></span>
---------------------------

当在应用程序的[入口脚本](structure-entry-scripts.md)中引入 `Yii.php` 文件时，
Yii 就创建了一个 DI 容器。这个 DI 容器可以通过 [[Yii::$container]] 访问。
当调用 [[Yii::createObject()]] 时，此方法实际上会调用这个容器的 [[yii\di\Container::get()|get()]] 方法创建新对象。
如上所述，DI 容器会自动解决依赖关系（如果有）并将其注入新创建的对象中。
因为 Yii 在其多数核心代码中都使用了[[Yii::createObject()]] 创建新对象，
所以你可以通过 [[Yii::$container]] 全局性地自定义这些对象。

例如，你可以全局性自定义 [[yii\widgets\LinkPager]] 中分页按钮的默认数量:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

这样如果你通过如下代码在一个视图里使用这个挂件，
它的 `maxButtonCount` 属性就会被初始化为 5 而不是类中定义的默认值 10。

```php
echo \yii\widgets\LinkPager::widget();
```

然而你依然可以覆盖通过 DI 容器设置的值：

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

> Note: 在部件调用中给出的属性将始终覆盖DI容器中的定义。
> 即使您指定了一个数组，例如 `'options' => ['id' => 'mypager']` 这些将不会与其他选项合并，
> 而是替换它们。

另一个例子是借用 DI 容器中自动构造方法注入带来的好处。
假设你的控制器类依赖一些其他对象，例如一个旅馆预订服务。
你可以通过一个构造器参数声明依赖关系，然后让 DI 容器帮你自动解决这个依赖关系。

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\BookingInterface;

class HotelController extends Controller
{
    protected $bookingService;

    public function __construct($id, $module, BookingInterface $bookingService, $config = [])
    {
        $this->bookingService = $bookingService;
        parent::__construct($id, $module, $config);
    }
}
```

如果你从浏览器中访问这个控制器，你将看到一个报错信息，提醒你 `BookingInterface` 无法被实例化。
这是因为你需要告诉 DI 容器怎样处理这个依赖关系。

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

现在如果你再次访问这个控制器，一个 `app\components\BookingService` 
的实例就会被创建并被作为第三个参数注入到控制器的构造器中。

高级实用性（Advanced Practical Usage） <span id="advanced-practical-usage"></span>
------------------------------------

比如说我们在 API 应用方面工作：

- `app\components\Request` 类继承了 `yii\web\Request` 并提供了额外的功能
- `app\components\Response` 类继承了 `yii\web\Response` 并且在创建时应该将 `format`
  属性设置为 `json`
- `app\storage\FileStorage` 和 `app\storage\DocumentsReader`
  用于处理位于某些文件存储中的文档的某些逻辑：
  
  ```php
  class FileStorage
  {
      public function __construct($root) {
          // whatever
      }
  }
  
  class DocumentsReader
  {
      public function __construct(FileStorage $fs) {
          // whatever
      }
  }
  ```

可以一次配置多个定义，将配置数组传递给
[[yii\di\Container::setDefinitions()|setDefinitions()]] 或 [[yii\di\Container::setSingletons()|setSingletons()]] 方法。
遍历配置数组，将分别为每个对象分别调用 [[yii\di\Container::set()|set()]]
或 [[yii\di\Container::setSingleton()|setSingleton()]] 方法。

配置数组格式为：

 - `key`：类名称，接口名称或别名。 该 key 将作为第一个参数
 `$class` 传递给 [[yii\di\Container::set()|set()]] 方法。
 - `value`：与 `$class` 关联的定义。`$definition` 参数的值可能在 [[yii\di\Container::set()|set()]]
 文档中描述。`$definition` 将作为第二个参数传递给 [[set()]]
 方法。

例如，让我们配置我们的容器以遵循上述要求：

```php
$container->setDefinitions([
    'yii\web\Request' => 'app\components\Request',
    'yii\web\Response' => [
        'class' => 'app\components\Response',
        'format' => 'json'
    ],
    'app\storage\DocumentsReader' => function () {
        $fs = new app\storage\FileStorage('/var/tempfiles');
        return new app\storage\DocumentsReader($fs);
    }
]);

$reader = $container->get('app\storage\DocumentsReader'); 
// 将按照配置中的描述创建 DocumentReader 对象及其依赖关系
```

> Tip: 自 2.0.11 版以后，可以使用应用程序配置以声明式风格配置容器。
查看[配置](concept-configurations.md)指南文章的
[应用程序配置](concept-configurations.md#application-configurations)小节。

一切正常，但如果我们需要创建 `Document Writer` 类，
我们将复制粘贴创建 `FileStorage` 对象的行，这显然不是最聪明的方式。

如 [解决依赖关系](#resolving-dependencies) 子节中所述，[[yii\di\Container::set()|set()]]
和 [[yii\di\Container::setSingleton()|setSingleton()]] 可以选择将依赖项的构造函数参数作为第三个参数。
要设置构造函数参数，可以使用以下配置数组格式：

 - `key`：类名称，接口名称或别名。该 key 将作为第一个参数
 `$class` 传递给 [[yii\di\Container::set()|set()]] 方法。
 - `value`：两个元素的数组。第一个元素将传递给 [[yii\di\Container::set()|set()]] 方法
 作为第二个参数 `$definition`，第二个元素为 `$params`。

让我们来修改我们的例子：

```php
$container->setDefinitions([
    'tempFileStorage' => [ // 我们为了方便创建了一个别名
        ['class' => 'app\storage\FileStorage'],
        ['/var/tempfiles'] // 可以从一些配置文件中获取
    ],
    'app\storage\DocumentsReader' => [
        ['class' => 'app\storage\DocumentsReader'],
        [Instance::of('tempFileStorage')]
    ],
    'app\storage\DocumentsWriter' => [
        ['class' => 'app\storage\DocumentsWriter'],
        [Instance::of('tempFileStorage')]
    ]
]);

$reader = $container->get('app\storage\DocumentsReader'); 
// 将与前面示例中的行为完全相同。
```

你可能会注意到 `Instance::of('tempFileStorage')` 符号。这意味着，[[yii\di\Container|Container]]
将隐含地提供一个用 `tempFileStorage` 名称注册的依赖项， 
并将其作为 `app\storage\DocumentsWriter` 构造函数的第一个参数传递。

> Note: [[yii\di\Container::setDefinitions()|setDefinitions()]] 和 [[yii\di\Container::setSingletons()|setSingletons()]]
  方法从版本 2.0.11 开始可用。
  
配置优化的另一个步骤是将某些依赖项注册为单例。
通过 [[yii\di\Container::set()|set()]] 注册的依赖项将在每次需要时实例化。
某些类在运行时不会更改状态，
因此它们可能会被注册为单例以提高应用程序的性能。

一个很好的例子可以是 `app\storage\FileStorage` 类，它用一个简单的 API 
（例如 `$fs->read()`，`$fs->write()`）在文件系统上执行一些操作。
这些操作不会更改内部类的状态，因此我们可以创建一次实例并多次使用它。

```php
$container->setSingletons([
    'tempFileStorage' => [
        ['class' => 'app\storage\FileStorage'],
        ['/var/tempfiles']
    ],
]);

$container->setDefinitions([
    'app\storage\DocumentsReader' => [
        ['class' => 'app\storage\DocumentsReader'],
        [Instance::of('tempFileStorage')]
    ],
    'app\storage\DocumentsWriter' => [
        ['class' => 'app\storage\DocumentsWriter'],
        [Instance::of('tempFileStorage')]
    ]
]);

$reader = $container->get('app\storage\DocumentsReader');
```

什么时候注册依赖关系（When to Register Dependencies） <span id="when-to-register-dependencies"></span>
------------------------------------------------

由于依赖关系在创建新对象时需要解决，因此它们的注册应该尽早完成。
如下是推荐的实践：

* 如果你是一个应用程序的开发者，
  你可以在应用程序的[入口脚本](structure-entry-scripts.md)
  或者被入口脚本引入的脚本中注册依赖关系。
* 如果你是一个可再分发[扩展](structure-extensions.md)的开发者，
  你可以将依赖关系注册到扩展的引导类中。


总结（Summary） <span id="summary"></span>
-------------

依赖注入和[服务定位器](concept-service-locator.md)都是流行的设计模式，
它们使你可以用充分解耦且更利于测试的风格构建软件。
强烈推荐你阅读 [Martin 的文章](https://martinfowler.com/articles/injection.html) ，
对依赖注入和服务定位器有个更深入的理解。

Yii 在依赖住入（DI）容器之上实现了它的[服务定位器](concept-service-locator.md)。
当一个服务定位器尝试创建一个新的对象实例时，它会把调用转发到 DI 容器。
后者将会像前文所述那样自动解决依赖关系。

