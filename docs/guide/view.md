View
====

The view component is an important part of MVC. The view acts as the interface to the application, making it responsible
for presenting data to end users, displaying forms, and so forth.


Basics
------

By default, Yii uses PHP in view templates to generate content and elements. A web application view typically contains
some combination of HTML, along with PHP `echo`, `foreach`, `if`, and other basic constructs.
Using complex PHP code in views is considered to be bad practice. When complex logic and functionality is needed,
such code should either be moved to a controller or a widget.

The view is typically called from controller action using the [[yii\base\Controller::render()|render()]] method:

```php
public function actionIndex()
{
    return $this->render('index', ['username' => 'samdark']);
}
```

The first argument to [[yii\base\Controller::render()|render()]] is the name of the view to display.
In the context of the controller, Yii will search for its views in `views/site/` where `site`
is the controller ID. For details on how the view name is resolved, refer to the [[yii\base\Controller::render()]] method.

The second argument to [[yii\base\Controller::render()|render()]] is a data array of key-value pairs.
Through this array, data can be passed to the view, making the value available in the view as a variable
named the same as the corresponding key.

The view for the action above would be `views/site/index.php` and can be something like:

```php
<p>Hello, <?= $username ?>!</p>
```

Any data type can be passed to the view, including arrays or objects.

Besides the above [[yii\web\Controller::render()|render()]] method, the [[yii\web\Controller]] class also provides
several other rendering methods. Below is a summary of these methods:

* [[yii\web\Controller::render()|render()]]: renders a view and applies the layout to the rendering result.
  This is most commonly used to render a complete page.
* [[yii\web\Controller::renderPartial()|renderPartial()]]: renders a view without applying any layout.
  This is often used to render a fragment of a page.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: renders a view without applying any layout, and injects all
  registered JS/CSS scripts and files. It is most commonly used to render an HTML output to respond to an AJAX request.
* [[yii\web\Controller::renderFile()|renderFile()]]: renders a view file. This is similar to
  [[yii\web\Controller::renderPartial()|renderPartial()]] except that it takes the file path
  of the view instead of the view name.


Widgets
-------

Widgets are self-contained building blocks for your views, a way to combine complex logic, display, and functionality into a single component. A widget:

* May contain advanced PHP programming
* Is typically configurable
* Is often provided data to be displayed
* Returns HTML to be shown within the context of the view

There are a good number of widgets bundled with Yii, such as [active form](form.md),
breadcrumbs, menu, and [wrappers around bootstrap component framework](bootstrap-widgets.md). Additionally there are
extensions that provide more widgets, such as the official widget for [jQueryUI](http://www.jqueryui.com) components.

In order to use a widget, your view file would do the following:

```php
// Note that you have to "echo" the result to display it
echo \yii\widgets\Menu::widget(['items' => $items]);

// Passing an array to initialize the object properties
$form = \yii\widgets\ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... form inputs here ...
\yii\widgets\ActiveForm::end();
```

In the first example in the code above, the [[yii\base\Widget::widget()|widget()]] method is used to invoke a widget
that just outputs content. In the second example, [[yii\base\Widget::begin()|begin()]] and [[yii\base\Widget::end()|end()]]
are used for a
widget that wraps content between method calls with its own output. In case of the form this output is the `<form>` tag
with some properties set.


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
    <footer class="footer">© 2013 me :)</footer>
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


### Accessing context

Views are generally used either by controller or by widget. In both cases the object that called view rendering is
available in the view as `$this->context`. For example if we need to print out the current internal request route in a
view rendered by controller we can use the following:

```php
echo $this->context->getRoute();
```

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
