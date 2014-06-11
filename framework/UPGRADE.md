Upgrading Instructions for Yii Framework v2
===========================================

!!!IMPORTANT!!!

The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to following the instructions
for both A and B.


Upgrade from Yii 2.0 Beta
-------------------------

* If you used `clearAll()` or `clearAllAssignments()` of `yii\rbac\DbManager`, you should replace
  them with `removeAll()` and `removeAllAssignments()` respectively.

* If you created RBAC rule classes, you should modify their `execute()` method by adding `$user`
  as the first parameter: `execute($user, $item, $params)`. The `$user` parameter represents
  the ID of the user currently being access checked. Previously, this is passed via `$params['user']`.

* If you override `yii\grid\DataColumn::getDataCellValue()` with visibility `protected` you have
  to change visibility to `public` as visibility of the base method has changed.

* If you have classes implementing `yii\web\IdentityInterface` (very common), you should modify
  the signature of `findIdentityByAccessToken()` as
  `public static function findIdentityByAccessToken($token, $type = null)`. The new `$type` parameter
  will contain the type information about the access token. For example, if you use
  `yii\filters\auth\HttpBearerAuth` authentication method, the value of this parameter will be
  `yii\filters\auth\HttpBearerAuth`. This allows you to differentiate access tokens taken by
  different authentication methods.

* If you are sharing the same cache across different applications, you should configure
  the `keyPrefix` property of the cache component to use some unique string.
  Previously, this property was automatically assigned with a unique string.

* If you are using `dropDownList()`, `listBox()`, `activeDropDownList()`, or `activeListBox()`
  of `yii\helpers\Html`, and your list options use multiple blank spaces to format and align
  option label texts, you need to specify the option `encodeSpaces` to be true.

* If you are using `yii\grid\GridView` and have configured a data column to use a PHP callable
  to return cell values (via `yii\grid\DataColumn::value`), you may need to adjust the signature
  of the callable to be `function ($model, $key, $index, $widget)`. The `$key` parameter was newly added
  in this release.

* `yii\console\controllers\AssetController` is now using hashes instead of timestamps. Replace all `{ts}` with `{hash}`.

* The database table of the `yii\log\DbTarget` now needs a `prefix` column to store context information.
  You can add it with `ALTER TABLE log ADD COLUMN prefix TEXT AFTER log_time;`.

* The  `fileinfo`  PHP extension is now required by Yii. If you use  `yii\helpers\FileHelper::getMimeType()`, make sure 
  you have enabled this extension. This extension is [builtin](http://www.php.net/manual/en/fileinfo.installation.php) in php above `5.3`.