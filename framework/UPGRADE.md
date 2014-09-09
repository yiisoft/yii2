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

* The `fileinfo` PHP extension is now required by Yii. If you use  `yii\helpers\FileHelper::getMimeType()`, make sure
  you have enabled this extension. This extension is [builtin](http://www.php.net/manual/en/fileinfo.installation.php) in php above `5.3`.

* Please update your main layout file by adding this line in the `<head>` section: `<?= Html::csrfMetaTags() ?>`.
  This change is needed because `yii\web\View` no longer automatically generates CSRF meta tags due to issue #3358.

* If your model code is using the `file` validation rule, you should rename its `types` option to `extensions`.

* `MailEvent` class has been moved to the `yii\mail` namespace. You have to adjust all references that may exist in your code.

* The behavior and signature of `ActiveRecord::afterSave()` has changed. `ActiveRecord::$isNewRecord` will now always be
  false in afterSave and also dirty attributes are not available. This change has been made to have a more consistent and
  expected behavior. The changed attributes are now available in the new parameter of afterSave() `$changedAttributes`.
  `$changedAttributes` contains the old values of attributes that had changed and were saved.

* `ActiveRecord::updateAttributes()` has been changed to not trigger events and not respect optimistic locking anymore to
  differentiate it more from calling `update(false)` and to ensure it can be used in `afterSave()` without triggering infinite
  loops.

* If you are developing RESTful APIs and using an authentication method such as `yii\filters\auth\HttpBasicAuth`,
  you should explicitly configure `yii\web\User::enableSession` in the application configuration to be false to avoid
  starting a session when authentication is performed. Previously this was done automatically by authentication method.

* `mail` component was renamed to `mailer`, `yii\log\EmailTarget::$mail` was renamed to `yii\log\EmailTarget::$mailer`.
  Please update all references in the code and config files.

* `yii\caching\GroupDependency` was renamed to `TagDependency`. You should create such a dependency using the code
  `new \yii\caching\TagDependency(['tags' => 'TagName'])`, where `TagName` is similar to the group name that you
  previously used.

* If you are using the constant `YII_PATH` in your code, you should rename it to `YII2_PATH` now.

* You must explicitly configure `yii\web\Request::cookieValidationKey` with a secret key. Previously this is done automatically.
  To do so, modify your application configuration like the following:

  ```php
  return [
      // ...
      'components' => [
          'request' => [
              'cookieValidationKey' => 'your secret key here',
          ],
      ],
  ];
  ```

  > Note: If you are using the `Advanced Application Template` you should not add this configuration to `common/config`
  or `console/config` because the console application doesn't have to deal with CSRF and uses its own request that
  doesn't have `cookieValidationKey` property.

* `yii\rbac\PhpManager` now stores data in three separate files instead of one. In order to convert old file to
new ones save the following code as `convert.php` that should be placed in the same directory your `rbac.php` is in: 

  ```php
  <?php
  $oldFile = 'rbac.php';
  $itemsFile = 'items.php';
  $assignmentsFile = 'assignments.php';
  $rulesFile = 'rules.php';
  
  $oldData = include $oldFile;
  
  function saveToFile($data, $fileName) {
      $out = var_export($data, true);
      $out = "<?php\nreturn " . $out . ";";
      $out = str_replace(['array (', ')'], ['[', ']'], $out);
      file_put_contents($fileName, $out);
  }
  
  $items = [];
  $assignments = [];
  if (isset($oldData['items'])) {
      foreach ($oldData['items'] as $name => $data) {
          if (isset($data['assignments'])) {
              foreach ($data['assignments'] as $userId => $assignmentData) {
                  $assignments[$userId][] = $assignmentData['roleName'];
              }
              unset($data['assignments']);
          }
          $items[$name] = $data;
      }
  }
  
  $rules = [];
  if (isset($oldData['rules'])) {
      $rules = $oldData['rules'];
  }
  
  saveToFile($items, $itemsFile);
  saveToFile($assignments, $assignmentsFile);
  saveToFile($rules, $rulesFile);
  
  echo "Done!\n";
  ```

  Run it once, delete `rbac.php`. If you've configured `authFile` property, remove the line from config and instead
  configure `itemFile`, `assignmentFile` and `ruleFile`.

* Static helper `yii\helpers\Security` has been converted into an application component. You should change all usage of
  its methods to a new syntax, for example: instead of `yii\helpers\Security::hashData()` use `Yii::$app->getSecurity()->hashData()`.
  Default encryption and hash parameters has been upgraded. If you need to decrypt/validate data that was encrypted/hashed
  before, use the following configuration of the 'security' component:

  ```php
  return [
      'components' => [
          'security' => [
              'cryptBlockSize' => 16,
              'cryptKeySize' => 24,
              'derivationIterations' => 1000,
              'deriveKeyStrategy' => 'hmac', // for PHP version < 5.5.0
              //'deriveKeyStrategy' => 'pbkdf2', // for PHP version >= 5.5.0
              'useDeriveKeyUniqueSalt' => false,
          ],
          // ...
      ],
      // ...
  ];
  ```

* If you are using query caching, you should modify your relevant code as follows, as `beginCache()` and `endCache()` are
  replaced by `cache()`:

  ```php
  $db->cache(function ($db) {

     // ... SQL queries that need to use query caching

  }, $duration, $dependency);
  ```
  
* Due to significant changes to security you need to upgrade your code to use `\yii\base\Security` component instead of
  helper. If you have any data encrypted it should be re-encrypted. In order to do so you can use old security helper
  [as explained by @docsolver at github](https://github.com/yiisoft/yii2/issues/4461#issuecomment-50237807).

* [[yii\helpers\Url::to()]] will no longer prefix base URL to relative URLs. For example, `Url::to('images/logo.png')`
  will return `images/logo.png` directly. If you want a relative URL to be prefix with base URL, you should make use
  of the alias `@web`. For example, `Url::to('@web/images/logo.png')` will return `/BaseUrl/images/logo.png`.

* The following properties are now taking `false` instead of `null` for "don't use" case:
  - `yii\bootstrap\NavBar::$brandLabel`.
  - `yii\bootstrap\NavBar::$brandUrl`.
  - `yii\bootstrap\Modal::$closeButton`.
  - `yii\bootstrap\Modal::$toggleButton`.
  - `yii\bootstrap\Alert::$closeButton`.
  - `yii\widgets\LinkPager::$nextPageLabel`.
  - `yii\widgets\LinkPager::$prevPageLabel`.
  - `yii\widgets\LinkPager::$firstPageLabel`.
  - `yii\widgets\LinkPager::$lastPageLabel`.

* The format of the Faker fixture template is changed. For an example, please refer to the file
  `apps/advanced/common/tests/templates/fixtures/user.php`.

* The signature of all file downloading methods in `yii\web\Response` is changed, as summarized below:
  - `sendFile($filePath, $attachmentName = null, $options = [])`
  - `sendContentAsFile($content, $attachmentName, $options = [])`
  - `sendStreamAsFile($handle, $attachmentName, $options = [])`
  - `xSendFile($filePath, $attachmentName = null, $options = [])`

* The signature of callbacks used in `yii\base\ArrayableTrait::fields()` is changed from `function ($field, $model) {`
  to `function ($model, $field) {`.

* `Html::radio()`, `Html::checkbox()`, `Html::radioList()`, `Html::checkboxList()` no longer generate the container
  tag around each radio/checkbox when you specify labels for them. You should manually render such container tags,
  or set the `item` option for `Html::radioList()`, `Html::checkboxList()` to generate the container tags.
