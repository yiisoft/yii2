Working with Databases
======================

In this section, we will describe how to create a new page to display the country data fetched from
from a database table `country`. To achieve this goal, you will configure a database connection,
create an [Active Record](db-active-record.md) class, and then create an [action](structure-controllers.md)
and a [view](structure-views.md).

Through this tutorial, you will learn

* How to configure a DB connection;
* How to define an Active Record class;
* How to query data using the Active Record class;
* How to display data in a view in a paginated fashion.

Note that in order to finish this section, you should have basic knowledge and experience about databases.
In particular, you should know how to create a database and how to execute SQL statements using a DB client tool.


Preparing a Database <a name="preparing-database"></a>
--------------------

To begin with, create a database named `yii2basic` from which you will fetch data in your application.
You may create a SQLite, MySQL, PostgreSQL, MSSQL or Oracle database. For simplicity, we will use MySQL
in the following description.

Create a table named `country` in the database and insert some sample data. You may run the following SQL statements.

```sql
CREATE TABLE `country` (
  `code` char(2) NOT NULL PRIMARY KEY,
  `name` char(52) NOT NULL,
  `population` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Country` VALUES ('AU','Australia',18886000);
INSERT INTO `Country` VALUES ('BR','Brazil',170115000);
INSERT INTO `Country` VALUES ('CA','Canada',1147000);
INSERT INTO `Country` VALUES ('CN','China',1277558000);
INSERT INTO `Country` VALUES ('DE','Germany',82164700);
INSERT INTO `Country` VALUES ('FR','France',59225700);
INSERT INTO `Country` VALUES ('GB','United Kingdom',59623400);
INSERT INTO `Country` VALUES ('IN','India',1013662000);
INSERT INTO `Country` VALUES ('RU','Russia',146934000);
INSERT INTO `Country` VALUES ('US','United States',278357000);
```

To this end, you have a database named `yii2basic`, and within this database there is a `country` table
with ten rows of data.


Configuring a DB Connection <a name="configuring-db-connection"></a>
---------------------------

Make sure you have installed the [PDO](http://www.php.net/manual/en/book.pdo.php) PHP extension and
the PDO driver for the database you are using (e.g. `pdo_mysql` for MySQL). This is a basic requirement
if your application uses a relational database.

Open the file `config/db.php` and adjust the content based on your database information. By default,
the file contains the following content:

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

This is a typical file-based [configuration](concept-configurations.md). It specifies the parameters
needed to create and initialize a [[yii\db\Connection]] instance through which you can make SQL queries
against the underlying database.

The DB connection configured above can be accessed in the code via the expression `Yii::$app->db`.

> Info: The `config/db.php` file will be included in the main application configuration `config/web.php`
  which specifies how the [application](structure-applications.md) instance should be initialized.
  For more information, please refer to the [Configurations](concept-configurations.md) section.


Creating an Active Record <a name="creating-active-record"></a>
-------------------------

To represent and fetch the data in the `country` table, create an [Active Record](db-active-record.md)
class named `Country` and save it in the file `models/Country.php`.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

The `Country` class extends from [[yii\db\ActiveRecord]]. You do not need to write any code inside of it.
Yii will guess the associated table name from the class name. In case this does not work, you may
override the [[yii\db\ActiveRecord::tableName()]] method to explicitly specify the associated table name.

Using the `Country` class, you can manipulate the data in the `country` table easily. Below are some
code snippets showing how you can make use of the `Country` class.

```php
use app\models\Country;

// get all rows from the country table and order them by "name"
$countries = Country::find()->orderBy('name')->all();

// get the row whose primary key is "US"
$country = Country::findOne('US');

// displays "United States"
echo $country->name;

// modifies the country name to be "U.S.A." and save it to database
$country->name = 'U.S.A.';
$country->save();
```

> Info: Active Record is a powerful way of accessing and manipulating database data in an object-oriented fashion.
You may find more detailed information in the [Active Record](db-active-record.md). Besides Active Record, you may also
use a lower-level data accessing method called [Data Access Objects](db-dao.md).


Creating an Action <a name="creating-action"></a>
------------------

To expose the country data to end users, you need to create a new action. Instead of doing this in the `site`
controller like you did in the previous sections, it makes more sense to create a new controller specifically
for all actions about manipulating country data. Name this new controller as `CountryController` and create
an `index` action in it, as shown in the following,

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

Save the above code in the file `controllers/CountryController.php`.

The `index` action calls `Country::find()` to build a DB query and retrieve all data from the `country` table.
To limit the number of countries returned in each request, the query is paginated with the help of a
[[yii\data\Pagination]] object. The `Pagination` object serves for two purposes:

* Sets the `offset` and `limit` clauses for the SQL statement represented by the query so that it only
  returns a single page of data (at most 5 rows in a page).
* Being used in the view to display a pager consisting of a list of page buttons, as will be explained in
  the next subsection.

At the end, the `index` action renders a view named `index` and passes the country data as well as the pagination
information to it.


Creating a View <a name="creating-view"></a>
---------------

Under the `views` directory, first create a sub-directory named `country`. This will used to hold all
views rendered by the `country` controller. Within the `views/country` directory, create a file named `index.php`
with the following content:

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

The view consists of two parts. In the first part, the country data is traversed and rendered as an unordered HTML list.
In the second part, a [[yii\widgets\LinkPager]] widget is rendered using the pagination information passed from the action.
The `LinkPager` widget displays a list of page buttons. Clicking on any of them will refresh the country data
in the corresponding page.


How It Works <a name="how-it-works"></a>
------------

To see how it works, use your browser to access the following URL:

```
http://hostname/index.php?r=country/index
```

![Country List](images/start-country-list.png)

You will see a page showing five countries. And below the countries, you will see a pager with four buttons.
If you click on the button "2", you will see that the page displays another five countries in the database.
Observe more carefully and you will find the URL in the browser changes to

```
http://hostname/index.php?r=country/index&page=2
```

Behind the scene, [[yii\data\Pagination|Pagination]] is playing the magic.

* Initially, [[yii\data\Pagination|Pagination]] represents the first page, which sets the country query
  with the clause `LIMIT 5 OFFSET 0`. As a result, the first five countries will be fetched and displayed.
* The [[yii\widgets\LinkPager|LinkPager]] widget renders the page buttons using the URLs
  created by [[yii\data\Pagination::createUrl()|Pagination]]. The URLs will contain the query parameter `page`
  representing different page numbers.
* If you click the page button "2", a new request for the route `country/index` will be triggered and handled.
  [[yii\data\Pagination|Pagination]] reads the `page` query parameter and sets the current page number 2.
  The new country query will thus have the clause `LIMIT 5 OFFSET 5` and return back the next five countries
  for display.


Summary <a name="summary"></a>
-------

In this section, you have learned how to work with a database. You have also learned how to fetch and display
data in pages with the help of [[yii\data\Pagination]] and [[yii\widgets\LinkPager]].

In the next section, you will learn how to use the powerful code generation tool, called [Gii](tool-gii.md),
to help you rapidly implement some commonly required features, such as the Create-Read-Update-Delete (CRUD)
operations about the data in a DB table. As a matter of fact, the code you have just written can all
be automatically generated using this tool.
