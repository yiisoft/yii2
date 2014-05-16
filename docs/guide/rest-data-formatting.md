Data Formatting
===============

By default, Yii supports two response formats for RESTful APIs: JSON and XML. If you want to support
other formats, you should configure the `contentNegotiator` behavior in your REST controller classes as follows,


```php
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge(parent::behaviors(), [
        'contentNegotiator' => [
            'formats' => [
                // ... other supported formats ...
            ],
        ],
    ]);
}
```

Formatting response data in general involves two steps:

1. The objects (including embedded objects) in the response data are converted into arrays by [[yii\rest\Serializer]];
2. The array data are converted into different formats (e.g. JSON, XML) by [[yii\web\ResponseFormatterInterface|response formatters]].

Step 2 is usually a very mechanical data conversion process and can be well handled by the built-in response formatters.
Step 1 involves some major development effort as explained below.

When the [[yii\rest\Serializer|serializer]] converts an object into an array, it will call the `toArray()` method
of the object if it implements [[yii\base\Arrayable]]. If an object does not implement this interface,
its public properties will be returned instead.

You may wonder who triggers the conversion from objects to arrays when an action returns an object or object collection.
The answer is that this is done by [[yii\rest\Controller::serializer]] in the [[yii\base\Controller::afterAction()|afterAction()]]
method. By default, [[yii\rest\Serializer]] is used as the serializer that can recognize resource objects extending from
[[yii\base\Model]] and collection objects implementing [[yii\data\DataProviderInterface]]. The serializer
will call the `toArray()` method of these objects and pass the `fields` and `expand` user parameters to the method.
If there are any embedded objects, they will also be converted into arrays recursively.

If all your resource objects are of [[yii\base\Model]] or its child classes, such as [[yii\db\ActiveRecord]],
and you only use [[yii\data\DataProviderInterface]] as resource collections, the default data formatting
implementation should work very well. However, if you want to introduce some new resource classes that do not
extend from [[yii\base\Model]], or if you want to use some new collection classes, you will need to
customize the serializer class and configure [[yii\rest\Controller::serializer]] to use it.
You new resource classes may use the trait [[yii\base\ArrayableTrait]] to support selective field output
as explained above.


Sometimes, you may want to help simplify the client development work by including pagination information
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
        "self": "http://localhost/users?page=1",
        "next": "http://localhost/users?page=2",
        "last": "http://localhost/users?page=50"
    },
    "_meta": {
        "totalCount": 1000,
        "pageCount": 50,
        "currentPage": 1,
        "perPage": 20
    }
}
```
