# Upgrading Instructions for Yii Framework 22.x

This file contains the upgrade notes for the Yii Framework `22.x` line. These notes highlight changes that break 
backwards compatibility from the `2.0.x` line and that require action when upgrading an application.

For the historical `2.0.x` upgrade notes see [`UPGRADE.md`](UPGRADE.md).

## Upgrade from Yii 2.0.x

### General upgrade notes

* Raised minimum supported PHP version to `8.3`.
* All methods that were previously deprecated have been removed. If your application still uses any deprecated methods,
  you must update your code before upgrading.

### ActiveField label, radio, and checkbox enhancements

`yii\widgets\ActiveField::label()` now supports a `tag` option in `$options` to control the wrapper element:

- `tag => 'label'` (default) generates a standard `<label>` element with `for` attribute.
- `tag => false` renders label content without any wrapping tag.
- `tag => 'span'`/`'div'`/etc. uses the specified HTML element as wrapper.

Example usage:

```php
$field->label('My Label', ['tag' => 'span', 'class' => 'custom-label']);
```

Additionally, `radio()` and `checkbox()` now support a `tag` sub-option inside `labelOptions` when
`enclosedByLabel` is `false`. This provides flexible label rendering for radio buttons and checkboxes:

```php
// Render radio label as a <span> instead of <label>
$field->radio(
    [
        'label' => 'Option A',
        'labelOptions' => ['tag' => 'span', 'class' => 'custom-label'],
    ],
    false,
);

// Render checkbox label without any wrapping tag
$field->checkbox(
    [
        'label' => '<strong>Accept Terms</strong>',
        'labelOptions' => ['tag' => false],
    ],
    false,
);
```

These changes are fully backward compatible. Existing code continues to work without modification.

### ApcCache renamed to ApcuCache

The `yii\caching\ApcCache` class has been renamed to `yii\caching\ApcuCache`. The legacy APC extension (`ext-apc`) is
not available in PHP >= `8.0`, so the dual-mode `$useApcu` property has been removed.

- Replace all references to `yii\caching\ApcCache` with `yii\caching\ApcuCache`.
- Remove any `'useApcu' => true` configuration, as it is no longer needed.
- Migration example:

```php
// before
'cache' => [
    'class' => 'yii\caching\ApcCache',
    'useApcu' => true,
],

// after
'cache' => [
    'class' => 'yii\caching\ApcuCache',
],
```  

### CUBRID database support removed

Yii `22.x` no longer includes support for the CUBRID database driver. Applications that still depend on CUBRID must
migrate to a supported database engine before upgrading to Yii `22.x`.

There is no compatibility layer for CUBRID in this release. The framework no longer ships CUBRID-specific database
classes, configuration entries, fixtures, or test coverage.

### HHVM support removed

All HHVM-specific code has been removed from the framework. Yii `22.x` targets PHP `8.3+` on the Zend engine only.

- `yii\base\ErrorException::E_HHVM_FATAL_ERROR` constant has been removed.
- `yii\base\ErrorHandler::handleHhvmError()` method has been removed.
- `yii\base\ErrorHandler` no longer registers a HHVM-specific error handler when `HHVM_VERSION` is defined.
- HHVM-specific test skips and workarounds have been removed.

If your application references `ErrorException::E_HHVM_FATAL_ERROR` or `ErrorHandler::handleHhvmError()`, remove those 
references when upgrading.

### jQuery is now optional (strategy pattern)

jQuery is no longer hardcoded in validators and widgets. A new `useJquery` property controls whether jQuery-based client 
scripts are registered. It defaults to `true` on `yii\web\Application` (full backward compatibility) and to `false` on 
`yii\console\Application` (no client scripts in console context).

**No action required** for existing web applications; the default behavior is fully backward compatible.

#### New interfaces

Two interfaces allow replacing the jQuery implementation with any alternative:

- `yii\web\client\ClientScriptInterface` strategy for widgets and components (`ActiveForm`, `GridView`,
  `CheckboxColumn`).
- `yii\validators\client\ClientValidatorScriptInterface` strategy for validators.

#### New `clientScript` property

All 13 built-in validators that support client-side validation and the widgets listed below now expose a `clientScript`
property that accepts an array config or an object implementing the matching interface:

- `yii\widgets\ActiveForm` and `yii\widgets\ActiveField`
- `yii\grid\GridView` and `yii\grid\CheckboxColumn`
- `BooleanValidator`, `CompareValidator`, `EmailValidator`, `FileValidator`, `ImageValidator`, `IpValidator`,
  `NumberValidator`, `RangeValidator`, `RegularExpressionValidator`, `RequiredValidator`, `StringValidator`,
  `TrimValidator`, `UrlValidator`

The custom strategy is **always instantiated** regardless of `useJquery`, enabling framework-agnostic client scripts.

#### New method

`Validator::getFormattedClientMessage(string $message, array $params): string` public wrapper around the protected
`formatMessage()`, used by extracted jQuery client script classes when composing error messages.

#### Opting out of jQuery

```php
// In application configuration
return [
    'useJquery' => false,
    // ... other config
];
```

When `useJquery` is `false` and no custom `clientScript` strategy is configured:

- `clientValidateAttribute()` returns `null` on all built-in validators.
- `getClientOptions()` returns `[]` on all built-in validators.
- `ActiveForm`, `GridView`, and `CheckboxColumn` do not register their built-in jQuery plugins.
- No built-in `JqueryAsset`, `ValidationAsset`, `ActiveFormAsset`, or `GridViewAsset` bundles are registered.

#### Custom client script strategy

```php
// Custom validator client script works even when useJquery is false
public function rules(): array
{
    return [
        ['username', 'required', 'clientScript' => ['class' => MyRequiredClientScript::class]],
    ];
}

// Custom form client script
ActiveForm::begin(
    [
        'clientScript' => ['class' => MyFormClientScript::class],
    ],
);
```

#### BC impact on subclasses

If you extend any of the following classes and override client-side script generation methods, review your code for
compatibility:

- `yii\widgets\ActiveForm::getClientOptions()`
- `yii\widgets\ActiveField::getClientOptions()`
- `yii\validators\Validator::clientValidateAttribute()`

The default implementations now delegate to the configured `$clientScript` handler when jQuery is enabled. Subclasses
that call `parent::clientValidateAttribute()` or `parent::getClientOptions()` are not affected.

> **Note:** Setting `useJquery` to `false` only prevents the framework from registering jQuery-based scripts. It does
> not remove jQuery from your application if you have included it manually or through other extensions.

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

1. Remove any code that writes to `Yii::$classMap` or calls `Yii::autoload()`. Both no longer exist and will produce a 
   fatal error.
2. Make sure every class your application uses is reachable through Composer autoload. Declare it under one of:
    - `autoload.psr-4` namespace mapping (preferred for application code).
    - `autoload.classmap` explicit class-to-file mapping (use for non-PSR-4 files or vendor overrides).
    - `autoload.exclude-from-classmap` paired with `classmap` when overriding a vendor class.
    - `autoload-dev` development and test-only classes.
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

Place the class at `src/helpers/MyHelper.php` with `namespace app\helpers;` and run `composer dump-autoload`. No runtime
registration is required.

#### Migration example: overriding a framework class

The most common reason for writing to `Yii::$classMap` was to *replace* a framework class with a custom implementation, 
for example swapping `yii\web\Request`. The Composer equivalent uses `classmap` together with `exclude-from-classmap`.

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

> Important: because the original `yii\web\Request` file is excluded from the classmap, the FQCN `yii\web\Request` is 
> now defined exclusively by your override file. You **cannot** write `class Request extends \yii\web\Request` inside 
> `namespace yii\web;` that would be self-inheritance and PHP will reject it. You must reimplement the full public 
> surface of the original class.
>
> If you only need to *extend* the framework class, do **not** use `exclude-from-classmap`. Instead, declare a subclass 
> under a different FQCN (for example `app\components\Request extends \yii\web\Request`) and point the `request` 
> application component at the new class via the application configuration:
>
> ```php
> 'components' => [
>     'request' => ['class' => \app\components\Request::class],
> ],
> ```

#### Installing Yii from an archive file (non-Composer install)

Yii 2 can still be installed from a downloadable archive file as documented in the [installation guide](../docs/guide/start-installation.md). 
The archive published at `yiiframework.com/download/` ships a prebuilt `vendor/` directory generated with 
`composer install`, so it already contains `vendor/autoload.php`. The standard application templates (`basic`, `advanced`)
require `vendor/autoload.php` from their entry scripts (`web/index.php`, `yii`) before any framework class is referenced, 
so the archive-install path keeps working unchanged with this release.

What is **no longer supported** is bootstrapping the framework by requiring `framework/Yii.php` (or the legacy 
`framework/classes.php`) *without* having Composer's autoloader active. There is no runtime fallback anymore: 
`vendor/autoload.php` MUST be loaded first. If you maintain a custom entry script that historically skipped Composer's 
autoloader, add `require __DIR__ . '/vendor/autoload.php';` before any reference to `Yii` or `yii\…` classes.

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
