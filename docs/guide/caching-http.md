HTTP Caching
============

Besides server-side caching that we have described in the previous sections, Web applications may
also exploit client-side caching to save the time for generating and transmitting the same page content.

To use client-side caching, you may configure [[yii\filters\HttpCache]] as a filter for controller
actions whose rendering result may be cached on the client-side. [[yii\filters\HttpCache|HttpCache]]
only works for `GET` and `HEAD` requests. It can handle three kinds of cache-related HTTP headers for these requests:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## `Last-Modified` Header <span id="last-modified"></span>

The `Last-Modified` header uses a timestamp to indicate if the page has been modified since the client caches it.

You may configure the [[yii\filters\HttpCache::lastModified]] property to enable sending
the `Last-Modified` header. The property should be a PHP callable returning a UNIX timestamp about
the page modification time. The signature of the PHP callable should be as follows,

```php
/**
 * @param Action $action the action object that is being handled currently
 * @param array $params the value of the "params" property
 * @return int a UNIX timestamp representing the page modification time
 */
function ($action, $params)
```

The following is an example of making use of the `Last-Modified` header:

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

The above code states that HTTP caching should be enabled for the `index` action only. It should
generate a `Last-Modified` HTTP header based on the last update time of posts. When a browser visits
the `index` page for the first time, the page will be generated on the server and sent to the browser;
If the browser visits the same page again and there is no post being modified during the period,
the server will not re-generate the page, and the browser will use the cached version on the client-side.
As a result, server-side rendering and page content transmission are both skipped.


## `ETag` Header <span id="etag"></span>

The "Entity Tag" (or `ETag` for short) header use a hash to represent the content of a page. If the page
is changed, the hash will be changed as well. By comparing the hash kept on the client-side with the hash
generated on the server-side, the cache may determine whether the page has been changed and should be re-transmitted.

You may configure the [[yii\filters\HttpCache::etagSeed]] property to enable sending the `ETag` header.
The property should be a PHP callable returning a seed for generating the ETag hash. The signature of the PHP callable
should be as follows,

```php
/**
 * @param Action $action the action object that is being handled currently
 * @param array $params the value of the "params" property
 * @return string a string used as the seed for generating an ETag hash
 */
function ($action, $params)
```

The following is an example of making use of the `ETag` header:

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

The above code states that HTTP caching should be enabled for the `view` action only. It should
generate an `ETag` HTTP header based on the title and content of the requested post. When a browser visits
the `view` page for the first time, the page will be generated on the server and sent to the browser;
If the browser visits the same page again and there is no change to the title and content of the post,
the server will not re-generate the page, and the browser will use the cached version on the client-side.
As a result, server-side rendering and page content transmission are both skipped.

ETags allow more complex and/or more precise caching strategies than `Last-Modified` headers.
For instance, an ETag can be invalidated if the site has switched to another theme.

Expensive ETag generation may defeat the purpose of using `HttpCache` and introduce unnecessary overhead,
since they need to be re-evaluated on every request. Try to find a simple expression that invalidates
the cache if the page content has been modified.

> Note: In compliance to [RFC 7232](https://datatracker.ietf.org/doc/html/rfc7232#section-2.4),
  `HttpCache` will send out both `ETag` and `Last-Modified` headers if they are both configured.
  And if the client sends both of the `If-None-Match` header and the `If-Modified-Since` header, only the former
  will be respected.


## `Cache-Control` Header <span id="cache-control"></span>

The `Cache-Control` header specifies the general caching policy for pages. You may send it by configuring
the [[yii\filters\HttpCache::cacheControlHeader]] property with the header value. By default, the following
header will be sent:

```
Cache-Control: public, max-age=3600
```

## Session Cache Limiter <span id="session-cache-limiter"></span>

When a page uses session, PHP will automatically send some cache-related HTTP headers as specified in
the `session.cache_limiter` PHP INI setting. These headers may interfere or disable the caching
that you want from `HttpCache`. To prevent this problem, by default `HttpCache` will disable sending
these headers automatically. If you want to change this behavior, you should configure the
[[yii\filters\HttpCache::sessionCacheLimiter]] property. The property can take a string value, including
`public`, `private`, `private_no_expire`, and `nocache`. Please refer to the PHP manual about
[session_cache_limiter()](https://www.php.net/manual/en/function.session-cache-limiter.php)
for explanations about these values.


## SEO Implications <span id="seo-implications"></span>

Search engine bots tend to respect cache headers. Since some crawlers have a limit on how many pages
per domain they process within a certain time span, introducing caching headers may help indexing your
site as they reduce the number of pages that need to be processed.

