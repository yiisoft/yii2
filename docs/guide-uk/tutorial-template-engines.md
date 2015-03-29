Використання шаблонізаторів
===========================

За замовчуванням, Yii використовує PHP в якості мови шаблонів, але ви можете налаштувати Yii для підтримки інших *шаблонізаторів*,
таких як [Twig](http://twig.sensiolabs.org/) або [Smarty](http://www.smarty.net/), які доступні в якості розширення.

Компонент `View` відповідає за рендиренг видів. Ви можете додати *користувацький* шаблон шляхом зміни конфігурації *поведінки*
цього компонента:

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

В приведеному вище коді, як Smarty так і Twig налаштовані для *корисного* перегляду файлів.
Але для того, щоб підключити ці розширення у ваш проект, вам також необхідно змінити файл `composer.json`, щоб включити їх:

```
"yiisoft/yii2-smarty": "*",
"yiisoft/yii2-twig": "*",
```

Цей код потрібно додати в `require` розділ файлу `composer.json`. Після внесення цих змін і збереження файлу,
ви можете встановити розширення виконавши команду `composer update --prefer-dist` через командний рядок.

Для отримання детальної інформації про використання конкретного шаблонізатора, будь ласка, зверніться до його документації:

- [Документація Twig](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Документація Smarty](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)