错误处理
===============

Yii 内置了一个[[yii\web\ErrorHandler|error handler]]错误处理器，它使错误处理更方便，
Yii错误处理器做以下工作来提升错误处理效果：

* 所有非致命PHP错误（如，警告，提示）会转换成可获取异常；
* 异常和致命的PHP错误会被显示，
  在调试模式会显示详细的函数调用栈和源代码行数。
* 支持使用专用的 [控制器操作](structure-controllers.md#actions) 来显示错误；
* 支持不同的错误响应格式；

[[yii\web\ErrorHandler|error handler]] 错误处理器默认启用，
可通过在应用的[入口脚本](structure-entry-scripts.md)中定义常量`YII_ENABLE_ERROR_HANDLER`来禁用。


## 使用错误处理器 <span id="using-error-handler"></span>

[[yii\web\ErrorHandler|error handler]] 注册成一个名称为`errorHandler`[应用组件](structure-application-components.md)， 
可以在应用配置中配置它类似如下：

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

使用如上代码，异常页面最多显示20条源代码。

如前所述，错误处理器将所有非致命PHP错误转换成可获取异常，
也就是说可以使用如下代码处理PHP错误：

```php
use Yii;
use yii\base\ErrorException;

try {
    10/0;
} catch (ErrorException $e) {
    Yii::warning("Division by zero.");
}

// execution continues...
```

如果你想显示一个错误页面告诉用户请求是无效的或无法处理的，
可简单地抛出一个 [[yii\web\HttpException|HTTP exception]]异常，
如 [[yii\web\NotFoundHttpException]]。
错误处理器会正确地设置响应的HTTP状态码并使用合适的错误视图页面来显示错误信息。

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```


## 自定义错误显示 <span id="customizing-error-display"></span>

[[yii\web\ErrorHandler|error handler]]错误处理器根据常量`YII_DEBUG`的值来调整错误显示，
当`YII_DEBUG` 为 true (表示在调试模式)，
错误处理器会显示异常以及详细的函数调用栈和源代码行数来帮助调试，
当`YII_DEBUG` 为 false，只有错误信息会被显示以防止应用的敏感信息泄漏。

> Info: 如果异常是继承 [[yii\base\UserException]]，
  不管`YII_DEBUG`为何值，函数调用栈信息都不会显示，
  这是因为这种错误会被认为是用户产生的错误，开发人员不需要去修正。

[[yii\web\ErrorHandler|error handler]] 错误处理器默认使用两个[视图](structure-views.md)显示错误:

* `@yii/views/errorHandler/error.php`: 显示不包含函数调用栈信息的错误信息是使用，
  当`YII_DEBUG` 为 false时，所有错误都使用该视图。
* `@yii/views/errorHandler/exception.php`: 显示包含函数调用栈信息的错误信息时使用。

可以配置错误处理器的 [[yii\web\ErrorHandler::errorView|errorView]] 和 [[yii\web\ErrorHandler::exceptionView|exceptionView]] 属性
使用自定义的错误显示视图。


### 使用错误动作 <span id="using-error-actions"></span>

使用指定的错误[操作](structure-controllers.md) 来自定义错误显示更方便，
为此，首先配置`errorHandler`组件的 [[yii\web\ErrorHandler::errorAction|errorAction]] 属性，
类似如下： 

```php
return [
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ]
];
```

[[yii\web\ErrorHandler::errorAction|errorAction]] 属性使用
[路由](structure-controllers.md#routes)到一个操作，
上述配置表示不用显示函数调用栈信息的错误会通过执行`site/error`操作来显示。

可以创建 `site/error` 动作如下所示：

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

上述代码定义`error` 操作使用[[yii\web\ErrorAction]] 类，
该类渲染名为`error`视图来显示错误。

除了使用[[yii\web\ErrorAction]], 可定义`error` 动作使用类似如下的操作方法：

```php
public function actionError()
{
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

现在应创建一个视图文件为`views/site/error.php`，在该视图文件中，如果错误动作定义为[[yii\web\ErrorAction]]，
可以访问该动作中定义的如下变量：

* `name`: 错误名称
* `message`: 错误信息
* `exception`: 更多详细信息的异常对象，如HTTP 状态码，
  错误码，错误调用栈等。

> Note: 如果你使用 [基础应用模板](start-installation.md) 或 [高级应用模板](tutorial-advanced-app.md),
错误动作和错误视图已经定义好了。

> Note: 如果您需要在错误处理程序中重定向，请执行以下操作：
>
> ```php
> Yii::$app->getResponse()->redirect($url)->send();
> return;
> ```


### 自定义错误格式 <span id="error-format"></span>

错误处理器根据[响应](runtime-responses.md)设置的格式来显示错误，
如果[[yii\web\Response::format|response format]] 响应格式为`html`, 
会使用错误或异常视图来显示错误信息，如上一小节所述。
对于其他的响应格式，错误处理器会错误信息作为数组赋值
给[[yii\web\Response::data]]属性，然后转换到对应的格式，
例如，如果响应格式为`json`，可以看到如下响应信息：

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

可在应用配置中响应`response`组件的
`beforeSend`事件来自定义错误响应格式。

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

上述代码会重新格式化错误响应，类似如下：

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
