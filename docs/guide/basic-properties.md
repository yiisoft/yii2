Object Properties
=================

In PHP, class member variables are also called *properties*. They are part of a class definition and are used
to represent the state of a class instance. In practice, you may often want to do some special handling when
a property is being read or modified. For example, you may want to trim a string when it is being assigned
to a `label` property. You could use the following code to achieve this task:

```php
$object->label = trim($label);
```

The drawback of the above code is that you have to call `trim()` everywhere whenever you modify the `label`
property. And if in future, the `label` property has a new requirement, such as the first letter must be turned
into upper case, you would have to modify all those places - a practice you want to avoid as much as possible.

To solve this problem, Yii introduces a base class called [[yii\base\Object]] to support defining properties
based on *getter* and *setter* class methods. If a class needs such a support, it should extend from
[[yii\base\Object]] or its child class.

> Info: Nearly every core class in the Yii framework extends from [[yii\base\Object]] or its child class.
  This means whenever you see a getter or setter in a core class, you can use it like a property.

A getter method is a method whose name starts with the word `get`, while a setter method starts with `set`.
The name after the `get` or `set` prefix defines the name of a property. For example, a getter `getLabel()` and/or
a setter `setLabel()` defines a property named `label`, as shown in the following code:

```php
namespace app\components;

use yii\base\Object;

class Foo extend Object
{
    private $_label;

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = trim($value);
    }
}
```

Properties defined by getters/setters can be used like class member variables. The main difference is that
when such a property is being read, the corresponding getter method will be called; and when the property is
being assigned, the corresponding setter method will be called. For example,

```php
// equivalent to $label = $object->getLabel();
$label = $object->label;

// equivalent to $object->setLabel('abc');
$object->label = 'abc';
```

A property defined by a getter without a setter is *read only*. Trying to assign a value to such a property will cause
an [[yii\base\InvalidCallException|InvalidCallException]]. Similarly, a property defined by a setter without a getter
is *write only*, and trying to read such a property will also cause an exception. It is not common to have write-only
properties.

There are several special rules or limitations of the properties defined based on getters and setters.

* The names of such properties are *case-insensitive*. For example, `$object->label` and `$object->Label` are the same.
  This is because PHP method names are case-insensitive.
* If the name of such a property is the same as a class member variable, the latter will take precedence.
  For example, if the above `Foo` class has a member variable `label`, then the assignment `$object->label = 'abc'`
  will happen to the member variable instead of the setter `setLabel()`.
* The properties do not support visibility. It makes no difference for the visibility of a property
  if the defining getter or setter method is public, protected or private.
* The properties can only be defined by *non-static* getters and/or setters. Static methods do not count.

Back to the problem we described at the very beginning, instead of calling `trim()` everywhere, we are calling it
only within the setter `setLabel()`. And if a new requirement comes that the first letter of the label should
be turned into upper case, we just need to modify the `setLabel()` method without touching any other code.
