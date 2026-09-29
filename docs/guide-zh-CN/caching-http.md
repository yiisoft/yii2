HTTP 缓存
============

除了前面章节讲到的服务器端缓存外， Web 应用还可以利用客户端缓存
去节省相同页面内容的生成和传输时间。

通过配置 [[yii\filters\HttpCache]] 过滤器，控制器操作渲染的内容就能缓存在客户端。
[[yii\filters\HttpCache|HttpCache]] 过滤器仅对 `GET` 和 `HEAD` 请求生效，
它能为这些请求设置三种与缓存有关的 HTTP 头。

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## `Last-Modified` 头 <span id="last-modified"></span>

`Last-Modified` 头使用时间戳标明页面自上次客户端缓存后是否被修改过。

通过配置 [[yii\filters\HttpCache::lastModified]] 属性向客户端发送 `Last-Modified` 头。
该属性的值应该为 PHP callable 类型，返回的是页面修改时的 Unix 时间戳。
该 callable 的参数和返回值应该如下：

```php
/**
 * @param Action $action 当前处理的动作对象
 * @param array $params “params” 属性的值
 * @return int 页面修改时的 Unix 时间戳
 */
function ($action, $params)
```

以下是使用 `Last-Modified` 头的示例：

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```

上述代码表明 HTTP 缓存只在 `index` 操作时启用。
它会基于页面最后修改时间生成一个 `Last-Modified` HTTP 头。
当浏览器第一次访问 `index` 页时，服务器将会生成页面并发送至客户端浏览器。
之后客户端浏览器在页面没被修改期间访问该页，
服务器将不会重新生成页面，浏览器会使用之前客户端缓存下来的内容。
因此服务端渲染和内容传输都将省去。


## `ETag` 头 <span id="etag"></span>

“Entity Tag”（实体标签，简称 ETag）使用一个哈希值表示页面内容。如果页面被修改过，
哈希值也会随之改变。通过对比客户端的哈希值和服务器端生成的哈希值，
浏览器就能判断页面是否被修改过，进而决定是否应该重新传输内容。

通过配置 [[yii\filters\HttpCache::etagSeed]] 属性向客户端发送 `ETag` 头。
该属性的值应该为 PHP callable 类型，返回的是一段种子字符用来生成 ETag 哈希值。
该 callable 的参数和返回值应该如下：

```php
/**
 * @param Action $action 当前处理的动作对象
 * @param array $params “params” 属性的值
 * @return string 一段种子字符用来生成 ETag 哈希值
 */
function ($action, $params)
```

以下是使用 `ETag` 头的示例：

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

上述代码表明 HTTP 缓存只在 `view` 操作时启用。
它会基于用户请求的标题和内容生成一个 `ETag` HTTP 头。
当浏览器第一次访问 `view` 页时，服务器将会生成页面并发送至客户端浏览器。
之后客户端浏览器标题和内容没被修改在期间访问该页，服务器将不会重新生成页面，
浏览器会使用之前客户端缓存下来的内容。
因此服务端渲染和内容传输都将省去。

ETag 相比 `Last-Modified` 能实现更复杂和更精确的缓存策略。
例如，当站点切换到另一个主题时可以使 ETag 失效。

复杂的 Etag 生成种子可能会违背使用 `HttpCache` 的初衷而引起不必要的性能开销，
因为响应每一次请求都需要重新计算 Etag。
请试着找出一个最简单的表达式去触发 Etag 失效。

> Note: 为了遵循 [RFC 7232（HTTP 1.1 协议）](https://datatracker.ietf.org/doc/html/rfc7232#section-2.4)，
如果同时配置了 `ETag` 和 `Last-Modified` 头，`HttpCache` 将会同时发送它们。
并且如果客户端同时发送 `If-None-Match` 头和 `If-Modified-Since` 头，
则只有前者会被接受。


## `Cache-Control` 头 <span id="cache-control"></span>

`Cache-Control` 头指定了页面的常规缓存策略。
可以通过配置 [[yii\filters\HttpCache::cacheControlHeader]] 
属性发送相应的头信息。默认发送以下头：

```
Cache-Control: public, max-age=3600
```

## 会话缓存限制器 <span id="session-cache-limiter"></span>

当页面使 session 时，PHP 将会按照 PHP.INI 
中所设置的 `session.cache_limiter` 值自动发送一些缓存相关的 HTTP 头。
这些 HTTP 头有可能会干扰你原本设置的 `HttpCache` 或让其失效。
为了避免此问题，默认情况下 `HttpCache` 禁止自动发送这些头。
想改变这一行为，可以配置 [[yii\filters\HttpCache::sessionCacheLimiter]] 属性。
该属性接受一个字符串值，包括 `public`，`private`，`private_no_expire`，和 `nocache`。
请参考 PHP 手册中的[缓存限制器](https://www.php.net/manual/zh/function.session-cache-limiter.php)
了解这些值的含义。


## SEO 影响 <span id="seo-implications"></span>

搜索引擎趋向于遵循站点的缓存头。因为一些爬虫的抓取频率有限制，
启用缓存头可以可以减少重复请求数量，增加爬虫抓取效率
（译者：大意如此，但搜索引擎的排名规则不了解，好的缓存策略应该是可以为用户体验加分的）。

