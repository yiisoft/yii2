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

Routes
------

Each controller action has a corresponding internal route. In our example above `actionIndex` has `site/index` route
and `actionTest` has `site/test` route. In this route `site` is referred to as controller ID while `test` is referred to
as action ID.

By default you can access specific controller and action using the `http://example.com/?r=controller/action` URL. This
behavior is fully customizable. For details refer to [URL Management](url.md).

If controller is located inside a module its action internal route will be `module/controller/action`.

In case module, controller or action specified isn't found Yii will return "not found" page and HTTP status code 404.

### Defaults

If user isn't specifying any route i.e. using URL like `http://example.com/`, Yii assumes that default route should be
used. It is determined by [[\yii\web\Application::defaultRoute]] method and is `site` by default meaning that `SiteController`
will be loaded.

A controller has a default action. When the user request does not specify which action to execute by usign an URL such as
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

		if($version) {
			$text = $post->getHistory($version);
		}

		return $this->render('view', array(
			'post' => $post,
			'text' => $text,
		));
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
		if(!$post) {
			throw new HttpException(404);
		}

		$data = \Yii::$app->request->getPost('Post');
		if($data) {
			$post->populate($data);
			if($post->save()) {
				$this->redirect(array('view', 'id' => $post->id));
			}
		}

		return $this->render('update', array(
			'post' => $post,
		));
	}
}
```

Standalone actions
------------------

If action is generic enough it makes sense to implement it in a separate class to be able to reuse it.
Create `actions/Page.php`

```php
namespace \app\actions;

class Page extends \yii\base\Action
{
	public $view = 'index';

	public function run()
	{
		$this->controller->render($view);
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
		return array(
			'about' => array(
				'class' => '@app/actions/Page',
					'view' => 'about',
				),
			),
		);
	}
}
```

After doing so you can access your action as `http://example.com/?r=site/about`.

Filters
-------

Catching all incoming requests
------------------------------


See also
--------

- [Console](console.md)