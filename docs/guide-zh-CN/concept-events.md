事件
======

事件可以将自定义代码“注入”到现有代码中的特定执行点。附加自定义代码到某个事件，当这个事件被触发时，这些代码就会自动执行。例如，邮件程序对象成功发出消息时可触发 `messageSent` 事件。如想追踪成功发送的消息，可以附加相应追踪代码到 `messageSent` 事件。

Yii 引入了名为 [[yii\base\Component]] 的基类以支持事件。如果一个类需要触发事件就应该继承 [[yii\base\Component]] 或其子类。


事件处理器（Event Handlers）
--------------

事件处理器是一个[PHP 回调函数](http://www.php.net/manual/en/language.types.callable.php)，当它所附加到的事件被触发时它就会执行。可以使用以下回调函数之一：

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


附加事件处理器
----------------

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

附加事件处理器时可以提供额外数据作为 [[yii\base\Component::on()]] 方法的第三个参数。数据在事件被触发和处理器被调用时能被处理器使用。如：

```php
// 当事件被触发时以下代码显示 "abc"
// 因为 $event->data 包括被传递到 "on" 方法的数据
$foo->on(Foo::EVENT_HELLO, function ($event) {
    echo $event->data;
}, 'abc');
```


事件处理器顺序
-----------------

可以附加一个或多个处理器到一个事件。当事件被触发，已附加的处理器将按附加次序依次调用。如果某个处理器需要停止其后的处理器调用，可以设置 `$event` 参数的 [yii\base\Event::handled]] 属性为真，如下：

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

默认新附加的事件处理器排在已存在处理器队列的最后。因此，这个处理器将在事件被触发时最后一个调用。在处理器队列最前面插入新处理器将使该处理器最先调用，可以传递第四个参数 `$append` 为假并调用 [[yii\base\Component::on()]] 方法实现：

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // 这个处理器将被插入到处理器队列的第一位...
}, $data, false);
```


触发事件
----------

事件通过调用 [[yii\base\Component::trigger()]] 方法触发，此方法须传递**事件名**，还可以传递一个事件对象，用来传递参数到事件处理器。如：

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

> 提示：推荐使用类常量来表示事件名。上例中，常量 `EVENT_HELLO` 用来表示 `hello` 。这有两个好处。第一，它可以防止拼写错误并支持 IDE 的自动完成。第二，只要简单检查常量声明就能了解一个类支持哪些事件。

有时想要在触发事件时同时传递一些额外信息到事件处理器。例如，邮件程序要传递消息信息到 `messageSent` 事件的处理器以便处理器了解哪些消息被发送了。为此，可以提供一个事件对象作为 [[yii\base\Component::trigger()]] 方法的第二个参数。这个事件对象必须是 [[yii\base\Event]] 类或其子类的实例。如：

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

当 [[yii\base\Component::trigger()]] 方法被调用时，它将调用所有附加到命名事件（trigger 方法第一个参数）的事件处理器。


移除事件处理器
---------------

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

注意当匿名函数附加到事件后一般不要尝试移除匿名函数，除非你在某处存储了它。以上示例中，假设匿名函数存储为变量 `$anonymousFunction` 。

移除事件的全部处理器，简单调用 [[yii\base\Component::off()]] 即可，不需要第二个参数：

```php
$foo->off(Foo::EVENT_HELLO);
```

类级别的事件处理器
-------------------

以上部分，我们叙述了在**实例级别**如何附加处理器到事件。有时想要一个类的所有实例而不是一个指定的实例都响应一个被触发的事件，并不是一个个附加事件处理器到每个实例，而是通过调用静态方法 [[yii\base\Event::on()]] 在**类级别**附加处理器。

例如，[活动记录](db-active-record.md)对象要在每次往数据库新增一条新记录时触发一个 [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 事件。要追踪每个[活动记录](db-active-record.md)对象的新增记录完成情况，应如下写代码：

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted');
});
```

每当 [[yii\db\BaseActiveRecord|ActiveRecord]] 或其子类的实例触发 [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] 事件时，这个事件处理器都会执行。在这个处理器中，可以通过 `$event->sender` 获取触发事件的对象。

当对象触发事件时，它首先调用实例级别的处理器，然后才会调用类级别处理器。

可调用静态方法[[yii\base\Event::trigger()]]来触发一个**类级别**事件。类级别事件不与特定对象相关联。因此，它只会引起类级别事件处理器的调用。如：

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    echo $event->sender;  // 显示 "app\models\Foo"
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

注意这种情况下 `$event->sender` 指向触发事件的类名而不是对象实例。

> 注意：因为类级别的处理器响应类和其子类的所有实例触发的事件，必须谨慎使用，尤其是底层的基类，如 [[yii\base\Object]]。

移除类级别的事件处理器只需调用[[yii\base\Event::off()]]，如：

```php
// 移除 $handler
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// 移除 Foo::EVENT_HELLO 事件的全部处理器
Event::off(Foo::className(), Foo::EVENT_HELLO);
```


全局事件
-------------

所谓**全局事件**实际上是一个基于以上叙述的事件机制的戏法。它需要一个全局可访问的单例，如[应用](structure-applications.md)实例。

事件触发者不调用其自身的 `trigger()` 方法，而是调用单例的 `trigger()` 方法来触发全局事件。类似地，事件处理器被附加到单例的事件。如：

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // 显示 "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

全局事件的一个好处是当附加处理器到一个对象要触发的事件时，不需要产生该对象。相反，处理器附加和事件触发都通过单例（如应用实例）完成。

然而，因为全局事件的命名空间由各方共享，应合理命名全局事件，如引入一些命名空间（例："frontend.mail.sent", "backend.mail.sent"）。
