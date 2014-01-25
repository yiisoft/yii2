Behaviors
=========

A behavior (also knows as *mixin*) can be used to enhance the functionality of an existing component without modifying the component's
code. In particular, a behavior can "inject" its own methods and properties into the component, making them directly accessible
via the component itself. A behavior can also respond to  events triggered in the component, thus intercepting the normal
code execution. Unlike [PHP's traits](http://www.php.net/traits), behaviors can be attached to classes at runtime.

Using behaviors
---------------

A behavior can be attached to any class that extends from `Component`. In order to attach a behavior to a class, the component class must implement the `behaviors`
method. As an example, Yii provides the `AutoTimestamp` behavior for automatically updating timestamp fields when saving an Active Record model:

```php
class User extends ActiveRecord
{
	// ...

	public function behaviors()
	{
		return [
			'timestamp' => [
				'class' => 'yii\behaviors\AutoTimestamp',
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
					ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
				],
			],
		];
	}
}
```

In the above, the `class` value is a string representing the fully qualified behavior class name. All of the other key-value pairs represent corresponding public properties of the `AutoTimestamp` class, thereby customizing how the behavior functions.

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

To make it customizable, like [[yii\behaviors\AutoTimestamp]], add public properties:

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
