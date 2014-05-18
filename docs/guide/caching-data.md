Data Caching
------------

Data caching is about storing some PHP variable in cache and retrieving it later from cache. For this purpose,
the cache component base class [[yii\caching\Cache]] provides two methods that are used most of the time:
[[yii\caching\Cache::set()|set()]] and [[yii\caching\Cache::get()|get()]]. Note, only serializable variables and objects could be cached successfully.

To store a variable `$value` in cache, we choose a unique `$key` and call [[yii\caching\Cache::set()|set()]] to store it:

```php
Yii::$app->cache->set($key, $value);
```

The cached data will remain in the cache forever unless it is removed because of some caching policy
(e.g. caching space is full and the oldest data are removed). To change this behavior, we can also supply
an expiration parameter when calling [[yii\caching\Cache::set()|set()]] so that the data will be removed from the cache after
a certain period of time:

```php
// keep the value in cache for at most 45 seconds
Yii::$app->cache->set($key, $value, 45);
```

Later when we need to access this variable (in either the same or a different web request), we call [[yii\caching\Cache::get()|get()]]
with the key to retrieve it from cache. If the value returned is `false`, it means the value is not available
in cache and we should regenerate it:

```php
public function getCachedData()
{
    $key = /* generate unique key here */;
    $value = Yii::$app->cache->get($key);
    if ($value === false) {
        $value = /* regenerate value because it is not found in cache and then save it in cache for later use */;
        Yii::$app->cache->set($key, $value);
    }
    return $value;
}
```

This is the common pattern of arbitrary data caching for general use.

When choosing the key for a variable to be cached, make sure the key is unique among all other variables that
may be cached in the application. It is **NOT** required that the key is unique across applications because
the cache component is intelligent enough to differentiate keys for different applications.

Some cache storages, such as MemCache, APC, support retrieving multiple cached values in a batch mode,
which may reduce the overhead involved in retrieving cached data. A method named [[yii\caching\Cache::mget()|mget()]] is provided
to exploit this feature. In case the underlying cache storage does not support this feature,
[[yii\caching\Cache::mget()|mget()]] will still simulate it.

To remove a cached value from cache, call [[yii\caching\Cache::delete()|delete()]]; and to remove everything from cache, call
[[yii\caching\Cache::flush()|flush()]].
Be very careful when calling [[yii\caching\Cache::flush()|flush()]] because it also removes cached data that are from
other applications if the cache is shared among different applications.

Note, because [[yii\caching\Cache]] implements `ArrayAccess`, a cache component can be used liked an array. The followings
are some examples:

```php
$cache = Yii::$app->cache;
$cache['var1'] = $value1;  // equivalent to: $cache->set('var1', $value1);
$value2 = $cache['var2'];  // equivalent to: $value2 = $cache->get('var2');
```

### Cache Dependency

Besides expiration setting, cached data may also be invalidated according to some dependency changes. For example, if we
are caching the content of some file and the file is changed, we should invalidate the cached copy and read the latest
content from the file instead of the cache.

We represent a dependency as an instance of [[yii\caching\Dependency]] or its child class. We pass the dependency
instance along with the data to be cached when calling [[yii\caching\Cache::set()|set()]].

```php
use yii\caching\FileDependency;

// the value will expire in 30 seconds
// it may also be invalidated earlier if the dependent file is changed
Yii::$app->cache->set($id, $value, 30, new FileDependency(['fileName' => 'example.txt']));
```

Now if we retrieve $value from cache by calling `get()`, the dependency will be evaluated and if it is changed, we will
get a false value, indicating the data needs to be regenerated.

Below is a summary of the available cache dependencies:

- [[yii\caching\FileDependency]]: the dependency is changed if the file's last modification time is changed.
- [[yii\caching\GroupDependency]]: marks a cached data item with a group name. You may invalidate the cached data items
  with the same group name all at once by calling [[yii\caching\GroupDependency::invalidate()]].
- [[yii\caching\DbDependency]]: the dependency is changed if the query result of the specified SQL statement is changed.
- [[yii\caching\ChainedDependency]]: the dependency is changed if any of the dependencies on the chain is changed.
- [[yii\caching\ExpressionDependency]]: the dependency is changed if the result of the specified PHP expression is
  changed.

### Query Caching

For caching the result of database queries you can wrap them in calls to [[yii\db\Connection::beginCache()]]
and [[yii\db\Connection::endCache()]]:

```php
$connection->beginCache(60); // cache all query results for 60 seconds.
// your db query code here...
$connection->endCache();
```


Data Caching
============

Data caching is about storing some PHP variable in cache and retrieving it
later from cache. For this purpose, the cache component base class [CCache]
provides two methods that are used most of the time: [set()|CCache::set]
and [get()|CCache::get].

To store a variable `$value` in cache, we choose a unique ID and call
[set()|CCache::set] to store it:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

The cached data will remain in the cache forever unless it is removed
because of some caching policy (e.g. caching space is full and the oldest
data are removed). To change this behavior, we can also supply an
expiration parameter when calling [set()|CCache::set] so that the data will
be removed from the cache after, at most, that period of time:

~~~
[php]
// keep the value in cache for at most 30 seconds
Yii::app()->cache->set($id, $value, 30);
~~~

Later when we need to access this variable (in either the same or a
different Web request), we call [get()|CCache::get] with the ID to retrieve
it from cache. If the returned value is false, it means the value is not
available in cache and we have to regenerate it.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// regenerate $value because it is not found in cache
	// and save it in cache for later use:
	// Yii::app()->cache->set($id,$value);
}
~~~

When choosing the ID for a variable to be cached, make sure the ID is
unique among all other variables that may be cached in the application. It
is NOT required that the ID is unique across applications because the cache
component is intelligent enough to differentiate IDs for different
applications.

Some cache storages, such as MemCache, APC, support retrieving
multiple cached values in a batch mode, which may reduce the overhead involved
in retrieving cached data. A method named
[mget()|CCache::mget] is provided to achieve this feature. In case the underlying
cache storage does not support this feature, [mget()|CCache::mget] will still
simulate it.

To remove a cached value from cache, call [delete()|CCache::delete]; and
to remove everything from cache, call [flush()|CCache::flush]. Be very
careful when calling [flush()|CCache::flush] because it also removes cached
data that are from other applications.

> Tip: Because [CCache] implements `ArrayAccess`, a cache component can be
> used liked an array. The followings are some examples:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // equivalent to: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // equivalent to: $value2=$cache->get('var2');
> ~~~

Cache Dependency
----------------

Besides expiration setting, cached data may also be invalidated according
to some dependency changes. For example, if we are caching the content of
some file and the file is changed, we should invalidate the cached copy and
read the latest content from the file instead of the cache.

We represent a dependency as an instance of [CCacheDependency] or its
child class. We pass the dependency instance along with the data to be
cached when calling [set()|CCache::set].

~~~
[php]
// the value will expire in 30 seconds
// it may also be invalidated earlier if the dependent file is changed
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

Now if we retrieve `$value` from cache by calling [get()|CCache::get], the
dependency will be evaluated and if it is changed, we will get a false
value, indicating the data needs to be regenerated.

Below is a summary of the available cache dependencies:

   - [CFileCacheDependency]: the dependency is changed if the file's last
modification time is changed.

   - [CDirectoryCacheDependency]: the dependency is changed if any of the
files under the directory and its subdirectories is changed.

   - [CDbCacheDependency]: the dependency is changed if the query result
of the specified SQL statement is changed.

   - [CGlobalStateCacheDependency]: the dependency is changed if the value
of the specified global state is changed. A global state is a variable that
is persistent across multiple requests and multiple sessions in an
application. It is defined via [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: the dependency is changed if any of the
dependencies on the chain is changed.

   - [CExpressionDependency]: the dependency is changed if the result of
the specified PHP expression is changed.


Query Caching
-------------

Since version 1.1.7, Yii has added support for query caching.
Built on top of data caching, query caching stores the result of a DB query
in cache and may thus save the DB query execution time if the same query is requested
in future, as the result can be directly served from the cache.

> Info: Some DBMS (e.g. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
> also support query caching on the DB server side. Compared with the server-side
> query caching, the same feature we support here offers more flexibility and
> may be potentially more efficient.


### Enabling Query Caching

To enable query caching, make sure [CDbConnection::queryCacheID] refers to the ID of a valid
cache application component (it defaults to `cache`).


### Using Query Caching with DAO

To use query caching, we call the [CDbConnection::cache()] method when we perform DB queries.
The following is an example:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

When running the above statements, Yii will first check if the cache contains a valid
result for the SQL statement to be executed. This is done by checking the following three conditions:

- if the cache contains an entry indexed by the SQL statement.
- if the entry is not expired (less than 1000 seconds since it was first saved in the cache).
- if the dependency has not changed (the maximum `update_time` value is the same as when
the query result was saved in the cache).

If all of the above conditions are satisfied, the cached result will be returned directly from the cache.
Otherwise, the SQL statement will be sent to the DB server for execution, and the corresponding
result will be saved in the cache and returned.


### Using Query Caching with ActiveRecord

Query caching can also be used with [Active Record](/doc/guide/database.ar).
To do so, we call a similar [CActiveRecord::cache()] method like the following:

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// relational AR query
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

The `cache()` method here is essentially a shortcut to [CDbConnection::cache()].
Internally, when executing the SQL statement generated by ActiveRecord, Yii will
attempt to use query caching as we described in the last subsection.


### Caching Multiple Queries

By default, each time we call the `cache()` method (of either [CDbConnection] or [CActiveRecord]),
it will mark the next SQL query to be cached. Any other SQL queries will NOT be cached
unless we call `cache()` again. For example,

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// query caching will NOT be used
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

By supplying an extra `$queryCount` parameter to the `cache()` method, we can enforce
multiple queries to use query caching. In the following example, when we call `cache()`,
we specify that query caching should be used for the next 2 queries:

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// query caching WILL be used
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

As we know, when performing a relational AR query, it is possible several SQL queries will
be executed (by checking the [log messages](/doc/guide/topics.logging)).
For example, if the relationship between `Post` and `Comment` is `HAS_MANY`,
then the following code will actually execute two DB queries:

- it first selects the posts limited by 20;
- it then selects the comments for the previously selected posts.

~~~
[php]
$posts = Post::model()->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

If we use query caching as follows, only the first DB query will be cached:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

In order to cache both DB queries, we need supply the extra parameter indicating how
many DB queries we want to cache next:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency, 2)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~


### Limitations

Query caching does not work with query results that contain resource handles. For example,
when using the `BLOB` column type in some DBMS, the query result will return a resource
handle for the column data.

Some caching storage has size limitation. For example, memcache limits the maximum size
of each entry to be 1MB. Therefore, if the size of a query result exceeds this limit,
the caching will fail.


<div class="revision">$Id$</div>
