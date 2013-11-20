Events
======

TBD, see also [Component.md](../api/base/Component.md).

There is no longer the need to define an `on`-method in order to define an event in Yii 2.0.
Instead, you can use whatever event names. To attach a handler to an event, you should
use the `on` method now:

```php
$component->on($eventName, $handler);
// To detach the handler, use:
// $component->off($eventName, $handler);
```


When you attach a handler, you can now associate it with some parameters which can be later
accessed via the event parameter by the handler:

```php
$component->on($eventName, $handler, $params);
```


Because of this change, you can now use "global" events. Simply trigger and attach handlers to
an event of the application instance:

```php
Yii::$app->on($eventName, $handler);
....
// this will trigger the event and cause $handler to be invoked.
Yii::$app->trigger($eventName);
```

If you need to handle all instances of a class instead of the object you can attach a handler like the following:

```php
Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
	Yii::trace(get_class($event->sender) . ' is inserted.');
});
```

The code above defines a handler that will be triggered for every Active Record object's `EVENT_AFTER_INSERT` event.
