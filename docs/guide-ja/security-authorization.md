権限付与
========

> Note|注意: この節はまだ執筆中です。

権限付与は、ユーザが何かをするのに十分な許可を得ているか否かを確認するプロセスです。
Yii は二つの権限付与の方法を提供しています。すなわち、アクセスコントロールフィルタ (ACF) と、ロールベースアクセスコントロール (RBAC) です。


アクセスコントロールフィルタ (ACF)
----------------------------------

アクセスコントロールフィルタ (ACF) は、何らかの単純なアクセス制御だけを必要とするアプリケーションで使うのに最も適した、単純な権限付与の方法です。
その名前が示すように、ACF は、コントローラまたはモジュールにビヘイビアとしてアタッチすることが出来るアクションフィルタです。
ACF は一連の [[yii\filters\AccessControl::rules|アクセス規則]] をチェックして、現在のユーザがリクエストしたアクションにアクセスすることが出来るかどうかを確認します。

下記のコードは、[[yii\filters\AccessControl]] として実装された ACF の使い方を示すものです。

```php
use yii\filters\AccessControl;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login', 'logout', 'signup'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'signup'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    // ...
}
```

上記のコードにおいて、ACF は `site` コントローラにビヘイビアとしてアタッチされています。
これはアクションフィルタを使用する典型的な方法です。
`only` オプションは、ACF が `login`、`logout`、`signup` のアクションにのみ適用されるべきであることを指定しています。
`rules` オプションは [[yii\filters\AccessRule|アクセス規則]] を指定するものであり、以下のように読むことが出来ます。

- 全てのゲストユーザ (まだ認証されていないユーザ) に、'login' と 'singup' のアクションにアクセスすることを許可します。
  `roles` オプションに疑問符 `?` が含まれていますが、これは「ゲスト」として認識される特殊なトークンです。
- 認証されたユーザに、'logout' アクションにアクセスすることを許可します。
  `@` という文字はもう一つの特殊なトークンで、認証されたユーザとして認識されるものです。

ACF が権限のチェックを実行するときには、規則を一つずつ上から下へ、適用されるものを見つけるまで調べます。
そして、適用される規則の `allow` の値が、ユーザが権限を有するか否かを判断するのに使われます。
適用される規則が一つもなかった場合は、ユーザが権限をもたないことを意味し、ACF はアクションの継続を中止します。

デフォルトでは、ユーザが現在のアクションにアクセスする権限を持っていないと判定した場合は、ACF は以下のことだけを行います。

* ユーザがゲストである場合は、[[yii\web\User::loginRequired()]] を呼び出します。
  このメソッドで、ブラウザをログインページにリダイレクトすることが出来ます。
* ユーザが既に認証されている場合は、[[yii\web\ForbiddenHttpException]] を投げます。

この動作は、[[yii\filters\AccessControl::denyCallback]] プロパティを構成することによって、カスタマイズすることが出来ます。

```php
[
    'class' => AccessControl::className(),
    'denyCallback' => function ($rule, $action) {
        throw new \Exception('このページにアクセスする権限がありません。');
    }
]
```

[[yii\filters\AccessRule|アクセス規則]] は多くのオプションをサポートしています。
以下はサポートされているオプションの要約です。
[[yii\filters\AccessRule]] を拡張して、あなた自身のカスタマイズしたアクセス規則のクラスを作ることも出来ます。

 * [[yii\filters\AccessRule::allow|allow]]: これが「許可」の規則であるか、「禁止」の規則であるかを指定します。

 * [[yii\filters\AccessRule::actions|actions]]: どのアクションにこの規則が適用されるかを指定します。
これはアクション ID の配列でなければなりません。
比較は大文字と小文字を区別します。
このオプションが空であるか指定されていない場合は、規則が全てのアクションに適用されることを意味します。

 * [[yii\filters\AccessRule::controllers|controllers]]: どのコントローラにこの規則が適用されるかを指定します。
これはコントローラ ID の配列でなければなりません。
比較は大文字と小文字を区別します。
このオプションが空であるか指定されていない場合は、規則が全てのコントローラに適用されることを意味します。

 * [[yii\filters\AccessRule::roles|roles]]: どのユーザロールにこの規則が適用されるかを指定します。
   二つの特別なロールが認識されます。
   これらは、[[yii\web\User::isGuest]] によって判断されます。

    - `?`: ゲストユーザ (まだ認証されていないユーザ) を意味します。
    - `@`: 認証されたユーザを意味します。

   その他のロール名を使う場合には、RBAC (次の節で説明します) が必要とされ、判断のために [[yii\web\User::can()]] が呼び出されます。
   このオプションが空であるか指定されていない場合は、規則が全てのロールに適用されることを意味します。

 * [[yii\filters\AccessRule::ips|ips]]: どの [[yii\web\Request::userIP|クライアントの IP アドレス]] にこの規則が適用されるかを指定します。
IP アドレスは、最後にワイルドカード `*` を含むことが出来て、同じプレフィクスを持つ IP アドレスに合致させることが出来ます。
例えば、'192.168.*' は、'192.168.' のセグメントに属する全ての IP アドレスに合致します。
このオプションが空であるか指定されていない場合は、規則が全ての IP アドレスに適用されることを意味します。

 * [[yii\filters\AccessRule::verbs|verbs]]: どのリクエストメソッド (例えば、`GET` や `POST`) にこの規則が適用されるかを指定します。
比較は大文字と小文字を区別しません。

 * [[yii\filters\AccessRule::matchCallback|matchCallback]]: この規則が適用されるべきか否かを決定するために呼び出されるべき PHP コーラブルを指定します。

 * [[yii\filters\AccessRule::denyCallback|denyCallback]]: この規則がアクセスを禁止する場合に呼び出されるべき PHP コーラブルを指定します。

下記は、`matchCallback` オプションを利用する方法を示す例です。
このオプションによって、任意のアクセス制御ロジックを書くことが可能になります。

```php
use yii\filters\AccessControl;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['special-callback'],
                'rules' => [
                    [
                        'actions' => ['special-callback'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return date('d-m') === '31-10';
                        }
                    ],
                ],
            ],
        ];
    }

    // matchCallback が呼ばれる。このページは毎年10月31日だけアクセス出来ます。
    public function actionSpecialCallback()
    {
        return $this->render('happy-halloween');
    }
}
```


Role based access control (RBAC)
--------------------------------

Role-Based Access Control (RBAC) provides a simple yet powerful centralized access control. Please refer to
the [Wiki article](http://en.wikipedia.org/wiki/Role-based_access_control) for details about comparing RBAC
with other more traditional access control schemes.

Yii implements a General Hierarchical RBAC, following the [NIST RBAC model](http://csrc.nist.gov/rbac/sandhu-ferraiolo-kuhn-00.pdf).
It provides the RBAC functionality through the [[yii\rbac\ManagerInterface|authManager]] [application component](structure-application-components.md).

Using RBAC involves two parts of work. The first part is to build up the RBAC authorization data, and the second
part is to use the authorization data to perform access check in places where it is needed.

To facilitate our description next, we will first introduce some basic RBAC concepts.


### Basic Concepts

A role represents a collection of *permissions* (e.g. creating posts, updating posts). A role may be assigned
to one or multiple users. To check if a user has a specified permission, we may check if the user is assigned
with a role that contains that permission.

Associated with each role or permission, there may be a *rule*. A rule represents a piece of code that will be
executed during access check to determine if the corresponding role or permission applies to the current user.
For example, the "update post" permission may have a rule that checks if the current user is the post creator.
During access checking, if the user is NOT the post creator, he/she will be considered not having the "update post" permission.

Both roles and permissions can be organized in a hierarchy. In particular, a role may consist of other roles or permissions;
and a permission may consist of other permissions. Yii implements a *partial order* hierarchy which includes the
more special *tree* hierarchy. While a role can contain a permission, it is not true vice versa.


### Configuring RBAC Manager

Before we set off to define authorization data and perform access checking, we need to configure the
[[yii\base\Application::authManager|authManager]] application component. Yii provides two types of authorization managers:
[[yii\rbac\PhpManager]] and [[yii\rbac\DbManager]]. The former uses a PHP script file to store authorization
data, while the latter stores authorization data in database. You may consider using the former if your application
does not require very dynamic role and permission management.

The following code shows how to configure `authManager` in the application configuration:

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        // ...
    ],
];
```

The `authManager` can now be accessed via `\Yii::$app->authManager`.

> Tip: By default, [[yii\rbac\PhpManager]] stores RBAC data in files under `@app/rbac/` directory. Make sure directory
  and all the files in it are writable by the Web server process if permissions hierarchy needs to be changed online.


### Building Authorization Data

Building authorization data is all about the following tasks:

- defining roles and permissions;
- establishing relations among roles and permissions;
- defining rules;
- associating rules with roles and permissions;
- assigning roles to users.

Depending on authorization flexibility requirements the tasks above could be done in different ways.

If your permissions hierarchy doesn't change at all and you have a fixed number of users you can create a
[console command](tutorial-console.md#create-command) command that will initialize authorization data once via APIs offered by `authManager`:

```php
<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // add "createPost" permission
        $createPost = $auth->createPermission('createPost');
        $createPost->description = 'Create a post';
        $auth->add($createPost);

        // add "updatePost" permission
        $updatePost = $auth->createPermission('updatePost');
        $updatePost->description = 'Update post';
        $auth->add($updatePost);

        // add "author" role and give this role the "createPost" permission
        $author = $auth->createRole('author');
        $auth->add($author);
        $auth->addChild($author, $createPost);

        // add "admin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $updatePost);
        $auth->addChild($admin, $author);

        // Assign roles to users. 1 and 2 are IDs returned by IdentityInterface::getId()
        // usually implemented in your User model.
        $auth->assign($author, 2);
        $auth->assign($admin, 1);
    }
}
```

After executing the command with `yii rbac/init` we'll get the following hierarchy:

![Simple RBAC hierarchy](images/rbac-hierarchy-1.png "Simple RBAC hierarchy")

Author can create post, admin can update post and do everything author can.

If your application allows user signup you need to assign roles to these new users once. For example, in order for all
signed up users to become authors you in advanced application template you need to modify `frontend\models\SignupForm::signup()`
as follows:

```php
public function signup()
{
    if ($this->validate()) {
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->save(false);

        // the following three lines were added:
        $auth = Yii::$app->authManager;
        $authorRole = $auth->getRole('author');
        $auth->assign($authorRole, $user->getId());

        return $user;
    }

    return null;
}
```

For applications that require complex access control with dynamically updated authorization data, special user interfaces
(i.e. admin panel) may need to be developed using APIs offered by `authManager`.


### Using Rules

As aforementioned, rules add additional constraint to roles and permissions. A rule is a class extending
from [[yii\rbac\Rule]]. It must implement the [[yii\rbac\Rule::execute()|execute()]] method. In the hierarchy we've
created previously author cannot edit his own post. Let's fix it. First we need a rule to verify that the user is the post author:

```php
namespace app\rbac;

use yii\rbac\Rule;

/**
 * Checks if authorID matches user passed via params
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    /**
     * @param string|integer $user the user ID.
     * @param Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['post']) ? $params['post']->createdBy == $user : false;
    }
}
```

The rule above checks if the `post` is created by `$user`. We'll create a special permission `updateOwnPost` in the
command we've used previously:

```php
$auth = Yii::$app->authManager;

// add the rule
$rule = new \app\rbac\AuthorRule;
$auth->add($rule);

// add the "updateOwnPost" permission and associate the rule with it.
$updateOwnPost = $auth->createPermission('updateOwnPost');
$updateOwnPost->description = 'Update own post';
$updateOwnPost->ruleName = $rule->name;
$auth->add($updateOwnPost);

// "updateOwnPost" will be used from "updatePost"
$auth->addChild($updateOwnPost, $updatePost);

// allow "author" to update their own posts
$auth->addChild($author, $updateOwnPost);
```

Now we've got the following hierarchy:

![RBAC hierarchy with a rule](images/rbac-hierarchy-2.png "RBAC hierarchy with a rule")

### Access Check

With the authorization data ready, access check is as simple as a call to the [[yii\rbac\ManagerInterface::checkAccess()]]
method. Because most access check is about the current user, for convenience Yii provides a shortcut method
[[yii\web\User::can()]], which can be used like the following:

```php
if (\Yii::$app->user->can('createPost')) {
    // create post
}
```

If the current user is Jane with ID=1 we're starting at `createPost` and trying to get to `Jane`:

![Access check](images/rbac-access-check-1.png "Access check")

In order to check if user can update post we need to pass an extra parameter that is required by the `AuthorRule` described before:

```php
if (\Yii::$app->user->can('updatePost', ['post' => $post])) {
    // update post
}
```

Here's what happens if current user is John:


![Access check](images/rbac-access-check-2.png "Access check")

We're starting with the `updatePost` and going through `updateOwnPost`. In order to pass it `AuthorRule` should return
`true` from its `execute` method. The method receives its `$params` from `can` method call so the value is
`['post' => $post]`. If everything is OK we're getting to `author` that is assigned to John.

In case of Jane it is a bit simpler since she's an admin:

![Access check](images/rbac-access-check-3.png "Access check")

### Using Default Roles

A default role is a role that is *implicitly* assigned to *all* users. The call to [[yii\rbac\ManagerInterface::assign()]]
is not needed, and the authorization data does not contain its assignment information.

A default role is usually associated with a rule which determines if the role applies to the user being checked.

Default roles are often used in applications which already have some sort of role assignment. For example, an application
may have a "group" column in its user table to represent which privilege group each user belongs to.
If each privilege group can be mapped to a RBAC role, you can use the default role feature to automatically
assign each user to a RBAC role. Let's use an example to show how this can be done.

Assume in the user table, you have a `group` column which uses 1 to represent the administrator group and 2 the author group.
You plan to have two RBAC roles `admin` and `author` to represent the permissions for these two groups, respectively.
You can create set up the RBAC data as follows,


```php
namespace app\rbac;

use Yii;
use yii\rbac\Rule;

/**
 * Checks if user group matches
 */
class UserGroupRule extends Rule
{
    public $name = 'userGroup';

    public function execute($user, $item, $params)
    {
        if (!Yii::$app->user->isGuest) {
            $group = Yii::$app->user->identity->group;
            if ($item->name === 'admin') {
                return $group == 1;
            } elseif ($item->name === 'author') {
                return $group == 1 || $group == 2;
            }
        }
        return false;
    }
}

$auth = Yii::$app->authManager;

$rule = new \app\rbac\UserGroupRule;
$auth->add($rule);

$author = $auth->createRole('author');
$author->ruleName = $rule->name;
$auth->add($author);
// ... add permissions as children of $author ...

$admin = $auth->createRole('admin');
$admin->ruleName = $rule->name;
$auth->add($admin);
$auth->addChild($admin, $author);
// ... add permissions as children of $admin ...
```

Note that in the above, because "author" is added as a child of "admin", when you implement the `execute()` method
of the rule class, you need to respect this hierarchy as well. That is why when the role name is "author",
the `execute()` method will return true if the user group is either 1 or 2 (meaning the user is in either "admin"
group or "author" group).

Next, configure `authManager` by listing the two roles in [[yii\rbac\BaseManager::$defaultRoles]]:

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['admin', 'author'],
        ],
        // ...
    ],
];
```

Now if you perform an access check, both of the `admin` and `author` roles will be checked by evaluating
the rules associated with them. If the rule returns true, it means the role applies to the current user.
Based on the above rule implementation, this means if the `group` value of a user is 1, the `admin` role
would apply to the user; and if the `group` value is 2, the `author` role would apply.
