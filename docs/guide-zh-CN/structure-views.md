视图
Views
=====

视图是 [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 模式中的一部分。
它是展示数据到终端用户的代码，在网页应用中，根据*视图模板*来创建视图，视图模板为PHP脚本文件，
主要包含HTML代码和展示类PHP代码，通过[[yii\web\View|view]]应用组件来管理，
该组件主要提供通用方法帮助视图构造和渲染，简单起见，我们称视图模板或视图模板文件为视图。


## 创建视图 <a name="creating-views"></a>
## Creating Views <a name="creating-views"></a>

如前所述，视图为包含HTML和PHP代码的PHP脚本，如下代码为一个登录表单的视图，
可看到PHP代码用来生成动态内容如页面标题和表单，HTML代码把它组织成一个漂亮的HTML页面。
As aforementioned, a view is simply a PHP script mixed with HTML and PHP code. The following is the view
that presents a login form. As you can see, PHP code is used to generate the dynamic content, such as the
page title and the form, while HTML code organizes them into a presentable HTML page.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Please fill out the following fields to login:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

在视图中，可访问 `$this` 指向 [[yii\web\View|view component]] 来管理和渲染这个视图文件。
Within a view, you can access `$this` which refers to the [[yii\web\View|view component]] managing
and rendering this view template.

除了 `$this`之外，上述示例中的视图有其他预定义变量如 `$model`，
这些变量代表从[控制器](structure-controllers.md)或其他触发[视图渲染](#rendering-views)的对象 *传入* 到视图的数据。
Besides `$this`, there may be other predefined variables in a view, such as `$model` in the above
example. These variables represent the data that are *pushed* into the view by [controllers](structure-controllers.md)
or other objects whose trigger the [view rendering](#rendering-views).

> 技巧: 将预定义变量列到视图文件头部注释处，这样可被IDE编辑器识别，也是生成视图文档的好方法。
> Tip: The predefined variables are listed in a comment block at beginning of a view so that they can
  be recognized by IDEs. It is also a good way of documenting your views.


### 安全 <a name="security"></a>
### Security <a name="security"></a>

当创建生成HTML页面的视图时，在显示之前将用户输入数据进行转码和过滤非常重要，
否则，你的应用可能会被[跨站脚本](http://en.wikipedia.org/wiki/Cross-site_scripting) 攻击。
When creating views that generate HTML pages, it is important that you encode and/or filter the data coming
from end users before presenting them. Otherwise, your application may be subject to
[cross-site scripting](http://en.wikipedia.org/wiki/Cross-site_scripting) attacks.

要显示纯文本，先调用 [[yii\helpers\Html::encode()]] 进行转码，例如如下代码将用户名在显示前先转码：
To display a plain text, encode it first by calling [[yii\helpers\Html::encode()]]. For example, the following code
encodes the user name before displaying it:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

要显示HTML内容，先调用 [[yii\helpers\HtmlPurifier]] 过滤内容，例如如下代码将提交内容在显示前先过滤：

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> 技巧：HTMLPurifier在保证输出数据安全上做的不错，但性能不佳，如果你的应用需要高性能可考虑
  [缓存](caching-overview.md) 过滤后的结果。
> Tip: While HTMLPurifier does excellent job in making output safe, it is not fast. You should consider
  [caching](caching-overview.md) the filtering result if your application requires high performance.


### 组织视图 <a name="organizing-views"></a>
### Organizing Views <a name="organizing-views"></a>

与 [控制器](structure-controllers.md) 和 [模型](structure-models.md) 类似，在组织视图上有一些约定：

* 控制器渲染的视图文件默认放在 `@app/views/ControllerID` 目录下，
  其中 `ControllerID` 对应 [控制器 ID](structure-controllers.md#routes),
  例如控制器类为 `PostController`，视图文件目录应为 `@app/views/post`，
  控制器类 `PostCommentController`对应的目录为 `@app/views/post-comment`，
  如果是模块中的控制器，目录应为 [[yii\base\Module::basePath|module directory]] 模块目录下的 `views/ControllerID` 目录；
* 对于 [小部件](structure-widgets.md) 渲染的视图文件默认放在 `WidgetPath/views` 目录，
  其中 `WidgetPath` 代表小部件类文件所在的目录；
* 对于其他对象渲染的视图文件，建议遵循和小部件相似的规则。
* For views rendered by a controller, they should be put under the directory `@app/views/ControllerID` by default,
  where `ControllerID` refers to the [controller ID](structure-controllers.md#routes). For example, if
  the controller class is `PostController`, the directory would be `@app/views/post`; If it is `PostCommentController`,
  the directory would be `@app/views/post-comment`. In case the controller belongs to a module, the directory
  would be `views/ControllerID` under the [[yii\base\Module::basePath|module directory]].
* For views rendered in a [widget](structure-widgets.md), they should be put under the `WidgetPath/views` directory by
  default, where `WidgetPath` stands for the directory containing the widget class file.
* For views rendered by other objects, it is recommended that you follow the similar convention as that for widgets.

可覆盖控制器或小部件的 [[yii\base\ViewContextInterface::getViewPath()]] 方法来自定义视图文件默认目录。
You may customize these default view directories by overriding the [[yii\base\ViewContextInterface::getViewPath()]]
method of controllers or widgets.


## 渲染视图 <a name="rendering-views"></a>
## Rendering Views <a name="rendering-views"></a>

可在 [控制器](structure-controllers.md), [小部件](structure-widgets.md), 或其他地方调用渲染视图方法来渲染视图，
该方法类似以下格式：
You can render views in [controllers](structure-controllers.md), [widgets](structure-widgets.md), or any
other places by calling view rendering methods. These methods share a similar signature shown as follows,

```
/**
 * @param string $view 视图名或文件路径，由实际的渲染方法决定
 * @param array $params 传递给视图的数据
 * @return string 渲染结果
 */
methodName($view, $params = [])
```


### 控制器中渲染 <a name="rendering-in-controllers"></a>

在 [控制器](structure-controllers.md) 中，可调用以下控制器方法来渲染视图：

* [[yii\base\Controller::render()|render()]]: 渲染一个 [视图名](#named-views) 并使用一个 [布局](#layouts)
  返回到渲染结果。
* [[yii\base\Controller::renderPartial()|renderPartial()]]: 渲染一个 [视图名](#named-views) 并且不使用布局。
* [[yii\web\Controller::renderAjax()|renderAjax()]]: 渲染一个 [视图名](#named-views) 并且不使用布局，
  并注入所有注册的JS/CSS脚本和文件，通常使用在响应AJAX网页请求的情况下。
* [[yii\base\Controller::renderFile()|renderFile()]]: 渲染一个视图文件目录或[别名](concept-aliases.md)下的视图文件。
* [[yii\base\Controller::render()|render()]]: renders a [named view](#named-views) and applies a [layout](#layouts)
  to the rendering result.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: renders a [named view](#named-views) without any layout.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renders a [named view](#named-views) without any layout,
  and injects all registered JS/CSS scripts and files. It is usually used in response to AJAX Web requests.
* [[yii\base\Controller::renderFile()|renderFile()]]: renders a view specified in terms of a view file path or
  [alias](concept-aliases.md).

例如：

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

        // 渲染一个名称为"view"的视图并使用布局
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### 小部件中渲染 <a name="rendering-in-widgets"></a>
### Rendering in Widgets <a name="rendering-in-widgets"></a>

在 [小部件](structure-widgets.md) 中，可调用以下小部件方法来渲染视图：
Within [widgets](structure-widgets.md), you may call the following widget methods to render views.

* [[yii\base\Widget::render()|render()]]: 渲染一个 [视图名](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: 渲染一个视图文件目录或[别名](concept-aliases.md)下的视图文件。
* [[yii\base\Widget::render()|render()]]: renders a [named view](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: renders a view specified in terms of a view file path or
  [alias](concept-aliases.md).

For example,

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // 渲染一个名为 "list" 的视图
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### 视图中渲染 <a name="rendering-in-views"></a>
### Rendering in Views <a name="rendering-in-views"></a>

可以在视图中渲染另一个视图，可以调用[[yii\base\View|view component]]视图组件提供的以下方法：
You can render a view within another view by calling one of the following methods provided by the [[yii\base\View|view component]]:

* [[yii\base\View::render()|render()]]: 渲染一个 [视图名](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: 渲染一个 [视图名](#named-views)
  并注入所有注册的JS/CSS脚本和文件，通常使用在响应AJAX网页请求的情况下。
* [[yii\base\View::renderFile()|renderFile()]]: 渲染一个视图文件目录或[别名](concept-aliases.md)下的视图文件。
* [[yii\base\View::render()|render()]]: renders a [named view](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: renders a [named view](#named-views) and injects all registered
  JS/CSS scripts and files. It is usually used in response to AJAX Web requests.
* [[yii\base\View::renderFile()|renderFile()]]: renders a view specified in terms of a view file path or
  [alias](concept-aliases.md).

例如，视图中的如下代码会渲染该视图所在目录下的 `_overview.php` 视图文件，
记住视图中 `$this` 对应 [[yii\base\View|view]] 组件:
For example, the following code in a view renders the `_overview.php` view file which is in the same directory
as the view being currently rendered. Remember that `$this` in a view refers to the [[yii\base\View|view]] component:

```php
<?= $this->render('_overview') ?>
```


### 其他地方渲染 <a name="rendering-in-other-places"></a>
### Rendering in Other Places <a name="rendering-in-other-places"></a>

在任何地方都可以通过表达式 `Yii::$app->view` 访问 [[yii\base\View|view]] 应用组件，
调用它的如前所述的方法渲染视图，例如：
In any place, you can get access to the [[yii\base\View|view]] application component by the expression
`Yii::$app->view` and then call its aforementioned methods to render a view. For example,

```php
// 显示视图文件 "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### 视图名 <a name="named-views"></a>
### Named Views <a name="named-views"></a>

渲染视图时，可指定一个视图名或视图文件路径/别名，大多数情况下使用前者因为前者简洁灵活，
我们称用名字的视图为 *视图名*.

视图名可以依据以下规则到对应的视图文件路径：
A view name is resolved into the corresponding view file path according to the following rules:

* 视图名可省略文件扩展名，这种情况下使用 `.php` 作为扩展，
  视图名 `about` 对应到 `about.php` 文件名；
* A view name may omit the file extension name. In this case, `.php` will be used as the extension. For example,
  the view name `about` corresponds to the file name `about.php`.
* 视图名以双斜杠 `//` 开头，对应的视图文件路径为 `@app/views/ViewName`，
  也就是说视图文件在 [[yii\base\Application::viewPath|application's view path]] 路径下找，
  例如 `//site/about` 对应到 `@app/views/site/about.php`。
* If the view name starts with double slashes `//`, the corresponding view file path would be `@app/views/ViewName`.
  That is, the view is looked for under the [[yii\base\Application::viewPath|application's view path]].
  For example, `//site/about` will be resolved into `@app/views/site/about.php`.
* 视图名以单斜杠`/`开始，视图文件路径以当前使用[模块](structure-modules.md) 的[[yii\base\Module::viewPath|view path]]开始，
  如果不存在模块，使用`@app/views/ViewName`开始，例如，如果当前模块为`user`， `/user/create` 对应成
  `@app/modules/user/views/user/create.php`, 如果在模块中，`/user/create`对应`@app/views/user/create.php`。
* If the view name starts with a single slash `/`, the view file path is formed by prefixing the view name
  with the [[yii\base\Module::viewPath|view path]] of the currently active [module](structure-modules.md).
  If there is no active module, `@app/views/ViewName` will be used. For example, `/user/create` will be resolved into
  `@app/modules/user/views/user/create.php`, if the currently active module is `user`. If there is no active module,
  the view file path would be `@app/views/user/create.php`.
* 如果 [[yii\base\View::context|context]] 渲染视图 并且上下文实现了 [[yii\base\ViewContextInterface]],
  视图文件路径由上下文的 [[yii\base\ViewContextInterface::getViewPath()|view path]] 开始，
  这种主要用在控制器和小部件中渲染视图，例如
  如果上下文为控制器`SiteController`，`site/about` 对应到 `@app/views/site/about.php`。
* If the view is rendered with a [[yii\base\View::context|context]] and the context implements [[yii\base\ViewContextInterface]],
  the view file path is formed by prefixing the [[yii\base\ViewContextInterface::getViewPath()|view path]] of the
  context to the view name. This mainly applies to the views rendered within controllers and widgets. For example,
  `site/about` will be resolved into `@app/views/site/about.php` if the context is the controller `SiteController`.
* 如果视图渲染另一个视图，包含另一个视图文件的目录以当前视图的文件路径开始，
  例如被视图`@app/views/post/index.php` 渲染的 `item` 对应到 `@app/views/post/item`。
* If a view is rendered within another view, the directory containing the other view file will be prefixed to
  the new view name to form the actual view file path. For example, `item` will be resolved into `@app/views/post/item`
  if it is being rendered in the view `@app/views/post/index.php`.

根据以上规则，在控制器中 `app\controllers\PostController` 调用 `$this->render('view')`，
实际上渲染 `@app/views/post/view.php` 视图文件，当在该视图文件中调用 `$this->render('_overview')`
会渲染 `@app/views/post/_overview.php` 视图文件。
According to the above rules, calling `$this->render('view')` in a controller `app\controllers\PostController` will
actually render the view file `@app/views/post/view.php`, while calling `$this->render('_overview')` in that view
will render the view file `@app/views/post/_overview.php`.


### 视图中访问数据 <a name="accessing-data-in-views"></a>
### Accessing Data in Views <a name="accessing-data-in-views"></a>

在视图中有两种方式访问数据：推送和拉取。
There are two approaches to access data within a view: push and pull.

推送方式是通过视图渲染方法的第二个参数传递数据，数据格式应为名称-值的数组，
视图渲染时，调用PHP `extract()` 方法将该数组转换为视图可访问的变量。
例如，如下控制器的渲染视图代码推送2个变量到 `report` 视图：`$foo = 1` 和 `$bar = 2`。

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

拉取方式可让视图从[[yii\base\View|view component]]视图组件或其他对象中主动获得数据(如`Yii::$app`)，
在视图中使用如下表达式`$this->context`可获取到控制器ID，
可让你在`report`视图中获取控制器的任意属性或方法，如以下代码获取控制器ID。

```php
The controller ID is: <?= $this->context->id ?>
?>
```

推送方式让视图更少依赖上下文对象，是视图获取数据优先使用方式，
缺点是需要手动构建数组，有些繁琐，在不同地方渲染时容易出错。
The push approach is usually the preferred way of accessing data in views, because it makes views less dependent
on context objects. Its drawback is that you need to manually build the data array all the time, which could
become tedious and error prone if a view is shared and rendered in different places.


### 视图间共享数据 <a name="sharing-data-among-views"></a>
### Sharing Data among Views <a name="sharing-data-among-views"></a>

[[yii\base\View|view component]]视图组件提供[[yii\base\View::params|params]]参数属性来让不同视图共享数据。

例如在`about`视图中，可使用如下代码指定当前breadcrumbs的当前部分。
For example, in an `about` view, you can have the following code which specifies the current segment of the
breadcrumbs.

```php
$this->params['breadcrumbs'][] = 'About Us';
```

在[布局](#layouts)文件（也是一个视图）中，可使用依次加入到[[yii\base\View::params|params]]数组的值来
生成显示breadcrumbs:

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## 布局 <a name="layouts"></a>
## Layouts <a name="layouts"></a>

布局是一种特殊的视图，代表多个视图的公共部分，例如，大多数Web应用共享相同的页头和页尾，
在每个视图中重复相同的页头和页尾，更好的方式是将这些公共放到一个布局中，
渲染内容视图后在合适的地方嵌入到布局中。
Layouts are a special type of views that represent the common parts of multiple views. For example, the pages
for most Web applications share the same page header and footer. While you can repeat the same page header and footer
in every view, a better way is to do this once in a layout and embed the rendering result of a content view at
an appropriate place in the layout.


### 创建布局 <a name="creating-layouts"></a>
### Creating Layouts <a name="creating-layouts"></a>

由于布局也是视图，它可像普通视图一样创建，布局默认存储在`@app/views/layouts`路径下，
[模块](structure-modules.md)中使用的布局应存储在[[yii\base\Module::basePath|module directory]]模块目录
下的`views/layouts`路径下，可配置[[yii\base\Module::layoutPath]]来自定义应用或模块的布局默认路径。
Because layouts are also views, they can be created in the similar way as normal views. By default, layouts
are stored in the directory `@app/views/layouts`. For layouts used within a [module](structure-modules.md),
they should be stored in the `views/layouts` directory under the [[yii\base\Module::basePath|module directory]].
You may customize the default layout directory by configuring the [[yii\base\Module::layoutPath]] property of
the application or modules.

如下示例为一个布局大致内容，注意作为示例，简化了很多代码，
在实际中，你可能想添加更多内容，如头部标签，主菜单等。
The following example shows how a layout looks like. Note that for illustrative purpose, we have greatly simplified
the code in the layout. In practice, you may want to add more content to it, such as head tags, main menu, etc.

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string 字符串 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>My Company</header>
    <?= $content ?>
    <footer>&copy; 2014 by My Company</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

如上所示，布局生成每个页面通用的HTML标签，在`<body>`标签中，打印`$content`变量，
`$content`变量代表当[[yii\base\Controller::render()]]控制器渲染方法调用时传递到布局的内容视图渲染结果。
As you can see, the layout generates the HTML tags that are common to all pages. Within the `<body>` section,
the layout echoes the `$content` variable which represents the rendering result of content views and is pushed
into the layout when [[yii\base\Controller::render()]] is called.

大多数视图应调用上述代码中的如下方法，这些方法触发关于渲染过程的事件，
这样其他地方注册的脚本和标签会添加到这些方法调用的地方。
Most layouts should call the following methods like shown in the above code. These methods mainly trigger events
about the rendering process so that scripts and tags registered in other places can be properly injected into
the places where these methods are called.

- [[yii\base\View::beginPage()|beginPage()]]: 该方法应在布局的开始处调用，
  它触发表明页面开始的 [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] 事件。
- [[yii\base\View::endPage()|endPage()]]: 该方法应在布局的结尾处调用，
  它触发表明页面结尾的 [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] 时间。
- [[yii\web\View::head()|head()]]: 该方法应在HTML页面的`<head>`标签中调用，
  它生成一个占位符，在页面渲染结束时会被注册的头部HTML代码（如，link标签, meta标签）替换。
- [[yii\web\View::beginBody()|beginBody()]]: 该方法应在`<body>`标签的开始处调用，
  它触发 [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] 事件并生成一个占位符，
  会被注册的HTML代码（如JavaScript）在页面主体开始处替换。
- [[yii\web\View::endBody()|endBody()]]: 该方法应在`<body>`标签的结尾处调用，
  它触发 [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] 事件并生成一个占位符，
  会被注册的HTML代码（如JavaScript）在页面主体结尾处替换。


### 布局中访问数据 <a name="accessing-data-in-layouts"></a>
### Accessing Data in Layouts <a name="accessing-data-in-layouts"></a>

在布局中可访问两个预定义变量：`$this` 和 `$content`，前者对应和普通视图类似的[[yii\base\View|view]] 视图组件
后者包含调用[[yii\base\Controller::render()|render()]]方法渲染内容视图的结果。

如果想在布局中访问其他数据，必须使用[视图中访问数据](#accessing-data-in-views)一节介绍的拉取方式，
如果想从内容视图中传递数据到布局，可使用[视图间共享数据](#sharing-data-among-views)一节中的方法。
If you want to access other data in layouts, you have to use the pull method as described in
the [Accessing Data in Views](#accessing-data-in-views) subsection. If you want to pass data from a content view
to a layout, you may use the method described in the [Sharing Data among Views](#sharing-data-among-views) subsection.


### 使用布局 <a name="using-layouts"></a>
### Using Layouts <a name="using-layouts"></a>

如[控制器中渲染](#rendering-in-controllers)一节描述，当控制器调用[[yii\base\Controller::render()|render()]]
方法渲染视图时，会同时使用布局到渲染结果中，默认会使用`@app/views/layouts/main.php`布局文件。
As described in the [Rendering in Controllers](#rendering-in-controllers) subsection, when you render a view
by calling the [[yii\base\Controller::render()|render()]] method in a controller, a layout will be applied
to the rendering result. By default, the layout `@app/views/layouts/main.php` will be used. 

可配置[[yii\base\Application::layout]] 或 [[yii\base\Controller::layout]] 使用其他布局文件，
前者管理所有控制器的布局，后者覆盖前者来控制单个控制器布局。
例如，如下代码使 `post` 控制器渲染视图时使用 `@app/views/layouts/post.php` 作为布局文件，
假如 `layout` 属性没改变，控制器默认使用 `@app/views/layouts/main.php` 作为布局文件。
You may use a different layout by configuring either [[yii\base\Application::layout]] or [[yii\base\Controller::layout]].
The former governs the layout used by all controllers, while the latter overrides the former for individual controllers.
For example, the following code makes the `post` controller to use `@app/views/layouts/post.php` as the layout
when rendering its views. Other controllers, assuming their `layout` property is untouched, will still use the default
`@app/views/layouts/main.php` as the layout.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

对于模块中的控制器，可配置模块的 [[yii\base\Module::layout|layout]] 属性指定布局文件应用到模块的所有控制器。

由于`layout` 可在不同层级（控制器、模块，应用）配置，在幕后Yii使用两步来决定控制器实际使用的布局。
Because the `layout` property may be configured at different levels (controllers, modules, application),
behind the scene Yii takes two steps to determine what is the actual layout file being used for a particular controller.

第一步，它决定布局的值和上下文模块：
In the first step, it determines the layout value and the context module:

- 如果控制器的 [[yii\base\Controller::layout]] 属性不为空null，使用它作为布局的值，
  控制器的 [[yii\base\Controller::module|module]]模块 作为上下文模块。
- 如果 [[yii\base\Controller::layout|layout]] 为空，从控制器的祖先模块（包括应用） 开始找
  第一个[[yii\base\Module::layout|layout]] 属性不为空的模块，使用该模块作为上下文模块，
  并将它的[[yii\base\Module::layout|layout]] 的值作为布局的值，
  如果都没有找到，表示不使用布局。
  
第二步，它决定第一步中布局的值和上下文模块对应到实际的布局文件，布局的值可为：

- 路径别名 (如 `@app/views/layouts/main`).
- 绝对路径 (如 `/main`): 布局的值以斜杠开始，在应用的[[yii\base\Application::layoutPath|layout path] 布局路径
  中查找实际的布局文件，布局路径默认为 `@app/views/layouts`。
- 相对路径 (如 `main`): 在上下文模块的[[yii\base\Module::layoutPath|layout path]]布局路径中查找实际的布局文件，the actual layout file will be looked for under the context module's
  布局路径默认为[[yii\base\Module::basePath|module directory]]模块目录下的`views/layouts` 目录。
- 布尔值 `false`: 不使用布局。

布局的值没有包含文件扩展名，默认使用 `.php`作为扩展名。
If the layout value does not contain a file extension, it will use the default one `.php`.


### 嵌套布局 <a name="nested-layouts"></a>
### Nested Layouts <a name="nested-layouts"></a>

有时候你想嵌套一个布局到另一个，例如，在Web站点不同地方，想使用不同的布局，
同时这些布局共享相同的生成全局HTML5页面结构的基本布局，可以在子布局中调用
[[yii\base\View::beginContent()|beginContent()]] 和[[yii\base\View::endContent()|endContent()]]
方法，如下所示：

```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...child layout content here...

<?php $this->endContent(); ?>
```

如上所示，子布局内容应在 [[yii\base\View::beginContent()|beginContent()]] 和
[[yii\base\View::endContent()|endContent()]] 方法之间，传给 [[yii\base\View::beginContent()|beginContent()]]
的参数指定父布局，父布局可为布局文件或别名。

使用以上方式可多层嵌套布局。
Using the above approach, you can nest layouts in more than one levels.


### 使用数据块 <a name="using-blocks"></a>
### Using Blocks <a name="using-blocks"></a>

数据块可以在一个地方指定视图内容在另一个地方显示，通常和布局一起使用，
例如，可在内容视图中定义数据块在布局中显示它。

调用 [[yii\base\View::beginBlock()|beginBlock()]] 和 [[yii\base\View::endBlock()|endBlock()]] 来定义数据块，
使用 `$view->blocks[$blockID]` 访问该数据块，其中 `$blockID` 为定义数据块时指定的唯一标识ID。

如下实例显示如何在内容视图中使用数据块让布局使用。
The following example shows how you can use blocks to customize specific parts of a layout in a content view.

首先，在内容视图中定一个或多个数据块：
First, in a content view, define one or multiple blocks:

```php
...

<?php $this->beginBlock('block1'); ?>

...content of block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

...content of block3...

<?php $this->endBlock(); ?>
```

然后，在布局视图中，数据块可用的话会渲染数据块，如果数据未定义则显示一些默认内容。

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... default content for block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... default content for block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... default content for block3 ...
<?php endif; ?>
...
```


## 使用视图组件 <a name="using-view-components"></a>
## Using View Components <a name="using-view-components"></a>

[[yii\base\View|View components]]视图组件提供许多视图相关特性，可创建[[yii\base\View]]或它的子类实例来获取视图组件，
大多数情况下主要使用 `view` 应用组件，可在[应用配置](structure-applications.md#application-configurations)中配置该组件，
如下所示：

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

视图组件提供如下实用的视图相关特性，每项详情会在独立章节中介绍：
View components provide the following useful view-related features, each described in more details in a separate section:

* [主题](output-theming.md): 允许为你的Web站点开发和修改主题；
* [片段缓存](caching-fragment.md): 允许你在Web页面中缓存片段；
* [客户脚本处理](output-client-scripts.md): 支持CSS 和 JavaScript 注册和渲染；
* [资源包处理](structure-assets.md): 支持 [资源包](structure-assets.md)的注册和渲染；
* [模板引擎](tutorial-template-engines.md): 允许你使用其他模板引擎，如
  [Twig](http://twig.sensiolabs.org/), [Smarty](http://www.smarty.net/)。
* [theming](output-theming.md): allows you to develop and change the theme for your Web site.
* [fragment caching](caching-fragment.md): allows you to cache a fragment within a Web page.
* [client script handling](output-client-scripts.md): supports CSS and JavaScript registration and rendering.
* [asset bundle handling](structure-assets.md): supports registering and rendering of [asset bundles](structure-assets.md).
* [alternative template engines](tutorial-template-engines.md): allows you to use other template engines, such as
  [Twig](http://twig.sensiolabs.org/), [Smarty](http://www.smarty.net/).

开发Web页面时，也可能频繁使用以下实用的小特性。
You may also frequently use the following minor yet useful features when you are developing Web pages.


### 设置页面标题 <a name="setting-page-titles"></a>
### Setting Page Titles <a name="setting-page-titles"></a>

每个Web页面应有一个标题，正常情况下标题的标签显示在 [布局](#layouts)中，
但是实际上标题大多由内容视图而不是布局来决定，为解决这个问题， [[yii\web\View]] 提供
[[yii\web\View::title|title]] 标题属性可让标题信息从内容视图传递到布局中。
Every Web page should have a title. Normally the title tag is being displayed in a [layout](#layouts). However, in practice
the title is often determined in content views rather than layouts. To solve this problem, [[yii\web\View]] provides
the [[yii\web\View::title|title]] property for you to pass the title information from content views to layouts.

为利用这个特性，在每个内容视图中设置页面标题，如下所示：
To make use of this feature, in each content view, you can set the page title like the following:

```php
<?php
$this->title = 'My page title';
?>
```

然后在视图中，确保在 `<head>` 段中有如下代码：
Then in the layout, make sure you have the following code in the `<head>` section:

```php
<title><?= Html::encode($this->title) ?></title>
```


### 注册Meta元标签 <a name="registering-meta-tags"></a>
### Registering Meta Tags <a name="registering-meta-tags"></a>

Web页面通常需要生成各种元标签提供给不同的浏览器，如`<head>`中的页面标题，元标签通常在布局中生成。

如果想在内容视图中生成元标签，可在内容视图中调用[[yii\web\View::registerMetaTag()]]方法，如下所示：

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

以上代码会在视图组件中注册一个 "keywords" 元标签，在布局渲染后会渲染该注册的元标签，
然后，如下HTML代码会插入到布局中调用[[yii\web\View::head()]]方法处：

```php
<meta name="keywords" content="yii, framework, php">
```

注意如果多次调用 [[yii\web\View::registerMetaTag()]] 方法，它会注册多个元标签，注册时不会检查是否重复。

为确保每种元标签只有一个，可在调用方法时指定键作为第二个参数，
例如，如下代码注册两次 "description" 元标签，但是只会渲染第二个。
To make sure there is only a single instance of a meta tag type, you can specify a key as a second parameter when calling the method.
For example, the following code registers two "description" meta tags. However, only the second one will be rendered.

```html
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'description');
```


### 注册链接标签 <a name="registering-link-tags"></a>
### Registering Link Tags <a name="registering-link-tags"></a>

和 [Meta标签](#adding-meta-tags) 类似，链接标签有时很使用，如自定义网站图标，指定Rss订阅，或授权OpenID到其他服务器。
可以和元标签相似的方式调用[[yii\web\View::registerLinkTag()]]，例如，在内容视图中注册链接标签如下所示：

```php
$this->registerLinkTag([
    'title' => 'Live News for Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

上述代码会转换成
The code above will result in

```html
<link title="Live News for Yii" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/">
```

和 [[yii\web\View::registerMetaTag()|registerMetaTags()]] 类似，
调用[[yii\web\View::registerLinkTag()|registerLinkTag()]] 指定键来避免生成重复链接标签。


## 视图事件 <a name="view-events"></a>
## View Events <a name="view-events"></a>

[[yii\base\View|View components]] 视图组件会在视图渲染过程中触发几个事件，
可以在内容发送给终端用户前，响应这些事件来添加内容到视图中或调整渲染结果。

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: 在控制器渲染文件开始时触发，
  该事件可设置 [[yii\base\ViewEvent::isValid]] 为 false 取消视图渲染。
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: 在布局中调用 [[yii\base\View::beginPage()]] 时触发，
  该事件可获取[[yii\base\ViewEvent::output]]的渲染结果，可修改该属性来修改渲染结果。
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: 在布局调用 [[yii\base\View::beginPage()]] 时触发；
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: 在布局调用 [[yii\base\View::endPage()]] 是触发；
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: 在布局调用 [[yii\web\View::beginBody()]] 时触发；
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: 在布局调用 [[yii\web\View::endBody()]] 时触发。
- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: triggered at the beginning of rendering a file
  in a controller. Handlers of this event may set [[yii\base\ViewEvent::isValid]] to be false to cancel the rendering process.
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: triggered by the call of [[yii\base\View::beginPage()]] in layouts.
  Handlers of this event may obtain the rendering result through [[yii\base\ViewEvent::output]] and may modify
  this property to change the rendering result.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: triggered by the call of [[yii\base\View::beginPage()]] in layouts.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: triggered by the call of [[yii\base\View::endPage()]] in layouts.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: triggered by the call of [[yii\web\View::beginBody()]] in layouts.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: triggered by the call of [[yii\web\View::endBody()]] in layouts.

例如，如下代码将当前日期添加到页面结尾处：
For example, the following code injects the current date at the end of the page body:

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## 渲染静态页面 <a name="rendering-static-pages"></a>
## Rendering Static Pages <a name="rendering-static-pages"></a>

静态页面值得是大部分内容为静态的不需要控制器传递动态数据的Web页面。
Static pages refer to those Web pages whose main content are mostly static without the need of accessing
dynamic data pushed from controllers.

可将HTML代码放置在视图中，在控制器中使用以下代码输出静态页面：
You can output static pages by putting their code in the view, and then using the code like the following in a controller:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

如果Web站点包含很多静态页面，多次重复相似的代码显得很繁琐，
为解决这个问题，可以使用一个在控制器中称为 [[yii\web\ViewAction]] 的[独立操作](structure-controllers.md#standalone-actions)。
例如：
If a Web site contains many static pages, it would be very tedious repeating the similar code many times.
To solve this problem, you may introduce a [standalone action](structure-controllers.md#standalone-actions)
called [[yii\web\ViewAction]] in a controller. For example,

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```

现在如果你在`@app/views/site/pages`目录下创建名为 `about` 的视图，
可通过如下rul显示该视图：

```
http://localhost/index.php?r=site/page&view=about
```

`GET` 中 `view` 参数告知 [[yii\web\ViewAction]] 操作请求哪个视图，然后操作在
`@app/views/site/pages`目录下寻找该视图，可配置 [[yii\web\ViewAction::viewPrefix]]
修改搜索视图的目录。


## 最佳实践 <a name="best-practices"></a>
## Best Practices <a name="best-practices"></a>

视图负责将模型的数据展示用户想要的格式，总之，视图
Views are responsible for presenting models in the format that end users desire. In general, views

* 应主要包含展示代码，如HTML, 和简单的PHP代码来控制、格式化和渲染数据；
* 不应包含执行数据查询代码，这种代码放在模型中；
* 应避免直接访问请求数据，如 `$_GET`, `$_POST`，这种应在控制器中执行，
  如果需要请求数据，应由控制器推送到视图。
* 可读取模型属性，但不应修改它们。
* should mainly contain presentational code, such as HTML, and simple PHP code to traverse, format and render data.
* should not contain code that performs DB queries. Such code should be done in models.
* should avoid direct access to request data, such as `$_GET`, `$_POST`. This belongs to controllers.
  If request data is needed, they should be pushed into views by controllers.
* may read model properties, but should not modify them.

为使模型更易于维护，避免创建太复杂或包含太多冗余代码的视图，可遵循以下方法达到这个目标：

* 使用 [布局](#layouts) 来展示公共代码（如，页面头部、尾部）；
* 将复杂的视图分成几个小视图，可使用上面描述的渲染方法将这些小视图渲染并组装成大视图；
* 创建并使用 [小部件](structure-widgets.md) 作为视图的数据块；
* 创建并使用助手类在视图中转换和格式化数据。
* use [layouts](#layouts) to represent common presentational sections (e.g. page header, footer).
* divide a complicated view into several smaller ones. The smaller views can be rendered and assembled into a bigger
  one using the rendering methods that we have described.
* create and use [widgets](structure-widgets.md) as building blocks of views.
* create and use helper classes to transform and format data in views.

