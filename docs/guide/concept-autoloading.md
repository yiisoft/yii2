Class Autoloading
=================

Yii relies on the [class autoloading mechanism](https://www.php.net/manual/en/language.oop5.autoload.php)
to locate and include all required class files. Starting with Yii `22.x`, autoloading is delegated entirely to
[Composer](https://getcomposer.org/), which provides a fully [PSR-4](https://www.php-fig.org/psr/psr-4/)
compliant autoloader. The framework no longer registers its own SPL autoloader and no longer exposes
`Yii::autoload()` or `Yii::$classMap`.

> Note: For simplicity of description, in this section we will only talk about autoloading of classes. However, keep in
  mind that the content described here applies to autoloading of interfaces, traits, and enums as well.

> Info: Upgrading from Yii `2.0.x`? See [`UPGRADE-22.md`](https://github.com/yiisoft/yii2/blob/master/framework/UPGRADE-22.md)
  for the migration steps and runnable examples.


How autoloading works
---------------------

When PHP encounters a class that has not been loaded yet, Composer's autoloader is invoked. It resolves the
class to a file using the rules declared under the `autoload` (and, for tests, `autoload-dev`) section of
each installed package's `composer.json`. The most common rule is PSR-4, which maps a namespace prefix to a
base directory.

For example, the framework declares:

```json
{
    "autoload": {
        "psr-4": {"yii\\": ""},
        "classmap": ["Yii.php"]
    }
}
```

This means every class in the `yii\` namespace is loaded from the framework directory according to PSR-4 
(for example, `yii\base\Component` &rarr; `base/Component.php`), and the global `Yii` helper class is loaded
from `Yii.php` through Composer's classmap.


Adding your own classes
-----------------------

The application templates ship with PSR-4 mappings already configured, so you only need to drop your classes
into the right directory and they will be picked up automatically; no `composer dump-autoload` is required during
development.

In the [Basic Project Template](start-installation.md) the `app\` namespace is mapped to the application root,
so a class named `app\components\MyClass` lives in `components/MyClass.php`.

In the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md)
each tier has its own namespace: `frontend\`, `backend\`, `common\`, `console\`. Place your class under the
matching directory and it will be autoloaded by Composer.

To add a custom namespace, declare it under `autoload.psr-4` in your application's `composer.json` and run
`composer dump-autoload`. For example, to map the `foo\` namespace to the `path/to/foo` directory:

```json
{
    "autoload": {
        "psr-4": {
            "foo\\": "path/to/foo/"
        }
    }
}
```


Overriding a framework class
----------------------------

The historical use case for `Yii::$classMap` was to replace a framework class with a custom implementation,
for example swapping `yii\web\Request`. The Composer equivalent uses `classmap` together with
`exclude-from-classmap` in your application's `composer.json`:

```json
{
    "autoload": {
        "classmap": [
            "src/overrides/Request.php"
        ],
        "exclude-from-classmap": [
            "vendor/yiisoft/yii2/web/Request.php"
        ]
    }
}
```

```php
// src/overrides/Request.php
<?php

declare(strict_types=1);

namespace yii\web;

class Request
{
    // full reimplementation of yii\web\Request, with your custom behavior
}
```

Then run `composer dump-autoload -o`. Composer will load the override from `src/overrides/Request.php` and
skip the vendor file thanks to `exclude-from-classmap`. The override survives optimized and authoritative
classmaps because it is resolved at autoload-generation time, not at runtime.

> Important: because the original `yii\web\Request` file is excluded from the classmap, the FQCN
> `yii\web\Request` is now defined exclusively by your override file. You **cannot** write
> `class Request extends \yii\web\Request` inside `namespace yii\web;` — that would be self-inheritance
> and PHP will reject it. You must reimplement the full public surface of the original class.
>
> If you only need to *extend* the framework class without replacing it, do **not** use
> `exclude-from-classmap`. Declare a subclass under a different FQCN (for example
> `app\components\Request extends \yii\web\Request`) and point the `request` application component at
> your subclass via the application configuration.


Optimizing autoloading for production
-------------------------------------

Composer can build a single optimized class map for production deployments, which is the modern equivalent of
the old `framework/classes.php` file:

```bash
composer dump-autoload -o
```

For maximum performance use the authoritative classmap, which tells Composer to never fall back to filesystem
lookups (any class not in the map is considered absent):

```bash
composer dump-autoload --classmap-authoritative
```

See the [Composer autoload documentation](https://getcomposer.org/doc/04-schema.md#autoload) for the full set
of options, including `files`, `exclude-from-classmap`, and `autoload-dev`.


Autoloading extension classes
-----------------------------

Yii 2 [extensions](structure-extensions.md) are regular Composer packages, so their classes are autoloaded the
same way as the framework: through the `autoload` section of the extension's own `composer.json`. No additional
setup is required as long as the extension declares its `psr-4` (and, when relevant, `classmap`) entries
correctly.


Installing Yii without Composer
-------------------------------

The downloadable archive published at [yiiframework.com/download/](https://www.yiiframework.com/download/) is
built with `composer install` and ships a working `vendor/autoload.php`. The standard application templates
require `vendor/autoload.php` from their entry scripts (`web/index.php`, `yii`) before any framework class is
referenced, so the archive-install path keeps working unchanged.

What is **not** supported is bootstrapping the framework by requiring `framework/Yii.php` without an active
Composer autoloader. Always require `vendor/autoload.php` first.
