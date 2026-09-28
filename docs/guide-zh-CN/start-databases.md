使用数据库
======================

本章节将介绍如何创建一个从数据表 `country` 中读取国家数据并显示出来的页面。
为了实现这个目标，你将会配置一个数据库连接，
创建一个[活动记录](db-active-record.md)类，
并且创建一个[操作](structure-controllers.md)及一个[视图](structure-views.md)。

贯穿整个章节，你将会学到：

* 配置一个数据库连接
* 定义一个活动记录类
* 使用活动记录从数据库中查询数据
* 以分页方式在视图中显示数据

请注意，为了掌握本章你应该具备最基本的数据库知识和使用经验。
尤其是应该知道如何创建数据库，如何通过数据库终端执行 SQL 语句。


准备数据库 <span id="preparing-database"></span>
--------------------

首先创建一个名为 `yii2basic` 的数据库，应用将从这个数据库中读取数据。
你可以创建 SQLite，MySQL，PostregSQL，MSSQL 或 Oracle 数据库，Yii 内置多种数据库支持。简单起见，后面的内容将以 MySQL 为例做演示。

> Info: 虽然 MariaDB 曾经是 MySQL 的直接替代品，但现在已经不再完全正确。如果您希望在 MariaDB 中使用“JSON”支持等高级功能，请查看下面列出的 MariaDB 扩展。

然后在数据库中创建一个名为 `country` 的表并插入简单的数据。可以执行下面的语句：

```sql
CREATE TABLE `country` (
  `code` CHAR(2) NOT NULL PRIMARY KEY,
  `name` CHAR(52) NOT NULL,
  `population` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` VALUES ('AU','Australia',18886000);
INSERT INTO `country` VALUES ('BR','Brazil',170115000);
INSERT INTO `country` VALUES ('CA','Canada',1147000);
INSERT INTO `country` VALUES ('CN','China',1277558000);
INSERT INTO `country` VALUES ('DE','Germany',82164700);
INSERT INTO `country` VALUES ('FR','France',59225700);
INSERT INTO `country` VALUES ('GB','United Kingdom',59623400);
INSERT INTO `country` VALUES ('IN','India',1013662000);
INSERT INTO `country` VALUES ('RU','Russia',146934000);
INSERT INTO `country` VALUES ('US','United States',278357000);
```

此时便有了一个名为 `yii2basic` 的数据库，在这个数据库中有一个包含三个字段的数据表 `country`，表中有十行数据。

配置数据库连接 <span id="configuring-db-connection"></span>
---------------------------

开始之前，请确保你已经安装了 PHP [PDO](https://www.php.net/manual/zh/book.pdo.php) 
扩展和你所使用的数据库的 PDO 驱动（例如 MySQL 的 `pdo_mysql`）。
对于使用关系型数据库来讲，这是基本要求。

驱动和扩展安装可用后，打开 `config/db.php` 修改里面的配置参数对应你的数据库配置。
该文件默认包含这些内容：

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

`config/db.php` 是一个典型的基于文件的[配置](concept-configurations.md)工具。
这个文件配置了数据库连接 [[yii\db\Connection]] 的创建和初始化参数，
应用的 SQL 查询正是基于这个数据库。

上面配置的数据库连接可以在应用中通过 `Yii::$app->db` 表达式访问。

> Info: `config/db.php` 将被包含在应用配置文件 `config/web.php` 中，
  后者指定了整个[应用](structure-applications.md)如何初始化。
  请参考[配置](concept-configurations.md)章节了解更多信息。

如果想要使用 Yii 没有捆绑支持的数据库，你可以查看以下插件：

- [Informix](https://github.com/edgardmessias/yii2-informix)
- [IBM DB2](https://github.com/edgardmessias/yii2-ibm-db2)
- [Firebird](https://github.com/edgardmessias/yii2-firebird)
- [MariaDB](https://github.com/sam-it/yii2-mariadb)


创建活动记录 <span id="creating-active-record"></span>
-------------------------

创建一个继承自[活动记录](db-active-record.md)类的类 `Country`，
把它放在 `models/Country.php` 文件，去代表和读取 `country` 表的数据。

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

这个 `Country` 类继承自 [[yii\db\ActiveRecord]]。你不用在里面写任何代码。
只需要像现在这样，Yii 就能根据类名去猜测对应的数据表名。

> Info: 如果类名和数据表名不能直接对应，
  可以覆写 [[yii\db\ActiveRecord::tableName()|tableName()]] 方法去显式指定相关表名。

使用 `Country` 类可以很容易地操作 `country` 表数据，就像这段代码：

```php
use app\models\Country;

// 获取 country 表的所有行并以 name 排序
$countries = Country::find()->orderBy('name')->all();

// 获取主键为 “US” 的行
$country = Country::findOne('US');

// 输出 “United States”
echo $country->name;

// 修改 name 为 “U.S.A.” 并在数据库中保存更改
$country->name = 'U.S.A.';
$country->save();
```

> Info: 活动记录是面向对象、功能强大的访问和操作数据库数据的方式。你可以在[活动记录](db-active-record.md)章节了解更多信息。
  除此之外你还可以使用另一种更原生的被称做[数据访问对象](db-dao)的方法操作数据库数据。


创建动作 <span id="creating-action"></span>
------------------

为了向最终用户显示国家数据，你需要创建一个操作。相比之前小节掌握的在 `site` 控制器中创建操作，
在这里为所有和国家有关的数据新建一个控制器更加合理。
新控制器名为 `CountryController`，并在其中创建一个 `index` 操作，
如下：

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Country;

class CountryController extends Controller
{
    public function actionIndex()
    {
        $query = Country::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $countries = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'countries' => $countries,
            'pagination' => $pagination,
        ]);
    }
}
```

把上面的代码保存在 `controllers/CountryController.php` 文件中。

`index` 操作调用了活动记录 `Country::find()` 方法，去生成查询语句并从 `country` 表中取回所有数据。
为了限定每个请求所返回的国家数量，查询在 [[yii\data\Pagination]] 对象的帮助下进行分页。
`Pagination` 对象的使命主要有两点：

* 为 SQL 查询语句设置 `offset` 和 `limit` 从句，
  确保每个请求只需返回一页数据（本例中每页是 5 行）。
* 在视图中显示一个由页码列表组成的分页器，
  这点将在后面的段落中解释。

在代码末尾，`index` 操作渲染一个名为 `index` 的视图，
并传递国家数据和分页信息进去。


创建视图 <span id="creating-view"></span>
---------------

在 `views` 目录下先创建一个名为 `country` 的子目录。
这个目录存储所有由 `country` 控制器渲染的视图。在 `views/country` 目录下
创建一个名为 `index.php` 的视图文件，内容如下：

```php
<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->name} ({$country->code})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

这个视图包含两部分用以显示国家数据。第一部分遍历国家数据并以无序 HTML 列表渲染出来。
第二部分使用 [[yii\widgets\LinkPager]] 去渲染从操作中传来的分页信息。
小部件 `LinkPager` 显示一个分页按钮的列表。
点击任何一个按钮都会跳转到对应的分页。


试运行 <span id="trying-it-out"></span>
-------------

浏览器访问下面的 URL 看看能否工作：

```
https://hostname/index.php?r=country/index
```

![国家列表](images/start-country-list.png)

首先你会看到显示着五个国家的列表页面。在国家下面，你还会看到一个包含四个按钮的分页器。
如果你点击按钮 “2”，将会跳转到显示另外五个国家的页面，
也就是第二页记录。如果观察仔细点你还会看到浏览器的 URL 变成了：

```
https://hostname/index.php?r=country/index&page=2
```

在这个场景里，[[yii\data\Pagination|Pagination]] 提供了为数据结果集分页的所有功能：

* 首先 [[yii\data\Pagination|Pagination]] 把 SELECT 的子查询 `LIMIT 5 OFFSET 0` 数据表示成第一页。
  因此开头的五条数据会被取出并显示。
* 然后小部件 [[yii\widgets\LinkPager|LinkPager]] 使用 
  [[yii\data\Pagination::createUrl()|Pagination::createUrl()]] 方法生成的 URL 去渲染翻页按钮。
  URL 中包含必要的参数 `page` 才能查询不同的页面编号。
* 如果你点击按钮 “2”，将会发起一个路由为 `country/index` 的新请求。
  [[yii\data\Pagination|Pagination]] 接收到 URL 中
  的 `page` 参数把当前的页码设为 2。
  新的数据库请求将会以 `LIMIT 5 OFFSET 5` 查询并显示。


总结 <span id="summary"></span>
-------

本章节中你学到了如何使用数据库。你还学到了如何取出并使用 
[[yii\data\Pagination]] 和 [[yii\widgets\LinkPager]] 显示数据。

下一章中你会学到如何使用 Yii 中强大的代码生成器 [Gii](tool-gii.md)，
去帮助你实现一些常用的功能需求，
例如增查改删（CRUD）数据表中的数据。
事实上你之前所写的代码全部都可以由 Gii 自动生成。
