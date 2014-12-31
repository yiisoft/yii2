Versioning
==========

A good API is *versioned*: changes and new features are implemented in new versions of the API instead of continually altering just one version. Unlike Web applications, with which you have full control of both the client-side and server-side
code, APIs are meant to be used by clients beyond your control. For this reason, backward
compatibility (BC) of the APIs should be maintained whenever possible. If a change that may break BC is necessary, you should introduce it in new version of the API, and bump up the version number. Existing clients can continue to use the old, working version of the API; and new or upgraded clients can get the new functionality in the new API version. 

> Tip: Refer to [Semantic Versioning](http://semver.org/)
for more information on designing API version numbers.

One common way to implement API versioning is to embed the version number in the API URLs.
For example, `http://example.com/v1/users` stands for the `/users` endpoint of API version 1. 

Another method of API versioning,
which has gained momentum recently, is to put the version number in the HTTP request headers. This is typically done through the `Accept` header:

```
// via a parameter
Accept: application/json; version=v1
// via a vendor content type
Accept: application/vnd.company.myapp-v1+json
```

Both methods have their pros and cons, and there are a lot of debates about each approach. Below you'll see a practical strategy
for API versioning that is a mix of these two methods:

* Put each major version of API implementation in a separate module whose ID is the major version number (e.g. `v1`, `v2`).
  Naturally, the API URLs will contain major version numbers.
* Within each major version (and thus within the corresponding module), use the `Accept` HTTP request header
  to determine the minor version number and write conditional code to respond to the minor versions accordingly.

For each module serving a major version, the module should include the resource and controller classes
serving that specific version. To better separate code responsibility, you may keep a common set of
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
            Module.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
            Module.php
```

Your application configuration would look like:

```php
return [
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
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

As a result of the above code, `http://example.com/v1/users` will return the list of users in version 1, while
`http://example.com/v2/users` will return version 2 users.

Thanks to modules, the code for different major versions can be well isolated. But modules make it still possible
to reuse code across the modules via common base classes and other shared resources.

To deal with minor version numbers, you may take advantage of the content negotiation
feature provided by the [[yii\filters\ContentNegotiator|contentNegotiator]] behavior. The `contentNegotiator`
behavior will set the [[yii\web\Response::acceptParams]] property when it determines which
content type to support.

For example, if a request is sent with the HTTP header `Accept: application/json; version=v1`,
after content negotiation, [[yii\web\Response::acceptParams]] will contain the value `['version' => 'v1']`.

Based on the version information in `acceptParams`, you may write conditional code in places
such as actions, resource classes, serializers, etc. to provide the appropriate functionality.

Since minor versions by definition require maintaining backward compatibility, hopefully there would not be many
version checks in your code. Otherwise, chances are that you may need to create a new major version.
