Properties
==========

In PHP, class member variables are also called *properties*. These variables are part of the class definition, and are used
to represent the state of a class instance (i.e., to differentiate one instance of the class from another). In practice, you may often want to handle the reading or writing of properties in special ways. For example, you may want to always trim a string when it is being assigned
to a `label` property. You *could* use the following code to achieve this task:

```php
$object->label = trim($label);
```

The drawback of the above code is that you would have to call `trim()` everywhere in your code where you might set the `label`
property. If, in the future, the `label` property gets a new requirement, such as the first letter must be capitalized, you would again have to modify every bit of code that assigns a value to `label`. The repetition of code leads to bugs, and is a practice you want to avoid as much as possible.

To solve this problem, Yii introduces a base class called [[yii\base\Object]] that supports defining properties
based on *getter* and *setter* class methods. If a class needs that functionality, it should extend from
[[yii\base\Object]], or from a child class.

> Info: Nearly every core class in the Yii framework extends from [[yii\base\Object]] or a child class.
  This means that whenever you see a getter or setter in a core class, you can use it like a property.

A getter method is a method whose name starts with the word `get`; a setter method starts with `set`.
The name after the `get` or `set` prefix defines the name of a property. For example, a getter `getLabel()` and/or
a setter `setLabel()` defines a property named `label`, as shown in the following code:

```php
namespace app\components;

use yii\base\Object;

class Foo extends Object
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

(To be clear, the getter and setter methods create the property `label`, which in this case internally refers to a private attribute named `_label`.)

Properties defined by getters and setters can be used like class member variables. The main difference is that
when such property is being read, the corresponding getter method will be called;  when the property is
being assigned a value, the corresponding setter method will be called. For example:

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

There are several special rules for, and limitations on, the properties defined via getters and setters:

* The names of such properties are *case-insensitive*. For example, `$object->label` and `$object->Label` are the same.
  This is because method names in PHP are case-insensitive.
* If the name of such a property is the same as a class member variable, the latter will take precedence.
  For example, if the above `Foo` class has a member variable `label`, then the assignment `$object->label = 'abc'`
  will affect the *member variable* 'label'; that line would not call the  `setLabel()` setter method.
* These properties do not support visibility. It makes no difference to the defining getter or setter method if the property is public, protected or private.
* The properties can only be defined by *non-static* getters and/or setters. Static methods will not be treated in the same manner.

Returning back to the problem described at the beginning of this guide, instead of calling `trim()` everywhere a `label` value is assigned, `trim()` now only needs to be invoked within the setter `setLabel()`. And if a new requirement makes it necessary that the label be initially capitalized, the `setLabel()` method can quickly be modified without touching any other code. The one change will universally affect every assignment to `label`.
