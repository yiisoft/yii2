Events
======

Events allow you to inject custom code into existing code at certain execution points. You can attach custom
code to an event so that when the event is triggered, the code gets executed automatically. For example,
a mailer object may trigger a `messageSent` event when it successfully sends a message. If you want to keep
track of the messages that are successfully sent, you could then simply attach the tracking code to the `messageSent` event.

Yii introduces a base class called [[yii\base\Component]] to support events. If a class needs to trigger
events, it should extend from [[yii\base\Component]], or from a child class.


Event Handlers <a name="event-handlers"></a>
--------------

An event handler is a [PHP callback](http://www.php.net/manual/en/language.types.callable.php) that gets executed
when the event it is attached to is triggered. You can use one of the following callbacks:

- a global PHP function specified in terms of a string, e.g., `'trim()'`;
- an object method specified in terms of an array of an object and a method name, e.g., `[$object, $method]`;
- a static class method specified in terms of an array of a class name and a method name, e.g., `[$class, $method]`;
- an anonymous function, e.g., `function ($event) { ... }`.

The signature of an event handler is:

```php
function ($event) {
    // $event is an object of yii\base\Event or its child class
}
```

Through the `$event` parameter, an event handler may get the following information about an event:

- [[yii\base\Event::name|event name]]
- [[yii\base\Event::sender|event sender]]: the object whose `trigger()` method is called.
- [[yii\base\Event::data|custom data]]: the data that is provided when attaching the event handler (to be explained shortly).


Attaching Event Handlers <a name="attaching-event-handlers"></a>
------------------------

You can attach a handler to an event by calling the [[yii\base\Component::on()]] method. For example,

```php
$foo = new Foo;

// the handler is a global function
$foo->on(Foo::EVENT_HELLO, 'function_name');

// the handler is an object method
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// the handler is a static class method
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// the handler is an anonymous function
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // event handling logic
});
```

You may also attach event handlers through [configurations](concept-configurations.md). For more details, please
refer to the [Configurations](concept-configurations.md#configuration-format) section.


When attaching an event handler, you may provide additional data as the third parameter to [[yii\base\Component::on()]].
The data will be made available to the handler when the event is triggered and the handler is called. For example,

```php
// The following code will display "abc" when the event is triggered
// because $event->data contains the data passed to "on"
$foo->on(Foo::EVENT_HELLO, function ($event) {
    echo $event->data;
}, 'abc');
```

You may attach one or multiple handlers to a single event. When an event is triggered, the attached handlers
will be called in the order they are attached to the event. If a handler needs to stop the invocation of the
handlers behind it, it may set the [[yii\base\Event::handled]] property of the `$event` parameter to be true,
like the following,

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

By default, a newly attached handler is appended to the existing handler queue for the event.
As a result, the handler will be called in the last place when the event is triggered.
To insert the new handler at the start of the handler queue so that the handler gets called first, y
ou may call [[yii\base\Component::on()]] by passing the fourth parameter `$append` as false:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

Besides calling the `on()` method, you may also attach event handlers in [configurations](concept-configurations.md)
like the following. For more details, please refer to the [Configurations](concept-configurations.md#configuration-format)
section.

```php
[
    'on hello' => function ($event) {
        echo 'hello event is triggered';
    }
]
```

Triggering Events <a name="triggering-events"></a>
-----------------

Events are triggered by calling the [[yii\base\Component::trigger()]] method. The method requires an *event name*,
and optionally an event object that describes the parameters to be passed to the event handlers. For example:

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

In the above code, when you call `bar()`, it will trigger an event named `hello`.

> Tip: It is recommended to use class constants to represent event names. In the above example, the constant
  `EVENT_HELLO` is used to represent `hello`. This approach has two benefits. First, it prevents typos and can impact IDE
  auto-completion support. Second, you can tell what events are supported by a class by simply checking the constant
  declarations.

Sometimes when triggering an event, you may want to pass along additional information to the event handlers.
For example, a mailer may want pass the message information to the handlers of the `messageSent` event so that the handlers
can know the particulars of the sent messages. To do so, you can provide an event object as the second parameter to
the [[yii\base\Component::trigger()]] method. The event object must be an instance of the [[yii\base\Event]] class
or a child class. For example:

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
        // ...sending $message...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

When the [[yii\base\Component::trigger()]] method is called, it will call handlers that are attached to
the named event.


Detaching Event Handlers <a name="detaching-event-handlers"></a>
------------------------

To detach a handler from an event, call the [[yii\base\Component::off()]] method. For example,

```php
// the handler is a global function
$foo->off(Foo::EVENT_HELLO, 'function_name');

// the handler is an object method
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// the handler is a static class method
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// the handler is an anonymous function
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

Note that in general you should not try to detach an anonymous function unless you store it
somewhere when it is attached to the event. In the above example, we assume the anonymous
function is stored as a variable `$anonymousFunction`.

To detach ALL handlers from an event, simply call [[yii\base\Component::off()]] without the second parameter:

```php
$foo->off(Foo::EVENT_HELLO);
```


Class-Level Event Handlers <a name="class-level-event-handlers"></a>
--------------------------

In the above subsections, we have described how to attach a handler to an event at *instance level*.
Sometimes, you may want to respond to an event triggered by EVERY instance of a class instead of
a specific instance. Instead of attaching an event handler to every instance, you may attach the handler
at *class level* by calling the static method [[yii\base\Event::on()]].

For example, an [Active Record](db-active-record.md) object will trigger a [[yii\base\ActiveRecord::EVENT_AFTER_INSERT]]
event whenever it inserts a new record into the database. In order to track insertions done by EVERY
[Active Record](db-active-record.md) object, you may write the following code:

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted');
});
```

The event handler will get invoked whenever an instance of [[yii\base\ActiveRecord|ActiveRecord]] or its child class triggers
the [[yii\base\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] event. In the handler, you can get the object
that triggers the event through `$event->sender`.

When an object triggers an event, it will first call instance-level handlers, followed by class-level handlers.

You may trigger an *class-level* event by calling the static method [[yii\base\Event::trigger()]]. A class-level
event is not associated with a particular object. As a result, it will cause the invocation of class-level event
handlers only. For example,

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    echo $event->sender;  // displays "app\models\Foo"
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

Note that in this case, `$event->sender` refers to the name of the class triggering the event instead of an object instance.

> Note: Because a class-level handler will respond to an event triggered by any instance of that class or its child
  class, you should use it carefully, especially if the class is a low-level base class, such as [[yii\base\Object]].

To detach a class-level event handler, call [[yii\base\Event::off()]]. For example,

```php
// detach $handler
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// detach all handlers of Foo::EVENT_HELLO
Event::off(Foo::className(), Foo::EVENT_HELLO);
```


Global Events <a name="global-events"></a>
-------------

The so-called *global event* is actually a trick based on the event mechanism described above.
It requires a globally accessible singleton, such as the [application](structure-applications.md) instance.

An event sender, instead of calling its own `trigger()` method, will call the singleton's `trigger()` method
to trigger the event. Similarly, the event handlers are attached to the event of the singleton. For example,

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // displays "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

A benefit of global events is that you do not need the object when attaching a handler to the event
which will be triggered by the object. Instead, the handler attachment and the event triggering are both
done through the singleton (e.g. the application instance).

However, because the namespace of the global events is shared by all parties, you should name the global events
wisely, such as introducing some sort of namespace (e.g. "frontend.mail.sent", "backend.mail.sent").
