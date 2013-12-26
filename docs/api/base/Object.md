Object is the base class that implements the *property* feature.

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

One can call [[hasProperty()]], [[canGetProperty()]] and/or [[canSetProperty()]] to check the existence of a property.


Besides the property feature, Object also introduces an important object initialization life cycle. In particular,
creating an new instance of Object or its derived class will involve the following life cycles sequentially:

1. the class constructor is invoked;
2. object properties are initialized according to the given configuration;
3. the `init()` method is invoked.

In the above, both Step 2 and 3 occur at the end of the class constructor. It is recommended that
you perform object initialization in the `init()` method because at that stage, the object configuration
is already applied.

In order to ensure the above life cycles, if a child class of Object needs to override the constructor,
it should be done like the following:

~~~
public function __construct($param1, $param2, ..., $config = [])
{
    ...
    parent::__construct($config);
}
~~~

That is, a `$config` parameter (defaults to `[]`) should be declared as the last parameter
of the constructor, and the parent implementation should be called at the end of the constructor.
