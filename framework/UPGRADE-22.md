Upgrading Instructions for Yii Framework 22.x
=============================================

This file contains the upgrade notes for the Yii Framework `22.x` line. These notes highlight changes that
break backwards compatibility from the `2.0.x` line and that require action when upgrading an application.

For the historical `2.0.x` upgrade notes see [`UPGRADE.md`](UPGRADE.md).

Upgrade from Yii 2.0.x
----------------------

### Yii runtime autoloader removed

The framework no longer registers its own SPL autoloader. The following public API has been removed:

- `Yii::autoload()` (and `yii\BaseYii::autoload()`).
- `Yii::$classMap` (and `yii\BaseYii::$classMap`).
- `framework/classes.php` (the prebuilt framework class map).
- The `build classmap` console command (`build/controllers/ClassmapController.php`).

All framework classes are now loaded exclusively by Composer:

- `autoload.psr-4` maps the `yii\` namespace.
- `autoload.classmap` covers the global `Yii` class (which lives outside any namespace).

#### What you must do

1. Remove any code that writes to `Yii::$classMap` or calls `Yii::autoload()`. Both no longer exist and
   will produce a fatal error.
2. Make sure every class your application uses is reachable through Composer autoload. Declare it under
   one of:
    - `autoload.psr-4` — namespace mapping (preferred for application code).
    - `autoload.classmap` — explicit class-to-file mapping (use for non-PSR-4 files or vendor overrides).
    - `autoload.exclude-from-classmap` — paired with `classmap` when overriding a vendor class.
    - `autoload-dev` — development and test-only classes.
3. Regenerate the autoload files after editing `composer.json`:

   ```
   composer dump-autoload -o
   ```

   Use `-o` (or `--classmap-authoritative`) in production for the same performance benefit that
   `framework/classes.php` used to provide.

#### Migration example: registering a new class

Before (runtime mapping in the entry script):

```php
Yii::$classMap['app\\helpers\\MyHelper'] = '@app/helpers/MyHelper.php';
```

After (in the application's `composer.json`):

```json
{
    "autoload": {
        "psr-4": {
            "app\\": "src/"
        }
    }
}
```

Place the class at `src/helpers/MyHelper.php` with `namespace app\helpers;` and run
`composer dump-autoload`. No runtime registration is required.

#### Migration example: overriding a framework class

The most common reason for writing to `Yii::$classMap` was to *replace* a framework class with a custom
implementation, for example swapping `yii\web\Request`. The Composer equivalent uses `classmap` together
with `exclude-from-classmap`.

Before (runtime override via `$classMap`, no longer supported):

```php
// entry script
Yii::$classMap['yii\\web\\Request'] = '@app/components/Request.php';
```

After (in the application's `composer.json`):

```json
{
    "autoload": {
        "psr-4": {
            "app\\": "src/"
        },
        "classmap": [
            "src/overrides/Request.php"
        ],
        "exclude-from-classmap": [
            "vendor/yiisoft/yii2/framework/web/Request.php"
        ]
    }
}
```

```php
// src/overrides/Request.php
<?php

declare(strict_types=1);

namespace yii\web;

class Request extends \yii\web\Request
{
    // custom behavior
}
```

Then run `composer dump-autoload -o`. Composer loads the override from `src/overrides/Request.php` and
skips the vendor file thanks to `exclude-from-classmap`. The override survives optimized and
authoritative classmaps because it is resolved at autoload-generation time, not at runtime.

> Note: if you redeclare the class from scratch (instead of extending), drop the `extends` clause and
> reimplement the full public surface. Either approach works; the choice depends on how much of the
> original behavior you want to keep.

#### Installing Yii from an archive file (non-Composer install)

Yii 2 can still be installed from a downloadable archive file as documented in the
[installation guide](../docs/guide/start-installation.md). The archive published at
`yiiframework.com/download/` ships a prebuilt `vendor/` directory generated with `composer install`,
so it already contains `vendor/autoload.php`. The standard application templates (`basic`, `advanced`)
require `vendor/autoload.php` from their entry scripts (`web/index.php`, `yii`) before any framework
class is referenced, so the archive-install path keeps working unchanged with this release.

What is **no longer supported** is bootstrapping the framework by requiring `framework/Yii.php` (or
the legacy `framework/classes.php`) *without* having Composer's autoloader active. There is no
runtime fallback anymore: `vendor/autoload.php` MUST be loaded first. If you maintain a custom entry
script that historically skipped Composer's autoloader, add `require __DIR__ . '/vendor/autoload.php';`
before any reference to `Yii` or `yii\…` classes.

#### Adding test-only classes

For classes used only by tests, declare them under `autoload-dev` instead of `autoload`:

```json
{
    "autoload-dev": {
        "psr-4": {
            "tests\\": "tests/"
        }
    }
}
```

This keeps test code out of production autoload maps.
