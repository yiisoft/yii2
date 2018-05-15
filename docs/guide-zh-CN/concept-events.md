事件（Events）
============

事件可以将自定义代码“注入”到现有代码中的特定执行点。
附加自定义代码到某个事件，当这个事件被触发时，这些代码就会自动执行。
例如，邮件程序对象成功发出消息时可触发 `messageSent` 事件。
如想追踪成功发送的消息，可以附加相应追踪代码到 `messageSent` 事件。

Yii 引入了名为 [[yii\base\Component]] 的基类以支持事件。
如果一个类需要触发事件就应该继承 [[yii\base\Component]] 或其子类。


事件处理器（Event Handlers） <span id="event-handlers"></span>
-------------------------

事件处理器是一个[PHP 回调函数](http://www.php.net/manual/en/language.types.callable.php)，
当它所附加到的事件被触发时它就会执行。可以使用以下回调函数之一：

- 字符串形式指定的 PHP 全局函数，如 `'trim'` ；
- 对象名和方法名数组形式指定的对象方法，如 `[$object, $method]` ；
- 类名和方法名数组形式指定的静态类方法，如 `[$class, $method]` ；
- 匿名函数，如 `function ($event) { ... }` 。

事件处理器的格式是：

```php
function ($event) {
    // $event 是 yii\base\Event 或其子类的对象
}
```

通过 `$event` 参数，事件处理器就获得了以下有关事件的信息：

- [[yii\base\Event::name|event name]]：事件名
- [[yii\base\Event::sender|event sender]]：调用 `trigger()` 方法的对象
- [[yii\base\Event::data|custom data]]：附加事件处理器时传入的数据，默认为空，后文详述


附加事件处理器（Attaching Event Handlers） <span id="attaching-event-handlers"></span>
--------------------------------------

调用 [[yii\base\Component::on()]] 方法来附加处理器到事件上。如：

```php
$foo = new Foo;

// 处理器是全局函数
$foo->on(Foo::EVENT_HELLO, 'function_name');

// 处理器是对象方法
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// 处理器是静态类方法
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// 处理器是匿名函数
$foo->on(Foo::EVENT_HELLO, function ($event) {
    //事件处理逻辑
});
```

你也可以通过 [配置](concept-configurations.md) 附加事件处理器。 请
参考 [配置的格式](concept-configurations.md#configuration-format) 小节了解更多.


附加事件处理器时可以提供额外数据作为 [[yii\base\Component::on()]] 方法的第三个参数。
数据在事件被触发和处理器被调用时能被处理器使用。如：

```php
// 当事件被触发时以下代码显示 "abc"
// 因为 $event->data 包括被传递到 "on" 方法的数据
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

事件处理器顺序（Event Handler Order）
---------------------------------

可以附加一个或多个处理器到一个事件。当事件被触发，已附加的处理器将按附加次序依次调用。
如果某个处理器需要停止其后的处理器调用，可以设置 `$event` 参数的 [[yii\base\Event::handled]] 属性为真，
如下：

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

默认新附加的事件处理器排在已存在处理器队列的最后。
因此，这个处理器将在事件被触发时最后一个调用。
在处理器队列最前面插入新处理器将使该处理器最先调用，可以传递第四个参数 `$append` 为假并调用 [[yii\base\Component::on()]] 方法实现：

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // 这个处理器将被插入到处理器队列的第一位...
}, $data, false);
```

触发事件（Triggering Events） <span id="triggering-events"></span>
--------------------------

事件通过调用 [[yii\base\Component::trigger()]] 方法触发，此方法须传递*事件名*，
还可以传递一个事件对象，用来传递参数到事件处理器。如：

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class Foo extends Component
{
    const EVENT_HELLO = 'hello';

    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```

以上代码当调用 `bar()` ，它将触发名为 `hello` 的事件。

> Tip: 推荐使用类常量来表示事件名。上例中，常量 `EVENT_HELLO` 用来表示 `hello` 。
  这有两个好处。第一，它可以防止拼写错误并支持 IDE 的自动完成。
  第二，只要简单检查常量声明就能了解一个类支持哪些事件。

有时想要在触发事件时同时传递一些额外信息到事件处理器。
例如，邮件程序要传递消息信息到 `messageSent` 事件的处理器以便处理器了解哪些消息被发送了。
为此，可以提供一个事件对象作为 [[yii\base\Component::trigger()]] 方法的第二个参数。
这个事件对象必须是 [[yii\base\Event]] 类或其子类的实例。
如：

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class MessageEvent extends Event
{
    public $message;
}

class Mailer extends Component
{
    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message)
    {
        // ...发送 $message 的逻辑...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

当 [[yii\base\Component::trigger()]] 方法被调用时，
它将调用所有附加到命名事件（trigger 方法第一个参数）的事件处理器。


移除事件处理器（Detaching Event Handlers） <span id="detaching-event-handlers"></span>
--------------------------------------

从事件移除处理器，调用 [[yii\base\Component::off()]] 方法。如：

```php
// 处理器是全局函数
$foo->off(Foo::EVENT_HELLO, 'function_name');

// 处理器是对象方法
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// 处理器是静态类方法
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// 处理器是匿名函数
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

注意当匿名函数附加到事件后一般不要尝试移除匿名函数，
除非你在某处存储了它。以上示例中，
假设匿名函数存储为变量 `$anonymousFunction` 。

移除事件的全部处理器，简单调用 [[yii\base\Component::off()]] 即可，不需要第二个参数：

```php
$foo->off(Foo::EVENT_HELLO);
```


类级别的事件处理器（Class-Level Event Handlers） <span id="class-level-event-handlers"></span>
-------------------------------------------

以上部分，我们叙述了在*实例级别*如何附加处理器到事件。
有时想要一个类的*所有*实例而不是一个指定的实例都响应一个被触发的事件，
并不是一个个附加事件处理器到每个实例，
而是通过调用静态方法 [[yii\base\Event::on()]] 在*类级别*附加处理器。

例如，[活动记录](db-active-record.md)对象要在每次往数据库新增一条新记录时触发一个 
[[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 事件。
要追踪每个[活动记录](db-active-record.md)对象的新增记录完成情况，应如下写代码：

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::class, ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::debug(get_class($event->sender) . ' is inserted');
});
```

每当 [[yii\db\BaseActiveRecord|ActiveRecord]] 或其子类的实例触发 
[[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 事件时，
这个事件处理器都会执行。在这个处理器中，可以通过 `$event->sender` 获取触发事件的对象。

当对象触发事件时，它首先调用实例级别的处理器，然后才会调用类级别处理器。

可调用静态方法[[yii\base\Event::trigger()]]来触发一个*类级别*事件。
类级别事件不与特定对象相关联。因此，它只会引起类级别事件处理器的调用。
如：

```php
use yii\base\Event;

Event::on(Foo::class, Foo::EVENT_HELLO, function ($event) {
    var_dump($event->sender);  // 显示 "null"
Event::on(Foo::class, Foo::EVENT_HELLO, function ($event) {
});

Event::trigger(Foo::class, Foo::EVENT_HELLO);
```

注意这种情况下 `$event->sender` 指向触发事件的类名而不是对象实例。

> Note: 因为类级别的处理器响应类和其子类的所有实例触发的事件，
  必须谨慎使用，尤其是底层的基类，如 [[yii\base\Object]]。

移除类级别的事件处理器只需调用[[yii\base\Event::off()]]，如：

```php
// 移除 $handler
Event::off(Foo::class, Foo::EVENT_HELLO, $handler);

// 移除 Foo::EVENT_HELLO 事件的全部处理器
Event::off(Foo::class, Foo::EVENT_HELLO);
```


使用接口事件（Events using interfaces） <span id="interface-level-event-handlers"></span>
-----------------------------------

有更多的抽象方式来处理事件。你可以为特殊的事件创建一个独立的接口，
然后在你需要的类中实现它。

例如，我们可以先创建下面这个接口:

```php
namespace app\interfaces;

interface DanceEventInterface
{
    const EVENT_DANCE = 'dance';
}
```

然后在两个类中实现:

```php
class Dog extends Component implements DanceEventInterface
{
    public function meetBuddy()
    {
        echo "Woof!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}

class Developer extends Component implements DanceEventInterface
{
    public function testsPassed()
    {
        echo "Yay!";
        $this->trigger(DanceEventInterface::EVENT_DANCE);
    }
}
```

要处理由这些类触发的 `EVENT_DANCE` ，调用 [[yii\base\Event::on()|Event::on()]] 
并将接口类名作为第一个参数:

```php
Event::on('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, function ($event) {
    Yii::trace(get_class($event->sender) . ' just danced'); // Will log that Dog or Developer danced
});
```

你可以在这些类中触发这个事件：

```php
// trigger event for Dog class
Event::trigger(Dog::class, DanceEventInterface::EVENT_DANCE);

// trigger event for Developer class
Event::trigger(Developer::class, DanceEventInterface::EVENT_DANCE);
```

但是请注意, 你不能让所有实现这个接口的类都触发事件：

```php
// 不会生效。实现此接口的类不会触发事件。
Event::trigger('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```

调用 [[yii\base\Event::off()|Event::off()]] 移除事件处理器。例如：

```php
// 移除 $handler
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, $handler);

// 移除所有 `DanceEventInterface::EVENT_DANCE` 的处理器
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```


全局事件（Global Events） <span id="global-events"></span>
----------------------

所谓*全局事件*实际上是一个基于以上叙述的事件机制的戏法。它需要一个全局可访问的单例，
如[应用](structure-applications.md)实例。

事件触发者不调用其自身的 `trigger()` 方法，而是调用单例的 `trigger()` 方法来触发全局事件。
类似地，事件处理器被附加到单例的事件。如：

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // 显示 "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

全局事件的一个好处是当附加处理器到一个对象要触发的事件时，
不需要产生该对象。相反，处理器附加和事件触发都通过单例
（如应用实例）完成。

然而，因为全局事件的命名空间由各方共享，应合理命名全局事件，
如引入一些命名空间（例："frontend.mail.sent", "backend.mail.sent"）。


通配符事件（Wildcard Events） <span id="wildcard-events"></span>
--------------------------

自 2.0.14 以来，您可以为多个匹配通配符模式的事件设置事件处理程序。
例如：

```php
use Yii;

$foo = new Foo();

$foo->on('foo.event.*', function ($event) {
    // 触发任何事件，该名称以 'foo.event.' 开头
    Yii::debug('trigger event: ' . $event->name);
});
```

通配符模式也可以用于类级别的事件。 例如：

```php
use yii\base\Event;
use Yii;

Event::on('app\models\*', 'before*', function ($event) {
    // 触发命名空间 'app\models' 中的任何类的任何事件，名称以 'before' 开头。
    Yii::debug('trigger event: ' . $event->name . ' for class: ' . get_class($event->sender));
});
```

这允许您使用以下代码通过单个处理程序捕获所有应用程序事件：

```php
use yii\base\Event;
use Yii;

Event::on('*', '*', function ($event) {
    // 触发任何类的任何事件
    Yii::debug('trigger event: ' . $event->name);
});
```

> Note: 事件处理程序设置的使用通配符可能会降低应用程序的性能。
  如果可能，最好避免。

为了移除由通配符模式指定的事件处理程序，您应该在
[[yii\base\Component::off()]] 或 [[yii\base\Event::off()]] 调用中重复相同的模式。
请记住，在移除事件处理程序期间传递通配符将移除为此通配符指定的处理程序，
而为常规事件名称附加的处理程序将保留，即使它们与模式匹配。 例如：

```php
use Yii;

$foo = new Foo();

// 附加常规处理
$foo->on('event.hello', function ($event) {
    echo 'direct-handler'
});

// 附加通配符处理程序
$foo->on('*', function ($event) {
    echo 'wildcard-handler'
});

// 仅移除通配符处理程序！
$foo->off('*');

$foo->trigger('event.hello'); // 输出：'direct-handler'
```
