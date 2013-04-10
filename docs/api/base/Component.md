Component is the base class that implements the *property*, *event* and *behavior* features.

Component provides the *event* and *behavior* features, in addition to the *property* feature which is implemented in
its parent class [[Object]].

Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
is triggered (i.e. comment will be added), our custom code will be executed.

An event is identified by a name that should be unique within the class it is defined at. Event names are *case-sensitive*.

One or multiple PHP callbacks, called *event handlers*, can be attached to an event. You can call [[trigger()]] to
raise an event. When an event is raised, the event handlers will be invoked automatically in the order they were
attached.

To attach an event handler to an event, call [[on()]]:

~~~
$post->on('update', function($event) {
    // send email notification
});
~~~

In the above, an anonymous function is attached to the "update" event of the post. You may attach
the following types of event handlers:

- anonymous function: `function($event) { ... }`
- object method: `array($object, 'handleAdd')`
- static class method: `array('Page', 'handleAdd')`
- global function: `'handleAdd'`

The signature of an event handler should be like the following:

~~~
function foo($event)
~~~

where `$event` is an [[Event]] object which includes parameters associated with the event.

You can also attach a handler to an event when configuring a component with a configuration array.
The syntax is like the following:

~~~
array(
    'on add' => function($event) { ... }
)
~~~

where `on add` stands for attaching an event to the `add` event.

Sometimes, you may want to associate extra data with an event handler when you attach it to an event
and then access it when the handler is invoked. You may do so by

~~~
$post->on('update', function($event) {
    // the data can be accessed via $event->data
}, $data);
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