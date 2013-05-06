Upgrading from Yii 1.1
======================

In this chapter, we list the major changes introduced in Yii 2.0 since version 1.1.
We hope this list will make it easier for you to upgrade from Yii 1.1 and quickly
master Yii 2.0 based on your existing Yii knowledge.


Component and Object
--------------------

Yii 2.0 breaks the `CComponent` class in 1.1 into two classes: `Object` and `Component`.
The `Object` class is a lightweight base class that allows defining class properties
via getters and setters. The `Component` class extends from `Object` and supports
the event feature and the behavior feature.

If your class does not need the event or behavior feature, you should consider using
`Object` as the based class. This is usually the case for classes that represent basic
data structures.


Object Configuration
--------------------

The `Object` class introduces a convention for configuring objects. In general, any
descendant class of `Object` should follow this convention when declaring a constructor:

~~~
class MyClass extends \yii\Object
{
    public function __construct($param1, $param2, $config = array())
    {
        // ...
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        // ... at this point, all configurations have been applied ...
    }
}
~~~

In descendant classes, you can override the `init()` method to do initialization work
that should be done after all configurations are applied.

By following this convention, you will be able to use the powerful object creation method via:

~~~
$object = Yii::createObject(array(
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
), $param1, $param2);
~~~


Events
------

There is no longer the need to define an `on`-method in order to define an event in Yii 2.0.
Instead, you can use whatever event names. To attach a handler to an event, you should
use the `on` method now:

~~~
$component->on($eventName, $handler);
// To detach the handler, use:
// $component->off($eventName, $handler);
~~~

When you attach a handler, you can now associate it with some parameters which can be later
accessed via the event parameter by the handler:

~~~
$component->on($eventName, $handler, $params);
~~~

Because of this change, you can now use "global" events. Simply trigger and attach handlers to
an event of the application instance:

~~~
Yii::$app->on($eventName, $handler);
....
// this will trigger the event and cause $handler to be invoked.
Yii::$app->trigger($eventName);
~~~


View
----

Yii 2.0 introduces a `View` class to represent the view part in the MVC pattern.
It can be configured globally through the "view" application component. It is also
accessible in any view file via `$this`. This is one of the biggest changes compared to 1.1:
**`$this` in a view file no longer refers to the controller or widget object.**
It refers to the view object that is used to render the view file. To access the controller
or the widget object, you have to use `$this->context` now.

Because you can access the view object through the "view" application component,
you can now render a view file like the following anywhere in your code, not necessarily
in controllers or widgets:

~~~
$content = Yii::$app->view->renderFile($viewFile, $params);
// You can also explicitly create a new View instance to do the rendering
// $view = new View;
// $view->renderFile($viewFile, $params);
~~~

Also, there is no more `CClientScript` in Yii 2.0. The `View` class has taken over its role
with significant improvements. For more details, please see the "assets" subsection.

TBD: built-in renderers

Models
------

Controllers
-----------

Themes
------

Console Applications
--------------------

I18N
----

Behaviors
---------

TBD

Action filters are replaced by behaviors (`ActiionFilter`).


Assets
------

Static Helpers
--------------

`ActiveForm`
------------


Query Builder
-------------


ActiveRecord
------------

User and Identity
-----------------

URL Management
--------------

Response
--------