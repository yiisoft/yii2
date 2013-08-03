Performance Tuning
==================

Application performance consists of two parts. First is the framework performance
and the second is the application itself. Yii has a pretty low performance impact
on your application out of the box and can be fine-tuned further for production
environment. As for the application, we'll provide some of the best practices
along with examples on how to apply them to Yii.

Preparing framework for production
----------------------------------

### Disabling Debug Mode

First thing you should do before deploying your application to production environment
is to disable debug mode. A Yii application runs in debug mode if the constant
`YII_DEBUG` is defined as `true` in `index.php` so to disable debug the following
should be in your `index.php`:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

Debug mode is very useful during development stage, but it would impact performance
because some components cause extra burden in debug mode. For example, the message
logger may record additional debug information for every message being logged.

### Enabling PHP opcode cache

Enabling the PHP opcode cache improves any PHP application performance and lowers
memory usage significantly. Yii is no exception. It was tested with
[APC PHP extension](http://php.net/manual/en/book.apc.php) that caches
and optimizes PHP intermediate code and avoids the time spent in parsing PHP
scripts for every incoming request.

### Turning on ActiveRecord database schema caching

If the application is using Active Record, we should turn on the schema caching
to save the time of parsing database schema. This can be done by setting the
`Connection::enableSchemaCache` property to be `true` via application configuration
`protected/config/main.php`:

```php
return array(
	// ...
	'components' => array(
		// ...
		'db' => array(
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=mydatabase',
			'username' => 'root',
			'password' => '',
			'enableSchemaCache' => true,

			// Duration of schema cache.
			// 'schemaCacheDuration' => 3600,

			// Name of the cache component used. Default is 'cache'.
			//'schemaCache' => 'cache',
		),
		'cache' => array(
			'class' => 'yii\caching\FileCache',
		),
	),
);
```

Note that `cache` application component should be configured.

### Combining and Minimizing Assets

TBD

### Using better storage for sessions

By default PHP uses files to handle sessions. It is OK for development and
small projects but when it comes to handling concurrent requests it's better to
switch to another storage such as database. You can do so by configuring your
application via `protected/config/main.php`:

```php
return array(
	// ...
	'components' => array(
		'session' => array(
			'class' => 'yii\web\DbSession',

			// Set the following if want to use DB component other than
			// default 'db'.
			// 'db' => 'mydb',

			// To override default session table set the following
			// 'sessionTable' => 'my_session',
		),
	),
);
```

You can use `CacheSession` to store sessions using cache. Note that some
cache storage such as memcached has no guarantee that session data will not
be lost leading to unexpected logouts.

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


### Leveraging HTTP to save procesing time and bandwidth

TBD

### Database Optimization

Fetching data from database is often the main performance bottleneck in
a Web application. Although using caching may alleviate the performance hit,
it does not fully solve the problem. When the database contains enormous data
and the cached data is invalid, fetching the latest data could be prohibitively
expensive without proper database and query design.

Design index wisely in a database. Indexing can make SELECT queries much faster,
but it may slow down INSERT, UPDATE or DELETE queries.

For complex queries, it is recommended to create a database view for it instead
of issuing the queries inside the PHP code and asking DBMS to parse them repetitively.

Do not overuse Active Record. Although Active Record is good at modelling data
in an OOP fashion, it actually degrades performance due to the fact that it needs
to create one or several objects to represent each row of query result. For data
intensive applications, using DAO or database APIs at lower level could be
a better choice.

Last but not least, use LIMIT in your SELECT queries. This avoids fetching
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
		return $this->render('index', array(
			'posts' => $posts,
		));
	}
}
```

In the view you should access fields of each individual record from `$posts` as array:

```php
foreach ($posts as $post) {
	echo $post['title']."<br>";
}
```

Note that you can use array notation even if `asArray` wasn't specified and you're
working with AR objects.

### Processing data in background

In order to respond to user requests faster you can process heavy parts of the
request later if there's no need for immediate response.

- Cron jobs + console.
- queues + handlers.

TBD
