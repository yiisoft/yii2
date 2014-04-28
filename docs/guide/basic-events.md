Events
======

Yii uses events to "inject" custom code into existing code at certain execution points. For example, a comment object can trigger
an "add" event when the user adds a comment to a post. 

Events are very useful for two reasons. First, they can make your components more flexible. Second, you can hook your own code into the regular workflow of both the framework and the extensions in use.

Attaching event handlers
------------------------

One or multiple PHP callbacks, called *event handlers*, can be attached to an event. When the event occurs, the event
handlers will be invoked automatically in the order in which they were attached.

There are two main ways to attaching event handlers. You can do so either via inline code or via the application configuration.

> Tip: In order to get an up-to-date list of framework and extension events, search the framework code for `->trigger`.

### Attaching event handlers via code

You can assign event handlers witin your code using the `on` method of a component object. The method's first argument is the name of
the event to watch for; the second is the handler (i.e., function) to be called when that event occurs:

```php
$component->on($eventName, $handler);
```

The handler must be a valid PHP callback. This could be represented as any of the following:

- The name of a global function
- An array consisting of a model name and method name
- An array consisting of an object and a method name
- An anonymous function

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

In order to pass extra data to the handler, supply the data as a third argument to the `on` method. Within the handler, the extra data will be available in `$event->data`:

```php
$component->on($eventName, function ($event) {
    // the extra data can be accessed via $event->data
}, $extraData);
```

### Attaching event handlers via config

You can also attach event handlers within your configuration file. To do so, add an element to the component to which the handler should be attached. The syntax is `"on <event>" => handler`:

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

When attaching event handlers in this way, the handler must be an anonymous function.

Triggering events
-----------------

Most events will be triggered through the normal workflow. For example, the "beforeSave" event occurs before an Active Record model is saved. 

But you can also manually trigger an event using the `trigger` method, invoked on the component with the attached event handler:

```php
$this->trigger('myEvent');

// or

$event = new CreateUserEvent(); // extended from yii\base\Event
$event->userName = 'Alexander';
$this->trigger('createUserEvent', $event);
```

The event name needs to be unique within the class it is defined. Event names are *case-sensitive*, but it is a good practice
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

Removing Event Handlers
-----------------------

The corresponding `off` method removes an event handler:

```php
$component->off($eventName);
```

Yii supports the ability to associate multiple handlers with the same event. When using `off` as in the above,
every handler will be removed. To remove only a specific handler, provide that as the second argument to `off`:

```php
$component->off($eventName, $handler);
```

The `$handler` should be presented in the `off` method in the same way as was presented in the `on` call in order to remove it. 

> Tip: You probably don't want to use anonymous functions for event handlers that you expect to later remove.

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
