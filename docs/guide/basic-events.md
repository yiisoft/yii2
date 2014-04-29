Events
======

Events allow you to inject custom code into existing code at certain execution points. You can attach custom
code to an event so that when the event is triggered, the code gets executed automatically. For example,
a mailer object may trigger a `messageSent` event when it successfully sends out a message. If you want to keep
track of the messages that are successfully sent, you may attach the tracking code to the `messageSent` event.

Yii introduces a base class called [[yii\base\Component]] to support events. If a class needs to trigger
events, it should extend from [[yii\base\Component]] or its child class.


Triggering Events
-----------------

Events are triggered by calling the [[yii\base\Component::trigger()]] method. The method requires an *event name*
and optionally an event object which describes the parameters to be passed to the event handlers. For example,

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
  `EVENT_HELLO` is used to represent `hello`. This has two benefits. First, it prevents typos and can get IDE
  auto-completion support. Second, you can tell what events are supported by a class by simply checking the constant
  declarations.

Sometimes when triggering an event, you may want to pass along some additional information to the event handlers.
For example, a mailer may want pass the message information to the handlers of the `messageSent` event so that the handlers
can know what messages are sent. To do so, you can provide an event object as the second parameter to
the [[yii\base\Component::trigger()]] method. The event object must be an instance of the [[yii\base\Event]] class
or its child class. For example,

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


Event Handlers
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


Attaching Event Handlers
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


Detaching Event Handlers
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


Global Events
-------------

You can use "global" events instead of per-component ones. A global event can take place on any component type.

In order to attach a handler to a global event, call the `on` method on the application instance:

```php
Yii::$app->on($eventName, $handler);
```

Global events are triggered on the application instance instead
of a specific component:

```php
Yii::$app->trigger($eventName);
```


Class Events
------------

It is possible to attach event handlers to all instances of a class instead of individual instances. To do so, use
the static `Event::on` method:

```php
Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted.');
});
```

The code above defines a handler that will be triggered for every Active Record object's `EVENT_AFTER_INSERT` event.
