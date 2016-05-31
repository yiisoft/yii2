Autorización
============

Autorización esl el proceso de verificación de que un usuario tenga sugifientes permisos para realizar algo. Yii provee
dos métodos de autorización: Filtro de Control de Acceso y Control Basado en Roles (ACF y RBAC por sus siglas en inglés).


## Filtro de Control de Acceso <span id="access-control-filter"></span>

Filtro de Control de Acceso (ACF) es un único método de autorización implementado como [[yii\filters\AccessControl]], el cual
es mejor utilizado por aplicaciones que sólo requieran un control de acceso simple. Como su nombre lo indica, ACF es 
un [filtro](structure-filters.md) de acción que puede ser utilizado en un controlador o en un módulo. Cuando un usuario solicita
la ejecución de una acción, ACF comprobará una lista de [[yii\filters\AccessControl::rules|reglas de acceso]] 
para determinar si el usuario tiene permitido acceder a dicha acción.

El siguiente código muestra cómo utilizar ACF en el controlador `site`:

```php
use yii\web\Controller;
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

En el código anterior, ACF es adjuntado al controlador `site` en forma de behavior (comportamiento). Esta es la forma típica de utilizar
un filtro de acción. La opción `only` especifica que el ACF debe ser aplicado solamente a las acciones `login`, `logout` y `signup`.
Las acciones restantes en el controlador `site` no están sujetas al control de acceso. La opción `rules` lista 
las [[yii\filters\AccessRule|reglas de acceso]], y se lee como a continuación:

- Permite a todos los usuarios invitados (sin autenticar) acceder a las acciones `login` y `signup`. La opción `roles`
  contiene el signo de interrogación `?`, que es un código especial para representar a los "invitados".
- Permite a los usuarios autenticados acceder a la acción `logout`. El signo `@` es otro código especial que representa
  a los "usuarios autenticados".

ACF ejecuta la comprobación de autorización examinando las reglas de acceso una a una desde arriba hacia abajo hasta que encuentra
una regla que aplique al contexto de ejecución actual. El valor `allow` de la regla que coincida será entonces utilizado 
para juzgar si el usuario está autorizado o no. Si ninguna de las reglas coincide, significa que el usuario NO está autorizado,
y el ACF detendrá la ejecución de la acción.

Cuando el ACF determina que un usuario no está autorizado a acceder a la acción actual, toma las siguientes medidas por defecto:

* Si el usuario es un invitado, llamará a [[yii\web\User::loginRequired()]] para redireccionar el navegador a la pantalla de login.
* Si el usuario está autenticado, lanzará una excepeción [[yii\web\ForbiddenHttpException]].

Puedes personalizar este comportamiento configurando la propiedad [[yii\filters\AccessControl::denyCallback]] como a continuación:

```php
[
    'class' => AccessControl::className(),
    ...
    'denyCallback' => function ($rule, $action) {
        throw new \Exception('No tienes los suficientes permisos para acceder a esta página');
    }
]
```

Las [[yii\filters\AccessRule|Reglas de Acceso]] soportan varias opciones. Abajo hay un resumen de las mismas.
También puedes extender de [[yii\filters\AccessRule]] para crear tus propias clases de reglas de acceso personalizadas.

 * [[yii\filters\AccessRule::allow|allow]]: especifica si la regla es de tipo "allow" (permitir) o "deny" (denegar).

 * [[yii\filters\AccessRule::actions|actions]]: especifica con qué acciones coinciden con esta regla. Esta debería ser
un array de IDs de acciones. La comparación es sensible a mayúsculas. Si la opción está vacía o no definida,
significa que la regla se aplica a todas las acciones.

 * [[yii\filters\AccessRule::controllers|controllers]]: especifica con qué controladores coincide
esta regla. Esta debería ser un array de IDs de controladores. Cada ID de controlador es prefijado con el ID del módulo (si existe).
La comparación es sensible a mayúsculas. Si la opción está vacía o no definida, significa que la regla se aplica a todos los controladores.

 * [[yii\filters\AccessRule::roles|roles]]: especifica con qué roles de usuarios coincide esta regla.
   Son reconocidos dos roles especiales, y son comprobados vía [[yii\web\User::isGuest]]:

     - `?`: coincide con el usuario invitado (sin autenticar)
     - `@`: coincide con el usuario autenticado

   El utilizar otro nombre de rol invocará una llamada a [[yii\web\User::can()]], que requiere habilitar RBAC 
   (a ser descrito en la próxima subsección). Si la opción está vacía o no definida, significa que la regla se aplica a todos los roles.

 * [[yii\filters\AccessRule::ips|ips]]: especifica con qué [[yii\web\Request::userIP|dirección IP del cliente]] coincide esta regla.
Una dirección IP puede contener el caracter especial `*` al final de manera que coincidan todas las IPs que comiencen igual.
Por ejemplo, '192.168.*' coincide con las direcciones IP en el segmento '192.168.'. Si la opción está vacía o no definida,
significa que la regla se aplica a todas las direcciones IP.

 * [[yii\filters\AccessRule::verbs|verbs]]: especifica con qué método de la solicitud (por ej. `GET`, `POST`) coincide esta regla.
La comparación no distingue minúsculas de mayúsculas.

 * [[yii\filters\AccessRule::matchCallback|matchCallback]]: especifica una función PHP invocable que debe ser llamada para determinar
si la regla debe ser aplicada.

 * [[yii\filters\AccessRule::denyCallback|denyCallback]]: especifica una función PHP invocable que debe ser llamada cuando esta regla
deniegue el acceso.

Debajo hay un ejemplo que muestra cómo utilizar la opción `matchCallback`, que te permite escribir lógica de comprabación de acceso
arbitraria:

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

    // Callback coincidente llamado! Esta página sólo puede ser accedida cada 31 de Octubre
    public function actionSpecialCallback()
    {
        return $this->render('happy-halloween');
    }
}
```


## Control de Acceso Basado en Roles (RBAC) <span id="rbac"></span>

El Control de Acceso Basado en Roles (RBAC) provee una simple pero poderosa manera centralizada de control de acceso. Por favos consulta
la [Wikipedia](http://en.wikipedia.org/wiki/Role-based_access_control) para más detalles sobre comparar RBAC
con otros mecanismos de control de acceso más tradicionales.

Yii implementa una Jerarquía General RBAC, siguiendo el [modelo NIST RBAC](http://csrc.nist.gov/rbac/sandhu-ferraiolo-kuhn-00.pdf).
Esto provee la funcionalidad RBAC a través de [componente de la aplicación](structure-application-components.md) [[yii\rbac\ManagerInterface|authManager]].

Utilizar RBAC envuelve dos cosas. La primera es construir los datos de autorización RBAC, y la segunda
es utilizar esos datos de autorización para comprobar el acceso en los lugares donde se necesite.

Para facilitar la próxima descripción, necesitamos primero instroducir algunos conceptos RBAC básicos.


### Conceptos Básicos <span id="basic-concepts"></span>

Un rol representa una colección de *permisos* (por ej. crear posts, actualizar posts). Un rol puede ser asignado
a uno o varios usuarios. Para comprobar que un usuario cuenta con determinado permiso, podemos comprobar si el usuario tiene asignado
un rol que cuente con dicho permiso.

Asociado a cada rol o permiso, puede puede haber una *regla*. Una regla representa una porción de código que será
ejecutada durante la comprobación de acceso para determinar si el rol o permiso correspondiente aplica al usuario actual.
Por ejemplo, el permiso "actualizar post" puede tener una regla que compruebe que el usuario actual es el autor del post.
Durante la comprobación de acceso, si el usuario NO es el autor del post, se considerará que el/ella no cuenta con el permiso "actualizar post".

Tanto los roles como los permisos pueden ser organizados en una jerarquía. En particular, un rol puede consistir en otros roles o permisos;
y un permiso puede consistir en otros permisos. Yii implementa una jerarquía de *orden parcial*, que incluye
una jerarquía de *árbol* especial. Mientras que un rol puede contener un permiso, esto no sucede al revés.


### Configurar RBAC <span id="configuring-rbac"></span>

Antes de definir todos los datos de autorización y ejecutar la comprobación de acceso, necesitamos configurar el
componente de la aplicación [[yii\base\Application::authManager|authManager]]. Yii provee dos tipos de administradores de autorización:
[[yii\rbac\PhpManager]] y [[yii\rbac\DbManager]]. El primero utiliza un archivo PHP para almacenar los datos
de autorización, mientras que el segundo almacena dichos datos en una base de datos. Puedes considerar utilizar el primero si tu aplicación
no requiere una administración de permisos y roles muy dinámica.


#### Utilizar `PhpManager` <span id="using-php-manager"></span>

El siguiente código muestra cómo configurar `authManager` en la configuración de nuestra aplicación utilizando la clase [[yii\rbac\PhpManager]]:

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

El `authManager` ahora puede ser accedido vía `\Yii::$app->authManager`.

Por defecto, [[yii\rbac\PhpManager]] almacena datos RBAC en archivos bajo el directorio `@app/rbac`. Asegúrate de que el directorio
y todos sus archivos son tienen permiso de escritura para el proceso del servidor Web si la jerarquía de permisos necesita ser modoficada en línea.


#### Utilizar `DbManager` <span id="using-db-manager"></span>

The following code shows how to configure the `authManager` in the application configuration using the [[yii\rbac\DbManager]] class:

```php
return [
    // ...
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        // ...
    ],
];
```
> Note: If you are using yii2-basic-app template, there is a `config/console.php` configuration file where the
  `authManager` needs to be declared additionally to `config/web.php`.
> In case of yii2-advanced-app the `authManager` should be declared only once in `common/config/main.php`.

`DbManager` uses four database tables to store its data: 

- [[yii\rbac\DbManager::$itemTable|itemTable]]: the table for storing authorization items. Defaults to "auth_item".
- [[yii\rbac\DbManager::$itemChildTable|itemChildTable]]: the table for storing authorization item hierarchy. Defaults to "auth_item_child".
- [[yii\rbac\DbManager::$assignmentTable|assignmentTable]]: the table for storing authorization item assignments. Defaults to "auth_assignment".
- [[yii\rbac\DbManager::$ruleTable|ruleTable]]: the table for storing rules. Defaults to "auth_rule".

Before you can go on you need to create those tables in the database. To do this, you can use the migration stored in `@yii/rbac/migrations`:

`yii migrate --migrationPath=@yii/rbac/migrations`

The `authManager` can now be accessed via `\Yii::$app->authManager`.


### Building Authorization Data <span id="generating-rbac-data"></span>

Building authorization data is all about the following tasks:

- defining roles and permissions;
- establishing relations among roles and permissions;
- defining rules;
- associating rules with roles and permissions;
- assigning roles to users.

Depending on authorization flexibility requirements the tasks above could be done in different ways.

If your permissions hierarchy doesn't change at all and you have a fixed number of users you can create a
[console command](tutorial-console.md#create-command) that will initialize authorization data once via APIs offered by `authManager`:

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

> Note: If you are using advanced template, you need to put your `RbacController` inside `console/controllers` directory
  and change namespace to `console/controllers`.

After executing the command with `yii rbac/init` we'll get the following hierarchy:

![Simple RBAC hierarchy](images/rbac-hierarchy-1.png "Simple RBAC hierarchy")

Author can create post, admin can update post and do everything author can.

If your application allows user signup you need to assign roles to these new users once. For example, in order for all
signed up users to become authors in your advanced project template you need to modify `frontend\models\SignupForm::signup()`
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


### Using Rules <span id="using-rules"></span>

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

Now we have got the following hierarchy:

![RBAC hierarchy with a rule](images/rbac-hierarchy-2.png "RBAC hierarchy with a rule")


### Access Check <span id="access-check"></span>

With the authorization data ready, access check is as simple as a call to the [[yii\rbac\ManagerInterface::checkAccess()]]
method. Because most access check is about the current user, for convenience Yii provides a shortcut method
[[yii\web\User::can()]], which can be used like the following:

```php
if (\Yii::$app->user->can('createPost')) {
    // create post
}
```

If the current user is Jane with `ID=1` we are starting at `createPost` and trying to get to `Jane`:

![Access check](images/rbac-access-check-1.png "Access check")

In order to check if a user can update a post, we need to pass an extra parameter that is required by `AuthorRule` described before:

```php
if (\Yii::$app->user->can('updatePost', ['post' => $post])) {
    // update post
}
```

Here is what happens if the current user is John:


![Access check](images/rbac-access-check-2.png "Access check")

We are starting with the `updatePost` and going through `updateOwnPost`. In order to pass the access check, `AuthorRule` 
should return `true` from its `execute()` method. The method receives its `$params` from the `can()` method call so the value is
`['post' => $post]`. If everything is fine, we will get to `author` which is assigned to John.

In case of Jane it is a bit simpler since she is an admin:

![Access check](images/rbac-access-check-3.png "Access check")


### Using Default Roles <span id="using-default-roles"></span>

A default role is a role that is *implicitly* assigned to *all* users. The call to [[yii\rbac\ManagerInterface::assign()]]
is not needed, and the authorization data does not contain its assignment information.

A default role is usually associated with a rule which determines if the role applies to the user being checked.

Default roles are often used in applications which already have some sort of role assignment. For example, an application
may have a "group" column in its user table to represent which privilege group each user belongs to.
If each privilege group can be mapped to a RBAC role, you can use the default role feature to automatically
assign each user to a RBAC role. Let's use an example to show how this can be done.

Assume in the user table, you have a `group` column which uses 1 to represent the administrator group and 2 the author group.
You plan to have two RBAC roles `admin` and `author` to represent the permissions for these two groups, respectively.
You can set up the RBAC data as follows,


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
