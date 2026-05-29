# Standalone Actions

> Available since version 22.0.

A standalone action is a [[yii\base\Action]] subclass that handles a route directly, without a hosting controller. The
feature is **purely additive**: existing controllers, `actions()`, `controllerMap`, filters, and components keep working
unchanged. You opt in only where you want it.

Two optional mechanisms dispatch standalone actions, mirroring the two Yii has always offered for controllers:

- [[yii\base\Module::$actionNamespace]] — convention-based discovery, parallel to
  [[yii\base\Module::$controllerNamespace]]. Defaults to `$controllerNamespace`, so by default actions live under the
  same root as controllers. **This is the mechanism you will use most.**
- [[yii\base\Module::$actionMap]] — explicit registration, parallel to [[yii\base\Module::$controllerMap]]. For
  endpoints that do not follow the convention (custom IDs, third-party classes).

## Quick start

Drop a class named `<X>Action` (extending [[yii\web\Action]] for HTTP, not [[yii\base\Controller]]) in the right folder
and a route resolves to it. With the default configuration the folder is `app/controllers/`:

```php
// app/controllers/HealthAction.php  ->  route: health
namespace app\controllers;

use yii\db\Connection;
use yii\web\Action;
use yii\web\Response;

final class HealthAction extends Action
{
    public function run(Connection $db, Response $response): Response
    {
        $ok = true;

        try {
            $db->createCommand('SELECT 1')->queryScalar();
        } catch (\Throwable) {
            $ok = false;
        }

        $response->format = Response::FORMAT_JSON;
        $response->statusCode = $ok ? 200 : 503;
        $response->data = ['status' => $ok ? 'ok' : 'degraded'];

        return $response;
    }
}
```

A request whose route resolves to `health` runs `HealthAction::run()` directly. No controller is instantiated, no
`actionXxx` method is called, and `Yii::$app->controller` stays `null`. The typed parameters of `run()`
(`Connection $db`, `Response $response`) are resolved by the DI container. Filters in the action's `behaviors()`
(such as [[yii\filters\AccessControl]] or [[yii\filters\VerbFilter]]) participate in the lifecycle exactly as on a
controller.

## How routes resolve to classes

The convention is the same as for controllers, with two differences: the class suffix is `Action` (not `Controller`)
and the parent is [[yii\base\Action]] / [[yii\web\Action]].

**Rule:** the **last** route segment becomes the class name (`<Camel>Action`); every **preceding** segment becomes a
sub-namespace (folder), appended verbatim to `$actionNamespace`.

With `actionNamespace` set to `app\usecase`:

| Route               | Resolves to                          | File                                 |
| ------------------- | ------------------------------------ | ------------------------------------ |
| `health`            | `app\usecase\HealthAction`           | `usecase/HealthAction.php`           |
| `post/index`        | `app\usecase\post\IndexAction`       | `usecase/post/IndexAction.php`       |
| `post/view-summary` | `app\usecase\post\ViewSummaryAction` | `usecase/post/ViewSummaryAction.php` |
| `admin/posts/view`  | `app\usecase\admin\posts\ViewAction` | `usecase/admin/posts/ViewAction.php` |

### Limits to keep in mind

- **Hyphens only in the last segment.** `view-summary` → `ViewSummaryAction`, but a hyphen in a preceding (folder)
  segment is rejected: `user-admin/login` does not resolve.
- **The last segment must start with a lowercase letter** (`[a-z]`). Folder segments are matched verbatim, so keep them
  lowercase to match conventional routes.
- **The first segment must not collide with a registered sub-module id.** If a sub-module with that id exists,
  resolution recurses into it (using _its_ `actionNamespace`) instead of looking under the current one.
- **The class must extend [[yii\base\Action]] (or [[yii\web\Action]]) and must not extend [[yii\base\Controller]].** A
  `<X>Action` class that extends `Controller` is skipped.
- Empty segments (`//`) never resolve.

## Choosing the base class

| Base class          | Use for                  | Parameter handling                                                                                           |
| ------------------- | ------------------------ | ------------------------------------------------------------------------------------------------------------ |
| [[yii\web\Action]]  | HTTP endpoints           | Scalar coercion (`'7'` → `int 7`), [[yii\web\BadRequestHttpException]] on bad/missing params, HTTP-aware DI. |
| [[yii\base\Action]] | Non-HTTP (jobs, console) | DI via [[\yii\di\Container::resolveCallableDependencies()]], no scalar coercion.                             |

Every HTTP example below extends `yii\web\Action`. Reserve `yii\base\Action` for actions that should not pay
HTTP-specific costs nor surface `BadRequestHttpException` on bad scalars. This tutorial is web-focused; console
standalone dispatch also runs through `Module::runAction()`, but is out of scope here.

## Dependency injection

Standalone actions support DI in **two places**, and both coexist:

- **Constructor** — typed parameters on `__construct()` are resolved once, before the action runs. Use for long-lived
  collaborators (DB, mailer, repositories) and anything you need from `behaviors()` or `init()`.
- **`run()`** — typed parameters are resolved per invocation. Use for request-scoped artifacts ([[yii\web\Request]],
  [[yii\web\Response]], [[yii\web\User]]) and route scalars (`int $id`, `string $slug`).

```php
final class HealthAction extends Action
{
    public function __construct(private readonly Connection $db) {}

    public function run(Response $response): Response
    {
        $response->format = Response::FORMAT_JSON;
        $response->data = ['db' => $this->db->open() ? 'ok' : 'down'];

        return $response;
    }
}
```

For a `run()` parameter, the binder resolves typed non-builtin parameters in this order: a **module component** named
like the parameter, then a **module DI definition** by type, then the **global container** by type, then `null` if the
parameter is nullable, otherwise [[yii\web\ServerErrorHttpException]]. Route scalars on a [[yii\web\Action]] are coerced
and validated (`?id=abc` for `int $id` raises [[yii\web\BadRequestHttpException]], not a `TypeError`).

> **Advanced — `init()` and the action id.** The dispatcher calls `Yii::createObject()` with no positional arguments and
> assigns [[yii\base\Action::$id]] _after_ construction. Modern actions need not call `parent::__construct()`. Call it
> explicitly only if you rely on [[yii\base\Action::init()]] (for example, attaching behaviors there) — but note that
> during `init()` the id is still `null`. Logic that depends on the id belongs in `behaviors()` event handlers, which
> fire on `EVENT_BEFORE_ACTION` after the id is assigned. Controllers and `actions()`-registered actions keep the
> historical `__construct($id, $controller, $config)` contract; the DI-friendly path applies only to standalone actions.

## Organizing actions

By default `actionNamespace` equals `controllerNamespace`, so actions sit next to controllers. For a separate root
(vertical slices, use cases, ports/adapters), point `actionNamespace` elsewhere:

```php
// config/web.php
'actionNamespace' => 'app\\usecase',
```

Controllers stay at `app/controllers/`; actions resolve under `app/usecase/`. The idiomatic layout groups actions by
feature, with the action class **directly** in the feature folder:

```text
usecase/post/IndexAction.php    ->  route: post/index
usecase/post/CreateAction.php   ->  route: post/create
```

### A directory per action without a redundant route

If you prefer one folder per action (`usecase/user/login/LoginAction.php`), the convention route becomes
`user/login/login` — the folder `login/` and the class `LoginAction` both encode "login". The convention cannot avoid
this, but three configuration-only approaches give a clean route without writing code:

- **`actionMap`** — map a flat id to the class wherever it lives:
  `'actionMap' => ['login' => app\usecase\user\login\LoginAction::class]` → route `login`.
- **`urlManager` rules** — keep the internal route, expose a clean public URL:
  `'user/login' => 'user/login/login'`.
- **Sub-modules + a uniform class name** — register `user` and `login` as plain [[yii\base\Module]] instances with
  `actionNamespace`, and name each slice's action `DefaultAction` (the module default route). Then `user/login`
  resolves natively to `usecase/user/login/DefaultAction.php`, no alias.

> **Note:** `actionMap` is consulted only by the module whose `runAction()` handles the route (typically the
> application). Unlike `controllerMap`, it is not resolved recursively across sub-modules. `actionNamespace` discovery
> _is_ recursive, so for sub-module standalone actions prefer the convention.

## A complete CRUD example

A `posts` resource organized as a vertical slice under `app/usecase`. The same resource could be one `PostController`
with six `actionXxx` methods; the patterns coexist.

```text
app/usecase/post/
    IndexAction.php  ViewAction.php  CreateAction.php
    UpdateAction.php DeleteAction.php SearchAction.php
    PostForm.php
    views/index.php  views/view.php  views/form.php
app/models/Post.php
config/web.php
```

### Migration

```php
use yii\db\Migration;

final class m250101_000000_create_post_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable(
            '{{%post}}',
            [
                'id' => $this->primaryKey(),
                'title' => $this->string(180)->notNull(),
                'body' => $this->text()->notNull(),
                'status' => $this->string(20)->notNull()->defaultValue('draft'),
                'author_id' => $this->integer()->notNull(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ],
        );
        $this->createIndex('idx-post-status', '{{%post}}', 'status');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%post}}');
    }
}
```

### Model and form

`app/models/Post.php` is a standard ActiveRecord (`tableName()`, `rules()`, `attributeLabels()`, `TimestampBehavior`).
`app/usecase/post/PostForm.php` is a form-only [[yii\base\Model]] used by Create and Update, keeping the input contract
independent of the table schema:

```php
namespace app\usecase\post;

use yii\base\Model;

final class PostForm extends Model
{
    public string $title = '';
    public string $body = '';
    public string $status = 'draft';

    public function rules(): array
    {
        return [
            [['title', 'body'], 'required'],
            [['title'], 'string', 'max' => 180],
            [['status'], 'in', 'range' => ['draft', 'published', 'archived']],
        ];
    }
}
```

### The actions

`IndexAction` — list with pagination. `$this->getModule()` returns the owning module when the action runs standalone
(it falls back to the controller's module when hosted by a controller):

```php
namespace app\usecase\post;

use app\models\Post;
use yii\data\ActiveDataProvider;
use yii\web\Action;

final class IndexAction extends Action
{
    public function run(): string
    {
        $provider = new ActiveDataProvider(
            [
                'query' => Post::find()->orderBy(['created_at' => SORT_DESC]),
                'pagination' => ['pageSize' => 20],
            ],
        );

        return $this->getModule()->view->render('@app/usecase/post/views/index', ['provider' => $provider], $this);
    }
}
```

There is no `Controller::render()` here, so `$module->view->render()` returns the **bare view, without a layout**. Apply
one explicitly (render the content, then `renderFile()` the layout with `['content' => $content]`) or hide it behind a
small injected renderer service. Use absolute view paths (`@app/...`): an action is not a
[[yii\base\ViewContextInterface]], so relative names will not resolve.

`ViewAction` — the `int $id` is filled from the route and coerced; `?id=abc` raises
[[yii\web\BadRequestHttpException]]:

```php
namespace app\usecase\post;

use app\models\Post;
use yii\web\Action;
use yii\web\NotFoundHttpException;

final class ViewAction extends Action
{
    public function run(int $id): string
    {
        $post = Post::findOne($id) ?? throw new NotFoundHttpException('Post not found.');

        return $this->getModule()->view->render('@app/usecase/post/views/view', ['post' => $post], $this);
    }
}
```

`CreateAction` — POST with form validation. Filters are declared on the action itself:

```php
namespace app\usecase\post;

use app\models\Post;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Action;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

final class CreateAction extends Action
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST']],
            ],
        ];
    }

    public function run(Request $request, Response $response, User $user, PostForm $form): Response|string
    {
        if (!$form->load($request->post()) || !$form->validate()) {
            $response->statusCode = 422;

            return $this->getModule()->view->render('@app/usecase/post/views/form', ['form' => $form], $this);
        }

        $post = new Post();

        $post->setAttributes($form->getAttributes());
        $post->author_id = (int) $user->getId();
        $post->save();

        Yii::$app->session->setFlash('success', 'Post created.');

        return $response->redirect(['/post/view', 'id' => $post->id]);
    }
}
```

`AccessControl` and `VerbFilter` attach to the action's `EVENT_BEFORE_ACTION` and run before `run()`. `Request`,
`Response`, and `User` are application components (resolved by name); `PostForm` is autowired by class. Validation runs
on the form, not the ActiveRecord. Because no controller runs, add a CSRF filter to `behaviors()` for this POST route
(see [CSRF protection](#csrf-protection)).

**`UpdateAction`, `DeleteAction`, `SearchAction`** follow the same pattern. Update is Create with a leading lookup
(`Post::findOne($id) ?? throw …`) guarded by `$request->isPut || $request->isPatch`. Delete only needs a `VerbFilter`
(`['delete' => ['DELETE']]`) plus an `AccessControl` rule. Search is read-only and accepts `string $q` plus pagination
parameters.

### CSRF protection

A standalone action bypasses [[yii\web\Controller::beforeAction()]], where Yii validates the CSRF token, so **CSRF is
not checked automatically**. APIs and webhooks benefit (no token to manage); state-changing form routes
(`POST`/`PUT`/`DELETE`) restore it with a small filter attached through `behaviors()`:

```php
final class CsrfFilter extends \yii\base\ActionFilter
{
    public function beforeAction($action): bool
    {
        if (!\Yii::$app->request->validateCsrfToken()) {
            throw new \yii\web\BadRequestHttpException('Unable to verify your data submission.');
        }

        return true;
    }
}
```

`validateCsrfToken()` is a no-op for `GET`/`HEAD`/`OPTIONS`, so the filter only enforces on unsafe methods. Add
`'csrf' => CsrfFilter::class` to the action's `behaviors()` — a trait is handy when several slices share the same guard.

### URL configuration

[[yii\web\UrlManager]] is unchanged; point clean URLs at the route strings. The whole slice needs only the namespace
pointer plus the rules:

```php
return [
    // ...
    'actionNamespace' => 'app\\usecase',
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'rules' => [
            'GET,HEAD posts'           => 'post/index',
            'GET posts/search'         => 'post/search',
            'POST posts'               => 'post/create',
            'GET posts/<id:\d+>'       => 'post/view',
            'PUT,PATCH posts/<id:\d+>' => 'post/update',
            'DELETE posts/<id:\d+>'    => 'post/delete',
        ],
    ],
];
```

There is no per-action registration: adding an action is dropping a file at the right path. For a one-off custom id or
namespace, add an `actionMap` entry (`'webhooks-stripe' => app\Integrations\Stripe\WebhookHandler::class`); explicit
entries take precedence over the convention.

## Coexistence and routing order

The same application can mix controllers (in `app/controllers/`) and standalone actions (wherever `actionNamespace`
points). `Module::runAction()` resolves a route deterministically:

1. **`actionMap`** (matched on the first segment, at the dispatching module).
2. **`controllerMap`**.
3. **Sub-modules** (matched on the first segment, recursively).
4. **Controller by namespace** — `<X>Controller` with a matching `actionXxx` method.
5. **Standalone action by namespace** — `<X>Action` under `actionNamespace`.

If a controller method **and** a standalone Action class both match the same route, `runAction()` throws
`InvalidConfigException` so you disambiguate explicitly. Existing applications upgrade with no behavioral change,
because before 22.0 a class file at the convention path was never resolved. You can adopt the pattern feature by
feature; there is no global switch.

## URL generation inside standalone actions

[[yii\data\Sort::createUrl()]] and [[yii\data\Pagination::createUrl()]] resolve the target route from `$route`, then the
active controller's `getRoute()`, then [[yii\base\Application::$requestedRoute]] (the 22.0 fallback). The third step is
what makes [[yii\widgets\GridView]] and [[yii\widgets\ListView]] work transparently inside a standalone action — no
`route` override needed. [[yii\helpers\Url::current()]] and [[yii\helpers\Url::canonical()]] use the same fallback. When
none of the three is available (calling outside a request lifecycle) the call throws `InvalidConfigException`.

For links and forms, prefer **absolute** routes (leading `/`), which resolve from the application root without
consulting any controller:

```php
<a href="<?= Url::to(['/post/view', 'id' => $post->id]) ?>">View</a>

<?= Html::beginForm(['/post/delete', 'id' => $post->id], 'post') ?>
```

A **relative** route (no leading slash, e.g. `['view']`) resolves against the current controller's module and throws
[[yii\base\InvalidArgumentException]] when no controller is active — "relative" has no meaning without a controller.

## Frequently asked questions

**Where do shared concerns live without a controller?** In three places, all pre-existing: _application-wide_
components (`db`, `session`, `mailer`, `urlManager`, …) read via typed `run()` parameters or `Yii::$app`; _per route_
in the action's own `behaviors()` (`AccessControl`, `VerbFilter`, `ContentNegotiator`, …); and _per module_ via
`EVENT_BEFORE_ACTION` / `EVENT_AFTER_ACTION`, which still fire on every ancestor module (auditing, feature flags,
request logging).

**Should I migrate every controller?** No. Standalone actions pay off for endpoints with their own dependencies and
behaviors — webhooks, health checks, integrations, single-purpose use cases, vertical slices. For CRUD where several
methods share filters, layout, and helper state, a controller stays the simpler, standard choice.

**What about REST resources and existing `Action` subclasses?** [[yii\rest\ActiveController]] and the REST action set
([[yii\rest\IndexAction]], …) keep working through `Controller::actions()` exactly as before; they are controller-based
by design because they share filters and serializer state. Standalone actions are complementary, not a replacement.

**How do I test a standalone action?** Build it the way the dispatcher does, then call `runWithParams([...])` and assert
on the result. A plain action needs only `new YourAction('id', null)`; a **constructor-injected** one must be created
through DI — register stubs in `Yii::$container`, build it with `Yii::createObject(YourAction::class)` (no positional
args), then assign `$action->id`. See `tests/framework/base/StandaloneActionTest.php` and
`tests/framework/base/ModuleActionNamespaceTest.php` for working examples.

## Things to keep in mind

- `yii\base\Action::$controller` is `null` for standalone actions. Classes that read `$this->controller` directly are
  not standalone-compatible (documented on [[yii\web\ViewAction]], [[yii\web\ErrorAction]], [[yii\base\InlineAction]]).
- `Yii::$app->controller` is **not** mutated when a standalone action runs; code reading it mid-request sees whatever
  was set previously (typically `null`).
- `yii\filters\AccessRule::matchController()` accepts `null`. A rule with a non-empty `controllers` constraint does not
  match a standalone action — leave `controllers` empty when the rule should apply to standalone actions.
- **CSRF is not validated automatically** — it lives in [[yii\web\Controller::beforeAction()]], which standalone
  actions skip. Attach a CSRF filter for state-changing routes (see [CSRF protection](#csrf-protection)). Session and
  authentication are application-level and unaffected.
