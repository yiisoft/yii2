Responses
=========

When an application finishes handling a [request](runtime-requests.md), it generates a [[yii\web\Response|response]] object
and sends it to the end user. The response object contains information such as the HTTP status code, HTTP headers and body.
The ultimate goal of Web application development is essentially to build such response objects upon various requests.

In most cases you should mainly deal with the `response` [application component](structure-application-components.md)
which is an instance of [[yii\web\Response]], by default. However, Yii also allows you to create your own response
objects and send them to end users as we will explain in the following.

In this section, we will describe how to compose and send responses to end users. 


## Status Code <span id="status-code"></span>

One of the first things you would do when building a response is to state whether the request is successfully handled.
This is done by setting the [[yii\web\Response::statusCode]] property which can take one of the valid
[HTTP status codes](https://tools.ietf.org/html/rfc2616#section-10). For example, to indicate the request
is successfully handled, you may set the status code to be 200, like the following:

```php
Yii::$app->response->statusCode = 200;
```

However, in most cases you do not need to explicitly set the status code. This is because the default value
of [[yii\web\Response::statusCode]] is 200. And if you want to indicate the request is unsuccessful, you may
throw an appropriate HTTP exception like the following:

```php
throw new \yii\web\NotFoundHttpException;
```

When the [error handler](runtime-handling-errors.md) catches an exception, it will extract the status code 
from the exception and assign it to the response. For the [[yii\web\NotFoundHttpException]] above, it is
associated with the HTTP status 404. The following HTTP exceptions are predefined in Yii:

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

If the exception that you want to throw is not among the above list, you may create one by extending
from [[yii\web\HttpException]], or directly throw it with a status code, for example,
 
```php
throw new \yii\web\HttpException(402);
```


## HTTP Headers <span id="http-headers"></span> 

You can send HTTP headers by manipulating the [[yii\web\Response::headers|header collection]] in the `response` component.
For example,

```php
$headers = Yii::$app->response->headers;

// add a Pragma header. Existing Pragma headers will NOT be overwritten.
$headers->add('Pragma', 'no-cache');

// set a Pragma header. Any existing Pragma headers will be discarded.
$headers->set('Pragma', 'no-cache');

// remove Pragma header(s) and return the removed Pragma header values in an array
$values = $headers->remove('Pragma');
```

> Info: Header names are case insensitive. And the newly registered headers are not sent to the user until
  the [[yii\web\Response::send()]] method is called.


## Response Body <span id="response-body"></span>

Most responses should have a body which gives the content that you want to show to end users.

If you already have a formatted body string, you may assign it to the [[yii\web\Response::content]] property
of the response. For example,

```php
Yii::$app->response->content = 'hello world!';
```

If your data needs to be formatted before sending it to end users, you should set both of the
[[yii\web\Response::format|format]] and [[yii\web\Response::data|data]] properties. The [[yii\web\Response::format|format]]
property specifies in which format the [[yii\web\Response::data|data]] should be formatted. For example,

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii supports the following formats out of the box, each implemented by a [[yii\web\ResponseFormatterInterface|formatter]] class.
You can customize these formatters or add new ones by configuring the [[yii\web\Response::formatters]] property.

* [[yii\web\Response::FORMAT_HTML|HTML]]: implemented by [[yii\web\HtmlResponseFormatter]].
* [[yii\web\Response::FORMAT_XML|XML]]: implemented by [[yii\web\XmlResponseFormatter]].
* [[yii\web\Response::FORMAT_JSON|JSON]]: implemented by [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: implemented by [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_RAW|RAW]]: use this format if you want to send the response directly without applying any formatting.

While the response body can be set explicitly as shown above, in most cases you may set it implicitly by the return value
of [action](structure-controllers.md) methods. A common use case is like the following:
 
```php
public function actionIndex()
{
    return $this->render('index');
}
```

The `index` action above returns the rendering result of the `index` view. The return value will be taken
by the `response` component, formatted and then sent to end users.

Because by default the response format is [[yii\web\Response::FORMAT_HTML|HTML]], you should only return a string
in an action method. If you want to use a different response format, you should set it first before returning the data.
For example,

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

As aforementioned, besides using the default `response` application component, you can also create your own
response objects and send them to end users. You can do so by returning such object in an action method, like the following,

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

> Note: If you are creating your own response objects, you will not be able to take advantage of the configurations
  that you set for the `response` component in the application configuration. You can, however, use 
  [dependency injection](concept-di-container.md) to apply a common configuration to your new response objects.


## Browser Redirection <span id="browser-redirection"></span>

Browser redirection relies on sending a `Location` HTTP header. Because this feature is commonly used, Yii provides
some special support for it.

You can redirect the user browser to a URL by calling the [[yii\web\Response::redirect()]] method. The method
sets the appropriate `Location` header with the given URL and returns the response object itself. In an action method,
you can call its shortcut version [[yii\web\Controller::redirect()]]. For example,

```php
public function actionOld()
{
    return $this->redirect('http://example.com/new', 301);
}
```

In the above code, the action method returns the result of the `redirect()` method. As explained before, the response
object returned by an action method will be used as the response sending to end users.

In places other than an action method, you should call [[yii\web\Response::redirect()]] directly followed by 
a chained call to the [[yii\web\Response::send()]] method to ensure no extra content will be appended to the response.

```php
\Yii::$app->response->redirect('http://example.com/new', 301)->send();
```

> Info: By default, the [[yii\web\Response::redirect()]] method sets the response status code to be 302 which instructs
  the browser that the resource being requested is *temporarily* located in a different URI. You can pass in a status
  code 301 to tell the browser that the resource has been *permanently* relocated.

When the current request is an AJAX request, sending a `Location` header will not automatically cause the browser
to redirect. To solve this problem, the [[yii\web\Response::redirect()]] method sets an `X-Redirect` header with 
the redirection URL as its value. On the client-side, you may write JavaScript code to read this header value and
redirect the browser accordingly.

> Info: Yii comes with a `yii.js` JavaScript file which provides a set of commonly used JavaScript utilities,
  including browser redirection based on the `X-Redirect` header. Therefore, if you are using this JavaScript file
  (by registering the [[yii\web\YiiAsset]] asset bundle), you do not need to write anything to support AJAX redirection.
  More information about `yii.js` can be found in the [Client Scripts Section](output-client-scripts.md#yii.js).

## Sending Files <span id="sending-files"></span>

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


## Sending Response <span id="sending-response"></span>

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
