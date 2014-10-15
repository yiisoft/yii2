Twig Extension for Yii 2
========================

This extension provides a `ViewRender` that would allow you to use Twig view template engine.

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'view' => [
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    // set cachePath to false in order to disable template caching
                    'cachePath' => '@runtime/Twig/cache',
                    // Array of twig options:
                    'options' => [
                        'auto_reload' => true,
                    ],
                    // ... see ViewRenderer for more options
                ],
            ],
        ],
    ],
];
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
