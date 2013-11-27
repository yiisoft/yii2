Basic concepts of Yii
=====================


Component and Object
--------------------

Classes of the Yii framework usually extend from one of the two base classes [[Object]] and [[Component]].
These classes provide useful features that are added automatically to all classes extending from them.

The `Object` class provides the [configuration and property feature](../api/base/Object.md).
The `Component` class extends from `Object` and adds [event handling](events.md) and [behaviors](behaviors.md).

`Object` is usually used for classes that represent basic data structures while `Component` is used for
application components and other classes that implement higher logic.


Object Configuration
--------------------

The [[Object]] class introduces a uniform way of configuring objects. Any descendant class
of [[Object]] should declare its constructor (if needed) in the following way so that
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

In the above, the last parameter of the constructor must take a configuration array
which contains name-value pairs for initializing the properties at the end of the constructor.
You can override the `init()` method to do initialization work that should be done after
the configuration is applied.

By following this convention, you will be able to create and configure a new object
using a configuration array like the following:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], $param1, $param2);
```


Path Aliases
------------

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. An alias
must start with a `@` character so that it can be differentiated from file/directory paths and URLs.
For example, the alias `@yii` refers to the Yii installation directory while `@web` contains base URL for currently
running web application. Path aliases are supported in most places in the Yii core code. For example,
`FileCache::cachePath` can take both a path alias and a normal directory path.

Path alias is also closely related with class namespaces. It is recommended that a path
alias be defined for each root namespace so that you can use Yii the class autoloader without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded by Yii. If you use a third party library
such as Zend Framework, you may define a path alias `@Zend` which refers to its installation
directory and Yii will be able to autoload any class in this library.


Autoloading
-----------

TBD

Helper classes
--------------

TDB