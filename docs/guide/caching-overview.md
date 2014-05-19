Caching
=======

Caching is a cheap and effective way to improve the performance of a Web application. By storing relatively
static data in cache and serving it from cache when requested, the application saves the time that would be
required to generate the data from scratch.

Caching can occur at different levels and places in a Web application. On the server side, at the lower level,
cache may be used to store basic data, such as a list of most recent article information fetched from database;
and at the higher level, cache may be used to store the page content, such as the rendering result of the most
recent articles. On the client side, HTTP caching may be used to keep most recently visited page content in
the browser cache.

Yii supports all these caching mechanisms which are described in the following sections:

* [Data caching](caching-data.md)
* [Content caching](caching-content.md)
* [HTTP caching](caching-http.md)


## Cache Components

Server-side caching (data caching and content caching) relies on the so-called *cache components*.
Each cache component represents a caching storage and provides a common set of APIs
that may be called to store data in the cache and retrieve it later.

Cache components are usually registered as application components so that they can be globally
configurable and accessible. You may register multiple cache components in a single application.
In most cases you would configure at least the [[yii\base\Application::getCache()|cache]] component
because it is the default cache component being used by most cache-dependent classes, such as [[yii\web\UrlManager]].

The following code shows how to configure the `cache` application component to
use [memcached](http://memcached.org/) with two cache servers:

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

You may access the `cache` component by using the expression `Yii::$app->cache`.

The following is a summary of the built-in cache components supported by Yii:

* [[yii\caching\ApcCache]]: uses PHP [APC](http://php.net/manual/en/book.apc.php) extension. This option can be
  considered as the fastest one when dealing with cache for a centralized thick application (e.g. one
  server, no dedicated load balancers, etc.).

* [[yii\caching\DbCache]]: uses a database table to store cached data. To use this cache, you must
  create a table as specified in [[yii\caching\DbCache::cacheTable]].

* [[yii\caching\DummyCache]]: serves as a cache placeholder which does no real caching.
  The purpose of this component is to simplify the code that needs to check the availability of cache.
  For example, during development or if the server doesn't have actual cache support, you may configure
  a cache component to use this cache. When an actual cache support is enabled, you can switch to use
  the corresponding cache component. In both cases, you may use the same code
  `Yii::$app->cache->get($key)` to attempt retrieving data from the cache without worrying that
  `Yii::$app->cache` might be `null`.

* [[yii\caching\FileCache]]: uses standard files to store cached data. This is particular suitable
  to cache large chunk of data, such as page content.

* [[yii\caching\MemCache]]: uses PHP [memcache](http://php.net/manual/en/book.memcache.php)
  and [memcached](http://php.net/manual/en/book.memcached.php) extensions. This option can be considered as
  the fastest one when dealing with cache in a distributed applications (e.g. with several servers, load
  balancers, etc.)

* [[yii\redis\Cache]]: implements a cache component based on [Redis](http://redis.io/) key-value store
  (redis version 2.6.12 or higher is required).

* [[yii\caching\WinCache]]: uses PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)
  ([see also](http://php.net/manual/en/book.wincache.php)) extension.

* [[yii\caching\XCache]]: uses PHP [XCache](http://xcache.lighttpd.net/) extension.

* [[yii\caching\ZendDataCache]]: uses
  [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  as the underlying caching medium.

> Because all cache components extend from the same base class [[yii\caching\Cache]], you can switch to use
  a different type of cache without modifying the code that uses a cache.

