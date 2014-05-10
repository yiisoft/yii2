Working with Databases
======================

In this section, we will describe how to create a new page to display data fetched from a database table.
To achieve this goal, you will configure a database connection, create an [Active Record](db-active-record.md) class,
and then create an [action](structure-controllers.md) and a [view](structure-views.md).

Through this tutorial, you will learn

* How to configure a DB connection;
* How to define an Active Record class;
* How to query data using the Active Record class;
* How to display data in a view in a paginated fashion.

Note that in order to finish this section, you should have basic knowledge and experience about databases.
In particular, you should know how to create a database and how to execute SQL statements using a DB client tool.


Preparing a Database
--------------------

To begin with, create a database from which you will fetch data in your application. You may create
a SQLite, MySQL, PostgreSQL, MSSQL or Oracle database. For simplicity, in the following description
let's assume you have created a MySQL database named `basic`.

Make sure you have installed the [PDO](http://www.php.net/manual/en/book.pdo.php) PHP extension and
the PDO driver for the database you are using (e.g. `pdo_mysql` for MySQL).

Now create a table named `country` and insert some sample data, using the SQL statements as shown below,

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


Configuring a DB Connection
---------------------------



Creating an Active Record
-------------------------


Creating an Action
------------------


Creating a View
---------------


How It Works
------------


Summary
-------
