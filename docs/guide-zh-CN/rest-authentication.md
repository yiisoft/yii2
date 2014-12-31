Authentication
认证
==============

Unlike Web applications, RESTful APIs are usually stateless, which means sessions or cookies should not
be used. Therefore, each request should come with some sort of authentication credentials because
the user authentication status may not be maintained by sessions or cookies. A common practice is
to send a secret access token with each request to authenticate the user. Since an access token
can be used to uniquely identify and authenticate a user, **API requests should always be sent
via HTTPS to prevent man-in-the-middle (MitM) attacks**.
和Web应用不同，RESTful APIs 通常是无状态的，也就意味着不应使用sessions 或 cookies，
因此每个请求应附带某种授权凭证，因为用户授权状态可能没通过sessions 或 cookies维护，
常用的做法是每个请求都发送一个秘密的access token来认证用户，由于access token可以唯一识别和认证用户，
**API 请求应通过HTTPS来防止man-in-the-middle (MitM) 中间人攻击**.

There are different ways to send an access token:
下面有几种方式来发送access token：

* [HTTP Basic Auth](http://en.wikipedia.org/wiki/Basic_access_authentication): the access token
  is sent as the username. This should only be used when an access token can be safely stored
  on the API consumer side. For example, the API consumer is a program running on a server.
* [HTTP 基本认证](http://en.wikipedia.org/wiki/Basic_access_authentication): access token
  当作用户名发送，应用在access token可安全存在API使用端的场景，例如，API使用端是运行在一台服务器上的程序。
* Query parameter: the access token is sent as a query parameter in the API URL, e.g.,
  `https://example.com/users?access-token=xxxxxxxx`. Because most Web servers will keep query
  parameters in server logs, this approach should be mainly used to serve `JSONP` requests which
  cannot use HTTP headers to send access tokens.
* 请求参数: access token 当作API URL请求参数发送，例如
  `https://example.com/users?access-token=xxxxxxxx`，由于大多数服务器都会保存请求参数到日志，
  这种方式应主要用于`JSONP` 请求，因为它不能使用HTTP头来发送access token 
* [OAuth 2](http://oauth.net/2/): 使用者从认证服务器上获取基于OAuth2协议的access token，然后通过
  [HTTP Bearer Tokens](http://tools.ietf.org/html/rfc6750) 发送到API 服务器。

Yii supports all of the above authentication methods. You can also easily create new authentication methods.
Yii 支持上述的认证方式，你也可很方便的创建新的认证方式。

To enable authentication for your APIs, do the following steps:
为你的APIs启用认证，做以下步骤：

1. Configure the `user` application component:
   - Set the [[yii\web\User::enableSession|enableSession]] property to be `false`.
   - Set the [[yii\web\User::loginUrl|loginUrl]] property to be `null` to show a HTTP 403 error instead of redirecting to the login page. 
2. Specify which authentication methods you plan to use by configuring the `authenticator` behavior
   in your REST controller classes.
3. Implement [[yii\web\IdentityInterface::findIdentityByAccessToken()]] in your [[yii\web\User::identityClass|user identity class]].
1. 配置`user` 应用组件:
   - 设置 [[yii\web\User::enableSession|enableSession]] 属性为 `false`.
   - 设置 [[yii\web\User::loginUrl|loginUrl]] 属性为`null` 显示一个HTTP 403 错误而不是跳转到登录界面. 
2. 在你的REST 控制器类中配置`authenticator` 行为来指定使用哪种认证方式
3. 在你的[[yii\web\User::identityClass|user identity class]] 类中实现 [[yii\web\IdentityInterface::findIdentityByAccessToken()]] 方法.

Step 1 is not required but is recommended for RESTful APIs which should be stateless. When [[yii\web\User::enableSession|enableSession]]
is false, the user authentication status will NOT be persisted across requests using sessions. Instead, authentication
will be performed for every request, which is accomplished by Step 2 and 3.
步骤1不是必要的，但是推荐配置，因为RESTful APIs应为无状态的，当[[yii\web\User::enableSession|enableSession]]为false，
请求中的用户认证状态就不能通过session来保持，每个请求的认证通过步骤2和3来实现。

> Tip: You may configure [[yii\web\User::enableSession|enableSession]] of the `user` application component
  in application configurations if you are developing RESTful APIs in terms of an application. If you develop
  RESTful APIs as a module, you may put the following line in the module's `init()` method, like the following:
> 提示: 如果你将RESTful APIs作为应用开发，可以设置应用配置中 `user` 组件的[[yii\web\User::enableSession|enableSession]]，
  如果将RESTful APIs作为模块开发，可以在模块的 `init()` 方法中增加如下代码，如下所示：

```php
public function init()
{
    parent::init();
    \Yii::$app->user->enableSession = false;
}
```

For example, to use HTTP Basic Auth, you may configure the `authenticator` behavior as follows,
例如，为使用HTTP Basic Auth，可配置`authenticator` 行为，如下所示：

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
}
```

If you want to support all three authentication methods explained above, you can use `CompositeAuth` like the following,
如果你系那个支持以上3个认证方式，可以使用`CompositeAuth`，如下所示：

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            HttpBasicAuth::className(),
            HttpBearerAuth::className(),
            QueryParamAuth::className(),
        ],
    ];
    return $behaviors;
}
```

Each element in `authMethods` should be an auth method class name or a configuration array.
`authMethods` 中每个单元应为一个认证方法名或配置数组。


Implementation of `findIdentityByAccessToken()` is application specific. For example, in simple scenarios
when each user can only have one access token, you may store the access token in an `access_token` column
in the user table. The method can then be readily implemented in the `User` class as follows,
`findIdentityByAccessToken()`方法的实现是系统定义的，
例如，一个简单的场景，当每个用户只有一个access token, 可存储access token 到user表的`access_token`列中，
方法可在`User`类中简单实现，如下所示：


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
在上述认证启用后，对于每个API请求，请求控制器都会在它的`beforeAction()`步骤中对用户进行认证。

If authentication succeeds, the controller will perform other checks (such as rate limiting, authorization)
and then run the action. The authenticated user identity information can be retrieved via `Yii::$app->user->identity`.
如果认证成功，控制器再执行其他检查(如频率限制，操作权限)，然后再执行操作，
授权用户信息可使用`Yii::$app->user->identity`获取.

If authentication fails, a response with HTTP status 401 will be sent back together with other appropriate headers
(such as a `WWW-Authenticate` header for HTTP Basic Auth).
如果认证失败，会发送一个HTTP状态码为401的响应，并带有其他相关信息头(如HTTP 基本认证会有`WWW-Authenticate` 头信息).


## Authorization <a name="authorization"></a>
## 授权 <a name="authorization"></a>

After a user is authenticated, you probably want to check if he or she has the permission to perform the requested
action for the requested resource. This process is called *authorization* which is covered in detail in
the [Authorization section](security-authorization.md).
在用户认证成功后，你可能想要检查他是否有权限执行对应的操作来获取资源，这个过程称为 *authorization* ，
详情请参考 [Authorization section](security-authorization.md).

If your controllers extend from [[yii\rest\ActiveController]], you may override
the [[yii\rest\Controller::checkAccess()|checkAccess()]] method to perform authorization check. The method
will be called by the built-in actions provided by [[yii\rest\ActiveController]].
如果你的控制器从[[yii\rest\ActiveController]]类继承，可覆盖 [[yii\rest\Controller::checkAccess()|checkAccess()]] 方法
来执行授权检查，该方法会被[[yii\rest\ActiveController]]内置的操作调用。
