Response Formatting
===================

When handling a RESTful API request, an application usually takes the following steps that are related
with response formatting:

1. Determine various factors that may affect the response format, such as media type, language, version, etc.
   This process is also known as [content negotiation](http://en.wikipedia.org/wiki/Content_negotiation).
2. Convert resource objects into arrays, as described in the [Resources](rest-resources.md) section.
   This is done by [[yii\rest\Serializer]].
3. Convert arrays into a string in the format as determined by the content negotiation step. This is
   done by [[yii\web\ResponseFormatterInterface|response formatters]] registered with
   the [[yii\web\Response::formatters|formatters]] property of the
   `response` [application component](structure-application-components.md).


## Content Negotiation <span id="content-negotiation"></span>

Yii supports content negotiation via the [[yii\filters\ContentNegotiator]] filter. The RESTful API base
controller class [[yii\rest\Controller]] is equipped with this filter under the name of `contentNegotiator`.
The filter provides response format negotiation as well as language negotiation. For example, if a RESTful
API request contains the following header,

```
Accept: application/json; q=1.0, */*; q=0.1
```

it will get a response in JSON format, like the following:

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

Behind the scene, before a RESTful API controller action is executed, the [[yii\filters\ContentNegotiator]]
filter will check the `Accept` HTTP header in the request and set the [[yii\web\Response::format|response format]]
to be `'json'`. After the action is executed and returns the resulting resource object or collection,
[[yii\rest\Serializer]] will convert the result into an array. And finally, [[yii\web\JsonResponseFormatter]]
will serialize the array into a JSON string and include it in the response body.

By default, RESTful APIs support both JSON and XML formats. To support a new format, you should configure
the [[yii\filters\ContentNegotiator::formats|formats]] property of the `contentNegotiator` filter like
the following in your API controller classes:

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```

The keys of the `formats` property are the supported MIME types, while the values are the corresponding
response format names which must be supported in [[yii\web\Response::formatters]].


## Data Serializing <span id="data-serializing"></span>

As we have described above, [[yii\rest\Serializer]] is the central piece responsible for converting resource
objects or collections into arrays. It recognizes objects implementing [[yii\base\ArrayableInterface]] as
well as [[yii\data\DataProviderInterface]]. The former is mainly implemented by resource objects, while
the latter resource collections.

You may configure the serializer by setting the [[yii\rest\Controller::serializer]] property with a configuration array.
For example, sometimes you may want to help simplify the client development work by including pagination information
directly in the response body. To do so, configure the [[yii\rest\Serializer::collectionEnvelope]] property
as follows:

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

You may then get the following response for request `http://localhost/users`:

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
