Using RBAC
===========

Lacking proper documentation, this guide is a stub copied from a topic on the forum.


First af all, you modify your config (web.php or main.php), 
```php
'authManager' => [
    'class' => 'app\components\PhpManager', // THIS IS YOUR AUTH MANAGER
    'defaultRoles' => ['guest'],
],
```

Next, create the manager itself (app/components/PhpManager.php)
```php
<?php
namespace app\components;

use Yii;

class PhpManager extends \yii\rbac\PhpManager
{
    public function init()
    {
        if ($this->authFile === NULL)
            $this->authFile = Yii::getAlias('@app/data/rbac') . '.php'; // HERE GOES YOUR RBAC TREE FILE

        parent::init();

        if (!Yii::$app->user->isGuest) {
            $this->assign(Yii::$app->user->identity->id, Yii::$app->user->identity->role); // we suppose that user's role is stored in identity
        }
    }
}
```

Now, the rules tree (@app/data/rbac.php):
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

As a result, you can now add access control filters to controllers
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
