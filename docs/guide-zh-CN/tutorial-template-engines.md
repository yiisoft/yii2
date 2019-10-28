使用模板引擎（Using template engines）
====================================

默认情况下，Yii 使用 PHP 作为其默认的模板引擎语言，但是，你可以配置 Yii 以扩展的方式支持其他的渲染引擎，
比如 [Twig](http://twig.sensiolabs.org/) 或 [Smarty](http://www.smarty.net/)等。

组件 `view` 就是用于渲染视图的。
你可以重新配置这个组件的行为以增加一个自定义的模板引擎。

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

在上述的代码中， Smarty 和 Twig 都被配置以让视图文件使用。但是，为了让扩展安装到项目中，
你同样需要修改你的 `composer.json` 文件，如下：

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```
上述代码需要增加到 `composer.json` 的 `require` 节中。在做了上述修改，并保存后，你可以运行 `composer update --prefer-dist` 命令来安装扩展。

对于特定模板引擎的使用详细，请参考其文档：

- [Twig guide](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Smarty guide](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)
