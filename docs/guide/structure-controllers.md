Controller
==========

Controller is one of the key parts of the application. It determines how to handle incoming request and creates a response.

Most often a controller takes HTTP request data and returns HTML, JSON or XML as a response.

Basics
------

Controller resides in application's `controllers` directory and is named like `SiteController.php`,
where the `Site` part could be anything describing a set of actions it contains.

The basic web controller is a class that extends [[yii\web\Controller]] and could be very simple:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        // will render view from "views/site/index.php"
        return $this->render('index');
    }

    public function actionTest()
    {
        // will just print "test" to the browser
        return 'test';
    }
}
```

As you can see, typical controller contains actions that are public class methods named as `actionSomething`.
The output of an action is what the method returns: it could be a string or an instance of [[yii\web\Response]], [for example](#custom-response-class).
The return value will be handled by the `response` application
component which can convert the output to different formats such as JSON for example. The default behavior
is to output the value unchanged though.


Routes
------

Each controller action has a corresponding internal route. In our example above `actionIndex` has `site/index` route
and `actionTest` has `site/test` route. In this route `site` is referred to as controller ID while `test` is action ID.

By default you can access specific controller and action using the `http://example.com/?r=controller/action` URL. This
behavior is fully customizable. For more details please refer to [URL Management](url.md).

If a controller is located inside a module, the route of its actions will be in the format of `module/controller/action`.

A controller can be located under a subdirectory of the controller directory of an application or module. The route
will be prefixed with the corresponding directory names. For example, you may have a `UserController` under `controllers/admin`.
The route of its `actionIndex` would be `admin/user/index`, and `admin/user` would be the controller ID.

In case module, controller or action specified isn't found Yii will return "not found" page and HTTP status code 404.

> Note: If module name, controller name or action name contains camelCased words, internal route will use dashes i.e. for
`DateTimeController::actionFastForward` route will be `date-time/fast-forward`.

### Defaults

If user isn't specifying any route i.e. using URL like `http://example.com/`, Yii assumes that default route should be
used. It is determined by [[yii\web\Application::defaultRoute]] method and is `site` by default meaning that `SiteController`
will be loaded.

A controller has a default action. When the user request does not specify which action to execute by using an URL such as
`http://example.com/?r=site`, the default action will be executed. By default, the default action is named as `index`.
It can be changed by setting the [[yii\base\Controller::defaultAction]] property.

Action parameters
-----------------

It was already mentioned that a simple action is just a public method named as `actionSomething`. Now we'll review
ways that an action can get parameters from HTTP.

### Action parameters

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

Standalone actions
------------------

If action is generic enough it makes sense to implement it in a separate class to be able to reuse it.
Create `actions/Page.php`

```php
namespace app\actions;

class Page extends \yii\base\Action
{
    public $view = 'index';

    public function run()
    {
        return $this->controller->render($view);
    }
}
```

The following code is too simple to implement as a separate action but gives an idea of how it works. Action implemented
can be used in your controller as following:

```php
class SiteController extends \yii\web\Controller
{
    public function actions()
    {
        return [
            'about' => [
                'class' => 'app\actions\Page',
                'view' => 'about',
            ],
        ];
    }
}
```

After doing so you can access your action as `http://example.com/?r=site/about`.


Action Filters
--------------

You may apply some action filters to controller actions to accomplish tasks such as determining
who can access the current action, decorating the result of the action, etc.

An action filter is an instance of a class extending [[yii\base\ActionFilter]].

To use an action filter, attach it as a behavior to a controller or a module. The following
example shows how to enable HTTP caching for the `index` action:

```php
public function behaviors()
{
    return [
        'httpCache' => [
            'class' => \yii\filters\HttpCache::className(),
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

You may use multiple action filters at the same time. These filters will be applied in the
order they are declared in `behaviors()`. If any of the filter cancels the action execution,
the filters after it will be skipped.

When you attach a filter to a controller, it can be applied to all actions of that controller;
If you attach a filter to a module (or application), it can be applied to the actions of any controller
within that module (or application).

To create a new action filter, extend from [[yii\base\ActionFilter]] and override the
[[yii\base\ActionFilter::beforeAction()|beforeAction()]] and [[yii\base\ActionFilter::afterAction()|afterAction()]]
methods. The former will be executed before an action runs while the latter after an action runs.
The return value of [[yii\base\ActionFilter::beforeAction()|beforeAction()]] determines whether
an action should be executed or not. If `beforeAction()` of a filter returns false, the filters after this one
will be skipped and the action will not be executed.

The [authorization](authorization.md) section of this guide shows how to use the [[yii\filters\AccessControl]] filter,
and the [caching](caching.md) section gives more details about the [[yii\filters\PageCache]] and [[yii\filters\HttpCache]] filters.
These built-in filters are also good references when you learn to create your own filters.


Catching all incoming requests
------------------------------

Sometimes it is useful to handle all incoming requests with a single controller action. For example, displaying a notice
when website is in maintenance mode. In order to do it you should configure web application `catchAll` property either
dynamically or via application config:

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    // ...
    'catchAll' => [ // <-- here
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```

In the above `offline/notice` refer to `OfflineController::actionNotice()`. `param1` and `param2` are parameters passed
to action method.

Custom response class
---------------------

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\web\MyCustomResponse; #extended from yii\web\Response

class SiteController extends Controller
{
    public function actionCustom()
    {
        /*
         * do your things here
         * since Response in extended from yii\base\Object, you can initialize its values by passing in
         * __constructor() simple array.
         */
        return new MyCustomResponse(['data' => $myCustomData]);
    }
}
```

See also
--------

- [Console](console.md)
