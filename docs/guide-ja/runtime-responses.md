レスポンス
==========

アプリケーションは [リクエスト](runtime-requests.md) の処理を完了すると、[[yii\web\Response|レスポンス]] オブジェクトを生成して、エンドユーザに送信します。
レスポンスオブジェクトは、HTTP ステータスコード、HTTP ヘッダ、HTTP ボディなどの情報を含みます。
ウェブアプリケーション開発の最終的な目的は、本質的には、さまざまなリクエストに対してそのようなレスポンスオブジェクトを作成することにあります。

ほとんどの場合は、主として、デフォルトでは [[yii\web\Response]] のインスタンスである `response` [アプリケーションコンポーネント](structure-application-components.md) を使用すべきです。
しかしながら、Yii は、以下で説明するように、あなた自身のレスポンスオブジェクトを作成してエンドユーザに送信することも許容しています。

この節では、レスポンスを構成してエンドユーザに送信する方法を説明します。


## ステータスコード <span id="status-code"></span>

レスポンスを作成するときに最初にすることの一つは、リクエストが成功裡に処理されたかどうかを記述することです。
そのためには、[[yii\web\Response::statusCode]] プロパティに有効な [HTTP ステータスコード](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html) の一つを設定します。
例えば、下記のように、リクエストの処理が成功したことを示すために、ステータスコードを 200 に設定します。

```php
Yii::$app->response->statusCode = 200;
```

ただし、たいていの場合、ステータスコードを明示的に設定する必要はありません。
これは、[[yii\web\Response::statusCode]] のデフォルト値が 200 であるからです。
そして、リクエストが失敗したことを示したいときは、下記のように、適切な HTTP 例外を投げることが出来ます。

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
あるいは、ステータスコードを指定して [[yii\web\HttpException]] を直接に投げることも出来ます。
例えば、

```php
throw new \yii\web\HttpException(402);
```


## HTTP ヘッダ <span id="http-headers"></span> 

`response` コンポーネントの [[yii\web\Response::headers|ヘッダコレクション]] を操作することによって、HTTP ヘッダを送信することが出来ます。
例えば、

```php
$headers = Yii::$app->response->headers;

// Pragma ヘッダを追加する。既存の Pragma ヘッダは上書きされない。
$headers->add('Pragma', 'no-cache');

// Pragma ヘッダを設定する。既存の Pragma ヘッダは全て破棄される。
$headers->set('Pragma', 'no-cache');

// Pragma ヘッダを削除して、削除された Pragma ヘッダの値を配列に返す。
$values = $headers->remove('Pragma');
```

> Info|情報: ヘッダ名は大文字小文字を区別しません。
  そして、新しく登録されたヘッダは、[[yii\web\Response::send()]] メソッドが呼ばれるまで送信されません。


## レスポンスボディ <span id="response-body"></span>

ほとんどのレスポンスは、エンドユーザに対して表示したい内容を示すボディを持っていなければなりません。

既にフォーマットされたボディの文字列を持っている場合は、それをレスポンスの [[yii\web\Response::content]] プロパティに割り付けることが出来ます。
例えば、

```php
Yii::$app->response->content = 'hello world!';
```

データをエンドユーザに送信する前にフォーマットする必要がある場合は、[[yii\web\Response::format|format]] と [[yii\web\Response::data|data]] の両方のプロパティをセットしなければなりません。
[[yii\web\Response::format|format]] プロパティは [[yii\web\Response::data|data]] がどの形式でフォーマットされるべきかを指定するものです。
例えば、

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii は下記の形式を初めからサポートしています。
それぞれ、[[yii\web\ResponseFormatterInterface|フォーマッタ]] クラスとして実装されています。
[[yii\web\Response::formatters]] プロパティを構成することで、これらのフォーマッタをカスタマイズしたり、新しいフォーマッタを追加したりすることが出来ます。

* [[yii\web\Response::FORMAT_HTML|HTML]]: [[yii\web\HtmlResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_XML|XML]]: [[yii\web\XmlResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_JSON|JSON]]: [[yii\web\JsonResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: [[yii\web\JsonResponseFormatter]] によって実装
* [[yii\web\Response::FORMAT_RAW|RAW]]: 書式を何も適用せずにレスポンスを送信したいときは、このフォーマットを使用

レスポンスボディは、上記のように、明示的に設定することも出来ますが、たいていの場合は、[アクション](structure-controllers.md) メソッドの返り値によって暗黙のうちに設定することが出来ます。
よくあるユースケースは下記のようなものになります。

```php
public function actionIndex()
{
    return $this->render('index');
}
```

上記の `index` アクションは、`index` ビューのレンダリング結果を返しています。
返された値は `response` コンポーネントによって受け取られ、フォーマットされてエンドユーザに送信されます。

デフォルトのレスポンス形式が [[yii\web\Response::FORMAT_HTML|HTML]] であるため、アクションメソッドの中では文字列を返すだけにすべきです。
別のレスポンス形式を使いたい場合は、データを返す前にレスポンス形式を設定しなければなりません。
例えば、

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

既に述べたように、デフォルトの `response` アプリケーションコンポーネントを使う代りに、自分自身のレスポンスオブジェクトを作成してエンドユーザに送信することも出来ます。
そうするためには、次のように、アクションメソッドの中でそのようなオブジェクトを返します。

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

> Note|注意: 自分自身のレスポンスオブジェクトを作成しようとする場合は、アプリケーションの構成情報で `response` コンポーネントのために設定した構成情報を利用することは出来ません。
  しかし、 [依存の注入](concept-di-container.md) を使えば、 共通の構成情報をあなたの新しいレスポンスオブジェクトに適用することが出来ます。


## ブラウザのリダイレクト <span id="browser-redirection"></span>

ブラウザのリダイレクトは `Location` HTTP ヘッダの送信に依存しています。
この機能は通常よく使われるものであるため、Yii はこれについて特別のサポートを提供しています。

[[yii\web\Response::redirect()]] メソッドを呼ぶことによって、ユーザのブラウザをある URL にリダイレクトすることが出来ます。
このメソッドは与えられた URL を持つ適切な `Location` ヘッダを設定して、レスポンスオブジェクトそのものを返します。
アクションメソッドの中では、そのショートカット版である [[yii\web\Controller::redirect()]] を呼ぶことが出来ます。
例えば、

```php
public function actionOld()
{
    return $this->redirect('http://example.com/new', 301);
}
```

上記のコードでは、アクションメソッドが `redirect()` メソッドの結果を返しています。
前に説明したように、アクションメソッドによって返されるレスポンスオブジェクトが、エンドユーザに送信されるレスポンスとして使用されることになります。

アクションメソッド以外の場所では、[[yii\web\Response::redirect()]] を直接に呼び出し、メソッドチェーンで [[yii\web\Response::send()]] メソッドを呼んで、レスポンスに余計なコンテントが追加されないことを保証しなければなりません。

```php
\Yii::$app->response->redirect('http://example.com/new', 301)->send();
```

> Info|情報: デフォルトでは、[[yii\web\Response::redirect()]] メソッドはレスポンスのステータスコードを 302 にセットします。
これはブラウザに対して、リクエストされているリソースが *一時的に* 異なる URI に配置されていることを示すものです。
ブラウザに対してリソースが *恒久的に* 配置替えされたことを教えるためには、ステータスコード 301 を渡すことが出来ます。

現在のリクエストが AJAX リクエストである場合は、`Location` ヘッダを送っても自動的にブラウザをリダイレクトすることにはなりません。
この問題を解決するために、[[yii\web\Response::redirect()]] メソッドは `X-Redirect` ヘッダにリダイレクト先 URL を値としてセットします。
そして、クライアントサイドで、このヘッダの値を読み、それに応じてブラウザをリダイレクトする JavaScript を書くことが出来ます。

> Info|情報: Yii には `yii.js` という JavaScript ファイルが付属しています。
これは、よく使われる一連の JavaScript 機能を提供するもので、その中には `X-Redirect` ヘッダに基づくブラウザのリダイレクトも含まれています。
従って、あなたが ([[yii\web\YiiAsset]] アセットバンドルを登録して) この JavaScript ファイルを使うつもりなら、AJAX のリダイレクトをサポートするためには、何も書く必要がなくなります。


## ファイルを送信する <span id="sending-files"></span>

ブラウザのリダイレクトと同じように、ファイルの送信という機能も特定の HTTP ヘッダに依存しています。
Yii はさまざまなファイル送信の必要をサポートするための一連のメソッドを提供しています。それらはすべて、HTTP range ヘッダに対するサポートを内蔵しています。

* [[yii\web\Response::sendFile()]]: 既存のファイルをクライアントに送信する
* [[yii\web\Response::sendContentAsFile()]]: テキストの文字列をファイルとしてクライアントに送信する
* [[yii\web\Response::sendStreamAsFile()]]: 既存のファイルストリームをファイルとしてクライアントに送信する

これらのメソッドは同じメソッドシグニチャを持ち、返り値としてレスポンスオブジェクトを返します。
送信しようとしているファイルが非常に大きなものである場合は、メモリ効率の良い [[yii\web\Response::sendStreamAsFile()]] の使用を検討すべきです。
次の例は、コントローラアクションでファイルを送信する方法を示すものです。

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('path/to/file.txt');
}
```

ファイル送信メソッドをアクションメソッド以外の場所で呼ぶ場合は、その後で [[yii\web\Response::send()]] メソッドも呼んで、レスポンスに余計なコンテントが追加されないことを保証しなければなりません。

```php
\Yii::$app->response->sendFile('path/to/file.txt')->send();
```

ウェブサーバには、*X-Sendfile* と呼ばれる特別なファイル送信をサポートするものがあります。
アイデアとしては、ファイルに対するリクエストをウェブサーバにリダイレクトして、ウェブサーバに直接にファイルを送信させる、というものです。
その結果として、ウェブサーバがファイルを送信している間でも、ウェブアプリケーションは早期に終了することが出来るようになります。
この機能を使うために、[[yii\web\Response::xSendFile()]] を呼ぶことが出来ます。
次のリストは、よく使われるいくつかのウェブサーバにおいて `X-Sendfile` 機能を有効にする方法を要約するものです。

- Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
- Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)


## レスポンスを送信する <span id="sending-response"></span>

レスポンスの中のコンテントは、[[yii\web\Response::send()]] メソッドが呼ばれるまでは、エンドユーザに向けて送信されません。
デフォルトでは、このメソッドは [[yii\base\Application::run()]] の最後で自動的に呼ばれます。
しかし、このメソッドを明示的に呼んで、強制的にレスポンスを即座に送信することも可能です。

[[yii\web\Response::send()]] メソッドは次のステップを踏んでレスポンスを送出します。

1. [[yii\web\Response::EVENT_BEFORE_SEND]] イベントをトリガする。
2. [[yii\web\Response::prepare()]] を呼んで [[yii\web\Response::data|レスポンスデータ]] を
   [[yii\web\Response::content|レスポンスコンテント]] としてフォーマットする。
3. [[yii\web\Response::EVENT_AFTER_PREPARE]] イベントをトリガする。
4. [[yii\web\Response::sendHeaders()]] を呼んで、登録された HTTP ヘッダを送出する。
5. [[yii\web\Response::sendContent()]] を呼んで、レスポンスのボディコンテントを送出する。
6. [[yii\web\Response::EVENT_AFTER_SEND]] イベントをトリガする。

[[yii\web\Response::send()]] メソッドが一度呼び出された後では、このメソッドに対する更なる呼び出しは無視されます。
このことは、いったんレスポンスが送出された後では、それにコンテントを追加することは出来なくなる、ということを意味します。

ごらんのように、[[yii\web\Response::send()]] メソッドはいくつかの有用なイベントをトリガします。
これらのイベントに反応することによって、レスポンスを調整したり修飾したりすることが出来ます。
