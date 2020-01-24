Контейнер внедрения зависимостей
==============================

Контейнер внедрения зависимостей — это объект, который знает, как создать и настроить экземпляр класса и зависимых от него объектов.
[Статья Мартина Фаулера](http://martinfowler.com/articles/injection.html) хорошо объясняет, почему контейнер внедрения зависимостей является полезным. Здесь, преимущественно, будет объясняться использование контейнера внедрения зависимостей, предоставляемого в Yii.


Внедрение зависимостей <span id="dependency-injection"></span>
--------------------

Yii обеспечивает функционал контейнера внедрения зависимостей через класс [[yii\di\Container]]. Он поддерживает следующие виды внедрения зависимостей:

* Внедрение зависимости через конструктор;
* Внедрение зависимости через метод;
* Внедрение зависимости через сеттер и свойство;
* Внедрение зависимости через PHP callback;


### Внедрение зависимости через конструктор <span id="constructor-injection"></span>

Контейнер внедрения зависимостей поддерживает внедрение зависимости через конструктор при помощи указания типов для параметров конструктора.
Указанные типы сообщают контейнеру, какие классы или интерфейсы зависят от него при создании нового объекта.
Контейнер попытается получить экземпляры зависимых классов или интерфейсов, а затем передать их в новый объект через конструктор. Например,

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}

$foo = $container->get('Foo');
// что равносильно следующему:
$bar = new Bar;
$foo = new Foo($bar);
```

### Внедрение зависимости через метод <span id="method-injection"></span>

Обычно зависимости класса передаются в конструктор и становятся доступными внутри класса в течение всего времени
его существования. При помощи инъекции через метод возможно задать зависимость, которая необходима в единственном
методе класса. Передавать такую зависимость через конструктор либо невозможно, либо это влечёт за собой ненужные
накладные расходы в большинстве случаев.

Метод класса может быть определён так же, как `doSomething()` в примере ниже:

```php
class MyClass extends \yii\base\Component
{
    public function __construct(/*Легковесные зависимости тут*/, $config = [])
    {
        // ...
    }

    public function doSomething($param1, \my\heavy\Dependency $something)
    {
        // Работаем с $something
    }
}
```

Метод можно вызвать либо передав экземпляр `\my\heavy\Dependency` самостоятельно, либо использовав
[[yii\di\Container::invoke()]]:

```php
$obj = new MyClass(/*...*/);
Yii::$container->invoke([$obj, 'doSomething'], ['param1' => 42]); // $something будет предоставлено DI-контейнером
```

### Внедрение зависимости через сеттер и свойство <span id="setter-and-property-injection"></span>

Внедрение зависимости через сеттер и свойство поддерживается через [конфигурации](concept-configurations.md).
При регистрации зависимости или при создании нового объекта, вы можете предоставить конфигурацию, которая
будет использована контейнером для внедрения зависимостей через соответствующие сеттеры или свойства.
Например,

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

> Info: Метод [[yii\di\Container::get()]] третьим аргументом принимает массив конфигурации, которым инициализируется создаваемый объект. Если класс реализует интерфейс [[yii\base\Configurable]] (например, [[yii\base\BaseObject]]), то массив конфигурации передается в последний параметр конструктора класса. Иначе конфигурация применяется уже *после* создания объекта.


Более сложное практическое применение <span id="advanced-practical-usage"></span>
---------------

Допустим, мы работаем над API и у нас есть:

- `app\components\Request`, наследуемый от `yii\web\Request` и реализующий дополнительные возможности.
- `app\components\Response`, наследуемый от `yii\web\Response` с свойством `format`, по умолчанию инициализируемом как `json`.
- `app\storage\FileStorage` и `app\storage\DocumentsReader`, где реализована некая логика для работы с документами в
  неком файловом хранилище:
  
  ```php
  class FileStorage
  {
      public function __construct($root) {
          // делаем что-то
      }
  }
  
  class DocumentsReader
  {
      public function __construct(FileStorage $fs) {
          // делаем что-то
      }
  }
  ```

Возможно настроить несколько компонентов сразу передав массив конфигурации в метод 
[[yii\di\Container::setDefinitions()|setDefinitions()]] или [[yii\di\Container::setSingletons()|setSingletons()]].
Внутри метода фреймворк обойдёт массив конфигурации и вызовет для каждого элемента [[yii\di\Container::set()|set()]] или
[[yii\di\Container::setSingleton()|setSingleton()]] соответственно.

Формат массива конфигурации следующий:

 - Ключ: имя класса, интерфейса или псевдонима. Ключ передаётся в первый аргумент `$class` метода
 [[yii\di\Container::set()|set()]].
 - Значение: конфигурация для класса. Возможные значения описаны в документации параметра `$definition` метода
 [[yii\di\Container::set()|set()]]. Значение передаётся в аргумент `$definition` метода [[set()]].

Для примера, давайте настроим наш контейнер:

```php
$container->setDefinitions([
    'yii\web\Request' => 'app\components\Request',
    'yii\web\Response' => [
        'class' => 'app\components\Response',
        'format' => 'json'
    ],
    'app\storage\DocumentsReader' => function ($container, $params, $config) {
        $fs = new app\storage\FileStorage('/var/tempfiles');
        return new app\storage\DocumentsReader($fs);
    }
]);

$reader = $container->get('app\storage\DocumentsReader');
// Создаст объект DocumentReader со всеми зависимостями 
```

> Tip: Начиная с версии 2.0.11 контейнер может быть настроен в декларативном стиле через конфигурацию приложения. 
Как это сделать ищите в подразделе [Конфигурация приложения](concept-configurations.md#application-configurations)
раздела [Конфигурации](concept-configurations.md).

Вроде всё работает, но если нам необходимо создать экземпляр класса `DocumentWriter`, придётся скопировать код, 
создающий экземпляр`FileStorage`, что, очевидно, не является оптимальным.

Как описано в подразделе [Разрешение зависимостей](#resolving-dependencies), [[yii\di\Container::set()|set()]]
и [[yii\di\Container::setSingleton()|setSingleton()]] могут опционально принимать третьим аргументов параметры
для конструктора. Формат таков:

 - Ключ: имя класса, интерфейса или псевдонима. Ключ передаётся в первый аргумент `$class` метода [[yii\di\Container::set()|set()]].
 - Значение: массив из двух элементов. Первый элемент передаётся в метод [[yii\di\Container::set()|set()]] вторым
  аргументом `$definition`, второй элемент — аргументом `$params`.

Исправим наш пример:

```php
$container->setDefinitions([
    'tempFileStorage' => [ // для удобства мы задали псевдоним
        ['class' => 'app\storage\FileStorage'],
        ['/var/tempfiles']
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
// Код будет работать ровно так же, как и в предыдущем примере.
```

Вы могли заметить вызов `Instance::of('tempFileStorage')`. Он означает, что [[yii\di\Container|Container]]
неявно предоставит зависимость, зарегистрированную с именем `tempFileStorage` и передаст её первым аргументом 
в конструктор `app\storage\DocumentsWriter`.

> Note: Методы [[yii\di\Container::setDefinitions()|setDefinitions()]] и [[yii\di\Container::setSingletons()|setSingletons()]]
  доступны с версии 2.0.11.
  
Ещё один шаг по оптимизации конфигурации — регистрировать некоторые зависимости как синглтоны. Зависимость, регистрируемая 
через метод [[yii\di\Container::set()|set()]], будет создаваться каждый раз при обращении к ней. Некоторые классы не меняют
своего состояния на протяжении всей работы приложения, поэтому могут быть зарегистрированы как синглтоны. Это увеличит
производительность приложения. 

Хорошим примером может быть класс `app\storage\FileStorage`, который выполняет некие операции над файловой системой
через простой API: `$fs->read()`, `$fs->write()`. Обе операции не меняют внутреннее состояние класса, поэтому мы можем
создать класс один раз и далее использовать его.

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

### Внедрение зависимости через PHP callback <span id="php-callable-injection"></span>

В данном случае, контейнер будет использовать зарегистрированный PHP callback для создания новых экземпляров класса.
Каждый раз при вызове [[yii\di\Container::get()]] вызывается соответствующий callback.
Callback отвечает за разрешения зависимостей и внедряет их в соответствии с вновь создаваемыми объектами. Например,

```php
$container->set('Foo', function ($container, $params, $config) {
    $foo = new Foo(new Bar);
    // ... дополнительная инициализация
    return $foo;
});

$foo = $container->get('Foo');
```

Для того, чтобы скрыть сложную логику инициализации нового объекта, можно использовать статический метод, возвращающий
callable:

```php
class FooBuilder
{
    public static function build($container, $params, $config)
    {
        $foo = new Foo(new Bar);
        // ... дополнительная инициализация ...
        return $foo;
    }
}

$container->set('Foo', ['app\helper\FooBuilder', 'build']);

$foo = $container->get('Foo');
```

Теперь тот, кто будет настраивать класс `Foo`, не обязан знать, как этот класс устроен.


Регистрация зависимостей <span id="registering-dependencies"></span>
------------------------

Вы можете использовать [[yii\di\Container::set()]] для регистрации зависимостей. При регистрации требуется имя зависимости, а также определение зависимости. Именем зависимости может быть имя класса, интерфейса или алиас, 
так же определением зависимости может быть имя класса, конфигурационным массивом, или PHP callback'ом.

```php
$container = new \yii\di\Container;

// регистрация имени класса, как есть. Это может быть пропущено.
$container->set('yii\db\Connection');

// регистрация интерфейса
// Когда класс зависит от интерфейса, соответствующий класс
// будет использован в качестве зависимости объекта
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// регистрация алиаса. Вы можете использовать $container->get('foo')
// для создания экземпляра Connection
$container->set('foo', 'yii\db\Connection');

// регистрация класса с конфигурацией. Конфигурация
// будет применена при создании экземпляра класса через get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// регистрация алиаса с конфигурацией класса
// В данном случае, параметр "class" требуется для указания класса
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// регистрация PHP callback'a
// Callback будет выполняться каждый раз при вызове $container->get('db')
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// регистрация экземпляра компонента
// $container->get('pageCache') вернёт тот же экземпляр при каждом вызове
$container->set('pageCache', new FileCache);
```

> Tip: Если имя зависимости такое же, как и определение соответствующей зависимости, то её повторная регистрация в контейнере внедрения зависимостей не нужна.

Зависимость, зарегистрированная через `set()` создаёт экземпляр каждый раз, когда зависимость необходима.
Вы можете использовать [[yii\di\Container::setSingleton()]] для регистрации зависимости, которая создаст только один экземпляр:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


Разрешение зависимостей <span id="resolving-dependencies"></span>
----------------------
После регистрации зависимостей, вы можете использовать контейнер внедрения зависимостей для создания новых объектов,
и контейнер автоматически разрешит зависимости их экземпляра и их внедрений во вновь создаваемых объектах. Разрешение
зависимостей рекурсивно, то есть если зависимость имеет другие зависимости, эти зависимости также будут автоматически
разрешены.

Вы можете использовать [[yii\di\Container::get()]] для создания или получения объектов. Метод принимает имя зависимости,
которым может быть имя класса, имя интерфейса или псевдоним. Имя зависимости может быть зарегистрировано через
`set()` или `setSingleton()`. Вы можете опционально предоставить список параметров конструктора класса и
[конфигурацию](concept-configurations.md) для настройки созданного объекта.

Например:

```php
// "db" ранее зарегистрированный псевдоним
$db = $container->get('db');

// эквивалентно: $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

За кулисами, контейнер внедрения зависимостей делает гораздо больше работы, чем просто создание нового объекта.
Прежде всего, контейнер, осмотрит конструктор класса, чтобы узнать имя зависимого класса или интерфейса, а затем
автоматически разрешит эти зависимости рекурсивно.

Следующий код демонстрирует более сложный пример. Класс `UserLister` зависит от объекта, реализующего интерфейс
`UserFinderInterface`; класс `UserFinder` реализует этот интерфейс и зависит от объекта `Connection`. Все эти зависимости
были объявлены через тип подсказки параметров конструктора класса. При регистрации зависимости через свойство, контейнер
внедрения зависимостей позволяет автоматически разрешить эти зависимости и создаёт новый экземпляр `UserLister` простым
вызовом `get('userLister')`.

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

// что эквивалентно:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


Практическое применение <span id="practical-usage"></span>
---------------

Yii создаёт контейнер внедрения зависимостей когда вы подключаете файл `Yii.php` во [входном скрипте](structure-entry-scripts.md) 
вашего приложения. Контейнер внедрения зависимостей доступен через [[Yii::$container]]. При вызове [[Yii::createObject()]],
метод на самом деле вызовет метод контейнера [[yii\di\Container::get()|get()]], чтобы создать новый объект.
Как упомянуто выше, контейнер внедрения зависимостей автоматически разрешит зависимости (если таковые имеются) и внедрит их
получаемый объект. Поскольку Yii использует [[Yii::createObject()]] в большей части кода своего ядра для создания новых
объектов, это означает, что вы можете настроить глобальные объекты, имея дело с [[Yii::$container]].

Например, давайте настроим количество кнопок в пейджере [[yii\widgets\LinkPager]] по умолчанию глобально:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Теперь, если вы вызовете в представлении виджет, используя следующий код, то свойство `maxButtonCount` будет инициализировано как 5 вместо значения по умолчанию 10, как это определено в классе.

```php
echo \yii\widgets\LinkPager::widget();
```

Хотя вы всё ещё можете переопределить установленное значение через контейнер внедрения зависимостей:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```
Другим примером является использование автоматического внедрения зависимости через конструктор контейнера внедрения зависимостей.
Предположим, ваш класс контроллера зависит от ряда других объектов, таких как сервис бронирования гостиницы. Вы
можете объявить зависимость через параметр конструктора и позволить контейнеру внедрения зависимостей, разрешить её за вас.

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

Если у вас есть доступ к этому контроллеру из браузера, вы увидите сообщение об ошибке, который жалуется на то, что `BookingInterface`
не может быть создан. Это потому что вы должны указать контейнеру внедрения зависимостей, как обращаться с этой зависимостью:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

Теперь, если вы попытаетесь получить доступ к контроллеру снова, то экземпляр `app\components\BookingService` будет создан и введён в качестве 3-го параметра конструктора контроллера.


Когда следует регистрировать зависимости <span id="when-to-register-dependencies"></span>
-----------------------------

Поскольку зависимости необходимы тогда, когда создаются новые объекты, то их регистрация должна быть сделана
как можно раньше. Ниже приведены рекомендуемые практики:

* Если вы разработчик приложения, то вы можете зарегистрировать зависимости в конфигурации вашего приложения.
  Как это сделать описано в подразделе [Конфигурация приложения](concept-service-locator.md#application-configurations) 
  раздела [Конфигурации](concept-configurations.md).
* Если вы разработчик распространяемого [расширения](structure-extensions.md), то вы можете зарегистрировать зависимости
  в загрузочном классе расширения.


Итог <span id="summary"></span>
-------
Как dependency injection, так и [service locator](concept-service-locator.md) являются популярными паттернами проектирования, которые позволяют 
создавать программное обеспечение в слабосвязанной и более тестируемой манере.
Мы настоятельно рекомендуем к прочтению
[статью Мартина Фаулера](http://martinfowler.com/articles/injection.html), для более глубокого понимания dependency injection и service locator. 

Yii реализует свой [service locator](concept-service-locator.md) поверх контейнера внедрения зависимостей.
Когда service locator пытается создать новый экземпляр объекта, он перенаправляет вызов на контейнер внедрения зависимостей.
Последний будет разрешать зависимости автоматически, как описано выше.

