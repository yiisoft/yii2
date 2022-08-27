Requests
========

Requests made to an application are represented in terms of [[yii\web\Request]] objects which provide information
such as request parameters, HTTP headers, cookies, etc. For a given request, you can get access to the corresponding
request object via the `request` [application component](structure-application-components.md) which is an instance
of [[yii\web\Request]], by default. In this section, we will describe how you can make use of this component in your applications.


## Request Parameters <span id="request-parameters"></span>

To get request parameters, you can call [[yii\web\Request::get()|get()]] and [[yii\web\Request::post()|post()]] methods
of the `request` component. They return the values of `$_GET` and `$_POST`, respectively. For example,

```php
$request = Yii::$app->request;

$get = $request->get();
// equivalent to: $get = $_GET;

$id = $request->get('id');
// equivalent to: $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);
// equivalent to: $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post();
// equivalent to: $post = $_POST;

$name = $request->post('name');
// equivalent to: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');
// equivalent to: $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Info: Instead of directly accessing `$_GET` and `$_POST` to retrieve the request parameters, it is recommended
  that you get them via the `request` component as shown above. This will make writing tests easier because
  you can create a mock request component with faked request data.

When implementing [RESTful APIs](rest-quick-start.md), you often need to retrieve parameters that are submitted
via PUT, PATCH or other [request methods](#request-methods). You can get these parameters by calling
the [[yii\web\Request::getBodyParam()]] methods. For example,

```php
$request = Yii::$app->request;

// returns all parameters
$params = $request->bodyParams;

// returns the parameter "id"
$param = $request->getBodyParam('id');
```

> Info: Unlike `GET` parameters, parameters submitted via `POST`, `PUT`, `PATCH` etc. are sent in the request body.
  The `request` component will parse these parameters when you access them through the methods described above.
  You can customize the way how these parameters are parsed by configuring the [[yii\web\Request::parsers]] property.
  

## Request Methods <span id="request-methods"></span>

You can get the HTTP method used by the current request via the expression `Yii::$app->request->method`.
A whole set of boolean properties is also provided for you to check if the current method is of certain type.
For example,

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* the request is an AJAX request */ }
if ($request->isGet)  { /* the request method is GET */ }
if ($request->isPost) { /* the request method is POST */ }
if ($request->isPut)  { /* the request method is PUT */ }
```

## Request URLs <span id="request-urls"></span>

The `request` component provides many ways of inspecting the currently requested URL. 

Assuming the URL being requested is `https://example.com/admin/index.php/product?id=100`, you can get various
parts of this URL as summarized in the following:

* [[yii\web\Request::url|url]]: returns `/admin/index.php/product?id=100`, which is the URL without the host info part. 
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: returns `https://example.com/admin/index.php/product?id=100`,
  which is the whole URL including the host info part.
* [[yii\web\Request::hostInfo|hostInfo]]: returns `https://example.com`, which is the host info part of the URL.
* [[yii\web\Request::pathInfo|pathInfo]]: returns `/product`, which is the part after the entry script and 
  before the question mark (query string).
* [[yii\web\Request::queryString|queryString]]: returns `id=100`, which is the part after the question mark. 
* [[yii\web\Request::baseUrl|baseUrl]]: returns `/admin`, which is the part after the host info and before
  the entry script name.
* [[yii\web\Request::scriptUrl|scriptUrl]]: returns `/admin/index.php`, which is the URL without path info and query string.
* [[yii\web\Request::serverName|serverName]]: returns `example.com`, which is the host name in the URL.
* [[yii\web\Request::serverPort|serverPort]]: returns 80, which is the port used by the Web server.


## HTTP Headers <span id="http-headers"></span> 

You can get the HTTP header information through the [[yii\web\HeaderCollection|header collection]] returned 
by the [[yii\web\Request::headers]] property. For example,

```php
// $headers is an object of yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// returns the Accept header value
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* there is User-Agent header */ }
```

The `request` component also provides support for quickly accessing some commonly used headers, including:

* [[yii\web\Request::userAgent|userAgent]]: returns the value of the `User-Agent` header.
* [[yii\web\Request::contentType|contentType]]: returns the value of the `Content-Type` header which indicates
  the MIME type of the data in the request body.
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: returns the content MIME types acceptable by users.
  The returned types are ordered by their quality score. Types with the highest scores will be returned first.
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: returns the languages acceptable by users.
  The returned languages are ordered by their preference level. The first element represents the most preferred language.

If your application supports multiple languages and you want to display pages in the language that is the most preferred
by the end user, you may use the language negotiation method [[yii\web\Request::getPreferredLanguage()]].
This method takes a list of languages supported by your application, compares them with [[yii\web\Request::acceptableLanguages|acceptableLanguages]],
and returns the most appropriate language.

> Tip: You may also use the [[yii\filters\ContentNegotiator|ContentNegotiator]] filter to dynamically determine 
  what content type and language should be used in the response. The filter implements the content negotiation
  on top of the properties and methods described above.


## Client Information <span id="client-information"></span>

You can get the host name and IP address
of the client machine through [[yii\web\Request::userHost|userHost]]
and [[yii\web\Request::userIP|userIP]], respectively. For example,

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```

## Trusted proxies and headers <span id="trusted-proxies"></span>

In the previous section you have seen how to get user information like host and IP address.
This will work out of the box in a normal setup where a single webserver is used to serve the website.
If your Yii application however runs behind a reverse proxy, you need to add additional configuration
to retrieve this information as the direct client is now the proxy and the user IP address is passed to
the Yii application by a header set by the proxy.

You should not blindly trust headers provided by proxies unless you explicitly trust the proxy.
Since 2.0.13 Yii supports configuring trusted proxies via the 
[[yii\web\Request::trustedHosts|trustedHosts]],
[[yii\web\Request::secureHeaders|secureHeaders]], 
[[yii\web\Request::ipHeaders|ipHeaders]],
[[yii\web\Request::secureProtocolHeaders|secureProtocolHeaders]] and
[[yii\web\Request::portHeaders|portHeaders]] (since 2.0.46)
properties of the `request` component.

The following is a request config for an application that runs behind an array of reverse proxies,
which are located in the `10.0.2.0/24` IP network:

```php
'request' => [
    // ...
    'trustedHosts' => [
        '10.0.2.0/24',
    ],
],
```

The IP is sent by the proxy in the `X-Forwarded-For` header by default, and the protocol (`http` or `https`) is sent in `X-Forwarded-Proto`.

In case your proxies are using different headers you can use the request configuration to adjust these, e.g.:

```php
'request' => [
    // ...
    'trustedHosts' => [
        '10.0.2.0/24' => [
            'X-ProxyUser-Ip',
            'Front-End-Https',
        ],
    ],
    'secureHeaders' => [
        'X-Forwarded-For',
        'X-Forwarded-Host',
        'X-Forwarded-Proto',
        'X-Forwarded-Port',
        'X-Proxy-User-Ip',
        'Front-End-Https',
    ],
    'ipHeaders' => [
        'X-Proxy-User-Ip',
    ],
    'secureProtocolHeaders' => [
        'Front-End-Https' => ['on']
    ],
],
```

With the above configuration, all headers listed in `secureHeaders` are filtered from the request,
except the `X-ProxyUser-Ip` and `Front-End-Https` headers in case the request is made by the proxy.
In that case the former is used to retrieve the user IP as configured in `ipHeaders` and the latter
will be used to determine the result of [[yii\web\Request::getIsSecureConnection()]].

Since 2.0.31 [RFC 7239](https://datatracker.ietf.org/doc/html/rfc7239) `Forwarded` header is supported. In order to enable
it you need to add header name to `secureHeaders`. Make sure your proxy is setting it, otherwise end user would be
able to spoof IP and protocol.

### Already resolved user IP <span id="already-respolved-user-ip"></span>

If the user's IP address is resolved before the Yii application (e.g. `ngx_http_realip_module` or similar),
the `request` component will work correctly with the following configuration:

```php
'request' => [
    // ...
    'trustedHosts' => [
        '0.0.0.0/0',
    ],
    'ipHeaders' => [], 
],
```

In this case, the value of [[yii\web\Request::userIP|userIP]] will be equal to `$_SERVER['REMOTE_ADDR']`.
Also, properties that are resolved from HTTP headers will work correctly (e.g. [[yii\web\Request::getIsSecureConnection()]]).

> Warning: The `trustedHosts=['0.0.0.0/0']` setting assumes that all IPs are trusted.
