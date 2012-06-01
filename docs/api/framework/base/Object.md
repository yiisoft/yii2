A property is defined by a getter method (e.g. `getLabel`), and/or a setter method (e.g. `setLabel`). For example,
the following getter and setter methods define a property named `label`:

~~~
private $_label;

public function getLabel()
{
    return $this->_label;
}

public function setLabel($value)
{
    $this->_label = $value;
}
~~~

Property names are *case-insensitive*.

A property can be accessed like a member variable of an object. Reading or writing a property will cause the invocation
of the corresponding getter or setter method. For example,

~~~
// equivalent to $label = $object->getLabel();
$label = $object->label;
// equivalent to $object->setLabel('abc');
$object->label = 'abc';
~~~

If a property has only a getter method and has no setter method, it is considered as *read-only*. In this case, trying
to modify the property value will cause an exception.

One can call [[hasProperty]], [[canGetProperty]] and/or [[canSetProperty]] to check the existence of a property.

Besides the property feature, the Object class defines a static method [[create]] which provides a convenient
alternative way of creating a new object instance.
