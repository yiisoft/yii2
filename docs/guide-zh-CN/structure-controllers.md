控制器
Controllers
===========

控制器是 [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 模式中的一部分，
是继承[[yii\base\Controller]]类的对象，负责处理请求和生成响应。
具体来说，控制器从[应用主体](structure-applications.md)接管控制后会分析请求数据并传送到[模型](structure-models.md)，
传送模型结果到[视图](structure-views.md)，最后生成输出响应信息。
Controllers are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are objects of classes extending from [[yii\base\Controller]] and are responsible for processing requests and
generating responses. In particular, after taking over the control from [applications](structure-applications.md),
controllers will analyze incoming request data, pass them to [models](structure-models.md), inject model results
into [views](structure-views.md), and finally generate outgoing responses.


## 操作 <a name="actions"></a>
## Actions <a name="actions"></a>

控制器由 *操作* 组成，它是执行终端用户请求的最基础的单元，一个控制器可有一个或多个操作。
Controllers are composed by *actions* which are the most basic units that end users can address and request for
execution. A controller can have one or multiple actions.

如下示例显示包含两个操作`view` and `create` 的控制器`post`：
The following example shows a `post` controller with two actions: `view` and `create`:

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

在操作 `view` (定义为 `actionView()` 方法)中， 代码首先根据请求模型ID加载 [模型](structure-models.md)，
如果加载成功，会渲染名称为`view`的[视图](structure-views.md)并显示，否则会抛出一个异常。
 the `view` action (defined by the `actionView()` method), the code first loads the [model](structure-models.md)
according to the requested model ID; If the model is loaded successfully, it will display it using
a [view](structure-views.md) named `view`. Otherwise, it will throw an exception.

在操作 `create` (定义为 `actionCreate()` 方法)中, 代码相似. 先将请求数据填入[模型](structure-models.md)，
然后保存模型，如果两者都成功，会跳转到ID为新创建的模型的`view`操作，否则显示提供用户输入的`create`视图。
In the `create` action (defined by the `actionCreate()` method), the code is similar. It first tries to populate
the [model](structure-models.md) using the request data and save the model. If both succeed it will redirect
the browser to the `view` action with the ID of the newly created model. Otherwise it will display
the `create` view through which users can provide the needed input.


## 路由 <a name="routes"></a>
## Routes <a name="routes"></a>

终端用户通过所谓的*路由*寻找到操作，路由是包含以下部分的字符串：End users address actions through the so-called *routes*. A route is a string that consists of the following parts:
End users address actions through the so-called *routes*. A route is a string that consists of the following parts:

* 模型ID: 仅存在于控制器属于非应用的[模块](structure-modules.md);
* 控制器ID: 同应用（或同模块如果为模块下的控制器）下唯一标识控制器的字符串;
* 操作ID: 同控制器下唯一标识操作的字符串。
* a module ID: this exists only if the controller belongs to a non-application [module](structure-modules.md);
* a controller ID: a string that uniquely identifies the controller among all controllers within the same application
  (or the same module if the controller belongs to a module);
* an action ID: a string that uniquely identifies the action among all actions within the same controller.

路由使用如下格式:
Routes take the following format:

```
ControllerID/ActionID
```

如果属于模块下的控制器，使用如下格式：
or the following format if the controller belongs to a module:

```php
ModuleID/ControllerID/ActionID
```

如果用户的请求地址为 `http://hostname/index.php?r=site/index`, 会执行`site` 控制器的`index` 操作。
更多关于处理路由的详情请参阅 [路由](runtime-routing.md) 一节。
So if a user requests with the URL `http://hostname/index.php?r=site/index`, the `index` action in the `site` controller
will be executed. For more details how routes are resolved into actions, please refer to
the [Routing](runtime-routing.md) section.


## 创建控制器 <a name="creating-controllers"></a>
## Creating Controllers <a name="creating-controllers"></a>

在[[yii\web\Application|Web applications]]网页应用中，控制器应继承[[yii\web\Controller]] 或它的子类。
同理在[[yii\console\Application|console applications]]控制台应用中，控制器继承[[yii\console\Controller]] 或它的子类。
如下代码定义一个 `site` 控制器:
In [[yii\web\Application|Web applications]], controllers should extend from [[yii\web\Controller]] or its
child classes. Similarly in [[yii\console\Application|console applications]], controllers should extend from
[[yii\console\Controller]] or its child classes. The following code defines a `site` controller:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### 控制器ID <a name="controller-ids"></a>
### Controller IDs <a name="controller-ids"></a>

通常情况下，控制器用来处理请求有关的资源类型，因此控制器ID通常为和资源有关的名词。
例如使用`article`作为处理文章的控制器ID。
For example, you may use `article` as the ID of a controller that handles article data.
Usually, a controller is designed to handle the requests regarding a particular type of resource.
For this reason, controller IDs are often nouns referring to the types of the resources that they are handling.
For example, you may use `article` as the ID of a controller that handles article data.

控制器ID应仅包含英文小写字母、数字、下划线、中横杠和正斜杠，
例如 `article` 和 `post-comment` 是真是的控制器ID，`article?`, `PostComment`, `admin\post`不是控制器ID。
By default, controller IDs should contain these characters only: English letters in lower case, digits,
underscores, dashes and forward slashes. For example, `article` and `post-comment` are both valid controller IDs,
while `article?`, `PostComment`, `admin\post` are not.

控制器Id可包含子目录前缀，例如 `admin/article` 代表
[[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间下 `admin`子目录中 `article` 控制器。
子目录前缀可为英文大小写字母、数字、下划线、正斜杠，其中正斜杠用来区分多级子目录(如 `panels/admin`)。
A controller ID may also contain a subdirectory prefix. For example, `admin/article` stands for an `article` controller
in the `admin` subdirectory under the [[yii\base\Application::controllerNamespace|controller namespace]].
Valid characters for subdirectory prefixes include: English letters in lower and upper cases, digits, underscores and
forward slashes, where forward slashes are used as separators for multi-level subdirectories (e.g. `panels/admin`).


### 控制器类命名 <a name="controller-class-naming"></a>
### Controller Class Naming <a name="controller-class-naming"></a>

控制器ID遵循以下规则衍生控制器类名：
Controller class names can be derived from controller IDs according to the following rules:

* 将用正斜杠区分的每个单词第一个字母转为大写。注意如果控制器ID包含正斜杠，只将最后的正斜杠后的部分第一个字母转为大写；
* 去掉中横杠，将正斜杠替换为反斜杠;
* 增加`Controller`后缀;
* 在前面增加[[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间.
* And prepend the [[yii\base\Application::controllerNamespace|controller namespace]].
* Turn the first letter in each word separated by dashes into upper case. Note that if the controller ID
  contains slashes, this rule only applies to the part after the last slash in the ID.
* Remove dashes and replace any forward slashes with backward slashes.
* Append the suffix `Controller`.
* And prepend the [[yii\base\Application::controllerNamespace|controller namespace]].

下面为一些示例，假设[[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间为 `app\controllers`:
The followings are some examples, assuming the [[yii\base\Application::controllerNamespace|controller namespace]]
takes the default value `app\controllers`:

* `article` 对应 `app\controllers\ArticleController`;
* `post-comment` 对应 `app\controllers\PostCommentController`;
* `admin/post-comment` 对应 `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` 对应 `app\controllers\adminPanels\PostCommentController`.
* `article` derives `app\controllers\ArticleController`;
* `post-comment` derives `app\controllers\PostCommentController`;
* `admin/post-comment` derives `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` derives `app\controllers\adminPanels\PostCommentController`.

控制器类必须能被 [自动加载](concept-autoloading.md)，所以在上面的例子中，
控制器`article` 类应在 [别名](concept-aliases.md) 为`@app/controllers/ArticleController.php`的文件中定义，
控制器`admin/post2-comment`应在`@app/controllers/admin/Post2CommentController.php`文件中。
Controller classes must be [autoloadable](concept-autoloading.md). For this reason, in the above examples,
the `article` controller class should be saved in the file whose [alias](concept-aliases.md)
is `@app/controllers/ArticleController.php`; while the `admin/post2-comment` controller should be
in `@app/controllers/admin/Post2CommentController.php`.

> 补充: 最后一个示例 `admin/post2-comment` 表示你可以将控制器放在
  [[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间下的子目录中，
  在你不想用 [模块](structure-modules.md) 的情况下给控制器分类，这种方式很有用。
> Info: The last example `admin/post2-comment` shows how you can put a controller under a sub-directory
  of the [[yii\base\Application::controllerNamespace|controller namespace]]. This is useful when you want
  to organize your controllers into several categories and you do not want to use [modules](structure-modules.md).


### 控制器部署 <a name="controller-map"></a>
### Controller Map <a name="controller-map"></a>

可通过配置 [[yii\base\Application::controllerMap|controller map]] 来强制上述的控制器ID和类名对应，
通常用在使用第三方不能掌控类名的控制器上。
You can configure [[yii\base\Application::controllerMap|controller map]] to overcome the constraints
of the controller IDs and class names described above. This is mainly useful when you are using some
third-party controllers which you do not control over their class names.

配置 [应用配置](structure-applications.md#application-configurations) 
中的[application configuration](structure-applications.md#application-configurations)，如下所示：
You may configure [[yii\base\Application::controllerMap|controller map]] in the
[application configuration](structure-applications.md#application-configurations) like the following:

```php
[
    'controllerMap' => [
        // 用类名申明 "account" 控制器
        'account' => 'app\controllers\UserController',

        // 用配置数组申明 "article" 控制器
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### 默认控制器 <a name="default-controller"></a>
### Default Controller <a name="default-controller"></a>

每个应用有一个由[[yii\base\Application::defaultRoute]]属性指定的默认控制器；
当请求没有指定 [路由](#ids-routes)，该属性值作为路由使用。
对于[[yii\web\Application|Web applications]]网页应用，它的值为 `'site'`，
对于 [[yii\console\Application|console applications]]控制台应用，它的值为 `help`，
所以URL为 `http://hostname/index.php` 表示由 `site` 控制器来处理。
Each application has a default controller specified via the [[yii\base\Application::defaultRoute]] property.
When a request does not specify a [route](#ids-routes), the route specified by this property will be used.
For [[yii\web\Application|Web applications]], its value is `'site'`, while for [[yii\console\Application|console applications]],
it is `help`. Therefore, if a URL is `http://hostname/index.php`, it means the `site` controller will handle the request.

可以在 [应用配置](structure-applications.md#application-configurations) 中修改默认控制器，如下所示：
You may change the default controller with the following [application configuration](structure-applications.md#application-configurations):

```php
[
    'defaultRoute' => 'main',
]
```


## 创建操作 <a name="creating-actions"></a>
## Creating Actions <a name="creating-actions"></a>

创建操作可简单地在控制器类中定义所谓的 *操作方法* 来完成，操作方法必须是以`action`开头的公有方法。
操作方法的返回值会作为响应数据发送给终端用户，如下代码定义了两个操作 `index` 和 `hello-world`:
Creating actions can be as simple as defining the so-called *action methods* in a controller class. An action method is
a *public* method whose name starts with the word `action`. The return value of an action method represents
the response data to be sent to end users. The following code defines two actions `index` and `hello-world`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```


### 操作ID <a name="action-ids"></a>
### Action IDs <a name="action-ids"></a>

操作通常是用来执行资源的特定操作，因此，操作ID通常为动词，如`view`, `update`等等。
An action is often designed to perform a particular manipulation about a resource. For this reason,
action IDs are usually verbs, such as `view`, `update`, etc.

操作ID应仅包含英文小写字母、数字、下划线和中横杠，操作ID中的中横杠用来分隔单词。
例如`view`, `update2`, `comment-post`是真实的操作ID，`view?`, `Update`不是操作ID.
By default, action IDs should contain these characters only: English letters in lower case, digits,
underscores and dashes. The dashes in an actionID are used to separate words. For example,
`view`, `update2`, `comment-post` are all valid action IDs, while `view?`, `Update` are not.

可通过两种方式创建操作ID，内联操作和独立操作. An inline action is
内联操作在控制器类中定义为方法；独立操作是继承[[yii\base\Action]]或它的子类的类。
内联操作容易创建，在无需重用的情况下优先使用；
独立操作相反，主要用于多个控制器重用，或重构为[扩展](structure-extensions.md)。
You can create actions in two ways: inline actions and standalone actions. An inline action is
defined as a method in the controller class, while a standalone action is a class extending
[[yii\base\Action]] or its child class. Inline actions take less effort to create and are often preferred
if you have no intention to reuse these actions. Standalone actions, on the other hand, are mainly
created to be used in different controllers or be redistributed as [extensions](structure-extensions.md).


### 内联操作 <a name="inline-actions"></a>
### Inline Actions <a name="inline-actions"></a>

内联操作指的是根据我们刚描述的操作方法。
Inline actions refer to the actions that are defined in terms of action methods as we just described.

操作方法的名字是根据操作ID遵循如下规则衍生：
The names of the action methods are derived from action IDs according to the following criteria:

* 将每个单词的第一个字母转为大写;
* 去掉中横杠;
* 增加`action`前缀.
* Turn the first letter in each word of the action ID into upper case;
* Remove dashes;
* Prepend the prefix `action`.

例如`index` 转成 `actionIndex`, `hello-world` 转成 `actionHelloWorld`。
For example, `index` becomes `actionIndex`, and `hello-world` becomes `actionHelloWorld`.

> 注意: 操作方法的名字*大小写敏感*，如果方法名称为`ActionIndex`不会认为是操作方法，
  所以请求`index`操作会返回一个异常，也要注意操作方法必须是公有的，私有或者受保护的方法不能定义成内联操作。
> Note: The names of the action methods are *case-sensitive*. If you have a method named `ActionIndex`,
  it will not be considered as an action method, and as a result, the request for the `index` action
  will result in an exception. Also note that action methods must be public. A private or protected
  method does NOT define an inline action.


因为容易创建，内联操作是最常用的操作，但是如果你计划在不同地方重用相同的操作，
或者你想重新分配一个操作，需要考虑定义它为*独立操作*。
Inline actions are the most commonly defined actions because they take little effort to create. However,
if you plan to reuse the same action in different places, or if you want to redistribute an action,
you should consider defining it as a *standalone action*.


### 独立操作 <a name="standalone-actions"></a>
### Standalone Actions <a name="standalone-actions"></a>

独立操作通过继承[[yii\base\Action]]或它的子类来定义。
例如Yii发布的[[yii\web\ViewAction]]和[[yii\web\ErrorAction]]都是独立操作。
Standalone actions are defined in terms of action classes extending [[yii\base\Action]] or its child classes.
For example, in the Yii releases, there are [[yii\web\ViewAction]] and [[yii\web\ErrorAction]], both of which
are standalone actions.

要使用独立操作，需要通过控制器中覆盖[[yii\base\Controller::actions()]]方法在*action map*中申明，如下例所示：
To use a standalone action, you should declare it in the *action map* by overriding the
[[yii\base\Controller::actions()]] method in your controller classes like the following:

```php
public function actions()
{
    return [
        // 用类来申明"error" 操作
        'error' => 'yii\web\ErrorAction',

        // 用配置数组申明 "view" 操作
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

如上所示， `actions()` 方法返回键为操作ID、值为对应操作类名或数组[configurations](concept-configurations.md) 的数组。
和内联操作不同，独立操作ID可包含任意字符，只要在`actions()` 方法中申明.
As you can see, the `actions()` method should return an array whose keys are action IDs and values the corresponding
action class names or [configurations](concept-configurations.md). Unlike inline actions, action IDs for standalone
actions can contain arbitrary characters, as long as they are declared in the `actions()` method.


为创建一个独立操作类，需要继承[[yii\base\Action]] 或它的子类，并实现公有的名称为`run()`的方法,
`run()` 方法的角色和操作方法类似，例如：
To create a standalone action class, you should extend [[yii\base\Action]] or its child class, and implement
a public method named `run()`. The role of the `run()` method is similar to that of an action method. For example,

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### 操作结果 <a name="action-results"></a>
### Action Results <a name="action-results"></a>

操作方法或独立操作的`run()`方法的返回值非常中药，它表示对应操作结果。
The return value of an action method or the `run()` method of a standalone action is significant. It stands
for the result of the corresponding action.

返回值可为 [响应](runtime-responses.md) 对象，作为响应发送给终端用户。
The return value can be a [response](runtime-responses.md) object which will be sent to as the response
to end users.

* 对于[[yii\web\Application|Web applications]]网页应用，返回值可为任意数据, 它赋值给[[yii\web\Response::data]]，
  最终转换为字符串来展示响应内容。
* 对于[[yii\console\Application|console applications]]控制台应用，返回值可为整数，
  表示命令行下执行的 [[yii\console\Response::exitStatus|exit status]] 退出状态。
* For [[yii\web\Application|Web applications]], the return value can also be some arbitrary data which will
  be assigned to [[yii\web\Response::data]] and be further converted into a string representing the response body.
* For [[yii\console\Application|console applications]], the return value can also be an integer representing
  the [[yii\console\Response::exitStatus|exit status]] of the command execution.

在上面的例子中，操作结果都为字符串，作为响应数据发送给终端用户，下例显示一个操作通过
返回响应对象（因为[[yii\web\Controller::redirect()|redirect()]]方法返回一个响应对象）可将用户浏览器跳转到新的URL。
In the examples shown above, the action results are all strings which will be treated as the response body
to be sent to end users. The following example shows how an action can redirect the user browser to a new URL
by returning a response object (because the [[yii\web\Controller::redirect()|redirect()]] method returns
a response object):

```php
public function actionForward()
{
    // 用户浏览器跳转到 http://example.com
    return $this->redirect('http://example.com');
}
```


### 操作参数 <a name="action-parameters"></a>
### Action Parameters <a name="action-parameters"></a>

内联操作的操作方法和独立操作的 `run()` 方法可以带参数，称为*操作参数*。
参数值从请求中获取，对于[[yii\web\Application|Web applications]]网页应用，
每个操作参数的值从`$_GET`中获得，参数名作为键；
对于[[yii\console\Application|console applications]]控制台应用, 操作参数对应命令行参数。
The action methods for inline actions and the `run()` methods for standalone actions can take parameters,
called *action parameters*. Their values are obtained from requests. For [[yii\web\Application|Web applications]],
the value of each action parameter is retrieved from `$_GET` using the parameter name as the key;
for [[yii\console\Application|console applications]], they correspond to the command line arguments.

如下例，操作`view` (内联操作) 申明了两个参数 `$id` 和 `$version`。
In the following example, the `view` action (an inline action) has declared two parameters: `$id` and `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

操作参数会被不同的参数填入，如下所示：
The action parameters will be populated as follows for different requests:

* `http://hostname/index.php?r=post/view&id=123`: `$id` 会填入`'123'`，`$version` 仍为 null 空因为没有`version`请求参数;
* `http://hostname/index.php?r=post/view&id=123&version=2`: $id` 和 `$version` 分别填入 `'123'` 和 `'2'`；
* `http://hostname/index.php?r=post/view`: 会抛出[[yii\web\BadRequestHttpException]] 异常
  因为请求没有提供参数给必须赋值参数`$id`；
* `http://hostname/index.php?r=post/view&id[]=123`: 会抛出[[yii\web\BadRequestHttpException]] 异常
  因为`$id` 参数收到数字值 `['123']`而不是字符串.
* `http://hostname/index.php?r=post/view&id=123`: the `$id` parameter will be filled with the value
  `'123'`,  while `$version` is still null because there is no `version` query parameter.
* `http://hostname/index.php?r=post/view&id=123&version=2`: the `$id` and `$version` parameters will
  be filled with `'123'` and `'2'`, respectively.
* `http://hostname/index.php?r=post/view`: a [[yii\web\BadRequestHttpException]] exception will be thrown
  because the required `$id` parameter is not provided in the request.
* `http://hostname/index.php?r=post/view&id[]=123`: a [[yii\web\BadRequestHttpException]] exception will be thrown
  because `$id` parameter is receiving an unexpected array value `['123']`.

如果想让操作参数接收数组值，需要指定$id为`array`，如下所示：
If you want an action parameter to accept array values, you should type-hint it with `array`, like the following:

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

现在如果请求为 `http://hostname/index.php?r=post/view&id[]=123`, 参数 `$id` 会使用数组值`['123']`，
如果请求为 `http://hostname/index.php?r=post/view&id=123`，
参数 `$id` 会获取相同数组值，因为无类型的`'123'`会自动转成数组。

上述例子主要描述网页应用的操作参数，对于控制台应用，更多详情请参阅[控制台命令](tutorial-console.md)。
The above examples mainly show how action parameters work for Web applications. For console applications,
please refer to the [Console Commands](tutorial-console.md) section for more details.


### 默认操作 <a name="default-action"></a>
### Default Action <a name="default-action"></a>

每个控制器都有一个由 [[yii\base\Controller::defaultAction]] 属性指定的默认操作，
当[路由](#ids-routes) 只包含控制器ID，会使用所请求的控制器的默认操作。
Each controller has a default action specified via the [[yii\base\Controller::defaultAction]] property.
When a [route](#ids-routes) contains the controller ID only, it implies that the default action of
the specified controller is requested.

默认操作默认为 `index`，如果想修改默认操作，只需简单地在控制器类中覆盖这个属性，如下所示：
By default, the default action is set as `index`. If you want to change the default value, simply override
this property in the controller class, like the following:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## 控制器生命周期 <a name="controller-lifecycle"></a>
## Controller Lifecycle <a name="controller-lifecycle"></a>

处理一个请求时，[应用主体](structure-applications.md) 会根据请求[路由](#routes)创建一个控制器，will create a controller
控制器经过以下生命周期来完成请求：
When processing a request, an [application](structure-applications.md) will create a controller
based on the requested [route](#routes). The controller will then undergo the following lifecycle
to fulfill the request:

1. 在控制器创建和配置后，[[yii\base\Controller::init()]] 方法会被调用。
1. The [[yii\base\Controller::init()]] method is called after the controller is created and configured.
2. 控制器根据请求操作ID创建一个操作对象:
   * 如果操作ID没有指定，会使用[[yii\base\Controller::defaultAction|default action ID]]默认操作ID；
   * 如果在[[yii\base\Controller::actions()|action map]]找到操作ID，会创建一个独立操作；
   * 如果操作ID对应操作方法，会创建一个内联操作；
   * 否则会抛出[[yii\base\InvalidRouteException]]异常。
2. The controller creates an action object based on the requested action ID:
   * If the action ID is not specified, the [[yii\base\Controller::defaultAction|default action ID]] will be used.
   * If the action ID is found in the [[yii\base\Controller::actions()|action map]], a standalone action
     will be created;
   * If the action ID is found to match an action method, an inline action will be created;
   * Otherwise an [[yii\base\InvalidRouteException]] exception will be thrown.
3. 控制器按顺序调用应用主体、模块（如果控制器属于模块）、控制器的 `beforeAction()` 方法；
   * 如果任意一个调用返回false，后面未调用的`beforeAction()`会跳过并且操作执行会被取消；
     action execution will be cancelled.
   * 默认情况下每个 `beforeAction()` 方法会触发一个 `beforeAction` 事件，在事件中你可以追加事件处理操作；
3. The controller sequentially calls the `beforeAction()` method of the application, the module (if the controller
   belongs to a module) and the controller.
   * If one of the calls returns false, the rest of the uncalled `beforeAction()` will be skipped and the
     action execution will be cancelled.
   * By default, each `beforeAction()` method call will trigger a `beforeAction` event to which you can attach a handler.
4. 控制器执行操作:
   * 请求数据解析和填入到操作参数；
4. The controller runs the action:
   * The action parameters will be analyzed and populated from the request data;
5. 控制器按顺序调用控制器、模块（如果控制器属于模块）、应用主体的 `afterAction()` 方法；
   * 默认情况下每个 `afterAction()` 方法会触发一个 `afterAction` 事件，在事件中你可以追加事件处理操作；
5. The controller sequentially calls the `afterAction()` method of the controller, the module (if the controller
   belongs to a module) and the application.
   * By default, each `afterAction()` method call will trigger an `afterAction` event to which you can attach a handler.
6. 应用主体获取操作结果并赋值给[响应](runtime-responses.md).
6. The application will take the action result and assign it to the [response](runtime-responses.md).


## 最佳实践 <a name="best-practices"></a>
## Best Practices <a name="best-practices"></a>

在设计良好的应用中，控制器很精练，包含的操作代码简短；
如果你的控制器很复杂，通常意味着需要重构，转移一些代码到其他类中。
In a well-designed application, controllers are often very thin with each action containing only a few lines of code.
If your controller is rather complicated, it usually indicates that you should refactor it and move some code
to other classes.

归纳起来，控制器
In summary, controllers

* 可访问 [请求](runtime-requests.md) 数据;
* 可根据请求数据调用 [模型](structure-models.md) 的方法和其他服务组件;
* 可使用 [视图](structure-views.md) 构造响应;
* 不应处理应被[模型](structure-models.md)处理的请求数据;
* 应避免嵌入HTML或其他展示代码，这些代码最好在 [视图](structure-views.md)中处理.
* may access the [request](runtime-requests.md) data;
* may call methods of [models](structure-models.md) and other service components with request data;
* may use [views](structure-views.md) to compose responses;
* should NOT process the request data - this should be done in [models](structure-models.md);
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md).
