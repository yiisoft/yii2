Yii2 view renderers
===================

By default Yii uses PHP as template language but you can change it in your application.
The component responsible for rendering a view is called `view`. You can configure
a custom template engine as follows:

```php
array(
	'components' => array(
		'view' => array(
			'class' => 'yii\base\View',
			'renderer' => array(
				'class' => 'yii\renderers\TwigViewRenderer',
				// or 'class' => 'yii\renderers\SmartyViewRenderer',
			),
		),
	),
)
```

Twig
----

In order to use Twig you need to put you templates in files with extension `.twig`.
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller.

Smarty
------

In order to use Smarty you need to put you templates in files with extension `.tpl`.
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller.

Using multiple view renderers in a single application
-----------------------------------------------------

If you need multiple view renderers at the same time in a single application you
can use `CompositeViewRenderer` as follows:

```php
'components' => array(
	'view' => array(
		'class' => 'yii\base\View',
		'renderer' => array(
			'class' => 'yii\renderers\CompositeViewRenderer',
			'renderers' => array(
				'tpl' => array(
					'class' => 'yii\renderers\SmartyViewRenderer',
				),
				'twig' => array(
					'class' => 'yii\renderers\TwigViewRenderer',
				),
			),
			//'class' => 'yii\renderers\TwigViewRenderer',
		),
	),
),
```