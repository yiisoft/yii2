Components
==========

> Note: This chapter is under development.


Object Configuration
--------------------

The [[yii\base\Object|Object]] class introduces a uniform way of configuring objects. Any descendant class
of [[yii\base\Object|Object]] should declare its constructor (if needed) in the following way so that
it can be properly configured:

```php
class MyClass extends \yii\base\Object
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... initialization before configuration is applied

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... initialization after configuration is applied
    }
}
```

In the above example, the last parameter of the constructor must take a configuration array
which contains name-value pairs that will be used to initialize the object's properties at the end of the constructor.
You can override the `init()` method to do initialization work after the configuration is applied.

By following this convention, you will be able to create and configure new objects
using a configuration array like the following:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```
