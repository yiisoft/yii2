View
====

View is an important part of MVC and is reponsible for how data is presented to the end user.

Basics
------

Yii uses PHP in view templates by default so in a web application a view typically contains some HTML, `echo`, `foreach`
and such basic constructs. It may also contain widget calls. Using complex code in views is considered a bad practice.
Such code should be moved to controller or widgets.

View is typically called from controller action like the following:

```php
public function actionIndex()
{
	return $this->render('index', array(
		'username' => 'samdark',
	));
}
```

First argument is the view name. In context of the controller Yii will search for its views in `views/site/` where `site`
is controller ID. For details on how view name is resolved please refer to [yii\base\Controller::render] method.
Second argument is data array that contains key-value pairs. Value is available in the view as a variable named the same
as the corresponding key.

So the view for the action above should be in `views/site/index.php` and can be something like:

```php
<p>Hello, <?php echo $username?>!</p>
```

Intead of just scalar values you can pass anything else such as arrays or objects.

Widgets
-------

Security
--------

One of the main security principles is to always escape output. If violated it leads to script execution and,
most probably, to cross-site scripting known as XSS leading to leaking of admin passwords, making a user to automatically
perform actions etc.

Yii provides a good toolset in order help you escaping your output. The very basic thing to escape is a text without any
markup. You can deal with it like the following:

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
	<?php echo Html::encode($user->name); ?>
</div>
```

When you want to render HTML it becomes complex so we're delegating the task to excellent
[HTMLPurifier](http://htmlpurifier.org/) library. In order to use it you need to modify your `composer.json` first by
adding the following to `require`:

```javascript
"ezyang/htmlpurifier": "v4.5.0"
```

After it's done run `php composer.phar install` and wait till package is downloaded. Now everything is prepared to use
Yii's HtmlPurifier helper:

```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
	<?php echo HtmlPurifier::process($post->text); ?>
</div>
```

Note that besides HTMLPurifier does excellent job making output safe it's not very fast so consider
[caching result](caching.md).

Alternative template languages
------------------------------

There are offlicial extensions for [Smarty](http://www.smarty.net/) and [Twig](http://twig.sensiolabs.org/). In order
to learn more refer to [Using template engines](template.md) section of the guide.

Using View object
-----------------

An instance of `yii\base\View` is available in view templates as `$this` variable. Using it you can do many useful things
including setting page title and meta, registering scripts and accessing the context.

### Setting page title

A common place to set page title are view templates. Since we can access view object with `$this`, setting a title
becomes as easy as:

```php
$this->title = 'My page title';
```

### Adding meta tags

Adding meta tags such as encodig, description, keywords is easy with view object as well:

```php
$this->registerMetaTag(array('encoding' => 'utf-8'));
```

The first argument is an map of `<meta>` tag option names and values. The code above will produce:

```html
<meta encoding="utf-8">
```

Sometimes there's a need to have only a single tag of a type. In this case you need to specify the second argument:

```html
$this->registerMetaTag(array('description' => 'This is my cool website made with Yii!'), 'meta-description');
$this->registerMetaTag(array('description' => 'This website is about funny raccoons.'), 'meta-description');
```

If there are multiple calls with the same value of the second argument (`meta-description` in this case), the latter will
override the former and only a single tag will be rendered:

```html
<meta description="This website is about funny raccoons.">
```

### Registering link tags

`<link>` tag is useful in many cases such as customizing favicon, ponting to RSS feed or delegating OpenID to another
server. Yii view object has a method to work with these:

```php
$this->registerLinkTag(array(
	'title' => 'Lives News for Yii Framework',
	'rel' => 'alternate',
	'type' => 'application/rss+xml',
	'href' => 'http://www.yiiframework.com/rss.xml/',
));
```

The code above will result in

```html
<link title="Lives News for Yii Framework" rel="alternate" type="application/rss+xml" href="http://www.yiiframework.com/rss.xml/" />
```

Same as with meta tags you can specify additional argument to make sure there's only one link of a type registered.

### Registering CSS

You can register CSS using `registerCss` or `registerCssFile`. Former is for outputting code in `<style>` tags directly
to the page which is not recommended in most cases (but still valid). Latter is for registering CSS file. In Yii it's
much better to [use asset manager](assets.md) to deal with these since it provides extra features so `registerCssFile`
is manly useful for external CSS files.

```php
$this->registerCss("body { background: #f00; }");
```

The code above will result in adding the following to the head section of the page:

```html
<style>
body { background: #f00; }
</style>
```

If you want to specify additional properties of the style tag, pass array of name-values to the second argument. If you
need to make sure there's only a single style tag use third argument as was mentioned in meta tags description.

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", array('media' => 'print'), 'css-print-theme');
```

The code above will add a link to CSS file to the head section of the page. The CSS will be used only when printing the
page. We're using third argument so one of the views could override it.

### Registering scripts




### Registering asset bundles

As was mentioned earlier it's preferred to use asset bundles instead of using CSS and JavaScript directly. You can get
details on how to define asset bundles in [asset manager](assets.md) section of the guide. As for using already defined
asset bundle, it's very straightforward:

```php
frontend\config\AppAsset::register($this);
```

### Layout

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
	<h2><?php echo Html::encode($username); ?></h2>
	<p><?php echo Html::encode($tagline); ?></p>
</div>
```

Then we're using it in `index.php` view where we display a list of users:

```php
<div class="user-index">
	<?php
	foreach($users as $user) {
		echo $this->render('_profile', array(
			'username' => $user->name,
			'tagline' => $user->tagline,
		));
	}
	?>
</div>
```

Same way we can reuse it in another view displaying a single user profile:

```php
echo $this->render('_profile', array(
	'username' => $user->name,
	'tagline' => $user->tagline,
));
```

### Accessing context

Views are generally used either by controller or by widget. In both cases the object that called view rendering is
available in the view as `$this->context`. For example if we need to print out the current internal request route in a
view rendered by controller we can use the following:

```php
echo $this->context->getRoute();
```

### Caching blocks


