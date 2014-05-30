Controllers
===========

Controllers are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They contain the actual logic about processing requests and generating responses. In particular, after
taking over the control from [applications](structure-applications.md), controllers will analyze incoming request data,
pass them to [models](structure-models.md), inject model results into [views](structure-views.md),
and finally generate outgoing responses.

Controllers are composed by *actions*, each of which deals with one particular type of request. A controller
can have one or multiple actions.

> Info: You may consider a controller as a grouping of similar actions. The controller provides an environment
  for sharing common data among its actions.


## IDs and Routes

Both controllers and actions have IDs. Controller IDs are used to uniquely identify controllers within the same
application, while actions IDs are used to identify actions within the same controller. The combination of
a controller ID and an action ID forms a *route* which takes the format of `ControllerID/ActionID`.

End users can address any controller action through the corresponding route. For example, the URL
`http://hostname/index.php?r=site/index` specifies that the request should be handled by the `site` controller
using its `index` action.

By default, controller and action IDs should contain lower-case alphanumeric characters and dashes only.
For example, `site`, `index`, `post-comment` and `comment2` are all valid controller/action IDs, while
`Site`, `postComment` and `index?` are not. To use other characters in the IDs, you should configure
[[yii\base\Application::controllerMap]] and/or override [[yii\base\Controller::actions()]].

In practice, a controller is often designed to handle the requests about a specific type of resource,
while each action within it supports a specific manipulation about that resource type. For this reason,
controller IDs are often nouns, while action IDs are often verbs. For example, you may create an `article`
controller to handle all requests about article data; and within the `article` controller, you may create
actions such as `create`, `update`, `delete` to support the corresponding manipulations about articles.


## Creating Controllers

In [[yii\web\Application|Web applications]], controllers should extend from [[yii\web\Controller]] or its
child classes. Similarly in [[yii\console\Application|console applications]], controllers should extend from
[[yii\console\Controller]] or its child classes.

Controller classes should be [autoloadable](concept-autoloading.md). They should be created under
the namespace as specified by [[yii\base\Application::controllerNamespace]]. By default, it is `app\controllers`.
This means controller classes should usually be located under the path aliased as `@app/controllers`.

The following code defines a `site` controller:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```

As aforementioned, the class should be saved in the file `@app/controllers/SiteController.php`.


### Controller Class Naming

The controller class name `SiteController` is derived from the controller ID `site` according to the following rules:

* Turn the first letter in each word into upper case;
* Remove dashes;
* Append the suffix `Controller`.

For example, `site` becomes `SiteController`, and `post-comment` becomes `PostCommentController`.

If you want to name controller classes in a different way, you may configure the [[yii\base\Application::controllerMap]]
property, like the following in an [application configuration](structure-applications.md#application-configurations):

```php
[
    'controllerMap' => [
        [
            'account' => 'app\controllers\UserController',
            'article' => [
                'class' => 'app\controllers\PostController',
                'enableCsrfValidation' => false,
            ],
        ],
    ],
]
```


## Creating Actions

You can create actions in two ways: inline actions and standalone actions. An inline action is
defined as a method in the controller class, while a standalone action is a class extending
[[yii\base\Action]] or its child class. Inline actions take less effort to create and are often preferred
if you have no intention to reuse these actions. Standalone actions, on the other hand, are mainly
created to be used in different controllers or be redistributed as [extensions](structure-extensions.md).


### Inline Actions

Inline actions are defined in terms of *public* `action*` methods in controller classes. The following code defines
two actions `index` and `hello-world`.

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```

Action IDs for inline actions must contain lower-case alphanumeric characters and dashes only. And the names
of the `action*` methods are derived from action IDs according to the following criteria:

* Turn the first letter in each word of the action ID into upper case;
* Remove dashes;
* Prepend the prefix `action`.

For example, `index` becomes `actionIndex`, and `hello-world` becomes `actionHelloWorld`, as shown in the
above example.

> Note: The names of `action*` methods are *case-sensitive*. If you have a method named `ActionIndex`,
  it will not be considered as an `action*` method, and as a result, the request for the `index` action
  will result in an exception. Also note that `action*` methods must be public. A private or protected
  `action*` method does NOT define an inline action.

The return value of an `action*` method can be either a [response](runtime-responses.md) object or
the data to be populated into a [response](runtime-responses.md). In particular, for Web applications,
the data will be assigned to [[yii\web\Response::data]], while for console applications, the data
will be assigned to [[yii\console\Response::exitStatus]]. In the example above, each action returns
a string which will be assigned to [[yii\web\Response::data]] and further displayed to end users.

Inline actions are preferred in most cases because they take little effort to create. However, if an action
can be reused in another controller or application, you may consider defining it as a standalone action.


### Standalone Actions

Standalone actions are defined in terms of action classes extending [[yii\base\Action]] or its child classes.
For example, in the Yii releases, there are [[yii\web\ViewAction]] a nd [[yii\web\ErrorAction]], both of which
are standalone actions.

To use a standalone action, you should override the [[yii\base\Controller::actions()]] method in your
controller classes like the following:

```php
public function actions()
{
    return [
        // declares "error" action using a class name
        'error' => 'yii\web\ErrorAction',

        // declares "view" action using a configuration array
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

The method should return an array whose keys are action IDs and values the corresponding action class names
or [configurations](concept-configurations.md).

Action IDs for standalone actions can contain arbitrary characters, as long as they are declared in
the [[yii\base\Controller::actions()]] method.

To create a standalone action class, you should extend [[yii\base\Action]] or its child class, and implement
a public method named `run()`. The role of the `run()` method is similar to an inline action method. For example,

```php
<?php
namespace app\components;

use yii\base\Action;

class DemoAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### Default Action

Each controller has a default action specified via the [[yii\base\Controller::defaultAction]] property.
When a [route](#ids-routes) contains the controller ID only, it implies that the default action of
the specified controller is requested.

By default, the default action is set as `index`. If you want to change the default value, simply override
this property in the controller class, like the following:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```

### Action Parameters

You can define named arguments for an action and these will be automatically populated from corresponding values from
`$_GET`. This is very convenient both because of the short syntax and an ability to specify defaults:

```php
namespace app\controllers;

use yii\web\Controller;

class BlogController extends Controller
{
    public function actionView($id, $version = null)
    {
        $post = Post::findOne($id);
        $text = $post->text;

        if ($version) {
            $text = $post->getHistory($version);
        }

        return $this->render('view', [
            'post' => $post,
            'text' => $text,
        ]);
    }
}
```

The action above can be accessed using either `http://example.com/?r=blog/view&id=42` or
`http://example.com/?r=blog/view&id=42&version=3`. In the first case `version` isn't specified and default parameter
value is used instead.


### Action Patterns

### Getting data from request

If your action is working with data from HTTP POST or has too many GET parameters you can rely on request object that
is accessible via `\Yii::$app->request`:

```php
namespace app\controllers;

use yii\web\Controller;
use yii\web\HttpException;

class BlogController extends Controller
{
    public function actionUpdate($id)
    {
        $post = Post::findOne($id);
        if (!$post) {
            throw new NotFoundHttpException();
        }

        if (\Yii::$app->request->isPost) {
            $post->load(Yii::$app->request->post());
            if ($post->save()) {
                return $this->redirect(['view', 'id' => $post->id]);
            }
        }

        return $this->render('update', ['post' => $post]);
    }
}
```


## Controllers in Modules and Subdirectories

If a controller is located inside a module, the route of its actions will be in the format of `module/controller/action`.

A controller can be located under a subdirectory of the controller directory of an application or module. The route
will be prefixed with the corresponding directory names. For example, you may have a `UserController` under `controllers/admin`.
The route of its `actionIndex` would be `admin/user/index`, and `admin/user` would be the controller ID.

In case module, controller or action specified isn't found Yii will return "not found" page and HTTP status code 404.


> Note: If module name, controller name or action name contains camelCased words, internal route will use dashes i.e. for
`DateTimeController::actionFastForward` route will be `date-time/fast-forward`.


## Controller Lifecycle <a name="lifecycle"></a>

