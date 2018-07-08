エラー処理
==========

Yii が内蔵している [[yii\web\ErrorHandler|エラー・ハンドラ]] は、エラー処理を従来よりはるかに快適な経験にしてくれます。
具体的には、Yii のエラー・ハンドラはエラー処理をより良くするために、次のことを行います。

* 致命的でない全ての PHP エラー (警告や通知) は捕捉可能な例外に変換されます。
* 例外および致命的 PHP エラーは、デバッグ・モードでは、
  詳細なコール・スタック情報とソース・コード行とともに表示されます。
* エラーを表示するために専用の [コントローラ・アクション](structure-controllers.md#actions) を使うことがサポートされています。
* さまざまなエラー・レスポンス形式をサポートしています。

[[yii\web\ErrorHandler|エラー・ハンドラ]] はデフォルトで有効になっています。
アプリケーションの [エントリ・スクリプト](structure-entry-scripts.md) において、定数 `YII_ENABLE_ERROR_HANDLER` を `false` と定義することによって、これを無効にすることが出来ます。


## エラー・ハンドラを使用する <span id="using-error-handler"></span>

[[yii\web\ErrorHandler|エラー・ハンドラ]] は `errorHandler` という名前の [アプリケーション・コンポーネント](structure-application-components.md) です。
次のように、アプリケーションの構成情報でこれをカスタマイズすることが出来ます。

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

上記の構成によって、例外ページで表示されるソース・コードの行数は最大で 20 までとなります。

既に述べたように、エラー・ハンドラは致命的でない全ての PHP エラーを捕捉可能な例外に変換します。
これは、次のようなコードを使って PHP エラーを処理することが出来るということを意味します。

```php
use Yii;
use yii\base\ErrorException;

try {
    10/0;
} catch (ErrorException $e) {
    Yii::warning("0 による除算。");
}

// 実行を継続 ...
```

リクエストが無効または予期しないものであることをユーザに知らせるエラー・ページを表示したい場合は、
単に [[yii\web\NotFoundHttpException]] のような [[yii\web\HttpException|HTTP 例外]] を投げるだけで済ませることが出来ます。
そうすれば、エラー・ハンドラがレスポンスの HTTP ステータス・コードを正しく設定し、
適切なエラー・ビューを使ってエラー・メッセージを表示してくれます。

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```


## エラー表示をカスタマイズする <span id="customizing-error-display"></span>

[[yii\web\ErrorHandler|エラー・ハンドラ]] は、定数 `YII_DEBUG` の値に従って、エラー表示を調整します。
`YII_DEBUG` が `true` である (デバッグ・モードである) 場合は、エラー・ハンドラは、デバッグがより容易になるように、
例外とともに、詳細なコール・スタック情報とソース・コード行を表示します。
そして、`YII_DEBUG` が `false` のときは、アプリケーションに関する公開できない情報の開示を防ぐために、エラー・メッセージだけが表示されます。

> Info: 例外が [[yii\base\UserException]] の子孫である場合は、`YII_DEBUG` の値の如何にかかわらず、コール・スタックは表示されません。
これは、この種の例外はユーザの誤操作によって引き起こされるものであり、
開発者は何も修正する必要がないと考えられるからです。

デフォルトでは、[[yii\web\ErrorHandler|エラー・ハンドラ]] は二つの [ビュー](structure-views.md) を使ってエラーを表示します。

* `@yii/views/errorHandler/error.php`: エラーがコール・スタック情報なしで表示されるべき場合に使用されます。
  `YII_DEBUG` が `false` の場合、これが表示される唯一のビューとなります。
* `@yii/views/errorHandler/exception.php`: エラーがコール・スタック情報と共に表示されるべき場合に使用されます。

エラー表示をカスタマイズするために、エラー・ハンドラの [[yii\web\ErrorHandler::errorView|errorView]] および [[yii\web\ErrorHandler::exceptionView|exceptionView]] プロパティを構成して、
自分自身のビューを使用することが出来ます。


### エラー・アクションを使う <span id="using-error-actions"></span>

エラー表示をカスタマイズするためのもっと良い方法は、専用のエラー [アクション](structure-controllers.md) を使うことです。
そうするためには、まず、`errorHandler` コンポーネントの [[yii\web\ErrorHandler::errorAction|errorAction]]
プロパティを次のように構成します。

```php
return [
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ]
];
```

[[yii\web\ErrorHandler::errorAction|errorAction]] プロパティは、アクションへの [ルート](structure-controllers.md#routes) を値として取ります。
上記の構成は、エラーをコール・スタック情報なしで表示する必要がある場合は、
`site/error` アクションが実行されるべきことを記述しています。

`site/error` アクションは次のようにして作成することが出来ます。

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
```

上記のコードは [[yii\web\ErrorAction]] クラスを使って `error` アクションを定義しています。
[[yii\web\ErrorAction]] クラスは `error` という名前のビューを使ってエラーをレンダリングします。

[[yii\web\ErrorAction]] を使う以外に、次のようにアクション・メソッドを使って `error` アクションを定義することも出来ます。

```php
public function actionError()
{
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

次に `views/site/error.php` に配置されるビュー・ファイルを作成しなければなりません。
エラー・アクションが [[yii\web\ErrorAction]] として定義されている場合は、このビュー・ファイルの中で次の変数にアクセスすることが出来ます。

* `name`: エラーの名前。
* `message`: エラー・メッセージ。
* `exception`: 例外オブジェクト。これを通じて、更に有用な情報、例えば、HTTP ステータス・コード、エラー・コード、
  エラー・コール・スタックなどにアクセスすることが出来ます。

> Info: あなたが [ベーシック・プロジェクト・テンプレート](start-installation.md) または [アドバンスト・プロジェクト・テンプレート](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-ja/README.md) を使っている場合は、
  エラー・アクションとエラー・ビューは、既にあなたのために定義されています。

> Note: エラー・ハンドラの中でリダイレクトする必要がある場合は、次のようにしてください。
>
> ```php
> Yii::$app->getResponse()->redirect($url)->send();
> return;
> ```


### エラーのレスポンス形式をカスタマイズする <span id="error-format"></span>

エラー・ハンドラは、[レスポンス](runtime-responses.md) 形式の設定に従ってエラーを表示します。
[[yii\web\Response::format|レスポンス形式]] が `html` である場合は、直前の項で説明したように、
エラー・ビューまたは例外ビューを使ってエラーを表示します。
その他のレスポンス形式の場合は、エラー・ハンドラは例外の配列表現を [[yii\web\Response::data]] プロパティに代入し、
次に `data` プロパティを様々な形式に変換します。
例えば、レスポンス形式が `json` である場合は、次のようなレスポンスになります。

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "リクエストされたリソースは見つかりませんでした。",
    "code": 0,
    "status": 404
}
```

エラーのレスポンス形式をカスタマイズするために、アプリケーションの構成情報の中で、
`response` コンポーネントの `beforeSend` イベントに反応するハンドラを構成することが出来ます。

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

上記のコードは、エラーのレスポンスを以下のようにフォーマットし直すものです。

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "リクエストされたリソースは見つかりませんでした。",
        "code": 0,
        "status": 404
    }
}
```
