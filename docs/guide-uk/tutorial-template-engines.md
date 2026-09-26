Використання шаблонізаторів
===========================

За замовчуванням, Yii використовує PHP в якості мови шаблонів, але ви можете налаштувати Yii для підтримки інших шаблонізаторів,
таких як [Twig](https://twig.symfony.com/) або [Smarty](https://www.smarty.net/), які доступні в якості розширеннь.

Компонент `View` відповідає за рендиренг видів. Ви можете додати користувальницький шаблон, шляхом зміни конфігурації поведінки
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

У наведеному вище коді, як Smarty так і Twig налаштовані так, щоб використовуватись файлами представлень.
Але для того, щоб підключити ці розширення у ваш проект, вам також необхідно змінити файл `composer.json`, щоб включити їх:

```
"yiisoft/yii2-smarty": "~2.0.0",
"yiisoft/yii2-twig": "~2.0.0",
```

Цей код потрібно додати у розділ `require` файлу `composer.json`. Після внесення цих змін і збереження файлу,
ви можете встановити розширення, виконавши команду `composer update --prefer-dist` через командний рядок.

Для отримання детальної інформації про використання конкретного шаблонізатора, будь ласка, зверніться до його документації:

- [Документація Twig](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Документація Smarty](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)
