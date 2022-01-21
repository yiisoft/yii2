Actualizar desde Yii 1.1
========================

Existen muchas diferencias entre las versiones 1.1 y 2.0 de Yii ya que el framework fue completamente reescrito
en su segunda versión.
Como resultado, actualizar desde la versión 1.1 no es tan trivial como actualizar entre versiones menores. En esta
guía encontrarás las diferencias más grandes entre estas dos versiones.

Si no has utilizado Yii 1.1 antes, puedes saltarte con seguridad esta sección e ir directamente a "[Comenzando con Yii](start-installation.md)".

Es importante anotar que Yii 2.0 introduce más características de las que van a ser cubiertas en este resumen. Es altamente recomendado
que leas a través de toda la guía definitiva para aprender acerca de todas ellas. Hay muchas posibilidades de que algo que hayas desarrollado anteriormente para extender Yii, sea ahora parte del núcleo de la librería.


Instalación
------------

Yii 2.0 adopta íntegramente [Composer](https://getcomposer.org/), el administrador de paquetes de facto de PHP.
Tanto la instalación del núcleo del framework como las extensiones se manejan a través de Composer. Por favor consulta
la sección [Comenzando con la Aplicación Básica](start-installation.md) para aprender a instalar Yii 2.0. Si quieres crear extensiones
o transformar extensiones de Yii 1.1 para que sean compatibles con Yii 2.0, consulta
la sección [Creando Extensiones](structure-extensions.md#creating-extensions) de la guía.


Requerimientos de PHP
---------------------

Yii 2.0 requiere PHP 5.4 o mayor, lo que es un gran progreso ya que Yii 1.1 funcionaba con PHP 5.2.
Como resultado, hay muchas diferencias a nivel del lenguaje a las que deberías prestar atención.
Abajo hay un resumen de los mayores cambios en relación a PHP:

- [Namespaces](https://www.php.net/manual/es/language.namespaces.php).
- [Funciones anónimas](https://www.php.net/manual/es/functions.anonymous.php).
- La sintaxis corta de Arrays `[...elementos...]` es utilizada en vez de `array(...elementos...)`.
- Etiquetas cortas de `echo`. Ahora en las vistas se usa `<?=`. Esto se puede utilizar desde PHP 5.4.
- [SPL - Biblioteca estándar de PHP](https://www.php.net/manual/es/book.spl.php).
- [Enlace estático en tiempo de ejecución](https://www.php.net/manual/es/language.oop5.late-static-bindings.php).
- [Fecha y Hora](https://www.php.net/manual/es/book.datetime.php).
- [Traits](https://www.php.net/manual/es/language.oop5.traits.php).
- [intl](https://www.php.net/manual/es/book.intl.php). Yii 2.0 utiliza la extensión `intl` de PHP
  como soporte para internacionalización.


Namespace
---------

El cambio más obvio en Yii 2.0 es el uso de namespaces. Casi todas las clases del núcleo
utilizan namespaces, ej., `yii\web\Request`. El prefijo "C" no se utiliza más en los nombre de clases.
El esquema de nombres sigue la estructura de directorios. Por ejemplo, `yii\web\Request`
indica que el archivo de la clase correspondiente `web/Request.php` está bajo el directorio de Yii framework.

(Puedes utilizar cualquier clase del núcleo sin necesidad de incluir el archivo que la contiene, gracias
al autoloader de Yii.)


Componentes y Objetos
----------------------

Yii 2.0 parte la clase `CComponent` de 1.1 en dos clases: [[yii\base\BaseObject]] y [[yii\base\Component]].
La clase [[yii\base\BaseObject|BaseObject]] es una clase base que permite definir [propiedades de object](concept-properties.md)
a través de getters y setters. La clase [[yii\base\Component|Component]] extiende de [[yii\base\BaseObject|BaseObject]] y soporta
[eventos](concept-events.md) y [comportamientos](concept-behaviors.md).

Si tu clase no necesita utilizar las características de eventos o comportamientos, puedes considerar usar
[[yii\base\BaseObject|BaseObject]] como clase base. Esto es frecuente en el caso de que las clases que representan sean
estructuras de datos básicas.


Configuración de objetos
------------------------

La clase [[yii\base\BaseObject|BaseObject]] introduce una manera uniforme de configurar objetos. Cualquier clase descendiente
de [[yii\base\BaseObject|BaseObject]] debería declarar su constructor (si fuera necesario) de la siguiente manera para que
puede ser adecuadamente configurado:

```php
class MyClass extends \yii\base\BaseObject
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... se aplica la inicialización antes de la configuración

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... se aplica la inicialización después de la configuración
    }
}
```

En el ejemplo de arriba, el último parámetro del constructor debe tomar un array de configuración que
contiene pares clave-valor para la inicialización de las propiedades al final del mismo.
Puedes sobrescribir el método [[yii\base\BaseObject::init()|init()]] para realizar el trabajo de inicialización
que debe ser hecho después de que la configuración haya sido aplicada.

Siguiendo esa convención, podrás crear y configurar nuevos objetos utilizando
un array de configuración:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Se puede encontrar más detalles acerca del tema en la sección [Configuración](concept-configurations.md).


Eventos
-------

En Yii 1, los eventos eran creados definiendo un método `on` (ej., `onBeforeSave`). En Yii 2, puedes utilizar cualquier nombre de evento.
Ahora puedes disparar un evento utilizando el método [[yii\base\Component::trigger()|trigger()]]:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Para conectar un manejador a un evento, utiliza el método [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// Para desconectar el manejador, utiliza:
// $component->off($eventName, $handler);
```

Hay muchas mejoras en lo que respecta a eventos. Para más detalles, consulta la sección [Eventos](concept-events.md).


Alias
-----

Yii 2.0 extiende el uso de alias tanto para archivos/directorios como URLs. Yii 2.0 ahora requiere que cada
alias comience con el carácter `@`, para diferenciarlos de rutas o URLs normales.
Por ejemplo, el alias `@yii` corresponde al directorio donde Yii se encuentra instalado. Los alias
están soportados en la mayor parte del núcleo. Por ejemplo, [[yii\caching\FileCache::cachePath]] puede tomar tanto
una ruta de directorios normal como un alias.

Un alias está estrechamente relacionado con un namespace de la clase. Se recomienda definir un alias
por cada namespace raíz, y así poder utilizar el autoloader de Yii sin otra configuración.
Por ejemplo, debido a que `@yii` se refiere al directorio de instalación, una clase
como `yii\web\Request` puede ser auto-cargada. Si estás utilizando una librería de terceros,
como Zend Framework, puedes definir un alias `@Zend` que se refiera al directorio de instalación
de ese framework. Una vez realizado esto, Yii será capaz de auto-cargar cualquier clase de Zend Framework también.

Se puede encontrar más detalles del tema en la sección [Alias](concept-aliases.md).


Vistas
------

El cambio más significativo con respecto a las vistas en Yii 2 es que la variable especial `$this` dentro de una vista
ya no se refiere al controlador o widget actual. En vez de eso, `$this` ahora se refiere al objeto de la *vista*, un concepto nuevo
introducido en Yii 2.0. El objeto *vista* es del tipo [[yii\web\View]], que representa la parte de las vistas
en el patrón MVC. Si quieres acceder al controlador o al widget correspondiente desde la propia vista, puedes utilizar `$this->context`.

Para renderizar una vista parcial (partial) dentro de otra vista, se utiliza `$this->render()`, no `$this->renderPartial()`. La llamada a `render` además tiene que ser mostrada explícitamente a través de `echo`,
ya que el método `render()` devuelve el resultado de la renderización en vez de mostrarlo directamente. Por ejemplo:

```php
echo $this->render('_item', ['item' => $item]);
```

Además de utilizar PHP como el lenguaje principal de plantillas (templates), Yii 2.0 está también equipado con soporte
oficial de otros dos motores de plantillas populares: Smarty y Twig. El motor de plantillas de Prado ya no está soportado.
Para utilizar esos motores, necesitas configurar el componente `view` de la aplicación, definiendo la propiedad [[yii\base\View::$renderers|View::$renderers]].
Por favor consulta la sección [Motores de Plantillas](tutorial-template-engines.md)
para más detalles.


Modelos
-------

Yii 2.0 utiliza [[yii\base\Model]] como modelo base, algo similar a `CModel` en 1.1.
La clase `CFormModel` ha sido descartada por completo. Ahora, en Yii 2 debes extender de [[yii\base\Model]] para crear clases de modelos basados en formularios.

Yii 2.0 introduce un nuevo método llamado [[yii\base\Model::scenarios()|scenarios()]] para declarar escenarios soportados,
y para indicar bajo que escenario un atributo necesita ser validado, puede ser considerado seguro o no, etc. Por ejemplo:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

En el ejemplo anterior, se declaran dos escenarios: `backend` y `frontend`. Para el escenario `backend` son considerados seguros
ambos atributos, `email` y `role`, y pueden ser asignados masivamente. Para el escenario `frontend`, `email` puede ser asignado
masivamente mientras `role` no. Tanto `email` como `role` deben ser validados utilizando reglas (rules).

El método [[yii\base\Model::rules()|rules()]] aún es utilizado para declara reglas de validación.
Ten en cuenta que dada la introducción de [[yii\base\Model::scenarios()|scenarios()]], ya no existe el validador `unsafe`.

En la mayoría de los casos, no necesitas sobrescribir [[yii\base\Model::scenarios()|scenarios()]] si el método [[yii\base\Model::rules()|rules()]]
especifica completamente los escenarios que existirán, y si no hay necesidad de declarar atributos inseguros (`unsafe`).

Para aprender más detalles de modelos, consulta la sección [Modelos](structure-models.md).


Controladores
-------------

Yii 2.0 utiliza [[yii\web\Controller]] como controlador base, similar a `CWebController` en Yii 1.1.
[[yii\base\Action]] es la clase base para clases de acciones.

El impacto más obvio de estos cambios en tu código es que que cada acción del controlador debe devolver el contenido
que quieres mostrar en vez de mostrarlo directamente:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Por favor, consulta la sección [Controladores](structure-controllers.md) para más detalles acerca de los controladores.


Widgets
-------

Yii 2.0 utiliza [[yii\base\Widget]] como clase base de los widgets, similar a `CWidget` en Yii 1.1.

Para obtener mejor soporte del framework en IDEs, Yii 2.0 introduce una nueva sintaxis para utilizar widgets.
Los métodos estáticos [[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], y [[yii\base\Widget::widget()|widget()]]
fueron incorporados, y deben utilizarse así:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Ten en cuenta que debes pasar el resultado a "echo" para mostrarlo
echo Menu::widget(['items' => $items]);

// Pasando un array para inicializar las propiedades del objeto
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... campos del formulario aquí ...
ActiveForm::end();
```

Consulta la sección [Widgets](structure-widgets.md) para más detalles.


Temas
------

Los temas funcionan completamente diferente en Yii 2.0. Ahora están basados en un mecanismo de mapeo de rutas,
que mapea la ruta de un archivo de la vista de origen a uno con un tema aplicado. Por ejemplo, si el mapeo de ruta de un tema es
`['/web/views' => '/web/themes/basic']`, entonces la versión con el tema aplicado del archivo
`/web/views/site/index.php` será `/web/themes/basic/site/index.php`. Por esta razón, ahora los temas pueden ser
aplicados a cualquier archivo de la vista, incluso una vista renderizada fuera del contexto de un controlador o widget.

Además, el componente `CThemeManager` ya no existe. En cambio, `theme` es una propiedad configurable del componente `view`
de la aplicación.

Consulta la sección [Temas](output-theming.md) para más detalles.


Aplicaciones de Consola
-----------------------

Las aplicaciones de consola ahora están organizadas en controladores, tal como aplicaciones Web. Estos controladores
deben extender de [[yii\console\Controller]], similar a `CConsoleCommand` en 1.1.

Para correr un comando de consola, utiliza `yii <ruta>`, donde `<ruta>` se refiere a la ruta del controlador
(ej. `sitemap/index`). Los argumentos anónimos adicionales son pasados como parámetros al método de la acción correspondiente
del controlador, mientras que los argumentos especificados son pasados de acuerdo a las declaraciones en [[yii\console\Controller::options()]].

Yii 2.0 soporta la generación automática de información de ayuda de los comandos a través de los bloques de comentarios del archivo.

Por favor consulta la sección [Comandos de Consola](tutorial-console.md) para más detalles.


I18N
----

Yii 2.0 remueve el formateador de fecha y números previamente incluido en favor del [módulo de PHP PECL intl](https://pecl.php.net/package/intl).

La traducción de mensajes ahora es ejecutada vía el componente `i18n` de la aplicación.
Este componente maneja un grupo de mensajes origen, lo que te permite utilizar diferentes mensajes
basados en categorías.

Por favor, consulta la sección [Internacionalización](tutorial-i18n.md) para más información.


Filtros de Acciones
-------------------

Los filtros de acciones son implementados a través de comportamientos. Para definir un
nuevo filtro personalizado, se debe extender de [[yii\base\ActionFilter]]. Para utilizar el filtro, conecta la clase del filtro
al controlador como un comportamiento. Por ejemplo, para utilizar el filtro [[yii\filters\AccessControl]], deberías tener
el siguiente código en el controlador:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Consulta la sección [Filtrando](structure-filters.md) para una mayor información acerca del tema.


Assets
------

Yii 2.0 introduce un nuevo concepto llamado *asset bundle* que reemplaza el concepto de script package encontrado en Yii 1.1.

Un asset bundle es una colección de archivos assets (ej. archivos JavaScript, archivos CSS, imágenes, etc.) dentro de un directorio.
Cada asset bundle está representado por una clase que extiende de [[yii\web\AssetBundle]].
Al registrar un asset bundle a través de [[yii\web\AssetBundle::register()]], haces que los assets de dicho bundle sean accesibles
vía Web. A diferencia de Yii 1, la página que registra el bundle contendrá automáticamente las referencias a los archivos
JavaScript y CSS especificados en el bundle.

Por favor, consulta la sección [Manejando Assets](structure-assets.md) para más detalles.


Helpers
-------

Yii 2.0 introduce muchos helpers estáticos comúnmente utilizados, incluyendo:

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

Por favor, consulta la sección [Información General de Helpers](helper-overview.md) para más detalles.

Formularios
-----------

Yii 2.0 introduce el concepto de *campo* (field) para construir formularios utilizando [[yii\widgets\ActiveForm]]. Un campo
es un contenedor que consiste en una etiqueta, un input, un mensaje de error y/o texto de ayuda.
Un campo es representado como un objeto [[yii\widgets\ActiveField|ActiveField]].
Utilizando estos campos, puedes crear formularios más legibles que antes:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Por favor, consulta la sección [Creando Formularios](input-forms.md) para más detalles.


Constructor de Consultas
------------------------

En Yii 1.1, la generación de consultas a la base de datos estaba dividida en varias clases, incluyendo `CDbCommand`,
`CDbCriteria`, y `CDbCommandBuilder`. Yii 2.0 representa una consulta a la base de datos en términos de un objeto [[yii\db\Query|Query]]
que puede ser convertido en una declaración SQL con la ayuda de [[yii\db\QueryBuilder|QueryBuilder]] detrás de la escena.
Por ejemplo:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Lo mejor de todo, dichos métodos de generación de consultas pueden ser también utilizados mientras se trabaja con [Active Record](db-active-record.md).

Consulta la sección [Constructor de Consultas](db-query-builder.md) para más detalles.


Active Record
-------------

Yii 2.0 introduce muchísimos cambios con respecto a [Active Record](db-active-record.md). Los dos más obvios se relacionan a
la generación de consultas y al manejo de relaciones.

La clase de Yii 1.1 `CDbCriteria` es reemplazada por [[yii\db\ActiveQuery]] en Yii 2. Esta clase extiende de [[yii\db\Query]],
y por lo tanto hereda todos los métodos de generación de consultas.
Para comenzar a generar una consulta, llamas al método [[yii\db\ActiveRecord::find()]]:

```php
// Recibe todos los clientes *activos* y ordenados por su ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

Para declarar una relación, simplemente define un método getter que devuelva un objeto [[yii\db\ActiveQuery|ActiveQuery]].
El nombre de la propiedad definida en el getter representa el nombre de la relación. Por ejemplo, el siguiente código declara
una relación `orders` (en Yii 1.1, las relaciones se declaraban centralmente en el método `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Ahora puedes utilizar `$customer->orders` para acceder a las órdenes de la tabla relacionada. También puedes utilizar el siguiente
código para realizar una consulta relacional 'sobre la marcha' con una condición personalizada:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

Cuando se utiliza la carga temprana (eager loading) de la relación, Yii 2.0 lo hace diferente de 1.1. En particular, en 1.1 una declaración JOIN
sería creada para seleccionar tanto los registros de la tabla primaria como los relacionados. En Yii 2.0, dos declaraciones SQL son ejecutadas
sin utilizar un JOIN: la primera trae todos los modelos primarios, mientras que la segunda trae los registros relacionados
utilizando como condición la clave primaria de los primarios.

En vez de devolver objetos [[yii\db\ActiveRecord|ActiveRecord]], puedes conectar el método [[yii\db\ActiveQuery::asArray()|asArray()]]
mientras generas una consulta que devuelve un gran número de registros. Esto causará que el resultado de la consulta sea devuelto como
arrays, lo que puede reducir significativamente la necesidad de tiempo de CPU y memoria si el número de registros es grande.
Por ejemplo:

```php
$customers = Customer::find()->asArray()->all();
```

Otro cambio es que ya no puedes definir valores por defecto a los atributos a través de propiedades públicas.
Si lo necesitaras, debes definirlo en el método `init` de la clase del registro en cuestión.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

Anteriormente, solía haber algunos problemas al sobrescribir el constructor de una clase ActiveRecord en 1.1. Estos ya no están presentes en
Yii 2.0. Ten en cuenta que al agregar parámetros al constructor podrías llegar a tener que sobrescribir [[yii\db\ActiveRecord::instantiate()]].

Hay muchos otros cambios y mejoras con respecto a ActiveRecord. Por favor, consulta
la sección [Active Record](db-active-record.md) para más detalles.


Active Record Behaviors
-----------------------

En 2.0, hemos eliminado la clase del comportamiento base `CActiveRecordBehavior`. Si desea crear un comportamiento Active Record, usted tendrá que extender directamente de `yii\base\Behavior`. Si la clase de comportamiento debe responder a algunos eventos propios, usted tiene que sobrescribir los métodos `events()` como se muestra a continuación,

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


User e IdentityInterface
------------------------

La clase `CWebUser` de 1.1 es reemplazada por [[yii\web\User]], y la clase `CUserIdentity` ha dejado de existir.
En cambio, ahora debes implementar [[yii\web\IdentityInterface]] el cual es mucho más directo de usar.
El template de proyecto avanzado provee un ejemplo así.

Consulta las secciones [Autenticación](security-authentication.md), [Autorización](security-authorization.md), y [Template de Proyecto Avanzado](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide-es/README.md) para más detalles.



Manejo de URLs
--------------

El manejo de URLs en Yii 2 es similar al de 1.1. Una mejora mayor es que el manejador actual ahora soporta parámetros opcionales.
Por ejemplo, si tienes una regla declarada como a continuación, entonces coincidirá tanto con `post/popular` como con `post/1/popular`.
En 1.1, tendrías que haber creado dos reglas diferentes para obtener el mismo resultado

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Por favor, consulta la sección [Documentación del Manejo de URLs](runtime-routing.md) para más detalles.

Un cambio importante en la convención de nombres para rutas es que los nombres en CamelCase de controladores
y acciones ahora son convertidos a minúsculas y cada palabra separada por un guión, por ejemplo el id del controlador
`CamelCaseController` será `camel-case`.
Consulta la sección acerca de [IDs de controladores](structure-controllers.md#controller-ids) y [IDs de acciones](structure-controllers.md#action-ids) para más detalles.


Utilizar Yii 1.1 y 2.x juntos
-----------------------------

Si tienes código en Yii 1.1 que quisieras utilizar junto con Yii 2.0, por favor consulta
la sección [Utilizando Yii 1.1 y 2.0 juntos](tutorial-yii-integration.md).

