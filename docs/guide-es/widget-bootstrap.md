Widgets de Bootstrap 
====================

> Nota: Esta sección está bajo desarrollo.

Yii incluye soporta las marcas y componentes del framework [Bootstrap 3](http://getbootstrap.com/) (también conocido como "Twitter Bootstrap"). Bootstrap es un excelente, adaptable framework que puede aumentar la velocidad de desarrollo de los procesos del lado del cliente.

El núcleo de Bootstrap está representado en dos partes:

- Elementos básicos de CSS, como son un sistema de diseño en formato cuadrícula , tipografía, clases de ayuda (helpers), y utilidades adaptables(responsive).

- Componentes preparados para su uso, tales como formularios, menús, paginación, cajas modales, pestañas, etc

Elementos básicos
-----------------

Yii no hace uso de elementos básicos de boostrap en el código PHP ya que HTML es muy simple por sí mismo, en este caso. Puedes encontrar detalle del uso de estos elementos básicos en [sitio web de la documentación de bootstrap](http://getbootstrap.com/css/). Aún así Yii provee una manera conveniente de incluir los elementos básicos de los recursos de bootstrap en tus páginas con una simple línea añadida a `AppAsset.php` localizada en tu directorio `@app/assets` :

```php
public $depends = [
    'yii\web\YiiAsset',
    'yii\bootstrap\BootstrapAsset', // Esta línea
];
```

Usar bootstrap a través de el gestor de recursos Yii te permite minimizar estos recursos y combinar con tus propios recursos cuando sea necesario..

Widgets de Yii
--------------

Componentes más complejos de bootstrap components están envueltos dentro de widgets de Yii para permitir una sintaxis más robusta e integrar con las posibilidades y características del framework. Todos los widgets pertenecen al espacio de nombres `\yii\bootstrap` :

- [[yii\bootstrap\ActiveForm|ActiveForm]]
- [[yii\bootstrap\Alert|Alert]]
- [[yii\bootstrap\Button|Button]]
- [[yii\bootstrap\ButtonDropdown|ButtonDropdown]]
- [[yii\bootstrap\ButtonGroup|ButtonGroup]]
- [[yii\bootstrap\Carousel|Carousel]]
- [[yii\bootstrap\Collapse|Collapse]]
- [[yii\bootstrap\Dropdown|Dropdown]]
- [[yii\bootstrap\Modal|Modal]]
- [[yii\bootstrap\Nav|Nav]]
- [[yii\bootstrap\NavBar|NavBar]]
- [[yii\bootstrap\Progress|Progress]]
- [[yii\bootstrap\Tabs|Tabs]]


Usando los ficheros .less de Bootstrap directamente
---------------------------------------------------

Si quieres incluir el [CSS Bootstrap directamente en tus ficheros less](http://getbootstrap.com/getting-started/#customizing) puedes necesitar desactivar la carga los ficheros css originales de bootstrap.
Esto lo puedes hacer poniendo la propiedad css de [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] vacía.
Para esto necesitas configurar el `assetManager` [componente de la aplicación](structure-application-components.md) como sigue:

```php
    'assetManager' => [
        'bundles' => [
            'yii\bootstrap\BootstrapAsset' => [
                'css' => [],
            ]
        ]
    ]
```
