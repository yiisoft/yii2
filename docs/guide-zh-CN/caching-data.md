数据缓存
============

数据缓存是指将一些 PHP 变量存储到缓存中，使用时再从缓存中取回。它也是更高级缓存特性的基础，例如[查询缓存](#query-caching)和[内容缓存](caching-content.md)。

如下代码是一个典型的数据缓存使用模式。其中 `$cache` 指向[缓存组件](#cache-components)：

```php
// 尝试从缓存中取回 $data 
$data = $cache->get($key);

if ($data === false) {

    // $data 在缓存中没有找到，则重新计算它的值

    // 将 $data 存放到缓存供下次使用
    $cache->set($key, $data);
}

// 这儿 $data 可以使用了。
```


## 缓存组件 <span id="cache-components"></span>

数据缓存需要**缓存组件**提供支持，它代表各种缓存存储器，例如内存，文件，数据库。

缓存组件通常注册为应用程序组件，这样它们就可以在全局进行配置与访问。如下代码演示了如何配置应用程序组件 `cache` 使用两个 [memcached](http://memcached.org/) 服务器：

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

然后就可以通过  `Yii::$app->cache` 访问上面的缓存组件了。

由于所有缓存组件都支持同样的一系列 API ，并不需要修改使用缓存的业务代码就能直接替换为其他底层缓存组件，只需在应用配置中重新配置一下就可以。例如，你可以将上述配置修改为使用 [[yii\caching\ApcCache|APC cache]]:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Tip: 你可以注册多个缓存组件，很多依赖缓存的类默认调用名为 `cache` 的组件（例如 [[yii\web\UrlManager]]）。


### 支持的缓存存储器 <span id="supported-cache-storage"></span>

Yii 支持一系列缓存存储器，概况如下：

* [[yii\caching\ApcCache]]：使用 PHP [APC](http://php.net/manual/en/book.apc.php) 扩展。这个选项可以认为是集中式应用程序环境中（例如：单一服务器，没有独立的负载均衡器等）最快的缓存方案。
* [[yii\caching\DbCache]]：使用一个数据库的表存储缓存数据。要使用这个缓存，你必须创建一个与 [[yii\caching\DbCache::cacheTable]] 对应的表。
* [[yii\caching\DummyCache]]: 仅作为一个缓存占位符，不实现任何真正的缓存功能。这个组件的目的是为了简化那些需要查询缓存有效性的代码。例如，在开发中如果服务器没有实际的缓存支持，用它配置一个缓存组件。一个真正的缓存服务启用后，可以再切换为使用相应的缓存组件。两种条件下你都可以使用同样的代码 `Yii::$app->cache->get($key)` 尝试从缓存中取回数据而不用担心 `Yii::$app->cache` 可能是 `null`。
* [[yii\caching\FileCache]]：使用标准文件存储缓存数据。这个特别适用于缓存大块数据，例如一个整页的内容。
* [[yii\caching\MemCache]]：使用 PHP [memcache](http://php.net/manual/en/book.memcache.php) 和 [memcached](http://php.net/manual/en/book.memcached.php) 扩展。这个选项被看作分布式应用环境中（例如：多台服务器，有负载均衡等）最快的缓存方案。
* [[yii\redis\Cache]]：实现了一个基于 [Redis](http://redis.io/) 键值对存储器的缓存组件（需要 redis 2.6.12 及以上版本的支持 ）。
* [[yii\caching\WinCache]]：使用 PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)（[另可参考](http://php.net/manual/en/book.wincache.php)）扩展.
* [[yii\caching\XCache]]：使用 PHP [XCache](http://xcache.lighttpd.net/)扩展。
* [[yii\caching\ZendDataCache]]：使用 [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm) 作为底层缓存媒介。


> Tip: 你可以在同一个应用程序中使用不同的缓存存储器。一个常见的策略是使用基于内存的缓存存储器存储小而常用的数据（例如：统计数据），使用基于文件或数据库的缓存存储器存储大而不太常用的数据（例如：网页内容）。


## 缓存 API <span id="cache-apis"></span>

所有缓存组件都有同样的基类 [[yii\caching\Cache]] ，因此都支持如下 API：

* [[yii\caching\Cache::get()|get()]]：通过一个指定的键（key）从缓存中取回一项数据。如果该项数据不存在于缓存中或者已经过期/失效，则返回值 false。
* [[yii\caching\Cache::set()|set()]]：将一项数据指定一个键，存放到缓存中。
* [[yii\caching\Cache::add()|add()]]：如果缓存中未找到该键，则将指定数据存放到缓存中。
* [[yii\caching\Cache::mget()|mget()]]：通过指定的多个键从缓存中取回多项数据。
* [[yii\caching\Cache::mset()|mset()]]：将多项数据存储到缓存中，每项数据对应一个键。
* [[yii\caching\Cache::madd()|madd()]]：将多项数据存储到缓存中，每项数据对应一个键。如果某个键已经存在于缓存中，则该项数据会被跳过。
* [[yii\caching\Cache::exists()|exists()]]：返回一个值，指明某个键是否存在于缓存中。
* [[yii\caching\Cache::delete()|delete()]]：通过一个键，删除缓存中对应的值。
* [[yii\caching\Cache::flush()|flush()]]：删除缓存中的所有数据。

有些缓存存储器如 MemCache，APC 支持以批量模式取回缓存值，这样可以节省取回缓存数据的开支。 [[yii\caching\Cache::mget()|mget()]] 和 [[yii\caching\Cache::madd()|madd()]] API提供对该特性的支持。如果底层缓存存储器不支持该特性，Yii 也会模拟实现。

由于 [[yii\caching\Cache]] 实现了 PHP `ArrayAccess` 接口，缓存组件也可以像数组那样使用，下面是几个例子：

```php
$cache['var1'] = $value1;  // 等价于： $cache->set('var1', $value1);
$value2 = $cache['var2'];  // 等价于： $value2 = $cache->get('var2');
```


### 缓存键 <span id="cache-keys"></span>

存储在缓存中的每项数据都通过键作唯一识别。当你在缓存中存储一项数据时，必须为它指定一个键，稍后从缓存中取回数据时，也需要提供相应的键。

你可以使用一个字符串或者任意值作为一个缓存键。当键不是一个字符串时，它将会自动被序列化为一个字符串。

定义一个缓存键常见的一个策略就是在一个数组中包含所有的决定性因素。例如，[[yii\db\Schema]] 使用如下键存储一个数据表的结构信息。

```php
[
    __CLASS__,              // 结构类名
    $this->db->dsn,         // 数据源名称
    $this->db->username,    // 数据库登录用户名
    $name,                  // 表名
];
```

如你所见，该键包含了可唯一指定一个数据库表所需的所有必要信息。

当同一个缓存存储器被用于多个不同的应用时，应该为每个应用指定一个唯一的缓存键前缀以避免缓存键冲突。可以通过配置 [[yii\caching\Cache::keyPrefix]] 属性实现。例如，在应用配置中可以编写如下代码：

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // 唯一键前缀
    ],
],
```

为了确保互通性，此处只能使用字母和数字。


### 缓存过期 <span id="cache-expiration"></span>

默认情况下，缓存中的数据会永久存留，除非它被某些缓存策略强制移除（例如：缓存空间已满，最老的数据会被移除）。要改变此特性，你可以在调用 [[yii\caching\Cache::set()|set()]] 存储一项数据时提供一个过期时间参数。该参数代表这项数据在缓存中可保持有效多少秒。当你调用 [[yii\caching\Cache::get()|get()]] 取回数据时，如果它已经过了超时时间，该方法将返回 false，表明在缓存中找不到这项数据。例如：

```php
// 将数据在缓存中保留 45 秒
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data 已过期，或者在缓存中找不到
}
```


### 缓存依赖 <span id="cache-dependencies"></span>

除了超时设置，缓存数据还可能受到**缓存依赖**的影响而失效。例如，[[yii\caching\FileDependency]] 代表对一个文件修改时间的依赖。这个依赖条件发生变化也就意味着相应的文件已经被修改。因此，缓存中任何过期的文件内容都应该被置为失效状态，对 [[yii\caching\Cache::get()|get()]] 的调用都应该返回 false。

缓存依赖用 [[yii\caching\Dependency]] 的派生类所表示。当调用 [[yii\caching\Cache::set()|set()]] 在缓存中存储一项数据时，可以同时传递一个关联的缓存依赖对象。例如：

```php
// 创建一个对 example.txt 文件修改时间的缓存依赖
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// 缓存数据将在30秒后超时
// 如果 example.txt 被修改，它也可能被更早地置为失效状态。
$cache->set($key, $data, 30, $dependency);

// 缓存会检查数据是否已超时。
// 它还会检查关联的依赖是否已变化。
// 符合任何一个条件时都会返回 false。
$data = $cache->get($key);
```

下面是可用的缓存依赖的概况：

- [[yii\caching\ChainedDependency]]：如果依赖链上任何一个依赖产生变化，则依赖改变。
- [[yii\caching\DbDependency]]：如果指定 SQL 语句的查询结果发生了变化，则依赖改变。
- [[yii\caching\ExpressionDependency]]：如果指定的 PHP 表达式执行结果发生变化，则依赖改变。
- [[yii\caching\FileDependency]]：如果文件的最后修改时间发生变化，则依赖改变。
- [[yii\caching\GroupDependency]]：将一项缓存数据标记到一个组名，你可以通过调用 [[yii\caching\GroupDependency::invalidate()]] 一次性将相同组名的缓存全部置为失效状态。


## 查询缓存 <span id="query-caching"></span>

查询缓存是一个建立在数据缓存之上的特殊缓存特性。它用于缓存数据库查询的结果。

查询缓存需要一个 [[yii\db\Connection|数据库连接]] 和一个有效的 `cache` 应用组件。查询缓存的基本用法如下，假设 `$db` 是一个 [[yii\db\Connection]] 实例：

```php
$duration = 60;     // 缓存查询结果60秒
$dependency = ...;  // 可选的缓存依赖

$db->beginCache($duration, $dependency);

// ...这儿执行数据库查询...

$db->endCache();
```

如你所见，`beginCache()` 和 `endCache()` 中间的任何查询结果都会被缓存起来。如果缓存中找到了同样查询的结果，则查询会被跳过，直接从缓存中提取结果。

查询缓存可以用于 [ActiveRecord](db-active-record.md) 和 [DAO](db-dao.md)。

> Info: 有些 DBMS （例如：[MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html)）也支持数据库服务器端的查询缓存。你可以选择使用任一查询缓存机制。上文所述的查询缓存的好处在于你可以指定更灵活的缓存依赖因此可能更加高效。


### 配置 <span id="query-caching-configs"></span>

查询缓存有两个通过 [[yii\db\Connection]] 设置的配置项：

* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: 查询结果在缓存中的有效期，以秒表示。如果在调用 [[yii\db\Connection::beginCache()]] 时传递了一个显式的时值参数，则配置中的有效期时值会被覆盖。
* [[yii\db\Connection::queryCache|queryCache]]: 缓存应用组件的 ID。默认为 `'cache'`。只有在设置了一个有效的缓存应用组件时，查询缓存才会有效。


### 限制条件 <span id="query-caching-limitations"></span>

当查询结果中含有资源句柄时，查询缓存无法使用。例如，在有些 DBMS 中使用了 `BLOB` 列的时候，缓存结果会为该数据列返回一个资源句柄。

有些缓存存储器有大小限制。例如，memcache 限制每条数据最大为 1MB。因此，如果查询结果的大小超出了该限制，则会导致缓存失败。

