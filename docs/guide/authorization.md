Authorization
=============

Authorization is the process of verifying that user has enough permissions to do something. Yii provides several methods
of controlling it.

Access control basics
---------------------

Basic access control is very simple to implement using [[yii\filters\AccessControl]]:

```php
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
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
[[yii\filters\AccessRule]] reads as follows:

- Allow all guest (not yet authenticated) users to access 'login' and 'signup' actions.
- Allow authenticated users to access 'logout' action.

Rules are checked one by one from top to bottom. If rule matches, action takes place immediately. If not, next rule is
checked. If no rules matched access is denied.

[[yii\filters\AccessRule]] is quite flexible and allows additionally to what was demonstrated checking IPs and request method
(i.e. POST, GET). If it's not enough you can specify your own check via anonymous function:

```php
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
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
