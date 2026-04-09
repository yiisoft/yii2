Upgrading Instructions for Yii Framework 22.x
=============================================

This file contains the upgrade notes for the Yii Framework `22.x` line. These notes highlight changes that break 
backwards compatibility from the `2.0.x` line and that require action when upgrading an application.

For the historical `2.0.x` upgrade notes see [`UPGRADE.md`](UPGRADE.md).

Upgrade from Yii 2.0.x
----------------------

* Raised minimum supported PHP version to `8.3`.
* All methods that were previously deprecated have been removed. If your application still uses any deprecated methods,
  you must update your code before upgrading.
* `yii\widgets\ActiveField::label()` method now supports a `tag` option to control the wrapper element. This provides
  flexibility for custom label rendering while maintaining full backward compatibility:

  - `tag => 'label'` default generates standard `<label>` element with `for` attribute.
  - `tag => false`  renders label content without any wrapper tag.
  - `tag => 'span'/'div'/etc` uses specified HTML element as wrapper.

  Example usage:

  ```php
  $field->label('My Label', ['tag' => 'span', 'class' => 'custom-label']);
  ```
* jQuery is now optional in the framework. A new `useJquery` property has been added to `yii\console\Application` and
  `yii\web\Application` to control whether jQuery-based client scripts are used. The default value is `true`,  
  maintaining full backward compatibility with existing applications. When the value is `false`, jQuery is not used and
  framework uses plain JavaScript instead.
  
  To disable jQuery globally, configure your application:
  
  ```php
  return [
      'useJquery' => false,
      // ... other config
  ];
  ```
  
  Or dynamically at runtime:
  
  ```php
  Yii::$app->useJquery = false;
  ```

* Two new interfaces have been introduced to support alternative client-side script implementations:
  
  - `yii\web\client\ClientScriptInterface` - for widgets and components (ActiveForm, GridView, etc.)
  - `yii\validators\client\ClientValidatorScriptInterface` - for validators
  
  These interfaces allow you to provide custom client-side validation and behavior without jQuery dependency.

* Widgets and validators now support a `clientScript` configuration option to specify custom client script handlers:
  
  ```php
  // Using default jQuery behavior (no changes needed)
  $form = ActiveForm::begin([]);
  
  // Using custom client script implementation without jQuery
  $form = ActiveForm::begin(['clientScript' => MyCustomClientScript::class]);
  ```
  
  The following components support custom client scripts via the new interfaces:

  - `yii\widgets\ActiveForm` and `yii\widgets\ActiveField`
  - `yii\grid\GridView` and `yii\grid\CheckboxColumn`
  - All built-in validators (RequiredValidator, EmailValidator, StringValidator, etc.)

* If you are extending any of the following classes and overriding client-side script generation methods, you should 
  review your code to ensure compatibility:
  
  - `yii\widgets\ActiveForm::getClientOptions()`
  - `yii\widgets\ActiveField::getClientOptions()`
  - `yii\validators\Validator::clientValidateAttribute()`
  
  The default implementations now delegate to the configured `clientScript` handler when jQuery is enabled.

* Example of implementing a custom client script handler:
  
  ```php
  use yii\base\BaseObject;
  use yii\web\client\ClientScriptInterface;
  use yii\web\View;
  use yii\widgets\ActiveField;
  use yii\widgets\ActiveForm;  
  
  /**
   * @template T of ActiveForm|ActiveField
   * @implements ClientScriptInterface<T>
   */
  class MyCustomClientScript implements ClientScriptInterface
  {
      public function getClientOptions(BaseObject $object, array $options = []): array
      {
          // Return client-side options for your custom implementation
          return [
              'myOption' => 'value',
          ];
      }
      
      public function register(BaseObject $object, View $view, array $options = []): void
      {
          // Register your custom JavaScript/CSS assets
          MyCustomAsset::register($view);
          
          // Register initialization script
          $view->registerJs("MyCustomLib.init('#" . $object->options['id'] . "');");
      }
  }
  ```

* If your application doesn't use jQuery and you want to completely remove the dependency, after setting `useJquery` to
  `false`, you'll need to provide your own client script implementations for any widgets or validators that require 
  client-side functionality. Alternatively, you can disable client-side validation entirely:
  
  ```php
  $form = ActiveForm::begin(
      [
          'enableClientValidation' => false,
          'enableAjaxValidation' => false,
      ],
  );
  ```

* Note: Setting `useJquery` to `false` only prevents the framework from registering jQuery-based scripts. 
  It does not remove jQuery from your application if you've included it manually or through other extensions. 
  You are responsible for ensuring your application works correctly without jQuery when this option is disabled.

### CUBRID database support removed

Yii 22.x no longer includes support for the CUBRID database driver. Applications that still depend on CUBRID must
migrate to a supported database engine before upgrading to Yii 22.x.

There is no compatibility layer for CUBRID in this release. The framework no longer ships CUBRID-specific database
classes, configuration entries, fixtures, or test coverage.

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

   ```bash
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

Then run `composer dump-autoload -o`. Composer loads the override from `src/overrides/Request.php` and
skips the vendor file thanks to `exclude-from-classmap`. The override survives optimized and
authoritative classmaps because it is resolved at autoload-generation time, not at runtime.

> Important: because the original `yii\web\Request` file is excluded from the classmap, the FQCN
> `yii\web\Request` is now defined exclusively by your override file. You **cannot** write
> `class Request extends \yii\web\Request` inside `namespace yii\web;` — that would be self-inheritance
> and PHP will reject it. You must reimplement the full public surface of the original class.
>
> If you only need to *extend* the framework class, do **not** use `exclude-from-classmap`. Instead,
> declare a subclass under a different FQCN (for example `app\components\Request extends \yii\web\Request`)
> and point the `request` application component at the new class via the application configuration:
>
> ```php
> 'components' => [
>     'request' => ['class' => \app\components\Request::class],
> ],
> ```

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
