Propiedades
===========

En PHP, las variables miembro de clases también llamadas *propiedades*, son parte de la definición de la clase, y se
usan para representar el estado de una instancia de la clase (ej. para diferenciar una instancia de clase de otra).
A la práctica, a menudo, se puede querer gestionar la lectura o escritura de las propiedades de algunos momentos. Por
ejemplo, se puede querer eliminar los espacios en blanco (trim) de una cadena de texto cada vez que esta se asigne a
una propiedad de tipo `label`. Se *podría* usar el siguiente código para realizar esta tarea:

```php
$object->label = trim($label);
```

La desventaja del código anterior es que se tendría que ejecutar `trim()` en todas las partes del código que pudieran
establecer la propiedad `label`. Si en el futuro, la propiedad `label` tiene que seguir otro funcionamiento, como por
ejemplo que la primera letra tiene que estar en mayúsculas, se tendrán que modificar todas las secciones de código que
asignen el valor a la propiedad `label`. La repetición de código conlleva a bugs, y es una practica que se tiene que
evitar en la medida de lo posible.

Para solventar este problema, Yii introduce la clase base llamada [[yii\base\BaseObject]] que da soporte a la definición
de propiedades basada en los métodos de clase *getter* y *setter*. Si una clase necesita más funcionalidad, debe
extender a la clase [[yii\base\BaseObject]] o a alguna de sus hijas.

> Info: Casi todas las clases del núcleo (core) en el framework Yii extienden a [[yii\base\BaseObject]] o a una de
  sus clases hijas. Esto significa que siempre que se encuentre un getter o un setter en una clase del núcleo, se
  puede utilizar como una propiedad.

Un método getter es un método cuyo nombre empieza por la palabra `get`: un metodo setter empieza por `set`. El nombre
añadido detrás del prefijo `get` o `set` define el nombre de la propiedad. Por ejemplo, un getter `getLabel()` y/o un
setter `setLabel()` definen la propiedad `label`, como se muestra a continuación:

```php
namespace app\components;

use yii\base\BaseObject;

class Foo extends BaseObject
{
    private $_label;

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = trim($value);
    }
}
```

(Para ser claros, los métodos getter y setter crean la propiedad `label`, que en este caso hace una referencia interna
al nombre de atributo privado `_label`.)

Las propiedades definidas por los getter y los setters se pueden usar como variables de clase miembro. La principal
diferencia radica en que cuando esta propiedad se lea, se ejecutará su correspondiente método getter; cuando se asigne
un valor a la propiedad, se ejecutará el correspondiente método setter. Por ejemplo:

```php
// equivalente a $label = $object->getLabel();
$label = $object->label;

// equivalente a $object->setLabel('abc');
$object->label = 'abc';
```

Una propiedad definida por un getter sin un setter es de tipo *sólo lectura*. Si se intenta asignar un valor a esta
propiedad se producirá una excepción de tipo [[yii\base\InvalidCallException|InvalidCallException]]. Del mismo modo
que una propiedad definida con un setter pero sin getter será de tipo *sólo escritura*, cualquier intento de lectura
de esta propiedad producirá una excepción. No es común tener variables de tipo sólo escritura.

Hay varias reglas especiales y limitaciones en las propiedades definidas mediante getters y setters:

* Los nombres de estas propiedades son *case-insensitive*. Por ejemplo, `$object->label` y `$object->Label` son la
  misma. Esto se debe a que los nombres de los métodos en PHP son case-insensitive.
* Si el nombre de una propiedad de este tipo es igual al de una variable miembro de la clase, la segunda tendrá
  prioridad. Por ejemplo, si la anterior clase `Foo` tiene la variable miembro `label`, entonces la asignación
  `$object->label = 'abc'` afectará a la *variable miembro* 'label'; no se ejecutará el método setter `setLabel()`.
* Estas variables no soportan la visibilidad. No hay diferencia en definir los métodos getter o setter en una
  propiedad public, protected, o private.
* Las propiedades sólo se pueden definir por getters y setters *no estáticos*. Los métodos estáticos no se tratarán de
  la misma manera.

Volviendo de nuevo al problema descrito al principio de la guía, en lugar de ejecutar `trim()` cada vez que se asigne
un valor a `label`, ahora `trim()` sólo necesita ser invocado dentro del setter `setLabel()`. I si se tiene que añadir
un nuevo requerimiento, para que `label` empiece con una letra mayúscula, se puede modificar rápidamente el método `
setLabel()` sin tener que modificar más secciones de código. El cambio afectará a cada asignación de `label`.
