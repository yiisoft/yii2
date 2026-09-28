请求
========

一个应用的请求是用 [[yii\web\Request]] 对象来表示的，该对象提供了诸如
请求参数（译者注：通常是GET参数或者POST参数）、HTTP头、cookies等信息。
默认情况下，对于一个给定的请求，你可以通过 `request` [application component](structure-application-components.md) 应用组件（[[yii\web\Request]] 类的实例）
获得访问相应的请求对象。在本章节，我们将介绍怎样在你的应用中使用这个组件。


## 请求参数 <span id="request-parameters"></span>

要获取请求参数，你可以调用 `request` 组件的 [[yii\web\Request::get()|get()]] 方法和 [[yii\web\Request::post()|post()]] 方法。
他们分别返回 `$_GET` 和 `$_POST` 的值。例如，

```php
$request = Yii::$app->request;

$get = $request->get(); 
// 等价于: $get = $_GET;

$id = $request->get('id');   
// 等价于: $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);   
// 等价于: $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post(); 
// 等价于: $post = $_POST;

$name = $request->post('name');   
// 等价于: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');   
// 等价于: $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Info: 建议你像上面那样通过 `request` 组件来获取请求参数，而不是
直接访问 `$_GET` 和 `$_POST`。
这使你更容易编写测试用例，因为你可以伪造数据来创建一个模拟请求组件。

当实现 [RESTful APIs](rest-quick-start.md) 接口的时候，你经常需要获取通过 PUT， 
PATCH 或者其他的 [request methods](#request-methods) 
请求方法提交上来的参数。你可以通过调用 [[yii\web\Request::getBodyParam()]] 方法来获取这些参数。例如，

```php
$request = Yii::$app->request;

// 返回所有参数
$params = $request->bodyParams;

// 返回参数 "id"
$param = $request->getBodyParam('id');
```

> Info: 不同于 `GET` 参数，`POST`，`PUT`，`PATCH` 等等这些提交上来的参数是在请求体中被发送的。
当你通过上面介绍的方法访问这些参数的时候，`request` 组件会解析这些参数。
你可以通过配置 [[yii\web\Request::parsers]] 属性来自定义怎样解析这些参数。
  

## 请求方法 <span id="request-methods"></span>
 
你可以通过 `Yii::$app->request->method` 表达式来获取当前请求使用的HTTP方法。
这里还提供了一整套布尔属性用于检测当前请求是某种类型。
例如，

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* 该请求是一个 AJAX 请求 */ }
if ($request->isGet)  { /* 请求方法是 GET */ }
if ($request->isPost) { /* 请求方法是 POST */ }
if ($request->isPut)  { /* 请求方法是 PUT */ }
```

## 请求URLs <span id="request-urls"></span>

`request` 组件提供了许多方式来检测当前请求的 URL。

假设被请求的 URL 是 `https://example.com/admin/index.php/product?id=100`，
你可以像下面描述的那样获取 URL 的各个部分：

* [[yii\web\Request::url|url]]：返回 `/admin/index.php/product?id=100`, 此 URL 不包括主机信息部分。
* [[yii\web\Request::absoluteUrl|absoluteUrl]]：返回 `https://example.com/admin/index.php/product?id=100`,
  包含host infode的整个URL。
* [[yii\web\Request::hostInfo|hostInfo]]：返回 `https://example.com`, 只有主机信息部分。
* [[yii\web\Request::pathInfo|pathInfo]]：返回 `/product`，
  这个是入口脚本之后，问号之前（查询字符串）的部分。
* [[yii\web\Request::queryString|queryString]]：返回 `id=100`，问号之后的部分。
* [[yii\web\Request::baseUrl|baseUrl]]：返回 `/admin`，主机信息之后，
  入口脚本之前的部分。
* [[yii\web\Request::scriptUrl|scriptUrl]]：返回 `/admin/index.php`，没有路径信息和查询字符串部分。
* [[yii\web\Request::serverName|serverName]]：返回 `example.com`，URL 中的主机名。
* [[yii\web\Request::serverPort|serverPort]]：返回 80，这是 web 服务中使用的端口。


## HTTP头 <span id="http-headers"></span> 

你可以通过 [[yii\web\Request::headers]] 属性返回的 [[yii\web\HeaderCollection|header collection]] 获取HTTP头信息。
例如，

```php
// $headers 是一个 yii\web\HeaderCollection 对象
$headers = Yii::$app->request->headers;

// 返回 Accept header 值
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* 这是一个 User-Agent 头 */ }
```

请求组件也提供了支持快速访问常用头的方法，包括：

* [[yii\web\Request::userAgent|userAgent]]：返回 `User-Agent` 头。
* [[yii\web\Request::contentType|contentType]]：返回 `Content-Type` 头的值，
  `Content-Type` 是请求体中MIME类型数据。
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]：返回用户可接受的内容MIME类型。
  返回的类型是按照他们的质量得分来排序的。得分最高的类型将被最先返回。
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]：返回用户可接受的语言。
  返回的语言是按照他们的偏好层次来排序的。第一个参数代表最优先的语言。

假如你的应用支持多语言，并且你想在终端用户最喜欢的语言中显示页面，
那么你可以使用语言协商方法 [[yii\web\Request::getPreferredLanguage()]]。
这个方法通过 [[yii\web\Request::acceptableLanguages|acceptableLanguages]] 
在你的应用中所支持的语言列表里进行比较筛选，返回最适合的语言。

> Tip: 你也可以使用 [[yii\filters\ContentNegotiator|ContentNegotiator]] 
  过滤器进行动态确定哪些内容类型和语言应该在响应中使用。
  这个过滤器实现了上面介绍的内容协商的属性和方法。


## 客户端信息 <span id="client-information"></span>

你可以通过 [[yii\web\Request::userHost|userHost]]
和 [[yii\web\Request::userIP|userIP]] 分别获取主机名和客户机的 IP 地址，
例如，

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```

## 受信任的代理和报头 <span id="trusted-proxies"></span>

在上一节中，您已经了解了如何获取主机和 IP 地址等用户信息。
这将在正常设置中开箱即用，其中使用单个网络服务器为网站提供服务。
然而，如果您的 Yii 应用程序在反向代理后面运行，则需要添加其他配置来检索此信息，
因为直接客户端现在是代理，
并且用户 IP 地址通过代理设置的报头传递给 Yii 应用程序。

除非您明确信任代理，否则不应盲目信任代理提供的报头。
从 2.0.13 开始，Yii 支持通过 `request` 组件的
[[yii\web\Request::trustedHosts|trustedHosts]]，
[[yii\web\Request::secureHeaders|secureHeaders]]，
[[yii\web\Request::ipHeaders|ipHeaders]] 和
[[yii\web\Request::secureProtocolHeaders|secureProtocolHeaders]]
属性配置可信代理。

以下是在反向代理数组后面运行的应用程序的请求配置，
它们位于 `10.0.2.0/24` IP 网络中：

```php
'request' => [
    // ...
    'trustedHosts' => [
        '10.0.2.0/24',
    ],
],
```

默认情况下，IP 由代理在 `X-Forwarded-For` 头中发送，协议（“http”或“https”）在 `X-Forwarded-Proto` 中发送。

如果您的代理使用不同的报头，您可以使用请求配置来调整它们，例如：

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

使用上面的配置，`secureHeaders` 中列出的所有报头都会从请求中过滤掉，
除了 `X-ProxyUser-Ip` 和 `Front-End-Https` 报头，以防请求由代理发出。
在这种情况下，前者用于检索 `ipHeaders` 中配置的用户IP，
后者将用于确定 [[yii\web\Request::getIsSecureConnection()]] 的结果。
