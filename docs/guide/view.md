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

Layout
------

Partials
--------


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
