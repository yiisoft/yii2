Behaviors
=========

A behavior (also knows as mixin) can be used to enhance the functionality of an existing component without modifying its
code. In particular, it can "inject" its own methods and properties into the component and make them directly accessible
via the component. It can also respond to the events triggered in the component and thus intercept the normal
code execution. Unlike PHP traits, behaviors could be attached to classes at runtime.

Using behaviors
---------------

Behavior can be attached to any class that extends from `Component`. In order to do so you need to implement `behaviors`
method. Yii provides `AutoTimestamp` behavior that handles updating timestamp fields on saving active record model.

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

In the above `class` value is a string containing fully qualified behavior class name. All the other key-values are
assigned to corresponding properties of the class.

Creating your own behaviors
---------------------------

TBD