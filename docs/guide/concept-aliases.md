Aliases
=======

Aliases are used to represent file paths or URLs so that you don't have to hard-code absolute paths or URLs in your project. An alias must start with the `@` character to be differentiated from normal file paths and URLs. Yii has many pre-defined aliases already available. 
For example, the alias `@yii` represents the installation path of the Yii framework; `@web` represents
the base URL for the currently running Web application.


Defining Aliases <span id="defining-aliases"></span>
----------------

You can define an alias for a file path or URL by calling [[Yii::setAlias()]]:

```php
// an alias of a file path
Yii::setAlias('@foo', '/path/to/foo');

// an alias of a URL
Yii::setAlias('@bar', 'http://www.example.com');
```

> Note: The file path or URL being aliased may *not* necessarily refer to an existing file or resource.

Given a defined alias, you may derive a new alias (without the need of calling [[Yii::setAlias()]]) by appending
a slash `/` followed with one or more path segments. The aliases defined via [[Yii::setAlias()]] becomes the 
*root alias*, while aliases derived from it are *derived aliases*. For example, `@foo` is a root alias,
while `@foo/bar/file.php` is a derived alias.

You can define an alias using another alias (either root or derived):

```php
Yii::setAlias('@foobar', '@foo/bar');
```

Root aliases are usually defined during the [bootstrapping](runtime-bootstrapping.md) stage.
For example, you may call [[Yii::setAlias()]] in the [entry script](structure-entry-scripts.md).
For convenience, [Application](structure-applications.md) provides a writable property named `aliases`
that you can configure in the application [configuration](concept-configurations.md):

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'http://www.example.com',
    ],
];
```


Resolving Aliases <span id="resolving-aliases"></span>
-----------------

You can call [[Yii::getAlias()]] to resolve a root alias into the file path or URL it represents.
The same method can also resolve a derived alias into the corresponding file path or URL:

```php
echo Yii::getAlias('@foo');               // displays: /path/to/foo
echo Yii::getAlias('@bar');               // displays: http://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // displays: /path/to/foo/bar/file.php
```

The path/URL represented by a derived alias is determined by replacing the root alias part with its corresponding
path/URL in the derived alias.

> Note: The [[Yii::getAlias()]] method does not check whether the resulting path/URL refers to an existing file or resource.


A root alias may also contain slash `/` characters. The [[Yii::getAlias()]] method
is intelligent enough to tell which part of an alias is a root alias and thus correctly determines
the corresponding file path or URL:

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // displays: /path/to/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // displays: /path2/bar/file.php
```

If `@foo/bar` is not defined as a root alias, the last statement would display `/path/to/foo/bar/file.php`.


Using Aliases <span id="using-aliases"></span>
-------------

Aliases are recognized in many places in Yii without needing to call [[Yii::getAlias()]] to convert
them into paths or URLs. For example, [[yii\caching\FileCache::cachePath]] can accept both a file path
and an alias representing a file path, thanks to the `@` prefix which allows it to differentiate a file path
from an alias.

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

Please pay attention to the API documentation to see if a property or method parameter supports aliases.


Predefined Aliases <span id="predefined-aliases"></span>
------------------

Yii predefines a set of aliases to easily reference commonly used file paths and URLs:

- `@yii`, the directory where the `BaseYii.php` file is located (also called the framework directory).
- `@app`, the [[yii\base\Application::basePath|base path]] of the currently running application.
- `@runtime`, the [[yii\base\Application::runtimePath|runtime path]] of the currently running application. Defaults to `@app/runtime`.
- `@webroot`, the Web root directory of the currently running Web application. It is determined based on the directory
  containing the [entry script](structure-entry-scripts.md).
- `@web`, the base URL of the currently running Web application. It has the same value as [[yii\web\Request::baseUrl]].
- `@vendor`, the [[yii\base\Application::vendorPath|Composer vendor directory]]. Defaults to `@app/vendor`.
- `@bower`, the root directory that contains [bower packages](http://bower.io/). Defaults to `@vendor/bower`.
- `@npm`, the root directory that contains [npm packages](https://www.npmjs.org/). Defaults to `@vendor/npm`.

The `@yii` alias is defined when you include the `Yii.php` file in your [entry script](structure-entry-scripts.md).
The rest of the aliases are defined in the application constructor when applying the application
[configuration](concept-configurations.md).


Extension Aliases <span id="extension-aliases"></span>
-----------------

An alias is automatically defined for each [extension](structure-extensions.md) that is installed via Composer.
Each alias is named after the root namespace of the extension as declared in its `composer.json` file, and each alias
represents the root directory of the package. For example, if you install the `yiisoft/yii2-jui` extension,
you will automatically have the alias `@yii/jui` defined during the [bootstrapping](runtime-bootstrapping.md) stage, equivalent to:

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
