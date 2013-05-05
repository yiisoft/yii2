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
					'twigPath' => '@app/vendors/Twig',
				),
				// ...
			),
		),
	),
)
```

Note that Smarty and Twig are not bundled with Yii and you have to download and
unpack these yourself and then specify `twigPath` and `smartyPath` respectively.

Twig
----

In order to use Twig you need to put you templates in files with extension `.twig`
(or another one if configured differently).
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.twig', array('username' => 'Alex'));
```

### Additional functions

Additionally to regular Twig syntax the following is available in Yii:

```php
<a href="{{ path('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
```

path function calls `Html::url()` internally.

### Additional variables

- `app` = `\Yii::$app`
- `this` = current `View` object

Smarty
------

In order to use Smarty you need to put you templates in files with extension `.tpl`
(or another one if configured differently).
Also you need to specify this extension explicitly when calling `$this->render()`
or `$this->renderPartial()` from your controller:

```php
echo $this->render('renderer.tpl', array('username' => 'Alex'));
```

### Additional functions

Additionally to regular Smarty syntax the following is available in Yii:

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
```

path function calls `Html::url()` internally.

### Additional variables

- `$app` = `\Yii::$app`
- `$this` = current `View` object