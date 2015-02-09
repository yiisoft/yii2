Eventos
=======

Los eventos permiten inyectar código dentro de otro código existente en ciertos puntos de ejecución. Se pueden adjuntar
código personalizado a un evento, cuando se lance (triggered), el código se ejecutará automáticamente. Por ejemplo, un
objeto mailer puede lanzar el evento `messageSent` cuando se envía un mensaje correctamente. Si se quiere rastrear
el correcto envío del mensaje, se puede, simplemente, añadir un código de seguimiento al evento `messageSent`.

Yii introduce una clase base [[yii\base\Component]] para soportar eventos. Si una clase necesita lanzar un evento,
este debe extender a [[yii\base\Component]] o a una clase hija.

Gestor de Eventos <span id="event-handlers"></span>
-----------------

Un gestor de eventos es una
[llamada de retorno PHP (PHP callback)](http://php.net/manual/es/language.types.callable.php) que se ejecuta cuando se
lanza el evento al que corresponde. Se puede usar cualquier llamada de retorno de las enumeradas a continuación:

- una función de PHP global especificada como una cadena de texto (sin paréntesis), ej. `'trim'`;
- un método de objeto especificado como un array de un objeto y un nombre de método como una cadena de texto
  (sin paréntesis), ej. `[$object, 'methodNAme']`;
- un método de clase estático especificado como un array de un nombre de clase y un método como una cadena de texto
  (sin paréntesis), ej. `[$class, 'methodName']`;
- una función anónima, ej. `function ($event) { ... }`.

La firma de un gestor de eventos es:

```php
function ($event) {
    // $event es un objeto de yii\base\Event o de una clase hija
}
```

Un gestor de eventos puede obtener la siguiente información acerca de un evento ya sucedido mediante el parámetro
`$event`:

- [[yii\base\Event::name|nombre del evento]]
- [[yii\base\Event::sender|evento enviando]]: el objeto desde el que se ha ejecutado `trigger()`
- [[yii\base\Event::data|custom data]]: los datos que se proporcionan al adjuntar el gestor de eventos
  (se explicará más adelante)


Añadir Gestores de Eventos <span id="attaching-event-handlers"></span>
--------------------------

Se puede añadir un gestor a un evento llamando al método [[yii\base\Component::on()]]. Por ejemplo:

```php
$foo = new Foo;

// este gestor es una función global
$foo->on(Foo::EVENT_HELLO, 'function_name');

// este gestor es un método de objeto
$foo->on(Foo::EVENT_HELLO, [$object, 'methodName']);

// este gestor es un método de clase estática
$foo->on(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// este gestor es una función anónima
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // event handling logic
});
```

También se pueden adjuntar gestores de eventos mediante [configuraciones](concept-configurations.md). Se pueden
encontrar más de talles en la sección [Configuraciones](concept-configurations.md#configuration-format).

Cuando se adjunta un gestor de eventos, se pueden proporcionar datos adicionales como tercer parámetro de
[[yii\base\Component::on()]]. El gestor podrá acceder a los datos cuando se lance el evento y se ejecute el gestor.
Por ejemplo:

```php
// El siguiente código muestra "abc" cuando se lanza el evento
// ya que $event->data contiene los datos enviados en el tercer parámetro de "on"
$foo->on(Foo::EVENT_HELLO, 'function_name', 'abc');

function function_name($event) {
    echo $event->data;
}
```

Ordenación de Gestores de Eventos
---------------------------------

Se puede adjuntar uno o más gestores a un único evento. Cuando se lanza un evento, se ejecutarán los gestores adjuntos
en el orden que se hayan añadido al evento. Si un gestor necesita parar la invocación de los gestores que le siguen,
se puede establecer la propiedad [[yii\base\Event::handled]] del parámetro `$event` para que sea `true`:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    $event->handled = true;
});
```

De forma predeterminada, cada nuevo gestor añadido se pone a la cola de la lista de gestores del evento. Por lo tanto,
el gestor se ejecutará en el último lugar cuando se lance el evento. Para insertar un nuevo gestor al principio de la
cola de gestores para que sea ejecutado primero, se debe llamar a [[yii\base\Component::on()]], pasando al cuarto
parámetro `$append` el valor false:

```php
$foo->on(Foo::EVENT_HELLO, function ($event) {
    // ...
}, $data, false);
```

Lanzamiento de Eventos <span id="triggering-events"></span>
----------------------

Los eventos se lanzan llamando al método [[yii\base\Component::trigger()]]. El método requiere un *nombre de evento*,
y de forma opcional un objeto de evento que describa los parámetros que se enviarán a los gestores de eventos. Por
ejemplo:

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class Foo extends Component
{
    const EVENT_HELLO = 'hello';

    public function bar()
    {
        $this->trigger(self::EVENT_HELLO);
    }
}
```

Con el código anterior, cada llamada a `bar()` lanzará un evento llamado `hello`

> Consejo: Se recomienda usar las constantes de clase para representar nombres de eventos. En el anterior ejemplo, la
  constante `EVENT_HELLO` representa el evento `hello`. Este enfoque proporciona tres beneficios. Primero, previene
  errores tipográficos. Segundo, puede hacer que los IDEs reconozcan los eventos en las funciones de auto-completado.
  Tercero, se puede ver que eventos soporta una clase simplemente revisando la declaración de constantes.

A veces cuando se lanza un evento se puede querer pasar información adicional al gestor de eventos. Por ejemplo, un
mailer puede querer enviar la información del mensaje para que los gestores del evento `messageSent` para que los
gestores puedan saber las particularidades del mensaje enviado. Para hacerlo, se puede proporcionar un objeto de tipo
evento como segundo parámetro al método [[yii\base\Component::trigger()]]. El objeto de tipo evento debe ser una
instancia de la clase [[yii\base\Event]] o de su clase hija. Por ejemplo:

```php
namespace app\components;

use yii\base\Component;
use yii\base\Event;

class MessageEvent extends Event
{
    public $message;
}

class Mailer extends Component
{
    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message)
    {
        // ...enviando $message...

        $event = new MessageEvent;
        $event->message = $message;
        $this->trigger(self::EVENT_MESSAGE_SENT, $event);
    }
}
```

Cuando se lanza el método [[yii\base\Component::trigger()]], se ejecutarán todos los gestores adjuntos al evento.

Desadjuntar Gestores de Evento <span id="detaching-event-handlers"></span>
------------------------------

Para desadjuntar un gestor de un evento, se puede ejecutar el método [[yii\base\Component::off()]]. Por ejemplo:

```php
// el gestor es una función global
$foo->off(Foo::EVENT_HELLO, 'function_name');

// el gestor es un método de objeto
$foo->off(Foo::EVENT_HELLO, [$object, 'methodName']);

// el gestor es un método estático de clase
$foo->off(Foo::EVENT_HELLO, ['app\components\Bar', 'methodName']);

// el gestor es una función anónima
$foo->off(Foo::EVENT_HELLO, $anonymousFunction);
```

Tenga en cuenta que en general no se debe intentar desadjuntar las funciones anónimas a no ser que se almacene donde
se ha adjuntado al evento. En el anterior ejemplo, se asume que la función anónima se almacena como variable
`$anonymousFunction`.

Para desadjuntar TODOS los gestores de un evento, se puede llamar [[yii\base\Component::off()]] sin el segundo
parámetro:

```php
$foo->off(Foo::EVENT_HELLO);
```

Nivel de Clase (Class-Level) Gestores de Eventos <span id="class-level-event-handlers"></span>
------------------------------------------------

En las subsecciones anteriores se ha descrito como adjuntar un gestor a un evento a *nivel de instancia*. A veces, se
puede querer que un gestor responda todos los eventos de *todos* las instancias de una clase en lugar de una instancia
especifica. En lugar de adjuntar un gestor de eventos a una instancia, se puede adjuntar un gestor a *nivel de clase*
llamando al método estático [[yii\base\Event::on()]].

Por ejemplo, un objeto de tipo [Active Record](db-active-record.md) lanzará un evento
[[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] cada vez que inserte un nuevo registro en la base
de datos. Para poder registrar las inserciones efectuadas por *todos* los objetos
[Active Record](db-active-record.md), se puede usar el siguiente código:

```php
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;

Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted');
});
```

Se invocará al gestor de eventos cada vez que una instancia de [[yii\db\ActiveRecord|ActiveRecord]], o de uno de sus
clases hijas, lance un evento de tipo [[yii\db\BaseActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]]. Se puede
obtener el objeto que ha lanzado el evento mediante `$event->sender` en el gestor.

Cuando un objeto lanza un evento, primero llamará los gestores a nivel de instancia, y a continuación los gestores a
nivel de clase.

Se puede lanzar un evento de tipo *nivel de clase* llamando al método estático [[yii\base\Event::trigger()]]. Un
evento de nivel de clase no se asocia a un objeto en particular. Como resultado, esto provocará solamente la
invocación de los gestores de eventos a nivel de clase.

```php
use yii\base\Event;

Event::on(Foo::className(), Foo::EVENT_HELLO, function ($event) {
    echo $event->sender;  // displays "app\models\Foo"
});

Event::trigger(Foo::className(), Foo::EVENT_HELLO);
```

Tenga en cuenta que en este caso, el `$event->sender` hace referencia al nombre de la clase que lanza el evento en
lugar de a la instancia del objeto.

> Nota: Debido a que los gestores a nivel de clase responderán a los eventos lanzados por cualquier instancia de la
clase, o cualquier clase hija, se debe usar con cuidado, especialmente en las clases de bajo nivel (low-level), tales
como [[yii\base\Object]].

Para desadjuntar un gestor de eventos a nivel de clase, se tiene que llamar a [[yii\base\Event::off()]]. Por ejemplo:

```php
// desadjunta $handler
Event::off(Foo::className(), Foo::EVENT_HELLO, $handler);

// desadjunta todos los gestores de Foo::EVENT_HELLO
Event::off(Foo::className(), Foo::EVENT_HELLO);
```

Eventos Globales <span id="global-events"></span>
----------------

Yii soporta los llamados *eventos globales*, que en realidad es un truco basado en el gestor de eventos descrito
anteriormente. El evento global requiere un Singleton globalmente accesible, tal como la instancia de
[aplicación](structure-applications.md) en si misma.

Para crear un evento global, un evento remitente (event sender) llama al método `trigger()` del Singleton para lanzar
el evento, en lugar de llamar al propio método `trigger()` del remitente. De forma similar, los gestores de eventos se
adjuntan al evento del Singleton. Por ejemplo:

```php
use Yii;
use yii\base\Event;
use app\components\Foo;

Yii::$app->on('bar', function ($event) {
    echo get_class($event->sender);  // muestra "app\components\Foo"
});

Yii::$app->trigger('bar', new Event(['sender' => new Foo]));
```

Un beneficio de usar eventos globales es que no se necesita un objeto cuando se adjuntan gestores a un evento para que
sean lanzados por el objeto. En su lugar, los gestores adjuntos y el lanzamiento de eventos se efectúan en el
Singleton (ej. la instancia de la aplicación).

Sin embargo, debido a que los `namespaces` de los eventos globales son compartidos por todas partes, se les deben
asignar nombres bien pensados, como puede ser la introducción de algún `namespace`
(ej. "frontend.mail.sent", "backend.mail.sent").
