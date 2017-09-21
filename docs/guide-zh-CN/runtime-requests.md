请求
===

应用的请求使用 [[yii\web\Request]] 对象来表示，它提供了请求参数、HTTP 头、cookie 等信息。
对于一个给定的请求，你可以通过 `request` [应用组件](structure-application-components.md) 来访问，默认情况下它是 [[yii\web\Request]] 的实例。在本节中，我们将介绍如何在应用中使用此组件。

## 请求参数 <span id="request-parameters"></span>

你可以通过 `request` 组件的 [[yii\web\Request::get()|get()]] 和 [[yii\web\Request::post()|post()]] 方法来获取请求参数。
它分别返回 `$_GET` 和 `$_POST` 的值。例如:

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

> Info: 推荐上面的方式使用 `request` 组件来获取请求参数而不是直接使用 `$_GET` 和 `$_POST`。这将使编写测试变得更容易，因为您可以创建一个带有伪造请求数据的模拟请求组件。

在实现 [RESTful APIs](rest-quick-start.md) 是，你通常会需要获取 PUT、PATCH 或其他 [请求方法](#request-methods) 提交的参数.
你可以通过调用 [[yii\web\Request::getBodyParam()]] 方法来获取。例如:

```php
$request = Yii::$app->request;

// returns all parameters
$params = $request->bodyParams;

// returns the parameter "id"
$param = $request->getBodyParam('id');
```

> Info:  和 `GET` 参数不同，通过 `POST`、`PUT`、`PATCH` 等提交的参数实在请求体中发送。
当使用上述方式访问它们时, `request` 组件将解析这些参数。
你可以通过配置 [[yii\web\Request::parsers]] 属性来自定义解析这些参数的方式。

## 请求方法 <span id="request-methods"></span>

你可以通过 `Yii::$app->request->method` 来获取当前请求使用的 HTTP 请求方法。
`request` 还提供了一组布尔属性，用来检查当前请求是否具有某种类型。例如:

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* the request is an AJAX request */ }
if ($request->isGet)  { /* the request method is GET */ }
if ($request->isPost) { /* the request method is POST */ }
if ($request->isPut)  { /* the request method is PUT */ }
```

## 请求 URLs <span id="request-urls"></span>

`request` 组件提供了许多检查当前请求地址的方法。

如果被请求的 URL 是 `http://example.com/admin/index.php/product?id=100`，您可以得到以下内容的不同部分:

* [[yii\web\Request::url|url]]: 返回 `/admin/index.php/product?id=100`, 没有 host info 部分的 URL。
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: 返回 `http://example.com/admin/index.php/product?id=100`，包含 host info 的完整 URL 地址。
* [[yii\web\Request::hostInfo|hostInfo]]: 返回 `http://example.com`，URL 的 host info 部分。
* [[yii\web\Request::pathInfo|pathInfo]]: 返回 `/product`，在脚本和问号(查询字符串)之间的部分。
* [[yii\web\Request::queryString|queryString]]: 返回 `id=100`，问号后面的部分。
* [[yii\web\Request::baseUrl|baseUrl]]: 返回 `/admin`，在 host info 和脚本名称之间的部分。
* [[yii\web\Request::scriptUrl|scriptUrl]]: 返回 `/admin/index.php`，不包含 host info 和 查询字符串的部分。
* [[yii\web\Request::serverName|serverName]]: 返回 `example.com`，URL 中的主机名。
* [[yii\web\Request::serverPort|serverPort]]: 返回 80，WEB 服务器使用的端口。


## HTTP 头信息 <span id="http-headers"></span> 

你可以通过 [[yii\web\Request::headers]] 返回的 [[yii\web\HeaderCollection|header collection]] 属性来获取 HTTP 头信息。例如:

```php
// $headers is an object of yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// returns the Accept header value
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* there is User-Agent header */ }
```

`请求` 组件还支持快速访问一些常用的头信息，包括:

* [[yii\web\Request::userAgent|userAgent]]: 返回 `User-Agent` 的头信息的值。
* [[yii\web\Request::contentType|contentType]]: 返回 `Content-Type` 的头信息的值，它表示请求请求主体中数据的 MIME 类型。
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: 返回用户可以接受的内容 MIME 类型。返回类型根据它们的评分。高评分的类型首先返回。
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: 返回用户可接受的语言。返回的语言是由它们的优先级排序的。第一个元素表示最优先的语言。

如果您的应用支持多语言，并且你想根据是最终用户语言显示页面的语言。你可以通过语言协商方法 [[yii\web\Request::getPreferredLanguage()]] 来设置。

此方法将你的应用支持的语言列表和 [[yii\web\Request::acceptableLanguages|acceptableLanguages]] 进行对比来返回合适的语言。

> Tip: 同样，你可以使用 [[yii\filters\ContentNegotiator|ContentNegotiator]] 过滤器来动态决定响应中应该使用的内容类型和语言。这个过滤器实现了上述属性和方法中的内容协商。

## 客户端信息 <span id="client-information"></span>

你可以分别通过 [[yii\web\Request::userHost|userHost]] 和 [[yii\web\Request::userIP|userIP]] 来获取客户端机器的机器名和 IP 地址。例如:

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```
