Using template engines
======================

By default, Yii uses PHP as its template language, but you can configure Yii to support other rendering engines, such as
[Twig](http://twig.sensiolabs.org/) or [Smarty](http://www.smarty.net/) available as extensions.

The `view` component is responsible for rendering views. You can add a custom template engine by reconfiguring this
component's behavior:

```php
[
    'components' => [
        'view' => [
            '__class' => yii\web\View::class,
            'renderers' => [
                'tpl' => [
                    '__class' => yii\smarty\ViewRenderer::class,
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
                'twig' => [
                    '__class' => yii\twig\ViewRenderer::class,
                    'cachePath' => '@runtime/Twig/cache',
                    // Array of twig options:
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
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
"yiisoft/yii2-smarty": "~2.0.0",
"yiisoft/yii2-twig": "~2.0.0",
```
That code would be added to the `require` section of `composer.json`. After making that change and saving the file, you can install the extensions by running `composer update --prefer-dist` in the command-line.

For details about using concrete template engine please refer to its documentation:

- [Twig guide](https://www.yiiframework.com/extension/yiisoft/yii2-twig/doc/guide/)
- [Smarty guide](https://www.yiiframework.com/extension/yiisoft/yii2-smarty/doc/guide/)
