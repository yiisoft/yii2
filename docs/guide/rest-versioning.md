Versioning
==========

Your APIs should be versioned. Unlike Web applications which you have full control on both client side and server side
code, for APIs you usually do not have control of the client code that consumes the APIs. Therefore, backward
compatibility (BC) of the APIs should be maintained whenever possible, and if some BC-breaking changes must be
introduced to the APIs, you should bump up the version number. You may refer to [Semantic Versioning](http://semver.org/)
for more information about designing the version numbers of your APIs.

Regarding how to implement API versioning, a common practice is to embed the version number in the API URLs.
For example, `http://example.com/v1/users` stands for `/users` API of version 1. Another method of API versioning
which gains momentum recently is to put version numbers in the HTTP request headers, typically through the `Accept` header,
like the following:

```
// via a parameter
Accept: application/json; version=v1
// via a vendor content type
Accept: application/vnd.company.myapp-v1+json
```

Both methods have pros and cons, and there are a lot of debates about them. Below we describe a practical strategy
of API versioning that is kind of a mix of these two methods:

* Put each major version of API implementation in a separate module whose ID is the major version number (e.g. `v1`, `v2`).
  Naturally, the API URLs will contain major version numbers.
* Within each major version (and thus within the corresponding module), use the `Accept` HTTP request header
  to determine the minor version number and write conditional code to respond to the minor versions accordingly.

For each module serving a major version, it should include the resource classes and the controller classes
serving for that specific version. To better separate code responsibility, you may keep a common set of
base resource and controller classes, and subclass them in each individual version module. Within the subclasses,
implement the concrete code such as `Model::fields()`.

Your code may be organized like the following:

```
api/
    common/
        controllers/
            UserController.php
            PostController.php
        models/
            User.php
            Post.php
    modules/
        v1/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
```

Your application configuration would look like:

```php
return [
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
        ],
        'v2' => [
            'basePath' => '@app/modules/v2',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
            ],
        ],
    ],
];
```

As a result, `http://example.com/v1/users` will return the list of users in version 1, while
`http://example.com/v2/users` will return version 2 users.

Using modules, code for different major versions can be well isolated. And it is still possible
to reuse code across modules via common base classes and other shared classes.

To deal with minor version numbers, you may take advantage of the content negotiation
feature provided by the [[yii\filters\ContentNegotiator|contentNegotiator]] behavior. The `contentNegotiator`
behavior will set the [[yii\web\Response::acceptParams]] property when it determines which
content type to support.

For example, if a request is sent with the HTTP header `Accept: application/json; version=v1`,
after content negotiation, [[yii\web\Response::acceptParams]] will contain the value `['version' => 'v1']`.

Based on the version information in `acceptParams`, you may write conditional code in places
such as actions, resource classes, serializers, etc.

Since minor versions require maintaining backward compatibility, hopefully there are not much
version checks in your code. Otherwise, chances are that you may need to create a new major version.

Take note that if you have different models per each version, you have to redeclare the relations with the proper version, if you don't do this, the related models will not be able to take advantage of the functions that you have introduced for that version

Example:
```php
class User extends \common\models\User
{
  public function getPosts()
  {
    return $this->hasMany('api\modules\v2\models\Post', ['user_id' => 'id']);
  }
}
```
