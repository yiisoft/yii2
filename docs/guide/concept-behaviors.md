Behaviors
=========

Behaviors are instances of [[yii\base\Behavior]] or its child class. Behaviors, also known
as [mixins](http://en.wikipedia.org/wiki/Mixin), allow you to enhance the functionality
of an existing [[yii\base\Component|component]] class without the need of changing its class inheritance.
When a behavior is attached to a component, it will "inject" its methods and properties into the component,
and you can access these methods and properties as if they are defined by the component class. Moreover, a behavior
can respond to the [events](concept-events.md) triggered by the component so that it can customize or adapt the normal
code execution of the component.


Using Behaviors <a name="using-behaviors"></a>
---------------

To use a behavior, you first need to attach it to a [[yii\base\Component|component]]. We will describe how to
attach a behavior in the next subsection.

Once a behavior is attached to a component, its usage is straightforward.

You can access a *public* member variable or a [property](concept-properties.md) defined by a getter and/or a setter
of the behavior through the component it is attached to, like the following,

```php
// "prop1" is a property defined in the behavior class
echo $component->prop1;
$component->prop1 = $value;
```

You can also call a *public* method of the behavior similarly,

```php
// bar() is a public method defined in the behavior class
$component->bar();
```

As you can see, although `$component` does not define `prop1` and `bar()`, they can be used as if they are part
of the component definition.

If two behaviors define the same property or method and they are both attached to the same component,
the behavior that is attached to the component first will take precedence when the property or method is being accessed.

A behavior may be associated with a name when it is attached to a component. If this is the case, you may
access the behavior object using the name, like the following,

```php
$behavior = $component->getBehavior('myBehavior');
```

You may also get all behaviors attached to a component:

```php
$behaviors = $component->getBehaviors();
```


Attaching Behaviors <a name="attaching-behaviors"></a>
-------------------

You can attach a behavior to a [[yii\base\Component|component]] either statically or dynamically. The former
is more commonly used in practice.

To attach a behavior statically, override the [[yii\base\Component::behaviors()|behaviors()]] method of the component
class that it is being attached. For example,

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // anonymous behavior, behavior class name only
            MyBehavior::className(),

            // named behavior, behavior class name only
            'myBehavior2' => MyBehavior::className(),

            // anonymous behavior, configuration array
            [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // named behavior, configuration array
            'myBehavior4' => [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

The [[yii\base\Component::behaviors()|behaviors()]] method should return a list of behavior [configurations](concept-configurations.md).
Each behavior configuration can be either a behavior class name or a configuration array.

You may associate a name with a behavior by specifying the array key corresponding to the behavior configuration.
In this case, the behavior is called a *named behavior*. In the above example, there are two named behaviors:
`myBehavior2` and `myBehavior4`. If a behavior is not associated with a name, it is called an *anonymous behavior*.


To attach a behavior dynamically, call the [[yii\base\Component::attachBehavior()]] method of the component
that it is attached to. For example,

```php
use app\components\MyBehavior;

// attach a behavior object
$component->attachBehavior('myBehavior1', new MyBehavior);

// attach a behavior class
$component->attachBehavior('myBehavior2', MyBehavior::className());

// attach a configuration array
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::className(),
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

You may also attach behaviors through [configurations](concept-configurations.md). For more details, please
refer to the [Configurations](concept-configurations.md#configuration-format) section.


Detaching Behaviors <a name="detaching-behaviors"></a>
-------------------

To detach a behavior, you can call [[yii\base\Component::detachBehavior()]] with the name associated with the behavior:

```php
$component->detachBehavior('myBehavior1');
```

You may also detach *all* behaviors:

```php
$component->detachBehaviors();
```


Defining Behaviors <a name="defining-behaviors"></a>
------------------

To define a behavior, create a class by extending from [[yii\base\Behavior]] or its child class. For example,

```php
namespace app\components;

use yii\base\Model;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

The above code defines the behavior class `app\components\MyBehavior` which will provide two properties
`prop1` and `prop2`, and one method `foo()` to the component it is attached to. Note that property `prop2`
is defined via the getter `getProp2()` and the setter `setProp2()`. This is so because [[yii\base\Object]]
is an ancestor class of [[yii\base\Behavior]], which supports defining [properties](concept-properties.md) by getters/setters.

Within a behavior, you can access the component that the behavior is attached to through the [[yii\base\Behavior::owner]] property.

If a behavior needs to respond to the events triggered by the component it is attached to, it should override the
[[yii\base\Behavior::events()]] method. For example,

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

The [[yii\base\Behavior::events()|events()]] method should return a list of events and their corresponding handlers.
The above example declares that the [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event and
its handler `beforeValidate()`. When specifying an event handler, you may use one of the following formats:

* a string that refers to the name of a method of the behavior class, like the example above;
* an array of an object or class name, and a method name, e.g., `[$object, 'methodName']`;
* an anonymous function.

The signature of an event handler should be as follows, where `$event` refers to the event parameter. Please refer
to the [Events](concept-events.md) section for more details about events.

```php
function ($event) {
}
```


Using `TimestampBehavior` <a name="using-timestamp-behavior"></a>
-------------------------

To wrap up, let's take a look at [[yii\behaviors\TimestampBehavior]] - a behavior that supports automatically
updating the timestamp attributes of an [[yii\db\ActiveRecord|Active Record]] when it is being saved.

First, attach this behavior to the [[yii\db\ActiveRecord|Active Record]] class that you plan to use.

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
```

The behavior configuration above specifies that

* when the record is being inserted, the behavior should assign the current timestamp to
  the `created_at` and `updated_at` attributes;
* when the record is being updated, the behavior should assign the current timestamp to the `updated_at` attribute.

Now if you have a `User` object and try to save it, you will find its `created_at` and `updated_at` are automatically
filled with the current timestamp:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // shows the current timestamp
```

The [[yii\behaviors\TimestampBehavior|TimestampBehavior]] also offers a useful method
[[yii\behaviors\TimestampBehavior::touch()|touch()]] which will assign the current timestamp
to a specified attribute and save it to the database:

```php
$user->touch('login_time');
```


Comparison with Traits <a name="comparison-with-traits"></a>
----------------------

While behaviors are similar to [traits](http://www.php.net/traits) in that they both "inject" their
properties and methods to the primary class, they differ in many aspects. As explained below, they
both have pros and cons. They are more like complements rather than replacements to each other.


<a name="pros-for-behaviors"></a>
### Pros for Behaviors

Behavior classes, like normal classes, support inheritance. Traits, on the other hand,
can be considered as language-supported copy and paste. They do not support inheritance.

Behaviors can be attached and detached to a component dynamically without requiring you to modify the component class.
To use a trait, you must modify the class using it.

Behaviors are configurable while traits are not.

Behaviors can customize the code execution of a component by responding to its events.

When there is name conflict among different behaviors attached to the same component, the conflict is
automatically resolved by respecting the behavior that is attached to the component first.
Name conflict caused by different traits requires you to manually resolve it by renaming the affected
properties or methods.


### Pros for Traits <a name="pros-for-traits"></a>

Traits are much more efficient than behaviors because behaviors are objects which take both time and memory.

IDEs are more friendly to traits as they are language construct.

