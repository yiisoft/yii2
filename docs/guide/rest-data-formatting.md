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

For classes extending from [[yii\base\Model]] or [[yii\db\ActiveRecord]], besides directly overriding `toArray()`,
you may also override the `fields()` method and/or the `extraFields()` method to customize the data being returned.

The method [[yii\base\Model::fields()]] declares a set of *fields* that should be included in the result.
A field is simply a named data item. In a result array, the array keys are the field names, and the array values
are the corresponding field values. The default implementation of [[yii\base\Model::fields()]] is to return
all attributes of a model as the output fields; for [[yii\db\ActiveRecord::fields()]], by default it will return
the names of the attributes whose values have been populated into the object.

You can override the `fields()` method to add, remove, rename or redefine fields. For example,

```php
// explicitly list every field, best used when you want to make sure the changes
// in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).
public function fields()
{
    return [
        // field name is the same as the attribute name
        'id',
        // field name is "email", the corresponding attribute name is "email_address"
        'email' => 'email_address',
        // field name is "name", its value is defined by a PHP callback
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filter out some fields, best used when you want to inherit the parent implementation
// and blacklist some sensitive fields.
public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

The return value of `fields()` should be an array. The array keys are the field names, and the array values
are the corresponding field definitions which can be either property/attribute names or anonymous functions
returning the corresponding field values.

> Warning: Because by default all attributes of a model will be included in the API result, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` or `toArray()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.

You may use the `fields` query parameter to specify which fields in `fields()` should be included in the result.
If this parameter is not specified, all fields returned by `fields()` will be returned.

The method [[yii\base\Model::extraFields()]] is very similar to [[yii\base\Model::fields()]].
The difference between these methods is that the latter declares the fields that should be returned by default,
while the former declares the fields that should only be returned when the user specifies them in the `expand` query parameter.

For example, `http://localhost/users?fields=id,email&expand=profile` may return the following JSON data:

```php
[
    {
        "id": 100,
        "email": "100@example.com",
        "profile": {
            "id": 100,
            "age": 30,
        }
    },
    ...
]
```

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


### Pagination

For API endpoints about resource collections, pagination is supported out-of-box if you use
[[yii\data\DataProviderInterface|data provider]] to serve the response data. In particular,
through query parameters `page` and `per-page`, an API consumer may specify which page of data
to return and how many data items should be included in each page. The corresponding response
will include the pagination information by the following HTTP headers (please also refer to the first example
in this section):

* `X-Pagination-Total-Count`: The total number of data items;
* `X-Pagination-Page-Count`: The number of pages;
* `X-Pagination-Current-Page`: The current page (1-based);
* `X-Pagination-Per-Page`: The number of data items in each page;
* `Link`: A set of navigational links allowing client to traverse the data page by page.

The response body will contain a list of data items in the requested page.

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


### HATEOAS Support

[HATEOAS](http://en.wikipedia.org/wiki/HATEOAS), an abbreviation for Hypermedia as the Engine of Application State,
promotes that RESTful APIs should return information that allow clients to discover actions supported for the returned
resources. The key of HATEOAS is to return a set of hyperlinks with relation information when resource data are served
by APIs.

You may let your model classes to implement the [[yii\web\Linkable]] interface to support HATEOAS. By implementing
this interface, a class is required to return a list of [[yii\web\Link|links]]. Typically, you should return at least
the `self` link, for example:

```php
use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;

class User extends ActiveRecord implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['user', 'id' => $this->id], true),
        ];
    }
}
```

When a `User` object is returned in a response, it will contain a `_links` element representing the links related
to the user, for example,

```
{
    "id": 100,
    "email": "user@example.com",
    ...,
    "_links" => [
        "self": "https://example.com/users/100"
    ]
}
```
