Cache de Dados
============

O Cache de Dados é responsável por armazenar uma ou mais variáveis PHP em um arquivo temporário para
ser recuperado posteriormente.
Este também é a fundação para funcionalidades mais avançadas do cache, como [cache de consulta](#query-caching)
e [cache de página](caching-page.md).

O código a seguir é um padrão de uso típico de cache de dados, onde `$cache` refere-se a
um [Componente de Cache](#cache-components):

```php
// tentar recuperar $data do cache
$data = $cache->get($key);

if ($data === false) {

    // $data não foi encontrado no cache, calculá-la do zero

    // armzaenar $data no cache para que esta possa ser recuperada na próxima vez
    $cache->set($key, $data);
}

// $data é acessível a partir daqui
```


## Componentes de Cache <span id="cache-components"></span>

O cache de dados se baseia nos, então chamados, *Componentes de Cache* que representam vários armazenamentos de cache,
como memória, arquivos, bancos de dados.

Componentes de Cache são normalmente registrados como [componentes de aplicação](structure-application-components.md) para que possam ser globalmente configuraveis e acessiveis. O código a seguir exibe como configurar o componente de aplicação `cache` para usar [memcached](http://memcached.org/) com dois servidores de cache:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'servidor1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'servidor2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

Você pode então, acessar o componente de cache acima usando a expressão `Yii::$app->cache`.

Já que todos os componentes de cache suportam as mesmas APIs, você pode trocar o componente de cache por outro 
reconfigurando-o nas configurações da aplicação sem modificar o código que usa o cache.
Por exemplo, você pode modificar a configuração acima para usar [[yii\caching\ApcCache|APC cache]]:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Dica: você pode registrar múltiplos componentes de cache na aplicação. O componente chamado `cache` é usado 
  por padrão por muitas classes dependentes de cache (ex. [[yii\web\UrlManager]]).


### Sistemas de cache suportados <span id="supported-cache-storage"></span>

Yii suporta uma ampla gama de sistemas de cache. A seguir um sumario:

* [[yii\caching\ApcCache]]: usa a extensão do PHP [APC](http://php.net/manual/en/book.apc.php). Esta opção pode ser
  considerada a mais rápida ao se implementar o cache de uma aplicação densa e centralizada (ex. um
  servidor, sem balanceadores de carga dedicados, etc.).
* [[yii\caching\DbCache]]: usa uma tabela no banco de dados para armazenar os dados em cache. Para usar este cache
  você deve criar uma tabela como especificada em [[yii\caching\DbCache::cacheTable]].
* [[yii\caching\DummyCache]]: serve apenas como um substituto e não faz nenhum cache na realidade
  O propósito deste comoponente é simplificar o codigo que precisa checar se o cache está disponível.
  Por exemplo, durante o desenvolvimento or se o servidor não suporta cache, você pode configurar um
  componente de cache para usar este cache. Quando o suporte ao cache for habilitado, você pode trocar o
  para o componente correspondente. Em ambos os casos, você pode usar o mesmo código 
  `Yii::$app->cache->get($key)` para tentar recuperar os dados do cache sem se procupar que
  `Yii::$app->cache` possa ser `null`.
* [[yii\caching\FileCache]]: usa arquivos para armazenar os dados em cache. Este é particularmente indicado 
  para armazenar grandes quantidades de dados como o conteúdo da página.
* [[yii\caching\MemCache]]: usa o [memcache](http://php.net/manual/en/book.memcache.php) do PHP e as extensões
  [memcached](http://php.net/manual/en/book.memcached.php). Esta opção pode ser considerada a mais rápida
  ao se implementar o cache em aplicações distribuidas (ex. vários servidores, balanceadores de carga, etc.)
* [[yii\redis\Cache]]: implementa um componente de cache baseado em armazenamento chave-valor 
  [Redis](http://redis.io/) (requere redis versão 2.6.12 ou mais recente).
* [[yii\caching\WinCache]]: usa a extensão PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)
  ([veja também](http://php.net/manual/en/book.wincache.php)).
* [[yii\caching\XCache]]: usa a extensão PHP [XCache](http://xcache.lighttpd.net/).
* [[yii\caching\ZendDataCache]]: usa
  [Cache de Dados Zend](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  como o meio de cache subjacente.


> Dica: Você pode usar vários tipos de cache na mesma aplicação. Uma estratégia comum é usar caches baseados 
  em memória para armazenar dados pequenos mas constantemente usados (ex. dados estatísticos), e usar caches
  baseados em arquivo ou banco da dados para armazenar dados que são maiores mas são menos usados 
  (ex. conteúdo da página).


## Cache APIs <span id="cache-apis"></span>

All Componente de Caches have the same base class [[yii\caching\Cache]] and thus support the following APIs:

* [[yii\caching\Cache::get()|get()]]: retrieves a data item from cache with a specified key. A `false`
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

> Note: Do not cache a `false` boolean value directly because the [[yii\caching\Cache::get()|get()]] method uses
`false` return value to indicate the data item is not found in the cache. You may put `false` in an array and cache
this array instead to avoid this problem.

Some cache storage, such as MemCache, APC, support retrieving multiple cached values in a batch mode,
which may reduce the overhead involved in retrieving cached data. The APIs [[yii\caching\Cache::mget()|mget()]]
and [[yii\caching\Cache::madd()|madd()]] are provided to exploit this feature. In case the underlying cache storage
does not support this feature, it will be simulated.

Because [[yii\caching\Cache]] implements `ArrayAccess`, a Componente de Cache can be used like an array. The following
are some examples:

```php
$cache['var1'] = $value1;  // equivalent to: $cache->set('var1', $value1);
$value2 = $cache['var2'];  // equivalent to: $value2 = $cache->get('var2');
```


### Cache Keys <span id="cache-keys"></span>

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


### Cache Expiration <span id="cache-expiration"></span>

A data item stored in a cache will remain there forever unless it is removed because of some caching policy
enforcement (e.g. caching space is full and the oldest data are removed). To change this behavior, you can provide
an expiration parameter when calling [[yii\caching\Cache::set()|set()]] to store a data item. The parameter
indicates for how many seconds the data item can remain valid in the cache. When you call
[[yii\caching\Cache::get()|get()]] to retrieve the data item, if it has passed the expiration time, the method
will return `false`, indicating the data item is not found in the cache. For example,

```php
// keep the data in cache for at most 45 seconds
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data is expired or is not found in the cache
}
```


### Cache Dependencies <span id="cache-dependencies"></span>

Besides expiration setting, cached data item may also be invalidated by changes of the so-called *cache dependencies*.
For example, [[yii\caching\FileDependency]] represents the dependency of a file's modification time.
When this dependency changes, it means the corresponding file is modified. As a result, any outdated
file content found in the cache should be invalidated and the [[yii\caching\Cache::get()|get()]] call
should return `false`.

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
- [[yii\caching\TagDependency]]: associates a cached data item with one or multiple tags. You may invalidate
  the cached data items with the specified tag(s) by calling [[yii\caching\TagDependency::invalidate()]].


## Cache De Consulta <span id="query-caching"></span>

Cache de consulta is a special caching feature built on top of data caching. It is provided to cache the result
of database queries.

Cache de consulta requires a [[yii\db\Connection|DB connection]] and a valid `cache` [application component](#cache-components).
The basic usage of cache de consulta is as follows, assuming `$db` is a [[yii\db\Connection]] instance:

```php
$result = $db->cache(function ($db) {

    // the result of the SQL query will be served from the cache
    // if cache de consulta is enabled and the query result is found in the cache
    return $db->createCommand('SELECT * FROM customer WHERE id=1')->queryOne();

});
```

Cache de consulta can be used for [DAO](db-dao.md) as well as [ActiveRecord](db-active-record.md):

```php
$result = Customer::getDb()->cache(function ($db) {
    return Customer::find()->where(['id' => 1])->one();
});
```

> Info: Some DBMS (e.g. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
  also support cache de consulta on the DB server side. You may choose to use either cache de consulta mechanism.
  The cache de consulta described above has the advantage that you may specify flexible cache dependencies
  and are potentially more efficient.


### Configurations <span id="query-caching-configs"></span>

Cache de consulta has three global configurable options through [[yii\db\Connection]]:

* [[yii\db\Connection::enableQueryCache|enableQueryCache]]: whether to turn on or off cache de consulta.
  It defaults to true. Note that to effectively turn on cache de consulta, you also need to have a valid
  cache, as specified by [[yii\db\Connection::queryCache|queryCache]].
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: this represents the number of seconds
  that a query result can remain valid in the cache. You can use 0 to indicate a query result should
  remain in the cache forever. This property is the default value used when [[yii\db\Connection::cache()]]
  is called without specifying a duration.
* [[yii\db\Connection::queryCache|queryCache]]: this represents the ID of the cache application component.
  It defaults to `'cache'`. Cache de consulta is enabled only if there is a valid cache application component.


### Usages <span id="query-caching-usages"></span>

You can use [[yii\db\Connection::cache()]] if you have multiple SQL queries that need to take advantage of
cache de consulta. The usage is as follows,

```php
$duration = 60;     // cache query results for 60 seconds.
$dependency = ...;  // optional dependency

$result = $db->cache(function ($db) {

    // ... perform SQL queries here ...

    return $result;

}, $duration, $dependency);
```

Any SQL queries in the anonymous function will be cached for the specified duration with the specified dependency.
If the result of a query is found valid in the cache, the query will be skipped and the result will be served
from the cache instead. If you do not specify the `$duration` parameter, the value of
[[yii\db\Connection::queryCacheDuration|queryCacheDuration]] will be used instead.

Sometimes within `cache()`, you may want to disable cache de consulta for some particular queries. You can use
[[yii\db\Connection::noCache()]] in this case.

```php
$result = $db->cache(function ($db) {

    // SQL queries that use cache de consulta

    $db->noCache(function ($db) {

        // SQL queries that do not use cache de consulta

    });

    // ...

    return $result;
});
```

If you just want to use cache de consulta for a single query, you can call [[yii\db\Command::cache()]] when building
the command. For example,

```php
// use cache de consulta and set query cache duration to be 60 seconds
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

You can also use [[yii\db\Command::noCache()]] to disable cache de consulta for a single command. For example,

```php
$result = $db->cache(function ($db) {

    // SQL queries that use cache de consulta

    // do not use cache de consulta for this command
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### Limitations <span id="query-caching-limitations"></span>

Cache de consulta does not work with query results that contain resource handlers. For example,
when using the `BLOB` column type in some DBMS, the query result will return a resource
handler for the column data.

Some caching storage has size limitation. For example, memcache limits the maximum size
of each entry to be 1MB. Therefore, if the size of a query result exceeds this limit,
the caching will fail.

