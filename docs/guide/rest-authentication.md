Authentication
==============

Unlike Web applications, RESTful APIs are usually stateless, which means sessions or cookies should not
be used. Therefore, each request should come with some sort of authentication credentials because
the user authentication status may not be maintained by sessions or cookies. A common practice is
to send a secret access token with each request to authenticate the user. Since an access token
can be used to uniquely identify and authenticate a user, **API requests should always be sent
via HTTPS to prevent man-in-the-middle (MitM) attacks**.

There are different ways to send an access token:

* [HTTP Basic Auth](https://en.wikipedia.org/wiki/Basic_access_authentication): the access token
  is sent as the username. This should only be used when an access token can be safely stored
  on the API consumer side. For example, the API consumer is a program running on a server.
* Query parameter: the access token is sent as a query parameter in the API URL, e.g.,
  `https://example.com/users?access-token=xxxxxxxx`. Because most Web servers will keep query
  parameters in server logs, this approach should be mainly used to serve `JSONP` requests which
  cannot use HTTP headers to send access tokens.
* [OAuth 2](https://oauth.net/2/): the access token is obtained by the consumer from an authorization
  server and sent to the API server via [HTTP Bearer Tokens](https://datatracker.ietf.org/doc/html/rfc6750),
  according to the OAuth2 protocol.

Yii supports all of the above authentication methods. You can also easily create new authentication methods.

To enable authentication for your APIs, do the following steps:

1. Configure the `user` [application component](structure-application-components.md):
   - Set the [[yii\web\User::enableSession|enableSession]] property to be `false`.
   - Set the [[yii\web\User::loginUrl|loginUrl]] property to be `null` to show an HTTP 403 error instead of redirecting to the login page. 
2. Specify which authentication methods you plan to use by configuring the `authenticator` behavior
   in your REST controller classes.
3. Implement [[yii\web\IdentityInterface::findIdentityByAccessToken()]] in your [[yii\web\User::identityClass|user identity class]].

Step 1 is not required but is recommended for RESTful APIs which should be stateless. When [[yii\web\User::enableSession|enableSession]]
is `false`, the user authentication status will NOT be persisted across requests using sessions. Instead, authentication
will be performed for every request, which is accomplished by Step 2 and 3.

> Tip: You may configure [[yii\web\User::enableSession|enableSession]] of the `user` application component
> in application configurations if you are developing RESTful APIs in terms of an application. If you develop
> RESTful APIs as a module, you may put the following line in the module's `init()` method, like the following:
>
> ```php
> public function init()
> {
>     parent::init();
>     \Yii::$app->user->enableSession = false;
> }
> ```

For example, to use HTTP Basic Auth, you may configure the `authenticator` behavior as follows,

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::class,
    ];
    return $behaviors;
}
```

If you want to support all three authentication methods explained above, you can use `CompositeAuth` like the following,

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => CompositeAuth::class,
        'authMethods' => [
            HttpBasicAuth::class,
            HttpBearerAuth::class,
            QueryParamAuth::class,
        ],
    ];
    return $behaviors;
}
```

Each element in `authMethods` should be an auth method class name or a configuration array.


Implementation of `findIdentityByAccessToken()` is application specific. For example, in simple scenarios
when each user can only have one access token, you may store the access token in an `access_token` column
in the user table. The method can then be readily implemented in the `User` class as follows,

```php
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
}
```

After authentication is enabled as described above, for every API request, the requested controller
will try to authenticate the user in its `beforeAction()` step.

If authentication succeeds, the controller will perform other checks (such as rate limiting, authorization)
and then run the action. The authenticated user identity information can be retrieved via `Yii::$app->user->identity`.

If authentication fails, a response with HTTP status 401 will be sent back together with other appropriate headers
(such as a `WWW-Authenticate` header for HTTP Basic Auth).


## Authorization <span id="authorization"></span>

After a user is authenticated, you probably want to check if he or she has the permission to perform the requested
action for the requested resource. This process is called *authorization* which is covered in detail in
the [Authorization section](security-authorization.md).

If your controllers extend from [[yii\rest\ActiveController]], you may override
the [[yii\rest\ActiveController::checkAccess()|checkAccess()]] method to perform authorization check. The method
will be called by the built-in actions provided by [[yii\rest\ActiveController]].
