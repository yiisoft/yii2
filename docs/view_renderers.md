Yii2 view renderers
===================

By default Yii uses PHP as template language but you can change it in your application.
The component responsible for rendering a view using custom template enging is
called `viewRenderer`. You can configure it as follows:

```php
array(
	'components' => array(
		'viewRenderer' => array(
			'class' => 'yii\renderers\TwigViewRenderer',
			// or 'class' => 'yii\renderers\SmartyViewRenderer',
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