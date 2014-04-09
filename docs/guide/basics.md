Basic concepts of Yii
=====================


Component and Object
--------------------

Classes of the Yii framework usually extend from one of the two base classes [[yii\base\Object]] or [[yii\base\Component]].
These classes provide useful features that are added automatically to all classes extending from them.

The [[yii\base\Object|Object]] class provides the [configuration and property feature](../api/base/Object.md).
The [[yii\base\Component|Component]] class extends from [[yii\base\Object|Object]] and adds
[event handling](events.md) and [behaviors](behaviors.md).

[[yii\base\Object|Object]] is usually used for classes that represent basic data structures while
[[yii\base\Component|Component]] is used for application components and other classes that implement higher logic.


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


Path Aliases
------------

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. An alias
must start with an `@` symbol so that it can be differentiated from file/directory paths and URLs.
For example, the alias `@yii` refers to the Yii installation directory while `@web` contains the base URL for the currently running web application. Path aliases are supported in most places in the Yii core code. For example, `FileCache::cachePath` can accept both a path alias and a normal directory path.

Path aliases are also closely related to class namespaces. It is recommended that a path
alias be defined for each root namespace so that Yii's class autoloader can be used without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded by Yii. If you use a third party library
such as Zend Framework, you may define a path alias `@Zend` which refers to its installation
directory and Yii will be able to autoload any class in this library.

The following aliases are predefined by the core framework:

- `@yii` - framework directory.
- `@app` - base path of currently running application.
- `@runtime` - runtime directory.
- `@vendor` - Composer vendor directory.
- `@webroot` - web root directory of currently running web application.
- `@web` - base URL of currently running web application.

Autoloading
-----------

All classes, interfaces and traits are loaded automatically at the moment they are used. There's no need to use `include` or `require`. It is true for Composer-loaded packages as well as Yii extensions.

Yii's autoloader works according to [PSR-4](https://github.com/php-fig/fig-standards/blob/master/proposed/psr-4-autoloader/psr-4-autoloader.md).
That means namespaces, classes, interfaces and traits must correspond to file system paths and file names accordinly, except for root namespace paths that are defined by an alias.

For example, if the standard alias `@app` refers to `/var/www/example.com/` then `\app\models\User` will be loaded from `/var/www/example.com/models/User.php`.

Custom aliases may be added using the following code:

```php
Yii::setAlias('@shared', realpath('~/src/shared'));
```

Additional autoloaders may be registered using PHP's standard `spl_autoload_register`.

Helper classes
--------------

Helper classes typically contain static methods only and are used as follows:

```php
use \yii\helpers\Html;
echo Html::encode('Test > test');
```

There are several classes provided by framework:

- ArrayHelper
- Console
- FileHelper
- Html
- HtmlPurifier
- Image
- Inflector
- Json
- Markdown
- Security
- StringHelper
- Url
- VarDumper
