Using template engines
======================

By default, Yii uses PHP as its template language, but you can configure Yii to support other rendering engines, such as
[Twig](http://twig.sensiolabs.org/) or [Smarty](http://www.smarty.net/).

The `view` component is responsible for rendering views. You can add a custom template engine by reconfiguring this
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

In the code above, both Smarty and Twig are configured to be useable by the view files. But in order to get these extensions into your project, you need to also modify
your `composer.json` file to include them, too:

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```
That code would be added to the `require` section of `composer.json`. After making that change and saving the file, you can install the extensions by running `composer update --preder-dist` in the command-line.

Twig
----

To use Twig, you need to create templates in files that have the `.twig` extension (or use another file extension but configure the component accordingly).
Unlike standard view files, when using Twig you must include the extension in your `$this->render()`
or `$this->renderPartial()` controller calls:

```php
echo $this->render('renderer.twig', ['username' => 'Alex']);
```

### Additional functions

Yii adds the following construct to the standard Twig syntax:

```php
<a href="{{ path('blog/view', {'alias' : post.alias}) }}">{{ post.title }}</a>
```

Internally, the `path()` function calls Yii's `Url::to()` method.

### Additional variables

Within Twig templates, you can also make use of these variables:

- `app`, which equates to `\Yii::$app`
- `this`, which equates to the current `View` object

### Globals

You can add global helpers or values via the application configuration's `globals` variable. You can define both Yii helpers and your own
variables there:

```php
'globals' => [
    'html' => '\yii\helpers\Html',
    'name' => 'Carsten',
],
```

Once configured, in your template you can use the globals in the following way:

```
Hello, {{name}}! {{ html.a('Please login', 'site/login') | raw }}.
```

### Additional filters

Additional filters may be added via the application configuration's `filters` option:

```php
'filters' => [
    'jsonEncode' => '\yii\helpers\Json::encode',
],
```

Then in the template you can use:

```
{{ model|jsonEncode }}
```


Smarty
------

To use Smarty, you need to create templates in files that have the `.tpl` extension (or use another file extension but configure the component accordingly). Unlike standard view files, when using Smarty you must include the extension in your `$this->render()`
or `$this->renderPartial()` controller calls:

```php
echo $this->render('renderer.tpl', ['username' => 'Alex']);
```

### Additional functions

Yii adds the following construct to the standard Smarty syntax:

```php
<a href="{path route='blog/view' alias=$post.alias}">{$post.title}</a>
```

Internally, the `path()` function calls Yii's `Url::to()` method.

### Additional variables

Within Smarty templates, you can also make use of these variables:

- `$app`, which equates to `\Yii::$app`
- `$this`, which equates to the current `View` object

