Comportamientos
===============

Comportamientos son instancias de [[yii\base\Behavior]] o sus clases "hija". Comportamientos, también conocido como
[mixins](https://es.wikipedia.org/wiki/Mixin), te permiten mejorar la funcionalidad de un [[yii\base\Component|componente]]
existente sin necesidad de modificar su herencia de clases.
Cuando un comportamiento se une a un componente, "inyectará" sus métodos y propiedades dentro del componente, y podrás
acceder a esos métodos y propiedades como si hubieran estado definidos por la clase de componente. Además, un
comportamiento puede responder a [eventos](concept-events.md) disparados por el componente de modo que se pueda personalizar
o adaptar a la ejecución normal del código del componente.


Definiendo comportamientos <span id="defining-behaviors"></span>
--------------------------

Para definir un comportamiento, se debe crear una clase que exiende [[yii\base\Behavior]], o se extiende una clase hija. Por ejemplo:

```php
namespace app\components;

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
El código anterior define la clase de comportamiento (behavior) app\components\MyBehavior`, con dos propiedades --
`prop1` y `prop2`--y un método `foo()`. Tenga en cuenta que la propiedad `prop2`
se define a través de la getter `getProp2()` y el setter `setProp2()`. Este caso es porque [[yii\base\Behavior]] extiende [[yii\base\BaseObject]] y por lo tanto se apoya en la definición de [propiedades](concept-properties.md) via getters y setters.

Debido a que esta clase es un comportamiento, cuando está unido a un componente, el componente también tienen la propiedad `prop1` y `prop2` y el método `foo()`.

> Tip: Dentro de un comportamiento, puede acceder al componente que el comportamiento está unido a través de la propiedad [[yii\base\Behavior::owner]].


Gestión de eventos de componentes
---------------------------------

Si un comportamiento necesita responder a los acontecimientos desencadenados por el componente al que está unido, se debe reemplazar el método [[yii\base\Behavior::events()]]. Por ejemplo:

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

El método [[yii\base\Behavior::events()|events()]] debe devolver una lista de eventos y sus correspondientes controladores.
El ejemplo anterior declara que el evento [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] existe y esta  exists y define su controlador, `beforeValidate()`. Al especificar un controlador de eventos, puede utilizar uno de los siguientes formatos:

* una cadena que se refiere al nombre de un método de la clase del comportamiento, como el ejemplo anterior
* un arreglo de objeto o nombre de clase, y un nombre de método como una cadena (sin paréntesis), ej., `[$object, 'methodName']`;
* una función anónima 

La firma de un controlador de eventos debe ser la siguiente, donde `$ event` refiere al parámetro de evento. Por favor, consulte la sección [Eventos](concept-events.md) para más detalles sobre los eventos.

```php
function ($event) {
}
```


Vinculando Comportamientos <span id="attaching-behaviors"></span>
--------------------------

Puedes vincular un comportamiento a un [[yii\base\Component|componente]] ya sea estática o dinámicamente. La primera forma
es la más comúnmente utilizada en la práctica.

Para unir un comportamiento estáticamente, reemplaza el método [[yii\base\Component::behaviors()|behaviors()]] dde la clase de componente a la que se une el comportamiento. El método [[yii\base\Component::behaviors()|behaviors()]]  debe devolver una lista de comportamiento [configuraciones](concept-configurations.md).
Cada configuración de comportamiento puede ser un nombre de clase de comportamiento o un arreglo de configuración:

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // anonymous behavior, behavior class name only
            MyBehavior::class,

            // named behavior, behavior class name only
            'myBehavior2' => MyBehavior::class,

            // anonymous behavior, configuration array
            [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // named behavior, configuration array
            'myBehavior4' => [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

Puedes asociciar un nombre a un comportamiento especificándolo en la clave de la matriz correspondiente a la configuración
del comportamiento. En este caso, el comportamiento puede ser llamado un *comportamiento nombrado* (named behavior). En
el ejemplo anterior, hay dos tipos de comportamientos nombrados: `myBehavior2` y `myBehavior4`. Si un comportamiento
no está asociado con un nombre, se le llama *comportamiento anónimo* (anonymous behavior).

Para vincular un comportamiento dinámicamente, llama al método [[yii\base\Component::attachBehavior()]] desde el componente al
que se le va a unir el comportamiento:

```php
use app\components\MyBehavior;

// vincular un objeto comportamiento "behavior"
$component->attachBehavior('myBehavior1', new MyBehavior());

// vincular una clase comportamiento
$component->attachBehavior('myBehavior2', MyBehavior::class);

// asociar una matriz de configuración
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::class,
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```
Puede vincular múltiples comportamientos a la vez mediante el uso del método [[yii\base\Component::attachBehaviors()]]. Por ejemplo,

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior(), // un comportamiento nombrado
    MyBehavior::class,                 // un comportamiento anónimo
]);
```

También puedes asociar comportamientos a traves de [configuraciones](concept-configurations.md) como el siguiente:

```php
[
    'as myBehavior2' => MyBehavior::class,

    'as myBehavior3' => [
        'class' => MyBehavior::class,
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

Para más detalles, por favor visita la sección [Configuraciones](concept-configurations.md#configuration-format).


Usando comportamientos <span id="using-behaviors"></span>
----------------------

Para poder utilizar un comportamiento, primero tienes que unirlo a un [[yii\base\Component|componente]] según las instrucciones anteriores. Una vez que un comportamiento ha sido vinculado a un componente, su uso es sencillo.

Puedes usar a una variable *pública* o a una [propiedad](concept-properties.md) definida por un `getter` y/o un `setter`
del comportamiento a través del componente con el que se ha vinculado:

```php
// "prop1" es una propiedad definida en la clase comportamiento
echo $component->prop1;
$component->prop1 = $value;
```

También puedes llamar métodos *públicos* del comportamiento de una forma similar:

```php
// foo() es un método público definido dentro de la clase comportamiento
$component->foo();
```

Como puedes ver, aunque `$component` no tiene definida `prop1` y `bar()`, que se pueden utilizar como si son parte
de la definición de componentes debido al comportamiento vinculado.

Si dos comportamientos definen la misma propiedad o método y ambos están vinculados con el mismo componente, el
comportamiento que ha sido vinculado *primero* tendrá preferencia cuando se esté accediendo a la propiedad o método.

Un comportamiento puede estar asociado con un nombre cuando se une a un componente. Si este es el caso, es posible
acceder al objeto de comportamiento mediante el nombre, como se muestra a continuación,

```php
$behavior = $component->getBehavior('myBehavior');
```

También puedes acceder a todos los comportamientos vinculados al componente:

```php
$behaviors = $component->getBehaviors();
```


Desasociar Comportamientos <span id="detaching-behaviors"></span>
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


Utilizando `TimestampBehavior` <span id="using-timestamp-behavior"></span>
-----------------------------

Para terminar, vamos a echar un vistazo a [[yii\behaviors\TimestampBehavior]]. Este comportamiento soporta de forma
automática la actualización de atributos timestamp de un modelo [[yii\db\ActiveRecord|Registro Activo]]
(Active Record) en cualquier momento donde se guarda el modelo (ej., en la inserción o actualización).

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
                'class' => TimestampBehavior::class,
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
  `updated_at`.

Ahora si tienes un objeto `User` e intentas guardarlo, descubrirás que sus campos `created_at` y `updated_at` están
automáticamente actualizados con el sello de tiempo actual:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // muestra el sello tiempo actual (timestamp)
```

El comportamiento [[yii\behaviors\TimestampBehavior|TimestampBehavior]] también ofrece un método muy útil llamado
[[yii\behaviors\TimestampBehavior::touch()|touch()]], que asigna el sello de tiempo actual a un atributo especificado y lo guarda automáticamente en la base de datos:

```php
$user->touch('login_time');
```


Comparación con Traits <span id="comparison-with-traits"></span>
----------------------

Mientras que los comportamientos son similares a [traits](https://www.php.net/manual/es/language.oop5.traits.php) en cuanto que ambos "inyectan" sus
métodos  y propiedades a la clase primaria, son diferentes en muchos aspectos. Tal y como se describe abajo, los dos
tienen sus ventajas y desventajas. Son más como complementos el uno al otro en lugar de alternativas.


### Razones para utilizar comportamientos <span id="pros-for-behaviors"></span>

Las clases de comportamientos, como todas las clases, soportan herencias. Traits, por otro lado, pueden ser
considerados como un copia-y-pega de PHP. Ellos no soportan la herencia de clases.

Los comportamientos pueden ser asociados y desasociados a un componente dinámicamente sin necesidad de que la clase del
componente sea modificada. Para usar un trait, debes modificar la clase que la usa.

Los comportamientos son configurables mientras que los traits no.

Los comportamientos pueden personalizar la ejecución de un componente al responder a sus eventos.

Cuando hay un conflicto de nombre entre los diferentes comportamientos vinculados a un mismo componente, el conflicto es
automáticamente resuelto respetando al que ha sido asociado primero.
El conflicto de nombres en traits requiere que manualmente sean resueltos cambiando el nombre de las propiedades o métodos afectados.


### Razones para utilizar los Traits <span id="pros-for-traits"></span>

Los Traits son mucho más eficientes que los comportamientos debido a que los últimos son objetos que consumen tiempo y
memoria.

Los IDEs (Programas de desarrollo) son más amigables con traits ya que son una construcción del lenguaje nativo.

