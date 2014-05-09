Working with Databases
======================

In this section, we will describe how to create a new page to display data fetched from a database table.
To achieve this goal, you will configure the database connection, create an [Active Record](db-active-record.md) class
to fetch and represent database data, and then create an [action](structure-controllers.md) and
a [view](structure-views.md) to present the data to end users.

Through this tutorial, you will learn

* How to configure DB connections;
* How to define an Active Record class;
* How to query data using the Active Record class;
* How to display data in a view in a paginated fashion.

Note that in order to finish this section, you should have basic knowledge and experience about databases.
You should know how to create a database and how to execute SQL statements using a DB client tool.


Configuring a Database Connection
---------------------------------

To start, you should have a database ready. It can be a SQLite, MySQL, PostgreSQL, MSSQL or Oracle database.
For simplicity, in the following description, we will assume that you already have a MySQL database named `basic`.

Create a table named `address` and insert some sample data. The SQL statements are showing as follows,

```sql
CREATE TABLE `address` (
    `id`       int(11) NOT NULL AUTO_INCREMENT,
    `street`   varchar(128),
    `city`     varchar(128),
    `state`    varchar(128),
    `country`  varchar(128)
);
```


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
