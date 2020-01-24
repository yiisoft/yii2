Temas
=====

> Note: Esta sección está en desarrollo.

Un tema (theme) es un directorio de archivos y de vistas (views) y layouts. Cada archivo de este directorio 
sobrescribe el archivo correspondiente de una aplicación cuando se renderiza. Una única aplicación puede usar 
múltiples temas para que pueden proporcionar experiencias totalmente diferentes. Solo se puede haber un único tema 
activo.

> Note: Los temas no están destinados a ser redistribuidos ya que están demasiado ligados a la aplicación. Si se 
  quiere redistribuir una apariencia personalizada, se puede considerar la opción de 
  [asset bundles](structure-assets.md) de archivos CSS y Javascript.

Configuración de un Tema
------------------------

La configuración de un tema se especifica a través del componente `view` de la aplicación. Para establecer que un tema 
trabaje con vistas de aplicación básicas, la configuración de la aplicación debe contener lo siguiente:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/views' => '@app/themes/basic'],
            'baseUrl' => '@web/themes/basic',
        ],
    ],
],
```

En el ejemplo anterior, el `pathMap` define un mapa (map) de las rutas a las que se aplicará el tema mientras que 
`baseUrl` define la URL base para los recursos a los que hacen referencia los archivos del tema.

En nuestro caso `pathMap` es `['@app/views' => '@app/themes/basic']`. Esto significa que cada vista de `@app/views` 
primero se buscará en `@app/themes/basic` y si existe, se usará la vista del directorio del tema en lugar de la vista 
original.

Por ejemplo, con la configuración anterior, la versión del tema para la vista `@app/views/site/index.php` será 
`@app/themes/basic/site/index.php`. Básicamente se reemplaza `@app/views` en `@app/views/site/index.php` por 
`@app/themes/basic`.

### Temas para Módulos

Para utilizar temas en los módulos, el `pathMap` debe ser similar al siguiente:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@app/views' => '@app/themes/basic',
                '@app/modules' => '@app/themes/basic/modules', // <-- !!!
            ],
        ],
    ],
],
```

Esto permite aplicar el tema a `@app/modules/blog/views/comment/index.php` con la vista 
`@app/themes/basic/modules/blog/views/comment/index.php`.

### Temas para Widgets

Para utilizar un tema en una vista que se encuentre en `@app/widgets/currency/views/index.php`, se debe aplicar la 
siguiente configuración para el componente vista, tema:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/widgets' => '@app/themes/basic/widgets'],
        ],
    ],
],
```

Con la configuración anterior, se puede crear una versión de la vista `@app/widgets/currency/index.php` para que se 
aplique el tema en `@app/themes/basic/widgets/currency/views/index.php`.

Uso de Multiples Rutas
----------------------

Es posible mapear una única ruta a múltiples rutas de temas. Por ejemplo:

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

En este caso, primero se buscara la vista en `@app/themes/christmas/site/index.php`, si no se encuentra, se intentará 
en `@app/themes/basic/site/index.php`. Si la vista no se encuentra en ninguna de rutas especificadas, se usará la 
vista de aplicación.

Esta capacidad es especialmente útil si se quieren sobrescribir algunas rutas temporal o condicionalmente.
