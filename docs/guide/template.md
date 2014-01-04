Using template engines
======================

By default Yii uses PHP as template language, but you can configure it to support other rendering engines, such as
[Twig](http://twig.sensiolabs.org/) or [Smarty](http://www.smarty.net/).

The `view` component is responsible for rendering views. You can add a custom template engines by reconfiguring this
component's behavior:

```php
[
	'components' => [
		'view' => [
			'class' => 'yii\web\View',
			'renderers' => [
				'tpl' => [
					'class' => 'yii\smarty\ViewRenderer',
					//'cachePath' => '@runtime/Smarty/cache',
				],
				'twig' => [
					'class' => 'yii\twig\ViewRenderer',
					//'cachePath' => '@runtime/Twig/cache',
					//'options' => [], /*  Array of twig options */
					'globals' => ['html' => '\yii\helpers\Html'],
				],
				// ...
			],
		],
	],
]
```

In the config above we're using Smarty and Twig. In order to get these extensions in your project you need to modify
your `composer.json` to include

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```

in `require` section and then run `composer update`.

Twig
----

To use Twig, you need to create templates in files with the `.twig` extension (or use another file extension but configure the component accordingly).
Unlike standard view files, when using Twig, you must include the extension  when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.twig', ['username' => 'Alex']);
```

### Additional functions

Yii adds the following construct to the standard Twig syntax:

```php
<a href="{{ path('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
```

Internally, the `path()` function calls Yii's `Html::url()` method.

### Additional variables

Within Twig templates, you can also make use of these variables:

- `app`, which equates to `\Yii::$app`
- `this`, which equates to the current `View` object

### Globals

You can add global helpers or values via config's `globals`. It allows both using Yii helpers and setting your own
values:

```php
'globals' => [
	'html' => '\yii\helpers\Html',
	'name' => 'Carsten',
],
```

Then in your template you can use it the following way:

```
Hello, {{name}}! {{ html.a('Please login', 'site/login') | raw }}.
```

### Additional filters

Additional filters may be added via config's `filters` option:

```php
'filters' => [
	'jsonEncode' => '\yii\helpers\Json::encode',
],
```

Then in the template you can use

```
{{ model|jsonEncode }}
```


Smarty
------

To use Smarty, you need to create templates in files with the `.tpl` extension (or use another file extension but configure the component accordingly). Unlike standard view files, when using Smarty, you must include the extension  when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.tpl', ['username' => 'Alex']);
```

### Additional functions

Yii adds the following construct to the standard Smarty syntax:

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
```

Internally, the `path()` function calls Yii's `Html::url()` method.

### Additional variables

Within Smarty templates, you can also make use of these variables:

- `$app`, which equates to `\Yii::$app`
- `$this`, which equates to the current `View` object

