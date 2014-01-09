Behaviors
=========

A behavior (also knows as *mixin*) can be used to enhance the functionality of an existing component without modifying the component's
code. In particular, a behavior can "inject" its own methods and properties into the component, making them directly accessible
via the component itslef. A behavior can also respond to  events triggered in the component, thus intercepting the normal
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
					ActiveRecord::EVENT_BEFORE_INSERT => ['create_time', 'update_time'],
					ActiveRecord::EVENT_BEFORE_UPDATE => 'update_time',
				],
			],
		];
	}
}
```

In the above, the `class` value is a string representing the fully qualified behavior class name. All of the other key-value pairs represent corresponding public properties of the `AutoTimestamp` class, thereby customizing how the behavior functions.

Creating your own behaviors
---------------------------

[[NEEDS UPDATING FOR Yii 2]]

To create your own behavior, you must define a class that implements the `IBehavior` interface. This can be accomplished by extending `CBehavior`. More specifically, you can extend `CModelBehavior` or `CActiveRecordBehavior` for behaviors to be used specifically with models or with Active Record models. 

```php
class MyBehavior extends CActiveRecordBehavior
{
}
```

To make your behavior customizable, like `AutoTimestamp`, add public properties:

```php
class MyBehavior extends CActiveRecordBehavior
{
	public $attr;
}
```

Now, when the behavior is used, you can set the attribute to which you'd want the behavior to be applied:

```php
class User extends ActiveRecord
{
	// ...

	public function behaviors()
	{
		return [
			'mybehavior' => [
				'class' => 'ext\mybehavior\MyBehavior',
				'attr' => 'member_type'
				],
			],
		];
	}
}
```

Behaviors are normally written to take action when certain model-related events occur, such as `beforeSave` or `afterFind`. You can write your behaviors to have the corresponding method. Within the method, you can access the model instance through `$this->getOwner()`:

```php
class MyBehavior extends CActiveRecordBehavior
{
	public $attr;
	public function beforeSave() {
		$model = $this->getOwner();
		// Use $model->$attr
	}
}
```