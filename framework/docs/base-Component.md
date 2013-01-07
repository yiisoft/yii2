Component provides the *event* and *behavior* features, in addition to the *property* feature which is implemented in
its parent class [[Object]].

Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
is triggered, our custom code will be executed.

An event is identified by a name (unique within the class it is defined). Event names are *case-sensitive*.

An event can be attached with one or multiple PHP callbacks, called *event handlers*. One can call [[trigger()]] to
raise an event. When an event is raised, the attached event handlers will be invoked automatically in the order they are
attached to the event.

To attach an event handler to an event, call [[on()]]. For example,

~~~
$comment->on('add', function($event) {
    // send email notification
});
~~~

In the above, we attach an anonymous function to the "add" event of the comment. Valid event handlers include:

- anonymous function: `function($event) { ... }`
- object method: `array($object, 'handleAdd')`
- static method: `array('Page', 'handleAdd')`
- global function: `'handleAdd'`

The signature of an event handler should be like the following:

~~~
function foo($event)
~~~

where `$event` is an [[Event]] object which includes parameters associated with the event.

One can also attach an event handler to an event when configuring a component with a configuration array. The syntax is
like the following:

~~~
array(
    'on add' => function($event) { ... }
)
~~~

where `on add` stands for attaching an event to the `add` event.

One can call [[getEventHandlers()]] to retrieve all event handlers that are attached to a specified event. Because this
method returns a [[Vector]] object, we can manipulate this object to attach/detach event handlers, or adjust their
relative orders.

~~~
$handlers = $comment->getEventHandlers('add');
$handlers->insertAt(0, $callback); // attach a handler as the first one
$handlers[] = $callback;           // attach a handler as the last one
unset($handlers[0]);               // detach the first handler
~~~


A behavior is an instance of [[Behavior]] or its child class. A component can be attached with one or multiple
behaviors. When a behavior is attached to a component, its public properties and methods can be accessed via the
component directly, as if the component owns those properties and methods.

To attach a behavior to a component, declare it in [[behaviors()]], or explicitly call [[attachBehavior]]. Behaviors
declared in [[behaviors()]] are automatically attached to the corresponding component.

One can also attach a behavior to a component when configuring it with a configuration array. The syntax is like the
following:

~~~
array(
    'as tree' => array(
        'class' => 'Tree',
    ),
)
~~~

where `as tree` stands for attaching a behavior named `tree`, and the array will be passed to [[\Yii::createObject()]]
to create the behavior object.