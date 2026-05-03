# Upgrading Instructions for Yii Framework 22.x

This file lists the **backwards-incompatible** changes between `2.0.x` and `22.x` and the concrete steps required to
upgrade an existing application. Non-breaking enhancements are documented in [`CHANGELOG.md`](CHANGELOG.md).

For the historical `2.0.x` upgrade notes see [`UPGRADE.md`](UPGRADE.md).

## Upgrade from Yii 2.0.x

### General upgrade notes

- Raised minimum supported PHP version to `8.3`.
- All methods that were previously deprecated have been removed. If your application still uses any deprecated methods,
  you must update your code before upgrading.

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

Bootstrap-specific CSS class defaults have been removed from framework widget properties (`ActiveField`, `ActiveForm`,
`GridView`, `DataColumn`, `DetailView`, `Breadcrumbs`, `LinkPager`, `Captcha`, `ActionColumn`). The framework is now
CSS-agnostic.

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

#### MariaDB

##### Pagination now uses `OFFSET ... FETCH` (`10.6+`)

`yii\db\mysql\QueryBuilder::buildLimit()` now delegates to `buildOffsetFetch()` and emits SQL using MariaDB's standard
row-limiting clause when the connected server is MariaDB:

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

#### MSSQL 

##### Pagination now uses `OFFSET ... FETCH` (`2019+`)

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

#### MySQL 

##### Dead code removal and integer display width cleanup

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

#### Oracle 

##### Pagination now uses `OFFSET ... FETCH` (`12.1+`)

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

#### PostgreSQL

##### Dead code removal (minimum `13+`)

The minimum supported PostgreSQL version is now **`13+`**. Every legacy version branch in the PostgreSQL driver
collapses under this floor, and the following dead code has been removed:

- `yii\db\pgsql\QueryBuilder::oldUpsert()` CTE-based upsert workaround for PostgreSQL `< 9.5`. The `ON CONFLICT`
  syntax (available since PostgreSQL `9.5`) is now used unconditionally.
- `yii\db\pgsql\QueryBuilder::newUpsert()` removed; its logic is now inlined into `upsert()`.
- The `version_compare(..., '9.5', '<')` check in `QueryBuilder::upsert()`.
- The `version_compare(..., '12.0', '>=')` branch in `Schema::findColumns()` for identity column detection
  (`attidentity != ''`). The identity column clause is now always emitted in the catalogue query.

If your application extends `\yii\db\pgsql\QueryBuilder` and overrides or calls `oldUpsert()` or `newUpsert()`, remove
those references. The upsert logic now lives directly in `upsert()` using the `ON CONFLICT` syntax.

##### Pagination now uses `OFFSET ... FETCH` (`13+`)

`yii\db\pgsql\QueryBuilder::buildLimit()` now emits SQL using PostgreSQL's standard row-limiting clause:

- `OFFSET <n> ROWS` is emitted only when an offset is set.
- `FETCH NEXT <n> ROWS ONLY` is emitted whenever a limit is set, including `limit(0)`.
- Raw `Expression` values are wrapped in parentheses, for example `FETCH NEXT (1 + 1) ROWS ONLY`.
- No synthetic `ORDER BY` is added when the user did not specify an `ORDER BY`.

If your tests assert exact SQL strings for PostgreSQL paginated queries, replace expected `LIMIT` / `OFFSET` clauses
with `OFFSET ... ROWS` / `FETCH NEXT ... ROWS ONLY`.

If you rely on paginated results without specifying `orderBy()`, note that PostgreSQL returns rows in an unspecified
order, so `OFFSET` / `FETCH` results may vary between executions. Always specify `orderBy()` when you need
deterministic pagination.

> [!NOTE]
> **Lifecycle:** PostgreSQL releases one major version per year, each supported for five years. PostgreSQL `13` was
> released on September 24, `2020` and reached community EOL on November 13, `2025`. Supported majors include
> PostgreSQL `14` (through November 12, `2026`), `15` (through November 11, `2027`), `16` (through November 9, `2028`),
> `17` (through November 8, `2029`), and `18` (released September 25, `2025`; through November 14, `2030`).
> PostgreSQL `12` reached community EOL on November 14, `2024` and is no longer covered by Yii. The `13+` floor lets
> Yii use `ON CONFLICT` upsert (`9.5+`) and `GENERATED AS IDENTITY` columns (`12+`) unconditionally, without runtime
> version branching.

#### SQLite 

##### Offset-only pagination now uses `LIMIT -1 OFFSET`

`yii\db\sqlite\QueryBuilder::buildLimit()` continues to emit SQLite's documented `LIMIT` / `OFFSET` syntax. SQLite does
not support the SQL-standard `OFFSET ... FETCH` row-limiting clause.

Offset-only queries now emit:

```sql
LIMIT -1 OFFSET <n>
```

instead of:

```sql
LIMIT 9223372036854775807 OFFSET <n>
```

SQLite treats a negative `LIMIT` expression as no upper bound, so the generated SQL remains equivalent while matching
the documented SQLite behavior. If your tests assert exact SQL strings for SQLite offset-only queries, update the
expected SQL.

SQLite `LIMIT` and `OFFSET` clauses also accept scalar expressions, so raw `Expression` values such as `1 + 1` remain
supported in Yii-generated pagination SQL.

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
widget and validator; `ActiveForm`, `GridView`, `CheckboxColumn`, `Captcha`, `CaptchaValidator`, `BooleanValidator`,
`CompareValidator`, `EmailValidator`, `FileValidator`, `FilterValidator`, `ImageValidator`, `IpValidator`,
`NumberValidator`, `RangeValidator`, `RegularExpressionValidator`, `RequiredValidator`, `StringValidator`,
`TrimValidator`, `UrlValidator` without any manual wiring.

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

### RBAC `DbManager` MSSQL cascade

`yii\rbac\DbManager` no longer installs the `INSTEAD OF` triggers for MSSQL. The schema now declares native FK
`ON DELETE` / `ON UPDATE CASCADE` for every FK except `auth_item_child.child`, which stays at `NO ACTION` because MSSQL
rejects the second cascading FK on the same target as a multi-path violation. The `child` direction is handled in PHP
through the new `protected function requiresSoftCascade(): bool` hook.

The `m200409_110543_rbac_update_mssql_trigger` migration file has been removed.

#### What you must do

**MSSQL applications upgrading from `2.0.x`:** run the following statements once, on a maintenance window, before
the new framework code starts handling requests. The new cascade code relies on the FK actions and will fail on
`removeItem` / `removeAllItems` while the old `NO ACTION` FKs are still in place.

```sql
-- 1. Drop the legacy INSTEAD OF triggers.
IF (OBJECT_ID(N'dbo.trigger_update_auth_item_child') IS NOT NULL)
    DROP TRIGGER dbo.trigger_update_auth_item_child;
IF (OBJECT_ID(N'dbo.trigger_delete_auth_item_child') IS NOT NULL)
    DROP TRIGGER dbo.trigger_delete_auth_item_child;
IF (OBJECT_ID(N'dbo.trigger_auth_item_child') IS NOT NULL)
    DROP TRIGGER dbo.trigger_auth_item_child;

-- 2. Replace the FK actions to match the new schema. Substitute the actual constraint names if they differ.
ALTER TABLE auth_item_child DROP CONSTRAINT FK__auth_item__parent;
ALTER TABLE auth_item_child
    ADD CONSTRAINT FK__auth_item__parent FOREIGN KEY (parent) REFERENCES auth_item(name)
        ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE auth_assignment DROP CONSTRAINT FK__auth_assignment__item_name;
ALTER TABLE auth_assignment
    ADD CONSTRAINT FK__auth_assignment__item_name FOREIGN KEY (item_name) REFERENCES auth_item(name)
        ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE auth_item DROP CONSTRAINT FK__auth_item__rule_name;
ALTER TABLE auth_item
    ADD CONSTRAINT FK__auth_item__rule_name FOREIGN KEY (rule_name) REFERENCES auth_rule(name)
        ON DELETE SET NULL ON UPDATE CASCADE;
```

The `auth_item_child.child` FK is intentionally left without actions; do not add `ON DELETE CASCADE` or
`ON UPDATE CASCADE` to it (MSSQL will reject the constraint with error 1785).

The orphan row in your `migration` table for `m200409_110543_rbac_update_mssql_trigger` is harmless for `migrate/up`, 
but `migrate/down --all` will fail trying to load the missing class. If you need full rollback, remove the row:

```sql
DELETE FROM migration WHERE version = 'm200409_110543_rbac_update_mssql_trigger';
```

**Subclasses of `yii\rbac\DbManager`:** the class now declares `strict_types=1`. Override signatures must match the
parameter and return types declared by the parent. The new `protected function requiresSoftCascade(): bool` hook
returns `true` for `mssql` / `sqlsrv` / `dblib`; override only when adding a custom MSSQL-like driver. Per-strategy
extension points (`removeItemSoftCascade()`, `removeItemManualCascade()`, `updateItemSoftCascade()`,
`updateItemManualCascade()`, `removeAllItemsSoftCascade()`, `removeAllItemsManualCascade()`,
`updateRuleManualCascade()`, `removeRuleManualCascade()`, `removeAllRulesManualCascade()`) are available for finer
overrides.

### Standalone action dispatch incidental BC

Alongside the new standalone-action dispatch (additive feature), two incidental changes can break code that relied on
prior behavior:

- `yii\base\Action::$controller` is now nullable. Subclass overrides and any code that read `$action->controller`
  unchecked must add a `null` check.
- `yii\filters\AccessRule::matchController()` accepts `null` and a rule with a non-empty `controllers` constraint
  no longer matches when the action runs standalone. To preserve the previous behavior of always matching, leave
  `controllers` empty on the rule.

### View no longer assumes jQuery (`View::registerJs()`)

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
