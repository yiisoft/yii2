Comportamientos
===============

Comportamientos son instancias de [[yii\base\Behavior]] o sus clases "hija". Comportamientos, también conocido como
[mixins](http://en.wikipedia.org/wiki/Mixin), te permiten mejorar la funcionalidad de un [[yii\base\Component|componente]]
existente sin necesidad de modificar su herencia de clases.
Cuando un comportamiento se une a un componente, "inyectará" sus métodos y propiedades dentro del componente, y podrás
acceder a esos métodos y propiedades como si hubieran estado definidos por la clase de componente. Además, un
comportamiento puede responder a [eventos](concept-events.md) disparados por el componente de modo que se pueda personalizar
o adaptar a la ejecución normal del código del componente.


Usando comportamientos <a name="using-behaviors"></a>
----------------------

Para poder utilizar un comportamiento, primero tienes que unirlo a un [[yii\base\Component|componente]]. Describiremos cómo
puedes vincular un comportamiento en la próxima sub-sección.

Una vez que el comportamiento ha sido vinculado a un componente, su uso es sencillo.

Puedes usar a una variable *pública* o a una [propiedad](concept-properties.md) definida por un `getter` y/o un `setter`
del comportamiento a través del componente con el que se ha vinculado, como por ejemplo,

```php
// "prop1" es una propiedad definida en la clase comportamiento
echo $component->prop1;
$component->prop1 = $value;
```

También puedes llamar métodos *públicos* del comportamiento de una forma similar,

```php
// bar() es un método público definido dentro de la clase comportamiento
$component->bar();
```

Como puedes ver, aunque `$component` no tiene definida `prop1` y `bar()`, pueden ser usadas como si fueran parte
definida del componente.

Si dos comportamientos definen la misma propiedad o método y ambos están vinculados con el mismo componente, el
comportamiento que ha sido vinculado primero tendrá preferencia cuando se esté accediendo a la propiedad o método.

Un comportamiento puede estar asociado con un nombre cuando se une a un componente. Si este es el caso, es posible
acceder al objeto de comportamiento mediante el nombre, como se muestra a continuación,

```php
$behavior = $component->getBehavior('myBehavior');
```

También puedes acceder a todos los comportamientos vinculados al componente:

```php
$behaviors = $component->getBehaviors();
```


Vinculando Comportamientos <a name="attaching-behaviors"></a>
--------------------------

Puedes vincular un comportamiento a un [[yii\base\Component|componente]] ya sea estática o dinámicamente. La primera forma
es la más comúnmente utilizada en la práctica.

Para unir un comportamiento estáticamente, reemplaza el método [[yii\base\Component::behaviors()|behaviors()]] de la
clase componente que se está conectando. Por ejemplo,

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // comportamiento anónimo, sólo el nombre de la clase del comportamiento
            MyBehavior::className(),

            // comportamiento nombrado, sólo el nombre de la clase del comportamiento
            'myBehavior2' => MyBehavior::className(),

            // comportamiento anónimo, matriz de configuración
            [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // comportamiento nombrado, matriz de configuración
            'myBehavior4' => [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

El método [[yii\base\Component::behaviors()|behaviors()]] tiene que devolver la lista de los comportamientos
[configuraciones](concept-configurations.md).
Cada configuración de un comportamiento puede ser el nombre de la clase o una matriz de configuración.

Puedes asociciar un nombre a un comportamiento especificándolo en la clave de la matriz correspondiente a la configuración
del comportamiento. En este caso, el comportamiento puede ser llamado un *comportamiento nombrado* (named behavior). En
el ejemplo anterior, hay dos tipos de comportamientos nombrados: `myBehavior2` y `myBehavior4`. Si un comportamiento
no está asociado con un nombre, se le llama *comportamiento anónimo* (anonymous behavior).

Para vincular un comportamiento dinámicamente, llama al método [[yii\base\Component::attachBehavior()]] desde el componente al
que se le va a unir el comportamiento. Por ejemplo,

```php
use app\components\MyBehavior;

// vincular un objeto comportamiento "behavior"
$component->attachBehavior('myBehavior1', new MyBehavior);

// vincular una clase comportamiento
$component->attachBehavior('myBehavior2', MyBehavior::className());

// asociar una matriz de configuración
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::className(),
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

You may attach multiple behaviors at once by using the [[yii\base\Component::attachBehaviors()]] method.
For example,

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // a named behavior
    MyBehavior::className(),          // an anonymous behavior
]);
```

También puedes asociar comportamientos a traves de [configuraciones](concept-configurations.md) compor el siguiente
ejemplo. Para más detalles, por favor visita la sección [Configuraciones](concept-configurations.md#configuration-format).

```php
[
    'as myBehavior2' => MyBehavior::className(),

    'as myBehavior3' => [
        'class' => MyBehavior::className(),
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```


Desasociar Comportamientos <a name="detaching-behaviors"></a>
--------------------------

Para desasociar un comportamiento, puedes llamar el método [[yii\base\Component::detachBehavior()]] con el nombre con el
que se le asoció:

```php
$component->detachBehavior('myBehavior1');
```

También puedes desvincular *todos* los comportamientos:

```php
$component->detachBehaviors();
```


Definiendo Comportamientos <a name="defining-behaviors"></a>
--------------------------

Para definir un comportamiento, crea una clase extendendiéndola de [[yii\base\Behavior]] o una de sus clases "hija". Por ejemplo,

```php
namespace app\components;

use yii\base\Model;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

El código anterior define la clase del comportamiento `app\components\MyBehavior` que provee dos propiedades `prop1` y
`prop2`, y un método `foo()` al componente con el que está asociado.

The above code defines the behavior class `app\components\MyBehavior` which will provide two properties
`prop1` and `prop2`, and one method `foo()` to the component it is attached to. Fíjese que la propiedad `prop2` esta
definida a través del getter `getProp2()` y el setter `setProp2()`. Esto es debido a que [[yii\base\Object]] es una
clase "ancestro" (o padre) de [[yii\base\Behavior]], la cual soporta la definición de [propiedades](concept-properties.md) por
getters/setters.

En un comportamiento, puedes acceder al componente al que está vinculado a través de la propiedad [[yii\base\Behavior::owner]].

Si un omportamiento necesita responder a los eventos que han sido disparados desde el componente al qu están asociados,
debería sobreescribir el método [[yii\base\Behavior::events()]]. Por ejemplo,

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

El método [[yii\base\Behavior::events()|events()]] tiene que devolver un listado de eventos y sus correspondientes
controladores (handlers). El código anterior declara el evento [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]]
con su controlador `beforeValidate()`. Cuando se especifica un controlador de evento, pudes utilizar uno de los siguientes
formatos:

* una cadena que se refiere al nombre de un método de la clase comportamiento, como el ejemplo anterior;
* una matriz con un objeto o nombre de la clase, y el nombre de un método, por ejemplo, `[$object, 'nombreMétodo']`;
* una función anónima.

El formato de un controlador de eventos tendría que ser como se describe a continuación, donde `$event` se refiere al
parámetro `evento`. Por favor, visita la sección [Eventos](concept-events.md) para obtener más información acerca de
eventos.

```php
function ($event) {
}
```


Utilizando `TimestampBehavior` <a name="using-timestamp-behavior"></a>
-----------------------------

Para terminar, vamos a echar un vistazo a [[yii\behaviors\TimestampBehavior]] - un comportamiento que soporta de forma
automática la actualización de atributos `timestamp` (sellos de tiempo) de un [[yii\db\ActiveRecord|Registro Activo]]
(Active Record) cuando éste está siendo guardado.

Primero, vincula este comportamiento a la clase [[yii\db\ActiveRecord|Active Record]] que desees utilizar.

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
```

La configuración del comportamiento anterior especifica que

* cuando el registro está siendo insertado, el comportamiento debe asignar el sello de tiempo actual a los atributos
  `created_at` y `updated_at`;
* cuando el registro está siendo actualizado, el comportamiento debe asignar el sello de tiempo actual al atributo
  `updated_at.

Ahora si tienes un objeto `User` e intentas guardarlo, descubrirás que sus campos `created_at` y `updated_at` están
automáticamente actualizados con el sello de tiempo actual:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // muestra el sello tiempo actual (timestamp)
```

El comportamiento [[yii\behaviors\TimestampBehavior|TimestampBehavior]] también ofrece un método muy útil llamado
[[yii\behaviors\TimestampBehavior::touch()|touch()]], que asigna el sello de tiempo actual a un atributo especificado y
lo guarda automáticamente en la base de datos:

```php
$user->touch('login_time');
```


Comparación con Traits <a name="comparison-with-traits"></a>
----------------------

Mientras que los comportamientos son similares a [traits](http://www.php.net/traits) en cuanto que ambos "inyectan" sus
métodos  y propiedades a la clase primaria, son diferentes en muchos aspectos. Tal y como se describe abajo, los dos
tienen sus ventajas y desventajas. Son much mejor descritos como complementos y no como reemplazos entre sí.


### Las Ventajas de los Comportamientos <a name="pros-for-behaviors"></a>

Las clases de comportamientos (Behaviors), como todas las clases, soportan herencias. Traits, por otro lado, pueden ser
considerados como un copia-y-pega de PHP. Los Traits no soportan la herencia de clases.

Los comportamientos pueden ser asociados y desasociados a un componente dinámicamente sin necesidad de que la clase del
componente sea modificada. Para usar un trait, debes modificar la clase que la usa.

Los comportamientos son configurables mientras que los traits no.

Los comportamientos pueden personalizar la ejecución de un componente al responder a sus eventos.

Cuando hay un conflicto de nombre entre los diferentes comportamientos vinculados a un mismo componente, el conflicto es
automáticamente resuelto respetando al que ha sido asociado primero.
El conflicto de nombres en traits requiere que manualmente sean resueltos cambiando el nombre de las propiedades o métodos
afectados.


### Las Ventajas de los Traits <a name="pros-for-traits"></a>

Los Traits son mucho más eficientes que los comportamientos debido a que los últimos son objetos que consumen tiempo y
memoria.

Los IDEs (Programas de desarrollo) trabajan mucho mejor con traits ya que forman parte del lenguaje PHP.

