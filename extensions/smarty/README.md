Smarty Extension for Yii 2
==========================

<<<<<<< HEAD
This extension provides a `ViewRender` that would allow you to use [Smarty](http://www.smarty.net/) view template engine.

This repository is a git submodule of <https://github.com/yiisoft/yii2>.
Please submit issue reports and pull requests to the main repository.
For license information check the [LICENSE](LICENSE.md)-file.
=======
This extension provides a `ViewRender` that would allow you to use Smarty view template engine.

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'view' => [
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
            ],
        ],
    ],
];
```
>>>>>>> yiichina/master

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
<<<<<<< HEAD
php composer.phar require --prefer-dist yiisoft/yii2-smarty
=======
php composer.phar require --prefer-dist yiisoft/yii2-smarty "*"
>>>>>>> yiichina/master
```

or add

```json
<<<<<<< HEAD
"yiisoft/yii2-smarty": "~2.0.0"
=======
"yiisoft/yii2-smarty": "*"
>>>>>>> yiichina/master
```

to the require section of your composer.json.

Note that the smarty composer package is distributed using subversion so you may need to install subversion.
<<<<<<< HEAD

Usage
-----

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'view' => [
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
            ],
        ],
    ],
];
```
=======
>>>>>>> yiichina/master
