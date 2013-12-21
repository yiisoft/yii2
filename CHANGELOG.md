Yii Framework 2 Change Log
==========================

2.0.0 beta under development
----------------------------

- Bug #1446: Logging while logs are processed causes infinite loop (qiangxue)
- Bug #1497: Localized view files are not correctly returned (mintao)
- Bug #1500: Log messages exported to files are not separated by newlines (omnilight, qiangxue)
- Bug #1509: The SQL for creating Postgres RBAC tables is incorrect (qiangxue)
- Bug #1545: It was not possible to execute db Query twice, params where missing (cebe)
- Bug #1591: StringValidator is accessing undefined property (qiangxue)
- Bug: Fixed `Call to a member function registerAssetFiles() on a non-object` in case of wrong `sourcePath` for an asset bundle (samdark)
- Bug: Fixed incorrect event name for `yii\jui\Spinner` (samdark)
- Bug: Json::encode() did not handle objects that implement JsonSerializable interface correctly (cebe)
- Bug: Fixed issue with tabular input on ActiveField::radio() and ActiveField::checkbox() (jom)
- Enh #1293: Replaced Console::showProgress() with a better approach. See Console::startProgress() for details (cebe)
- Enh #1406: DB Schema support for Oracle Database (p0larbeer, qiangxue)
- Enh #1437: Added ListView::viewParams (qiangxue)
- Enh #1469: ActiveRecord::find() now works with default conditions (default scope) applied by createQuery (cebe)
- Enh #1523: Query conditions now allow to use the NOT operator (cebe)
- Enh #1552: It is now possible to use multiple bootstrap NavBar in a single page (Alex-Code)
- Enh: Added `favicon.ico` and `robots.txt` to defauly application templates (samdark)
- Enh: Added `Widget::autoIdPrefix` to support prefixing automatically generated widget IDs (qiangxue)
- Enh: Support for file aliases in console command 'message' (omnilight)
- Enh: Sort and Paginiation can now create absolute URLs (cebe)
- Chg: Renamed yii\jui\Widget::clientEventsMap to clientEventMap (qiangxue)
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
