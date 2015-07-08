Behaviors
=========

Behaviors are instances of [[yii\base\Behavior]], or of a child class. Behaviors, also known
as [mixins](http://en.wikipedia.org/wiki/Mixin), allow you to enhance the functionality
of an existing [[yii\base\Component|component]] class without needing to change the class's inheritance.
Attaching a behavior to a component "injects" the behavior's methods and properties into the component, making those methods and properties accessible as if they were defined in the component class itself. Moreover, a behavior
can respond to the [events](concept-events.md) triggered by the component, which allows behaviors to also customize the normal
code execution of the component.


Defining Behaviors <span id="defining-behaviors"></span>
------------------

To define a behavior, create a class that extends [[yii\base\Behavior]], or extends a child class. For example:

```php
namespace app\components;

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

The above code defines the behavior class `app\components\MyBehavior`, with two properties--
`prop1` and `prop2`--and one method `foo()`. Note that property `prop2`
is defined via the getter `getProp2()` and the setter `setProp2()`. This is the case because [[yii\base\Behavior]] extends [[yii\base\Object]] and therefore supports defining [properties](concept-properties.md) via getters and setters.

Because this class is a behavior, when it is attached to a component, that component will then also have the the `prop1` and `prop2` properties and the `foo()` method.

> Tip: Within a behavior, you can access the component that the behavior is attached to through the [[yii\base\Behavior::owner]] property.

Handling Component Events
------------------

If a behavior needs to respond to the events triggered by the component it is attached to, it should override the
[[yii\base\Behavior::events()]] method. For example:

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
The above example declares that the [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event exists and defines
its handler, `beforeValidate()`. When specifying an event handler, you may use one of the following formats:

* a string that refers to the name of a method of the behavior class, like the example above
* an array of an object or class name, and a method name as a string (without parentheses), e.g., `[$object, 'methodName']`;
* an anonymous function

The signature of an event handler should be as follows, where `$event` refers to the event parameter. Please refer
to the [Events](concept-events.md) section for more details about events.

```php
function ($event) {
}
```

Attaching Behaviors <span id="attaching-behaviors"></span>
-------------------

You can attach a behavior to a [[yii\base\Component|component]] either statically or dynamically. The former is more common in practice.

To attach a behavior statically, override the [[yii\base\Component::behaviors()|behaviors()]] method of the component
class to which the behavior is being attached. The [[yii\base\Component::behaviors()|behaviors()]] method should return a list of behavior [configurations](concept-configurations.md).
Each behavior configuration can be either a behavior class name or a configuration array:

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

You may associate a name with a behavior by specifying the array key corresponding to the behavior configuration. In this case, the behavior is called a *named behavior*. In the above example, there are two named behaviors:
`myBehavior2` and `myBehavior4`. If a behavior is not associated with a name, it is called an *anonymous behavior*.


To attach a behavior dynamically, call the [[yii\base\Component::attachBehavior()]] method of the component to which the behavior is being attached:

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

You may attach multiple behaviors at once using the [[yii\base\Component::attachBehaviors()]] method:

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // a named behavior
    MyBehavior::className(),          // an anonymous behavior
]);
```

You may also attach behaviors through [configurations](concept-configurations.md) like the following: 

```php
[
    'as myBehavior2' => MyBehavior::className(),

    'as myBehavior3' => [
        'class' => MyBehavior::className(),
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

For more details,
please refer to the [Configurations](concept-configurations.md#configuration-format) section.

Using Behaviors <span id="using-behaviors"></span>
---------------

To use a behavior, first attach it to a [[yii\base\Component|component]] per the instructions above. Once a behavior is attached to a component, its usage is straightforward.

You can access a *public* member variable or a [property](concept-properties.md) defined by a getter and/or a setter
of the behavior through the component it is attached to:

```php
// "prop1" is a property defined in the behavior class
echo $component->prop1;
$component->prop1 = $value;
```

You can also call a *public* method of the behavior similarly:

```php
// foo() is a public method defined in the behavior class
$component->foo();
```

As you can see, although `$component` does not define `prop1` and `foo()`, they can be used as if they are part
of the component definition due to the attached behavior.

If two behaviors define the same property or method and they are both attached to the same component,
the behavior that is attached to the component *first* will take precedence when the property or method is accessed.

A behavior may be associated with a name when it is attached to a component. If this is the case, you may
access the behavior object using the name:

```php
$behavior = $component->getBehavior('myBehavior');
```

You may also get all behaviors attached to a component:

```php
$behaviors = $component->getBehaviors();
```


Detaching Behaviors <span id="detaching-behaviors"></span>
-------------------

To detach a behavior, call [[yii\base\Component::detachBehavior()]] with the name associated with the behavior:

```php
$component->detachBehavior('myBehavior1');
```

You may also detach *all* behaviors:

```php
$component->detachBehaviors();
```


Using `TimestampBehavior` <span id="using-timestamp-behavior"></span>
-------------------------

To wrap up, let's take a look at [[yii\behaviors\TimestampBehavior]]. This behavior supports automatically
updating the timestamp attributes of an [[yii\db\ActiveRecord|Active Record]] model anytime the model is saved (e.g., on insert or update).

First, attach this behavior to the [[yii\db\ActiveRecord|Active Record]] class that you plan to use:

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

The behavior configuration above specifies that when the record is being:

* inserted, the behavior should assign the current timestamp to
  the `created_at` and `updated_at` attributes
* updated, the behavior should assign the current timestamp to the `updated_at` attribute

With that code in place, if you have a `User` object and try to save it, you will find its `created_at` and `updated_at` are automatically
filled with the current timestamp:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // shows the current timestamp
```

The [[yii\behaviors\TimestampBehavior|TimestampBehavior]] also offers a useful method
[[yii\behaviors\TimestampBehavior::touch()|touch()]], which will assign the current timestamp
to a specified attribute and save it to the database:

```php
$user->touch('login_time');
```

Comparing Behaviors with Traits <span id="comparison-with-traits"></span>
----------------------

While behaviors are similar to [traits](http://www.php.net/traits) in that they both "inject" their
properties and methods to the primary class, they differ in many aspects. As explained below, they
both have pros and cons. They are more like complements to each other rather than alternatives.


### Reasons to Use Behaviors <span id="pros-for-behaviors"></span>

Behavior classes, like normal classes, support inheritance. Traits, on the other hand,
can be considered as language-supported copy and paste. They do not support inheritance.

Behaviors can be attached and detached to a component dynamically without requiring modification of the component class.
To use a trait, you must modify the code of the class using it.

Behaviors are configurable while traits are not.

Behaviors can customize the code execution of a component by responding to its events.

When there can be name conflicts among different behaviors attached to the same component, the conflicts are
automatically resolved by prioritizing the behavior attached to the component first.
Name conflicts caused by different traits requires manual resolution by renaming the affected
properties or methods.


### Reasons to Use Traits <span id="pros-for-traits"></span>

Traits are much more efficient than behaviors as behaviors are objects that take both time and memory.

IDEs are more friendly to traits as they are a native language construct.

