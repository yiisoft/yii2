Controllers
===========

Controllers are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are objects of classes extending from [[yii\base\Controller]] and are responsible for processing requests and
generating responses. In particular, after taking over the control from [applications](structure-applications.md),
controllers will analyze incoming request data, pass them to [models](structure-models.md), inject model results
into [views](structure-views.md), and finally generate outgoing responses.


## Actions <span id="actions"></span>

Controllers are composed of *actions* which are the most basic units that end users can address and request for
execution. A controller can have one or multiple actions.

The following example shows a `post` controller with two actions: `view` and `create`:

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

In the `view` action (defined by the `actionView()` method), the code first loads the [model](structure-models.md)
according to the requested model ID; If the model is loaded successfully, it will display it using
a [view](structure-views.md) named `view`. Otherwise, it will throw an exception.

In the `create` action (defined by the `actionCreate()` method), the code is similar. It first tries to populate
a new instance of the [model](structure-models.md) using the request data and save the model. If both succeed it
will redirect the browser to the `view` action with the ID of the newly created model. Otherwise it will display
the `create` view through which users can provide the needed input.


## Routes <span id="routes"></span>

End users address actions through the so-called *routes*. A route is a string that consists of the following parts:

* a module ID: this exists only if the controller belongs to a non-application [module](structure-modules.md);
* a [controller ID](#controller-ids): a string that uniquely identifies the controller among all controllers within the same application
  (or the same module if the controller belongs to a module);
* an [action ID](#action-ids): a string that uniquely identifies the action among all actions within the same controller.

Routes take the following format:

```
ControllerID/ActionID
```

or the following format if the controller belongs to a module:

```php
ModuleID/ControllerID/ActionID
```

So if a user requests with the URL `http://hostname/index.php?r=site/index`, the `index` action in the `site` controller
will be executed. For more details on how routes are resolved into actions, please refer to
the [Routing and URL Creation](runtime-routing.md) section.


## Creating Controllers <span id="creating-controllers"></span>

In [[yii\web\Application|Web applications]], controllers should extend from [[yii\web\Controller]] or its
child classes. Similarly in [[yii\console\Application|console applications]], controllers should extend from
[[yii\console\Controller]] or its child classes. The following code defines a `site` controller:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### Controller IDs <span id="controller-ids"></span>

Usually, a controller is designed to handle the requests regarding a particular type of resource.
For this reason, controller IDs are often nouns referring to the types of the resources that they are handling.
For example, you may use `article` as the ID of a controller that handles article data.

By default, controller IDs should contain these characters only: English letters in lower case, digits,
underscores, hyphens, and forward slashes. For example, `article` and `post-comment` are both valid controller IDs,
while `article?`, `PostComment`, `admin\post` are not.

A controller ID may also contain a subdirectory prefix. For example, `admin/article` stands for an `article` controller
in the `admin` subdirectory under the [[yii\base\Application::controllerNamespace|controller namespace]].
Valid characters for subdirectory prefixes include: English letters in lower and upper cases, digits, underscores, and
forward slashes, where forward slashes are used as separators for multi-level subdirectories (e.g. `panels/admin`).


### Controller Class Naming <span id="controller-class-naming"></span>

Controller class names can be derived from controller IDs according to the following procedure:

1. Turn the first letter in each word separated by hyphens into upper case. Note that if the controller ID
  contains slashes, this rule only applies to the part after the last slash in the ID.
2. Remove hyphens and replace any forward slashes with backward slashes.
3. Append the suffix `Controller`.
4. Prepend the [[yii\base\Application::controllerNamespace|controller namespace]].

The following are some examples, assuming the [[yii\base\Application::controllerNamespace|controller namespace]]
takes the default value `app\controllers`:

* `article` becomes `app\controllers\ArticleController`;
* `post-comment` becomes `app\controllers\PostCommentController`;
* `admin/post-comment` becomes `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` becomes `app\controllers\adminPanels\PostCommentController`.

Controller classes must be [autoloadable](concept-autoloading.md). For this reason, in the above examples,
the `article` controller class should be saved in the file whose [alias](concept-aliases.md)
is `@app/controllers/ArticleController.php`; while the `admin/post-comment` controller should be
in `@app/controllers/admin/PostCommentController.php`.

> Info: The last example `admin/post-comment` shows how you can put a controller under a sub-directory
  of the [[yii\base\Application::controllerNamespace|controller namespace]]. This is useful when you want
  to organize your controllers into several categories and you do not want to use [modules](structure-modules.md).


### Controller Map <span id="controller-map"></span>

You can configure the [[yii\base\Application::controllerMap|controller map]] to overcome the constraints
of the controller IDs and class names described above. This is mainly useful when you are using
third-party controllers and you do not have control over their class names.

You may configure the [[yii\base\Application::controllerMap|controller map]] in the
[application configuration](structure-applications.md#application-configurations). For example:

```php
[
    'controllerMap' => [
        // declares "account" controller using a class name
        'account' => 'app\controllers\UserController',

        // declares "article" controller using a configuration array
        'article' => [
            '__class' => app\controllers\PostController::class,
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### Default Controller <span id="default-controller"></span>

Each application has a default controller specified via the [[yii\base\Application::defaultRoute]] property.
When a request does not specify a [route](#routes), the route specified by this property will be used.
For [[yii\web\Application|Web applications]], its value is `'site'`, while for [[yii\console\Application|console applications]],
it is `help`. Therefore, if a URL is `http://hostname/index.php`, then the `site` controller will handle the request.

You may change the default controller with the following [application configuration](structure-applications.md#application-configurations):

```php
[
    'defaultRoute' => 'main',
]
```


## Creating Actions <span id="creating-actions"></span>

Creating actions can be as simple as defining the so-called *action methods* in a controller class. An action method is
a *public* method whose name starts with the word `action`. The return value of an action method represents
the response data to be sent to end users. The following code defines two actions, `index` and `hello-world`:

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


### Action IDs <span id="action-ids"></span>

An action is often designed to perform a particular manipulation of a resource. For this reason,
action IDs are usually verbs, such as `view`, `update`, etc.

By default, action IDs should contain these characters only: English letters in lower case, digits,
underscores, and hyphens (you can use hyphens to separate words). For example,
`view`, `update2`, and `comment-post` are all valid action IDs, while `view?` and `Update` are not.

You can create actions in two ways: inline actions and standalone actions. An inline action is
defined as a method in the controller class, while a standalone action is a class extending
[[yii\base\Action]] or its child classes. Inline actions take less effort to create and are often preferred
if you have no intention to reuse these actions. Standalone actions, on the other hand, are mainly
created to be used in different controllers or be redistributed as [extensions](structure-extensions.md).


### Inline Actions <span id="inline-actions"></span>

Inline actions refer to the actions that are defined in terms of action methods as we just described.

The names of the action methods are derived from action IDs according to the following procedure:

1. Turn the first letter in each word of the action ID into upper case.
2. Remove hyphens.
3. Prepend the prefix `action`.

For example, `index` becomes `actionIndex`, and `hello-world` becomes `actionHelloWorld`.

> Note: The names of the action methods are *case-sensitive*. If you have a method named `ActionIndex`,
  it will not be considered as an action method, and as a result, the request for the `index` action
  will result in an exception. Also note that action methods must be public. A private or protected
  method does NOT define an inline action.


Inline actions are the most commonly defined actions because they take little effort to create. However,
if you plan to reuse the same action in different places, or if you want to redistribute an action,
you should consider defining it as a *standalone action*.


### Standalone Actions <span id="standalone-actions"></span>

Standalone actions are defined in terms of action classes extending [[yii\base\Action]] or its child classes.
For example, in the Yii releases, there are [[yii\web\ViewAction]] and [[yii\web\ErrorAction]], both of which
are standalone actions.

To use a standalone action, you should declare it in the *action map* by overriding the
[[yii\base\Controller::actions()]] method in your controller classes like the following:

```php
public function actions()
{
    return [
        // declares "error" action using a class name
        'error' => 'yii\web\ErrorAction',

        // declares "view" action using a configuration array
        'view' => [
            '__class' => yii\web\ViewAction::class,
            'viewPrefix' => '',
        ],
    ];
}
```

As you can see, the `actions()` method should return an array whose keys are action IDs and values the corresponding
action class names or [configurations](concept-configurations.md). Unlike inline actions, action IDs for standalone
actions can contain arbitrary characters, as long as they are declared in the `actions()` method.

To create a standalone action class, you should extend [[yii\base\Action]] or a child class, and implement
a public method named `run()`. The role of the `run()` method is similar to that of an action method. For example,

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### Action Results <span id="action-results"></span>

The return value of an action method or of the `run()` method of a standalone action is significant. It stands
for the result of the corresponding action.

The return value can be a [response](runtime-responses.md) object which will be sent to the end user as the response.

* For [[yii\web\Application|Web applications]], the return value can also be some arbitrary data which will
  be assigned to [[yii\web\Response::data]] and be further converted into a string representing the response body.
* For [[yii\console\Application|console applications]], the return value can also be an integer representing
  the [[yii\console\Response::exitStatus|exit status]] of the command execution.

In the examples shown above, the action results are all strings which will be treated as the response body
to be sent to end users. The following example shows how an action can redirect the user browser to a new URL
by returning a response object (because the [[yii\web\Controller::redirect()|redirect()]] method returns
a response object):

```php
public function actionForward()
{
    // redirect the user browser to http://example.com
    return $this->redirect('http://example.com');
}
```


### Action Parameters <span id="action-parameters"></span>

The action methods for inline actions and the `run()` methods for standalone actions can take parameters,
called *action parameters*. Their values are obtained from requests. For [[yii\web\Application|Web applications]],
the value of each action parameter is retrieved from `$_GET` using the parameter name as the key;
for [[yii\console\Application|console applications]], they correspond to the command line arguments.

In the following example, the `view` action (an inline action) has declared two parameters: `$id` and `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

The action parameters will be populated as follows for different requests:

* `http://hostname/index.php?r=post/view&id=123`: the `$id` parameter will be filled with the value
  `'123'`,  while `$version` is still `null` because there is no `version` query parameter.
* `http://hostname/index.php?r=post/view&id=123&version=2`: the `$id` and `$version` parameters will
  be filled with `'123'` and `'2'`, respectively.
* `http://hostname/index.php?r=post/view`: a [[yii\web\BadRequestHttpException]] exception will be thrown
  because the required `$id` parameter is not provided in the request.
* `http://hostname/index.php?r=post/view&id[]=123`: a [[yii\web\BadRequestHttpException]] exception will be thrown
  because `$id` parameter is receiving an unexpected array value `['123']`.

If you want an action parameter to accept array values, you should type-hint it with `array`, like the following:

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

Now if the request is `http://hostname/index.php?r=post/view&id[]=123`, the `$id` parameter will take the value
of `['123']`. If the request is `http://hostname/index.php?r=post/view&id=123`, the `$id` parameter will still
receive the same array value because the scalar value `'123'` will be automatically turned into an array.

The above examples mainly show how action parameters work for Web applications. For console applications,
please refer to the [Console Commands](tutorial-console.md) section for more details.


### Default Action <span id="default-action"></span>

Each controller has a default action specified via the [[yii\base\Controller::defaultAction]] property.
When a [route](#routes) contains the controller ID only, it implies that the default action of
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


## Controller Lifecycle <span id="controller-lifecycle"></span>

When processing a request, an [application](structure-applications.md) will create a controller
based on the requested [route](#routes). The controller will then undergo the following lifecycle
to fulfill the request:

1. The [[yii\base\Controller::init()]] method is called after the controller is created and configured.
2. The controller creates an action object based on the requested action ID:
   * If the action ID is not specified, the [[yii\base\Controller::defaultAction|default action ID]] will be used.
   * If the action ID is found in the [[yii\base\Controller::actions()|action map]], a standalone action
     will be created;
   * If the action ID is found to match an action method, an inline action will be created;
   * Otherwise an [[yii\base\InvalidRouteException]] exception will be thrown.
3. The controller sequentially calls the `beforeAction()` method of the application, the module (if the controller
   belongs to a module), and the controller.
   * If one of the calls returns `false`, the rest of the uncalled `beforeAction()` methods will be skipped and the
     action execution will be cancelled.
   * By default, each `beforeAction()` method call will trigger a `beforeAction` event to which you can attach a handler.
4. The controller runs the action.
   * The action parameters will be analyzed and populated from the request data.
5. The controller sequentially calls the `afterAction()` method of the controller, the module (if the controller
   belongs to a module), and the application.
   * By default, each `afterAction()` method call will trigger an `afterAction` event to which you can attach a handler.
6. The application will take the action result and assign it to the [response](runtime-responses.md).


## Best Practices <span id="best-practices"></span>

In a well-designed application, controllers are often very thin, with each action containing only a few lines of code.
If your controller is rather complicated, it usually indicates that you should refactor it and move some code
to other classes.

Here are some specific best practices. Controllers

* may access the [request](runtime-requests.md) data;
* may call methods of [models](structure-models.md) and other service components with request data;
* may use [views](structure-views.md) to compose responses;
* should NOT process the request data - this should be done in [the model layer](structure-models.md);
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md).
