# Upgrading from Yii2 2.0 to Yii2 22.0

This file documents the backward-incompatible changes between Yii2 `2.0.x` and Yii2 `22.0` and explains how to update an
existing application. Non-breaking enhancements and bug fixes are listed in [`CHANGELOG.md`](CHANGELOG.md).

Before following this guide, update the application to the latest Yii2 `2.0.x` release and apply every relevant section
of [`UPGRADE.md`](UPGRADE.md). This keeps the `22.0` migration focused on changes introduced by the new major release.

## Recommended upgrade process

1. Update the application to the latest Yii2 `2.0.x` release.
2. Upgrade the runtime and CI environments to PHP `8.3` or later.
3. Remove the deprecated APIs listed in this guide while the application still runs on Yii2 `2.0.x`, where possible.
4. Check that every installed Yii2 extension supports Yii2 `22.0` and PHP `8.3`.
5. Review the front-end asset and optional jQuery changes before running Composer.
6. Verify that the database server meets the new minimum version and apply any required schema migrations.
7. Upgrade Yii2, regenerate Composer's autoloader, clear runtime caches, and run the full test suite.

> [!IMPORTANT]
> Back up the database before applying the MSSQL session or RBAC schema changes described below.

## Runtime and database requirements

Yii2 `22.0` requires PHP `8.3` or later.

The supported database versions are:

| Database             | Minimum version | Important migration impact                                                                  |
| -------------------- | --------------: | ------------------------------------------------------------------------------------------- |
| MySQL                |           `8.0` | Integer display widths are no longer generated.                                             |
| MariaDB              |          `10.6` | Pagination uses `OFFSET ... FETCH`.                                                         |
| Microsoft SQL Server |          `2019` | Pagination and schema operations use modern SQL Server syntax and catalog views.            |
| Oracle Database      |          `12.1` | New auto-increment columns use native identity columns; pagination uses `OFFSET ... FETCH`. |
| PostgreSQL           |            `14` | Legacy upsert and identity-detection branches have been removed.                            |
| SQLite               |          `3.35` | Upserts use native `INSERT ... ON CONFLICT`.                                                |

CUBRID support has been removed. Applications using CUBRID must migrate to another database engine before upgrading.

## Composer is now the only class autoloader

Yii2 no longer registers its own SPL autoloader. The following APIs and files have been removed:

- `Yii::autoload()` and `yii\BaseYii::autoload()`;
- `Yii::$classMap` and `yii\BaseYii::$classMap`;
- `framework/classes.php`;
- the `build classmap` console command.

All framework and application classes must be reachable through Composer's `autoload` or `autoload-dev` configuration.

### Registering application classes

Replace runtime class-map entries with PSR-4 mappings in the application's `composer.json`.

Before:

```php
Yii::$classMap['app\\helpers\\MyHelper'] = '@app/helpers/MyHelper.php';
```

After:

```json
{
    "autoload": {
        "psr-4": {
            "app\\": "src/"
       }
    }
}
```

Place `app\helpers\MyHelper` in `src/helpers/MyHelper.php`, then regenerate the autoloader:

```bash
composer dump-autoload
```

Use an optimized or authoritative class map in production when appropriate:

```bash
composer dump-autoload --classmap-authoritative
```

### Replacing framework classes

If the application used `Yii::$classMap` to replace a framework class, use Composer's `classmap` and 
`exclude-from-classmap` options instead:

```json
{
    "autoload": {
        "classmap": ["src/overrides/Request.php"],
        "exclude-from-classmap": ["vendor/yiisoft/yii2/web/Request.php"]
    }
}
```

The replacement file must declare the original FQCN and implement its complete public API. It cannot extend the class it
replaces because that would be self-inheritance. If only customization is needed, create a subclass under an application
namespace and configure the corresponding application component to use it.

### Archive installations

The official Yii2 archive includes a Composer-generated `vendor/` directory. Custom entry scripts must load
`vendor/autoload.php` before referencing `Yii` or any `yii\...` class. Requiring `framework/Yii.php` directly without an
active Composer autoloader is no longer supported.

## Front-end assets

### Bower dependencies replaced by NPM packages

The framework no longer declares `bower-asset/*` Composer dependencies. Its JavaScript dependencies are declared as NPM
packages instead.

The default asset aliases have changed:

| Yii2 2.0                                | Yii2 22.0                                          |
| --------------------------------------- | -------------------------------------------------- |
| `@bower` points to `<vendorPath>/bower` | `@bower` is no longer registered by the framework. |
| `@npm` points to `<vendorPath>/npm`     | `@npm` points to `<basePath>/node_modules`.        |

If the application or an extension uses an `@bower/...` source path, replace it with the corresponding `@npm/...` path
and ensure that the package is installed in the application's `node_modules` directory. Remove Asset Packagist and Bower
requirements only after confirming that no installed extension still needs them.

Applications may manage the required packages with NPM directly, through Foxy, from a CDN, or by overriding the affected
asset bundles. Whichever approach is used, every registered bundle must resolve its files under the configured `@npm`
alias.

### jQuery integration moved to `yiisoft/yii2-jquery`

jQuery-backed client-script strategies are now provided by the `yiisoft/yii2-jquery` extension. Applications that need
the Yii2 `2.0.x` client-side behavior must install and bootstrap it:

```bash
composer require yiisoft/yii2-jquery
```

```php
// config/web.php
return [
    'bootstrap' => [
        \yii\jquery\Bootstrap::class,
    ],
];
```

The extension registers the default client-script strategies for `ActiveForm`, `GridView`, `CheckboxColumn`, `Captcha`,
`CaptchaValidator`, and the built-in validators that support client-side validation.

The following properties have been removed:

- `yii\web\Application::$useJquery`;
- `yii\console\Application::$useJquery`;
- `yii\web\View::$useJquery`.

Whether jQuery behavior is active is now determined by the configured client-script strategies. Without the extension or
a custom strategy:

- widgets render their server-side HTML without registering their jQuery behavior;
- built-in validators continue to validate on the server but do not generate client-side validation scripts;
- `clientValidateAttribute()` returns `null` and `getClientOptions()` returns an empty array where applicable.

The public asset-bundle and widget FQCNs, including `yii\web\JqueryAsset`, `yii\web\YiiAsset`, `MaskedInput`, and `Pjax`,
remain in the framework in `22.0` for compatibility with existing extensions.

Custom integrations may implement:

- `yii\web\client\ClientScriptInterface` for widgets and web components;
- `yii\validators\client\ClientValidatorScriptInterface` for validators.

The affected widgets and validators expose a `clientScript` property that accepts an implementation instance or an
object configuration.

```php
ActiveForm::begin([
    'clientScript' => [
        'class' => MyFormClientScript::class,
    ],
]);
```

If a subclass overrides `ActiveForm::getClientOptions()`, `ActiveField::getClientOptions()`,
`Validator::clientValidateAttribute()`, `Captcha::registerClientScript()`, `Captcha::getClientOptions()`, or
`CaptchaValidator::clientValidateAttribute()`, review the override because the default implementation now delegates to
the configured strategy.

### `View::registerJs()` no longer assumes jQuery

`yii\web\View::registerJs()` no longer registers `JqueryAsset` automatically for `POS_READY` or `POS_LOAD`. Its wrappers
now use native DOM events:

| Position    | Yii2 2.0 wrapper                                  | Yii2 22.0 wrapper                                                          |
| ----------- | ------------------------------------------------- | -------------------------------------------------------------------------- |
| `POS_READY` | `jQuery(function ($) { ... });`                   | `document.addEventListener('DOMContentLoaded', function (event) { ... });` |
| `POS_LOAD`  | `jQuery(window).on('load', function () { ... });` | `window.addEventListener('load', function (event) { ... });`               |

JavaScript fragments that assume `$` is injected must either wrap themselves explicitly or use native DOM APIs.

```php
// Keep using jQuery explicitly.
$this->registerJs("jQuery(function ($) { $('#save').on('click', handler); });");

// Or migrate to native DOM APIs.
$this->registerJs("document.getElementById('save').addEventListener('click', handler);");
```

If jQuery is still required globally, register `yii\web\JqueryAsset` through the application's layout asset bundle.

### Bootstrap-specific widget defaults removed

Core widgets no longer assume Bootstrap CSS. The following defaults changed:

| Class and property                | Yii2 2.0 default                                                | Yii2 22.0 default            |
| --------------------------------- | --------------------------------------------------------------- | ---------------------------- |
| `ActiveField::$options`           | `['class' => 'form-group']`                                     | `[]`                         |
| `ActiveField::$inputOptions`      | `['class' => 'form-control']`                                   | `[]`                         |
| `ActiveField::$errorOptions`      | `['class' => 'help-block']`                                     | `['class' => 'field-error']` |
| `ActiveField::$labelOptions`      | `['class' => 'control-label']`                                  | `[]`                         |
| `ActiveForm::$errorCssClass`      | `'has-error'`                                                   | `''`                         |
| `ActiveForm::$successCssClass`    | `'has-success'`                                                 | `''`                         |
| `GridView::$tableOptions`         | `['class' => 'table table-striped table-bordered']`             | `[]`                         |
| `GridView::$filterErrorOptions`   | `['class' => 'help-block']`                                     | `['class' => 'field-error']` |
| `DataColumn::$filterInputOptions` | `['class' => 'form-control', 'id' => null]`                     | `['id' => null]`             |
| `DetailView::$options`            | `['class' => 'table table-striped table-bordered detail-view']` | `['class' => 'detail-view']` |
| `Breadcrumbs::$options`           | `['class' => 'breadcrumb']`                                     | `[]`                         |
| `LinkPager::$options`             | `['class' => 'pagination']`                                     | `[]`                         |
| `Captcha::$options`               | `['class' => 'form-control']`                                   | `[]`                         |

Set the required classes explicitly in application configuration or use the corresponding UI extension. Also review
snapshots and CSS selectors that depended on these implicit classes.

`yii\grid\ActionColumn` no longer creates Bootstrap Glyphicon markup when an icon is not present in `$icons`. Its
fallback is a plain `<span>` containing the icon name. Configure `$icons` or `$buttons` to render the application's icon
system.

## Caching

### `ApcCache` renamed to `ApcuCache`

Replace `yii\caching\ApcCache` with `yii\caching\ApcuCache` and remove the obsolete `$useApcu` configuration.

```php
// Before
'cache' => [
    'class' => \yii\caching\ApcCache::class,
    'useApcu' => true,
],

// After
'cache' => [
    'class' => \yii\caching\ApcuCache::class,
],
```

### XCache and Zend Data Cache removed

`yii\caching\XCache` and `yii\caching\ZendDataCache` have been removed. Replace them with a supported cache backend such
as APCu, Redis, Memcached, the filesystem, or a database cache.

### Deprecated cache aliases removed

| Removed API                   | Replacement               |
| ----------------------------- | ------------------------- |
| `Cache::mget()`               | `Cache::multiGet()`       |
| `Cache::mset()`               | `Cache::multiSet()`       |
| `Cache::madd()`               | `Cache::multiAdd()`       |
| `Dependency::getHasChanged()` | `Dependency::isChanged()` |

## Removed core APIs

The following table lists removed APIs that may still appear in applications or custom framework subclasses. It is more
precise than assuming that every API marked deprecated in Yii2 `2.0.x` was removed; some deprecated APIs remain in
`22.0`.

| Removed API                                                                        | Migration                                                                                                      |
| ---------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| `yii\BaseYii::trace()` / `Yii::trace()`                                            | Use `Yii::debug()`.                                                                                            |
| `yii\BaseYii::powered()` / `Yii::powered()`                                        | Render the attribution text in application code if it is still needed.                                         |
| `yii\base\InvalidParamException`                                                   | Use `yii\base\InvalidArgumentException`.                                                                       |
| `Security::$passwordHashStrategy`                                                  | Remove the configuration; `generatePasswordHash()` selects the supported strategy.                             |
| `Security::generateSalt()`                                                         | Use `generatePasswordHash()` for password hashing; custom subclasses should use PHP's supported password APIs. |
| `Security::shouldUseLibreSSL()`                                                    | Remove the override or call; there is no replacement.                                                          |
| `View::$cacheStack`                                                                | Use `getDynamicContents()`, `pushDynamicContent()`, and `popDynamicContent()`.                                 |
| `View::$dynamicPlaceholders`                                                       | Use `getDynamicPlaceholders()`, `setDynamicPlaceholders()`, and `addDynamicPlaceholder()`.                     |
| `ErrorHandler::convertExceptionToError()`                                          | Throw or handle the original exception directly.                                                               |
| `UniqueValidator::$comboNotUnique`                                                 | Use `UniqueValidator::$message`.                                                                               |
| `Query::getUniqueColumns()`                                                        | Remove custom calls or move the required logic into the application subclass.                                  |
| `Query::getUnaliasedColumnsFromSelect()`                                           | Remove custom calls or move the required logic into the application subclass.                                  |
| `QueryBuilder::$conditionBuilders`                                                 | Use `setExpressionBuilders()` or override `defaultExpressionBuilders()`.                                       |
| `QueryBuilder::buildHashCondition()` and the deprecated `build*Condition()` family | Use `QueryBuilder::buildCondition()`.                                                                          |
| `Connection::$commandClass`                                                        | Configure the required driver entries in `Connection::$commandMap`.                                            |
| `console\Controller::EXIT_CODE_NORMAL`                                             | Use `yii\console\ExitCode::OK`.                                                                                |
| `console\Controller::EXIT_CODE_ERROR`                                              | Use `yii\console\ExitCode::UNSPECIFIED_ERROR`.                                                                 |
| `FileTarget::$rotateByCopy`                                                        | Remove the configuration; setting it to `false` had no effect.                                                 |
| `Request::CSRF_MASK_LENGTH`                                                        | Remove references; the mask length is an implementation detail.                                                |
| `User::getAuthManager()`                                                           | Use `User::getAccessChecker()`.                                                                                |
| `mysql\ColumnSchema::$disableJsonSupport`                                          | Remove the configuration; JSON support is no longer optional.                                                  |
| `pgsql\ColumnSchema::$disableJsonSupport`                                          | Remove the configuration.                                                                                      |
| `pgsql\ColumnSchema::$disableArraySupport`                                         | Remove the configuration.                                                                                      |
| `pgsql\ColumnSchema::$deserializeArrayColumnToArrayExpression`                     | Remove the configuration.                                                                                      |
| `DbMessageSource::CACHE_KEY_PREFIX`                                                | Remove references; the unused constant has no replacement.                                                     |
| `framework/views/createJunctionMigration.php`                                      | Use a normal migration and define the junction table explicitly.                                               |

## Method signatures and subclass compatibility

PHP `8.3` compatibility and native typing changed several public or protected signatures. Custom subclasses must remain
compatible with their parent declarations.

Notable changes include:

- `BaseObject::className(): string` now declares its return type.
- `Action::runWithParams(array $params)` now requires an array.
- `InlineAction::runWithParams($params): mixed` now declares its return type.
- `InCondition` constructor parameters, accessors, and `fromArrayDefinition()` are typed.
- protected methods in `InConditionBuilder` and driver-specific condition builders now declare parameter and return
  types; `getRawValuesFromTraversableObject()` was removed.
- MSSQL `PDO`, `DBLibPDO`, and `SqlsrvPDO` overrides now declare native PDO-compatible parameter and return types.
- `yii\rbac\DbManager` and its new cascade extension points declare native parameter and return types.

Run the application's static analysis and test suite after upgrading. Pay particular attention to classes extending
`Action`, `InlineAction`, `InConditionBuilder`, a database query builder, a database schema class, an MSSQL PDO wrapper,
or `DbManager`.

`#[\SensitiveParameter]` is now applied to secret-bearing parameters in `Security`, `User`, and `IdentityInterface` so
that their values are redacted from stack traces. This does not require a call-site change, but reflection-based tests may
need updated expectations.

## Actions and routing

### Inline action lifecycle

`yii\base\InlineAction::runWithParams()` now invokes `beforeRun()` and `afterRun()`, matching the lifecycle of other
actions. If a custom inline-action class called these hooks manually, remove the duplicate calls. A `false` result from
`beforeRun()` now prevents the action method from running, and `afterRun()` may transform the result.

### Standalone actions

Yii2 `22.0` adds standalone action discovery through `Module::$actionMap`, `Module::$actionNamespace`, and
`yii\web\Action`. Most of this feature is additive, but it introduces the following compatibility considerations:

- `yii\base\Action::$controller` may be `null`. Code that reads this property must perform a `null` check.
- `yii\filters\AccessRule::matchController()` accepts `null`. A rule with a non-empty `controllers` constraint does not
  match a standalone action.
- `Module::$actionMap` is consulted by the module whose `runAction()` handles the route. It is not recursively resolved
  through child modules. Use the child module's `actionNamespace`, or register the action in the dispatching module's
  `actionMap`.

## Database abstraction layer

### Composite `IN` and `NOT IN` conditions

`yii\db\conditions\InCondition` now normalizes `Traversable` columns and values to arrays and caches the normalized
value. Its constructor and getters use native union types.

Composite conditions are expanded into boolean expressions so that `NULL` values use `IS NULL` or `IS NOT NULL`.

```sql
-- Yii2 2.0
([[id]], [[name]]) IN ((:p0, NULL))

-- Yii2 22.0
(([[id]] = :p0 AND [[name]] IS NULL))
```

Update tests that compare exact SQL strings. Subclasses of `InConditionBuilder` must also update their protected method
signatures.

### `resolveTableNames()` removed from database schemas

The protected `resolveTableNames($table, $name)` method has been removed from the MSSQL, MySQL, PostgreSQL, and Oracle
schema classes. Override `resolveTableName($name)` instead. The replacement returns a `TableSchema`; it does not mutate a
`TableSchema` supplied by the caller.

```php
// Before
$table = new TableSchema();
$this->resolveTableNames($table, $name);

// After
$table = $this->resolveTableName($name);
```

### Global `UNION` ordering and pagination

Use `Query::unionOrderBy()`, `addUnionOrderBy()`, `unionLimit()`, and `unionOffset()` to order and paginate the complete
`UNION` result. The existing `orderBy()`, `addOrderBy()`, `limit()`, and `offset()` methods always configure the first
`SELECT`, regardless of when they are called.

```php
$query = (new Query())
    ->from('current_orders')
    ->limit(10) // local to current_orders
    ->union((new Query())->from('archived_orders')->limit(10))
    ->unionOrderBy(['created_at' => SORT_DESC])
    ->unionLimit(20);
```

`ActiveDataProvider` uses these explicit UNION modifiers automatically, so its sorting and pagination apply to the
complete compound result. The corresponding public properties (`unionOrderBy`, `unionLimit`, and `unionOffset`) may also
be configured directly, consistently with the existing query properties.

### Pagination SQL

Generated pagination SQL changed for every supported database except MySQL:

| Database   | Yii2 22.0 behavior                                                                                        |
| ---------- | --------------------------------------------------------------------------------------------------------- |
| MariaDB    | Uses `OFFSET ... ROWS` and `FETCH NEXT ... ROWS ONLY`.                                                    |
| MSSQL      | Uses `ORDER BY ... OFFSET ... ROWS FETCH NEXT ... ROWS ONLY`; the legacy `ROW_NUMBER()` path was removed. |
| Oracle     | Uses `OFFSET ... ROWS` and `FETCH NEXT ... ROWS ONLY`; the legacy `ROWNUM`/CTE path was removed.          |
| PostgreSQL | Uses `OFFSET ... ROWS` and `FETCH NEXT ... ROWS ONLY`; expressions are parenthesized.                     |
| SQLite     | Offset-only queries use `LIMIT -1 OFFSET ...` instead of the maximum signed integer.                      |

MSSQL adds `ORDER BY (SELECT NULL)` when no order is provided, or `ORDER BY 1` for `SELECT DISTINCT` and compound
`UNION` queries. Its `limit(0)` path emits `SELECT TOP (0)` or `SELECT DISTINCT TOP (0)` and ignores the offset. The
other standard-pagination drivers emit a zero-row `FETCH` clause.

Always specify `orderBy()` when pagination order must be deterministic. Update snapshot tests or code that compares exact
SQL generated by the query builders.

### MySQL and MariaDB

MySQL integer display widths are no longer generated:

| Yii2 2.0              | Yii2 22.0         |
| --------------------- | ----------------- |
| `int(11)`             | `int`             |
| `int(10) UNSIGNED`    | `int UNSIGNED`    |
| `bigint(20)`          | `bigint`          |
| `bigint(20) UNSIGNED` | `bigint UNSIGNED` |
| `smallint(6)`         | `smallint`        |
| `tinyint(3)`          | `tinyint`         |

`tinyint(1)` remains the representation of `TYPE_BOOLEAN`. Explicit sizes passed for integer abstract types, such as
`primaryKey(8)`, no longer produce a display width.

The following legacy extension points were removed:

- `mysql\Schema::isOldMysql()` and `$_oldMysql`;
- `mysql\QueryBuilder::supportsFractionalSeconds()`;
- legacy foreign-key and check-constraint branches for unsupported MySQL versions.

### Microsoft SQL Server

The legacy `isOldMssql()`, `oldBuildOrderByAndLimit()`, `newBuildOrderByAndLimit()`, and `getAllColumnNames()` query-builder
extension points have been removed.

MSSQL schema discovery now uses catalog-qualified `sys.*` views and preserves composite-key order. Code that depended on
raw `INFORMATION_SCHEMA` result shapes or exact generated DDL should be retested.

#### `DbSession` data is now binary

`yii\web\DbSession` uses `varbinary(max)` instead of `nvarchar(max)` for the MSSQL `data` column. Existing installations
must alter the configured session table before the new code handles requests:

```sql
ALTER TABLE [dbo].[session] ALTER COLUMN [data] VARBINARY(MAX) NULL;
```

Replace the schema and table name as needed. Because session rows are temporary, consider clearing existing sessions in
the maintenance window rather than preserving payloads through the type conversion.

#### RBAC foreign-key cascades

`yii\rbac\DbManager` no longer uses MSSQL `INSTEAD OF` triggers. Native foreign-key actions are used for every relation
except `auth_item_child.child`, which remains `NO ACTION` because SQL Server rejects the second cascading path to
`auth_item.name`. Yii2 handles that direction in PHP.

Before serving traffic with Yii2 `22.0`:

1. Drop the legacy `trigger_update_auth_item_child`, `trigger_delete_auth_item_child`, and
   `trigger_auth_item_child` triggers if present.
2. Recreate the `auth_item_child.parent` and `auth_assignment.item_name` foreign keys with
   `ON DELETE CASCADE ON UPDATE CASCADE`.
3. Recreate the `auth_item.rule_name` foreign key with `ON DELETE SET NULL ON UPDATE CASCADE`.
4. Keep the `auth_item_child.child` foreign key without cascading actions and re-trust it if an older trigger left it
   untrusted.

First discover the actual constraint names; SQL Server-generated names vary between installations:

```sql
SELECT
    fk.name AS constraint_name,
    OBJECT_SCHEMA_NAME(fk.parent_object_id) AS schema_name,
    OBJECT_NAME(fk.parent_object_id) AS table_name,
    COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS column_name
FROM sys.foreign_keys AS fk
JOIN sys.foreign_key_columns AS fkc ON fkc.constraint_object_id = fk.object_id
WHERE fk.parent_object_id IN (
    OBJECT_ID(N'dbo.auth_item'),
    OBJECT_ID(N'dbo.auth_item_child'),
    OBJECT_ID(N'dbo.auth_assignment')
);
```

Apply the changes in an application migration using the discovered names. Do not add cascading actions to the
`auth_item_child.child` foreign key; SQL Server rejects it with error `1785`.

The removed `m200409_110543_rbac_update_mssql_trigger` migration may remain recorded in the application's `migration`
table. This does not affect `migrate/up`. If the application must support `migrate/down --all`, remove that migration row
after the triggers have been removed:

```sql
DELETE FROM [migration]
WHERE [version] = 'm200409_110543_rbac_update_mssql_trigger';
```

### Oracle

New `TYPE_PK`, `TYPE_UPK`, `TYPE_BIGPK`, and `TYPE_UBIGPK` columns use
`GENERATED BY DEFAULT ON NULL AS IDENTITY`. The invalid `UNSIGNED` keyword has been removed from unsigned primary-key
types. Existing sequence-and-trigger primary keys continue to work; this change affects newly generated DDL.

`batchInsert()` now generates `INSERT INTO ... SELECT ... FROM SYS.DUAL UNION ALL` instead of `INSERT ALL`. Update exact
SQL assertions. This also makes empty-column batch inserts valid and allows each row to receive its own identity value.

Oracle BLOB inserts, updates, and upserts now use native PDO_OCI LOB locators. Applications that subclass Oracle
`Command` or `QueryBuilder` and customize parameter binding should review the new `LobValue` path.

### PostgreSQL

`pgsql\QueryBuilder::oldUpsert()` and `newUpsert()` have been removed. `upsert()` now uses `ON CONFLICT` directly. Remove
calls or overrides of those protected methods.

Reflected `bytea` values may now be returned as strings where Yii2 previously exposed a stream, including values read by
`DbCache`. Code that deliberately handled the old stream representation should accept the normalized string value.

`pgsql\QueryBuilder::alterColumn()` no longer parses `DEFAULT`, `NULL`, `CHECK`, or `UNIQUE` from compound strings. This
avoids generating incorrect SQL for valid PostgreSQL expressions.

A string containing only a type changes the column type while preserving its default and nullability:

```php
$this->alterColumn('foo1', 'bar', 'string');
```

Compound definitions must use a `ColumnSchemaBuilder`. In migrations, type helpers such as `$this->string()` create the
builder:

```php
$this->alterColumn(
    'foo1',
    'bar',
    $this->string()->notNull()->defaultValue('hello world')
);
```

A builder without additional options also changes only the type:

```php
$this->alterColumn('foo1', 'bar', $this->string());
```

Outside migrations, create the builder through the database schema:

```php
$db = Yii::$app->db;

$type = $db->getSchema()
    ->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING)
    ->notNull()
    ->defaultValue('hello world');

$db->createCommand()
    ->alterColumn('foo1', 'bar', $type)
    ->execute();
```

PostgreSQL-specific actions beginning with `SET ...`, `DROP ...`, `RESET (...)`, `RESTART [WITH n]`, or
`ADD GENERATED ...` can still be passed directly:

```php
$this->alterColumn('foo1', 'bar', "SET DEFAULT 'hello world'");
$this->alterColumn('foo1', 'bar', 'DROP DEFAULT');
$this->alterColumn('foo1', 'bar', 'SET NOT NULL');
$this->alterColumn('foo1', 'bar', 'DROP NOT NULL');
```

These keywords cover the complete PostgreSQL `ALTER COLUMN` action grammar, and their contents are never parsed; complex
defaults work through `SET DEFAULT ...` or `defaultExpression()`.

The following compound string is no longer supported:

```php
$this->alterColumn('foo1', 'bar', "string NOT NULL DEFAULT 'hello world'");
```

`defaultValue(null)` does not remove an existing default. Use the explicit `'DROP DEFAULT'` action instead.
`yii\db\ColumnSchemaBuilder` adds public getters for the column definition state used by query builders.
Builder-path `CHECK` and `UNIQUE` constraints are created under stable names (`{table}_{column}_check`,
`{table}_{column}_key`) and replace an existing constraint with the same name, so repeated migrations are idempotent.

### SQLite

`sqlite\QueryBuilder::upsert()` now emits a single native `INSERT ... ON CONFLICT` statement. The old implementation
generated `UPDATE; INSERT OR IGNORE;`.

This changes failure behavior: native upsert handles uniqueness conflicts but does not silently ignore unrelated
`NOT NULL`, check, or foreign-key violations. Those violations now raise an error.

The SQLite-specific `batchInsert()` override has been removed; the base query-builder implementation is used.

SQLite cannot change `PRAGMA foreign_keys` while a transaction or savepoint is active. Executing
`Command::checkIntegrity()` in that state now throws `yii\db\Exception` instead of silently doing nothing. Move the call
before `BEGIN`/`SAVEPOINT` or after `COMMIT`/`ROLLBACK`.

### Reflected column defaults and typecasting

Column-schema default parsing and typecasting were consolidated across drivers. Among other corrections, Yii2 now
preserves MySQL `CURRENT_TIMESTAMP(0)`, recognizes Oracle `CURRENT_TIMESTAMP`, handles PostgreSQL native booleans, and
parses SQLite `DEFAULT NULL` and escaped string literals consistently. Update schema snapshots or metadata assertions
that encoded the previous values.

MySQL `BIT` columns declared as nullable with `DEFAULT NULL` now expose `null` through
`ColumnSchema::$defaultValue`; Yii2 `2.0.x` incorrectly reported `0`. Review code that compares reflected default values
or calls `ActiveRecord::loadDefaultValues()` for models containing these columns.

On MySQL `8.0.13+`, expression-based column defaults reported as `DEFAULT_GENERATED` now expose a
`yii\db\Expression` through `ColumnSchema::$defaultValue` instead of the raw string. This includes parenthesized literal
defaults required by types such as `TEXT` and `JSON`. Review strict type checks, schema snapshots, and code that calls
`ActiveRecord::loadDefaultValues()` for models containing these columns. MariaDB does not report the
`DEFAULT_GENERATED` metadata; its `TEXT` and `JSON` defaults, which MariaDB stores in expression form, are exposed as
a `yii\db\Expression` as well, and a `JSON` default that is valid JSON is returned decoded. Expression defaults on
other MariaDB column types still reflect as plain strings.

On PostgreSQL, expression-based column defaults reported by `pg_get_expr()` — operator expressions, function calls,
and the `nextval(...)` default of a non-primary-key `serial` column — now expose an executable `yii\db\Expression`
through `ColumnSchema::$defaultValue`. Yii2 `2.0.x` mangled these values: `integer DEFAULT (1 + 2)` reflected as `1`,
a `jsonb` expression default as `null`, and a non-primary-key `serial` default as a raw string. Reflected quoted
string literals also unescape SQL-doubled quotes now (`'O''Reilly'` reflects as `O'Reilly`). Review strict type
checks, schema snapshots, and code that calls `ActiveRecord::loadDefaultValues()` for models containing these
columns; on a non-primary-key `serial` column, `loadDefaultValues()` now assigns an `Expression` that advances the
sequence when the record is saved. Primary-key and identity column defaults keep their current behavior.

## Removed platform support

### HHVM

HHVM support has been removed. Yii2 `22.0` runs on the Zend PHP engine only.

The following HHVM-specific APIs were removed:

- `yii\base\ErrorException::E_HHVM_FATAL_ERROR`;
- `yii\base\ErrorHandler::handleHhvmError()`;
- HHVM-specific error-handler registration and test workarounds.

## Post-upgrade checklist

- Run `composer dump-autoload` and verify that no code references `Yii::$classMap` or `Yii::autoload()`.
- Search the application and its local extensions for every removed API listed above.
- Verify that all custom framework subclasses have compatible native signatures.
- Confirm that registered asset bundles resolve their NPM files and that no required bundle still uses `@bower`.
- Test forms, grids, validators, CAPTCHA, PJAX, and inline `registerJs()` calls with and without the jQuery extension as
  appropriate for the application.
- Visually verify widgets after restoring any CSS classes that were previously provided by Yii2 defaults.
- Run migrations and integration tests against every supported database used by the application.
- On MSSQL, verify the session column type and RBAC foreign-key trust/cascade configuration.
- Update tests that compare generated SQL, reflected column defaults, HTML classes, icons, or JavaScript wrappers.
- Clear application caches and restart long-running workers after deployment.
