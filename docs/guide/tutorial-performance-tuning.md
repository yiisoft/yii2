Performance Tuning
==================

There are many factors affecting the performance of your Web application. Some are environmental, some are related 
with your code, while some others are related with Yii itself. In this section, we will enumerate most of these
factors and explain how you can improve your application performance by adjusting these factors.


## Optimizing your PHP Environment <span id="optimizing-php"></span>

A well configured PHP environment is very important. In order to get maximum performance,

- Use the latest stable PHP version. Major releases of PHP may bring significant performance improvements.
- Enable bytecode caching with [Opcache](http://php.net/opcache) (PHP 5.5 or later) or [APC](http://ru2.php.net/apc) 
  (PHP 5.4 or earlier). Bytecode caching avoids the time spent in parsing and including PHP scripts for every
  incoming request.


## Disabling Debug Mode <span id="disable-debug"></span>

When running an application in production, you should disable debug mode. Yii uses the value of a constant
named `YII_DEBUG` to indicate whether debug mode should be enabled. When debug mode is enabled, Yii
will take extra time to generate and record debugging information.

You may place the following line of code at the beginning of the [entry script](structure-entry-scripts.md) to
disable debug mode:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

> Info: The default value of `YII_DEBUG` is false. So if you are certain that you do not change its default
  value somewhere else in your application code, you may simply remove the above line to disable debug mode. 
  

## Using Caching Techniques <span id="using-caching"></span>

You can use various caching techniques to significantly improve the performance of your application. For example,
if your application allows users to enter text in Markdown format, you may consider caching the parsed Markdown
content to avoid parsing the same Markdown text repeatedly in every request. Please refer to 
the [Caching](caching-overview.md) section to learn about the caching support provided by Yii.


## Enabling Schema Caching <span id="enable-schema-caching"></span>

Schema caching is a special caching feature that should be enabled whenever you are using [Active Record](db-active-record.md).
As you know, Active Record is intelligent enough to detect schema information (e.g. column names, column types, constraints)
about a DB table without requiring you to manually describe them. Active Record obtains this information by executing 
extra SQL queries. By enabling schema caching, the retrieved schema information will be saved in the cache and reused
in future requests.

To enable schema caching, configure a `cache` [application component](structure-application-components.md) to store
the schema information and set [[yii\db\Connection::enableSchemaCache]] to be `true` in the [application configuration](concept-configurations.md):

```php
return [
    // ...
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase',
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,

            // Duration of schema cache.
            'schemaCacheDuration' => 3600,

            // Name of the cache component used to store schema information
            'schemaCache' => 'cache',
        ],
    ],
];
```


## Combining and Minimizing Assets <span id="optimizing-assets"></span>

A complex Web page often includes many CSS and/or JavaScript asset files. To reduce the number of HTTP requests 
and the overall download size of these assets, you should consider combining them into one single file and
compressing it. This may greatly improve the page loading time and reduce the server load. For more details,
please refer to the [Assets](structure-assets.md) section.


## Optimizing Session Storage <span id="optimizing-session"></span>

By default session data are stored in files. This is fine for development and small projects. But when it comes 
to handling massive concurrent requests, it is better to use more sophisticated storage, such as database. Yii supports
a variety of session storage out of box. You can use these storage by configuring the `session` component in the
[application configuration](concept-configurations.md) like the following,

```php
return [
    // ...
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',

            // Set the following if you want to use DB component other than
            // default 'db'.
            // 'db' => 'mydb',

            // To override default session table, set the following
            // 'sessionTable' => 'my_session',
        ],
    ],
];
```

The above configuration uses a database table to store session data. By default, it will use the `db` application
component as the database connection and store the session data in the `session` table. You do have to create the
`session` table as follows in advance, though,

```sql
CREATE TABLE session (
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

You may also store session data in a cache by using [[yii\web\CacheSession]]. In theory, you can use any supported
[cache storage](caching-data.md#supported-cache-storage). Note, however, that some cache storage may flush cached data
when the storage limit is reached. For this reason, you should mainly use those cache storage that do not enforce
storage limit.

If you have [Redis](http://redis.io/) on your server, it is highly recommended you use it as session storage by using
[[yii\redis\Session]].


## Optimizing Databases <span id="optimizing-databases"></span>

Execute DB queries and fetching data from databases is often the main performance bottleneck in
a Web application. Although using [data caching](caching-data.md) techniques may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous amounts of data and the cached data is invalid, 
fetching the latest data could be prohibitively expensive without proper database and query design.

A general technique to improve the performance of DB queries is to create indices for table columns that
need to be filtered by. For example, if you need to look for a user record by `username`, you should create an index
on `username`. Note that while indexing can make SELECT queries much faster, it will slow down INSERT, UPDATE and DELETE queries.

For complex DB queries, it is recommended that you create database views to save the query parsing and preparation time.

Last but not least, use `LIMIT` in your `SELECT` queries. This avoids fetching an overwhelming amount of data from the database
and exhausting the memory allocated to PHP.


## Using Plain Arrays <span id="using-arrays"></span>

Although [Active Record](db-active-record.md) is very convenient to use, it is not as efficient as using plain arrays
when you need to retrieve a large amount of data from database. In this case, you may consider calling `asArray()`
while using Active Record to query data so that the retrieved data is represented as arrays instead of bulky Active
Record objects. For example,

```php
class PostController extends Controller
{
    public function actionIndex()
    {
        $posts = Post::find()->limit(100)->asArray()->all();
        
        return $this->render('index', ['posts' => $posts]);
    }
}
```

In the above code, `$posts` will be populated as an array of table rows. Each row is a plain array. To access
the `title` column of the i-th row, you may use the expression `$posts[$i]['title']`.

You may also use [DAO](db-dao.md) to build queries and retrieve data in plain arrays. 


## Optimizing Composer Autoloader <span id="optimizing-autoloader"></span>

Because Composer autoloader is used to include most third-party class files, you should consider optimizing it
by executing the following command:

```
composer dumpautoload -o
```


## Processing Data Offline <span id="processing-data-offline"></span>

When a request involves some resource intensive operations, you should think of ways to perform those operations
in offline mode without having users wait for them to finish.

There are two methods to process data offline: pull and push. 

In the pull method, whenever a request involves some complex operation, you create a task and save it in a persistent 
storage, such as database. You then use a separate process (such as a cron job) to pull the tasks and process them.
This method is easy to implement, but it has some drawbacks. For example, the task process needs to periodically pull
from the task storage. If the pull frequency is too low, the tasks may be processed with great delay; but if the frequency
is too high, it will introduce high overhead.

In the push method, you would use a message queue (e.g. RabbitMQ, ActiveMQ, Amazon SQS, etc.) to manage the tasks. 
Whenever a new task is put on the queue, it will initiate or notify the task handling process to trigger the task processing.


## Performance Profiling <span id="performance-profiling"></span>

You should profile your code to find out the performance bottlenecks and take appropriate measures accordingly.
The following profiling tools may be useful:

- [Yii debug toolbar and debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
- [XDebug profiler](http://xdebug.org/docs/profiler)
- [XHProf](http://www.php.net/manual/en/book.xhprof.php)
