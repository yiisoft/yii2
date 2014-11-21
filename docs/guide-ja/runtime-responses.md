レスポンス
==========

アプリケーションは [リクエスト](runtime-requests.md) の処理を完了すると、[[yii\web\Response|レスポンス]] オブジェクトを生成して、
エンドユーザに送信します。レスポンスオブジェクトは、HTTP ステータスコード、HTTP ヘッダ、HTTP ボディなどの情報を含みます。
ウェブアプリケーション開発の最終的な目的は、本質的には、さまざまなリクエストに対してそのようなレスポンスオブジェクトを
作成することにあります。

ほとんどの場合は、主として `response` [アプリケーションコンポーネント](structure-application-components.md) を使用すべきです。
このコンポーネントは、既定では、[[yii\web\Response]] のインスタンスです。しかしながら、Yii は、以下で説明するように、
あなた自身のレスポンスオブジェクトを作成してエンドユーザに送信することも許容しています。

この節では、レスポンスを構成してエンドユーザに送信する方法を説明します。


## ステータスコード <a name="status-code"></a>

レスポンスを作成するときに最初にすることの一つは、リクエストが成功裡に処理されたかどうかを記述することです。そのためには、
[[yii\web\Response::statusCode]] プロパティに有効な [HTTP ステータスコード](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html)
の一つを設定します。例えば、下記のように、リクエストの処理が成功したことを示すために、ステータスコードを 200 に設定します。

```php
Yii::$app->response->statusCode = 200;
```

けれども、たいていの場合、ステータスコードを明示的に設定する必要はありません。これは、[[yii\web\Response::statusCode]]
の既定値が 200 であるからです。そして、リクエストが失敗したことを示したいときは、下記のように、適切な HTTP 例外を投げることが出来ます。

```php
throw new \yii\web\NotFoundHttpException;
```

[エラーハンドラ](runtime-handling-errors.md) は、例外をキャッチすると、例外からステータスコードを抽出してレスポンスに割り当てます。
上記の [[yii\web\NotFoundHttpException]] の場合は、HTTP ステータス 404 と関連付けられています。
次の HTTP 例外が Yii によって事前定義されています。

* [[yii\web\BadRequestHttpException]]: ステータスコード 400
* [[yii\web\ConflictHttpException]]: ステータスコード 409
* [[yii\web\ForbiddenHttpException]]: ステータスコード 403
* [[yii\web\GoneHttpException]]: ステータスコード 410
* [[yii\web\MethodNotAllowedHttpException]]: ステータスコード 405
* [[yii\web\NotAcceptableHttpException]]: ステータスコード 406 
* [[yii\web\NotFoundHttpException]]: ステータスコード 404
* [[yii\web\ServerErrorHttpException]]: ステータスコード 500
* [[yii\web\TooManyRequestsHttpException]]: ステータスコード 429
* [[yii\web\UnauthorizedHttpException]]: ステータスコード 401
* [[yii\web\UnsupportedMediaTypeHttpException]]: ステータスコード 415

投げたい例外が上記のリストに無い場合は、[[yii\web\HttpException]] から拡張したものを作成することが出来ます。
あるいは、ステータスコードを指定して [[yii\web\HttpException]] を直接に投げることも出来ます。例えば、
 
```php
throw new \yii\web\HttpException(402);
```


## HTTP ヘッダ <a name="http-headers"></a> 

`response` コンポーネントの [[yii\web\Response::headers|ヘッダコレクション]] を操作することによって、
HTTP ヘッダを送信することが出来ます。例えば、

```php
$headers = Yii::$app->response->headers;

// Pragma ヘッダを追加する。既存の Pragma ヘッダは上書きされない。
$headers->add('Pragma', 'no-cache');

// Pragma ヘッダを設定する。既存の Pragma ヘッダは全て破棄される。
$headers->set('Pragma', 'no-cache');

// Pragma ヘッダを削除して、削除された Pragma ヘッダの値を配列に返す。
$values = $headers->remove('Pragma');
```

> Info|情報: ヘッダ名は大文字小文字を区別しません。そして、新しく登録されたヘッダは、
  [[yii\web\Response::send()]] メソッドが呼ばれるまで送信されません。


## レスポンスボディ <a name="response-body"></a>

ほとんどのレスポンスは、エンドユーザに対して表示したい内容を示すボディを持っていなければなりません。

既にフォーマットされたボディの文字列を持っている場合は、それをレスポンスの [[yii\web\Response::content]]
プロパティに割り付けることが出来ます。例えば、

```php
Yii::$app->response->content = 'hello world!';
```

データをエンドユーザに送信する前にフォーマットする必要がある場合は、[[yii\web\Response::format|format]] と [[yii\web\Response::data|data]]
の両方のプロパティをセットしなければなりません。[[yii\web\Response::format|format]]
プロパティは [[yii\web\Response::data|data]] がどの形式でフォーマットされるべきかを指定するものです。
例えば、

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii は下記の形式を初めからサポートしています。それぞれ、[[yii\web\ResponseFormatterInterface|フォーマッタ]] クラスとして実装されています。
[[yii\web\Response::formatters]] プロパティを構成することで、これらのフォーマッタをカスタマイズしたり、
または、新しいフォーマッタを追加することが出来ます。

* [[yii\web\Response::FORMAT_HTML|HTML]]: [[yii\web\HtmlResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_XML|XML]]: [[yii\web\XmlResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_JSON|JSON]]: [[yii\web\JsonResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: [[yii\web\JsonResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_RAW|RAW]]: 書式を何も適用せずにレスポンスを送信したいときは、このフォーマットを使用

レスポンスボディは、上記のように、明示的に設定することも出来ますが、たいていの場合は、[アクション](structure-controllers.md)
メソッドの返り値によって暗黙のうちに設定することが出来ます。よくあるユースケースは下記のようなものになります。

```php
public function actionIndex()
{
    return $this->render('index');
}
```

上記の `index` アクションは、`index` ビューのレンダリング結果を返しています。返された値は `response`
コンポーネントによって受け取られ、フォーマットされてエンドユーザに送信されます。

デフォルトのレスポンス形式が [[yii\web\Response::FORMAT_HTML|HTML]] であるため、アクションメソッドの中では文字列を返すだけにすべきです。
別のレスポンス形式を使いたい場合は、データを返す前にレスポンス形式を設定しなければなりません。例えば、

```php
public function actionInfo()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
        'message' => 'hello world',
        'code' => 100,
    ];
}
```

既に述べたように、デフォルトの `response` アプリケーションコンポーネントを使う代りに、
自分自身のレスポンスオブジェクトを作成してエンドユーザに送信することも出来ます。そうするためには、次のように、
アクションメソッドの中でそのようなオブジェクトを返します。

```php
public function actionInfo()
{
    return \Yii::createObject([
        'class' => 'yii\web\Response',
        'format' => \yii\web\Response::FORMAT_JSON,
        'data' => [
            'message' => 'hello world',
            'code' => 100,
        ],
    ]);
}
```

> Note|注意: 自分自身のレスポンスオブジェクトを作成しようとする場合は、アプリケーションのコンフィギュレーションで `response`
  コンポーネントのために設定したコンフィギュレーションを利用することは出来ません。しかし、 [依存の注入](concept-di-container.md) を使えば、
  共通のコンフィギュレーションをあなたの新しいレスポンスオブジェクトに適用することが出来ます。


## ブラウザのリダイレクト <a name="browser-redirection"></a>

ブラウザのリダイレクトは `Location` HTTP ヘッダの送信に依存しています。この機能は通常よく使われるものであるため、
Yii はこれについて特別のサポートを提供しています。

[[yii\web\Response::redirect()]] メソッドを呼ぶことによって、ユーザのブラウザをある URL にリダイレクトすることが出来ます。
このメソッドは与えられた URL を持つ適切な `Location` ヘッダを設定して、レスポンスオブジェクトそのものを返します。
アクションメソッドの中では、そのショートカット版である [[yii\web\Controller::redirect()]] を呼ぶことが出来ます。例えば、

```php
public function actionOld()
{
    return $this->redirect('http://example.com/new', 301);
}
```

上記のコードでは、アクションメソッドが `redirect()` メソッドの結果を返しています。前に説明したように、
アクションメソッドによって返されるレスポンスオブジェクトは、エンドユーザに送信されるレスポンスとして使用されることになります。

アクションメソッド以外の場所では、[[yii\web\Response::redirect()]] を直接に呼び出し、メソッドチェーンで
[[yii\web\Response::send()]] メソッドを呼んで、レスポンスに余計なコンテンツが追加されないことを保証すべきです。

```php
\Yii::$app->response->redirect('http://example.com/new', 301)->send();
```

> Info: By default, the [[yii\web\Response::redirect()]] method sets the response status code to be 302 which instructs
  the browser that the resource being requested is *temporarily* located in a different URI. You can pass in a status
  code 301 to tell the browser that the resource has been *permanently* relocated.

When the current request is an AJAX request, sending a `Location` header will not automatically cause the browser
to redirect. To solve this problem, the [[yii\web\Response::redirect()]] method sets an `X-Redirect` header with 
the redirection URL as its value. On the client side, you may write JavaScript code to read this header value and
redirect the browser accordingly.

> Info: Yii comes with a `yii.js` JavaScript file which provides a set of commonly used JavaScript utilities,
  including browser redirection based on the `X-Redirect` header. Therefore, if you are using this JavaScript file
  (by registering the [[yii\web\YiiAsset]] asset bundle), you do not need to write anything to support AJAX redirection.


## Sending Files <a name="sending-files"></a>

Like browser redirection, file sending is another feature that relies on specific HTTP headers. Yii provides
a set of methods to support various file sending needs. They all have built-in support for the HTTP range header.

* [[yii\web\Response::sendFile()]]: sends an existing file to a client.
* [[yii\web\Response::sendContentAsFile()]]: sends a text string as a file to a client.
* [[yii\web\Response::sendStreamAsFile()]]: sends an existing file stream as a file to a client. 

These methods have the same method signature with the response object as the return value. If the file
to be sent is very big, you should consider using [[yii\web\Response::sendStreamAsFile()]] because it is more
memory efficient. The following example shows how to send a file in a controller action:

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('path/to/file.txt');
}
```

If you are calling the file sending method in places other than an action method, you should also call
the [[yii\web\Response::send()]] method afterwards to ensure no extra content will be appended to the response.

```php
\Yii::$app->response->sendFile('path/to/file.txt')->send();
```

Some Web servers have a special file sending support called *X-Sendfile*. The idea is to redirect the
request for a file to the Web server which will directly serve the file. As a result, the Web application
can terminate earlier while the Web server is sending the file. To use this feature, you may call
the [[yii\web\Response::xSendFile()]]. The following list summarizes how to enable the `X-Sendfile` feature
for some popular Web servers:

- Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
- Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)


## Sending Response <a name="sending-response"></a>

The content in a response is not sent to the user until the [[yii\web\Response::send()]] method is called.
By default, this method will be called automatically at the end of [[yii\base\Application::run()]]. You can, however,
explicitly call this method to force sending out the response immediately.

The [[yii\web\Response::send()]] method takes the following steps to send out a response:

1. Trigger the [[yii\web\Response::EVENT_BEFORE_SEND]] event.
2. Call [[yii\web\Response::prepare()]] to format [[yii\web\Response::data|response data]] into 
   [[yii\web\Response::content|response content]].
3. Trigger the [[yii\web\Response::EVENT_AFTER_PREPARE]] event.
4. Call [[yii\web\Response::sendHeaders()]] to send out the registered HTTP headers.
5. Call [[yii\web\Response::sendContent()]] to send out the response body content.
6. Trigger the [[yii\web\Response::EVENT_AFTER_SEND]] event.

After the [[yii\web\Response::send()]] method is called once, any further call to this method will be ignored.
This means once the response is sent out, you will not be able to append more content to it.

As you can see, the [[yii\web\Response::send()]] method triggers several useful events. By responding to
these events, it is possible to adjust or decorate the response.
