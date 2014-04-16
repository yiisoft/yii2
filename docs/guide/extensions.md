Extending Yii
=============
The Yii framework was designed to be easily extendable. Additional features can be added to your project and then reused, either by yourself on other projects or by sharing your work as a formal Yii extension.

Code style
----------

To be consistent with core Yii conventions, your extensions ought to adhere to certain coding styles:

- Use the [core framework code style](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).
- Document classes, methods and properties using [phpdoc](http://www.phpdoc.org/). - Extension classes should *not* be prefixed. Do not use the format `TbNavBar`, `EMyWidget`, etc.

> Note that you can use Markdown within your code for documentation purposes. With Markdown, you can link to properties and methods using the following syntax: `[[name()]]`, `[[namespace\MyClass::name()]]`.

### Namespace

Yii 2 relies upon namespaces to organize code. (Namespace support was added to PHP in version 5.3.) If you want to use namespaces within your extension,

- Do not use `yiisoft` anywhere in your namespaces.
- Do not use `\yii`, `\yii2` or `\yiisoft` as root namespaces.
- Namespaces should use the syntax `vendorName\uniqueName`.

Choosing a unique namespace is important to prevent name collisions, and also results in faster autoloading of classes. Examples of unique, consistent namepacing are:

- `samdark\wiki`
- `samdark\debugger`
- `samdark\googlemap`

Distribution
------------

Beyond the code itself, the entire extension distribution ought to have certain things.

There should be a `readme.md` file, written in English. This file should clearly describe what the extension does, its requirements, how to install it, 
  and to use it. The README should be written using Markdown. If you want to provide translated README files, name them as `readme_ru.md`
  where `ru` is your language code (in this case, Russian). 
  
  It is a good idea to include some screenshots as part of the documentation, especially if your extension provides a widget. 
  
It is recommended to host your extensions at [Github](https://github.com).

Extensions should also be registered at [Packagist](https://packagist.org) in order to be installable via Composer. 

### Composer package name

Choose your extension's package name wisely, as you shouldn't change the package name later on. (Changing the name leads to losing the Composer stats, and makes it impossible for people  to install the package by the old name.) 

If your extension was made specifically for Yii2 (i.e. cannot be used as a standalone PHP library) it is recommended to
name it like the following:

```
yii2-my-extension-name-type
```

Where: 

- `yii2-` is a prefix.
- The extension name is in all lowercase letters, with words separated by `-`.
- The `-type` postfix may be `widget`, `behavior`, `module` etc.

### Dependencies

Some extensions you develop may have their own dependencies, such as relying upon other extensions or third-party libraries. When dependencies exist, you should require them in your extension's `composer.json` file. Be certain to also use appropriate version constraints, eg. `1.*`, `@stable` for requirements.

Finally, when your extension is released in a stable version, double-check that its requirements do not include `dev` packages that do not have a `stable` release. In other words, the stable release of your extension should only rely upon stable dependencies.

### Versioning

As you maintain and upgrading your extension, 

- Use the rules of [semantic versioning](http://semver.org).
- Use a consistent format for your repository tags, as they are treated as version strings by composer, eg. `0.2.4`,
  `0.2.5`,`0.3.0`,`1.0.0`.

### composer.json

Yii2 uses Composer for installation, and extensions for Yii2 should as well. Towards that end, 

- Use the type `yii2-extension` in `composer.json` file if your extension is Yii-specific.
- Do not use `yii` or `yii2` as the Composer vendor name.
- Do not use `yiisoft` in the Composer package name or the Composer vendor name.

If your extension classes reside directly in the repository root directory, you can use the PSR-4 autoloader in the following way in your `composer.json` file:

```json
{
    "name": "myname/mywidget",
    "description": "My widget is a cool widget that does everything",
    "keywords": ["yii", "extension", "widget", "cool"],
    "homepage": "https://github.com/myname/yii2-mywidget-widget",
    "type": "yii2-extension",
    "license": "BSD-3-Clause",
    "authors": [
        {
            "name": "John Doe",
            "email": "doe@example.com"
        }
    ],
    "require": {
        "yiisoft/yii2": "*"
    },
    "autoload": {
        "psr-4": {
            "myname\\mywidget\\": ""
        }
    }
}
```

In the above, `myname/mywidget` is the package name that will be registered
at [Packagist](https://packagist.org). It is common for the package name to match your Github repository name.
Also, the `psr-4` autoloader is specified in the above, which maps the `myname\mywidget` namespace to the root directory where the classes reside.

More details on this syntax can be found in the [Composer documentation](http://getcomposer.org/doc/04-schema.md#autoload).


### Bootstrap with extension

Sometimes, you may want your extension to execute some code during the bootstrap stage of an application.
For example, your extension may want to respond to the application's `beginRequest` event. You can ask the extension user
to explicitly attach your event handler in the extension to the application's event. A better way, however, is to
do all these automatically.

To achieve this goal, you can create a bootstrap class by implementing [[yii\base\BootstrapInterface]].

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // do something here
        });
    }
}
```

You then list this bootstrap class in `composer.json` as follows,

```json
{
    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

When the extension is installed in an application, Yii will automatically hook up the bootstrap class
and call its `bootstrap()` while initializing the application for every request.


Working with database
---------------------

Extensions sometimes have to use their own database tables. In such a situation,

- If the extension creates or modifies the database schema, always use Yii migrations instead of SQL files or custom scripts.
- Migrations should be applicable to different database systems.
- Do not use Active Record models in your migrations.

Assets
------

- Register assets [through bundles](assets.md).

Events
------

TBD

i18n
----

- If extension outputs messages intended for end user these should be wrapped into `Yii::t()` in order to be translatable.
- Exceptions and other developer-oriented message should not be translated.
- Consider proving `config.php` for `yii message` command to simplify translation.

Testing your extension
----------------------

- Consider adding unit tests for PHPUnit.
