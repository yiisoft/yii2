Behaviors
=========

A behavior (also knows as *mixin*) can be used to enhance the functionality of an existing component without modifying the component's
code. In particular, a behavior can "inject" its public methods and properties into the component, making them directly accessible
via the component itself. A behavior can also respond to  events triggered in the component, thus intercepting the normal
code execution. Unlike [PHP's traits](http://www.php.net/traits), behaviors can be attached to classes at runtime.

Using behaviors
---------------

A behavior can be attached to any class that extends from [[yii\base\Component]] either from code or via application
config.

### Attaching behaviors via `behaviors` method

In order to attach a behavior to a class you can implement the `behaviors` method of the component.
As an example, Yii provides the [[yii\behaviors\TimestampBehavior]] behavior for automatically updating timestamp
fields when saving an [[yii\db\ActiveRecord|Active Record]] model:

```php
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
            ],
        ];
    }
}
```

In the above, the name `timestamp` can be used to reference the behavior through the component. For example, `$user->timestamp`
gives the attached timestamp behavior instance. The corresponding array is the configuration used to create the
[[yii\behaviors\TimestampBehavior|TimestampBehavior]] object.

Besides responding to the insertion and update events of ActiveRecord, `TimestampBehavior` also provides a method `touch()`
that can assign the current timestamp to a specified attribute. As aforementioned, you can access this method directly
through the component, like the following:

```php
$user->touch('login_time');
```

If you do not need to access a behavior object, or the behavior does not need customization, you can also
use the following simplified format when specifying the behavior,

```php
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            // or the following if you want to access the behavior object
            // 'timestamp' => TimestampBehavior::className(),
        ];
    }
}
```

### Attaching behaviors dynamically

Another way to attach a behavior to a component is calling `attachBehavior` method like the followig:

```php
$component = new MyComponent();
$component->attachBehavior();
```

### Attaching behaviors from config

One can attach a behavior to a component when configuring it with a configuration array. The syntax is like the
following:

```php
return [
    // ...
    'components' => [
        'myComponent' => [
            // ...
            'as tree' => [
                'class' => 'Tree',
                'root' => 0,
            ],
        ],
    ],
];
```

In the config above `as tree` stands for attaching a behavior named `tree`, and the array will be passed to [[\Yii::createObject()]]
to create the behavior object.


Creating your own behaviors
---------------------------

To create your own behavior, you must define a class that extends [[yii\base\Behavior]].

```php
namespace app\components;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
}
```

To make it customizable, like [[yii\behaviors\TimestampBehavior]], add public properties:

```php
namespace app\components;

use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $attr;
}
```

Now, when the behavior is used, you can set the attribute to which you'd want the behavior to be applied:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            'mybehavior' => [
                'class' => 'app\components\MyBehavior',
                'attr' => 'member_type'
            ],
        ];
    }
}
```

Behaviors are normally written to take action when certain events occur. Below we're implementing `events` method
to assign event handlers:

```php
namespace app\components;

use yii\base\Behavior;
use yii\db\ActiveRecord;

class MyBehavior extends Behavior
{
    public $attr;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
        ];
    }

    public function beforeInsert() {
        $model = $this->owner;
        // Use $model->$attr
    }

    public function beforeUpdate() {
        $model = $this->owner;
        // Use $model->$attr
    }
}
```
