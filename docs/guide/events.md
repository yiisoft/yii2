Events
======

TBD, see also [Component.md](../api/base/Component.md).

[[ADD INTRODUCTION]]

Creating Event Handlers
-----------------------

In Yii 1, events were defined using the `onEventName` method syntax, such as `onBeforeSave`. This is no longer necessary in Yii 2, as event handling is now assigned using the `on` method. The method's first argument is the name of the event to watch for; the second is the handling method to be called when that event occurs:

```php
$component->on($eventName, $handler);
```

[[LINK TO LIST OF EVENTS]]

The handler must be a valid PHP callback. This could be represented as:

* The name of a global function
* An array consisting of a model name and method name
* An array consisting of an object and a method name
* An anonymous function

```php
// Global function:
$component->on($eventName, 'functionName');

// Model and method names:
$component->on($eventName, ['Modelname', 'functionName']);

// Object and method name:
$component->on($eventName, [$obj, 'functionName']);

// Anonymous function:
$component->on($eventName, function($event) {
	// Use $event.
});
```

As shown in the anonymous function example, the event handling function must be defined so that it takes one argument. This will be an [[Event]] object.


Removing Event Handlers
-----------------------

The correspondoing `off` method removes an event handler:

```php
// $component->off($eventName);
```

Yii supports the ability to associate multiple handlers with the same event. When using `off` as in the above, every handler is removed. To remove only a specific handler, provide that as the second argument to `off`:

```php
// $component->off($eventName, $handler);
```

The `$handler` should be presented in the `off` method in the same way as was presented in `on` in order to remove it.

Event Parameters
----------------

You can make your event handlers easier to work with and more powerful by passing additional values as parameters. 

```php
$component->on($eventName, $handler, $params);
```

The passed parameters will be available in the event handler through `$event->data`, which will be an array.

[[NEED TO CONFIRM THE ABOVE]]

Global Events
-------------

Thanks to the change in Yii 2 as to how event handlers are created, you can now use "global" events. To create a global event, simply attach handlers to an event on the application instance:

```php
Yii::$app->on($eventName, $handler);
```

You can use the `trigger` method to trigger these events manually:

```php
// this will trigger the event and cause $handler to be invoked:
Yii::$app->trigger($eventName);
```

Class Events
------------

You can also attach event handlers to all instances of a class instead of individual instances. To do so, use the static `Event::on` method:

```php
Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
	Yii::trace(get_class($event->sender) . ' is inserted.');
});
```

The code above defines a handler that will be triggered for every Active Record object's `EVENT_AFTER_INSERT` event.
