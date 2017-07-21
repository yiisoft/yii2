Componentes
===========

Los componentes son los principales bloques de construcción de las aplicaciones Yii. Los componentes son instancias de [[yii\base\Component]] o de una clase extendida. Las tres características principales que los componentes proporcionan
a las otras clases son:

* [Propiedades](concept-properties.md)
* [Eventos](concept-events.md)
* [Comportamientos](concept-behaviors.md)

Por separado y combinadas, estas características hacen que las clases Yii sean mucho mas personalizables y sean mucho más fáciles de usar. Por ejemplo, el incluido [[yii\jui\DatePicker|widget de selección de fecha]], un componente de la interfaz de usuario, puede ser utilizado en una [vista](structure-view.md) para generar un DatePicker interactivo:

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'ru',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```

Las propiedades del widget son fácilmente modificables porque la clase se extiende de [[yii\base\Component]].

Mientras que los componentes son muy potentes, son un poco más pesados que los objetos normales, debido al hecho de que necesitan más memoria y tiempo de CPU para poder soportar [eventos](concept-events.md) y [comportamientos](concept-behaviors.md) en particular.
Si tus componentes no necesitan estas dos características, deberías considerar extender tu componente directamente de [[yii\base\BaseObject]] en vez de [[yii\base\Component]]. De esta manera harás que tus componentes sean mucho más eficientes que objetos PHP normales, pero con el añadido soporte para [propiedades](concept-properties.md).

Cuando extiendes tu clase de [[yii\base\Component]] o [[yii\base\BaseObject]], se recomienda que sigas las siguientes convenciones:

- Si sobrescribes el constructor, especifica un parámetro `$config` como el *último* parámetro del constructor, y después pasa este parámetro al constructor padre.
- Siempre llama al constructor padre al *final* de su propio constructor.
- Si sobrescribes el método [[yii\base\BaseObject::init()]], asegúrese de llamar la implementación padre de `init` * al principio * de su método` init`.

Por ejemplo:

```php
namespace yii\components\MyClass;

use yii\base\BaseObject;

class MyClass extends BaseObject
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... inicialización antes de la configuración está siendo aplicada

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... inicialización después de la configuración esta siendo aplicada
    }
}
```

Siguiendo esas directrices hará que tus componentes sean [configurables](concept-configurations.md) cuando son creados. Por ejemplo:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternativamente
$component = \Yii::createObject([
    'class' => MyClass::class,
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: Mientras que el enfoque de llamar [[Yii::createObject()]] parece mucho más complicado, es mucho más potente debido al hecho de que se implementa en la parte superior de un [contenedor de inyección de dependencia](concept-di-container.md).
  

La clase [[yii\base\BaseObject]] hace cumplir el siguiente ciclo de vida del objeto:

1. Pre-inicialización en el constructor. Puedes establecer los valores predeterminados de propiedades aquí.
2. Configuración del objeto a través de `$config`. La configuración puede sobrescribir los valores prdeterminados dentro del constructor.
3. Post-inicialización dentro de [[yii\base\BaseObject::init()|init()]]. Puedes sobrescribir este método para realizar comprobaciones de validez y normalización de las propiedades.
4. Llamadas a métodos del objeto.

Los tres primeros pasos ocurren dentro del constructor del objeto. Esto significa que una vez obtengas la instancia de un objeto, ésta ha sido inicializada para que puedas utilizarla adecuadamente.
