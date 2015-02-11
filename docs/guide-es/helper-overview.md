Helpers
=======

> Nota: Esta sección está en desarrollo.

Yii ofrece muchas clases que ayudan a simplificar las tareas comunes de codificación, como manipulación de string o array,
generación de código HTML, y más. Estas clases helper están organizadas bajo el namespace `yii\helpers` y
son todo clases estáticas (lo que significa que sólo contienen propiedades y métodos estáticos y no deben ser instanciadas).

Puedes usar una clase helper directamente llamando a uno de sus métodos estáticos, como a continuación:

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Nota: Para soportar la [personalización de clases helper](#customizing-helper-classes), Yii separa cada clase helper del núcleo
  en dos clases: una clase base (ej. `BaseArrayHelper`) y una clase concreta (ej. `ArrayHelper`).
  Cuando uses un helper, deberías sólo usar la versión concreta y nunca usar la clase base.


Clases Helper del núcleo
------------------------

Las siguientes clases helper del núcleo son proporcionadas en los releases de Yii:

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- [Html](helper-html.md)
- HtmlPurifier
- Image
- Inflector
- Json
- Markdown
- Security
- StringHelper
- [Url](helper-url.md)
- VarDumper


Personalizando Las Clases Helper <span id="customizing-helper-classes"></span>
--------------------------------

Para personalizar una clase helper del núcleo (ej. [[yii\helpers\ArrayHelper]]), deberías crear una nueva clase extendiendo
de los helpers correspondientes a la clase base (ej. [[yii\helpers\BaseArrayHelper]]), incluyendo su namespace. Esta clase
será creada para remplazar la implementación original del framework.

El siguiente ejemplo muestra como personalizar el método [[yii\helpers\ArrayHelper::merge()|merge()]] de la clase
[[yii\helpers\ArrayHelper]]:

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // tu implementación personalizada
    }
}
```

Guarda tu clase en un fichero nombrado `ArrayHelper.php`. El fichero puede estar en cualquier directorio, por ejemplo `@app/components`.

A continuación, en tu [script de entrada](structure-entry-scripts.md) de la aplicación, añade las siguientes lineas de código
después de incluir el fichero `yii.php` para decirle a la [clase autoloader de Yii](concept-autoloading.md) que cargue tu
clase personalizada en vez de la clase helper original del framework:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Nota que la personalización de clases helper sólo es útil si quieres cambiar el comportamiento de una función
existente de los helpers. Si quieres añadir funciones adicionales para usar en tu aplicación puedes mejor crear un helper
por separado para eso.
