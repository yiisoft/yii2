URL Management
==============

The concept of URL management in Yii fairly simple. The idea is that application uses internal routes and parameters
everywhere. Framework takes care of translating routes into URLs and back according to URL manager configuration.
This approach allows you to adjust URLs in a single config file without touching application code.

Internal route
--------------

Internal routes and parameters are what you're dealing with when implementing an application using Yii.

Each controller and its action has a corresponding internal route such as `site/index`. Here `site` is referred to as
controller ID while `test` is referred to as action ID. If controller belongs to a module, internal route is prefixed
with the module ID such as `blog/post/index` for a blog module.

Creating URLs
-------------

As was already mentioned, the most important rule is to always use URL manager to create URLs. Url manages is an
application component with `urlManager` id that is accessible both from web and console applications via
`\Yii::$app->urlManager` and has two following URL creation methods available:

- createUrl($route, $params = [])
- createAbsoluteUrl($route, $params = [])

First one creates URL relative to the application root while the second one creates URL prefixed with protocol and
hostname. The former is suitable for internal application URLs while the latter is used when you need to create rules
for outside the website. For example, when sending emails or generating RSS feed.

Some examples:

```php
echo \Yii::$app->urlManager->createUrl('site/page', ['id' => 'about']);
echo \Yii::$app->urlManager->createAbsoluteUrl('blog/post/index');
```

Inside web application controller you can use its own `createUrl` shortcut method in the following forms:

```php
echo $this->createUrl(''); // currently active route
echo $this->createUrl('view', ['id' => 'contact']); // same controller, different action
echo $this->createUrl('post/index'); // same module, different controller and action
echo $this->createUrl('/site/index'); // absolute route no matter which controller we're in
```

> **Tip**: In order to generate URL with a hashtag, for example `/index.php?r=site/page&id=100#title`, you need to
  specify parameter named `#` using `$this->createUrl('post/read', ['id' => 100, '#' => 'title'])`.

Customizing URLs
----------------

By default Yii uses a query string format URLs such as `/index.php?r=news/view&id=100`. In order to make URLs
human-friendly you need to configure `urlManager` component like the following:

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

Note that

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


URL parsing
-----------

Complimentary to creating URLs Yii is handling transforming custom URLs back into internal route and parameters.

### Strict URL parsing

By default if there's no custom rule for URL and URL matches default format such as `/site/page` Yii tries to run a
corresponding controller's action. This beahvior could be disabled so if there's no custom rule match, a 404 not found
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
