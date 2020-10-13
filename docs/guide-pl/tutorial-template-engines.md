Silniki szablonów
=================

Yii domyślnie używa PHP jako języka szablonów, ale nic nie stoi na przeszkodzie, aby skonfigurować wsparcie dla innych silników renderujących widok, 
takich jak [Twig](http://twig.sensiolabs.org/) lub [Smarty](http://www.smarty.net/), dostępnych w postaci rozszerzeń.

Komponent `view` jest odpowiedzialny za renderowanie widoków. Aby dodać niestandardowy silnik szablonów, należy skonfigurować komponent jak poniżej:

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
                    // Tablica ustawień twig:
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

W powyższym przykładzie zarówno Smarty jak i Twig są gotowe do użycia w plikach widoków. Aby dodać te rozszerzenia w projekcie, należy zmodyfikować 
dodatkowo plik `composer.json` poprzez dopisanie w wymaganiach (`require`):

```
"yiisoft/yii2-smarty": "~2.0.0",
"yiisoft/yii2-twig": "~2.0.0",
```
Po zapisaniu pliku można zainstalować rozszerzenia uruchamiając komendę `composer update --prefer-dist` z konsoli.

Szczegóły na temat każdego z powyższych silników szablonów dostępne są w ich dokumentacjach:

- [Przewodnik po Twig](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Przewodnik po Smarty](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)