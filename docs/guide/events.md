Events
======

Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
is triggered (i.e. comment will be added), our custom code will be executed.

Events are very useful both to make your components flexible and to hook into framework and extensions workflow.

Triggering events
-----------------

Any component can trigger events using `trigger` method:

```php
$this->trigger('myEvent');

// or

$event = new CreateUserEvent(); // extended from yii\base\Event
$event->userName = 'Alexander';
$this->trigger('createUserEvent', $event);
```

Event name should be unique within the class it is defined at. Event names are *case-sensitive*. It is a good practice
to define event names using class constants:

```php
class Mailer extends Component
{
    const EVENT_SEND_EMAIL = 'sendEmail';

    public function send()
    {
        // ...
        $this->trigger(self::EVENT_SEND_EMAIL);
    }
}
```

Attaching event handlers
------------------------

One or multiple PHP callbacks, called *event handlers*, can be attached to an event. When an event is raised, the event
handlers will be invoked automatically in the order they were attached.

There are two main methods of attaching event handlers. It can be done either via code or via application config.

> Tip: In order to get up to date list of framework and extension events search code for `->trigger`.

### Attaching event handlers via code

You can assign event handlers using `on` method of the component instance. The method's first argument is the name of
the event to watch for; the second is the handler to be called when that event occurs:

```php
$component->on($eventName, $handler);
```

The handler must be a valid PHP callback. This could be represented as:

- The name of a global function.
- An array consisting of a model name and method name.
- An array consisting of an object and a method name.
- An anonymous function.

```php
// Global function:
$component->on($eventName, 'functionName');

// Model and method names:
$component->on($eventName, ['Modelname', 'functionName']);

// Object and method name:
$component->on($eventName, [$obj, 'functionName']);

// Anonymous function:
$component->on($eventName, function ($event) {
    // Use $event.
});
```

As shown in the anonymous function example, the event handling function must be defined so that it takes one argument.
This will be an [[yii\base\Event]] object.

In order to pass extra data supply it via third argument:

```php
$component->on($eventName, function ($event) {
    // the extra data can be accessed via $event->data
}, $extraData);
```

### Attaching event handlers via config

It is possible to use application config to attach event hanelers:

```php
return [
    // ...
    'components' => [
        'db' => [
            // ...
            'on afterOpen' => function ($event) {
                // do something right after connected to database
            }
        ],
    ],
];
```

Removing Event Handlers
-----------------------

The correspondoing `off` method removes an event handler:

```php
$component->off($eventName);
```

Yii supports the ability to associate multiple handlers with the same event. When using `off` as in the above,
every handler is removed. To remove only a specific handler, provide that as the second argument to `off`:

```php
$component->off($eventName, $handler);
```

The `$handler` should be presented in the `off` method in the same way as was presented in `on` in order to remove it.

Global Events
-------------

You can use "global" events instead of per-component ones. To trigger a global event use an application instance instead
of specific component:

```php
Yii::$app->trigger($eventName);
```

In order to attach a handler to it use the following:

```php
Yii::$app->on($eventName, $handler);
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
