Using template engines
======================

By default Yii uses PHP as template language, but you can configure it to support other rendering engines, such as [Twig](http://twig.sensiolabs.org/) or [Smarty](http://www.smarty.net/).

The `view` component is responsible for rendering views. You can add
a custom template engines by reconfiguring this component's behavior:

```php
[
	'components' => [
		'view' => [
			'class' => 'yii\base\View',
			'renderers' => [
				'tpl' => [
					'class' => 'yii\renderers\SmartyViewRenderer',
				],
				'twig' => [
					'class' => 'yii\renderers\TwigViewRenderer',
					'twigPath' => '@app/vendors/Twig',
				],
				// ...
			],
		],
	],
]
```

Note that the Smarty and Twig packages themselves are not bundled with Yii. You must download them yourself. Then unpack the packages and place the resulting files in a logical location, such as the application's `protected/vendor` folder. Finally, specify the correct `smartyPath` or `twigPath`, as in the code above (for Twig).

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

