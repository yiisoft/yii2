Events
======

Events allow you to inject custom code into existing code at certain execution points. You can attach custom
code to an event so that when the event is triggered, the code gets executed automatically. For example,
a mailer object may trigger a `messageSent` event when it successfully sends a message. If you want to keep
track of the messages that are successfully sent, you could then simply attach the tracking code to the `messageSent` event.

Yii introduces a base class called [[yii\base\Component]] to support events. If a class needs to trigger
events, it should extend from [[yii\base\Component]], or from a child class.


Event Handlers <span id="event-handlers"></span>
--------------

An event handler is a [PHP callback](http://www.php.net/manual/en/language.types.callable.php) that gets executed
when the event it is attached to is triggered. You can use any of the following callbacks:

- a global PHP function specified as a string (without parentheses), e.g., `'trim'`;
- an object method specified as an array of an object and a method name as a string (without parentheses), e.g., `[$object, 'methodName']`;
- a static class method specified as an array of a class name and a method name as a string (without parentheses), e.g., `['ClassName', 'methodName']`;
- an anonymous function, e.g., `function ($event) { ... }`.

The signature of an event handler is:

```php
function ($event) {
    // $event is an object of yii\base\Event or a child class
}
```

Through the `$event` parameter, an event handler may get the following information about the event that occurred:

- [[yii\base\Event::name|event name]];
- [[yii\base\Event::sender|event sender]]: the object whose `trigger()` method was called;
- [[yii\base\Event::data|custom data]]: the data that is provided when attaching the event handler (to be explained next).


Attaching Event Handlers <span id="attaching-event-handlers"></span>
------------------------

You can attach a handler to an event by calling the [[yii\base\Component::on()]] method. For example:

```php
$foo = new Foo;

// this handler is a global function
$foo->on(Foo::EVENT_HELLO, 'function_name');

// this handler is an object method
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// this handler is a static class method
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// this handler is an anonymous function
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // event handling logic
});
```

You may also attach event handlers through [configurations](concept-configurations.md). For more details, please
refer to the [Configurations](concept-configurations.md#configuration-format) section.


When attaching an event handler, you may provide additional data as the third parameter to [[yii\base\Component::on()]].
The data will be made available to the handler when the event is triggered and the handler is called. For example:

```php
// The following code will display "abc" when the event is triggered
// because $event->data contains the data passed as the 3rd argument to "on"
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

Event Handler Order
-------------------

You may attach one or more handlers to a single event. When an event is triggered, the attached handlers
will be called in the order that they were attached to the event. If a handler needs to stop the invocation of the
handlers that follow it, it may set the [[yii\base\Event::handled]] property of the `$event` parameter to be `true`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

By default, a newly attached handler is appended to the existing handler queue for the event.
As a result, the handler will be called in the last place when the event is triggered.
To insert the new handler at the start of the handler queue so that the handler gets called first, you may call [[yii\base\Component::on()]], passing `false` for the fourth parameter `$append`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

Triggering Events <span id="triggering-events"></span>
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

With the above code, any calls to `bar()` will trigger an event named `hello`.

> Tip: It is recommended to use class constants to represent event names. In the above example, the constant
  `EVENT_HELLO` represents the `hello` event. This approach has three benefits. First, it prevents typos. Second, it can make events recognizable for IDE
  auto-completion support. Third, you can tell what events are supported in a class by simply checking its constant declarations.

Sometimes when triggering an event you may want to pass along additional information to the event handlers.
For example, a mailer may want to pass the message information to the handlers of the `messageSent` event so that the handlers
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

When the [[yii\base\Component::trigger()]] method is called, it will call all handlers attached to
the named event.


Detaching Event Handlers <span id="detaching-event-handlers"></span>
------------------------

To detach a handler from an event, call the [[yii\base\Component::off()]] method. For example:

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
somewhere when it is attached to the event. In the above example, it is assumed that the anonymous
function is stored as a variable `$anonymousFunction`.

To detach *all* handlers from an event, simply call [[yii\base\Component::off()]] without the second parameter:

```php
$foo->off(Foo::EVENT_HELLO);
```


Class-Level Event Handlers <span id="class-level-event-handlers"></span>
--------------------------

The above subsections described how to attach a handler to an event on an *instance level*.
Sometimes, you may want to respond to an event triggered by *every* instance of a class instead of only by
a specific instance. Instead of attaching an event handler to every instance, you may attach the handler
on the *class level* by calling the static method [[yii\base\Event::on()]].

For example, an [Active Record](db-active-record.md) object will trigger an [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]
event whenever it inserts a new record into the database. In order to track insertions done by *every*
[Active Record](db-active-record.md) object, you may use the following code:

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted');
});
```

The event handler will be invoked whenever an instance of [[yii\db\ActiveRecord|ActiveRecord]], or one of its child classes, triggers
the [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] event. In the handler, you can get the object
that triggered the event through `$event->sender`.

When an object triggers an event, it will first call instance-level handlers, followed by the class-level handlers.

You may trigger a *class-level* event by calling the static method [[yii\base\Event::trigger()]]. A class-level
event is not associated with a particular object. As a result, it will cause the invocation of class-level event
handlers only. For example:

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    var_dump($event->sender);  // displays "null"
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

Note that, in this case, `$event->sender` is `null` instead of an object instance.

> Note: Because a class-level handler will respond to an event triggered by any instance of that class, or any child
  classes, you should use it carefully, especially if the class is a low-level base class, such as [[yii\base\BaseObject]].

To detach a class-level event handler, call [[yii\base\Event::off()]]. For example:

```php
// detach $handler
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// detach all handlers of Foo::EVENT_HELLO
Event::off(Foo::className(), Foo::EVENT_HELLO);
```


Events using interfaces <span id="interface-level-event-handlers"></span>
-------------

There is even more abstract way to deal with events. You can create a separated interface for the special event and
implement it in classes, where you need it.

For example, we can create the following interface:

```php
namespace app\interfaces;

interface DanceEventInterface
{
    const EVENT_DANCE = 'dance';
}
```

And two classes, that implement it:

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

To handle the `EVENT_DANCE`, triggered by any of these classes, call [[yii\base\Event::on()|Event::on()]] and
pass the interface class name as the first argument:

```php
Event::on('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, function ($event) {
    Yii::trace(get_class($event->sender) . ' just danced'); // Will log that Dog or Developer danced
});
```

You can trigger the event of those classes:

```php
// trigger event for Dog class
Event::trigger(Dog::className(), DanceEventInterface::EVENT_DANCE);

// trigger event for Developer class
Event::trigger(Developer::className(), DanceEventInterface::EVENT_DANCE);
```

But please notice, that you can not trigger all the classes, that implement the interface:

```php
// DOES NOT WORK. Classes that implement this interface will NOT be triggered.
Event::trigger('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```

To detach event handler, call [[yii\base\Event::off()|Event::off()]]. For example:

```php
// detaches $handler
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE, $handler);

// detaches all handlers of DanceEventInterface::EVENT_DANCE
Event::off('app\interfaces\DanceEventInterface', DanceEventInterface::EVENT_DANCE);
```


Global Events <span id="global-events"></span>
-------------

Yii supports a so-called *global event*, which is actually a trick based on the event mechanism described above.
The global event requires a globally accessible Singleton, such as the [application](structure-applications.md) instance itself.

To create the global event, an event sender calls the Singleton's `trigger()` method
to trigger the event, instead of calling the sender's own `trigger()` method. Similarly, the event handlers are attached to the event on the Singleton. For example:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // displays "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

A benefit of using global events is that you do not need an object when attaching a handler to the event
which will be triggered by the object. Instead, the handler attachment and the event triggering are both
done through the Singleton (e.g. the application instance).

However, because the namespace of the global events is shared by all parties, you should name the global events
wisely, such as introducing some sort of namespace (e.g. "frontend.mail.sent", "backend.mail.sent").
