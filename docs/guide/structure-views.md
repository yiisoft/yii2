Views
=====

Views are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are code responsible for presenting data to end users. In a Web application, views are usually created
in terms of *view templates* which are PHP script files containing mainly HTML code and presentational PHP code.
They are managed by the [[yii\web\View|view]] application component which provides commonly used methods
to facilitate view composition and rendering. For simplicity, we often call view templates or view template files
as views.


## Creating Views

As aforementioned, a view is simply a PHP script mixed with HTML and PHP code. The following is the view
that presents a login form. As you can see, PHP code is used to generate the dynamic content, such as the
page title and the form, while HTML code organizes them into a presentable HTML page.

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\LoginForm $model
 */
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

Within a view, you can access `$this` which refers to the [[yii\web\View|view component]] managing
and rendering this view template.

Besides `$this`, there may be other predefined variables in a view, such as `$form` and `$model` in the above
example. These variables represent the data that are *pushed* into the view by [controllers](structure-controllers.md)
or other objects whose trigger the [view rendering](#rendering-views).

> Tip: The predefined variables are listed in a comment block at beginning of a view so that they can
  be recognized by IDEs. It is also a good practice to document your views.

TODO: features in creating views




## Organizing Views

Like [controllers](structure-controllers.md) and [models](structure-models.md), there are conventions to organize views.

* For views rendered in a controller, they should be put under the directory `@app/views/ControllerID` by default,
  where `ControllerID` refers to [the ID of the controller](structure-controllers.md#routes). For example, if
  the controller class is `PostController`, the directory would be `@app/views/post`; If the class is `PostCommentController`,
  the directory would be `@app/views/post-comment`. In case the controller belongs to a module, the directory
  would be `views/ControllerID` under the [[yii\base\Module::basePath|module directory]].
* For views rendered in a [widget](structure-widgets.md), they should be put under the `WidgetPath/views` directory by
  default, where `WidgetPath` stands for the directory containing the widget class file.
* For views rendered by other objects, it is recommended that you follow the similar convention as that for widgets.

You may customize these default view directories by overriding the [[yii\base\ViewContextInterface::getViewPath()]]
method of controllers or widgets.


## Rendering Views

You can render views in [controllers](structure-controllers.md), [widgets](structure-widgets.md), or any
other places by calling view rendering methods. These methods share a similar signature shown as follows,

```
/**
 * @param string $view view name or file path, depending on the actual rendering method
 * @param array $params the data to be passed to the view
 * @return string rendering result
 */
methodName($view, $params = [])
```


### Rendering in Controllers

Within [controllers](structure-controllers.md), you may call the following controller methods to render views:

* [[yii\base\Controller::render()|render()]]: renders a [named view](#named-views) and applies a [layout](#layouts)
  to the rendering result.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: renders a [named view](#named-views) without any layout.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renders a [named view](#named-views) without any layout,
  and injects all registered JS/CSS scripts and files. It is usually used in response to AJAX Web requests.
* [[yii\base\Controller::renderFile()|renderFile()]]: renders a view specified in terms of a view file path or
  [alias](concept-aliases.md).

For example,

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

        // renders a view named "view" and applies a layout to it
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### Rendering in Widgets

Within [widgets](structure-widgets.md), you may call the following widget methods to render views.

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
        // renders a view named "list"
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### Rendering in Other Places

In any place, you can render views with the help of the [[yii\base\View|view]] application component by calling
its following methods:

* [[yii\base\View::render()|render()]]: renders a [named view](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: renders a [named view](#named-views) and injects all registered
  JS/CSS scripts and files. It is usually used in response to AJAX Web requests.
* [[yii\base\View::renderFile()|renderFile()]]: renders a view specified in terms of a view file path or
  [alias](concept-aliases.md).

For example,

```php
// displays the view file "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```

If you are rendering a view within another view, you can use the following code, because `$this` in a view refers to
the [[yii\base\View|view]] component:

```php
<?= $this->renderFile('@app/views/site/license.php') ?>
```


## Named Views

When you render a view, you can specify the view using either a view name or a view file path/alias. In most cases,
you would use the former because it is more concise and flexible. We call views specified using names as *named views*.

A view name is resolved into the corresponding view file path according to the following rules:

* A view name may omit the file extension name. In this case, `.php` will be used as the extension. For example,
  the view name `about` corresponds to the file name `about.php`.
* If the view name starts with double slashes `//`, the corresponding view file path would be `@app/views/ViewName`.
  That is, the view is looked for under the [[yii\base\Application::viewPath|application's view path]].
  For example, `//site/about` will be resolved into `@app/views/site/about.php`.
* If the view name starts with a single slash `/`, the view file path is formed by prefixing the view name
  with the [[yii\base\Module::viewPath|view path]] of the currently active [module](structure-modules.md).
  If there is no active module, `@app/views/ViewName` will be used. For example, `/user/create` will be resolved into
  `@app/modules/user/views/user/create.php`, if the currently active module is `user`. If there is no active module,
  the view file path would be `@app/views/user/create.php`.
* If the view is rendered with a [[yii\base\View::context|context]] and the context implements [[yii\base\ViewContextInterface]],
  the view file path is formed by prefixing the [[yii\base\ViewContextInterface::getViewPath()|view path]] of the
  context to the view name. This mainly applies to the views rendered within controllers and widgets. For example,
  `site/about` will be resolved into `@app/views/site/about.php` if the context is the controller `SiteController`.
* If a view is rendered within another view, the directory containing the other view file will be prefixed to
  the new view name to form the actual view file path. For example, `item` will be resolved into `@app/views/post/item`
  if it is being rendered in the view `@app/views/post/index.php`.

According to the above rules, the following code in a controller is actually rendering the view file
`@app/views/post/view.php`

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

        // renders a view named "view" and applies a layout to it
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```

And the following code in the view `@app/views/post/view.php` is actually rendering the view file
`@app/views/post/_overview.php`:

```php
<?= $this->render('_overview', ['model' => $model]) ?>
```


### Accessing Data in Views

There are two approaches to access data within a view: push and pull.

By passing the data as the second parameter to the view rendering methods, you are using the push approach.
The data should be represented be an array of name-value pairs. When the view is being rendered, the PHP
`extract()` function will be called on this array so that the array is extracted into variables in the view.
For example, the following view rendering code in a controller will push two variables to the `report` view:
`$foo = 1` and `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

The pull approach actively retrieves data from the [[yii\base\View::context|view context object]]. Using the above
code as an example, within the view you can get the controller object by the expression `$this->context`.
As a result, it is possible for you to access any properties or methods of the controller in the `report` view.
For example, in the `report` view you may pull the `id` data like the following:

```php
The controller ID is: <?= $this->context->id ?>
?>
```

The pull approach is usually the preferred way of accessing data in views, because it makes views less dependent
on context objects. Its drawback is that you need to manually build the data array all the time, which could
becomes tedious and error prone if a view is shared and rendered in different places.


## Layouts

Layouts are a special type of views that represent the common parts of multiple views. For example, the pages
for most Web applications share the same page header and footer. While you can repeat the same page header and footer
in every view, a better way is to do this once in a layout and embed the rendering result of a content view at
an appropriate place in the layout.


### Creating Layouts

Because layouts are also views, they can be created in the similar way as normal views. The following example
shows how a layout looks like:

```php
<?php
use yii\helpers\Html;
/**
 * @var yii\web\View $this
 * @var string $content
 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <div class="container">
        <?= $content ?>
    </div>
    <footer class="footer">&copy; 2014 by me :)</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

The above layout is used to generate HTML pages. It generates HTML tags that are common to all pages. You may
also generate other common HTML tags in the layout, such as head tags, main menu, etc.

Within a layout, you have access to a special variable named `$content`. This is the only variable injected into
the layout by the controller when the [[yii\base\Controller::render()]] method is called to render a view.
The value of `$content` represents the rendering result of the view. As you can see in the above code,
`$content` is embedded within the body part of the layout.

Besides `$content`, you can also access the [[yii\base\View|view]] component via `$this`, like in normal views.


### Organizing Layouts




### Using Layouts

A layout is applied when you call the [[yii\base\Controller::render()|render()]] method in a controller. The method
will first render the view being requested; it will then render the layout specified by the [[yii\base\Controller::layout]]
property of the controller and push the rendering result of the view into the layout as a variable `$content`.



### View Events


The [[yii\base\View|view]] component provides several *placeholder* methods, such as `head()` and `beginBody()`,
which generate placeholders which will be replaced later by



 code shows a typical layout
A layout is a very convenient way to represent the part of the page that is common for all or at least for most pages
generated by your application. Typically it includes `<head>` section, footer, main menu and alike elements.
You can find a fine example of the layout in a [basic application template](apps-basic.md). Here we'll review the very
basic one without any widgets or extra markup.


In the markup above there's some code. First of all, `$content` is a variable that will contain result of views rendered
with controller's `$this->render()` method.

We are importing [[yii\helpers\Html|Html]] helper via standard PHP `use` statement. This helper is typically used for almost all views
where one need to escape outputted data.

Several special methods such as [[yii\web\View::beginPage()|beginPage()]]/[[yii\web\View::endPage()|endPage()]],
[[yii\web\View::head()|head()]], [[yii\web\View::beginBody()|beginBody()]]/[[yii\web\View::endBody()|endBody()]]
are triggering page rendering events that are used for registering scripts, links and process page in many other ways.
Always include these in your layout in order for the rendering to work correctly.

By default layout is loaded from `views/layouts/main.php`. You may change it at controller or module level by setting
different value to `layout` property.

In order to pass data from controller to layout, that you may need for breadcrumbs or similar elements, use view component
params property. In controller it can be set as:

```php
$this->view->params['breadcrumbs'][] = 'Contact';
```

In a view it will be:

```php
$this->params['breadcrumbs'][] = 'Contact';
```

In layout file the value can be used like the following:

```php
<?= Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```

You may also wrap the view render result into a layout using [[yii\base\View::beginContent()]], [[yii\base\View::endContent()]].
This approach can be used while applying nested layouts:

```php
<?php $this->beginContent('//layouts/overall') ?>
<div class="content">
    <?= $content ?>
<div>
<?php $this->endContent() ?>
```

### Nested Layouts

### Accessing Data in Layouts


## View Components

### Setting page title
### Adding meta tags
### Registering link tags
### Registering CSS
### Registering scripts
### Static Pages
### Assets
### Alternative Template Engines


### Rendering Static Pages

Static pages refer to those Web pages whose main content are mostly static without the need of accessing
dynamic data pushed from controllers.

You can generate static pages using the code like the following in a controller:

```php
public function actionAbout()
{
    return $this->render('about');
}
```

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

Now if you create a view named `about` under the directory `@app/views/site/pages`, you will be able to
display this view by the following URL:

```
http://localhost/index.php?r=site/page&view=about
```

The `GET` parameter `view` tells [[yii\web\ViewAction]] which view is requested. The action will then look
for this view under the directory `@app/views/site/pages`. You may configure [[yii\web\ViewAction::viewPrefix]]
to change the directory for searching these views.


## Best Practices



Alternative template languages
------------------------------

There are official extensions for [Smarty](http://www.smarty.net/) and [Twig](http://twig.sensiolabs.org/). In order
to learn more refer to [Using template engines](template.md) section of the guide.

Using View object in templates
------------------------------

An instance of [[yii\web\View]] component is available in view templates as `$this` variable. Using it in templates you
can do many useful things including setting page title and meta, registering scripts and accessing the context.

### Setting page title

A common place to set page title are view templates. Since we can access view object with `$this`, setting a title
becomes as easy as:

```php
$this->title = 'My page title';
```

### Adding meta tags

Adding meta tags such as encoding, description, keywords is easy with view object as well:

```php
$this->registerMetaTag(['encoding' => 'utf-8']);
```

The first argument is an map of `<meta>` tag option names and values. The code above will produce:

```html
<meta encoding="utf-8">
```

Sometimes there's a need to have only a single tag of a type. In this case you need to specify the second argument:

```html
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'meta-description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'meta-description');
```

If there are multiple calls with the same value of the second argument (`meta-description` in this case), the latter will
override the former and only a single tag will be rendered:

```html
<meta name="description" content="This website is about funny raccoons.">
```

### Registering link tags

`<link>` tag is useful in many cases such as customizing favicon, pointing to RSS feed or delegating OpenID to another
server. Yii view object has a method to work with these:

```php
$this->registerLinkTag([
    'title' => 'Lives News for Yii Framework',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'http://www.yiiframework.com/rss.xml/',
]);
```

The code above will result in

```html
<link title="Lives News for Yii Framework" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/" />
```

Same as with meta tags you can specify additional argument to make sure there's only one link of a type registered.

### Registering CSS

You can register CSS using [[yii\web\View::registerCss()|registerCss()]] or [[yii\web\View::registerCssFile()|registerCssFile()]].
The former registers a block of CSS code while the latter registers an external CSS file. For example,

```php
$this->registerCss("body { background: #f00; }");
```

The code above will result in adding the following to the head section of the page:

```html
<style>
body { background: #f00; }
</style>
```

If you want to specify additional properties of the style tag, pass an array of name-values to the third argument.
If you need to make sure there's only a single style tag use fourth argument as was mentioned in meta tags description.

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", [BootstrapAsset::className()], ['media' => 'print'], 'css-print-theme');
```

The code above will add a link to CSS file to the head section of the page.

* The first argument specifies the CSS file to be registered.
* The second argument specifies that this CSS file depends on [[yii\bootstrap\BootstrapAsset|BootstrapAsset]], meaning it will be added
  AFTER the CSS files in [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]. Without this dependency specification, the relative order
  between this CSS file and the [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] CSS files would be undefined.
* The third argument specifies the attributes for the resulting `<link>` tag.
* The last argument specifies an ID identifying this CSS file. If it is not provided, the URL of the CSS file will be
  used instead.


It is highly recommended that you use [asset bundles](assets.md) to register external CSS files rather than
using [[yii\web\View::registerCssFile()|registerCssFile()]]. Using asset bundles allows you to combine and compress
multiple CSS files, which is desirable for high traffic websites.


### Registering scripts

With the [[yii\web\View]] object you can register scripts. There are two dedicated methods for it:
[[yii\web\View::registerJs()|registerJs()]] for inline scripts and
[[yii\web\View::registerJsFile()|registerJsFile()]] for external scripts.
Inline scripts are useful for configuration and dynamically generated code.
The method for adding these can be used as follows:

```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

The first argument is the actual JS code we want to insert into the page. The second argument
determines where script should be inserted into the page. Possible values are:

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] for head section.
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] for right after opening `<body>`.
- [[yii\web\View::POS_END|View::POS_END]] for right before closing `</body>`.
- [[yii\web\View::POS_READY|View::POS_READY]] for executing code on document `ready` event. This will register [[yii\web\JqueryAsset|jQuery]] automatically.
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] for executing code on document `load` event. This will register [[yii\web\JqueryAsset|jQuery]] automatically.

The last argument is a unique script ID that is used to identify code block and replace existing one with the same ID
instead of adding a new one. If you don't provide it, the JS code itself will be used as the ID.

An external script can be added like the following:

```php
$this->registerJsFile('http://example.com/js/main.js', [JqueryAsset::className()]);
```

The arguments for [[yii\web\View::registerJsFile()|registerJsFile()]] are similar to those for
[[yii\web\View::registerCssFile()|registerCssFile()]]. In the above example,
we register the `main.js` file with the dependency on `JqueryAsset`. This means the `main.js` file
will be added AFTER `jquery.js`. Without this dependency specification, the relative order between
`main.js` and `jquery.js` would be undefined.

Like for [[yii\web\View::registerCssFile()|registerCssFile()]], it is also highly recommended that you use
[asset bundles](assets.md) to register external JS files rather than using [[yii\web\View::registerJsFile()|registerJsFile()]].


### Registering asset bundles

As was mentioned earlier it's preferred to use asset bundles instead of using CSS and JavaScript directly. You can get
details on how to define asset bundles in [asset manager](assets.md) section of the guide. As for using already defined
asset bundle, it's very straightforward:

```php
\frontend\assets\AppAsset::register($this);
```

### Layout

A layout is a very convenient way to represent the part of the page that is common for all or at least for most pages
generated by your application. Typically it includes `<head>` section, footer, main menu and alike elements.
You can find a fine example of the layout in a [basic application template](apps-basic.md). Here we'll review the very
basic one without any widgets or extra markup.

```php
<?php
use yii\helpers\Html;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <div class="container">
        <?= $content ?>
    </div>
    <footer class="footer">&copy; 2013 me :)</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

In the markup above there's some code. First of all, `$content` is a variable that will contain result of views rendered
with controller's `$this->render()` method.

We are importing [[yii\helpers\Html|Html]] helper via standard PHP `use` statement. This helper is typically used for almost all views
where one need to escape outputted data.

Several special methods such as [[yii\web\View::beginPage()|beginPage()]]/[[yii\web\View::endPage()|endPage()]],
[[yii\web\View::head()|head()]], [[yii\web\View::beginBody()|beginBody()]]/[[yii\web\View::endBody()|endBody()]]
are triggering page rendering events that are used for registering scripts, links and process page in many other ways.
Always include these in your layout in order for the rendering to work correctly.

By default layout is loaded from `views/layouts/main.php`. You may change it at controller or module level by setting
different value to `layout` property.

In order to pass data from controller to layout, that you may need for breadcrumbs or similar elements, use view component
params property. In controller it can be set as:

```php
$this->view->params['breadcrumbs'][] = 'Contact';
```

In a view it will be:

```php
$this->params['breadcrumbs'][] = 'Contact';
```

In layout file the value can be used like the following:

```php
<?= Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```

You may also wrap the view render result into a layout using [[yii\base\View::beginContent()]], [[yii\base\View::endContent()]].
This approach can be used while applying nested layouts:

```php
<?php $this->beginContent('//layouts/overall') ?>
<div class="content">
    <?= $content ?>
<div>
<?php $this->endContent() ?>
```

### Partials

Often you need to reuse some HTML markup in many views and often it's too simple to create a full-featured widget for it.
In this case you may use partials.

Partial is a view as well. It resides in one of directories under `views` and by convention is often started with `_`.
For example, we need to render a list of user profiles and, at the same time, display individual profile elsewhere.

First we need to define a partial for user profile in `_profile.php`:

```php
<?php
use yii\helpers\Html;
?>

<div class="profile">
    <h2><?= Html::encode($username) ?></h2>
    <p><?= Html::encode($tagline) ?></p>
</div>
```

Then we're using it in `index.php` view where we display a list of users:

```php
<div class="user-index">
    <?php
    foreach ($users as $user) {
        echo $this->render('_profile', [
            'username' => $user->name,
            'tagline' => $user->tagline,
        ]);
    }
    ?>
</div>
```

Same way we can reuse it in another view displaying a single user profile:

```php
echo $this->render('_profile', [
    'username' => $user->name,
    'tagline' => $user->tagline,
]);
```


When you call `render()` to render a partial in a current view, you may use different formats to refer to the partial.
The most commonly used format is the so-called relative view name which is as shown in the above example.
The partial view file is relative to the directory containing the current view. If the partial is located under
a subdirectory, you should include the subdirectory name in the view name, e.g., `public/_profile`.

You may use path alias to specify a view, too. For example, `@app/views/common/_profile`.

And you may also use the so-called absolute view names, e.g., `/user/_profile`, `//user/_profile`.
An absolute view name starts with a single slashes or double slashes. If it starts with a single slash,
the view file will be looked for under the view path of the currently active module. Otherwise, it will
will be looked for under the application view path.




### Caching blocks

To learn about caching of view fragments please refer to [caching](caching.md) section of the guide.

Customizing View component
--------------------------

Since view is also an application component named `view` you can replace it with your own component that extends
from [[yii\base\View]] or [[yii\web\View]]. It can be done via application configuration file such as `config/web.php`:

```php
return [
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
];
```


Security
--------

One of the main security principles is to always escape output. If violated it leads to script execution and,
most probably, to cross-site scripting known as XSS leading to leaking of admin passwords, making a user to automatically
perform actions etc.

Yii provides a good tool set in order to help you escape your output. The very basic thing to escape is a text without any
markup. You can deal with it like the following:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

When you want to render HTML it becomes complex so we're delegating the task to excellent
[HTMLPurifier](http://htmlpurifier.org/) library which is wrapped in Yii as a helper [[yii\helpers\HtmlPurifier]]:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

Note that besides HTMLPurifier does excellent job making output safe it's not very fast so consider
[caching result](caching.md).
