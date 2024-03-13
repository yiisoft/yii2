响应格式
===================

当处理一个 RESTful API 请求时，一个应用程序通常需要如下步骤
来处理响应格式：

1. 确定可能影响响应格式的各种因素，例如媒介类型，语言，版本，等等。
   这个过程也被称为 [content negotiation](https://zh.wikipedia.org/wiki/%E5%86%85%E5%AE%B9%E5%8D%8F%E5%95%86)。
2. 资源对象转换为数组，如在 [Resources](rest-resources.md) 部分中所描述的。
   通过 [[yii\rest\Serializer]] 来完成。
3. 通过内容协商步骤将数组转换成字符串。
   [[yii\web\ResponseFormatterInterface|response formatters]] 通过
   [[yii\web\Response::formatters|response]] 应用程序
   组件来注册完成。


## 内容协商 <span id="content-negotiation"></span>

Yii 提供了通过 [[yii\filters\ContentNegotiator]] 过滤器支持内容协商。RESTful API 基于
控制器类 [[yii\rest\Controller]] 在 `contentNegotiator` 下配备这个过滤器。
文件管理器提供了涉及的响应格式和语言。例如，如果一个 RESTful
API 请求中包含以下 header，

```
Accept: application/json; q=1.0, */*; q=0.1
```

将会得到JSON格式的响应，如下：

```
$ curl -i -H "Accept: application/json; q=1.0, */*; q=0.1" "http://localhost/users"

HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
    {
        "id": 1,
        ...
    },
    {
        "id": 2,
        ...
    },
    ...
]
```

幕后，执行一个 RESTful API 控制器动作之前，[[yii\filters\ContentNegotiator]]
filter 将检查 `Accept` HTTP header 在请求时和配置 [[yii\web\Response::format|response format]]
为 `'json'`。 之后的动作被执行并返回得到的资源对象或集合，
[[yii\rest\Serializer]] 将结果转换成一个数组。最后，[[yii\web\JsonResponseFormatter]]
该数组将序列化为JSON字符串，并将其包括在响应主体。

默认, RESTful APIs 同时支持JSON和XML格式。为了支持新的格式，你应该
在 `contentNegotiator` 过滤器中配置 [[yii\filters\ContentNegotiator::formats|formats]] 属性，
类似如下 API 控制器类:

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```

`formats` 属性的 keys 支持 MIME 类型，而 values 必须在 [[yii\web\Response::formatters]]
中支持被响应格式名称。


## 数据序列化 <span id="data-serializing"></span>

正如我们上面所描述的，[[yii\rest\Serializer]] 负责转换资源的中间件
对象或集合到数组。它将对象 [[yii\base\ArrayableInterface]] 作为
[[yii\data\DataProviderInterface]]。前者主要由资源对象实现，
而后者是资源集合。

你可以通过设置 [[yii\rest\Controller::serializer]] 属性和一个配置数组。
例如，有时你可能想通过直接在响应主体内包含分页信息来
简化客户端的开发工作。这样做，按照如下规则配置 [[yii\rest\Serializer::collectionEnvelope]] 
属性：

```php
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
}
```

那么你的请求可能会得到的响应如下 `http://localhost/users`：

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "items": [
        {
            "id": 1,
            ...
        },
        {
            "id": 2,
            ...
        },
        ...
    ],
    "_links": {
        "self": {
            "href": "http://localhost/users?page=1"
        },
        "next": {
            "href": "http://localhost/users?page=2"
        },
        "last": {
            "href": "http://localhost/users?page=50"
        }
    },
    "_meta": {
        "totalCount": 1000,
        "pageCount": 50,
        "currentPage": 1,
        "perPage": 20
    }
}
```

### 控制 JSON 输出

JSON 响应将由 [[yii\web\JsonResponseFormatter|JsonResponseFormatter]] 类来生成，
并且将在内部使用 [[yii\helpers\Json|JSON helper]]。
这个格式化程序可以配置不同的选项，
比如 [[yii\web\JsonResponseFormatter::$prettyPrint|$prettyPrint]]，这对于开发更好的可读式响应更有用，
或者用 [[yii\web\JsonResponseFormatter::$encodeOptions|$encodeOptions]] 去控制 JSON 编码的输出。

格式化程序可以在 [configuration](concept-configurations.md) 的 `response` 应用程序组件 [[yii\web\Response::formatters|formatters]] 的属性进行配置，
如下所示：

```php
'response' => [
    // ...
    'formatters' => [
        \yii\web\Response::FORMAT_JSON => [
            'class' => 'yii\web\JsonResponseFormatter',
            'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
            'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            // ...
        ],
    ],
],
```

当使用 [DAO](db-dao.md) 数据库层从数据库返回数据时，所有的数据将会表示成字符串，
这不总是预期的结果，尤其是数值应该表现为 JSON 的中的数字时。
当使用 ActiveRecord 层从数据库检索数据时，
在 [[yii\db\ActiveRecord::populateRecord()]] 中从数据库中提取数据，数字列的值将会被转换为整数。
