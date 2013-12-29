Controller
==========

Controller is one of the key parts of the application. It determines how to handle incoming request and creates a response.

Most often a controller takes HTTP request data and returns HTML, JSON or XML as a response.

Basics
------

Controller resides in application's `controllers` directory is is named like `SiteController.php` where `Site`
part could be anything describing a set of actions it contains.

The basic web controller is a class that extends [[\yii\web\Controller]] and could be very simple:

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
The output of an action is what the method returns, it could be rendered result or it can be instance of ```yii\web\Response```, for [example](#custom-response-class).
The return value will be handled by the `response` application
component which can convert the output to differnet formats such as JSON for example. The default behavior
is to output the value unchanged though.

You also can disable CSRF validation per controller and/or action, by setting its property:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{

	public $enableCsrfValidation = false;

	public function actionIndex()
	{
		#CSRF validation will no be applied on this and other actions
	}

}
```

To disable CSRF validation per custom actions you can do:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
	public function beforeAction($action)
	{
		// ...set `$this->enableCsrfValidation` here based on some conditions...
		// call parent method that will check CSRF if such property is true.
		return parent::beforeAction($action);
	}
}
```

Routes
------

Each controller action has a corresponding internal route. In our example above `actionIndex` has `site/index` route
and `actionTest` has `site/test` route. In this route `site` is referred to as controller ID while `test` is referred to
as action ID.

By default you can access specific controller and action using the `http://example.com/?r=controller/action` URL. This
behavior is fully customizable. For details refer to [URL Management](url.md).

If controller is located inside a module its action internal route will be `module/controller/action`.

In case module, controller or action specified isn't found Yii will return "not found" page and HTTP status code 404.

> Note: If controller name or action name contains camelCased words, internal route will use dashes i.e. for
`DateTimeController::actionFastForward` route will be `date-time/fast-forward`.

### Defaults

If user isn't specifying any route i.e. using URL like `http://example.com/`, Yii assumes that default route should be
used. It is determined by [[\yii\web\Application::defaultRoute]] method and is `site` by default meaning that `SiteController`
will be loaded.

A controller has a default action. When the user request does not specify which action to execute by using an URL such as
`http://example.com/?r=site`, the default action will be executed. By default, the default action is named as `index`.
It can be changed by setting the [[\yii\base\Controller::defaultAction]] property.

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
		$post = Post::find($id);
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
		$post = Post::find($id);
		if (!$post) {
			throw new NotFoundHttpException;
		}

		if (\Yii::$app->request->isPost) {
			$post->load($_POST);
			if ($post->save()) {
				$this->redirect(['view', 'id' => $post->id]);
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
public SiteController extends \yii\web\Controller
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

Action filters are implemented via behaviors. You should extend from `ActionFilter` to
define a new filter. To use a filter, you should attach the filter class to the controller
as a behavior. For example, to use the [[AccessControl]] filter, you should have the following
code in a controller:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\web\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

In order to learn more about access control check [authorization](authorization.md) section of the guide.
Two other filters, [[PageCache]] and [[HttpCache]] are described in [caching](caching.md) section of the guide.

Catching all incoming requests
------------------------------

TBD

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
