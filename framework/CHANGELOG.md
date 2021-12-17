Yii Framework 2 Change Log
==========================

2.0.44 under development
------------------------

- Bug #18660: Check name if backslash appears (iridance)
- Enh #13105: Add yiiActiveForm validate_only property for skipping form auto-submission (ptolomaues)
- Enh #18967: Use proper attribute names for tabular data in `yii\widgets\ActiveField::addAriaAttributes()` (AnkIF)
- Bug #18798: Fix `StringHelper::dirname()` when passing string with a trailing slash (perlexed)
- Enh #18328: Raise warning when trying to register a file after `View::endPage()` has been called (perlexed)
- Enh #18812: Added error messages and optimized "error" methods in `yii\helpers\BaseJson` (WinterSilence, samdark)
- Chg #18823: Rollback changes #18806 in `yii\validators\ExistValidator::checkTargetRelationExistence()` (WinterSilence)
- Enh #18826: Add ability to turn the sorting off for a clicked column in GridView with multisort (ditibal)
- Bug #18646: Remove stale identity data from session if `IdentityInterface::findIdentity()` returns `null` (mikehaertl)
- Bug #18832: Fix `Inflector::camel2words()` adding extra spaces (brandonkelly)
- Enh #18762: Added `yii\helpers\Json::$keepObjectType` and `yii\web\JsonResponseFormatter::$keepObjectType` in order to avoid changing zero-indexed objects to array in `yii\helpers\Json::encode()` (zebraf1)
- Enh #18783: Add support for URI namespaced tags in `XmlResponseFormatter` (WinterSilence, samdark)
- Enh #18783: Add `XmlResponseFormatter::$objectTagToLowercase` option to lowercase object tags (WinterSilence, samdark)
- Bug #18845: Fix duplicating `id` in `MigrateController::addDefaultPrimaryKey()` (WinterSilence, samdark)
- Bug #17119: Fix `yii\caching\Cache::multiSet()` to use `yii\caching\Cache::$defaultDuration` when no duration is passed (OscarBarrett)
- Bug #18842: Fix `yii\base\Controller::bindInjectedParams()` to not throw error when argument of `ReflectionUnionType` type is passed (bizley)
- Enh #18858: Reduce memory usage in `yii\base\View::afterRender` method (LeoOnTheEarth)
- Bug #18880: Fix `yii\helpers\ArrayHelper::toArray()` for `DateTime` objects in PHP >= 7.4 (rhertogh)
- Bug #18883: Fix `yii\web\HeaderCollection::fromArray()` now ensures lower case keys (rhertogh)
- Enh #18899: Replace usages of `strpos` with `strncmp` and remove redundant usage of `array_merge` and `array_values` (AlexGx)
- Bug #18898: Fix `yii\helpers\Inflector::camel2words()` to work with words ending with 0 (michaelarnauts)
- Enh #18904: Improve Captcha client-side validation (hexkir)
- Bug #18913: Add filename validation for `MessageSource::getMessageFilePath()` (uaoleg)
- Bug #18909: Fix bug with binding default action parameters for controllers (bizley)
- Bug #18955: Check `yiisoft/yii2-swiftmailer` before using as default mailer in `yii\base\Application` (WinterSilence)
- Bug #18988: Fix default value of `yii\console\controllers\MessageController::$translator` (WinterSilence)
- Enh #19005: Add `yii\base\Module::setControllerPath()` (WinterSilence)
- Bug #18993: Load defaults by `attributes()` in `yii\db\ActiveRecord::loadDefaultValues()` (WinterSilence)
- Bug #19021: Fix return type in PhpDoc `yii\db\Migration` functions `up()`, `down()`, `safeUp()` and `safeDown()` (WinterSilence, rhertogh)
- Bug #19031: Fix displaying console help for parameters with declared types (WinterSilence)
- Bug #19030: Add DI container usage to `yii\base\Widget::end()` (papppeter)


2.0.43 August 09, 2021
----------------------

- Bug #14663: Do not convert int to string if database type of column is numeric (egorrishe)
- Bug #18274: Fix `yii\log\Logger` to calculate profile timings no matter the value of the flush interval (bizley)
- Bug #18648: Fix `yii\web\Request` to properly handle HTTP Basic Auth headers (olegbaturin)
- Bug #18650: Refactor `framework/assets/yii.activeForm.js` arrow function into traditional function for IE11 compatibility (marcovtwout)
- Bug #18678: Fix `yii\caching\DbCache` to use configured cache table name instead of the default one in case of MSSQL varbinary column type detection (aidanbek)
- Bug #18749: Fix `yii\web\ErrorHandler::encodeHtml()` to support strings with invalid UTF symbols (vjik)
- Bug #18756: Fix `\yii\validators\ExistValidator::queryValueExists` to validate against an array of unique values (DrDeath72)
- Bug #18807: Fix replacing source whitespaces and optimize code of `yii\helpers\BaseStringHelper::mb_ucwords()` (WinterSilence)
- Enh #18274: Add `profilingAware` option to `yii\log\Logger` to prevent breaking the profiling block messages pair when flushing them (bizley)
- Enh #18628: Add strings "software", and "hardware" to `$specials` array in `yii\helpers\BaseInflector` (kjusupov)
- Enh #18653: Add method `yii\helpers\BaseHtml::getInputIdByName()` (WinterSilence)
- Enh #18656: Add ability for `yii serve`'s `--router` param to take an alias (markhuot)
- Enh #18669: Change visibility of `yii\web\User::checkRedirectAcceptable()` to `public` (rhertogh)
- Enh #18674: Add more user-friendly exception messages for `yii\i18n\Formatter` (bizley)
- Enh #18676: Add method `yii\helpers\BaseFileHelper::changeOwnership()` and `newFileMode`/`newFileOwnership` properties to `yii\console\controllers\BaseMigrateController` (rhertogh)
- Enh #18695: Add `yii\web\Cookie::SAME_SITE_NONE` constant (rhertogh)
- Enh #18707: Change the base error handler to not expose `$_SERVER` details unless `YII_DEBUG` is enabled (coolgoose)
- Enh #18712: Add `scheme` option for `$options` argument for `yii\i18n\Formatter::asUrl()` (bizley)
- Enh #18724: Allow jQuery 3.6 to be installed (marcovtwout)
- Enh #18726: Add `yii\helpers\Json::$prettyPrint` (rhertogh)
- Enh #18734: Add `yii\validators\EmailValidator::$enableLocalIDN` (brandonkelly)
- Enh #18789: Add JSONP support in `yii\web\JsonParser::parse()` (WinterSilence)
- Enh #18817: Use `paragonie/random_compat` for random bytes and int generation (samdark)


2.0.42.1 May 06, 2021
---------------------

- Bug #18634: Fix `yii\db\BaseActiveRecord::unlink()` and `unlinkAll()` to omit condition for `on` property when it doesn't exist (bizley)


2.0.42 May 05, 2021
-------------------

- Bug #14343: Fix `yii\test\ActiveFixture` to use model's DB connection instead of the default one (margori, bizley)
- Bug #17174: Fix `yii\db\BaseActiveRecord::unlink()` to not ignore `on` conditions in `via` relations (bizley)
- Bug #17203: Fix `yii\db\Connection` to persist customized `queryBuilder` configuration after the `close()` → `open()` cycle (silverfire)
- Bug #17479: Fix `yii\grid\ActionColumn` to render icons when no glyphicons are available (simialbi)
- Bug #17631: Fix `yii\widgets\BaseListView` to properly render custom summary (sjaakp, bizley)
- Bug #18323: Fix client validation of RadioList when there are disabled items (toir427)
- Bug #18325: Fix `yii\db\pgsql\Schema` to respect non-default PgSQL schema name for data types (theonedemon, silverfire)
- Bug #18526: Fix `yii\caching\DbCache` to work with MSSQL, add `normalizeTableRowData()` to `yii\db\mssql\QueryBuilder::upsert()` (darkdef)
- Bug #18544: Fix `yii\validators\NumberValidator` to disallow values with whitespaces (bizley)
- Bug #18552: Fix `yii\data\SqlDataProvider` to properly handle SQL with `ORDER BY` clause (bizley)
- Bug #18557: Fix `yii\data\ActiveDataProvider` to handle DB connection configuration of different type than just `yii\db\Connection` (bizley)
- Bug #18574: Fix `yii\web\DbSession` to use the correct db if strict mode is used (Mignar)
- Bug #18585: Fix `yii\validators\EmailValidator` to handle an edge case where `IDN` is enabled, but fails ascii conversion for valid email addresses (ihitbuttons)
- Bug #18590: Fix `yii\web\UrlManager` to instantiate cache only when it's actually needed (bizley)
- Bug #18592: Fix `yii\db\Command::getRawSql()` to not replace query params in invalid places (sartor)
- Bug #18593: Fix setting the `maxlength` attribute for `Html::activeInput()` and `Html::activeTextArea()` based on `length` parameter of validator (BSCheshir)
- Bug #18604: Function alterColumn for MSSQL build incorrect query with default values `NULL` and other expressions (darkdef)
- Bug #18613: Do not call static methods non-statically in `BaseActiveRecord` (samdark)
- Bug #18619: Do not modify `yii\web\Cookie::$path` on `yii\web\Response::sendCookies()` (mikk150)
- Bug #18624: Fix `yii\di\Container` to properly resolve dependencies in case of PHP 8 union types (bizley)
- Enh #18534: Add `prepareSearchQuery` property in `yii\rest\IndexAction` (programmis)
- Enh #18566: Throw the original exception when `yii\web\Controller::bindInjectedParams()` catches HttpException (pigochu)
- Enh #18569: Add `NumberValidator::$allowArray` (raidkon)


2.0.41.1 March 04, 2021
-----------------------

- Bug #18545: Reversed changes made to the `yii\db\Query::all()` and `indexBy` handling (bizley)
- Bug #18548: Fix bug with REST rules with prefixes containing tokens not being parsed properly (bizley)


2.0.41 March 03, 2021
---------------------

- Bug #8750: Fix MySQL support when running in `ANSI`/`ANSI_QUOTES` modes (brandonkelly)
- Bug #9718: Fix user staying authorized despite authKey change (kidol, Charlie Jack, Kunal Mhaske, samdark)
- Bug #18448: Fix issues in queries and tests for older MSSQL versions (darkdef)
- Bug #18450: Allow empty string to be passed as a nullable typed argument to a controller's action (dicrtarasov, bizley)
- Bug #18464: Fix bug with processing fallback messages when translation language is set to `null` (bizley)
- Bug #18472: Fix initializing `db` component configuration in `yii\data\ActiveDataProvider` (bizley)
- Bug #18477: Fix detecting availability of Xdebug's stack trace in `yii\base\ErrorException` (bizley)
- Bug #18479: Fix invalid argument type for `preg_split()` in `\yii\console\Controller` (gazooz)
- Bug #18480: Transactions are not committed using the dblib driver (bbrunekreeft)
- Bug #18505: Fix `yii\helpers\ArrayHelper::getValue()` for ArrayAccess objects with explicitly defined properties (samdark)
- Bug #18508: Fix Postgres SQL query for load table indexes with correct column order (insolita)
- Bug #18529: Fix asset files path with `appendTimestamp` option for non-root-relative base URLs (bizley)
- Bug #18535: Set Cookie SameSite to Lax by default (samdark)
- Bug #18539: Fix "driver does not support quoting" when using the driver pdo_odbc (xpohoc69)
- Enh #18447: Do not use `getLastInsertID()` to get PK from insert query to lower collision probability for concurrent inserts (darkdef)
- Enh #18455: Add ability to use separate attributes for data model and filter model of `yii\grid\GridView` in `yii\grid\DataColumn` (PowerGamer1)
- Enh #18457: Add `EVENT_RESET` and `EVENT_FINISH` events to `yii\db\BatchQueryResult` (brandonkelly)
- Enh #18460: `compareValue` in `CompareValidator` can now take a closure returning a value (mmonem)
- Enh #18483: Add `yii\log\Logger::$dbEventNames` that allows specifying event names used to get statistical results (profiling) of DB queries (atiline)
- Enh #18487: Allow creating URLs for non-GET-verb rules (bizley)
- Enh #18493: Faster request parsing for REST UrlRule with prefix handling (bizley)
- Enh #18499: When using `yii\db\Query::all()` and `yii\db\Query::$indexBy`, the `yii\db\Query::$indexBy` is auto inserted into `yii\db\Query::$select` - the same as in `yii\db\Query::column()` (OndrejVasicek, samdark, bizley)
- Enh #18518: Add support for ngrok’s `X-Original-Host` header (brandonkelly)


2.0.40 December 23, 2020
------------------------

- Bug #16492: Fix eager loading Active Record relations when relation key is a subject to a type-casting behavior (bizley)
- Bug #18199: Fix content body response on 304 HTTP status code, according to RFC 7232 (rad8329)
- Bug #18287: Fix the OUTPUT got SQL syntax error if the column name is MSSQL keyword e.g. key (darkdef)
- Bug #18339: Fix migrate controller actions to return exit codes (haohetao, bizley)
- Bug #18365: Move quoting of table names to upper level to function `getSchemaMetadata()` in MSSQL driver to get clean names from the schema (darkdef)
- Bug #18383: RBAC's generated file made PSR-12 compliant (perlexed)
- Bug #18386: Fix `assets/yii.activeForm.js` incorrect target selector for `validatingCssClass` (brussens)
- Bug #18393: Fix `ActiveRecord::refresh()` to load data from the database even if cache is enabled (hooman-mirghasemi)
- Bug #18395: Fix regression in `yii\helpers\BaseArrayHelper::filter()` (allowing filtering arrays with numeric keys) (bizley)
- Bug #18400: Set parent module of the newly attached child module by `Module::setModule()` and `Module::setModules()` (sup-ham)
- Bug #18406: Fix PDO exception when committing or rolling back an autocommitted transaction in PHP 8 (brandonkelly)
- Bug #18414: Fix `AssetManager::appendTimestamp()` not appending timestamp for website root in sub-directory (Isitar)
- Bug #18426: Fix check for route's leading slash in `yii\widgets\Menu` (stevekr)
- Bug #18435: Fix ensuring Active Record relation links' keys to be strings (bizley)
- Bug #18437: Change the check order whether an object is an implementation of `Arrayable` or `JsonSerializable` in `\yii\base\ArrayableTrait::toArray()` and `\yii\rest\Serializer::serialize()` (spell6inder)
- Bug #18442: Fix calls with array access to string (bizley)
- Enh #18381: The `yii\web\AssetManager` `$basePath` readable and writeable check has been moved to the `checkBasePathPermission()`. This check will run once before `publishFile()` and `publishDirectory()` (nadar)
- Enh #18394: Add support for setting `yii\web\Response::$stream` to a callable (brandonkelly)


2.0.39.3 November 23, 2020
--------------------------

- Bug #18396: Fix not throw `InvalidConfigException` when failed to instantiate class via DI container in some cases (vjik)
- Enh #18200: Add `maxlength` attribute by default to the input text when it is an active field within a `yii\grid\DataColumn` (rad8329)


2.0.39.2 November 13, 2020
--------------------------

- Bug #18378: Fix not taking default value when unable to resolve abstract class via DI container (vjik)


2.0.39.1 November 10, 2020
--------------------------

- Bug #18373: Fix not taking default value when unable to resolve non-existing class via DI container (vjik)
- Enh #18370: Add option to provide a string replacement for `null` value in `yii\data\DataFilter` (bizley)


2.0.39 November 10, 2020
------------------------

- Bug #16418: Fix `yii\data\Pagination::getLinks()` to return links to the first and the last pages regardless of the current page (ptz-nerf, bizley)
- Bug #16831: Fix console Table widget does not render correctly in combination with ANSI formatting (issidorov, cebe)
- Bug #18160, #18192: Fix `registerFile` with set argument `depends` does not take `position` and `appendTimestamp` into account (baleeny)
- Bug #18263: Fix writing `\yii\caching\FileCache` files to the same directory when `keyPrefix` is set (githubjeka)
- Bug #18287: Fix for `OUTPUT INSERTED` and computed columns. Add flag to mark computed values in table schema (darkdef)
- Bug #18290: Fix response with non-seekable streams (schmunk42)
- Bug #18297: Replace usage of deprecated `ReflectionParameter::isArray()` method in PHP8 (baletskyi)
- Bug #18303: Fix creating migration for column methods used after `defaultValues` (wsaid)
- Bug #18308: Fix `\yii\base\Model::getErrorSummary()` reverse order (DrDeath72)
- Bug #18313: Fix multipart form data parsing with double quotes (wsaid)
- Bug #18317: Additional PHP 8 compatibility fixes (samdark, bizley)
- Enh #18247: Add support for the 'session.use_strict_mode' ini directive in `yii\web\Session` (rhertogh)
- Enh #18285: Enhanced DI container to allow passing parameters by name in a constructor (vjik)
- Enh #18304: Add support of constructor parameters with default values to DI container (vjik)
- Enh #18351: Add option to change default timezone for parsing formats without time part in `yii\validators\DateValidator` (bizley)


2.0.38 September 14, 2020
-------------------------

- Bug #13973: Correct alterColumn for MSSQL & drop constraints before dropping a column (darkdef)
- Bug #15265: PostgreSQL > 10.0 is not pass tests with default value of timestamp CURRENT_TIMESTAMP (terabytesoftw)
- Bug #16892: Validation error class was not applied to checkbox and radio when validationStateOn = self::VALIDATION_STATE_ON_INPUT (dan-szabo, samdark)
- Bug #18040: Display width specification for integer data types was deprecated in MySQL 8.0.19 (terabytesoftw)
- Bug #18066: Fix `yii\db\Query::create()` wasn't using all info from `withQuery()` (maximkou)
- Bug #18229: Add a flag to specify SyBase database when used with pdo_dblib (darkdef)
- Bug #18232: Fail tests pgsql v-10.14, v-11.9, v-12-latest (terabytesoftw)
- Bug #18233: Add PHP 8 support (samdark)
- Bug #18239: Fix support of no-extension files for `FileValidator::validateExtension()` (darkdef)
- Bug #18245: Make resolving DI references inside of arrays in dependencies optional (SamMousa, samdark, hiqsol)
- Bug #18248: Render only one stack trace on a console for chained exceptions (mikehaertl)
- Bug #18269: Fix integer safe attribute to work properly in `yii\base\Model` (Ladone)
- Bug: (CVE-2020-15148): Disable unserialization of `yii\db\BatchQueryResult` to prevent remote code execution in case application calls unserialize() on user input containing specially crafted string (samdark, russtone)
- Enh #18196: `yii\rbac\DbManager::$checkAccessAssignments` is now `protected` (alex-code)
- Enh #18213: Do not load fixtures with circular dependencies twice instead of throwing an exception (JesseHines0)
- Enh #18236: Allow `yii\filters\RateLimiter` to accept a closure function for the `$user` property in order to assign values on runtime (nadar)


2.0.37 August 07, 2020
----------------------

- Bug #17147: Fix form attribute validations for empty select inputs (kartik-v)
- Bug #18156: Fix `yii\db\Schema::quoteSimpleTableName()` was checking incorrect quote character (M4tho, samdark)
- Bug #18170: Fix 2.0.36 regression in passing extra console command arguments to the action (darkdef)
- Bug #18171: Change case of column names in SQL query for `findConstraints` to fix MySQL 8 compatibility (darkdef)
- Bug #18182: `yii\db\Expression` was not supported as condition in `ActiveRecord::findOne()` and `ActiveRecord::findAll()` (rhertogh)
- Bug #18189: Fix "Invalid parameter number" in `yii\rbac\DbManager::removeItem()` (samdark)
- Bug #18198: Fix saving tables with trigger by outputting inserted data from insert query with usage of temporary table (darkdef)
- Bug #18203: PDO exception code was not properly passed to `yii\db\Exception` (samdark)
- Bug #18204: Fix 2.0.36 regression in inline validator and JS validation (samdark)
- Enh #18205: Add `.phpstorm.meta.php` file for better auto-completion in PhpStorm (vjik)
- Enh #18210: Allow strict comparison for multi-select inputs (alex-code)
- Enh #18217: Make `yii\console\ErrorHandler` render chained exceptions in debug mode (mikehaertl)


2.0.36 July 07, 2020
--------------------

- Bug #13828: Fix retrieving inserted data for a primary key of type uniqueidentifier for SQL Server 2005 or later (darkdef)
- Bug #17474: Fix retrieving inserted data for a primary key of type trigger for SQL Server 2005 or later (darkdef)
- Bug #17985: Convert migrationNamespaces to array if needed (darkdef)
- Bug #18001: Fix getting table metadata for tables `(` in their name (floor12)
- Bug #18026: Fix `ArrayHelper::getValue()` did not work with `ArrayAccess` objects (mikk150)
- Bug #18028: Fix division by zero exception in console `Table::calculateRowHeight()` (fourhundredfour)
- Bug #18031: `HttpBasicAuth` with auth callback now triggers login events same was as other authentication methods (samdark)
- Bug #18041: Fix RBAC migration for MSSQL (darkdef)
- Bug #18047: Fix colorization markers output in console `Table` (cheeseq)
- Bug #18051: Fix missing support for custom validation method in EachValidator (bizley)
- Bug #18051: Fix using `EachValidator` with custom validation function (bizley)
- Bug #18081: Fix for PDO_DBLIB/MSSQL. Set flag `ANSI_NULL_DFLT_ON` to ON for current DB connection (darkdef)
- Bug #18086: Fix accessing public properties of `ArrayAccess` via `ArrayHelper::getValue()` (samdark)
- Bug #18094: Add support for composite file extension validation (darkdef)
- Bug #18096: Fix `InlineValidator` with anonymous inline function not working well from `EachValidator` (trombipeti)
- Bug #18101: Fix behavior of `OUTPUT INSERTED.*` for SQL Server query: "insert default values"; correct MSSQL unit tests; turn off profiling echo message in migration test (darkdef)
- Bug #18105: Fix for old trigger in RBAC migration with/without `prefixTable` (darkdef)
- Bug #18110: Add quotes to return value of viewName in MSSQL schema. It is `[someView]` now (darkdef)
- Bug #18127: Resolve DI references inside of arrays in dependencies (hiqsol)
- Bug #18134: `Expression` as `columnName` should not be quoted in `likeCondition` (darkdef)
- Bug #18147: Fix parameters binding for MySQL when prepare emulation is off (rskrzypczak)
- Enh #15202: Add optional param `--silent-exit-on-exception` in `yii\console\Controller` (egorrishe)
- Enh #17722: Add action injection support (SamMousa, samdark, erickskrauch)
- Enh #18019: Allow jQuery 3.5.0 to be installed (wouter90)
- Enh #18048: Use `Instance::ensure()` to set `User::$accessChecker` (lav45)
- Enh #18083: Add `Controller::$request` and `$response` (brandonkelly)
- Enh #18120: Include the path to the log file into error message if `FileTarget::export` fails (uaoleg)
- Enh #18151: Add `Mutex::isAcquired()` to check if lock is currently acquired (rhertogh)


2.0.35 May 02, 2020
-------------------

- Bug #16481: Fix RBAC MSSQL trigger (achretien)
- Bug #17653: Fix `TypeError: pair[1] is undefined` when query param doesn't have `=` sign (baso10)
- Bug #17810: Fix `EachValidator` crashing with uninitialized typed properties (ricardomm85)
- Bug #17942: Fix for `DbCache` loop in MySQL `QueryBuilder` (alex-code)
- Bug #17948: Ignore errors caused by `set_time_limit(0)` (brandonkelly)
- Bug #17960: Fix unsigned primary key type mapping for SQLite (bizley)
- Bug #17961: Fix pagination `pageSizeLimit` ignored if set as array with more then 2 elements (tsvetiligo)
- Bug #17974: Fix `ActiveRelationTrait` compatibility with PHP 7.4 (Ximich)
- Bug #17975: Fix deleting unused messages with console command if message tables were created manually (auerswald, cebe)
- Bug #17991: Improve `yii\db\Connection` master and slave failover, no connection attempt was made when all servers are marked as unavailable  (cebe)
- Bug #18000: PK value of Oracle ActiveRecord is missing after save (mankwok)
- Bug #18010: Allow upper or lower case operators in `InCondition` and `LikeCondition` (alex-code)
- Bug #18011: Add attribute labels support for `DynamicModel`, fixed `EachValidator` to pass the attribute label to the underlying `DynamicModel` (storch)
- Enh #17758: `Query::withQuery()` can now be used for CTE (sartor)
- Enh #17993: Add `yii\i18n\Formatter::$currencyDecimalSeparator` to allow setting custom symbols for currency decimal in `IntlNumberFormatter` (XPOHOC269)
- Enh #18006: Allow `SameSite` cookie pre PHP 7.3 (scottix)


2.0.34 March 26, 2020
---------------------

- Bug #17932: Fix regression in detection of AJAX requests (samdark)
- Bug #17933: Log warning instead of erroring when URLManager is unable to initialize cache (samdark)
- Bug #17934: Fix regression in Oracle when binding several string parameters (fen1xpv, samdark)
- Bug #17935: Reset DB quoted table/column name caches when the connection is closed (brandonkelly)


2.0.33 March 24, 2020
---------------------

- Bug #11945: Fix Schema Builder MySQL column definition order (simialbi)
- Bug #13749: Fix Yii opens db connection even when hits query cache (shushenghong)
- Bug #16092: Fix duplicate joins in usage of `joinWith` (germanow)
- Bug #16145: Fix `Html` helper `checkboxList()`, `radioList()`, `renderSelectOptions()`, `dropDownList()`, `listBox()` methods to work properly with traversable selection (samdark)
- Bug #16334: Add `\JsonSerializable` support to `ArrayableTrait` (germanow)
- Bug #17667: Fix `CREATE INDEX` failure on SQLite when specifying schema (santilin, samdark)
- Bug #17679: Fix Oracle exception "ORA-01461: can bind a LONG value only for insert into a LONG column" when inserting 4k+ string (vinpel, 243083df)
- Bug #17797: Fix for `activeListInput` options (alex-code)
- Bug #17798: Avoid creating directory for stream log targets in `FileTarget` (wapmorgan)
- Bug #17828: Fix `yii\web\UploadedFile::saveAs()` failing when error value in `$_FILES` entry is a string (haveyaseen)
- Bug #17829: `yii\helpers\ArrayHelper::filter` now correctly filters data when passing a filter with more than 2 levels (rhertogh)
- Bug #17843: Fix `yii\web\Session::setCookieParamsInternal` checked "samesite" parameter incorrectly (schevgeny)
- Bug #17850: Update to `ReplaceArrayValue` config exception message (alex-code)
- Bug #17859: Fix loading fixtures under Windows (samdark)
- Bug #17863: `\yii\helpers\BaseInflector::slug()` doesn't work with an empty string as a replacement argument (haruatari)
- Bug #17875: Use `move_uploaded_file()` function instead of `copy()` and `unlink()` for saving uploaded files in case of POST request (sup-ham)
- Bug #17878: Detect CORS AJAX requests without `X-Requested-With` in `Request::getIsAjax()` (dicrtarasov, samdark)
- Bug #17881: `yii\db\Query::queryScalar()` wasn’t reverting the `select`, `orderBy`, `limit`, and `offset` params if an exception occurred (brandonkelly)
- Bug #17884: Fix 0 values in console Table rendered as empty string (mikehaertl)
- Bug #17886: Fix `yii\rest\Serializer` to serialize arrays (patacca)
- Bug #17909: Reset DB schema, transaction, and driver name when the connection is closed (brandonkelly)
- Bug #17920: Fix quoting for `Command::getRawSql` having `Expression` in params (alex-code)
- Enh #7622: Allow `yii\data\ArrayDataProvider` to control the sort flags for `sortModels` through `yii\data\Sort::sortFlags` property (askobara)
- Enh #16721: Use `Instance::ensure()` to initialize `UrlManager::$cache` (rob006)
- Enh #17827: Add `StringValidator::$strict` that can be turned off to allow any scalars (adhayward, samdark)
- Enh #17929: Actions can now have bool typed params bound (alex-code)


2.0.32 January 21, 2020
-----------------------

- Bug #12539: `yii\filters\ContentNegotiator` now generates 406 'Not Acceptable' instead of 415 'Unsupported Media Type' on content-type negotiation fail (PowerGamer1)
- Bug #17037: Fix uploaded file saving method when data came from `MultipartFormDataParser` (sup-ham)
- Bug #17300: Fix class-level event handling with wildcards (Toma91)
- Bug #17635: Fix varbinary data handling for MSSQL (toatall)
- Bug #17744: Fix a bug with setting incorrect `defaultValue` to AR column with `CURRENT_TIMESTAMP(x)` as default expression (MySQL >= 5.6.4) (bizley)
- Bug #17749: Fix logger dispatcher behavior when target crashes in PHP 7.0+ (kamarton)
- Bug #17755: Fix a bug for web request with `trustedHosts` set to format `['10.0.0.1' => ['X-Forwarded-For']]` (shushenghong)
- Bug #17760: Fix `JSON::encode()` for `\DateTimeInterface` under PHP 7.4 (samdark)
- Bug #17762: PHP 7.4: Remove special condition for converting PHP errors to exceptions if they occurred inside of `__toString()` call (rob006)
- Bug #17766: Remove previous PJAX event binding before registering new one (samdark)
- Bug #17767: Make `Formatter::formatNumber` method protected (TheCodeholic)
- Bug #17771: migrate/fresh was not returning exit code (samdark)
- Bug #17793: Fix inconsistent handling of null `data` attribute values in `yii\helpers\BaseHtml::renderTagAttributes()` (brandonkelly)
- Bug #17803: Fix `ErrorHandler` unregister and register to only change global state when applicable (SamMousa)
- Enh #17729: Path alias support was added to `yii\web\UploadedFile::saveAs()` (sup-ham)
- Enh #17792: Add support for `aria` attributes to `yii\helpers\BaseHtml::renderTagAttributes()` (brandonkelly)

2.0.31 December 18, 2019
------------------------

- Bug #17661: Fix query builder incorrect IN/NOT IN condition handling for null values (strychu)
- Bug #17685: Fix invalid db component in `m180523_151638_rbac_updates_indexes_without_prefix` (rvkulikov)
- Bug #17687: `Query::indexBy` can now include a table alias (brandonkelly)
- Bug #17694: Fixed Error Handler to clear registered view tags, scripts, and files when rendering error view through action view (bizley)
- Bug #17701: Throw `BadRequestHttpException` when request params can’t be bound to `int` and `float` controller action arguments (brandonkelly)
- Bug #17710: Fix MemCache duration normalization to avoid memcached/system timestamp mismatch (samdark)
- Bug #17723: Fix `Model::activeAttributes()` to access array offset on value of non-string (samdark)
- Bug #17723: Fix incorrect decoding of default binary value for PostgreSQL (samdark)
- Bug #17723: Fix incorrect type-casting of reflection type to string (samdark)
- Bug #17725: Ensure we do not use external polyfills for pbkdf2() as these may be implemented incorrectly (samdark)
- Bug #17740: `yii\helpers\BaseInflector::slug()` doesn't replace multiple replacement string occurrences to single one (batyrmastyr)
- Bug #17745: Fix PostgreSQL query builder drops default value when it is empty (xepozz)
- Enh #17665: Implement RFC 7239 `Forwarded` header parsing in Request (mikk150, kamarton)
- Enh #17720: DI 3 support for application core components and default object configurations (sup-ham)


2.0.30 November 19, 2019
------------------------

- Bug #17434: IE Ajax redirect fix for non 11.0 versions (kamarton)
- Bug #17632: Unicode file name was not correctly parsed in multipart forms (AlexRas007, samdark)
- Bug #17648: Handle empty column arrays in console `Table` widget (alex-code)
- Bug #17657: Fix migration errors from missing `$schema` in RBAC init file when using MSSQL (PoohOka)
- Bug #17670: Fix overriding core component class using `__class` (sup-ham, samdark)


2.0.29 October 22, 2019
-----------------------

- Bug #8225: Fixed AJAX validation with checkboxList was only triggered on first select (execut)
- Bug #17597: PostgreSQL 12 and partitioned tables support (batyrmastyr)
- Bug #17602: `EmailValidator` with `checkDNS=true` throws `ErrorException` on bad domains on Alpine (batyrmastyr)
- Bug #17606: Fix error in `AssetBundle` when a disabled bundle with custom init() was still published (onmotion)
- Bug #17625: Fix boolean `data` attributes from subkeys rendering in `Html::renderTagAttributes()` (brandonkelly)
- Enh #17607: Added Yii version 3 DI config compatibility (hiqsol)


2.0.28 October 08, 2019
-----------------------

- Bug #17573: `Request::getUserIP()` security fix for the case when `Request::$trustedHost` and `Request::$ipHeaders` are used (kamarton)
- Bug #17585: Fix `yii\i18n\Formatter` including the `@calendar` locale param in `Yii::t()` calls (brandonkelly)
- Bug #17853: Fix errors in ActiveField to be properly caught when PHP 7 is used (My6UoT9)


2.0.27 September 18, 2019
-------------------------

- Bug #16610: ErrorException trace was cut when using XDebug (Izumi-kun)
- Bug #16671: Logging in `Connection::open()` was not respecting `Connection::$enableLogging` (samdark)
- Bug #16855: Ignore console commands that have no actions (alexeevdv)
- Bug #17434: Fix regular expression illegal character; Repeated fix for Internet Explorer 11 AJAX redirect bug in case of 301 and 302 response codes (`XMLHttpRequest: Network Error 0x800c0008`) (kamarton)
- Bug #17539: Fixed error when using `batch()` with `indexBy()` with MSSQL (alexkart)
- Bug #17549: Fix `yii\db\ExpressionInterface` not supported in `yii\db\conditions\SimpleConditionBuilder` (razvanphp)
- Enh #15526: Show valid aliases and options on invalid input in console application (samdark)
- Enh #16826: `appendTimestamp` support was added to `View` methods `registerCssFile()` and `registerJsFile()` (onmotion)


2.0.26 September 03, 2019
-------------------------

- Bug #16305: Fix `FileValidator` mime-type validation failure because of case sensitivity (kamarton)
- Bug #16531: Fix error in `Response::sendContent()` when `set_time_limit()` is disabled (brandonkelly)
- Bug #17355: Fix incorrect sequence of `EVENT_AFTER_REQUEST` when using Pjax (kamarton)
- Bug #17434: Fix Internet Explorer 11 AJAX redirect bug in case of 301 and 302 response codes (`XMLHttpRequest: Network Error 0x800c0008`) (kamarton)
- Bug #17449: Ensure `CHECK` statement goes after `COMMENT` in MySQL `QueryBuilder::addCommentOnColumn()` (Manu311)
- Bug #17504: Fix upsert when `$updateColumns` is `true` but there are no columns to update in the table (alexkart)
- Bug #17507: Fix regular expression escaping and simplify condition in `Controller::createAction()` (kamarton)
- Bug #17511: Fix IPv6 subnets matching in `IpHelper::inRange()` (kamarton)
- Bug #17522: `DbManager::isEmptyUserId()` is now protected (samdark)


2.0.25 August 13, 2019
----------------------

- Bug #15779: If directory path is passed to `FileHelper::unlink()` and directory has files it will not delete files in this directory on Windows now (alexkart)
- Bug #17223: Fixed detaching a behavior event when it is a Closure instance (GHopperMSK, rob006)
- Bug #17473: Fixed `SimpleConditionBuilder::build()` when column is not a string (alexkart)
- Bug #17485: Reverted #17477 (samdark)
- Bug #17486: Fixed error when using `batch()` without `$db` parameter with MSSQL (alexkart)


2.0.24 July 30, 2019
--------------------

- Bug #10020: Fixed quoting of column names with dots in MSSQL (alexkart)
- Bug #16796: Fixed addition and removal of table and column comments in MSSQL (sdlins)
- Bug #17219: Fixed quoting of table names with spaces in MSSQL (alexkart)
- Bug #17424: Subdomain support for `User::loginRequired` (alex-code)
- Bug #17437: Fixed generating namespaced migrations (bizley)
- Bug #17449: Fixed order of SQL column build syntax for MySQL migration (choken)
- Bug #17457: Fixed `phpTypecast()` for MSSQL (alexkart)
- Bug #17469: Fixed updating `Yii` logger instance when setting new logger via configuration (samdark)


2.0.23 July 16, 2019
--------------------

- Bug #10023: Fixed MSSQL "There are no more rows in the active result set" exception when using `each()` and `batch()` (alexkart)
- Bug #17395: Fixed issues with actions that contain underscores in their names (alexkart)
- Bug #17413, #17418, #17426, #17431: Fixed MSSQL tests (alexkart)
- Bug #17420: Fixed loading of column default values for MSSQL (alexkart)
- Bug #17435: Fixed `i18n_init` migration for MSSQL (alexkart)


2.0.22 July 02, 2019
--------------------

- Bug #16394: Fixed issues in `migrate/create` when specifying default values with colons and adding multiple columns (alexkart)
- Bug #17057: Fixed issues with table names that contain special characters or keywords in MSSQL (alexkart)
- Bug #17325: Fixed "Cannot drop view" for MySQL while `migrate/fresh` (alexkart)
- Bug #17341: Re-added fix for error from yii.activeForm.js in strict mode (mikehaertl)
- Bug #17384: Fixed SQL error when passing `DISTINCT ON` queries (brandonkelly)
- Bug #17389: Fixed `UniqueValidator` to work with Active Record having `joinWith()` in its `find()` (garthpmurray)
- Enh #17382: Added `\yii\validators\DateValidator::$strictDateFormat` to enable strict validation (alexkart)
- Enh #17396: Added 'invoked by controller' to the debug log message when `\yii\base\Action` is used (alexkart)


2.0.21 June 18, 2019
--------------------

- Bug #16565: Added missing parts of the context message in `\yii\log\Target::collect` (alexkart)
- Bug #17070: Striped invalid character from fallback file name in `Content-Disposition` header when using `\yii\web\Response::sendFile` (alexkart)
- Bug #17332: Trigger 'change' for checkboxes in GridView (andrii-borysov-me)
- Bug #17341: Fixed error from yii.activeForm.js in strict mode (mikehaertl)
- Bug #17341: Allowed callable objects to be set to `\yii\filters\AccessRule::$roleParams` (alexkart)
- Bug #17356: MSSQL Schema was not detecting string field size (ricarnevale, sdlins)
- Enh #17344: Improved performance of `yii\db\Connection::addSelect()` (brandonkelly)
- Enh #17345: Improved performance of `yii\db\Connection::quoteColumnName()` (brandonkelly)
- Enh #17348: Improved performance of `yii\db\Connection::quoteTableName()` (brandonkelly)
- Enh #17353: Added `sameSite` support for `yii\web\Cookie` and `yii\web\Session::cookieParams` (rhertogh)


2.0.20 June 04, 2019
--------------------

- Bug #16509: Fixed console command help text wordwrap for multi-byte strings (alexkart)
- Bug #17299: Fixed adding of input error class in `\yii\widgets\ActiveField::widget` (alexkart)
- Bug #17328: Added mime aliases for BMP and SVG files (cmoeke)
- Bug #17336: Fixed wildcard matching in Event::hasHandlers() (samdark)
- Bug #12080: Fixed afterValidate triggering when any validation occurs (czzplnm)


2.0.19 May 21, 2019
-------------------

- Bug #12077, #12135, #17263: Fixed PostgreSQL version of `alterColumn()` to accept properly `ColumnSchemaBuilder` definition of column (bizley)
- Bug #16918: Console Table widget variables visibility was changed to protected to allow extending it (samdark)
- Bug #17233: Fixed bug with integer model attribute names in Validator class (nadar)
- Bug #17306: Added ".mjs" extensions to mimetypes meta (samdark)
- Bug #17313: Support jQuery 3.4 (samdark)


2.0.18 April 23, 2019
---------------------

- Bug #16589: Fixed not using `defaultValue` in `BlameableBehavior` for console app (evil1)
- Bug #16820: `yii\filters\Cors::prepareHeaders()` now accepts Access-Control-Allow-Headers in preflight response (georgezim85)
- Bug #17220: Fixed error when using non-InputWidget in active form field (s1lver)
- Bug #17235: `yii\helpers\FileHelper::normalizePath()` now accepts stream wrappers (razvanphp)
- Bug #17268: Fixed Formatter didn't take power into account (samdark)


2.0.17 March 22, 2019
---------------------

- Bug #9438, #13740, #15037: Handle DB session callback custom fields before session closed (lubosdz)
- Bug #16158: Fix multiple select validation was trigged on other fields blur event (GHopperMSK)
- Bug #16335: Fixed in `yii\filters\AccessRule::matchIP()` user IP validation with netmask in rule (omentes)
- Bug #16681: `ActiveField::inputOptions` were not used during some widgets rendering (GHopperMSK)
- Bug #17083: Fixed `yii\validators\EmailValidator::$checkDNS` tells that every domain is correct on alpine linux (mikk150)
- Bug #17124: Fixed ErrorException when run `./yii fixture/unload` without arguments (ricpelo)
- Bug #17127: `yii\db\ActiveRecord::findOne()` now accepts table aliases (albertborsos)
- Bug #17133: Fixed aliases rendering during help generation for a console command (GHopperMSK)
- Bug #17152: Fixed error page when using traceline option (asamats)
- Bug #17156: Fixes PHP 7.2 warning when a data provider has no data as a parameter for a GridView (evilito)
- Bug #17180: Do not populate `yii\web\Response::$response` when response code is 204 (mikk150)
- Bug #17185: Fixed `AssetManager` timestamp appending when a file is published manually (GHopperMSK)
- Bug #17215: Improved security for servers running PHP 7.0.0+ (brandonkelly)


2.0.16.1 February 28, 2019
--------------------------

- Bug #17089: Fixed caching of related records when `via()` using with callable (rugabarbo)
- Bug #17094: Fixed response on 204 status. Now it is empty (GHopperMSK)
- Bug #17098: Fixed message/extract when using message params returned from method calls (rugabarbo)
- Bug #17150: Fixed `yii\helpers\BaseInflector::camel2words()` splitting `ALLCAPS` words on each letter (brandonkelly)
- Bug #17093: Fixed regression in `DataProvider::totalCount` (samdark)


2.0.16 January 30, 2019
-----------------------

- Bug #5341: HasMany via two relations (shirase, cebe)
- Bug #10843: Additional hidden input rendered by `yii\helpers\BaseHtml` methods inherits `disabled` HTML option if provided and set to `true` (bizley)
- Bug #11960: Fixed `checked` option ignore in `yii\helpers\BaseHtml::checkbox()` (misantron)
- Bug #13932: Fix number validator attributes comparison (uaoleg, s1lver)
- Bug #13977: Skip validation if file input does not exist (RobinKamps, s1lver)
- Bug #14039, #16636: Fixed validation for disabled inputs (s1lver, omzy83)
- Bug #14230: Fixed `itemsOptions` ignored in `checkBoxList` and `radioList` (s1lver)
- Bug #14230: Fixed `itemsOptions` ignored in `checkBoxList` (s1lver)
- Bug #14368: Added `role` attribute for active radio list (s1lver)
- Bug #14636: Views can now use relative paths even when using themed views (sammousa)
- Bug #14660: Fixed `yii\caching\DbCache` concurrency issue when set values with the same key (rugabarbo)
- Bug #14759: Fixed `yii\web\JsonResponseFormatter` output for `null` data (misantron)
- Bug #14901: Fixed trim validation for radio/checkbox button (s1lver)
- Bug #14950: Fixed `yii\i18n\Formatter` methods `asInteger`, `asDecimal`, `asPercent`, and `asCurrency` outputs for very big numbers (bizley)
- Bug #15117: Fixed `yii\db\Schema::getTableMetadata` cache refreshing (boboldehampsink)
- Bug #15167: Fixed loading of default value `current_timestamp()` for MariaDB >= 10.2.3 (rugabarbo, bloodrain777, Skinka)
- Bug #15204: `yii\helpers\BaseInflector::slug()` is not removing substrings matching provided replacement from given string anymore (bizley)
- Bug #15286: Fixed incorrect formatting of time with timezone information (rugabarbo)
- Bug #15482: AR::find()->with() missing data when using string identifiers for relations (rugabarbo)
- Bug #15528: Fix timestamp formatting to always use decimal notation in `yii\log\Target::getTime()` (rob006)
- Bug #15548: Fixed index names collision in RBAC (gonimar)
- Bug #15683: Fixed file as array uploading in MultipartFormDataParser (Groonya)
- Bug #15791: Added a warning when the form names conflict (s1lver, rustamwin)
- Bug #15798: Fixed render `yii\grid\RadioButtonColumn::$content` and `yii\grid\CheckboxColumn::$content` (lesha724)
- Bug #15802: Fixed exception class in yii\di\Container (vuchastyi, developeruz)
- Bug #15826: Fixed JavaScript compareValidator in `yii.validation.js` for attributes not in rules (mgrechanik)
- Bug #15850: check basePath is writable on publish in AssetManager (Groonya)
- Bug #15875: afterSave for new models flushes unsaved data (shirase)
- Bug #15876: `yii\db\ActiveQuery::viaTable()` now throws `InvalidConfigException`, if query is not prepared correctly (silverfire)
- Bug #15889: Fixed override `yii\helpers\Html::setActivePlaceholder` (lesha724)
- Bug #15931: `yii\db\ActiveRecord::findOne()` now accepts quoted table and column names using curly and square braces respectively (silverfire)
- Bug #15988: Fixed bash completion (alekciy)
- Bug #16006: Handle case when `X-Forwarded-Host` header have multiple hosts separated with a comma (pgaultier)
- Bug #16010: Fixed `yii\filters\ContentNegotiator` behavior when GET parameters contain an array (rugabarbo)
- Bug #16022: Fix UniqueValidator for PostgreSQL. Checks the uniqueness of keys in `jsonb` field (lav45)
- Bug #16028: Fix serialization of complex cache keys that contain non-UTF sequences (rugabarbo)
- Bug #16039: Fixed implicit conversion from `char` to `varbinnary` in MSSQL (vsivsivsi)
- Bug #16068: Fixed `yii\web\CookieCollection::has` when an expiration param is set to 'until the browser is closed' (OndrejVasicek)
- Bug #16073: Fixed regression in Oracle `IN` condition builder for more than 1000 items (cebe)
- Bug #16081: Fixed composite IN using just one column (rugabarbo)
- Bug #16091: Make `yii\test\InitDbFixture` work with non-SQL DBMS (cebe)
- Bug #16101: Fixed Error Handler to clear registered meta tags, link tags, css/js scripts and files in error view (bizley)
- Bug #16104: Fixed `yii\db\pgsql\QueryBuilder::dropIndex()` to prepend index name with schema name (wapmorgan)
- Bug #16120: FileCache: rebuild cache file before touch when different file owner (Slamdunk)
- Bug #16183: Fixed when `yii\helpers\BaseFileHelper` sometimes returned wrong value (samdark, SilverFire, OndrejVasicek)
- Bug #16184: Fixed `yii\base\Widget` to access `stack` property with `self` instead of `static` (yanggs07)
- Bug #16193: Fixed `yii\filters\Cors` to not reflect origin header value when configured to wildcard origins (Jianjun Chen)
- Bug #16217: Fixed `yii\console\controllers\HelpController` to work well in Windows environment (samdark)
- Bug #16245: Fixed `__isset()` in `BaseActiveRecord` not catching errors (sammousa)
- Bug #16252: Fixed `yii\base\DynamicModel` for checking exist property (vuongxuongminh)
- Bug #16253: Fixed empty checkboxlist validation (GHopperMSK)
- Bug #16266: Fixed `yii\helpers\BaseStringHelper` where explode would not allow 0 as trim string (Thoulah)
- Bug #16277: Fixed `yii\db\Query::from()` to respect `yii\db\ExpressionInterface` (noname007)
- Bug #16278: Fixed drop existing views when console `migrate/fresh` command runs (developeruz)
- Bug #16280: Fixed `yii\base\Model::getActiveValidators()` to return correct validators for attribute on scenario (paweljankowiak06)
- Bug #16292: Fixed misconfigured CORS filter exception throwing. Now it throws `InvalidConfigException` in Debug mode (khvalov)
- Bug #16301: Fixed `yii\web\User::setIdentity()` to clear access check cache while setting identity object to `null` (Izumi-kun)
- Bug #16322: Fixed strings were not were not compared using timing attack resistant approach while CSRF token validation (samdark, Felix Wiedemann)
- Bug #16331: Fixed console table without headers (rhertogh)
- Bug #16377: Fixed `yii\base\Event:off()` undefined index error when event handler does not match (razvanphp)
- Bug #16424: `yii\db\Transaction::begin()` throws now `NotSupportedException` for nested transaction and DBMS not supporting savepoints (bizley)
- Bug #16425: Check for additional values for disabled confirm dialog (Alex-Code, s1lver)
- Bug #16469: Allow cache to be specified as interface and to be configured in DI container (alexeevdv)
- Bug #16490: Fix schema on rbac init (marcelodeandrade)
- Bug #16514: Fixed `yii\di\Container::resolveCallableDependencies` to support callable object (wi1dcard)
- Bug #16527: Fixed return content for `\yii\widgets\ActiveForm::run()` (carono)
- Bug #16552: Added check in `yii\db\ActiveQuery::prepare()` to prevent populating already populated relation when another relation is requested with `via` (drlibra)
- Bug #16558: Added cloning `yii\data\ActiveDataProvider::query` property when ActiveDataProvider object is cloned (mgrechanik)
- Bug #16580: Delete unused php message files in MessageController if `$removeUnused` option is on (Groonya)
- Bug #16648: Html::strtolower() was corrupting UTF-8 strings (Kolyunya)
- Bug #16657: Ensure widgets after run event result contains the result of the rendered widget (AdeAttwood)
- Bug #16666: Fixed `yii\helpers\ArrayHelper::merge` (rustamwin)
- Bug #16680: Fixed ActiveField 'text' input with maxlength (s1lver)
- Bug #16687: Add missing translations for `nl-NL` durations used in `yii\i18n\Formatter::asDuration()` (alexeevdv)
- Bug #16716: The ability to filter by pressing the Enter key when the option `$filterOnFocusOut` off (s1lver)
- Bug #16748: Fixed params when normalize data (marcelodeandrade)
- Bug #16752: Fix rotating files under Windows (samdark, nadirvishun)
- Bug #16766: `yii\filters\ContentNegotiator` was not setting `Vary` header to inform cache recipients (koteq, cebe, samdark)
- Bug #16822: Create config dir recursively in message/config (Groonya)
- Bug #16828: `yii\console\controllers\MessageController::translator` recognized object' methods and functions calls as identical sets of tokens (erickskrauch)
- Bug #16836: Fix `yii\mutex\MysqlMutex` to handle locks with names longer than 64 characters (rob006)
- Bug #16838: `yii\mutex\Mutex::acquire()` no longer returns `true` if lock is already acquired by the same component in the same process (rob006)
- Bug #16858: Allow `\yii\console\widgets\Table` to render empty table when headers provided but no columns (damiandziaduch)
- Bug #16891: Fixed Pagination::totalCount initialized incorrectly (taobig)
- Bug #16897: Fixed `yii\db\sqlite\Schema` missing primary key constraint detection in case of `INTEGER PRIMARY KEY` (bizley)
- Bug #16903: Fixed 'yii\validators\NumberValidator' method 'isNotNumber' returns false for true/false value (annechko)
- Bug #16910: Fix messages sorting on extract (Groonya)
- Bug #16945: Fixed RBAC DbManager ruleName fetching on the case of PDO::ATTR_ORACLE_NULLS => PDO::NULL_TO_STRING (razonyang)
- Bug #16959: Fixed typo in if condition inside `yii\web\DbSession::typecastFields()` that caused problems with session overwriting (silverfire)
- Bug #16966: Fix ArrayExpression support in related tables (GHopperMSK)
- Bug #16969: Fix `yii\filters\PageCache` incorrectly storing empty data in some cases (sammousa)
- Bug #16974: Regular Expression Validator to include support for 'u' (UTF-8) modifier (Dzhuneyt)
- Bug #16991: Removed usage of `utf8_encode()` from `Request::resolvePathInfo()` (GHopperMSK)
- Bug #17021: Fix to do not remove existing message category files in a subfolder (albertborsos)
- Bug: Fixed bad instanceof check in `yii\db\Schema::getTableMetadata()` (samdark)
- Bug: (CVE-2018-14578): Fixed CSRF token check bypassing in `\yii\web\Request::getMethod()` (silverfire)
- Bug: (CVE-2018-19454): Fixed excess logging of sensitive information in `\yii\log\Target` (silverfire)
- Enh #9133: Added `yii\behaviors\OptimisticLockBehavior` (tunecino)
- Enh #14289: Added `yii\db\Command::executeResetSequence()` to work with Oracle (CedricYii)
- Enh #14367: In `yii\db\mysql\QueryBuilder` added support fractional seconds for time types for MySQL >= 5.6.4 (konstantin-vl)
- Enh #16151: `ActiveQuery::getTableNameAndAlias()` is now protected (s1lver)
- Enh #16151: Change of scope for method `getTableNameAndAlias()` (s1lver)
- Enh #16191: Enhanced `yii\helpers\Inflector` to work correctly with UTF-8 (silverfire)
- Enh #16365: Added $filterOnFocusOut option for GridView (s1lver)
- Enh #16522: Allow jQuery 3.3 (Slamdunk)
- Enh #16603: Added `yii\mutex\FileMutex::$isWindows` for Windows file shares on Unix guest machines (brandonkelly)
- Enh #16839: Increase frequency of lock tries for `yii\mutex\FileMutex::acquireLock()` when $timeout is provided (rob006)
- Enh #16839: Add support for `$timeout` in  `yii\mutex\PgsqlMutex::acquire()` (rob006)
- Enh: `yii\helpers\UnsetArrayValue`, `yii\helpers\ReplaceArrayValue` object now can be restored after serialization using `var_export()` function (silvefire)
- Chg #16192: `yii\db\Command::logQuery()` is now protected, extracted `getCacheKey()` from `queryInternal()` (drlibra)
- Chg #16941: Set `yii\console\controllers\MigrateController::useTablePrefix` to true as default value (GHopperMSK)


2.0.15.1 March 21, 2018
-----------------------

- Bug #15931: `yii\db\ActiveRecord::findOne()` now accepts column names prefixed with table name (cebe)


2.0.15 March 20, 2018
---------------------

- Bug #15688: (CVE-2018-7269): Fixed possible SQL injection through `yii\db\ActiveRecord::findOne()`, `::findAll()` (analitic1983, silverfire, cebe)
- Bug #15878: Fixed migration with a comment containing an apostrophe (MarcoMoreno)


2.0.14.2 March 13, 2018
-----------------------

- Bug #15776: Fixed slow MySQL constraints retrieving (MartijnHols, berosoboy, sergeymakinen)
- Bug #15783: Regenerate CSRF token only when logging in directly (samdark)
- Bug #15792: Added missing `yii\db\QueryBuilder::conditionClasses` setter (silverfire)
- Bug #15801: Fixed `has-error` CSS class assignment in `yii\widgets\ActiveField` when attribute name is prefixed with tabular index (FabrizioCaldarelli)
- Bug #15804: Fixed `null` values handling for PostgresSQL arrays (silverfire)
- Bug #15817: Fixed support of deprecated array format type casting in `yii\db\Command::bindValues()` (silverfire)
- Bug #15822: Fixed `yii\base\Component::off()` not to throw an exception when handler does not exist (silverfire)
- Bug #15829: Fixed JSONB support in PostgreSQL 9.4 (silverfire)
- Bug #15836: Fixed nesting of `yii\db\ArrayExpression`, `yii\db\JsonExpression` (silverfire)
- Bug #15839: Fixed `yii\db\mysql\JsonExpressionBuilder` to cast JSON explicitly (silverfire)
- Bug #15840: Fixed regression on load fixture data file (leandrogehlen)
- Bug #15858: Fixed `Undefined offset` error calling `yii\helpers\Html::errorSummary()` with the same error messages for different model attributes (FabrizioCaldarelli, silverfire)
- Bug #15863: Fixed saving of `null` attribute value for JSON and Array columns in MySQL and PostgreSQL (silverfire)
- Bug: Fixed encoding of empty `yii\db\ArrayExpression` for PostgreSQL (silverfire)
- Bug: Fixed table schema retrieving for PostgreSQL when the table name was wrapped in quotes (silverfire)


2.0.14.1 February 24, 2018
--------------------------

- Bug #15318: Fixed `session_name(): Cannot change session name when session is active` errors (bscheshirwork, samdark)
- Bug #15678: Fixed `resetForm()` method in `yii.activeForm.js` which used an undefined variable (Izumi-kun)
- Bug #15692: Fix `yii\validators\ExistValidator` to respect filter when `targetRelation` is used (developeruz)
- Bug #15693: Fixed `yii\filters\auth\HttpHeaderAuth` to work correctly when pattern is set but was not matched (bboure)
- Bug #15696: Fix magic getter for `yii\db\ActiveRecord` (developeruz)
- Bug #15707: Fixed JSON retrieving from MySQL (silverfire)
- Bug #15708: Fixed `yii\db\Command::upsert()` for Cubrid/MSSQL/Oracle (sergeymakinen)
- Bug #15724: Changed shortcut in `yii\console\controllers\BaseMigrateController` for `comment` option from `-c` to `-C` due to conflict (Izumi-kun)
- Bug #15726: Fix ExistValidator is broken for NOSQL (developeruz)
- Bug #15728, #15731: Fixed BC break in `Query::select()` method (silverfire)
- Bug #15742: Updated `yii\helpers\BaseHtml::setActivePlaceholder()` to be consistent with `activeLabel()` (edwards-sj)
- Enh #15716: Added `disableJsonSupport` to MySQL and PgSQL `ColumnSchema`, `disableArraySupport` and `deserializeArrayColumnToArrayExpression` to PgSQL `ColumnSchema` (silverfire)
- Enh #15716: Implemented `\Traversable` in `yii\db\ArrayExpression` (silverfire)
- Enh #15760: Added `ArrayAccess` support as validated value in `yii\validators\EachValidator` (silverfire)


2.0.14 February 18, 2018
------------------------

- Bug #8983: Only truncate the original log file for rotation (matthewyang, developeruz)
- Bug #9342: Fixed `yii\db\ActiveQueryTrait` to apply `indexBy` after relations population in order to prevent excess queries (sammousa, silverfire)
- Bug #11401: Fixed `yii\web\DbSession` concurrency issues when writing and regenerating IDs (samdark, andreasanta, cebe)
- Bug #13034: Fixed `normalizePath` for windows network shares that start with two backslashes (developeruz)
- Bug #14135: Fixed `yii\web\Request::getBodyParam()` crashes on object type body params (klimov-paul)
- Bug #14157: Add support for loading default value `CURRENT_TIMESTAMP` of MySQL `datetime` field (rossoneri)
- Bug #14276: Fixed I18N format with dotted parameters (developeruz)
- Bug #14296: Fixed log targets to throw exception in case log can not be properly exported (bizley)
- Bug #14484: Fixed `yii\validators\UniqueValidator` for target classes with a default scope (laszlovl, developeruz)
- Bug #14604: Fixed `yii\validators\CompareValidator` `compareAttribute` does not work if `compareAttribute` form ID has been changed (mikk150)
- Bug #14711: (CVE-2018-6010): Fixed `yii\web\ErrorHandler` displaying exception message in non-debug mode (samdark)
- Bug #14811: Fixed `yii\filters\HttpCache` to work with PHP 7.2 (samdark)
- Bug #14859: Fixed OCI DB `defaultSchema` failure when `masterConfig` is used (lovezhl456)
- Bug #14903: Fixed route with extra dashes is executed controller while it should not (developeruz)
- Bug #14916: Fixed `yii\db\Query::each()` iterator key starts from 1 instead of 0 (Vovan-VE)
- Bug #14980: Fix looping in `yii\i18n\MessageFormatter` tokenize pattern if pattern is invalid (uaoleg, developeruz)
- Bug #15031: Fixed incorrect string type length detection for OCI DB schema (Murolike)
- Bug #15046: Throw an `yii\web\HeadersAlreadySentException` if headers were sent before web response (dmirogin)
- Bug #15122: Fixed `yii\db\Command::getRawSql()` to properly replace expressions (hiscaler, samdark)
- Bug #15142: Fixed array params replacing in `yii\helpers\BaseUrl::current()` (IceJOKER)
- Bug #15169: Fixed translating a string when NULL parameter is passed (developeruz)
- Bug #15194: Fixed `yii\db\QueryBuilder::insert()` to preserve passed params when building a `INSERT INTO ... SELECT` query for MSSQL, PostgreSQL and SQLite (sergeymakinen)
- Bug #15229: Fixed `yii\console\widgets\Table` default value for `getScreenWidth()`, when `Console::getScreenSize()` can't determine screen size (webleaf)
- Bug #15234: Fixed `\yii\widgets\LinkPager` removed `tag` from `disabledListItemSubTagOptions` (SDKiller)
- Bug #15249: Controllers in subdirectories were not visible in commands list (IceJOKER)
- Bug #15270: Resolved potential race conditions when writing generated php-files (kalessil)
- Bug #15300: Fixed "Cannot read property 'style' of undefined" error at the error screen (vitorarantes)
- Bug #15301: Fixed `ArrayHelper::filter()` to work properly with `0` in values (hhniao)
- Bug #15302: Fixed `yii\caching\DbCache` so that `getValues` now behaves the same as `getValue` with regards to streams (edwards-sj)
- Bug #15317: Regenerate CSRF token if an empty value is given (sammousa)
- Bug #15320: Fixed special role checks in `yii\filters\AccessRule::matchRole()` (Izumi-kun)
- Bug #15322: Fixed PHP 7.2 compatibility of `FileHelper::getExtensionsByMimeType()` (samdark)
- Bug #15353: Remove side effect of ActiveQuery::getTablesUsedInFrom() introduced in 2.0.13 (terales)
- Bug #15355: Fixed `yii\db\Query::from()` does not work with `yii\db\Expression` (vladis84, silverfire, samdark)
- Bug #15356: Fixed multiple bugs in `yii\db\Query::getTablesUsedInFrom()` (vladis84, samdark)
- Bug #15380: `FormatConverter::convertDateIcuToPhp()` now converts `a` ICU symbols to `A` (brandonkelly)
- Bug #15407: Fixed rendering rows with associative arrays in `yii\console\widgets\Table` (dmrogin)
- Bug #15432: Fixed wrong value being set in `yii\filters\RateLimiter::checkRateLimit()` resulting in wrong `X-Rate-Limit-Reset` header value (bizley)
- Bug #15440: Fixed `yii\behaviors\AttributeTypecastBehavior::$attributeTypes` auto-detection fails for rule, which specify attribute with '!' prefix (klimov-paul)
- Bug #15462: Fixed `accessChecker` configuration error (developeruz)
- Bug #15494: Fixed missing `WWW-Authenticate` header (developeruz)
- Bug #15522: Fixed `yii\db\ActiveRecord::refresh()` method does not use an alias in the condition (vladis84)
- Bug #15523: `yii\web\Session` settings could now be configured after session is started (StalkAlex, rob006, daniel1302, samdark)
- Bug #15536: Fixed `yii\widgets\ActiveForm::init()` for call `parent::init()` (panchenkodv)
- Bug #15540: Fixed `yii\db\ActiveRecord::with()` unable to use relation defined via attached behavior in case `asArray` is enabled (klimov-paul)
- Bug #15553: Fixed `yii\validators\NumberValidator` incorrectly validate resource (developeruz)
- Bug #15621: Fixed `yii\web\User::getIdentity()` returning `null` if an exception had been thrown when it was called previously (brandonkelly)
- Bug #15628: Fixed `yii\validators\DateValidator` to respect time when the `format` property is set to UNIX Epoch format (silverfire, gayHacker)
- Bug #15644: Avoid wrong default selection on a dropdown, checkbox list, and radio list, when a option has a key equals to zero (berosoboy)
- Bug #15658: Fixed `yii\filters\auth\HttpBasicAuth` not to switch identity, when user is already authenticated and identity does not get changed (silverfire)
- Bug #15662: Fixed `yii\log\FileTarget` not to create log directory during init process (alexeevdv)
- Enh #3087: Added `yii\helpers\BaseHtml::error()` "errorSource" option to be able to customize errors display (yanggs07, developeruz, silverfire)
- Enh #3250: Added support for events partial wildcard matching (klimov-paul)
- Enh #5515: Added default value for `yii\behaviors\BlameableBehavior` for cases when the user is guest (dmirogin)
- Enh #6844: `yii\base\ArrayableTrait::toArray()` now allows recursive `$fields` and `$expand` (bboure)
- Enh #7640: Implemented custom data types support. Added JSON support for MySQL and PostgreSQL, array support for PostgreSQL (silverfire, cebe)
- Enh #7988: Added `\yii\helpers\Console::errorSummary()` and `\yii\helpers\Json::errorSummary()` (developeruz)
- Enh #7996: Short syntax for verb in GroupUrlRule (schojniak, developeruz)
- Enh #8092: ExistValidator for relations (developeruz)
- Enh #8527: Added `yii\i18n\Locale` component having `getCurrencySymbol()` method (amarox, samdark)
- Enh #8752: Allow specify `$attributeNames` as a string for `yii\base\Model` `validate()` method (developeruz)
- Enh #9137: Added `Access-Control-Allow-Method` header for the OPTIONS request (developeruz)
- Enh #9253: Allow `variations` to be a string for `yii\filters\PageCache` and `yii\widgets\FragmentCache` (schojniak, developeruz)
- Enh #9771: Assign hidden input with its own set of HTML options via `$hiddenOptions` in activeFileInput `$options` (HanafiAhmat)
- Enh #10186: Use native `hash_equals` in `yii\base\Security::compareString()` if available, throw exception if non-strings are compared (aotd1, samdark)
- Enh #11611: Added `BetweenColumnsCondition` to build SQL condition like `value BETWEEN col1 and col2` (silverfire)
- Enh #12623: Added `yii\helpers\StringHelper::matchWildcard()` replacing usage of `fnmatch()`, which may be unreliable (klimov-paul)
- Enh #13019: Support JSON in SchemaBuilderTrait (zhukovra, undefinedor)
- Enh #13425: Added caching of dynamically added URL rules with `yii\web\UrlManager::addRules()` (scriptcube, silverfire)
- Enh #13465: Added `yii\helpers\FileHelper::findDirectories()` method (ArsSirek, developeruz)
- Enh #13618: Active Record now resets related models after corresponding attributes updates (Kolyunya, rob006)
- Enh #13679: Added `yii\behaviors\CacheableWidgetBehavior` (Kolyunya)
- Enh #13814: MySQL unique index names can now contain spaces (df2)
- Enh #13879: Added upsert support for `yii\db\QueryBuilder`, `yii\db\Command`, and `yii\db\Migration` (sergeymakinen)
- Enh #13919: Added option to add comment for created table to migration console command (mixartemev, developeruz)
- Enh #13996: Added `yii\web\View::registerJsVar()` method that allows registering JavaScript variables (Eseperio, samdark)
- Enh #14043: Added `yii\helpers\IpHelper` (silverfire, cebe)
- Enh #14254: add an option to specify whether validator is forced to always use master DB for `yii\validators\UniqueValidator` and `yii\validators\ExistValidator` (rossoneri, samdark)
- Enh #14355: Added ability to pass an empty array as a parameter in console command (developeruz)
- Enh #14488: Added support for X-Forwarded-Host to `yii\web\Request`, fixed `getServerPort()` usage (si294r, samdark)
- Enh #14538: Added `yii\behaviors\AttributeTypecastBehavior::typecastAfterSave` property (littlefuntik, silverfire)
- Enh #14546: Added `dataDirectory` property into `BaseActiveFixture` (leandrogehlen)
- Enh #14568: Refactored migration templates to use `safeUp()` and `safeDown()` methods (Kolyunya)
- Enh #14638: Added `yii\db\SchemaBuilderTrait::tinyInteger()` (rob006)
- Enh #14643: Added `yii\web\ErrorAction::$layout` property to conveniently set layout from error action config (swods, cebe, samdark)
- Enh #14662: Added support for custom `Content-Type` specification to `yii\web\JsonResponseFormatter` (Kolyunya)
- Enh #14732, #11218, #14810, #10855: It is now possible to pass `yii\db\Query` anywhere, where `yii\db\Expression` was supported (silverfire)
- Enh #14806: Added $placeFooterAfterBody option for GridView (terehru)
- Enh #15024: `yii\web\Pjax` widget does not prevent CSS files from sending anymore because they are handled by client-side plugin correctly (onmotion)
- Enh #15047: `yii\db\Query::select()` and `yii\db\Query::addSelect()` now check for duplicate column names (wapmorgan)
- Enh #15076: Improve `yii\db\QueryBuilder::buildColumns()` to throw exception on invalid input (hiscaler)
- Enh #15120: Refactored dynamic caching introducing `DynamicContentAwareInterface` and `DynamicContentAwareTrait` (sergeymakinen)
- Enh #15135: Automatic completion for help in bash and zsh (Valkeru)
- Enh #15216: Added `yii\web\ErrorHandler::$traceLine` to allow opening file at line clicked in IDE (vladis84)
- Enh #15219: Added `yii\filters\auth\HttpHeaderAuth` (bboure)
- Enh #15221: Added support for specifying `--camelCase` console options in `--kebab-case` (brandonkelly)
- Enh #15221: Added support for the `--<option> <value>` console option syntax (brandonkelly)
- Enh #15221: Improved the `help/list-action-options` console command output for command options without a description (brandonkelly)
- Enh #15226: Auto generate placeholder from fields (vladis84)
- Enh #15272: Removed type attribute from script tag (aleksbelic)
- Enh #15332: Always check for availability of `openssl_pseudo_random_bytes`, even if LibreSSL is available (sammousa)
- Enh #15335: Added `FileHelper::unlink()` that works well under all OSes (samdark)
- Enh #15340: Test CHANGELOG.md for valid format (sammousa)
- Enh #15347: Add `Instance` support for object property in DI container (kojit2009)
- Enh #15357: Added multi statement support for `yii\db\sqlite\Command` (sergeymakinen)
- Enh #15360: Refactored `BaseConsole::updateProgress()` (developeruz)
- Enh #15398: Added `yii\db\Query::cache()` (hubeiwei, silverfire)
- Enh #15415: Added transaction/retry support for `yii\db\Command` (sergeymakinen)
- Enh #15417: Added `yii\validators\FileValidator::$minFiles` (vladis84)
- Enh #15422: Added default roles dynamic definition support via closure for `yii\rbac\BaseManager` (deltacube)
- Enh #15426: Added abilitiy to create and drop database views (igravity, vladis84)
- Enh #15476: Added `\yii\widgets\ActiveForm::$validationStateOn` to be able to specify where to add class for invalid fields (samdark)
- Enh #15496: (CVE-2018-6009): CSRF token is now regenerated on changing identity (samdark, rhertogh)
- Enh #15595: `yii\data\DataFilter` can now handle `lt`,`gt`,`lte` and `gte` on `yii\validators\DateValidator` (mikk150)
- Enh #15661: Added `yii\db\ExpressionInterface` support to `yii\db\Command::batchInsert()` (silverfire)
- Enh: Added check to `yii\base\Model::formName()` to prevent source path disclosure when form is represented by an anonymous class (silverfire)
- Chg #15420: Handle OPTIONS request in `yii\filter\Cors` so the preflight check isn't passed trough authentication filters (michaelarnauts, leandrogehlen)
- Chg #15625: `yii\grid\DataColumn` boolean filter dropdown list values are now in reversed order (bizley)
- Chg #15633: Deprecated `yii\base\BaseObject::className()` in favor of native PHP syntax `::class`, which does not trigger autoloading (brandonkelly)
- Chg #15633: Deprecated XCache and Zend data cache support as caching backends (brandonkelly)
- Chg #15633: Deprecated `yii\BaseYii::powered()` method (brandonkelly)
- Chg #15633: Added `yii\base\InvalidArgumentException` and deprecated `yii\base\InvalidParamException` (brandonkelly)
- Chg #15633: Added `yii\BaseYii::debug()` and deprecated `yii\BaseYii::trace()` (brandonkelly)


2.0.13.1 November 14, 2017
--------------------------

- Bug #15081: Fixed "Undefined offset: 1" in log Target (ischenko)
- Bug #15086: Fixed jQuery onLoad event handling (alexantr)
- Bug #15108: Fixed `yii\db\Schema::getSchemaNames()` for MSSQL and added tests for all DBMSes (sergeymakinen)
- Bug #15117: Fixed DB schema cache did not honor table prefixes (sergeymakinen)


2.0.13 November 03, 2017
------------------------

- Bug #6226: Fix fatal symlink error during assets publishing in multi threaded environment (dynasource)
- Bug #6526: Fixed `yii\db\Command::batchInsert()` casting of double values correctly independent of the locale (cebe, leammas)
- Bug #6588: Fixed changing array keys after validation of multiple files in `yii\validators\FileValidator` (developeruz)
- Bug #7890: Allow `migrate/mark` to mark history at the point of the base migration (cebe)
- Bug #11242: Fixed excess escaping in `yii\db\Command::batchInsert()` (silverfire)
- Bug #11825: User can login by cookie only once when `autoRenewCookie` is set to false (shirase, silverfire)
- Bug #12860: Fixed possible race conditions in `yii\mutex\FileMutex` (kidol)
- Bug #13258: Fixed `yii\mutex\FileMutex::$autoRelease` having no effect due to missing base class initialization (kidol)
- Bug #13436: Fixed migration for MSSQL DbSession (silverfire)
- Bug #13564: Fixed `yii\web\Request::getAuthUser()`, `getAuthPassword()` to respect `HTTP_AUTHORIZATION` request header (silverfire)
- Bug #13720: Improve `yii\helpers\FormatConverter::convertDatePhpToIcu()` to handle escaped chars correctly (rob006)
- Bug #13757: Fixed ambiguous column error in `BaseActiveRecord::refresh()` when the query adds a JOIN by default (cebe, ivankff)
- Bug #13779: Fixed `yii\db\ActiveRecord::joinWith()` unable to use relation defined via attached behavior (ElisDN, klimov-paul)
- Bug #13859: Fixed ambiguous column error in `Query::column()` when `$indexBy` is used with a JOIN (cebe)
- Bug #13969: Fixed a bug in a `yii\console\controllers\CacheController` when caches defined via a closure were not detected (Kolyunya)
- Bug #14016: Fixed empty messages marked as unused in PHP and PO sources when extracted with message command when `markUnused` is `false` (samdark)
- Bug #14129: Fixed console help to properly work with tricky camelcased controller names (samdark, silverfire)
- Bug #14134: Fixed multiple `validateAttribute()` calls when `scenarios()` returns duplicate attributes (krukru)
- Bug #14165: Set `_slave` of `Connection` to `false` instead of `null` in `close` method (rossoneri)
- Bug #14186: Forced validation in `yiiActiveForm` do not trigger `afterValidate` event (arogachev)
- Bug #14192: Fixed wrong default null value for TIMESTAMP when using PostgreSQL (Tigrov)
- Bug #14202: Fixed current time in (UTC) `\Yii::$app->formatter` if time not set (bscheshirwork)
- Bug #14206: `MySqlMutex`, `PgsqlMutex` and `OracleMutex` now use `useMaster()` to ensure lock is aquired on the same DB server (cebe, ryusoft)
- Bug #14248: `yii\console\controllers\MessageController` no longer outputs colorized filenames when console does not support text colorization (PowerGamer1)
- Bug #14264: Fixed a bug where `yii\log\Logger::calculateTimings()` was not accepting messages with array tokens (bizley)
- Bug #14269: Fixed broken error page when calling an undefined method (cebe)
- Bug #14304: Fixed `yii\validators\UniqueValidator` and `yii\validators\ExistValidator` to skip prefixes in case expressions are used (samdark)
- Bug #14307: Fixed PHP warning when `yii\console\UnknownCommandException` is thrown for empty command (rob006)
- Bug #14318: Trigger `yiiActiveForm.events.afterValidateAttribute` after updating attribute  (dmirogin)
- Bug #14334: Fixed `\yii\db\QueryBuilder::buildNotCondition` loses params when operand is `\yii\db\Expression` (Ni-san)
- Bug #14341: Fixed regression in error handling introduced by fixing #14264 (samdark)
- Bug #14370: Fixed creating built-in validator in model with same function name (dmirogin)
- Bug #14406: Fixed caching rules in `yii\web\UrlManager` with different ruleConfig configuration (dmirogin)
- Bug #14423: Fixed `ArrayHelper::merge` behavior with null values for integer-keyed elements (dmirogin)
- Bug #14449: Fix PHP 7.2 compatibility bugs and add explicit closure support in `yii\base\Application` (dynasource)
- Bug #14471: `ContentNegotiator` will always set one of the configured server response formats even if the client does not accept any of them (PowerGamer1)
- Bug #14492: Fixed error handler not escaping error info in debug mode, see CVE-2017-11516 (samdark)
- Bug #14493: Fixed getting permissions in `yii\rbac\Dbmanger::getPermissionsByUser` by user with id equals 0 (dmirogin)
- Bug #14510: The state of a form is always "not validated" when using forced validation in `yiiActiveForm` (arogachev)
- Bug #14523: Added `yii\web\MultipartFormDataParser::$force` option allowing to enforce parsing even on 'POST' request (klimov-paul)
- Bug #14525: Fixed 2.0.12 regression of loading of global fixtures trough `yii fixture/load` (michaelarnauts)
- Bug #14533: Fixed `yii\validators\ExistValidator` and `yii\validators\UniqueValidator` throw exception in case they are set for `yii\db\ActiveRecord` with `$targetClass` pointing to NOSQL ActiveRecord (klimov-paul)
- Bug #14542: Ensured only ASCII characters are in CSRF cookie value since binary data causes issues with ModSecurity and some browsers (samdark)
- Bug #14543: Throw exception when trying to create migration longer than 180 symbols (dmirogin, cebe)
- Bug #14596: Fix event call on init in `yii\widgets\BaseListView` (panchenkodv)
- Bug #14697: Fixed `console\widgets\Table` rendering when there's no data supplied (bscheshirwork)
- Bug #14723: Fixed serialization of `yii\db\Connection` instance closes database connection (klimov-paul)
- Bug #14773: Fixed `yii\widgets\ActiveField::$options` does not support 'class' option in array format (klimov-paul)
- Bug #14902: Fixed PHP notice in `yii\web\MultipartFormDataParser` (olimsaidov)
- Bug #14921: Fixed bug with replacing numeric keys in `yii\helpers\Url::current()` (rob006)
- Enh #4479: Implemented REST filters (klimov-paul)
- Enh #4495:  Added closure support in `yii\i18n\Formatter` (developeruz)
- Enh #5786: Allowed to use custom constructors in ActiveRecord-based classes (ElisDN, klimov-paul)
- Enh #6644: Added `yii\helpers\ArrayHelper::setValue()` (LAV45)
- Enh #7823: Added `yii\filters\AjaxFilter` filter (dmirogin)
- Enh #9438: `yii\web\DbSession` now relies on error handler to display errors (samdark)
- Enh #9703, #9709: Added `yii\i18n\Formatter::asWeight()` and `::asLength()` formatters (nineinchnick, silverfire)
- Enh #11415: Added `yii\console\widgets\Table` to draw tables in console apps (pana1990, rob006, samdark, tonykor)
- Enh #13254: Made `yii\helpers\StringHelper` and `yii\validators\StringValidator` independent of `Yii::$app` instance (cebe)
- Enh #13378: Added `yii\behaviors\SluggableBehaviour::skipOnEmpty` option (andrewnester)
- Enh #13403: Added 'permissions' additionally to 'roles' in `yii\filters\AccessRule` in order to be able to specify these separately (thyseus)
- Enh #13486: Use DI container to instantiate cookies in order to be able to set defaults (samdark)
- Enh #13586: Added `$preserveNonEmptyValues` property to the `yii\behaviors\AttributeBehavior` (Kolyunya)
- Enh #13780: Added support for trusted proxies in `yii\web\Request` (sammousa, cebe, silverfire)
- Enh #13787: Added `yii\db\Migration::$maxSqlOutputLength` that allows limiting number of characters for outputting SQL (thiagotalma)
- Enh #13824: Support extracting concatenated strings in `yii message` (developeruz)
- Enh #13835: Added `yii\web\Request::getOrigin()` method that returns `HTTP_ORIGIN` of current CORS request (yyxx9988)
- Enh #13853: Added `yii\db\Migration::$compact` as well as `yii\console\controllers\BaseMigrateController::$compact` to allow making the migration console output more compact (francislavoie)
- Enh #14022: `yii\web\UrlManager::setBaseUrl()` now supports aliases (dmirogin)
- Enh #14061: Added request scope assignments cache to `yii\rbac\DbManager::checkAccess()` to avoid duplicate queries for user assignments (leandrogehlen, cebe, nineinchnick, ryusoft)
- Enh #14081: Added `yii\caching\CacheInterface` to make custom cache extensions adoption easier (silverfire)
- Enh #14087: Added `yii\web\View::registerCsrfMetaTags()` method that registers CSRF tags dynamically ensuring that caching doesn't interfere (RobinKamps)
- Enh #14089: Added tests for `yii\base\Theme` (vladis84)
- Enh #14105: Implemented a solution for retrieving DBMS constraints in `yii\db\Schema` (sergeymakinen)
- Enh #14126: Added variadic parameters support to DI container (SamMousa)
- Enh #14151: Added `yii\behaviors\AttributesBehavior` that assigns values specified to one or multiple attributes of an AR object when certain events happen (bscheshirwork)
- Enh #14184: Module service locator now falls back to its parent module service locator in case component isn't found (SamMousa)
- Enh #14188: Add constants and function for sysexits(3) to `ConsoleHelper` (tom--, samdark, cebe)
- Enh #14273: `yii\log\Target::$enabled` now supports callable value (dmirogin)
- Enh #14294: Added `InputWidget::renderInput()` to move behavior described in `InputWidget` class docs to the class itself (cebe)
- Enh #14298: The default response formatter configs defined by `yii\web\Response::defaultFormatters()` now use the array syntax (brandonkelly)
- Enh #14363: Added `yii\widgets\LinkPager::$linkContainerOptions` and possibility to override tag in `yii\widgets\LinkPager::$options` (dmirogin)
- Enh #14389: Optimize `Validator::validateAttributes()` by calling `attributeNames()` only once (nicdnep)
- Enh #14417: Added configuration for headers in PHP files generated by `message/extract` command (rob006)
- Enh #14431: Moved `ActiveQuery::getTablesUsedInFrom()` to `Query` to make the functionality available on the lower layer (cebe)
- Enh #14620: Updated `yii.activeForm.js` and `yii\web\View` to jQuery 3.0 compatible API (silverfire)
- Enh #14633: Add miliseconds to log time in `\yii\log\Target` (Ni-san)
- Enh #14664: Add migrate/fresh command to truncate database and apply migrations again (thyseus)
- Enh #14765: RBAC: add index on `user_id` column in `auth_assignment` table for performance reasons (bicf)
- Enh #14864: Ability to use dependencies in constructor of migrations (vtvz)
- Enh #14877: Disabled profiling on connection opening when profiling is disabled (njasm)
- Enh #14913: Assset hashing now takes asset linking into account to improve cache busting (schmunk42)
- Enh #14929: Ensure trailing `;` on combining files with `asset` command to fix compiler failures (tanakahisateru)
- Enh #14958: Added options to copy stacktrace and search for error message to the exception page (cebe)
- Enh #14967: Added Armenian Translations (gevorgmansuryan)
- Enh #15015: Added `StringHelper::floatToString()` to safely cast float values independent of the locale, also fixes some places in the framework that use it now (cebe)
- Chg #7936: Deprecate `yii\base\Object` in favor of `yii\base\BaseObject` for compatibility with PHP 7.2 (rob006, cebe, klimov-paul)
- Chg #14201: `yii\console\controllers\MessageController::extractMessagesFromTokens()` is now protected (faenir)
- Chg #14286: Used primary inputmask package name instead of an alias (samdark)
- Chg #14321: `yii\widgets\MaskedInput` is now registering its JavaScript `clientOptions` initialization code in head section (DaveFerger)
- Chg #14487: Changed i18n message error to warning (dmirogin)


2.0.12 June 05, 2017
--------------------

- Bug #4408: Add support for unicode word characters and `+` character in attribute names (sammousa, kmindi)
- Bug #5442: Fixed problem on load fixture dependencies with database related tests (leandrogehlen)
- Bug #7946: Fixed a bug when the `form` attribute was not propagated to the hidden input of the checkbox (Kolyunya)
- Bug #8120: Fixes LIKE special characters escaping for Cubrid/MSSQL/Oracle/SQLite in `yii\db\QueryBuilder` (sergeymakinen)
- Bug #9669: AssetManager and `FileHelper::copyDirectory()` were copying empty directories when using `only` or `except` options. Added an option to disable this (cebe)
- Bug #10305: Oracle SQL queries with `IN` condition and more than 1000 parameters are working now (silverfire)
- Bug #10346: Fixed "DOMException: Invalid Character Error" in `yii\web\XmlResponseFormatter::buildXml()` (sasha-ch)
- Bug #10372: Fixed console controller including complex typed arguments in help (sammousa)
- Bug #11230: Include `defaultRoles` in `yii\rbac\DbManager->getRolesByUser()` results (developeruz)
- Bug #11404: `yii\base\Model::loadMultiple()` returns true even if `yii\base\Model::load()` returns false (zvook)
- Bug #11719: Fixed `yii\db\Connection::$enableQueryCache` caused infinite loop when the same connection was used for `yii\caching\DbCache` (michaelarnauts)
- Bug #12715: Exception `SAVEPOINT LEVEL1 does not exist` instead of deadlock exception (Vovan-VE)
- Bug #13058: Fixed caught exception thrown during view file rendering produces wrong output (klimov-paul)
- Bug #13086, #13656: Fixed bug with optional parameters at the beginning of pattern in `yii\web\UrlRule` (rob006)
- Bug #13087: Fixed getting active validators for safe attribute (developeruz, klimov-paul)
- Bug #13306: Wildcard in `reloadableScripts` in `yii.js` allows 0 characters (arogachev)
- Bug #13340: Fixed `yii\db\Connection::useMaster()` - exception within callback completely disables slaves (Vovan-VE)
- Bug #13343: Fixed `yii\i18n\Formatter::asTime()` to process time-only values without time zone conversion (bizley)
- Bug #13350: Fixed bug with incorrect caching of `yii\web\UrlRule::createUrl()` results in `yii\web\UrlManager` (rob006)
- Bug #13362: Fixed return value of `yii\caching\MemCache::setValues()`  (masterklavi)
- Bug #13379: Fixed `applyFilter()` function in `yii.gridView.js` to work correctly when params in `filterUrl` are indexed (SilverFire, arogachev)
- Bug #13418: Fixed `QueryBuilder::batchInsert()` if `$rows` is `\Generator` (lav45)
- Bug #13494: Fixed `yii\console\controllers\MessageConstroller::saveMessagesToDb()` to work on different DBMS correctly (silverfire)
- Bug #13513: Fixed RBAC migration to work correctly on Oracle DBMS (silverfire)
- Bug #13537: Fixed `yii\web\CacheSession::destroySession()` to work correctly when session is not written yet (silverfire, papalapa)
- Bug #13538: Fixed `yii\db\BaseActiveRecord::deleteAll()` changes method signature declared by `yii\db\ActiveRecordInterface::deleteAll()` (klimov-paul)
- Bug #13551: Fixed `FixtureController` to load fixtures from subdirectories (d1rtyf1ng3rs, silverfire)
- Bug #13571: Fix `yii\db\mssql\QueryBuilder::checkIntegrity` for all tables (boboldehampsink)
- Bug #13577: `yii\db\QueryBuilder::truncateTable` should work consistent over all databases (boboldehampsink)
- Bug #13582: PK column in `yii\db\pgsql\QueryBuilder::resetSequence()` was not quoted properly (boboldehampsink)
- Bug #13592: Fixes `yii\db\oci\Schema::setTransactionIsolationLevel()` in Oracle (sergeymakinen)
- Bug #13594: Fixes insufficient quoting in `yii\db\QueryBuilder::prepareInsertSelectSubQuery()` (sergeymakinen)
- Bug #13649: Fixes issue where `['uncheck' => false]` and `['label' => false]` options for `ActiveRadio` and `ActiveCheckbox` were ignored (Alex-Code)
- Bug #13657: Fixed `yii\helpers\StringHelper::truncateHtml()` skip extra tags at the end (sam002)
- Bug #13670: Fixed alias option from console when it includes `-` or `_` in option name (pana1990)
- Bug #13671: Fixed error handler trace to work correctly with XDebug (samdark)
- Bug #13689: Fixed handling of errors in closures (mikehaertl)
- Bug #13694: `yii\widgets\Pjax` now sends `X-Pjax-Url` header with response to fix redirect (wleona3, Faryshta)
- Bug #13704: Fixed `yii\validators\UniqueValidator` to prefix attribute name with model's database table name (vladis84)
- Bug #13707: Fixed `yii\web\ErrorHandler` and `yii\web\ErrorAction` not setting correct response code to response object before rendering error view (samdark)
- Bug #13728: Fixed the bug when `yii\behaviors\SluggableBehavior` wasn't preserving immutable slug values (Kolyunya)
- Bug #13738: Fixed `getQueryParams()` method in `yii.js` to correctly parse URL with question mark and no query parameters (vladdnepr)
- Bug #13776: Fixed setting precision and scale for decimal columns in MSSQL (arturf)
- Bug #13790: Fixed error in `\yii\widgets\MaskedInput` JavaScript by raising version required (samdark)
- Bug #13807: Fixed `yii\db\QueryBuilder` to inherit subquery params when building a `INSERT INTO ... SELECT` query (sergeymakinen)
- Bug #13842: Fixed ambiguous table SQL error while using `yii\validators\ExistValidator` and `yii\validators\UniqueValidator` (vladis84, samdark)
- Bug #13846: Fixed `Query::count()` issue with `orderBy` (Alex-Code)
- Bug #13848: `yii\di\Instance::ensure()` wasn't throwing an exception when `$type` is specified and `$reference` object isn't instance of `$type` (c-jonua)
- Bug #13890: `yii\log\DbTarget` log messages where not written when a database transaction was rolled back, added support for cloning a `yii\db\Connection` (shirase, cebe)
- Bug #13901: Fixed passing unused parameter to `formatMessage()` call in `\yii\validators\IpValidator` (Kolyunya)
- Bug #13961: Fixed `unserialize()` error during RBAC rule retrieving from PostgreSQL DBMS (vsguts, nanodesu88, cebe)
- Bug #14012: `yii\db\pgsql\Schema::findViewNames()` was skipping materialized views (insolita)
- Bug #14033: Fixed `yii\filters\AccessRule::matchIp()` erroring in case IP is not defined under HHVM (Kolyunya)
- Bug #14042: Fixed ambiguous column name in SELECT in UniqueValidator (cebe)
- Bug #14052: Fixed processing parse errors on PHP 7 since these are instances of `\ParseError` (samdark)
- Bug #14072: Fixed a bug where `\yii\db\Command::createTable()`, `addForeignKey()`, `dropForeignKey()`, `addCommentOnColumn()`, and `dropCommentFromColumn()` weren't refreshing the table cache on `yii\db\Schema` (brandonkelly)
- Bug #14074: Fixed default value of `yii\console\controllers\FixtureController::$globalFixtures` to contain valid class name (lynicidn)
- Bug #14094: Fixed bug when single `yii\web\UrlManager::createUrl()` call my result multiple calls of `yii\web\UrlRule::createUrl()` for the same rule (rossoneri)
- Bug #14133: Fixed bug when calculating timings with mixed nested profile begin and end in `yii\log\Logger::calculateTimings()` (bizley)
- Enh #4793: `yii\filters\AccessControl` now can be used without `user` component (bizley)
- Enh #4999: Added support for wildcards at `yii\filters\AccessRule::$controllers` (klimov-paul)
- Enh #5108: `yii\validators\DateValidator` now resets `$timestampAttribute` value on empty validated attribute value (klimov-paul)
- Enh #8426: `yii\filters\AccessRule` now allows passing parameters to the role checking function (fsateler, cebe, Faryshta)
- Enh #8641: Enhanced `yii\console\Request::resolve()` to prevent passing parameters, that begin from digits (silverfire)
- Enh #11288: Added support for caching of `yii\web\UrlRule::createUrl()` results in `yii\web\UrlManager` for rules with defaults (rob006)
- Enh #12528: Added option to disable query logging and profiling in DB command (cebe)
- Enh #13144: Refactored `yii\db\Query::queryScalar()` (Alex-Code)
- Enh #13179: Added `yii\data\Sort::parseSortParam` allowing to customize sort param in descendant class (leandrogehlen)
- Enh #13221: Make `\yii\db\QueryTrait::limit()` and `\yii\db\QueryTrait::offset()` methods work with `\yii\db\Expression` (Ni-san)
- Enh #13226: `yii cache` command now warns about the fact that it's not able to flush APC cache from console (samdark)
- Enh #13240: Client scripts registration in `yii\widgets\ActiverForm` was moved to the separate `registerClientScript()` method (uaoleg, silverfire)
- Enh #13243: Added support for unicode attribute names in `yii\widgets\DetailView` (arogachev)
- Enh #13254: Core validators no longer require `Yii::$app` to be set (sammousa)
- Enh #13260: Added support for sorting by expression to `\yii\data\Sort` (LAV45, klimov-paul)
- Enh #13278: `yii\caching\DbQueryDependency` created allowing specification of the cache dependency via `yii\db\QueryInterface` (klimov-paul)
- Enh #13352: Added option to not render empty row in `yii\grid\GridView` when data is empty and `emptyText` set to `false` (arogachev)
- Enh #13356: Support multiple paths in `MigrateController::$migrationPath` to load non-namespaced migrations for BC with existing applications and extensions (schmunk42, cebe)
- Enh #13360: Added Dockerized test setup for the framework tests (schmunk42)
- Enh #13369: Added ability to render current `yii\widgets\LinkPager` page disabled (aquy)
- Enh #13376: Data provider now automatically sets an ID so there is no need to set it manually in case multiple data providers are used with pagination (SamMousa)
- Enh #13407: Added URL-safe base64 encode/decode methods to `StringHelper` (andrewnester)
- Enh #13467: `yii\data\ActiveDataProvider` no longer queries models if models count is zero (kLkA, Kolyunya)
- Enh #13523: Fixed pluralization and singularization for words `pasta`, `currency` (developeruz, silverfire)
- Enh #13550: Refactored `unset()` call order in `yii\di\ServiceLocator::set()` (Lanrik)
- Enh #13560: Refactored `\yii\widgets\FragmentCache::getCachedContent()`, added tests (Kolyunya)
- Enh #13576: Added support of `srcset` to `yii\helpers\Html::img()` (Kolyunya)
- Enh #13577: Implemented `yii\db\mssql\QueryBuilder::resetSequence()` (boboldehampsink)
- Enh #13582: Added tests for all `yii\db\QueryBuilder::resetSequence()` implementations, fixed SQLite implementation (boboldehampsink)
- Enh #13642: Allow overriding the function for creating related queries in ActiveRecord by adding `createRelationQuery()` (leandrogehlen)
- Enh #13650: Improved `yii\base\Security::hkdf()` to take advantage of native `hash_hkdf()` implementation in PHP >= 7.1.2  (charlesportwoodii)
- Enh #13695: `yii\web\Response::setStatusCode()` method now returns the Response object itself (kyle-mccarthy)
- Enh #13698: `yii\grid\DataColumn` filter is automatically generated as dropdown list in case of `format` set to `boolean` (bizley)
- Enh #13770: Added support for `yii\widgets\Menu` item classes definition in the form of an array (Kolyunya)
- Enh #13820: Add new HTTP status code 451 (yyxx9988)
- Enh #13823: Refactored migrations template (Kolyunya)
- Enh #13837: Refactored masking of CSRF tokens (sammousa)
- Enh #13845: `mt_rand()` is used instead of `rand()` in `yii\captcha\CaptchaAction` (kalessil)
- Enh #13883: `yii\data\SqlDataProvider` now provides automatic fallback for the case when `totalCount` is not specified (SamMousa)
- Enh #13911: Significantly enhanced MSSQL schema reading performance (paulzi, WebdevMerlion)
- Enh #13945: Removed Courier New from error page fonts list since it looks bad on Linux (samdark)
- Enh #13963: Added tests for `yii\behaviors\TimestampBehavior` (vladis84)
- Enh #13976: Disabled IPv6 check on `\yii\validators\IpValidator` as it turns out it is not needed for `inet_*` methods to work (mikk150)
- Enh #13981: `yii\caching\Cache::getOrSet()` now supports both `Closure` and `callable` (silverfire)
- Enh #13994: Refactored `yii\filters\RateLimiter`. Added tests (vladis84)
- Enh #14059: Removed unused AR instantiating for calling of static methods (ElisDN)
- Enh #14067: `yii\web\View::clear()` sets populated arrays to empty arrays instead of null, also changed default values to empty array (craiglondon)
- Enh #14098: `yii\helpers\BaseFileHelper::normalizeOptions()` is now protected (brandonkelly)
- Enh: Added `yii\di\Instance::__set_state()` method to restore object after serialization using `var_export()` function (silvefire)


2.0.11.2 February 08, 2017
--------------------------

- Bug #13501: Fixed `yii\rbac\DbManager::getRule()` and `yii\rbac\DbManager::getRules()` to properly handle resource data came from Rule table when using PostgreSQL (StalkAlex)
- Bug #13508: Fixed duplicate attachment of behavior BC break (cebe)
- Bug #13522: Issue with UrlRule, which created duplicate slashes when a default value was used (cebe)
- Bug #13533: Fixed BC break in `yii\validators\ExistValidator::$targetAttribute` (developeruz)


2.0.11.1 February 02, 2017
------------------------

- Bug #11502: Fixed `yii\console\controllers\MessageController` to properly populate missing languages in case of extraction with "db" format (bizley)
- Bug #13489: Fixed button names in ActionColumn to contain proper `Yii::t()` tags and restored missing translations for `el`, `fa`, `ja`, `ru`, and `sk` (cebe, softark)


2.0.11 February 01, 2017
------------------------

- Bug #4113: Error page stacktrace was generating links to private methods which are not part of the API docs (samdark)
- Bug #7727: Fixed `yii\helpers\StringHelper::truncateHtml()` leaving extra tags (developeruz)
- Bug #9305: Fixed MSSQL `Schema::TYPE_TIMESTAMP` to be 'datetime' instead of 'timestamp', which is just an incremental number (nkovacs)
- Bug #9616: Fixed mysql\Schema::loadColumnSchema to set enumValues attribute correctly if enum definition contains commas (fphammerle)
- Bug #9796: Initialization of not existing `yii\grid\ActionColumn` default buttons (arogachev)
- Bug #10488: Fixed incorrect behavior of `yii\validation\NumberValidator` when used with locales where decimal separator is comma (quantum13, samdark, rob006)
- Bug #11122: Fixed can not use `orderBy` with aggregate functions like `count` (Ni-san)
- Bug #11771: Fixed semantics of `yii\di\ServiceLocator::__isset()` to match the behavior of `__get()` which fixes inconsistent behavior on newer PHP versions (cebe)
- Bug #12133: Fixed `getDbTargets()` function in `yii\log\migrations\m141106_185632_log_init` that would create a log table correctly (bumstik)
- Bug #12213: Fixed `yii\db\ActiveRecord::unlinkAll()` to respect `onCondition()` of the relational query (silverfire)
- Bug #12345: Fixed `Formatter::asCurrency()` for proper decimal formatting (Oxyaction)
- Bug #12599: Fixed MSSQL fail to work with `nvarbinary`. Enhanced SQL scripts compatibility with older versions (samdark)
- Bug #12681: Changed `data` column type from `text` to `blob` to handle null-byte (`\0`) in serialized RBAC rule properly (silverfire)
- Bug #12703: Fixed `StringHelper::truncateHtml()` non functional when dom PHP extension is disabled (samdark)
- Bug #12713: Fixed `yii\caching\FileDependency` to clear stat cache before reading filemtime (SG5)
- Bug #12714: Fixed `yii\validation\EmailValidator` to prevent false-positives checks when property `checkDns` is set to `true` (silverfire)
- Bug #12735: Fixed `yii\console\controllers\MigrateController` creating multiple primary keys for field `bigPrimaryKey:unsigned` (SG5)
- Bug #12791: Fixed `yii\behaviors\AttributeTypecastBehavior` unable to automatically detect `attributeTypes`, triggering PHP Fatal Error (klimov-paul)
- Bug #12795: Fixed inconsistency, `Yii::$app->controller` is available after handling the request since 2.0.10, this is now also the case for `Yii::$app->controller->action` (cebe)
- Bug #12803, #12921: Fixed BC break in `yii.activeForm.js` introduced in #11999. Reverted commit 3ba72da (silverfire)
- Bug #12810: Fixed `yii\rbac\DbManager::getChildRoles()` and `yii\rbac\PhpManager::getChildRoles()` throws an exception when role has no child roles (mysterydragon)
- Bug #12822: Fixed `yii\i18n\Formatter::asTimestamp()` to process timestamp with miliseconds correctly (h311ion)
- Bug #12824: Enabled usage of `yii\mutex\FileMutex` on Windows systems (davidsonalencar)
- Bug #12828: Fixed handling of nested arrays, objects in `\yii\grid\GridView::guessColumns` (githubjeka)
- Bug #12836: Fixed `yii\widgets\GridView::filterUrl` to not ignore `#` part of filter URL (cebe, arogachev)
- Bug #12856: Fixed `yii\web\XmlResponseFormatter` to use `true` and `false` to represent booleans (samdark)
- Bug #12879: Console progress bar was not working properly in Windows terminals (samdark, kids-return)
- Bug #12880: Fixed `yii\behaviors\AttributeTypecastBehavior` marks attributes with `null` value as 'dirty' (klimov-paul)
- Bug #12904: Fixed lowercase table name in migrations (zlakomanoff)
- Bug #12939: Hard coded table names for MSSQL in RBAC migration (arogachev)
- Bug #12969: Improved unique ID generation for `yii\widgets\Pjax` widgets (dynasource, samdark, rob006)
- Bug #12974: Fixed incorrect order of migrations history in case `yii\console\controllers\MigrateController::$migrationNamespaces` is in use (evgen-d, klimov-paul)
- Bug #13071: Help option for 
