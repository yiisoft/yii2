Использование шаблонизаторов
======================

По умолчанию, Yii использует PHP в шаблонах, но вы можете настроить Yii на поддержку других шаблонизаторов, таких как
[Twig](http://twig.sensiolabs.org/) или [Smarty](http://www.smarty.net/), которые доступны в расширениях.

Компонент `view`, отвественный за генерацию видов. Вы можете добавить шаблонизатор с помощью перенастройки поведения компонента:

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
                    // Массив опций twig:
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

В коде, показанном выше, оба шаблонизатора Smarty и Twig настроены, чтобы использоваться в файле вида. Но чтобы добавить эти расширения в ваш проект, вам необходимо также изменить ваш `composer.json` файл. Добавить в него:

```
"yiisoft/yii2-smarty": "~2.0.0",
"yiisoft/yii2-twig": "~2.0.0",
```
Это код вставляется в секцию `require` файла `composer.json`. После изменения и сохранения этого файла, вы можете установить расширение, запустив `composer update --prefer-dist` в командной строке.

Для получения подробной информации об использовании конкретного шаблонизатора обратитесь в их документации:

- [Twig guide](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Smarty guide](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)
