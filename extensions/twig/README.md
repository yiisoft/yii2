Twig Extension for Yii 2
========================

This extension provides a `ViewRender` that would allow you to use Twig view template engine.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'view' => [
			'defaultExtension' => 'twig',
			'renderers' => [
				'twig' => [
					'class' => 'yii\twig\ViewRenderer',
					//'cachePath' => '@runtime/Twig/cache',
					//'options' => [], /*  Array of twig options */
					//'namespaces' => []
					// ... see ViewRenderer for more options
				],
			],
		],
	],
];
```


Using namespaces
----------------

Specify in your application config file:

```php
'namespaces' => [
	'@app/themes/default/layouts' => 'layouts',
	'@app/views/layouts' => 'layouts'
]
```

and in template you can use

```
{% extends "@layouts:common.twig" %}
```

the view will be searched in `app/themes/default/layouts/common.twig` then if it's not found it will check `app/views/layouts/common.twig`.

Layout Example
--------------

```
{% spaceless %}
	{{ this.beginPage() }}
	<!DOCTYPE html>
	<html lang="{{ app.language }}">
	<head>
		<meta charset="{{ app.charset }}"/>
		<title>{% block title %}{{ html.encode(this.title) }}{% endblock %}</title>
		{{ this.head() }}
	</head>
	<body>
		<div id="layout">
			{{ this.beginBody() }}

				<div id="container">
					{% block content %}{% endblock %}
				</div>

			{{ this.endBody() }}
		</div>
	</body>
	</html>
	{{ this.endPage() }}
{% endspaceless %}
```

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-twig "*"
```

or add

```
"yiisoft/yii2-twig": "*"
```

to the require section of your composer.json.
