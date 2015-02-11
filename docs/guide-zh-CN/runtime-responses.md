响应
=========

当应用完成处理一个[请求](runtime-requests.md)后, 会生成一个[[yii\web\Response|response]]响应对象并发送给终端用户
响应对象包含的信息有HTTP状态码，HTTP头和主体内容等, 网页应用开发的最终目的本质上就是根据不同的请求构建这些响应对象。

在大多是情况下主要处理继承自 [[yii\web\Response]] 的 `response` [应用组件](structure-application-components.md)，
尽管如此，Yii也允许你创建你自己的响应对象并发送给终端用户，这方面后续会阐述。

在本节，将会描述如何构建响应和发送给终端用户。


## 状态码 <span id="status-code"></span>

构建响应时，最先应做的是标识请求是否成功处理的状态，可通过设置
[[yii\web\Response::statusCode]] 属性，该属性使用一个有效的
[HTTP 状态码](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html)。例如，为标识处理已被处理成功，
可设置状态码为200，如下所示：

```php
Yii::$app->response->statusCode = 200;
```

尽管如此，大多数情况下不需要明确设置状态码，因为 [[yii\web\Response::statusCode]] 状态码默认为200，
如果需要指定请求失败，可抛出对应的HTTP异常，如下所示：

```php
throw new \yii\web\NotFoundHttpException;
```

当[错误处理器](runtime-handling-errors.md) 捕获到一个异常，会从异常中提取状态码并赋值到响应，
对于上述的 [[yii\web\NotFoundHttpException]] 对应HTTP 404状态码，以下为Yii预定义的HTTP异常：

* [[yii\web\BadRequestHttpException]]: status code 400.
* [[yii\web\ConflictHttpException]]: status code 409.
* [[yii\web\ForbiddenHttpException]]: status code 403.
* [[yii\web\GoneHttpException]]: status code 410.
* [[yii\web\MethodNotAllowedHttpException]]: status code 405.
* [[yii\web\NotAcceptableHttpException]]: status code 406.
* [[yii\web\NotFoundHttpException]]: status code 404.
* [[yii\web\ServerErrorHttpException]]: status code 500.
* [[yii\web\TooManyRequestsHttpException]]: status code 429.
* [[yii\web\UnauthorizedHttpException]]: status code 401.
* [[yii\web\UnsupportedMediaTypeHttpException]]: status code 415.

如果想抛出的异常不在如上列表中，可创建一个[[yii\web\HttpException]]异常，带上状态码抛出，如下：

```php
throw new \yii\web\HttpException(402);
```


## HTTP 头部 <span id="http-headers"></span>

可在 `response` 组件中操控[[yii\web\Response::headers|header collection]]来发送HTTP头部信息，例如：

```php
$headers = Yii::$app->response->headers;

// 增加一个 Pragma 头，已存在的Pragma 头不会被覆盖。
$headers->add('Pragma', 'no-cache');

// 设置一个Pragma 头. 任何已存在的Pragma 头都会被丢弃
$headers->set('Pragma', 'no-cache');

// 删除Pragma 头并返回删除的Pragma 头的值到数组
$values = $headers->remove('Pragma');
```

> 补充: 头名称是大小写敏感的，在[[yii\web\Response::send()]]方法调用前新注册的头信息并不会发送给用户。


## 响应主体 <span id="response-body"></span>

大多是响应应有一个主体存放你想要显示给终端用户的内容。

如果已有格式化好的主体字符串，可赋值到响应的[[yii\web\Response::content]]属性，例如：

```php
Yii::$app->response->content = 'hello world!';
```

如果在发送给终端用户之前需要格式化，应设置
[[yii\web\Response::format|format]] 和 [[yii\web\Response::data|data]] 属性，[[yii\web\Response::format|format]]
属性指定[[yii\web\Response::data|data]]中数据格式化后的样式，例如：

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii支持以下可直接使用的格式，每个实现了[[yii\web\ResponseFormatterInterface|formatter]] 类，
可自定义这些格式器或通过配置[[yii\web\Response::formatters]] 属性来增加格式器。

* [[yii\web\Response::FORMAT_HTML|HTML]]: 通过 [[yii\web\HtmlResponseFormatter]] 来实现.
* [[yii\web\Response::FORMAT_XML|XML]]: 通过 [[yii\web\XmlResponseFormatter]]来实现.
* [[yii\web\Response::FORMAT_JSON|JSON]]: 通过 [[yii\web\JsonResponseFormatter]]来实现.
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: 通过 [[yii\web\JsonResponseFormatter]]来实现.

上述响应主体可明确地被设置，但是在大多数情况下是通过 [操作](structure-controllers.md) 方法的返回值隐式地设置，常用场景如下所示：
 
```php
public function actionIndex()
{
    return $this->render('index');
}
```

上述的 `index` 操作返回 `index` 视图渲染结果，返回值会被 `response` 组件格式化后发送给终端用户。

因为响应格式默认为[[yii\web\Response::FORMAT_HTML|HTML]], 只需要在操作方法中返回一个字符串，
如果想使用其他响应格式，应在返回数据前先设置格式，例如：

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

如上所述，触雷使用默认的 `response` 应用组件，也可创建自己的响应对象并发送给终端用户，可在操作方法中返回该响应对象，如下所示：

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

> 注意: 如果创建你自己的响应对象，将不能在应用配置中设置 `response` 组件，尽管如此，
  可使用 [依赖注入](concept-di-container.md) 应用通用配置到你新的响应对象。


## 浏览器跳转 <span id="browser-redirection"></span>

浏览器跳转依赖于发送一个`Location` HTTP 头，因为该功能通常被使用，Yii提供对它提供了特别的支持。

可调用[[yii\web\Response::redirect()]] 方法将用户浏览器跳转到一个URL地址，该方法设置合适的
带指定URL的 `Location` 头并返回它自己为响应对象，在操作的方法中，可调用缩写版[[yii\web\Controller::redirect()]]，例如：

```php
public function actionOld()
{
    return $this->redirect('http://example.com/new', 301);
}
```

在如上代码中，操作的方法返回`redirect()` 方法的结果，如前所述，操作的方法返回的响应对象会被当总响应发送给终端用户。

除了操作方法外，可直接调用[[yii\web\Response::redirect()]] 再调用
[[yii\web\Response::send()]] 方法来确保没有其他内容追加到响应中。

```php
\Yii::$app->response->redirect('http://example.com/new', 301)->send();
```

> 补充: [[yii\web\Response::redirect()]] 方法默认会设置响应状态码为302，该状态码会告诉浏览器请求的资源
  *临时* 放在另一个URI地址上，可传递一个301状态码告知浏览器请求的资源已经 *永久* 重定向到新的URId地址。

如果当前请求为AJAX 请求，发送一个 `Location` 头不会自动使浏览器跳转，为解决这个问题，
[[yii\web\Response::redirect()]] 方法设置一个值为要跳转的URL的`X-Redirect` 头，
在客户端可编写JavaScript 代码读取该头部值然后让浏览器跳转对应的URL。

> 补充: Yii 配备了一个`yii.js` JavaScript 文件提供常用JavaScript功能，包括基于`X-Redirect`头的浏览器跳转，
  因此，如果你使用该JavaScript 文件(通过[[yii\web\YiiAsset]] 资源包注册)，就不需要编写AJAX跳转的代码。


## 发送文件 <span id="sending-files"></span>

和浏览器跳转类似，文件发送是另一个依赖指定HTTP头的功能，Yii提供方法集合来支持各种文件发送需求，它们对HTTP头都有内置的支持。

* [[yii\web\Response::sendFile()]]: 发送一个已存在的文件到客户端
* [[yii\web\Response::sendContentAsFile()]]: 发送一个文本字符串作为文件到客户端
* [[yii\web\Response::sendStreamAsFile()]]: 发送一个已存在的文件流作为文件到客户端

这些方法都将响应对象作为返回值，如果要发送的文件非常大，应考虑使用
[[yii\web\Response::sendStreamAsFile()]] 因为它更节约内存，以下示例显示在控制器操作中如何发送文件：

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('path/to/file.txt');
}
```

如果不是在操作方法中调用文件发送方法，在后面还应调用 [[yii\web\Response::send()]] 没有其他内容追加到响应中。

```php
\Yii::$app->response->sendFile('path/to/file.txt')->send();
```

一些浏览器提供特殊的名为*X-Sendfile*的文件发送功能，原理为将请求跳转到服务器上的文件，
Web应用可在服务器发送文件前结束，为使用该功能，可调用[[yii\web\Response::xSendFile()]]，
如下简要列出一些常用Web服务器如何启用`X-Sendfile` 功能：

- Apache: [X-Sendfile](http://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](http://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](http://wiki.nginx.org/XSendfile)
- Cherokee: [X-Sendfile and X-Accel-Redirect](http://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)


## 发送响应 <span id="sending-response"></span>

在[[yii\web\Response::send()]] 方法调用前响应中的内容不会发送给用户，该方法默认在[[yii\base\Application::run()]]
结尾自动调用，尽管如此，可以明确调用该方法强制立即发送响应。

[[yii\web\Response::send()]] 方法使用以下步骤来发送响应：

1. 触发 [[yii\web\Response::EVENT_BEFORE_SEND]] 事件.
2. 调用 [[yii\web\Response::prepare()]] 来格式化 [[yii\web\Response::data|response data]] 为 
   [[yii\web\Response::content|response content]].
3. 触发 [[yii\web\Response::EVENT_AFTER_PREPARE]] 事件.
4. 调用 [[yii\web\Response::sendHeaders()]] 来发送注册的HTTP头
5. 调用 [[yii\web\Response::sendContent()]] 来发送响应主体内容
6. 触发 [[yii\web\Response::EVENT_AFTER_SEND]] 事件.

一旦[[yii\web\Response::send()]] 方法被执行后，其他地方调用该方法会被忽略，
这意味着一旦响应发出后，就不能再追加其他内容。

如你所见[[yii\web\Response::send()]] 触发了几个实用的事件，通过响应这些事件可调整或包装响应。
