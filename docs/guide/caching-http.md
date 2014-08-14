HTTP Caching
============

Besides server-side caching that we have described in the previous sections, Web applications may
also exploit client-side caching to skip transmitting the same page content. To enable client-side
caching, you may use [[yii\filters\HttpCache]] which generates appropriate cache-related HTTP headers.

[[yii\filters\HttpCache|HttpCache]] is an [action filter](runtime-filtering.md) that can be used
like the following in a controller class:

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
the server will not re-generate the page, and the browser will use the cached version on the client side.
As a result, server-side rendering and page content transmission are both skipped.

[[yii\filters\HttpCache|HttpCache]] only works for `GET` and `HEAD` requests. It can handle
three kinds of cache-related HTTP headers for these requests:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## `Last-Modified` Header <a name="last-modified"></a>

The `Last-Modified` header uses a timestamp to indicate if the page has been modified since the client caches it.

You may configure the [[yii\filters\HttpCache::lastModified]] property to enable sending
the `Last-Modified` header. The property should be a PHP callable returning a UNIX timestamp about
the page modification time. The signature of the PHP callable should be as follows,

```php
/**
 * @param Action $action the action object that is being handled currently
 * @param array $params the value of the "params" property
 * @return integer a UNIX timestamp representing the page modification time
 */
function ($action, $params)
```

## `ETag` Header <a name="etag"></a>

The "Entity Tag" (or `ETag` for short) header use a hash to represent the content of a page. If the page
is changed, the hash will be changed as well. By comparing the hash kept on the client side with the hash
generated on the server side, the cache may determine whether the page has been changed and should be re-transmitted.

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

ETags allow more complex and/or more precise caching strategies than `Last-Modified` headers.
For instance, an ETag can be invalidated if the site has switched to another theme.

Expensive ETag generation may defeat the purpose of using `HttpCache` and introduce unnecessary overhead,
since they need to be re-evaluated on every request. Try to find a simple expression that invalidates
the cache if the page content has been modified.


> Note: In compliance to [RFC 7232, section 2.4](http://tools.ietf.org/html/rfc7232#section-2.4),
  `HttpCache` will send out both `ETag` and `Last-Modified` headers if they are both configured.
  However, in order to satisfy [section 3.3](http://tools.ietf.org/html/rfc7232#section-3.3) the
  `If-None-Match` client header will always take precedence over `If-Modified-Since` during validation; meaning
  latter one is going to be ignored if a `If-None-Match` header is present in the request.


## `Cache-Control` Header <a name="cache-control"></a>

The `Cache-Control` header specifies the general caching policy for pages. You may send it by configuring
the [[yii\filters\HttpCache::cacheControlHeader]] property with the header value.


## SEO Implications <a name="seo-implications"></a>

Search engine bots tend to respect cache headers. Since some crawlers have a limit on how many pages
per domain they process within a certain time span, introducing caching headers may help indexing your
site as they reduce the number of pages that need to be processed.

