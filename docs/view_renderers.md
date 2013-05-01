Yii2 view renderers
===================

By default Yii uses PHP as template language but you can configure it to be able
to render templates with special engines such as Twig or Smarty.

The component responsible for rendering a view is called `view`. You can add
a custom template engines as follows:

```php
array(
	'components' => array(
		'view' => array(
			'class' => 'yii\base\View',
			'renderers' => array(
				'tpl' => array(
					'class' => 'yii\renderers\SmartyViewRenderer',
				),
				'twig' => array(
					'class' => 'yii\renderers\TwigViewRenderer',
				),
				// ...
			),
		),
	),
)
```

Twig
----

In order to use Twig you need to put you templates in files with extension `.twig`
(or another one if configured differently).
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.twig', array('username' => 'Alex'));
```

Smarty
------

In order to use Smarty you need to put you templates in files with extension `.tpl`
(or another one if configured differently).
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.tpl', array('username' => 'Alex'));
```
