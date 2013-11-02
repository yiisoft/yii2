Authorization
=============

Authorization is the process of verifying that user has enough permissions to do something. Yii provides several methods
of controlling it.

Access control basics
---------------------

Basic access control is very simple to implement using [[\yii\web\AccessControl]]:

```php
class SiteController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => \yii\web\AccessControl::className(),
				'only' => ['login', 'logout', 'signup'],
				'rules' => [
					[
						'actions' => ['login', 'signup'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}
	// ...
```

In the code above we're attaching access control behavior to a controller. Since there's `only` option specified, it
will be applied to 'login', 'logout' and 'signup' actions only. A set of rules that are basically options for
[[\yii\web\AccessRule]] reads as follows:

- Allow all guest (not yet authenticated) users to access 'login' and 'signup' actions.
- Allow authenticated users to access 'logout' action.

Rules are checked one by one from top to bottom. If rule matches, action takes place immediately. If not, next rule is
checked. If no rules matched access is denied.

[[\yii\web\AccessRule]] is quite flexible and allows additionally to what was demonstrated checking IPs and request method
(i.e. POST, GET). If it's not enough you can specify your own check via anonymous function:

```php
class SiteController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => \yii\web\AccessControl::className(),
				'only' => ['special'],
				'rules' => [
					[
						'actions' => ['special'],
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							return date('d-m') === '31-10';
						}
					],
```

Sometimes you want a custom action to be taken when access is denied. In this case you can specify `denyCallback`.

Role based access control (RBAC)
--------------------------------

Role based access control is very flexible approach to controlling access that is a perfect match for complex systems
where permissions are customizable.

In order to start using it some extra steps are required. First of all we need to configure `authManager` application
component:

```php

```

Then create permissions hierarchy.

Specify roles from RBAC in controller's access control configuration or call [[User::checkAccess()]] where appropriate.

### How it works

TBD: write about how it works with pictures :)

### Avoiding too much RBAC

In order to keep auth hierarchy simple and efficient you should avoid creating and using too much nodes. Most of the time
simple checks could be used instead. For example such code that uses RBAC:

```php
public function editArticle($id)
{
  $article = Article::find($id);
  if (!$article) {
    throw new HttpException(404);
  }
  if (!\Yii::$app->user->checkAccess('edit_article', ['article' => $article])) {
    throw new HttpException(403);
  }
  // ...
}
```

can be replaced with simpler code that doesn't use RBAC:

```php
public function editArticle($id)
{
    $article = Article::find(['id' => $id, 'author_id' => \Yii::$app->user->id]);
    if (!$article) {
      throw new HttpException(404);
    }
    // ...
}
```
