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
				'only' => ['special-callback'],
				'rules' => [
					[
						'actions' => ['special-callback'],
						'allow' => true,
						'matchCallback' => function ($rule, $action) {
							return date('d-m') === '31-10';
						}
					],
```

And the action:

```php
	// ...
	// Match callback called! This page can be accessed only each October 31st
	public function actionSpecialCallback()
	{
		return $this->render('happy-halloween');
	}
```

Sometimes you want a custom action to be taken when access is denied. In this case you can specify `denyCallback`.

Role based access control (RBAC)
--------------------------------

Role based access control is very flexible approach to controlling access that is a perfect match for complex systems
where permissions are customizable.

### Using file-based config for RBAC

In order to start using it some extra steps are required. First of all we need to configure `authManager` application
component in application config file (`web.php` or `main.php` depending on template you've used):

```php
'authManager' => [
    'class' => 'app\components\PhpManager',
    'defaultRoles' => ['guest'],
],
```

Often use role is stored in the same database table as other user data. In this case we may defined it by creating our
own component (`app/components/PhpManager.php`):

```php
<?php
namespace app\components;

use Yii;

class PhpManager extends \yii\rbac\PhpManager
{
    public function init()
    {
        parent::init();
        if (!Yii::$app->user->isGuest) {
            // we suppose that user's role is stored in identity
            $this->assign(Yii::$app->user->identity->id, Yii::$app->user->identity->role);
        }
    }
}
```

Then create permissions hierarchy in `@app/data/rbac.php`:

```php
<?php
use yii\rbac\Item;

return [
    // HERE ARE YOUR MANAGEMENT TASKS
    'manageThing0' => ['type' => Item::TYPE_OPERATION, 'description' => '...', 'bizRule' => NULL, 'data' => NULL],
    'manageThing1' => ['type' => Item::TYPE_OPERATION, 'description' => '...', 'bizRule' => NULL, 'data' => NULL],
    'manageThing2' => ['type' => Item::TYPE_OPERATION, 'description' => '...', 'bizRule' => NULL, 'data' => NULL],
    'manageThing2' => ['type' => Item::TYPE_OPERATION, 'description' => '...', 'bizRule' => NULL, 'data' => NULL],

    // AND THE ROLES
    'guest' => [
        'type' => Item::TYPE_ROLE,
        'description' => 'Guest',
        'bizRule' => NULL,
        'data' => NULL
    ],

    'user' => [
        'type' => Item::TYPE_ROLE,
        'description' => 'User',
        'children' => [
            'guest',
            'manageThing0', // User can edit thing0
        ],
        'bizRule' => 'return !Yii::$app->user->isGuest;',
        'data' => NULL
    ],

    'moderator' => [
        'type' => Item::TYPE_ROLE,
        'description' => 'Moderator',
        'children' => [
            'user',         // Can manage all that user can
            'manageThing1', // and also thing1
        ],
        'bizRule' => NULL,
        'data' => NULL
    ],

    'admin' => [
        'type' => Item::TYPE_ROLE,
        'description' => 'Admin',
        'children' => [
            'moderator',    // can do all the stuff that moderator can
            'manageThing2', // and also manage thing2
        ],
        'bizRule' => NULL,
        'data' => NULL
    ],

    'godmode' => [
        'type' => Item::TYPE_ROLE,
        'description' => 'Super admin',
        'children' => [
            'admin',        // can do all that admin can
            'manageThing3', // and also thing3
        ],
        'bizRule' => NULL,
        'data' => NULL
    ],

];
```

Now you can specify roles from RBAC in controller's access control configuration:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\web\AccessControl',
            'except' => ['something'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['manageThing1'],
                ],
            ],
        ],
    ];
}
```

Another way is to call [[yii\web\User::checkAccess()]] where appropriate.

### Using DB-based storage for RBAC

Storing RBAC hierarchy in database is less efficient performancewise but is much more flexible. It is easier to create
a good management UI for it so in case you need permissions structure that is managed by end user DB is your choice.

In order to get started you need to configure database connection in `db` component. After it is done [get `schema-*.sql`
file for your database](https://github.com/yiisoft/yii2/tree/master/framework/rbac) and execute it.

Next step is to configure `authManager` application component in application config file (`web.php` or `main.php`
depending on template you've used):

```php
'authManager' => [
    'class' => 'yii\rbac\DbManager',
    'defaultRoles' => ['guest'],
],
```

TBD

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
    throw new NotFoundHttpException;
  }
  if (!\Yii::$app->user->checkAccess('edit_article', ['article' => $article])) {
    throw new ForbiddenHttpException;
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
      throw new NotFoundHttpException;
    }
    // ...
}
```
