Usar motores de plantillas
==========================

Por defecto, Yii utiliza PHP como su lenguaje de plantilla, pero puedes configurar Yii para que soporte otros motores de renderizado, tal como
[Twig](http://twig.sensiolabs.org/) o [Smarty](http://www.smarty.net/), disponibles como extensiones.

El componente `view` es el responsable de renderizar las vistas. Puedes agregar un motor de plantillas personalizado reconfigurando
el comportamiento (behavior) de este componente:

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
                    // Array de opciones de Twig:
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

En el código de arriba, tanto Smarty como Twig son configurados para ser utilizables por los archivos de vista. Pero para tener ambas extensiones en tu proyecto, también necesitas modificar
tu archivo `composer.json` para incluirlos:

```
"yiisoft/yii2-smarty": "~2.0.0",
"yiisoft/yii2-twig": "~2.0.0",
```
Ese código será agregado a la sección `require` de `composer.json`. Después de realizar ese cambio y guardar el archivo, puedes instalar estas extensiones ejecutando `composer update --prefer-dist` en la línea de comandos.

Para más detalles acerca del uso concreto de cada motor de plantillas, visita su documentación:

- [Guía de Twig](https://github.com/yiisoft/yii2-twig/tree/master/docs/guide)
- [Guía de Smarty](https://github.com/yiisoft/yii2-smarty/tree/master/docs/guide)
