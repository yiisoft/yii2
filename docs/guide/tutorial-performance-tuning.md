Performance Tuning
==================

> Note: This section is under development.

The performance of your web application is based upon two parts. First is the framework performance
and the second is the application itself. Yii has a pretty low performance impact
on your application out of the box and can be fine-tuned further for production
environment. As for the application, we'll provide some of the best practices
along with examples on how to apply them to Yii.

Preparing environment
---------------------

A well configured environment to run PHP application really matters. In order to get maximum performance:

- Always use the latest stable PHP version. Each major release brings significant performance improvements and reduced
  memory usage.
- Use [APC](http://ru2.php.net/apc) for PHP 5.4 and less or [Opcache](http://php.net/opcache) for PHP 5.5 and more. It
  gives a very good performance boost.

Preparing framework for production
----------------------------------

### Disabling Debug Mode

First thing you should do before deploying your application to production environment
is to disable debug mode. A Yii application runs in debug mode if the constant
`YII_DEBUG` is defined as `true` in `index.php`.
So, in order to disable debug mode, the following should be in your `index.php`:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

Debug mode is very useful during development stage, but it would impact performance
because some components cause extra burden in debug mode. For example, the message
logger may record additional debug information for every message being logged.

### Enabling PHP opcode cache

Enabling the PHP opcode cache improves any PHP application performance and lowers
memory usage significantly. Yii is no exception. It was tested with both
[PHP 5.5 OPcache](http://php.net/manual/en/book.opcache.php) and
[APC PHP extension](http://php.net/manual/en/book.apc.php). Both cache
optimize PHP intermediate code and avoid the time spent in parsing PHP
scripts for every incoming request.

### Turning on ActiveRecord database schema caching

If the application is using Active Record, we should turn on the schema caching
to save the time of parsing database schema. This can be done by setting the
`Connection::enableSchemaCache` property to be `true` via application configuration
`config/web.php`:

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mydatabase',
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,

            // Duration of schema cache.
            // 'schemaCacheDuration' => 3600,

            // Name of the cache component used. Default is 'cache'.
            //'schemaCache' => 'cache',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
```

Note that the `cache` [application component](structure-application-components.md) should be configured.

### Combining and Minimizing Assets

It is possible to combine and minimize assets, typically JavaScript and CSS, in order to slightly improve page load
time and therefore deliver better experience for end user of your application.

In order to learn how it can be achieved, refer to [assets](structure-assets.md) guide section.

### Using better storage for sessions

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

Improving application
---------------------

### Using Serverside Caching Techniques

As described in the Caching section, Yii provides several caching solutions that
may improve the performance of a Web application significantly. If the generation
of some data takes long time, we can use the data caching approach to reduce the
data generation frequency; If a portion of page remains relatively static, we
can use the fragment caching approach to reduce its rendering frequency;
If a whole page remains relative static, we can use the page caching approach to
save the rendering cost for the whole page.


### Leveraging HTTP caching to save processing time and bandwidth

Leveraging HTTP caching saves both processing time, bandwidth and resources significantly. It can be implemented by
sending either `ETag` or `Last-Modified` header in your application response. If browser is implemented according to
HTTP specification (most browsers are), content will be fetched only if it is different from what it was prevously.

Forming proper headers is time consuming task so Yii provides a shortcut in form of controller filter
[[yii\filters\HttpCache]]. Using it is very easy. In a controller you need to implement `behaviors` method like
the following:

```php
public function behaviors()
{
    return [
        'httpCache' => [
            'class' => \yii\filters\HttpCache::className(),
            'only' => ['list'],
            'lastModified' => function ($action, $params) {
                $q = new Query();
                return strtotime($q->from('users')->max('updated_timestamp'));
            },
            // 'etagSeed' => function ($action, $params) {
                // return // generate etag seed here
            //}
        ],
    ];
}
```

In the code above one can use either `etagSeed` or `lastModified`. Implementing both isn't necessary. The goal is to
determine if content was modified in a way that is cheaper than fetching and rendering that content. `lastModified`
should return unix timestamp of the last content modification while `etagSeed` should return a string that is then
used to generate `ETag` header value.


### Database Optimization

Fetching data from database is often the main performance bottleneck in
a Web application.
Although using [caching](caching.md#Query-Caching) may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous data
and the cached data is invalid, fetching the latest data could be prohibitively
expensive without proper database and query design.

Design index wisely in a database. Indexing can make SELECT queries much faster,
but it may slow down INSERT, UPDATE or DELETE queries.

For complex queries, it is recommended to create a database view for it instead
of issuing the queries inside the PHP code and asking DBMS to parse them repetitively.

Do not overuse Active Record. Although Active Record is good at modeling data
in an OOP fashion, it actually degrades performance due to the fact that it needs
to create one or several objects to represent each row of query result. For data
intensive applications, using DAO or database APIs at lower level could be
a better choice.

Last but not least, use `LIMIT` in your `SELECT` queries. This avoids fetching
overwhelming data from database and exhausting the memory allocated to PHP.

### Using asArray

A good way to save memory and processing time on read-only pages is to use
ActiveRecord's `asArray` method.

```php
class PostController extends Controller
{
    public function actionIndex()
    {
        $posts = Post::find()->orderBy('id DESC')->limit(100)->asArray()->all();
        return $this->render('index', ['posts' => $posts]);
    }
}
```

In the view you should access fields of each individual record from `$posts` as array:

```php
foreach ($posts as $post) {
    echo $post['title'] . "<br>";
}
```

Note that you can use array notation even if `asArray` wasn't specified and you're
working with AR objects.

### Composer autoloader optimization

In order to improve overall performance you can execute `composer dumpautoload -o` to optimize Composer autoloader.

### Processing data in background

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

### If nothing helps

If nothing helps, never assume what may fix performance problem. Always profile your code instead before changing
anything. The following tools may be helpful:

- [Yii debug toolbar and debugger](tool-debugger.md)
- [XDebug profiler](http://xdebug.org/docs/profiler)
- [XHProf](http://www.php.net/manual/en/book.xhprof.php)
