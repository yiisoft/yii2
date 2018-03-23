Class Autoloading
=================

Yii relies on the [class autoloading mechanism](http://www.php.net/manual/en/language.oop5.autoload.php)
to locate and include all required class files. It provides a high-performance class autoloader that is compliant with the
[PSR-4 standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md).
The autoloader is installed when you include the `Yii.php` file.

> Note: For simplicity of description, in this section we will only talk about autoloading of classes. However, keep in
  mind that the content we are describing here applies to autoloading of interfaces and traits as well.


Using the Yii Autoloader <span id="using-yii-autoloader"></span>
------------------------

To make use of the Yii class autoloader, you should follow two simple rules when creating and naming your classes:

* Each class must be under a [namespace](http://php.net/manual/en/language.namespaces.php) (e.g. `foo\bar\MyClass`)
* Each class must be saved in an individual file whose path is determined by the following algorithm:

```php
// $className is a fully qualified class name without the leading backslash
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```

For example, if a class name and namespace is `foo\bar\MyClass`, the [alias](concept-aliases.md) for the corresponding class file path
would be `@foo/bar/MyClass.php`. In order for this alias to be resolvable into a file path,
either `@foo` or `@foo/bar` must be a [root alias](concept-aliases.md#defining-aliases).

When using the [Basic Project Template](start-installation.md), you may put your classes under the top-level
namespace `app` so that they can be autoloaded by Yii without the need of defining a new alias. This is because
`@app` is a [predefined alias](concept-aliases.md#predefined-aliases), and a class name like `app\components\MyClass`
can be resolved into the class file `AppBasePath/components/MyClass.php`, according to the algorithm just described.

In the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), each tier has its own root alias. For example,
the front-end tier has a root alias `@frontend`, while the back-end tier root alias is `@backend`. As a result,
you may put the front-end classes under the namespace `frontend` while the back-end classes are under `backend`. This will
allow these classes to be autoloaded by the Yii autoloader.

To add a custom namespace to the autoloader you need to define an alias for the base directory of the namespace using [[Yii::setAlias()]].
For example to load classes in the `foo` namespace that are located in the `path/to/foo` directory you will call `Yii::setAlias('@foo', 'path/to/foo')`.

Class Map <span id="class-map"></span>
---------

The Yii class autoloader supports the *class map* feature, which maps class names to the corresponding class file paths.
When the autoloader is loading a class, it will first check if the class is found in the map. If so, the corresponding
file path will be included directly without further checks. This makes class autoloading super fast. In fact,
all core Yii classes are autoloaded this way.

You may add a class to the class map, stored in `Yii::$classMap`, using:

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

[Aliases](concept-aliases.md) can be used to specify class file paths. You should set the class map in the
[bootstrapping](runtime-bootstrapping.md) process so that the map is ready before your classes are used.


Using Other Autoloaders <span id="using-other-autoloaders"></span>
-----------------------

Because Yii embraces Composer as a package dependency manager, it is recommended that you also install
the Composer autoloader. If you are using 3rd-party libraries that have their own autoloaders, you should
also install those.

When using the Yii autoloader together with other autoloaders, you should include the `Yii.php` file
*after* all other autoloaders are installed. This will make the Yii autoloader the first one responding to
any class autoloading request. For example, the following code is extracted from
the [entry script](structure-entry-scripts.md) of the [Basic Project Template](start-installation.md). The first
line installs the Composer autoloader, while the second line installs the Yii autoloader:

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

You may use the Composer autoloader alone without the Yii autoloader. However, by doing so, the performance
of your class autoloading may be degraded, and you must follow the rules set by Composer in order for your classes
to be autoloadable.

> Info: If you do not want to use the Yii autoloader, you must create your own version of the `Yii.php` file
  and include it in your [entry script](structure-entry-scripts.md).


Autoloading Extension Classes <span id="autoloading-extension-classes"></span>
-----------------------------

The Yii autoloader is capable of autoloading [extension](structure-extensions.md) classes. The sole requirement
is that an extension specifies the `autoload` section correctly in its `composer.json` file. Please refer to the
[Composer documentation](https://getcomposer.org/doc/04-schema.md#autoload) for more details about specifying `autoload`.

In case you do not use the Yii autoloader, the Composer autoloader can still autoload extension classes for you.
