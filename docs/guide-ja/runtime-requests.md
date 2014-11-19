リクエスト
==========

アプリケーションに対するリクエストは、リクエストのパラメータ、HTTP ヘッダ、クッキーなどの情報を提供する [[yii\web\Request]]
オブジェクトの形で表されます。与えられたリクエストに対応するリクエストオブジェクトには、
既定では [[yii\web\Request]] のインスタンスである `request` [アプリケーションコンポーネント](structure-application-components.md)
を通じてアクセスすることが出来ます。この節では、アプリケーションの中でこのコンポーネントをどのように利用できるかを説明します。


## リクエストのパラメータ <a name="request-parameters"></a>

リクエストのパラメータを取得するためには、`request` コンポーネントの [[yii\web\Request::get()|get()]] および
[[yii\web\Request::post()|post()]] メソッドを呼ぶことが出来ます。これらは、ぞれぞれ、`$_GET` と `$_POST` の値を返します。例えば、

```php
$request = Yii::$app->request;

$get = $request->get(); 
// $get = $_GET; と同等

$id = $request->get('id');   
// $id = isset($_GET['id']) ? $_GET['id'] : null; と同等

$id = $request->get('id', 1);   
// $id = isset($_GET['id']) ? $_GET['id'] : 1; と同等

$post = $request->post(); 
// $post = $_POST; と同等

$name = $request->post('name');   
// $name = isset($_POST['name']) ? $_POST['name'] : null; と同等

$name = $request->post('name', '');   
// $name = isset($_POST['name']) ? $_POST['name'] : ''; と同等
```

> Info|情報: 直接に `$_GET` と `$_POST` にアクセスしてリクエストのパラメータを読み出す代りに、上記に示されているように
  `request` コンポーネントを通じてそれらを取得することが推奨されます。このようにすると、ダミーのリクエストデータを持った
  模擬リクエストコンポーネントを作ることが出来るため、テストを書くことがより容易になります。

[RESTful API](rest-quick-start.md) を実装するときは、PUT、PATCH またはその他の [リクエストメソッド](#request-methods)
によって送信されたパラメータを読み出さなければならないことがよくあります。そういうパラメータは [[yii\web\Request::getBodyParam()]]
メソッドを呼ぶことで取得することが出来ます。例えば、

```php
$request = Yii::$app->request;

// 全てのパラメータを返す
$params = $request->bodyParams;

// パラメータ "id" を返す
$param = $request->getBodyParam('id');
```

> Info|情報: `GET` パラメータとは異なって、`POST`、`PUT`、`PATCH` などで送信されたパラメータは、リクエストのボディの中で送られます。
  上述のメソッドによってこういうパラメータにアクセスすると、`request` コンポーネントがパラメータを解析します。
  [[yii\web\Request::parsers]] プロパティを構成することによって、これらのパラメータが解析される方法をカスタマイズすることが出来ます。


## リクエストメソッド <a name="request-methods"></a>

現在のリクエストに使用された HTTP メソッドは、`Yii::$app->request->method` という式によって取得することが出来ます。
現在のメソッドが特定のタイプであるかどうかをチェックするための、一連の真偽値のプロパティも提供されています。
例えば、

```php
$request = Yii::$app->request;

if ($request->isAjax) { // リクエストは AJAX リクエスト }
if ($request->isGet)  { // リクエストメソッドは GET }
if ($request->isPost) { // リクエストメソッドは POST }
if ($request->isPut)  { // リクエストメソッドは PUT }
```

## Request URLs <a name="request-urls"></a>

The `request` component provides many ways of inspecting the currently requested URL. 

Assuming the URL being requested is `http://example.com/admin/index.php/product?id=100`, you can get various
parts of this URL as summarized in the following:

* [[yii\web\Request::url|url]]: returns `/admin/index.php/product?id=100`, which is the URL without the host info part. 
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: returns `http://example.com/admin/index.php/product?id=100`,
  which is the whole URL including the host info part.
* [[yii\web\Request::hostInfo|hostInfo]]: returns `http://example.com`, which is the host info part of the URL.
* [[yii\web\Request::pathInfo|pathInfo]]: returns `/product`, which is the part after the entry script and 
  before the question mark (query string).
* [[yii\web\Request::queryString|queryString]]: returns `id=100`, which is the part after the question mark. 
* [[yii\web\Request::baseUrl|baseUrl]]: returns `/admin`, which is the part after the host info and before
  the entry script name.
* [[yii\web\Request::scriptUrl|scriptUrl]]: returns `/admin/index.php`, which is the URL without path info and query string.
* [[yii\web\Request::serverName|serverName]]: returns `example.com`, which is the host name in the URL.
* [[yii\web\Request::serverPort|serverPort]]: returns 80, which is the port used by the Web server.


## HTTP Headers <a name="http-headers"></a> 

You can get the HTTP header information through the [[yii\web\HeaderCollection|header collection]] returned 
by the [[yii\web\Request::headers]] property. For example,

```php
// $headers is an object of yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// returns the Accept header value
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { // there is User-Agent header }
```

The `request` component also provides support for quickly accessing some commonly used headers, including

* [[yii\web\Request::userAgent|userAgent]]: returns the value of the `User-Agent` header.
* [[yii\web\Request::contentType|contentType]]: returns the value of the `Content-Type` header which indicates
  the MIME type of the data in the request body.
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: returns the content MIME types acceptable by users.
  The returned types ordered by the quality score. Types with the highest scores will be returned first.
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: returns the languages acceptable by users.
  The returned languages are ordered by their preference level. The first element represents the most preferred language.

If your application supports multiple languages and you want to display pages in the language that is the most preferred
by the end user, you may use the language negotiation method [[yii\web\Request::getPreferredLanguage()]].
This method takes a list of languages supported by your application, compares them with [[yii\web\Request::acceptableLanguages|acceptableLanguages]],
and returns the most appropriate language.

> Tip: You may also use the [[yii\filters\ContentNegotiator|ContentNegotiator]] filter to dynamically determine 
  what content type and language should be used in the response. The filter implements the content negotiation
  on top the properties and methods described above.


## Client Information <a name="client-information"></a>

You can get the host name and IP address of the client machine through [[yii\web\Request::userHost|userHost]]
and [[yii\web\Request::userIP|userIP]], respectively. For example,

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```
