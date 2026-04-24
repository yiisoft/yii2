# Upgrading Instructions for Yii Framework 22.x

This file contains the upgrade notes for the Yii Framework `22.x` line. These notes highlight changes that break
backwards compatibility from the `2.0.x` line and that require action when upgrading an application.

For the historical `2.0.x` upgrade notes see [`UPGRADE.md`](UPGRADE.md).

## Upgrade from Yii 2.0.x

### General upgrade notes

- Raised minimum supported PHP version to `8.3`.
- All methods that were previously deprecated have been removed. If your application still uses any deprecated methods,
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

### Bootstrap CSS class defaults removed

Bootstrap-specific CSS class defaults have been removed from widget properties. The framework is now CSS-agnostic; no
CSS framework is required or assumed. The following property defaults have changed:

| Class                     | Property              | Old default                                                     | New default                  |
| ------------------------- | --------------------- | --------------------------------------------------------------- | ---------------------------- |
| `yii\widgets\ActiveField` | `$options`            | `['class' => 'form-group']`                                     | `[]`                         |
| `yii\widgets\ActiveField` | `$inputOptions`       | `['class' => 'form-control']`                                   | `[]`                         |
| `yii\widgets\ActiveField` | `$errorOptions`       | `['class' => 'help-block']`                                     | `['class' => 'field-error']` |
| `yii\widgets\ActiveField` | `$labelOptions`       | `['class' => 'control-label']`                                  | `[]`                         |
| `yii\widgets\ActiveForm`  | `$errorCssClass`      | `'has-error'`                                                   | `''`                         |
| `yii\widgets\ActiveForm`  | `$successCssClass`    | `'has-success'`                                                 | `''`                         |
| `yii\grid\GridView`       | `$tableOptions`       | `['class' => 'table table-striped table-bordered']`             | `[]`                         |
| `yii\grid\GridView`       | `$filterErrorOptions` | `['class' => 'help-block']`                                     | `['class' => 'field-error']` |
| `yii\grid\DataColumn`     | `$filterInputOptions` | `['class' => 'form-control', 'id' => null]`                     | `['id' => null]`             |
| `yii\widgets\DetailView`  | `$options`            | `['class' => 'table table-striped table-bordered detail-view']` | `['class' => 'detail-view']` |
| `yii\widgets\Breadcrumbs` | `$options`            | `['class' => 'breadcrumb']`                                     | `[]`                         |
| `yii\widgets\LinkPager`   | `$options`            | `['class' => 'pagination']`                                     | `[]`                         |
| `yii\captcha\Captcha`     | `$options`            | `['class' => 'form-control']`                                   | `[]`                         |

`yii\grid\ActionColumn::initDefaultButton()` no longer falls back to `glyphicon glyphicon-$iconName` markup when an
icon name is not present in `$icons`. It now renders the icon name as plain text.

**Migration.** If your application depends on Bootstrap CSS for these widgets, configure the old values explicitly.
For `ActiveField`:

```php
$form->field(
    $model,
    'username',
    [
        'options'      => ['class' => 'form-group'],
        'inputOptions' => ['class' => 'form-control'],
        'errorOptions' => ['class' => 'help-block'],
        'labelOptions' => ['class' => 'control-label'],
    ],
);
```

Or subclass `ActiveField` and override the properties once. For `ActiveForm`:

```php
$form = ActiveForm::begin(
    [
        'errorCssClass'   => 'has-error',
        'successCssClass' => 'has-success',
    ],
);
```

**Note on `yii.activeForm.js` compatibility.** `ActiveField::$errorOptions` now defaults to `['class' => 'field-error']`
instead of `['class' => 'help-block']`. The client-side validation JavaScript in `yiisoft/yii2-jquery` still defaults
its error selector to `.help-block`, but `ActiveFormJqueryClientScript::getClientOptions()` passes the per-field `error`
selector in the payload that overrides the JS default at runtime, so client-side validation keeps working against
`.field-error` with no manual configuration.

### Database

#### CUBRID database support removed

Yii `22.x` no longer includes support for the CUBRID database driver. Applications that still depend on CUBRID must
migrate to a supported database engine before upgrading to Yii `22.x`.

There is no compatibility layer for CUBRID in this release. The framework no longer ships CUBRID-specific database
classes, configuration entries, fixtures, or test coverage.

#### `InCondition` and `InConditionBuilder` typing + composite `NULL` handling

`yii\db\conditions\InCondition` now uses typed constructor parameters and typed return values:

- `__construct(array|string|ExpressionInterface|Traversable $column, string $operator, array|int|string|ExpressionInterface|Traversable $values)`
- `getOperator(): string`
- `getColumn(): array|string|ExpressionInterface`
- `getValues(): array|int|string|ExpressionInterface`
- `fromArrayDefinition(...): static`

`Traversable` values passed as `$column` or `$values` are normalized to arrays on first access in `getColumn()` /
`getValues()` and cached for subsequent calls.

`yii\db\conditions\InConditionBuilder` protected methods now declare parameter and return types. If you extend this
builder (or DB-specific builders that inherit it), update overridden method signatures accordingly.

Composite `IN` / `NOT IN` generation has changed: the builder now decomposes composite comparisons into boolean
expressions and uses `IS NULL` / `IS NOT NULL` instead of literal `NULL` comparisons.

Example:

- Before: `([[id]], [[name]]) IN ((:p0, NULL))`
- After: `(([[id]] = :p0 AND [[name]] IS NULL))`

If your tests assert exact SQL strings for composite `IN` / `NOT IN`, update expected SQL.

#### MariaDB pagination now uses `OFFSET ... FETCH` (`10.6+`)

`yii\db\mysql\QueryBuilder::buildOrderByAndLimit()` now emits SQL using MariaDB's standard row-limiting clause when the
connected server is MariaDB:

- `OFFSET <n> ROWS` is emitted only when an offset is set.
- `FETCH NEXT <n> ROWS ONLY` is emitted whenever a limit is set, including `limit(0)`.
- No synthetic `ORDER BY` is added when the user did not specify an `ORDER BY`.

MariaDB versions earlier than `10.6` are no longer supported for Yii-generated pagination SQL. MySQL continues to use
`LIMIT` / `LIMIT ... OFFSET ...` because MySQL's `SELECT` syntax does not support `OFFSET ... FETCH`.

If you rely on paginated results without specifying `orderBy()`, note that MariaDB returns rows in an unspecified order,
so `OFFSET`/`FETCH` results may vary between executions. Always specify `orderBy()` when you need deterministic
pagination.

> [!NOTE]
> **Lifecycle:** MariaDB `10.6` LTS was released on July 6, `2021` and introduced the standard
> `SELECT ... OFFSET ... FETCH` row-limiting clause. Community support for `10.6` ends on July 6, `2026`. Newer LTS
> releases are `10.11` (released February 16, `2023`; community support through February 16, `2028`), `11.4` (released
> May 29, `2024`; community support through May 29, `2029`), and `11.8` (released June 4, `2025`; community support
> through June 4, `2028`). MariaDB `10.5` LTS reached community support EOL on June 24, `2025` and is no longer covered
> by Yii. The `10.6+` floor matches the earliest MariaDB release with the standard `OFFSET ... FETCH` syntax.

#### MSSQL pagination now uses `OFFSET ... FETCH` (`2019+`)

`yii\db\mssql\QueryBuilder::buildOrderByAndLimit()` now emits SQL using SQL Server's native row-limiting clause. SQL
Server versions earlier than `2019` are no longer supported (the legacy `ROW_NUMBER()` fallback was removed).

Generated SQL:

- `ORDER BY` is always present (Yii adds `ORDER BY (SELECT NULL)`, or `ORDER BY 1` for `SELECT DISTINCT`, when no
  `orderBy()` is set).
- `OFFSET <n> ROWS` is always emitted for paginated queries (`OFFSET 0 ROWS` when only `limit()` is set).
- `FETCH NEXT <n> ROWS ONLY` is emitted only when `limit(n)` with `n >= 1`.
- `limit(0)` wraps the query as `SELECT * FROM (...) sub WHERE 1=0` (returns zero rows, consistent with
  MySQL/PostgreSQL/SQLite/Oracle).

Behavioral notes:

- `DISTINCT` + unorderable column types: the `ORDER BY 1` fallback cannot sort `text`, `ntext`, `image`, `xml`,
  `geography`, or `geometry`. Add an explicit `orderBy()` when the first selected column has one of those types and
  `DISTINCT` is required.
- Always specify `orderBy()` for deterministic pagination; SQL Server returns rows in an unspecified order otherwise.

> [!NOTE]
> **Lifecycle:** SQL Server `2019` was released on November 4, `2019`. Mainstream support ended on February 28, `2025`;
> extended support (security-only) ends on January 8, `2030`. Although `OFFSET ... FETCH NEXT` has been available since
> SQL Server `2012`, Yii `22.x` raises its supported floor to `2019+` as a policy decision to reduce the legacy test
> matrix; SQL Server `2017` remains in vendor extended support until October 12, `2027`, but is no longer covered by
> Yii.

#### MySQL dead code removal and integer display width cleanup

The minimum supported MySQL version is now **8.0+** (**MariaDB 10.6+**). The following dead code has been removed:

- `Schema::isOldMysql()` method and `$_oldMysql` property (checked for MySQL <= `5.1`, never called).
- `QueryBuilder::supportsFractionalSeconds()` method (always `true` for MySQL `8.0+`).
- `CacheInterface` / `DbCache` imports from `QueryBuilder` (only used by the removed method).
- Version checks for MySQL < `5.6` / < `5.6.4` / < `5.7` in tests.

**Integer display width** (`int(11)`, `bigint(20)`, `smallint(6)`, `tinyint(3)`) has been removed from the MySQL type
map. MySQL `8.0.17+` deprecated display width for integer types and emits deprecation warnings. The new defaults are:

| Before                | After             |
| --------------------- | ----------------- |
| `int(11)`             | `int`             |
| `int(10) UNSIGNED`    | `int UNSIGNED`    |
| `bigint(20)`          | `bigint`          |
| `bigint(20) UNSIGNED` | `bigint UNSIGNED` |
| `smallint(6)`         | `smallint`        |
| `tinyint(3)`          | `tinyint`         |

`tinyint(1)` for `TYPE_BOOLEAN` is preserved; MySQL uses it as the canonical boolean representation.

Explicit integer sizes (for example, `$this->primaryKey(8)`) are now ignored; display width is no longer emitted.

If your application or migrations rely on the exact SQL output of `QueryBuilder::getColumnType()` for integer types
(for example, in string assertions or snapshot tests), update the expected values.

> [!NOTE]
> **Lifecycle:** MySQL `5.7` reached end of extended support on October 31, `2023`. MySQL `8.0` reaches end of extended
> support on April 30, `2026`; MySQL `8.4` is the current LTS release (premier support through April `2029`). MariaDB
> `10.4` reached community support EOL on June 18, `2024`, and MariaDB `10.5` reached community support EOL on June 24,
> `2025`. The version floors catch up to releases that are at or near EOL; the dead code removed in this change
> targeted MySQL branches already unsupported by the vendor.

#### Oracle pagination now uses `OFFSET ... FETCH` (`12.1+`)

`yii\db\oci\QueryBuilder::buildOrderByAndLimit()` now emits SQL using Oracle's native row-limiting clause:

- `OFFSET <n> ROWS` is emitted only when an offset is set.
- `FETCH NEXT <n> ROWS ONLY` is emitted whenever a limit is set, including `limit(0)`. Oracle accepts
  `FETCH NEXT 0 ROWS ONLY` as valid syntax that returns zero rows, so `limit(0)` keeps the same semantics as in
  MySQL/PostgreSQL/SQLite.
- No synthetic `ORDER BY (SELECT NULL)` is added when the user did not specify an `ORDER BY`.

The previous legacy `ROWNUM`/CTE pagination SQL has been removed. Oracle versions earlier than `12.1` are no longer
supported for Yii-generated pagination SQL in the OCI QueryBuilder.

If you rely on paginated results without specifying `orderBy()`, note that Oracle returns rows in an unspecified order,
so `OFFSET`/`FETCH` results may vary between executions. Always specify `orderBy()` when you need deterministic
pagination.

> [!NOTE]
> **Lifecycle:** Oracle Database `12.1` introduced the standard `OFFSET ... FETCH NEXT` row-limiting clause. Premier
> support for `12.1` ended on July 31, `2018`; extended support ended on July 31, `2022`. Oracle Database `19c` remains
> a supported LTS release (premier support through December 31, `2029`, extended through December 31, `2032`); Oracle
> AI Database `26ai` (released January `2026` on-premises) is the newest LTS, with premier support through December
> 31, `2031`. The `12.1+` floor matches the earliest release with native row-limiting syntax.

### HHVM support removed

All HHVM-specific code has been removed from the framework. Yii `22.x` targets PHP `8.3+` on the Zend engine only.

- `yii\base\ErrorException::E_HHVM_FATAL_ERROR` constant has been removed.
- `yii\base\ErrorHandler::handleHhvmError()` method has been removed.
- `yii\base\ErrorHandler` no longer registers a HHVM-specific error handler when `HHVM_VERSION` is defined.
- HHVM-specific test skips and workarounds have been removed.

If your application references `ErrorException::E_HHVM_FATAL_ERROR` or `ErrorHandler::handleHhvmError()`, remove those
references when upgrading.

### jQuery client scripts moved to `yiisoft/yii2-jquery`

Every `*JqueryClientScript` class previously shipped under `framework/jquery/` has moved to the `yiisoft/yii2-jquery`
extension. FQCNs and behaviour are identical, so application code that referenced them keeps working as long as the
extension is installed:

```bash
composer require yiisoft/yii2-jquery
```

Then register the extension's Bootstrap in your application config:

```php
// config/web.php
'bootstrap' => [\yii\jquery\Bootstrap::class],
```

The Bootstrap registers DI container defaults so that `$clientScript` is automatically populated on every supported
widget and validator — `ActiveForm`, `GridView`, `CheckboxColumn`, `Captcha`, `CaptchaValidator`, `BooleanValidator`,
`CompareValidator`, `EmailValidator`, `FileValidator`, `FilterValidator`, `ImageValidator`, `IpValidator`,
`NumberValidator`, `RangeValidator`, `RegularExpressionValidator`, `RequiredValidator`, `StringValidator`,
`TrimValidator`, `UrlValidator` — without any manual wiring.

Asset bundles (`yii\web\JqueryAsset`, `yii\web\YiiAsset`, `yii\widgets\ActiveFormAsset`,
`yii\widgets\MaskedInputAsset`, `yii\widgets\PjaxAsset`, `yii\grid\GridViewAsset`, `yii\validators\ValidationAsset`,
`yii\captcha\CaptchaAsset`), JavaScript files (`yii.js`, `yii.activeForm.js`, `yii.validation.js`, `yii.gridView.js`,
`yii.captcha.js`) and the `MaskedInput` / `Pjax` widgets **remain in the framework** in this release to preserve
backwards compatibility with downstream extensions that depend on those public FQCNs. A later 22.x release will
complete the extraction.

The `$useJquery` property on `yii\web\Application`, `yii\console\Application` and `yii\web\View` has been removed.
Activation of jQuery client scripts is now controlled entirely by whether `yiisoft/yii2-jquery` is installed and its
`Bootstrap` is registered. Applications that do not install the extension get a jQuery-agnostic framework: widgets
render HTML without client-side behaviour and validators perform server-side validation only.

#### New interfaces

Two interfaces allow replacing the jQuery implementation with any alternative:

- `yii\web\client\ClientScriptInterface` strategy for widgets and components (`ActiveForm`, `GridView`,
  `CheckboxColumn`, `Captcha`).
- `yii\validators\client\ClientValidatorScriptInterface` strategy for validators and `CaptchaValidator`.

#### New `clientScript` property

All 13 built-in validators that support client-side validation, the CAPTCHA widget / validator, and the widgets listed
below expose a `clientScript` property that accepts an array config or an object implementing the matching interface:

- `yii\widgets\ActiveForm` and `yii\widgets\ActiveField`
- `yii\grid\GridView` and `yii\grid\CheckboxColumn`
- `yii\captcha\Captcha` and `yii\captcha\CaptchaValidator`
- `BooleanValidator`, `CompareValidator`, `EmailValidator`, `FileValidator`, `ImageValidator`, `IpValidator`,
  `NumberValidator`, `RangeValidator`, `RegularExpressionValidator`, `RequiredValidator`, `StringValidator`,
  `TrimValidator`, `UrlValidator`

#### New method

`Validator::getFormattedClientMessage(string $message, array $params): string` public wrapper around the protected
`formatMessage()`, used by extracted jQuery client script classes when composing error messages.

#### Agnostic behaviour (no extension installed)

When `yiisoft/yii2-jquery` is **not** installed and no custom `clientScript` strategy is configured:

- `clientValidateAttribute()` returns `null` on all built-in validators.
- `getClientOptions()` returns `[]` on all built-in validators.
- `ActiveForm`, `GridView`, `CheckboxColumn` and `Captcha` do not register any client-side script.

#### Custom client script strategy

```php
// Custom validator client script
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
- `yii\captcha\Captcha::registerClientScript()` and `yii\captcha\Captcha::getClientOptions()` (now `public`).
- `yii\captcha\CaptchaValidator::clientValidateAttribute()`

The default implementations now delegate to the configured `$clientScript` handler. Subclasses that call
`parent::clientValidateAttribute()` or `parent::getClientOptions()` are not affected.

### `View::registerJs()` no longer assumes jQuery

`yii\web\View::registerJs()` no longer calls `JqueryAsset::register($this)` automatically when the script position is
`POS_READY` or `POS_LOAD`, and `View::wrapReadyScript()` / `wrapLoadScript()` now emit vanilla JavaScript event
listeners instead of jQuery wrappers:

| Position    | Old wrapper                                       | New wrapper                                                                |
| ----------- | ------------------------------------------------- | -------------------------------------------------------------------------- |
| `POS_READY` | `jQuery(function ($) { ... });`                   | `document.addEventListener('DOMContentLoaded', function (event) { ... });` |
| `POS_LOAD`  | `jQuery(window).on('load', function () { ... });` | `window.addEventListener('load', function (event) { ... });`               |

This is a behaviour change for applications that passed JavaScript fragments relying on `$` being bound inside the
wrapper, for example:

```php
$this->registerJs("$('#foo').click(function () { ... });");
```

In 22.0 that code runs inside a `DOMContentLoaded` listener where `$` is **not** in scope. Two migration options:

1. Wrap your own code explicitly, letting the jQuery plugin define `$`:

    ```php
    $this->registerJs("jQuery(function ($) { $('#foo').click(function () { ... }); });");
    ```

2. Replace the jQuery call with vanilla DOM APIs:

    ```php
    $this->registerJs("document.getElementById('foo').addEventListener('click', function () { ... });");
    ```

Option 1 keeps working transparently as long as `yiisoft/yii2-jquery` is installed and `JqueryAsset` (or any asset
bundle that depends on it) has been registered somewhere in the request.

Because `registerJs()` no longer auto-registers `JqueryAsset`, applications that only used inline `registerJs()` calls
and never explicitly depended on any asset bundle will no longer pull in jQuery. If you still want jQuery on every page,
register `JqueryAsset` (from `yiisoft/yii2-jquery`) in a layout-level asset bundle:

```php
class AppAsset extends \yii\web\AssetBundle
{
    public $depends = [\yii\web\JqueryAsset::class];
}
```

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

The most common reason for writing to `Yii::$classMap` was to _replace_ a framework class with a custom implementation,
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
        "classmap": ["src/overrides/Request.php"],
        "exclude-from-classmap": ["vendor/yiisoft/yii2/web/Request.php"]
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
> If you only need to _extend_ the framework class, do **not** use `exclude-from-classmap`. Instead, declare a subclass
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
`framework/classes.php`) _without_ having Composer's autoloader active. There is no runtime fallback anymore:
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
