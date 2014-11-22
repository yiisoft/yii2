エラー処理
==========

Yii は、エラー処理を従来よりはるかに快適な経験にしてくれる、内臓の [[yii\web\ErrorHandler|エラーハンドラ]]
を持っています。具体的には、Yii のエラーハンドラはエラー処理を向上させるために、次のことを行います。

* 致命的でない全ての PHP エラー (警告や通知) は捕捉可能な例外に変換されます。
* 例外と致命的な PHP エラーは、デバッグモードでは、詳細なコールスタック情報とソースコード行とともに表示されます。
* エラーを表示するために専用の [コントローラアクション](structure-actions.md) を使うことがサポートされています。
* さまざまなエラーレスポンス形式をサポートしています。

[[yii\web\ErrorHandler|エラーハンドラ]] は既定で有効になっています。アプリケーションの [エントリスクリプト](structure-entry-scripts.md)
において、定数 `YII_ENABLE_ERROR_HANDLER` を false と定義することによって、これを無効にすることが出来ます。


## エラーハンドラを使用する <a name="using-error-handler"></a>

[[yii\web\ErrorHandler|エラーハンドラ]] は `errorHandler` という名前の [アプリケーションコンポーネント](structure-application-components.md) です。
次のように、アプリケーションのコンフィギュレーションでこれをカスタマイズすることが出来ます。

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

上記のコンフィギュレーションによって、例外ページで表示されるソースコードの行数は最大で 20 までとなります。

既に述べたように、エラーハンドラは致命的でない全ての PHP エラーを捕捉可能な例外に変換します。
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

ユーザに対して、リクエストが無効であったり予期しないものであったりすることを知らせるエラーページを表示したい場合は、
単に [[yii\web\NotFoundHttpException]] のような [[yii\web\HttpException|HTTP 例外]] を投げるだけで済ませることが出来ます。
エラーハンドラがレスポンスの HTTP ステータスコードを正しく設定し、適切なエラービューを使ってエラーメッセージを表示してくれます。

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```


## エラー表示をカスタマイズする <a name="customizing-error-display"></a>

[[yii\web\ErrorHandler|エラーハンドラ]] は、定数 `YII_DEBUG` の値に従って、エラー表示を調整します。
`YII_DEBUG` が true である (デバッグモードである) 場合は、エラーハンドラは、デバッグがより容易になるように、
詳細なコールスタック情報とソースコード行とともに例外を表示します。そして、`YII_DEBUG` が false のときは、
アプリケーションに関する公開できない情報を開示することを防ぐために、エラーメッセージだけが表示されます。

> Info|情報: 例外が [[yii\base\UserException]] の子孫である場合は、`YII_DEBUG` の値の如何にかかわらず、コールスタックは表示されません。
これは、この種の例外はユーザの誤操作によって引き起こされるものであり、開発者は何も修正する必要がないと考えられるからです。

既定では、[[yii\web\ErrorHandler|エラーハンドラ]] は二つの [views](structure-views.md) を使ってエラーを表示します。

* `@yii/views/errorHandler/error.php`: エラーがコールスタック情報なしで表示されるべき場合に使用されます。
  `YII_DEBUG` が false の場合、これが表示される唯一のビューとなります。
* `@yii/views/errorHandler/exception.php`: エラーがコールスタック情報と共に表示されるべき場合に使用されます。

エラー表示をカスタマイズするために、エラーハンドラの [[yii\web\ErrorHandler::errorView|errorView]] および
[[yii\web\ErrorHandler::exceptionView|exceptionView]] プロパティを構成して、自分自身のビューを使用することが出来ます。


### エラーアクションを使う <a name="using-error-actions"></a>

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
上記のコンフィギュレーションは、エラーをコールスタック情報なしで表示する必要がある場合は、`site/error`
アクションが実行されるべきであることを記述しています。

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

The above code defines the `error` action using the [[yii\web\ErrorAction]] class which renders an error
using a view named `error`.

Besides using [[yii\web\ErrorAction]], you may also define the `error` action using an action method like the following,

```php
public function actionError()
{
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

You should now create a view file located at `views/site/error.php`. In this view file, you can access
the following variables if the error action is defined as [[yii\web\ErrorAction]]:

* `name`: the name of the error;
* `message`: the error message;
* `exception`: the exception object through which you can more useful information, such as HTTP status code,
  error code, error call stack, etc.

> Info: If you are using the [basic application template](start-installation.md) or the [advanced application template](tutorial-advanced-app.md),
the error action and the error view are already defined for you.


### Customizing Error Response Format <a name="error-format"></a>

The error handler displays errors according to the format setting of the [response](runtime-responses.md).
If the the [[yii\web\Response::format|response format]] is `html`, it will use the error or exception view
to display errors, as described in the last subsection. For other response formats, the error handler will
assign the array representation of the exception to the [[yii\web\Response::data]] property which will then
be converted to different formats accordingly. For example, if the response format is `json`, you may see
the following response:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

You may customize the error response format by responding to the `beforeSend` event of the `response` component
in the application configuration:

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

The above code will reformat the error response like the following:

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
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```
