URL Management
==============

The concept of URL management in Yii fairly simple. URL management is based on the premise that the application uses internal routes and parameters
everywhere. The framework itself will then translates routes into URLs, and translate URLs into routes, according to the URL manager's configuration.
This approach allows you to change site-wide URLs merely by editing a single config file, without ever touching the application code.

Internal route
--------------

When implementing an application using Yii, you'll deal with internal routes and parameters. Each controller and controller action has a corresponding internal route, such as `site/index` or `user/create`. In the former, `site` is referred to as the *controller ID* while `index` is referred to as the *action ID*. In the second example, `user` is the controller ID and `create` is the action ID. If controller belongs to a *module*, the internal route is prefixed with the module ID, such as `blog/post/index` for a blog module (with `post` being the controller ID and `index` being the action ID).

Creating URLs
-------------

The most important rule for creating URLs in your site is to always do so using the URL manager. The URL manager is an
application component with the `urlManager` ID. This component is accessible both from web and console applications via
`\Yii::$app->urlManager`. The component makes availabe the two following URL creation methods:

- `createUrl($route, $params = [])`
- `createAbsoluteUrl($route, $params = [])`

The `createUrl` method creates a URL relative to the application root, such as `/index.php/site/index/`. The `createAbsoluteUrl` method creates URL prefixed with the proper protocol and
hostname: `http://www.example.com/index.php/site/index`. The former is suitable for internal application URLs, while the latter is used when you need to create rules for outside the website, such as when sending emails or generating an RSS feed.

Some examples:

```php
echo \Yii::$app->urlManager->createUrl('site/page', ['id' => 'about']);
// /index.php/site/page/id/about/
echo \Yii::$app->urlManager->createAbsoluteUrl('blog/post/index');
// http://www.example.com/index.php/blog/post/index/
```

The exact format of the outputted URL will depend upon how the URL manager is configured (which is the point). The above examples may also output:

* `/site/page/id/about/`
* `/index.php?r=site/page&id=about`
* `http://www.example.com/blog/post/index/`
* `http://www.example.com/index.php?r=blog/post/index`

Inside a web application controller, you can use the controller's own `createUrl` shortcut method. Unlike the global `createUrl` method, the controller version is context sensitive:

```php
echo $this->createUrl(''); // currently active route
echo $this->createUrl('view', ['id' => 'contact']); // same controller, different action
echo $this->createUrl('post/index'); // same module, different controller and action
echo $this->createUrl('/site/index'); // absolute route no matter what controller is making this call
```

> **Tip**: In order to generate URL with a hashtag, for example `/index.php?r=site/page&id=100#title`, you need to
  specify the parameter named `#` using `$this->createUrl('post/read', ['id' => 100, '#' => 'title'])`.

Customizing URLs
----------------

By default, Yii uses a query string format for URLs, such as `/index.php?r=news/view&id=100`. In order to make URLs
human-friendly (i.e., more readable), you need to configure the `urlManager` component in the application's configuration file. Enabling "pretty" URLs will convert the query string format to a directory-based format: `/index.php/news/view/id/100`. Disabling the `showScriptName` parameter means that `index.php` will not be part of the URLs. Here's the relevant part of the application's configuration file.

```php
<?php
return [
	// ...
	'components' => [
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
		],
	],
];
```

Note that this configuration will only work if the web server has been properly configured for Yii, see [installation](installation.md#recommended-apache-configuration).

### Named parameters

### Handling subdomains

### Faking URL Suffix

```php
<?php
return [
	// ...
	'components' => [
		'urlManager' => [
			'suffix' => '.html',
		],
	],
];
```

### Handling REST

TBD: [[\yii\web\VerbFiler]]


URL parsing
-----------

Complimentary to creating URLs Yii is handling transforming custom URLs back into internal route and parameters.

### Strict URL parsing

By default if there's no custom rule for URL and URL matches default format such as `/site/page` Yii tries to run a
corresponding controller's action. This behavior could be disabled so if there's no custom rule match, a 404 not found
error will be produced immediately.

```php
<?php
return [
	// ...
	'components' => [
		'urlManager' => [
			'enableStrictParsing' => true,
		],
	],
];
```

Creating your own rule classes
------------------------------

[[\yii\web\UrlRule]] class is used for both parsing URL into parameters and creating URL based on parameters. Despite
the fact that default implementation is flexible enough for majority of projects, there could be a situation when using
your own rule class is the best choice. For example, in a car dealer website, we may want to support the URL format like
`/Manufacturer/Model`, where `Manufacturer` and `Model` must both match some data in a database table. The default rule
class will not work because it mostly relies on statically declared regular expressions which have no database knowledge.

We can write a new URL rule class by extending from [[\yii\web\UrlRule]] and use it in one or multiple URL rules. Using
the above car dealer website as an example, we may declare the following URL rules in application config:

```php
// ...
'components' => [
	'urlManager' => [
		'rules' => [
			'<action:(login|logout|about)>' => 'site/<action>',

			// ...

			['class' => 'app\components\CarUrlRule', 'connectionID' => 'db', ...],
		],
	],
],
```

In the above, we use the custom URL rule class `CarUrlRule` to handle
the URL format `/Manufacturer/Model`. The class can be written like the following:

```php
namespace \app\components;

use \yii\web\UrlRule;

class CarUrlRule extends UrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager, $route, $params)
	{
		if ($route === 'car/index') {
			if (isset($params['manufacturer'], $params['model'])) {
				return $params['manufacturer'] . '/' . $params['model'];
			} elseif (isset($params['manufacturer'])) {
				return $params['manufacturer'];
			}
		}
		return false;  // this rule does not apply
	}

	public function parseRequest($manager, $request)
	{
		$pathInfo = $request->getPathInfo();
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
			// check $matches[1] and $matches[3] to see
			// if they match a manufacturer and a model in the database
			// If so, set $_GET['manufacturer'] and/or $_GET['model']
			// and return 'car/index'
		}
		return false;  // this rule does not apply
	}
}
```

Besides the above usage, custom URL rule classes can also be implemented
for many other purposes. For example, we can write a rule class to log the URL parsing
and creation requests. This may be useful during development stage. We can also
write a rule class to display a special 404 error page in case all other URL rules fail
to resolve the current request. Note that in this case, the rule of this special class
must be declared as the last rule.
