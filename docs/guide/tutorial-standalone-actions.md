# Standalone Actions

> Available since version 22.0.

This tutorial introduces the standalone-action support added in Yii 22.0. A standalone action is a [[yii\base\Action]]
subclass that handles a route directly, without a hosting controller. There are two mechanisms both optional, both
additive and they mirror the two mechanisms Yii has always offered for controllers:

- [[yii\base\Module::$actionNamespace]] — convention-based discovery, parallel to
  [[yii\base\Module::$controllerNamespace]]. Defaults to the value of `$controllerNamespace`, so by default
  standalone actions live under the same root as controllers.
- [[yii\base\Module::$actionMap]] — explicit registration, parallel to [[yii\base\Module::$controllerMap]]. Use it for
  endpoints that do not follow the convention.

The tutorial walks through both, plus a complete CRUD example backed by ActiveRecord.

## The traditional controller approach is unchanged

Everything you already know keeps working in 22.0:

- `app\controllers\PostController` extending [[yii\web\Controller]] with `actionIndex()`, `actionCreate()`,
  `actionUpdate()`, `actionDelete()` methods.
- `Controller::actions()` registering reusable [[yii\base\Action]] subclasses such as [[yii\rest\IndexAction]],
  [[yii\web\ErrorAction]], or your own.
- `Module::$controllerMap` and namespace-based controller discovery.
- All filters, behaviors, and CSRF/auth components attached at the controller level.

The new feature is purely additive. Existing applications upgrade without code changes; you only opt in where you want
the new pattern.

## What 22.0 adds

The headline mechanism is **convention**. Drop a class named `<X>Action` (extending [[yii\base\Action]], not
[[yii\base\Controller]]) in the right folder, and a route resolves to it. Route segments map to sub-namespaces just as
they do for controllers; the only differences are the class suffix (`Action` instead of `Controller`) and the parent
class.

Default file placement (no configuration required, because `$actionNamespace` defaults to `$controllerNamespace`):

```text
app/controllers/PostController.php           (existing controller, unchanged)
app/controllers/post/IndexAction.php         (route post/index)
app/controllers/post/ViewAction.php          (route post/view)
app/controllers/admin/posts/CreateAction.php (route admin/posts/create)
```

The hyphen-to-CamelCase rule applies to the last route segment (`view-summary` resolves to `ViewSummaryAction`);
preceding segments form a sub-namespace prefix verbatim.

### Choosing the right base class

Yii ships two action base classes for standalone dispatch:

- **[[yii\web\Action]]** — for HTTP endpoints. Reuses [[yii\web\Controller::bindActionParams()]] semantics: scalar
  coercion (`'7'` → `int 7`), [[yii\web\BadRequestHttpException]] on type mismatches and missing required
  parameters, and the same DI resolution as web controllers (module components, module DI, container).
- **[[yii\base\Action]]** — for non-HTTP contexts (queue jobs, scheduled tasks, console-side dispatch). Resolves
  typed parameters through [[\yii\di\Container::resolveCallableDependencies()]] without HTTP-specific filtering.

For every example below that handles an HTTP request, the action extends `yii\web\Action`. Reserve `yii\base\Action`
for actions that should not pay HTTP-specific costs nor surface `BadRequestHttpException` on bad scalars.

A realistic example is a health-check endpoint that verifies database connectivity. With the default configuration, drop
the file at `app/controllers/HealthAction.php`:

```php
namespace app\controllers;

use yii\db\Connection;
use yii\web\Action;
use yii\web\Response;

final class HealthAction extends Action
{
    public function run(Connection $db, Response $response): Response
    {
        $checks = ['database' => 'unknown'];
        $statusCode = 200;

        try {
            $db->createCommand('SELECT 1')->queryScalar();

            $checks['database'] = 'ok';
        } catch (\Throwable $e) {
            $checks['database'] = 'failed';
            $statusCode = 503;
        }

        $response->format = Response::FORMAT_JSON;

        $response->statusCode = $statusCode;

        $response->data = [
            'status' => $statusCode === 200 ? 'ok' : 'degraded',
            'checks' => $checks,
        ];

        return $response;
    }
}
```

A request whose route resolves to `health` runs `HealthAction::run()` directly. No controller class is instantiated, no
`actionXxx` method is called, and `Yii::$app->controller` is not set. The action's dependencies (`Connection $db`,
`Response $response`) are resolved by the DI container based on the typed parameters of `run()`. Filters declared in the
action's `behaviors()` (such as [[yii\filters\AccessControl]] or [[yii\filters\VerbFilter]]) participate in the lifecycle
the same way they do on a controller.

For projects that prefer a separate root for standalone actions (vertical slices, use cases, ports/adapters layout), set
`actionNamespace` to the desired namespace:

```php
// config/web.php
return [
    // ...
    'actionNamespace' => 'app\\UseCase',
];
```

With the line above, controllers continue to live at `app/controllers/PostController.php` (untouched), and standalone
actions live at `app/UseCase/post/IndexAction.php`, `app/UseCase/admin/posts/CreateAction.php`, etc. Routes resolve
under `actionNamespace` instead of `controllerNamespace`.

When a route does not follow the convention (custom ID, third-party class, two slices that need disambiguation), use
[[yii\base\Module::$actionMap]] for explicit registration:

```php
'actionMap' => [
    'health' => app\actions\HealthCheckAction::class,
],
```

`actionMap` is parallel to `controllerMap` and takes precedence over both `controllerMap` and the convention. It is
rarely needed in practice; the convention covers most cases.

## When to use which

Use the **traditional controller approach** when:

- Several actions naturally share state, filters, or layout (typical CRUD on a single resource where the controller's
  `behaviors()` cover all actions).
- You rely on `view` rendering helpers from [[yii\web\Controller::render()]].
- You are extending an existing controller-based code base and consistency matters more than slicing.

Use **standalone actions** (convention or `actionMap`) when:

- Each endpoint has its own dependencies, behaviors, or filters and you want them owned by the action.
- You organize an application as vertical slices or use cases, with each slice in its own folder.
- The endpoint is single-purpose: webhooks, health checks, integrations, one-shot operations.

Both styles can coexist. A controller can have most of its `actionXxx` methods, and a standalone `<X>Action.php` file
can fill in a specific action that the controller does **not** implement. If both a controller method and a standalone
Action class match the same route, `Module::runAction()` throws `InvalidConfigException` so the developer
disambiguates instead of silently shadowing one or the other.

## A complete CRUD example

The rest of this tutorial builds a `posts` resource with six endpoints organized as a vertical slice under `app/UseCase`.
The same example could be built with one `PostController` and six `actionXxx` methods; the patterns coexist.

### Folder layout

```text
app/
    UseCase/
        post/
            IndexAction.php
            ViewAction.php
            CreateAction.php
            UpdateAction.php
            DeleteAction.php
            SearchAction.php
            PostForm.php
            views/
                index.php
                view.php
                form.php
    models/
        Post.php
    migrations/
        m250101_000000_create_post_table.php
config/
    web.php
```

The folder name `post` (lowercase) is the route segment. Each `*Action.php` file is one HTTP endpoint.
The form model and views live next to the actions because they are part of the same slice.

### 1. Migration

Migrations are unchanged. Use `safeUp()` and `safeDown()` per Yii conventions:

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
        $this->createIndex('idx-post-author_id', '{{%post}}', 'author_id');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%post}}');
    }
}
```

### 2. ActiveRecord model

`app/models/Post.php` follows standard Yii conventions:

```php
namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

final class Post extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%post}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'body', 'author_id'], 'required'],
            [['title'], 'string', 'max' => 180],
            [['body'], 'string'],
            [['status'], 'in', 'range' => ['draft', 'published', 'archived']],
            [['author_id'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'body' => 'Body',
            'status' => 'Status',
            'author_id' => 'Author',
        ];
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }
}
```

### 3. Form model (input validation)

`app/UseCase/post/PostForm.php` is a form-only model used by Create and Update. Keeping it separate from the
ActiveRecord lets you change the table without touching the input contract:

```php
namespace app\UseCase\post;

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
            [['body'], 'string'],
            [['status'], 'in', 'range' => ['draft', 'published', 'archived']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'Title',
            'body' => 'Body',
            'status' => 'Status',
        ];
    }
}
```

### 4. The actions

Each action extends [[yii\web\Action]] (HTTP endpoints) and owns its `run()` method. Typed parameters are coerced and
validated through the same binder as web controllers.

#### `IndexAction` — list with pagination

`app/UseCase/post/IndexAction.php`:

```php
namespace app\UseCase\post;

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

        return $this->getModule()->view->render(
            '@app/UseCase/post/views/index',
            ['provider' => $provider],
            $this,
        );
    }
}
```

`$this->getModule()` returns the owning module when the action runs standalone (it falls back to the controller's module
when the action is hosted by a controller). Rendering goes through the module's view component; you can equally inject
[[yii\web\Response]] and return JSON without touching views.

#### `ViewAction` — show one record

`app/UseCase/post/ViewAction.php`:

```php
namespace app\UseCase\post;

use app\models\Post;
use yii\web\Action;
use yii\web\NotFoundHttpException;

final class ViewAction extends Action
{
    public function run(int $id): string
    {
        $post = Post::findOne($id);

        if ($post === null) {
            throw new NotFoundHttpException('Post not found.');
        }

        return $this->getModule()->view->render(
            '@app/UseCase/post/views/view',
            ['post' => $post],
            $this,
        );
    }
}
```

The `int $id` parameter is filled from the route and coerced by [[yii\web\Action]]'s binder; passing `?id=abc`
raises [[yii\web\BadRequestHttpException]] (HTTP 400) instead of a `TypeError`. Typed services are resolved from
module components and the DI container.

#### `CreateAction` — POST with form validation

`app/UseCase/post/CreateAction.php`:

```php
namespace app\UseCase\post;

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

            return $this->getModule()->view->render(
                '@app/UseCase/post/views/form',
                ['form' => $form],
                $this,
            );
        }

        $post = new Post();

        $post->setAttributes($form->getAttributes());
        $post->author_id = (int) $user->getId();

        if (!$post->save()) {
            throw new \RuntimeException('Failed to persist post: ' . print_r($post->errors, true));
        }

        Yii::$app->session->setFlash('success', 'Post created.');

        return $response->redirect(['post/view', 'id' => $post->id]);
    }
}
```

Three things to notice:

1. `behaviors()` is declared on the action itself. `AccessControl` and `VerbFilter` attach to the action's
   `EVENT_BEFORE_ACTION` and run before `run()`.
2. `Request`, `Response`, `User`, and `PostForm` are typed parameters; the DI container resolves each of them.
   `Request`, `Response`, and `User` are application components (resolved by name), while `PostForm` is autowired by
   class.
3. The form is _separate_ from the ActiveRecord. Validation runs on `PostForm`, then attributes are copied to a `Post`
   instance. This keeps input validation independent of the persistence schema.

#### `UpdateAction`, `DeleteAction`, `SearchAction`

These follow the same pattern. Update is a copy of Create with a leading lookup:

```php
public function run(int $id, Request $request, Response $response, PostForm $form): Response|string
{
    $post = Post::findOne($id) ?? throw new NotFoundHttpException('Post not found.');

    $form->setAttributes($post->getAttributes());

    if ($request->isPut || $request->isPatch) {
        if ($form->load($request->post()) && $form->validate()) {
            $post->setAttributes($form->getAttributes());
            $post->save(false);

            return $response->redirect(
                [
                    'post/view',
                    'id' => $post->id,
                ],
            );
        }
    }

    return $this->getModule()->view->render(
        '@app/UseCase/post/views/form',
        ['form' => $form],
        $this,
    );
}
```

Delete is even smaller and only needs `VerbFilter` plus an `AccessControl` rule for the destroyer role.
Search is read-only and accepts `string $q` plus pagination parameters.

### 5. URL configuration

Yii's [[yii\web\UrlManager]] is unchanged; you just point clean URLs at the conventional route strings.
Verb-prefixed rules give you proper REST routing:

```php
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
```

The right-hand side of each rule is a route string. Yii resolves the route by following the standard order: `actionMap`,
`controllerMap`, sub-modules, controllers by namespace, then standalone actions by namespace. With the layout above,
`post/index` resolves to `app\UseCase\post\IndexAction` because `actionNamespace` is set to `app\UseCase`. Placeholders
like `<id:\d+>` are captured and passed to `run()` as named parameters.

### 6. The configuration

The whole vertical slice needs only one configuration line for routing to work — the namespace pointer:

```php
return [
    // ...
    'actionNamespace' => 'app\\UseCase',
    'urlManager' => [/* rules above */],
];
```

There is no per-action registration. Adding a new action is dropping the file at the right path; the next request finds
it.

If a single action needs a custom ID or different namespace from the rest, register it explicitly through `actionMap`:

```php
'actionMap' => [
    'webhooks-stripe' => app\Integrations\Stripe\WebhookHandler::class,
],
```

`actionMap` and `actionNamespace` coexist with no conflict; explicit entries take precedence.

### 7. Coexistence with controllers

The same application can declare controllers (in `app/controllers/`) and standalone actions (in `app/UseCase/` or
wherever `actionNamespace` points). Routing is deterministic:

1. `actionMap` is checked first.
2. `controllerMap` is checked next.
3. Sub-modules are matched on the first segment.
4. The controller pipeline tries to resolve a `<X>Controller` with a matching `actionXxx` method.
5. If no controller method matches, the standalone-action pipeline tries `<X>Action` under `actionNamespace`.

Because ambiguous matches now raise `InvalidConfigException`, existing applications can still adopt the pattern
feature by feature, but overlapping controller/standalone routes must be disambiguated explicitly. There is no global
switch.

## Scaling to N slices

The convention scales linearly because the resolver is route-driven, not slice-driven. Ten slices look like ten folders
under `actionNamespace`:

```text
app/UseCase/
    post/
        IndexAction.php
        ViewAction.php
        CreateAction.php
    user/
        IndexAction.php
        ProfileAction.php
        UpdateAction.php
    order/
        IndexAction.php
        ViewAction.php
        FulfillAction.php
    product/
        ...
    invoice/
        ...
    cart/
        ...
    checkout/
        ...
    notification/
        ...
    audit/
        ...
    integration/
        StripeWebhookAction.php
```

The configuration stays a single line: `'actionNamespace' => 'app\\UseCase'`. Each request performs a single class
lookup. Route `post/index` only tries `app\UseCase\post\IndexAction`; the resolver does not iterate over the other
slices, and adding the eleventh slice is dropping a folder. Performance is the same as a controller lookup of equivalent
depth.

If two slices need entirely different roots (rare), register one of them through a sub-module that has its own
`actionNamespace`. Sub-modules are an existing Yii2 mechanism; nothing about standalone actions changes how they work.

## Frequently asked questions

**"Is the request really running without any controller?"**

Yes, by design. `Module::runAction()` dispatches the action directly: it builds the [[yii\base\Action]] instance (either
from `actionMap`, the `actionNamespace` convention, or a user-provided class), fires module before-action events, fires
the action's own `EVENT_BEFORE_ACTION` (so behaviors like `AccessControl` and `VerbFilter` attach), invokes `run()`
with arguments resolved through the DI container, then fires the after-action events. No `Controller` subclass is
instantiated, no `actionXxx` method is called, and `Yii::$app->controller` is not mutated.

**"Where do shared concerns live without a controller?"**

In three places, all of which were already there:

- _Application-wide._ `Yii::$app->components` (session, db, mailer, log, urlManager, …) and bootstrap components are
  unchanged. They are read by typed parameters in the action's `run()` or pulled directly from `Yii::$app`.
- _Per route._ The action's own `behaviors()` declares filters (`AccessControl`, `VerbFilter`, `ContentNegotiator`, rate
  limiting, …). They attach to the action's `EVENT_BEFORE_ACTION` and run before `run()`.
- _Per module._ `EVENT_BEFORE_ACTION` and `EVENT_AFTER_ACTION` still fire on every ancestor module. Use these for
  cross-cutting policies like auditing, feature flags, or request logging.

**"Should we migrate every controller to standalone actions?"**

No. Standalone actions pay off when an endpoint has its own dependencies and behaviors that do not need to be shared
with siblings — webhooks, health checks, integrations, single-purpose use cases, vertical slices. For traditional CRUD
where four to six methods naturally share filters, layouts, and helper state, a controller remains the simpler choice
and the standard Yii pattern. Adopt the new pattern feature by feature where it pays off; leave the rest on controllers.

**"What about REST resources and existing `Action` subclasses?"**

[[yii\rest\ActiveController]] and the REST action set ([[yii\rest\IndexAction]], [[yii\rest\ViewAction]], …) keep
working through `Controller::actions()` exactly as before. They are controller-based by design because they share
filters and serializer state. Standalone actions are complementary, not a replacement.

**"How do I test a standalone action?"**

The same way you would test any class. Instantiate it with `new YourAction('id', null)`, call `runWithParams([...])`
with the route parameters, and assert against the result. The DI container resolves typed parameters; for unit tests,
register stubs in `Yii::$container` before invoking. See `tests/framework/base/StandaloneActionTest.php` and
`tests/framework/base/ModuleActionNamespaceTest.php` in the framework for working examples.

## Things to keep in mind

- `yii\base\Action::$controller` is `null` for standalone actions. Action classes that read `$this->controller` directly
  are not standalone-compatible. The framework documents this on [[yii\web\ViewAction]], [[yii\web\ErrorAction]], and
  [[yii\base\InlineAction]].
- `yii\filters\AccessRule::matchController()` accepts `null`. A rule with a non-empty `controllers` constraint does not
  match a standalone action. Leave `controllers` empty (or omit the constraint) when the rule should apply to standalone
  actions.
- `Yii::$app->controller` is **not** mutated when a standalone action runs. Code that reads `Yii::$app->controller`
  mid-request still sees whatever value was set previously (typically `null`).
- CSRF, session, and authentication components are application-level. They keep working unchanged; nothing about
  standalone actions disables them.
- When a controller method AND a standalone Action class both match the same route, `Module::runAction()` throws
  `InvalidConfigException` so the developer disambiguates explicitly. Existing applications upgrade with no behavioral
  change because before this release a class file at the convention path was never resolved.
