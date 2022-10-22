リクエスト
==========

アプリケーションに対するリクエストは、リクエストのパラメータ、HTTP ヘッダ、クッキーなどの情報を提供する [[yii\web\Request]] オブジェクトの形で表されます。
与えられたリクエストに対応するリクエスト・オブジェクトには、デフォルトでは [[yii\web\Request]] のインスタンスである `request` [アプリケーション・コンポーネント](structure-application-components.md)
を通じてアクセスすることが出来ます。
このセクションでは、アプリケーションの中でこのコンポーネントをどのように利用できるかを説明します。


## リクエストのパラメータ <span id="request-parameters"></span>

リクエストのパラメータを取得するためには、`request` コンポーネントの [[yii\web\Request::get()|get()]] および [[yii\web\Request::post()|post()]] メソッドを呼ぶことが出来ます。
これらは、ぞれぞれ、`$_GET` と `$_POST` の値を返します。例えば、

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

> Info: 直接に `$_GET` と `$_POST` にアクセスしてリクエストのパラメータを読み出す代りに、上記に示されているように、
`request` コンポーネントを通じてそれらを取得することが推奨されます。
  このようにすると、ダミーのリクエスト・データを持った模擬リクエスト・コンポーネントを作ることが出来るため、テストを書くことがより容易になります。

[RESTful API](rest-quick-start.md) を実装するときは、PUT、PATCH またはその他の [リクエスト・メソッド](#request-methods) によって送信されたパラメータを読み出さなければならないことがよくあります。
そういうパラメータは [[yii\web\Request::getBodyParam()]] メソッドを呼ぶことで取得することが出来ます。
例えば、

```php
$request = Yii::$app->request;

// 全てのパラメータを返す
$params = $request->bodyParams;

// パラメータ "id" を返す
$param = $request->getBodyParam('id');
```

> Info: `GET` パラメータとは異なって、`POST`、`PUT`、`PATCH` などで送信されたパラメータは、リクエストのボディの中で送られます。
  上述のメソッドによってこれらのパラメータにアクセスすると、`request` コンポーネントがパラメータを解析します。
  [[yii\web\Request::parsers]] プロパティを構成することによって、これらのパラメータが解析される方法をカスタマイズすることが出来ます。


## リクエスト・メソッド <span id="request-methods"></span>

現在のリクエストに使用された HTTP メソッドは、`Yii::$app->request->method` という式によって取得することが出来ます。
現在のメソッドが特定のタイプであるかどうかをチェックするための、一揃いの真偽値のプロパティも提供されています。
例えば、

```php
$request = Yii::$app->request;

if ($request->isAjax) { /* リクエストは AJAX リクエスト */ }
if ($request->isGet)  { /* リクエスト・メソッドは GET */ }
if ($request->isPost) { /* リクエスト・メソッドは POST */ }
if ($request->isPut)  { /* リクエスト・メソッドは PUT */ }
```

## リクエストの URL <span id="request-urls"></span>

`request` コンポーネントは現在リクエストされている URL を調べるための方法を数多く提供しています。

リクエストされた URL が `https://example.com/admin/index.php/product?id=100` であると仮定したとき、
次にまとめたように、この URL のさまざまな部分を取得することが出来ます。

* [[yii\web\Request::url|url]]: `/admin/index.php/product?id=100` を返します。ホスト情報の部分を省略した URL です。
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: `https://example.com/admin/index.php/product?id=100` を返します。
  ホスト情報の部分を含んだ URL です。
* [[yii\web\Request::hostInfo|hostInfo]]: `https://example.com` を返します。URL のホスト情報の部分です。
* [[yii\web\Request::pathInfo|pathInfo]]: `/product` を返します。
  エントリ・スクリプトの後、疑問符 (クエリ文字列) の前の部分です。
* [[yii\web\Request::queryString|queryString]]: `id=100` を返します。疑問符の後の部分です。
* [[yii\web\Request::baseUrl|baseUrl]]: `/admin` を返します。ホスト情報の後、かつ、
  エントリ・スクリプトの前の部分です。
* [[yii\web\Request::scriptUrl|scriptUrl]]: `/admin/index.php` を返します。パス情報とクエリ文字列を省略した URL です。
* [[yii\web\Request::serverName|serverName]]: `example.com` を返します。URL の中のホスト名です。
* [[yii\web\Request::serverPort|serverPort]]: 80 を返します。ウェブ・サーバによって使用されているポートです。


## HTTP ヘッダ <span id="http-headers"></span> 

[[yii\web\Request::headers]] プロパティによって返される [[yii\web\HeaderCollection|header コレクション]] を通じて、
HTTP ヘッダ情報を取得することが出来ます。例えば、

```php
// $headers は yii\web\HeaderCollection のオブジェクト
$headers = Yii::$app->request->headers;

// Accept ヘッダの値を返す
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { /* User-Agent ヘッダが在る */ }
```

`request` コンポーネントは、よく使用されるいくつかのヘッダにすばやくアクセスする方法を提供しています。その中には下記のものが含まれます。

* [[yii\web\Request::userAgent|userAgent]]: `User-Agent` ヘッダの値を返します。
* [[yii\web\Request::contentType|contentType]]: リクエスト・ボディのデータの MIME タイプを示す
  `Content-Type` ヘッダの値を返します。
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: ユーザが受け入れ可能なコンテントの MIME タイプを返します。
  返されるタイプは品質スコアによって順序付けられます。最もスコアの高いタイプが最初に返されます。
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: ユーザが受け入れ可能な言語を返します。
  返される言語は優先レベルによって順序付けられます。最初の要素が最も優先度の高い言語を表します。

あなたのアプリケーションが複数の言語をサポートしており、エンド・ユーザが最も優先する言語でページを表示したいと思う場合は、
言語ネゴシエーション・メソッド [[yii\web\Request::getPreferredLanguage()]] を使うことが出来ます。
このメソッドはアプリケーションによってサポートされている言語のリストを引数として取り、
[[yii\web\Request::acceptableLanguages|acceptableLanguages]] と比較して、最も適切な言語を返します。

> Tip: [[yii\filters\ContentNegotiator|ContentNegotiator]] フィルタを使用して、
  レスポンスにおいてどのコンテント・タイプと言語を使うべきかを動的に決定することも出来ます。
  このフィルタは、上記で説明したプロパティとメソッドの上に、コンテント・ネゴシエーションを実装しています。


## クライアント情報 <span id="client-information"></span>

クライアント・マシンのホスト名と IP アドレスを、
それぞれ、[[yii\web\Request::userHost|userHost]] と [[yii\web\Request::userIP|userIP]] によって取得することが出来ます。
例えば、

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```

## 信頼できるプロキシとヘッダ <span id="trusted-proxies"></span>

前のセクションでホストや IP アドレスなどのユーザ情報を取得する方法を説明しました。
単一のウェブ・サーバがウェブ・サイトをホストしている通常の環境では、このままで動作します。
しかし、Yii アプリケーションがリバース・プロキシの背後で動作している場合は、この情報を読み出すために構成情報を追加する必要があります。
なぜなら、その場合、直接のクライアントはプロキシになっており、ユーザの IP アドレスはプロキシがセットするヘッダによって
Yii アプリケーションに渡されるからです。

明示的に信頼したプロキシ以外は、プロキシによって提供されるヘッダを盲目的に信頼してはいけません。
2.0.13 以降、Yii は `request` コンポーネントの以下のプロパティによって、
信頼できるプロキシの情報を構成することが出来るようになっています。
[[yii\web\Request::trustedHosts|trustedHosts]]、
[[yii\web\Request::secureHeaders|secureHeaders]]、 
[[yii\web\Request::ipHeaders|ipHeaders]] および
[[yii\web\Request::secureProtocolHeaders|secureProtocolHeaders]]

以下は、リバース・プロキシ・アレイの背後で動作するアプリケーションのための、request の構成例です
(リバース・プロキシ・アレイは `10.0.2.0/24` のネットワークに設置されているとします)。

```php
'request' => [
    // ...
    'trustedHosts' => [
        '10.0.2.0/24',
    ],
],
```

プロキシは、デフォルトでは、IP を `X-Forwarded-For` ヘッダで送信し、プロトコル (`http` または `https`) を `X-Forwarded-Proto` で送信します。

あなたのプロキシが異なるヘッダを使っている場合は、request の構成情報を使って調整することが出来ます。例えば、

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

上記の構成によって、`secureHeaders` としてリストされているヘッダはリクエストから除去され、
信頼できるプロキシからのリクエストである場合にのみ、`X-ProxyUser-Ip` と `Front-End-Https` ヘッダが受け入れられます。
その場合、前者は `ipHeaders` で構成されているようにユーザの IP を読み出すために使用され、
後者は [[yii\web\Request::getIsSecureConnection()]] の結果を決定するために使用されます。

2.0.31 以降、[RFC 7239](https://datatracker.ietf.org/doc/html/rfc7239) の `Forwarded` ヘッダがサポートされています。
有効にするためには、ヘッダ名を `secureHeaders` に追加する必要があります。
あなたのプロキシにそれを設定させることを忘れないで下さい。さもないと、エンド・ユーザが IP とプロトコルを盗み見ることが可能になります。

### 解決済みのユーザ IP<span id="already-respolved-user-ip"></span>

ユーザの IP アドレスが Yii アプリケーション以前に解決済みである場合(例えば、`ngx_http_realip_module` など) は、
`request` コンポーネントは下記の構成で正しく動作します。

```php
'request' => [
    // ...
    'trustedHosts' => [
        '0.0.0.0/0',
    ],
    'ipHeaders' => [], 
],
```

この場合、[[yii\web\Request::userIP|userIP]] の値は `$_SERVER['REMOTE_ADDR']` に等しくなります。
同時に、HTTP ヘッダから解決されるプロパティも正しく動作します (例えば、[[yii\web\Request::getIsSecureConnection()]])。

> 注意: `trustedHosts=['0.0.0.0/0']` の設定は、全ての IP が信頼できることを前提としています。
