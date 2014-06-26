数据缓存
============

数据缓存是指将一些 PHP 变量存储到缓存中，使用时再从缓存中取回。
它也是更高级缓存特性的基础，例如 [查询缓存](#query-caching) 和 [内容缓存](caching-content.md).

如下代码是一个典型的数据缓存使用模式。其中 `$cache` 代表一个 [缓存组件](#cache-components):

```php
// 尝试从缓存中取出 $data 
$data = $cache->get($key);

if ($data === false) {

    // $data 在缓存中没有找到，则重新计算它的值

    // 将 $data 存放到缓存供下次使用
    $cache->set($key, $data);
}

// 这儿 $data 可以使用了。
```


## 缓存组件 <a name="cache-components"></a>

数据缓存需要称作“*缓存组件*”的东西提供支持，它代表着各种缓存存储器，例如内存，文件，数据库。

缓存组件通常注册为应用程序组件，这样它们就可以接受全局性配置和调用。如下代码演示了如何配置 `cache`
应用程序组件使用两个 [memcached](http://memcached.org/) 服务器:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'server1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'server2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

然后你就可以通过  `Yii::$app->cache` 访问上面的缓存组件了。

由于所有缓存组件都支持同样的一系列 API ，你并不需要修改使用缓存的那些代码就能直接替换为其他低层缓存组件，
只需在应用程序配置中重新配置一下就可以。例如，你可以将上述配置修改为使用 [[yii\caching\ApcCache|APC cache]]:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Tip: 你可以注册多个缓存组件，很多依赖缓存的类默认调用名为 `cache` 的组件（例如 [[yii\web\UrlManager]]）。


### 支持的缓存存储器 <a name="supported-cache-storage"></a>

Yii 支持一系列缓存存储器，概况如下：

* [[yii\caching\ApcCache]]: 使用 PHP [APC](http://php.net/manual/en/book.apc.php) 扩展。这个选项可以认为是集中式应用程序环境中（例如：单一服务器，没有独立的负载均衡器等）最快的缓存方案。
* [[yii\caching\DbCache]]: 使用一个数据库的表存储缓存数据。要使用这个缓存，你必须创建一个 [[yii\caching\DbCache::cacheTable]] 对应的表。
* [[yii\caching\DummyCache]]: 仅作为一个缓存占位符，不实现任何真正的缓存功能。
  这个组件的目的是为了简化那些需要查询缓存有效性的代码。例如，在开发中如果服务器没有实际的缓存支持，你就可以用它配置一个缓存组件。
  一个真正的缓存服务启用后，就可以切换为使用相应的缓存组件。两种条件下你都可以使用同样的代码
  `Yii::$app->cache->get($key)` 尝试从缓存中获取数据而不用担心
  `Yii::$app->cache` 可能是 `null`。
* [[yii\caching\FileCache]]: 使用标准文件存储缓存数据。这个特别适用于缓存大块数据，例如一个网页的内容。
* [[yii\caching\MemCache]]: 使用 PHP [memcache](http://php.net/manual/en/book.memcache.php) 和
  [memcached](http://php.net/manual/en/book.memcached.php) 扩展。这个选项可以认为是分布式应用程序环境中（例如：多台服务器，有负载均衡等）最快的缓存方案。
* [[yii\redis\Cache]]: 实现了一个基于 [Redis](http://redis.io/) 键值对存储器的缓存组件（需要 redis 2.6.12 及以上版本的支持 ）。
* [[yii\caching\WinCache]]: 使用 PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)
  ([另可参考](http://php.net/manual/en/book.wincache.php)) 扩展.
* [[yii\caching\XCache]]: 使用 PHP [XCache](http://xcache.lighttpd.net/) 扩展。
* [[yii\caching\ZendDataCache]]: 使用
  [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  作为底层缓存介质。


> Tip: 你可以在同一个应用程序中使用不同的缓存存储器。一个常见的策略是使用基于内存的缓存存储器存储小而常用的数据（例如：统计数据），使用基于文件或数据库的缓存存储器存储大而不太常用的数据（例如：网页内容）。


## Cache APIs <a name="cache-apis"></a>

All cache components have the same base class [[yii\caching\Cache]] and thus support the following APIs:

* [[yii\caching\Cache::get()|get()]]: retrieves a data item from cache with a specified key. A false
  value will be returned if the data item is not found in the cache or is expired/invalidated.
* [[yii\caching\Cache::set()|set()]]: stores a data item identified by a key in cache.
* [[yii\caching\Cache::add()|add()]]: stores a data item identified by a key in cache if the key is not found in the cache.
* [[yii\caching\Cache::mget()|mget()]]: retrieves multiple data items from cache with the specified keys.
* [[yii\caching\Cache::mset()|mset()]]: stores multiple data items in cache. Each item is identified by a key.
* [[yii\caching\Cache::madd()|madd()]]: stores multiple data items in cache. Each item is identified by a key.
  If a key already exists in the cache, the data item will be skipped.
* [[yii\caching\Cache::exists()|exists()]]: returns a value indicating whether the specified key is found in the cache.
* [[yii\caching\Cache::delete()|delete()]]: removes a data item identified by a key from the cache.
* [[yii\caching\Cache::flush()|flush()]]: removes all data items from the cache.

Some cache storage, such as MemCache, APC, support retrieving multiple cached values in a batch mode,
which may reduce the overhead involved in retrieving cached data. The APIs [[yii\caching\Cache::mget()|mget()]]
and [[yii\caching\Cache::madd()|madd()]] are provided to exploit this feature. In case the underlying cache storage
does not support this feature, it will be simulated.

Because [[yii\caching\Cache]] implements `ArrayAccess`, a cache component can be used liked an array. The followings
are some examples:

```php
$cache['var1'] = $value1;  // equivalent to: $cache->set('var1', $value1);
$value2 = $cache['var2'];  // equivalent to: $value2 = $cache->get('var2');
```


### Cache Keys <a name="cache-keys"></a>

Each data item stored in cache is uniquely identified by a key. When you store a data item in cache,
you have to specify a key for it. Later when you retrieve the data item from cache, you should provide
the corresponding key.

You may use a string or an arbitrary value as a cache key. When a key is not a string, it will be automatically
serialized into a string.

A common strategy of defining a cache key is to include all determining factors in terms of an array.
For example, [[yii\db\Schema]] uses the following key to cache schema information about a database table:

```php
[
    __CLASS__,              // schema class name
    $this->db->dsn,         // DB connection data source name
    $this->db->username,    // DB connection login user
    $name,                  // table name
];
```

As you can see, the key includes all necessary information needed to uniquely specify a database table.

When the same cache storage is used by different applications, you should specify a unique cache key prefix
for each application to avoid conflicts of cache keys. This can be done by configuring the [[yii\caching\Cache::keyPrefix]]
property. For example, in the application configuration you can write the following code:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // a unique cache key prefix
    ],
],
```

To ensure interoperability, only alphanumeric characters should be used.


### Cache Expiration <a name="cache-expiration"></a>

A data item stored in a cache will remain there forever unless it is removed because of some caching policy
enforcement (e.g. caching space is full and the oldest data are removed). To change this behavior, you can provide
an expiration parameter when calling [[yii\caching\Cache::set()|set()]] to store a data item. The parameter
indicates for how many seconds the data item can remain valid in the cache. When you call
[[yii\caching\Cache::get()|get()]] to retrieve the data item, if it has passed the expiration time, the method
will return false, indicating the data item is not found in the cache. For example,

```php
// keep the data in cache for at most 45 seconds
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data is expired or is not found in the cache
}
```


### Cache Dependencies <a name="cache-dependencies"></a>

Besides expiration setting, cached data item may also be invalidated by changes of the so-called *cache dependencies*.
For example, [[yii\caching\FileDependency]] represents the dependency of a file's modification time.
When this dependency changes, it means the corresponding file is modified. As a result, any outdated
file content found in the cache should be invalidated and the [[yii\caching\Cache::get()|get()]] call
should return false.

Cache dependencies are represented as objects of [[yii\caching\Dependency]] descendant classes. When you call
[[yii\caching\Cache::set()|set()]] to store a data item in the cache, you can pass along an associated cache
dependency object. For example,

```php
// Create a dependency on the modification time of file example.txt.
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// The data will expire in 30 seconds.
// It may also be invalidated earlier if example.txt is modified.
$cache->set($key, $data, 30, $dependency);

// The cache will check if the data has expired.
// It will also check if the associated dependency was changed.
// It will return false if any of these conditions is met.
$data = $cache->get($key);
```

Below is a summary of the available cache dependencies:

- [[yii\caching\ChainedDependency]]: the dependency is changed if any of the dependencies on the chain is changed.
- [[yii\caching\DbDependency]]: the dependency is changed if the query result of the specified SQL statement is changed.
- [[yii\caching\ExpressionDependency]]: the dependency is changed if the result of the specified PHP expression is changed.
- [[yii\caching\FileDependency]]: the dependency is changed if the file's last modification time is changed.
- [[yii\caching\GroupDependency]]: marks a cached data item with a group name. You may invalidate the cached data items
  with the same group name all at once by calling [[yii\caching\GroupDependency::invalidate()]].


## Query Caching <a name="query-caching"></a>

Query caching is a special caching feature built on top of data caching. It is provided to cache the result
of database queries.

Query caching requires a [[yii\db\Connection|DB connection]] and a valid `cache` application component.
The basic usage of query caching is as follows, assuming `$db` is a [[yii\db\Connection]] instance:

```php
$duration = 60;     // cache query results for 60 seconds.
$dependency = ...;  // optional dependency

$db->beginCache($duration, $dependency);

// ...performs DB queries here...

$db->endCache();
```

As you can see, any SQL queries in between the `beginCache()` and `endCache()` calls will be cached.
If the result of the same query is found valid in the cache, the query will be skipped and the result
will be served from the cache instead.

Query caching can be used for [DAO](db-dao.md) as well as [ActiveRecord](db-active-record.md).

> Info: Some DBMS (e.g. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
  also support query caching on the DB server side. You may choose to use either query caching mechanism.
  The query caching described above has the advantage that you may specify flexible cache dependencies
  and are potentially more efficient.


### Configurations <a name="query-caching-configs"></a>

Query caching has two two configurable options through [[yii\db\Connection]]:

* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: this represents the number of seconds
  that a query result can remain valid in the cache. The duration will be overwritten if you call
  [[yii\db\Connection::beginCache()]] with an explicit duration parameter.
* [[yii\db\Connection::queryCache|queryCache]]: this represents the ID of the cache application component.
  It defaults to `'cache'`. Query caching is enabled only when there is a valid cache application component.


### Limitations <a name="query-caching-limitations"></a>

Query caching does not work with query results that contain resource handles. For example,
when using the `BLOB` column type in some DBMS, the query result will return a resource
handle for the column data.

Some caching storage has size limitation. For example, memcache limits the maximum size
of each entry to be 1MB. Therefore, if the size of a query result exceeds this limit,
the caching will fail.

