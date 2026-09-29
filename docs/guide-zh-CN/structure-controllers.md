控制器
===========

控制器是 [MVC](https://zh.wikipedia.org/wiki/MVC) 模式中的一部分，
是继承[[yii\base\Controller]]类的对象，负责处理请求和生成响应。
具体来说，控制器从[应用主体](structure-applications.md)
接管控制后会分析请求数据并传送到[模型](structure-models.md)，
传送模型结果到[视图](structure-views.md)，最后生成输出响应信息。


## 动作 <span id="actions"></span>

控制器由 *操作* 组成，它是执行终端用户请求的最基础的单元，
一个控制器可有一个或多个操作。

如下示例显示包含两个动作`view` and `create` 的控制器`post`：

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

在操作 `view` (定义为 `actionView()` 方法)中， 
代码首先根据请求模型ID加载 [模型](structure-models.md)，
如果加载成功，会渲染名称为`view`的[视图](structure-views.md)并显示，否则会抛出一个异常。

在操作 `create` (定义为 `actionCreate()` 方法)中, 代码相似. 
先将请求数据填入[模型](structure-models.md)，
然后保存模型，如果两者都成功，会跳转到ID为新创建的模型的`view`操作，
否则显示提供用户输入的`create`视图。


## 路由 <span id="routes"></span>

终端用户通过所谓的*路由*寻找到动作，路由是包含以下部分的字符串：

* 模块ID: 仅存在于控制器属于非应用的[模块](structure-modules.md);
* 控制器ID: 同应用（或同模块如果为模块下的控制器）
  下唯一标识控制器的字符串;
* 操作ID: 同控制器下唯一标识操作的字符串。

路由使用如下格式:

```
ControllerID/ActionID
```

如果属于模块下的控制器，使用如下格式：

```php
ModuleID/ControllerID/ActionID
```

如果用户的请求地址为 `https://hostname/index.php?r=site/index`, 
会执行`site` 控制器的`index` 操作。
更多关于处理路由的详情请参阅 [路由](runtime-routing.md) 一节。


## 创建控制器 <span id="creating-controllers"></span>

在[[yii\web\Application|Web applications]]网页应用中，控制器应继承[[yii\web\Controller]] 或它的子类。
同理在[[yii\console\Application|console applications]]控制台应用中，控制器继承[[yii\console\Controller]] 或它的子类。
如下代码定义一个 `site` 控制器:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### 控制器ID <span id="controller-ids"></span>

通常情况下，控制器用来处理请求有关的资源类型，
因此控制器ID通常为和资源有关的名词。
例如使用`article`作为处理文章的控制器ID。

控制器ID应仅包含英文小写字母、数字、下划线、中横杠和正斜杠，
例如 `article` 和 `post-comment` 是真是的控制器ID，
`article?`, `PostComment`, `admin\post`不是控制器ID。

控制器Id可包含子目录前缀，例如 `admin/article` 代表
[[yii\base\Application::controllerNamespace|controller namespace]]
控制器命名空间下 `admin`子目录中 `article` 控制器。
子目录前缀可为英文大小写字母、数字、下划线、正斜杠，其中正斜杠用来区分多级子目录(如 `panels/admin`)。


### 控制器类命名 <span id="controller-class-naming"></span>

控制器ID遵循以下规则衍生控制器类名：

1. 将用正斜杠区分的每个单词第一个字母转为大写。注意如果控制器ID包含正斜杠，
   只将最后的正斜杠后的部分第一个字母转为大写；
2. 去掉中横杠，将正斜杠替换为反斜杠;
3. 增加`Controller`后缀;
4. 在前面增加[[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间.

下面为一些示例，假设[[yii\base\Application::controllerNamespace|controller namespace]]
控制器命名空间为 `app\controllers`:

* `article` 对应 `app\controllers\ArticleController`;
* `post-comment` 对应 `app\controllers\PostCommentController`;
* `admin/post-comment` 对应 `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` 对应 `app\controllers\adminPanels\PostCommentController`.

控制器类必须能被 [自动加载](concept-autoloading.md)，所以在上面的例子中，
控制器`article` 类应在 [别名](concept-aliases.md) 
为`@app/controllers/ArticleController.php`的文件中定义，
控制器`admin/post-comment`应在`@app/controllers/admin/PostCommentController.php`文件中。

> Info: 最后一个示例 `admin/post-comment` 表示你可以将控制器放在
  [[yii\base\Application::controllerNamespace|controller namespace]]控制器命名空间下的子目录中，
  在你不想用 [模块](structure-modules.md) 的情况下给控制器分类，这种方式很有用。


### 控制器部署 <span id="controller-map"></span>

可通过配置 [[yii\base\Application::controllerMap|controller map]] 
来强制上述的控制器ID和类名对应，
通常用在使用第三方不能掌控类名的控制器上。

配置 [应用配置](structure-applications.md#application-configurations) 
中的[application configuration](structure-applications.md#application-configurations)，如下所示：

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


### 默认控制器 <span id="default-controller"></span>

每个应用有一个由[[yii\base\Application::defaultRoute]]属性指定的默认控制器；
当请求没有指定 [路由](#ids-routes)，该属性值作为路由使用。
对于[[yii\web\Application|Web applications]]网页应用，它的值为 `'site'`，对于 [[yii\console\Application|console applications]]
控制台应用，它的值为 `help`，所以URL为 `https://hostname/index.php` 表示由 `site` 控制器来处理。

可以在 [应用配置](structure-applications.md#application-configurations) 中修改默认控制器，如下所示：

```php
[
    'defaultRoute' => 'main',
]
```


## 创建动作 <span id="creating-actions"></span>

创建操作可简单地在控制器类中定义所谓的 *操作方法* 来完成，操作方法必须是以`action`开头的公有方法。
操作方法的返回值会作为响应数据发送给终端用户，
如下代码定义了两个操作 `index` 和 `hello-world`:

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


### 动作ID <span id="action-ids"></span>

操作通常是用来执行资源的特定操作，因此，
操作ID通常为动词，如`view`, `update`等。

操作ID应仅包含英文小写字母、数字、下划线和中横杠，操作ID中的中横杠用来分隔单词。
例如`view`, `update2`, `comment-post`是真实的操作ID，
`view?`, `Update`不是操作ID.

可通过两种方式创建操作ID，内联操作和独立操作. An inline action is
内联操作在控制器类中定义为方法；独立操作是继承[[yii\base\Action]]或它的子类的类。
内联操作容易创建，在无需重用的情况下优先使用；
独立操作相反，主要用于多个控制器重用，
或重构为[扩展](structure-extensions.md)。


### 内联动作 <span id="inline-actions"></span>

内联动作指的是根据我们刚描述的操作方法。

动作方法的名字是根据操作ID遵循如下规则衍生：

1. 将每个单词的第一个字母转为大写;
2. 去掉中横杠;
3. 增加`action`前缀.

例如`index` 转成 `actionIndex`, `hello-world` 转成 `actionHelloWorld`。

> Note: 操作方法的名字*大小写敏感*，如果方法名称为`ActionIndex`不会认为是操作方法，
  所以请求`index`操作会返回一个异常，
  也要注意操作方法必须是公有的，
  私有或者受保护的方法不能定义成内联操作。


因为容易创建，内联操作是最常用的操作，
但是如果你计划在不同地方重用相同的操作，
或者你想重新分配一个操作，需要考虑定义它为*独立操作*。


### 独立动作 <span id="standalone-actions"></span>

独立操作通过继承[[yii\base\Action]]或它的子类来定义。
例如Yii发布的[[yii\web\ViewAction]]
和[[yii\web\ErrorAction]]都是独立操作。

要使用独立操作，需要通过控制器中覆盖[[yii\base\Controller::actions()]]方法在*action map*中申明，
如下例所示：

```php
public function actions()
{
    return [
        // 用类来申明"error" 动作
        'error' => 'yii\web\ErrorAction',

        // 用配置数组申明 "view" 动作
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

如上所示， `actions()` 方法返回键为操作ID、值为对应操作类名
或数组[configurations](concept-configurations.md) 的数组。
和内联操作不同，独立操作ID可包含任意字符，只要在`actions()` 方法中申明.

为创建一个独立操作类，需要继承[[yii\base\Action]] 或它的子类，并实现公有的名称为`run()`的方法,
`run()` 方法的角色和操作方法类似，例如：

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


### 动作结果 <span id="action-results"></span>

操作方法或独立操作的`run()`方法的返回值非常重要，
它表示对应操作结果。

返回值可为 [响应](runtime-responses.md) 对象，作为响应发送给终端用户。

* 对于[[yii\web\Application|Web applications]]网页应用，返回值可为任意数据, 它赋值给[[yii\web\Response::data]]，
  最终转换为字符串来展示响应内容。
* 对于[[yii\console\Application|console applications]]控制台应用，返回值可为整数，
  表示命令行下执行的 [[yii\console\Response::exitStatus|exit status]] 退出状态。

在上面的例子中，操作结果都为字符串，作为响应数据发送给终端用户，
下例显示一个操作通过
返回响应对象（因为[[yii\web\Controller::redirect()|redirect()]]方法返回一个响应对象）
可将用户浏览器跳转到新的URL。

```php
public function actionForward()
{
    // 用户浏览器跳转到 https://example.com
    return $this->redirect('https://example.com');
}
```


### 动作参数 <span id="action-parameters"></span>

内联动作的操作方法和独立动作的 `run()` 方法可以带参数，称为*动作参数*。
参数值从请求中获取，对于[[yii\web\Application|Web applications]]网页应用，
每个动作参数的值从`$_GET`中获得，参数名作为键；
对于[[yii\console\Application|console applications]]控制台应用, 动作参数对应命令行参数。

如下例，动作`view` (内联动作) 申明了两个参数 `$id` 和 `$version`。

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

动作参数会被不同的参数填入，如下所示：

* `https://hostname/index.php?r=post/view&id=123`: `$id` 会填入`'123'`，
  `$version` 仍为 null 空因为没有`version`请求参数;
* `https://hostname/index.php?r=post/view&id=123&version=2`: 
  $id` 和 `$version` 分别填入 `'123'` 和 `'2'`；
* `https://hostname/index.php?r=post/view`: 会抛出[[yii\web\BadRequestHttpException]] 异常
  因为请求没有提供参数给必须赋值参数`$id`；
* `https://hostname/index.php?r=post/view&id[]=123`: 会抛出[[yii\web\BadRequestHttpException]] 异常
  因为 `$id` 参数收到数组值 `['123']` 而不是字符串.

如果你想要一个动作参数来接受数组值，你应该使用 `array` 来提示它，如下所示：

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

现在如果请求为 `https://hostname/index.php?r=post/view&id[]=123`, 参数 `$id` 会使用数组值 `['123']`，
如果请求为 `https://hostname/index.php?r=post/view&id=123`，
参数 `$id` 会获取相同数组值，因为无类型的 `'123'` 会自动转成数组。

上述例子主要描述网页应用的操作参数，对于控制台应用，
更多详情请参阅[控制台命令](tutorial-console.md)。


### 默认动作 <span id="default-action"></span>

每个控制器都有一个由 [[yii\base\Controller::defaultAction]] 属性指定的默认操作，
当[路由](#ids-routes) 只包含控制器ID，
会使用所请求的控制器的默认操作。

默认操作默认为 `index`，如果想修改默认操作，只需简单地在控制器类中覆盖这个属性，
如下所示：

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


## 控制器生命周期 <span id="controller-lifecycle"></span>

处理一个请求时，[应用主体](structure-applications.md) 会根据请求
[路由](#routes)创建一个控制器，
控制器经过以下生命周期来完成请求：

1. 在控制器创建和配置后，[[yii\base\Controller::init()]] 方法会被调用。
2. 控制器根据请求操作ID创建一个操作对象:
   * 如果操作ID没有指定，会使用[[yii\base\Controller::defaultAction|default action ID]]默认操作ID；
   * 如果在[[yii\base\Controller::actions()|action map]]找到操作ID，
     会创建一个独立操作；
   * 如果操作ID对应操作方法，会创建一个内联操作；
   * 否则会抛出[[yii\base\InvalidRouteException]]异常。
3. 控制器按顺序调用应用主体、模块（如果控制器属于模块）、
   控制器的 `beforeAction()` 方法；
   * 如果任意一个调用返回false，后面未调用的`beforeAction()`会跳过并且操作执行会被取消；
     action execution will be cancelled.
   * 默认情况下每个 `beforeAction()` 方法会触发一个 `beforeAction` 事件，在事件中你可以追加事件处理操作；
4. 控制器执行操作:
   * 请求数据解析和填入到操作参数；
5. 控制器按顺序调用控制器、模块（如果控制器属于模块）、应用主体的 `afterAction()` 方法；
   * 默认情况下每个 `afterAction()` 方法会触发一个 `afterAction` 事件，
   在事件中你可以追加事件处理操作；
6. 应用主体获取操作结果并赋值给[响应](runtime-responses.md).


## 最佳实践 <span id="best-practices"></span>

在设计良好的应用中，控制器很精练，包含的操作代码简短；
如果你的控制器很复杂，通常意味着需要重构，
转移一些代码到其他类中。

归纳起来，控制器

* 可访问 [请求](runtime-requests.md) 数据;
* 可根据请求数据调用 [模型](structure-models.md) 的方法和其他服务组件;
* 可使用 [视图](structure-views.md) 构造响应;
* 不应处理应被[模型](structure-models.md)处理的请求数据;
* 应避免嵌入HTML或其他展示代码，这些代码最好在 [视图](structure-views.md)中处理.
