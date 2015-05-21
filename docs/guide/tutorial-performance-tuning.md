Performance Tuning
==================

> Note: This section is under development.

The performance of your web application is based upon two parts. First is the framework performance
and the second is the application itself. Yii has a pretty low performance impact
on your application out of the box and can be fine-tuned further for production
environment. As for the application, we'll provide some of the best practices
along with examples on how to apply them to Yii.


## Optimizing PHP Environment <span id="optimizing-php"></span>

A well configured environment to run PHP application really matters. In order to get maximum performance,

- Use the latest stable PHP version. Major releases of PHP may bring significant performance improvement.
- Enable bytecode caching with [Opcache](http://php.net/opcache) (PHP 5.5 or later) or [APC](http://ru2.php.net/apc) 
  (PHP 5.4 or earlier). Bytecode caching avoids the time spent in parsing and including PHP scripts for every
  incoming request.


## Disabling Debug Mode <span id="disable-debug"></span>

When running an application in production, you should disable the debug mode. Yii uses the value of a constant
named `YII_DEBUG` to indicate whether the debug mode should be enabled. When the debug mode is enabled, Yii
will take extra time to generate and record extra debugging information.

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
about a DB table without requiring you to manually describe them. Active Record obtains these information by executing 
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


## Using better storage for sessions

By default PHP uses files to handle sessions. It is OK for development and
small projects. But when it comes to handling concurrent requests, it's better to
switch to another storage such as database. You can do so by configuring your
application via `config/web.php`:

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

You can use `CacheSession` to store sessions using cache. Note that some
cache storage such as memcached has no guarantee that session data will not
be lost, and it would lead to unexpected logouts.

If you have [Redis](http://redis.io/) on your server, it's highly recommended as session storage.


## Optimizing Databases <span id="optimizing-databases"></span>

Execute DB queries and fetching data from databases is often the main performance bottleneck in
a Web application. Although using [data caching](caching-data.md) techniques may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous amount of data and the cached data is invalid, 
fetching the latest data could be prohibitively expensive without proper database and query design.

A general technique to improve the performance of DB queries is to create indices for table columns that
need to be filtered by. For example, if you need to look for a user record by `username`, you should create an index
on `username`. Note that while indexing can make SELECT queries much faster, it will slow down INSERT, UPDATE and DELETE queries.

For complex DB queries, it is recommended that you create database views to save the query parsing and preparation time.

Last but not least, use `LIMIT` in your `SELECT` queries. This avoids fetching overwhelming amount of data from database
and exhausting the memory allocated to PHP.


## Using Plain Arrays <span id="using-arrays"></span>

Although [Active Record](db-active-record.md) is very convenient to use, it is not as efficient as using plain arrays
when you need to retrieve large amount of data from database. In this case, you may consider calling `asArray()`
while using Active Record to query data so that the retrieved data are represented as arrays instead of bulky Active
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


## Composer autoloader optimization

In order to improve overall performance you can execute `composer dumpautoload -o` to optimize Composer autoloader.

## Processing data in offline mode

In order to respond to user requests faster you can process heavy parts of the
request later if there's no need for immediate response.

There are two common ways to achieve it: cron job processing and specialized queues.

In the first case we need to save the data that we want to process later to a persistent storage
such as database. A [console command](tutorial-console.md) that is run regularly via cron job queries
database and processes data if there's any.

The solution is OK for most cases but has one significant drawback. We aren't aware if there's data to
process before we query database, so we're either querying database quite often or have a slight delay
between each data processing.

This issue could be solved by queue and job servers such RabbitMQ, ActiveMQ, Amazon SQS and more.
In this case instead of writing data to persistent storage you're queueing it via APIs provided
by queue or job server. Processing is often put into job handler class. Job from the queue is executed
right after all jobs before it are done.

## If nothing helps

If nothing helps, never assume what may fix performance problem. Always profile your code instead before changing
anything. The following tools may be helpful:

- [Yii debug toolbar and debugger](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
- [XDebug profiler](http://xdebug.org/docs/profiler)
- [XHProf](http://www.php.net/manual/en/book.xhprof.php)
