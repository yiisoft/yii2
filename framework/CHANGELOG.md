Yii Framework 2 Change Log
==========================

2.0.0 beta under development
----------------------------

- Bug #1446: Logging while logs are processed causes infinite loop (qiangxue)
- Bug #1497: Localized view files are not correctly returned (mintao)
- Bug #1500: Log messages exported to files are not separated by newlines (omnilight, qiangxue)
- Bug #1504: Debug toolbar isn't loaded successfully in some environments when xdebug is enabled (qiangxue)
- Bug #1509: The SQL for creating Postgres RBAC tables is incorrect (qiangxue)
- Bug #1545: It was not possible to execute db Query twice, params where missing (cebe)
- Bug #1550: fixed the issue that JUI input widgets did not property input IDs.
- Bug #1582: Error messages shown via client-side validation should not be double encoded (qiangxue)
- Bug #1591: StringValidator is accessing undefined property (qiangxue)
- Bug #1597: Added `enableAutoLogin` to basic and advanced application templates so "remember me" now works properly (samdark)
- Bug: Fixed `Call to a member function registerAssetFiles() on a non-object` in case of wrong `sourcePath` for an asset bundle (samdark)
- Bug: Fixed incorrect event name for `yii\jui\Spinner` (samdark)
- Bug: Json::encode() did not handle objects that implement JsonSerializable interface correctly (cebe)
- Bug: Fixed issue with tabular input on ActiveField::radio() and ActiveField::checkbox() (jom)
- Enh #797: Added support for validating multiple columns by `UniqueValidator` and `ExistValidator` (qiangxue)
- Enh #1293: Replaced Console::showProgress() with a better approach. See Console::startProgress() for details (cebe)
- Enh #1406: DB Schema support for Oracle Database (p0larbeer, qiangxue)
- Enh #1437: Added ListView::viewParams (qiangxue)
- Enh #1469: ActiveRecord::find() now works with default conditions (default scope) applied by createQuery (cebe)
- Enh #1499: Added `ActionColumn::controller` property to support customizing the controller for handling GridView actions (qiangxue)
- Enh #1523: Query conditions now allow to use the NOT operator (cebe)
- Enh #1552: It is now possible to use multiple bootstrap NavBar in a single page (Alex-Code)
- Enh #1572: Added `yii\web\Controller::createAbsoluteUrl()` (samdark)
- Enh #1579: throw exception when the given AR relation name does not match in a case sensitive manner (qiangxue)
- Enh #1581: Added `ActiveQuery::joinWith()` and `ActiveQuery::innerJoinWith()` to support joining with relations (qiangxue)
- Enh #1601: Added support for tagName and encodeLabel parameters in ButtonDropdown (omnilight)
- Enh #1611: Added `BaseActiveRecord::markAttributeDirty()` (qiangxue)
- Enh #1641: Added `BaseActiveRecord::updateAttributes()` (qiangxue)
- Enh: Added `favicon.ico` and `robots.txt` to defauly application templates (samdark)
- Enh: Added `Widget::autoIdPrefix` to support prefixing automatically generated widget IDs (qiangxue)
- Enh: Support for file aliases in console command 'message' (omnilight)
- Enh: Sort and Pagination can now create absolute URLs (cebe)
- Chg #1610: `Html::activeCheckboxList()` and `Html::activeRadioList()` will submit an empty string if no checkbox/radio is selected (qiangxue)
- Chg: Renamed `yii\jui\Widget::clientEventsMap` to `clientEventMap` (qiangxue)
- Chg: Renamed `ActiveRecord::getPopulatedRelations()` to `getRelatedRecords()` (qiangxue)
- Chg: Renamed `attributeName` and `className` to `targetAttribute` and `targetClass` for `UniqueValidator` and `ExistValidator` (qiangxue)
- Chg: Added `yii\widgets\InputWidget::options` (qiangxue)
- Chg: Changed the signature of `urlCreator` and button creators for `yii\gridview\ActionColumn` (qiangxue)
- New #1438: [MongoDB integration](https://github.com/yiisoft/yii2-mongodb) ActiveRecord and Query (klimov-paul)
- New #1393: [Codeception testing framework integration](https://github.com/yiisoft/yii2-codeception) (Ragazzo)

2.0.0 alpha, December 1, 2013
---------------------------

- Initial release.
- Official extensions released in this version:
  - [Twitter bootstrap 3.0](https://github.com/yiisoft/yii2-bootstrap)
  - [Jquery UI](https://github.com/yiisoft/yii2-jui)

  - [Debug Toolbar](https://github.com/yiisoft/yii2-debug)
  - [Gii code generator](https://github.com/yiisoft/yii2-gii)

  - [Elasticsearch integration](https://github.com/yiisoft/yii2-elasticsearch): ActiveRecord and Query
  - [Redis integration](https://github.com/yiisoft/yii2-redis): ActiveRecord, Cache and Session
  - [Sphinx integration](https://github.com/yiisoft/yii2-sphinx): ActiveRecord and Query

  - [Swiftmailer](https://github.com/yiisoft/yii2-swiftmailer)

  - [Smarty View Renderer](https://github.com/yiisoft/yii2-smarty)
  - [Twig View Renderer](https://github.com/yiisoft/yii2-twig)
