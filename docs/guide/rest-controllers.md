Controllers
===========

After creating the resource classes and specifying how resource data should be formatted, the next thing
to do is to create controller actions to expose the resources to end users through RESTful APIs.

Yii provides two base controller classes to simplify your work of creating RESTful actions:
[[yii\rest\Controller]] and [[yii\rest\ActiveController]]. The difference between these two controllers
is that the latter provides a default set of actions that are specifically designed to deal with
resources represented as [Active Record](db-active-record.md). So if you are using [Active Record](db-active-record.md)
and are comfortable with the provided built-in actions, you may consider extending your controller classes
from [[yii\rest\ActiveController]], which will allow you to create powerful RESTful APIs with minimal code.

Both [[yii\rest\Controller]] and [[yii\rest\ActiveController]] provide the following features, some of which
will be described in detail in the next few sections:

* HTTP method validation;
* [Content negotiation and Data formatting](rest-response-formatting.md);
* [Authentication](rest-authentication.md);
* [Rate limiting](rest-rate-limiting.md).

[[yii\rest\ActiveController]] in addition provides the following features:

* A set of commonly needed actions: `index`, `view`, `create`, `update`, `delete`, `options`;
* User authorization in regard to the requested action and resource.


## Creating Controller Classes <span id="creating-controller"></span>

When creating a new controller class, a convention in naming the controller class is to use
the type name of the resource and use singular form. For example, to serve user information,
the controller may be named as `UserController`.

Creating a new action is similar to creating an action for a Web application. The only difference
is that instead of rendering the result using a view by calling the `render()` method, for RESTful actions
you directly return the data. The [[yii\rest\Controller::serializer|serializer]] and the
[[yii\web\Response|response object]] will handle the conversion from the original data to the requested
format. For example,

```php
public function actionView($id)
{
    return User::findOne($id);
}
```


## Filters <span id="filters"></span>

Most RESTful API features provided by [[yii\rest\Controller]] are implemented in terms of [filters](structure-filters.md).
In particular, the following filters will be executed in the order they are listed:

* [[yii\filters\ContentNegotiator|contentNegotiator]]: supports content negotiation, to be explained in
  the [Response Formatting](rest-response-formatting.md) section;
* [[yii\filters\VerbFilter|verbFilter]]: supports HTTP method validation;
* [[yii\filters\auth\AuthMethod|authenticator]]: supports user authentication, to be explained in
  the [Authentication](rest-authentication.md) section;
* [[yii\filters\RateLimiter|rateLimiter]]: supports rate limiting, to be explained in
  the [Rate Limiting](rest-rate-limiting.md) section.

These named filters are declared in the [[yii\rest\Controller::behaviors()|behaviors()]] method.
You may override this method to configure individual filters, disable some of them, or add your own filters.
For example, if you only want to use HTTP basic authentication, you may write the following code:

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

### CORS <span id="cors"></span>

Adding the [Cross-Origin Resource Sharing](structure-filters.md#cors) filter to a controller is a bit more complicated
than adding other filters described above, because the CORS filter has to be applied before authentication methods
and thus needs a slightly different approach compared to other filters. Also authentication has to be disabled for the
[CORS Preflight requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#preflighted_requests)
so that a browser can safely determine whether a request can be made beforehand without the need for sending
authentication credentials. The following shows the code that is needed to add the [[yii\filters\Cors]] filter
to an existing controller that extends from [[yii\rest\ActiveController]]:

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();

    // remove authentication filter
    $auth = $behaviors['authenticator'];
    unset($behaviors['authenticator']);
    
    // add CORS filter
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::class,
    ];
    
    // re-add authentication filter
    $behaviors['authenticator'] = $auth;
    // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
    $behaviors['authenticator']['except'] = ['options'];

    return $behaviors;
}
```


## Extending `ActiveController` <span id="extending-active-controller"></span>

If your controller class extends from [[yii\rest\ActiveController]], you should set
its [[yii\rest\ActiveController::modelClass|modelClass]] property to be the name of the resource class
that you plan to serve through this controller. The class must extend from [[yii\db\ActiveRecord]].


### Customizing Actions <span id="customizing-actions"></span>

By default, [[yii\rest\ActiveController]] provides the following actions:

* [[yii\rest\IndexAction|index]]: list resources page by page;
* [[yii\rest\ViewAction|view]]: return the details of a specified resource;
* [[yii\rest\CreateAction|create]]: create a new resource;
* [[yii\rest\UpdateAction|update]]: update an existing resource;
* [[yii\rest\DeleteAction|delete]]: delete the specified resource;
* [[yii\rest\OptionsAction|options]]: return the supported HTTP methods.

All these actions are declared through the [[yii\rest\ActiveController::actions()|actions()]] method.
You may configure these actions or disable some of them by overriding the `actions()` method, like shown the following,

```php
public function actions()
{
    $actions = parent::actions();

    // disable the "delete" and "create" actions
    unset($actions['delete'], $actions['create']);

    // customize the data provider preparation with the "prepareDataProvider()" method
    $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

    return $actions;
}

public function prepareDataProvider()
{
    // prepare and return a data provider for the "index" action
}
```

Please refer to the class references for individual action classes to learn what configuration options are available.


### Performing Access Check <span id="performing-access-check"></span>

When exposing resources through RESTful APIs, you often need to check if the current user has the permission
to access and manipulate the requested resource(s). With [[yii\rest\ActiveController]], this can be done
by overriding the [[yii\rest\ActiveController::checkAccess()|checkAccess()]] method like the following,

```php
/**
 * Checks the privilege of the current user.
 *
 * This method should be overridden to check whether the current user has the privilege
 * to run the specified action against the specified data model.
 * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
 *
 * @param string $action the ID of the action to be executed
 * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
 * @param array $params additional parameters
 * @throws ForbiddenHttpException if the user does not have access
 */
public function checkAccess($action, $model = null, $params = [])
{
    // check if the user can access $action and $model
    // throw ForbiddenHttpException if access should be denied
    if ($action === 'update' || $action === 'delete') {
        if ($model->author_id !== \Yii::$app->user->id)
            throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
    }
}
```

The `checkAccess()` method will be called by the default actions of [[yii\rest\ActiveController]]. If you create
new actions and also want to perform access check, you should call this method explicitly in the new actions.

> Tip: You may implement `checkAccess()` by using the [Role-Based Access Control (RBAC) component](security-authorization.md).
