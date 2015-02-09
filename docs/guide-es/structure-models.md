Modelos
=======

Los modelos forman parte de la arquitectura 
[MVC](http://es.wikipedia.org/wiki/Modelo%E2%80%93vista%E2%80%93controlador). Son objetos que representan datos de 
negocio, reglas y lógica.

Se pueden crear clases modelo extendiendo a [[yii\base\Model]] o a sus clases hijas. La clase base [[yii\base\Model]] 
soporta muchas características útiles:

* [Atributos](#attributes): representan los datos de negocio y se puede acceder a ellos como propiedades normales de 
  un objeto o como elementos de un array;
* [Etiquetas de atributo](#attribute-labels): especifica la etiqueta a mostrar para los atributos;
* [Asignación masiva](#massive-assignment): soporta la asignación múltiple de atributos en un único paso;
* [validación](#validation-rules): asegura la validez de los datos de entrada basándose en reglas declaradas;
* [Exportación de datos](#data-exporting): permite que los datos del modelo sean exportados en términos de arrays con 
  formatos personalizables.

La clase 'modelo' también es una base para modelos más avanzados, tales como [Active Records](db-active-record.md).

> Información: No es obligatorio basar las clases modelo en [[yii\base\Model]]. Sin embargo, debido a que hay muchos 
  componentes de Yii construidos para dar soporte a [[yii\base\Model]], por lo general, es la clase base preferible 
  para un modelo.

### Atributos <span id="attributes"></span>

Los modelos representan los datos de negocio en términos de *atributos*. Cada atributos es como una propiedad 
públicamente accesible de un modelo. El método [[yii\base\Model::attributes()]] especifica qué atributos tiene la 
clase modelo.

Se puede acceder a un atributo como se accede a una propiedad de un objeto normal.

```php
$model = new \app\models\ContactForm;

// "name" es un atributo de ContactForm
$model->name = 'example';
echo $model->name;
```

También se puede acceder a los atributos como se accede a los elementos de un array, gracias al soporte para 
[ArrayAccess](http://php.net/manual/es/class.arrayaccess.php) y 
[ArrayIterator](http://php.net/manual/es/class.arrayiterator.php) que brinda [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// acceder a atributos como elementos de array
$model['name'] = 'example';
echo $model['name'];

// iterar entre atributos
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```

### Definir Atributos <span id="defining-attributes"></span>

Por defecto, si un modelo extiende directamente a [[yii\base\Model]], todas sus variables miembro no estáticas son 
atributos. Por ejemplo, la siguiente clase modelo 'ContactForm' tiene cuatro atributos: 'name', 'email', 'subject', 
'body'. El modelo 'ContactForm' se usa para representar los datos de entrada recibidos desde un formulario HTML.

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
}
```

Se puede sobrescribir [[yii\base\Model::attributes()]] para definir los atributos de diferente manera. El método debe 
devolver los nombres de los atributos de un modelo. Por ejemplo [[yii\db\ActiveRecord]] lo hace devolviendo el nombre 
de las columnas de la tabla de la base de datos asociada como el nombre de sus atributos. Hay que tener en cuenta que 
también puede necesitar sobrescribir los métodos mágicos como `__get()`, `__set()` de modo que se puede acceder a los 
atributos como a propiedades de objetos normales.

### Etiquetas de atributo <span id="attribute-labels"></span>

Cuando se muestran valores o se obtienen entradas para atributos, normalmente se necesita mostrar etiquetas asociadas 
a los atributos. Por ejemplo, dado un atributo con nombre 'segundoApellido', es posible que se quiera mostrar la 
etiqueta 'Segundo Apellido' ya que es más fácil de interpretar por el usuario final en lugares como campos de 
formularios y en mensajes de error.

Se puede obtener la etiqueta de un atributo llamando a [[yii\base\Model::getAttributeLabel()]]. Por ejemplo:

```php
$model = new \app\models\ContactForm;

// muestra "Name"
echo $model->getAttributeLabel('name');
```

Por defecto, una etiqueta de atributo se genera automáticamente a partir del nombre de atributo. La generación se hace 
con el método [[yii\base\Model::generateAttributeLabel()]]. Este convertirá los nombres de variables de tipo 
camel-case en múltiples palabras con la primera letra de cada palabra en mayúsculas. Por ejemplo 'usuario' se 
convertirá en 'Nombre', y 'primerApellido' se convertirá en 'Primer Apellido'.
Si no se quieren usar las etiquetas generadas automáticamente, se puede sobrescribir 
[[yii\base\Model::attributeLabels()]] a una declaración de etiquetas de atributo especifica. Por ejemplo:

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    public function attributeLabels()
    {
        return [
            'name' => 'Your name',
            'email' => 'Your email address',
            'subject' => 'Subject',
            'body' => 'Content',
        ];
    }
}
```

Para aplicaciones con soporte para múltiples idiomas, se puede querer traducir las etiquetas de los atributos. Esto se 
puede hacer en el método [[yii\base\Model::attributeLabels()|attributeLabels()]], como en el siguiente ejemplo:

```php
public function attributeLabels()
{
    return [
        'name' => \Yii::t('app', 'Your name'),
        'email' => \Yii::t('app', 'Your email address'),
        'subject' => \Yii::t('app', 'Subject'),
        'body' => \Yii::t('app', 'Content'),
    ];
}
```

Incluso se puede definir etiquetas de atributo condicionales. Por ejemplo, basándose en el [escenario](#scenarios) en 
que se esta usando el modelo, se pueden devolver diferentes etiquetas para un mismo atributo.

> Información: Estrictamente hablando, los atributos son parte de las [vistas](structure-views.md). Pero declarar las 
  etiquetas en los modelos, a menudo, es muy conveniente y puede generar a un código muy limpio y reutilizable.

## Escenarios <span id="scenarios"></span>

Un modelo puede usarse en diferentes *escenarios*. Por ejemplo, un modelo 'Usuario', puede ser utilizado para recoger 
entradas de inicio de sesión de usuarios, pero también puede usarse para generar usuarios. En diferentes escenarios, 
un modelo puede usar diferentes reglas de negocio y lógica. Por ejemplo, un atributo 'email' puede ser requerido 
durante un registro de usuario, pero no ser necesario durante el inicio de sesión del mismo.

Un modelo utiliza la propiedad [[yii\base\Model::scenario]] para mantener saber en qué escenario se esta usando. Por 
defecto, un modelo soporta sólo un escenario llamado 'default'. El siguiente código muestra dos maneras de establecer 
el escenario en un modelo.

```php
// el escenario se establece como una propiedad
$model = new User;
$model->scenario = 'login';

// el escenario se establece mediante configuración
$model = new User(['scenario' => 'login']);
```

Por defecto, los escenarios soportados por un modelo se determinan por las [reglas de validación](#validation-rules) 
declaradas en el modelo. Sin embargo, se puede personalizar este comportamiento sobrescribiendo el método 
[[yii\base\Model::scenarios()]], como en el siguiente ejemplo:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        return [
            'login' => ['username', 'password'],
            'register' => ['username', 'email', 'password'],
        ];
    }
}
```

> Información: En el anterior y en los siguientes ejemplos, las clases modelo extienden a [[yii\db\ActiveRecord]] 
  porque el uso de múltiples escenarios normalmente sucede con clases de [Active Records](db-active-record.md).

El método 'scenarios()' devuelve un array cuyas claves son el nombre de escenario y los valores correspondientes a los 
*atributos activos*. Un atributo activo puede ser [asignado masivamente](#massive-assignment) y esta sujeto a 
[validación](#validation-rules). En el anterior ejemplo, los atributos 'username' y 'password' están activados en el 
escenario 'login'; mientras que en el escenario 'register', el atributo 'email' esta activado junto con 'username' y 
'password'.

La implementación por defecto de los 'scenarios()' devolverá todos los escenarios encontrados en el método de 
declaración de las reglas de validación [[yii\base\Model::rules()]]. Cuando se sobrescribe 'scenarios()', si se quiere 
introducir nuevos escenarios además de los predeterminados, se puede hacer como en el siguiente ejemplo:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username', 'password'];
        $scenarios['register'] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

La característica escenario se usa principalmente en las [validaciones](#validation-rules) y por la 
[asignación masiva de atributos](#massive-assignment). Aunque también se puede usar para otros propósitos. Por 
ejemplo, se pueden declarar [etiquetas de atributo](#attribute-labels) diferentes basándose en el escenario actual.

## Reglas de Validación <span id="validation-rules"></span>

Cuando un modelo recibe datos del usuario final, estos deben ser validados para asegurar que cumplan ciertas reglas 
(llamadas *reglas de validación*, también conocidas como *reglas de negocio*). Por ejemplo, dado un modelo 
'ContactForm', se puede querer asegurar que ningún atributo este vacío y que el atributo 'email' contenga una 
dirección de correo válida. Si algún valor no cumple con las reglas, se debe mostrar el mensaje de error apropiado 
para ayudar al usuario a corregir estos errores.

Se puede llamar a [[yii\base\Model::validate()]] para validar los datos recibidos. El método se usará para validar las 
reglas declaradas en [[yii\base\Model::rules()]] para validar cada atributo relevante. Si no se encuentran errores, se 
devolverá true. De otro modo, este almacenará los errores en la propiedad [[yii\base\Model::errors]] y devolverá falso.
 Por ejemplo:

```php
$model = new \app\models\ContactForm;

// establece los atributos del modelo con la entrada de usuario
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // todas las entradas validadas
} else {
    // validación fallida: $errors es un array que contiene los mensajes de error
    $errors = $model->errors;
}
```

Para declarar reglas de validación asociadas a un modelo, se tiene que sobrescribir el método 
[[yii\base\Model::rules()]] para que devuelva las reglas que los atributos del modelo deben satisfacer. El siguiente 
ejemplo muestra las reglas de validación declaradas para el modelo 'ContactForm'.

```php
public function rules()
{
    return [
        // name, email, subject y body son atributos requeridos
        [['name', 'email', 'subject', 'body'], 'required'],

        // el atribuido email debe ser una dirección de correo electrónico válida
        ['email', 'email'],
    ];
}
```

Una regla puede usarse para validar uno o más atributos, y un atributo puede validarse por una o múltiples reglas. Por 
favor refiérase a la sección [Validación de entrada](input-validation.md) para obtener más detalles sobre cómo 
declarar reglas de validación.

A veces, solamente se quiere aplicar una regla en ciertos [escenarios](#scenarios). Para hacerlo, se puede especificar 
la propiedad 'on' de una regla, como en el siguiente ejemplo:

```php
public function rules()
{
    return [
        // username, email y password son obligatorios en el escenario “register”
        [['username', 'email', 'password'], 'required', 'on' => 'register'],

        // username y password son obligatorios en el escenario “login”
        [['username', 'password'], 'required', 'on' => 'login'],
    ];
}
```

Si no se especifica la propiedad 'on', la regla se aplicará en todos los escenarios. Se llama a una regla 
*regla activa* si esta puede aplicarse en el [[yii\base\Model::scenario|scenario]] actual.

Un atributo será validado si y sólo si es un atributo activo declarado en 'scenarios()' y esta asociado con una o más 
reglas activas declaradas en 'rules()'.

## Asignación Masiva <span id="massive-assignment"></span>

La asignación masiva es una buena forma de rellenar los atributos de un modelo con las entradas de usuario en una 
única línea de código. Rellena los atributos de un modelo asignando los datos de entrada directamente a las 
propiedades de [[yii\base\Model::$attributes]]. Los siguientes dos ejemplos son equivalentes, ambos intentan asignar 
los datos enviados por el usuario final a través de un formulario a los atributos del modelo 'ContactForm'. 
Claramente, el primero, que usa la asignación masiva, es más claro y menos propenso a errores que el segundo:

```php
$model = new \app\models\ContactForm;
$model->attributes = \Yii::$app->request->post('ContactForm');
```

```php
$model = new \app\models\ContactForm;
$data = \Yii::$app->request->post('ContactForm', []);
$model->name = isset($data['name']) ? $data['name'] : null;
$model->email = isset($data['email']) ? $data['email'] : null;
$model->subject = isset($data['subject']) ? $data['subject'] : null;
$model->body = isset($data['body']) ? $data['body'] : null;
```

### Atributos Seguros <span id="safe-attributes"></span>

La asignación masiva sólo se aplica a los llamados *atributos seguros* qué son los atributos listados en 
[[yii\base\Model::scenarios()]] para el actual [[yii\base\Model::scenario|scenario]] del modelo. Por ejemplo, si en el 
modelo 'User' tenemos la siguiente declaración de escenario, entonces cuando el escenario actual sea 'login', sólo los 
atributos 'username' y 'password' podrán ser asignados masivamente. Cualquier otro atributo permanecerá intacto 

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password'],
        'register' => ['username', 'email', 'password'],
    ];
}
```

> Información: La razón de que la asignación masiva sólo se aplique a los atributos seguros es debida a que se quiere 
controlar qué atributos pueden ser modificados por los datos del usuario final. Por ejemplo, si el modelo 'User' tiene 
un atributo 'permission' que determina los permisos asignados al usuario, se quiere que estos atributos sólo sean 
modificados por administradores desde la interfaz backend.

Debido a que la implementación predeterminada de [[yii\base\Model::scenarios()]] devolverá todos los escenarios y 
atributos encontrados en [[yii\base\Model::rules()]], si no se sobrescribe este método, significa que un atributo es 
seguro mientras aparezca en una de las reglas de validación activas.

Por esta razón, se proporciona un validador especial con alias 'safe' con el que se puede declarar un atributo como 
seguro sin llegar a validarlo. Por ejemplo, las siguientes reglas declaran que los atributos 'title' y 'description' 
son atributos seguros.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```

### Atributos Inseguros <span id="unsafe-attributes"></span>

Como se ha descrito anteriormente, el método [[yii\base\Model::scenarios()]] sirve para dos propósitos: determinar qué 
atributos deben ser validados y determinar qué atributos son seguros. En situaciones poco comunes, se puede querer 
validar un atributo pero sin marcarlo como seguro. Se puede hacer prefijando el signo de exclamación '!' delante del 
nombre del atributo cuando se declaran en 'scenarios()', como el atributo 'secret' del siguiente ejemplo:

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password', '!secret'],
    ];
}
```

Cuando el modelo esté en el escenario 'login', los tres atributos serán validados. Sin embargo, sólo los atributos 
'username' y 'password' se asignarán masivamente. Para asignar un valor de entrada al atribuido 'secret', se tendrá 
que hacer explícitamente como en el ejemplo:

```php
$model->secret = $secret;
```

## Exportación de Datos <span id="data-exporting"></span>

A menudo necesitamos exportar modelos a diferentes formatos. Por ejemplo, se puede querer convertir un conjunto de 
modelos a formato JSON o Excel. El proceso de exportación se puede dividir en dos pasos independientes. En el primer 
paso, se convierten los modelos en arrays; en el segundo paso, los arrays se convierten a los formatos deseados. Nos 
puede interesar fijarnos en el primer paso, ya que el segundo paso se puede lograr mediante un formateador de datos 
genérico, tal como [[yii\web\JsonResponseFormatter]].
La manera más simple de convertir un modelo en un array es usar la propiedad [[yii\base\Model::$attributes]]. Por 
ejemplo:

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

Por defecto, la propiedad [[yii\base\Model::$attributes]] devolverá los valores de *todos* los atributos declarados en 
[[yii\base\Model::attributes()]].

Una manera más flexible y potente de convertir un modelo en un array es usar el método [[yii\base\Model::toArray()]]. 
Su funcionamiento general es el mismo que el de [[yii\base\Model::$attributes]]. Sin embargo, este permite elegir que 
elementos de datos, llamados *campos*, queremos poner en el array resultante y elegir como debe ser formateado. De 
hecho, es la manera por defecto de exportar modelos en desarrollo de servicios Web RESTful, tal y como se describe en 
[Formatos de Respuesta](rest-response-formatting.md).

### Campos <span id="fields"></span>

Un campo es simplemente un elemento nombrado en el array resultante de ejecutar el método [[yii\base\Model::toArray()]]
 de un modelo.
Por defecto, los nombres de los campos son equivalentes a los nombres de los atributos. Sin embargo, se puede 
modificar este comportamiento sobrescribiendo el método [[yii\base\Model::fields()|fields()]] y/o el método 
[[yii\base\Model::extraFields()|extraFields()]]. Ambos métodos deben devolver una lista de las definiciones de los 
campos. Los campos definidos mediante 'fields()' son los campos por defecto, esto significa que 'toArray()' devolverá 
estos campos por defecto. El método 'extraFields()' define campos adicionalmente disponibles que también pueden 
devolverse mediante 'toArray()' siempre y cuando se especifiquen a través del parámetro '$expand'. Por ejemplo, el 
siguiente código devolverá todos los campos definidos en 'fields()' y los campos 'prettyName' y 'fullAdress' si estos 
están definidos en 'extraFields()'.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

Se puede sobrescribir 'fields()' para añadir, eliminar, renombrar o redefinir campos. El valor devuelto por 'fields()' 
debe se un array. Las claves del array son los nombres de los campos, y los valores son las correspondientes 
definiciones de los campos que pueden ser nombres de propiedades/atributos o funciones anónimas que devuelvan los 
correspondientes valores de campo. En el caso especial en que un nombre de un campo es el mismo a su definición de 
nombre de atributo, se puede omitir la clave del array. Por ejemplo:

```php
// lista explícitamente cada campo, es mejor usarlo cuando nos queremos asegurar 
// de que los cambios en la tabla de la base de datos o los atributos del modelo 
// no modifiquen los campos(para asegurar compatibilidades para versiones anteriores de API)
public function fields()
{
    return [
        // el nombre del campo es el mismo que el nombre de atributo
        'id',

        // el nombre del campo es “email”, el nombre de atributo correspondiente es “email_address”
        'email' => 'email_address',

        // El nombre del campo es “name”, su valor esta definido por una llamada de retorno PHP
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filtrar algunos campos, es mejor usarlo cuando se quiere heredar la implementación del padre
// y discriminar algunos campos sensibles.
public function fields()
{
    $fields = parent::fields();

    // elimina campos que contengan información sensible.
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Atención: debido a que por defecto todos los atributos de un modelo serán incluidos en el array exportado, se debe 
examinar los datos para asegurar que no contienen información sensible. Si existe dicha información, se debe 
sobrescribir 'fields()' para filtrarla. En el anterior ejemplo, se filtra 'aut_key', 'password_hash' y 
'password_reset_token'.

## Mejores Prácticas <span id="best-practices"></span>

Los modelos son los lugares centrales para representar datos de negocio, reglas y lógica. Estos a menudo necesitan ser 
reutilizados en diferentes lugares. En una aplicación bien diseñada, los modelos normalmente son más grandes que los 
[controladores](structure-controllers.md).

En resumen, los modelos:
* pueden contener atributos para representar los datos de negocio;
* pueden contener reglas de validación para asegurar la validez e integridad de los datos;
* pueden contener métodos que para implementar la lógica de negocio;
* NO deben acceder directamente a peticiones, sesiones, u otro tipo de datos de entorno. Estos datos deben ser 
  inyectados por los [controladores](structure-controllers.md) en los modelos.
* deben evitar embeber HTML u otro código de presentación – esto es mejor hacerlo en las [vistas](structure-views.md);
* evitar tener demasiados [escenarios](#scenarios) en un mismo modelo.

Generalmente se puede considerar la última recomendación cuando se estén desarrollando grandes sistemas complejos. En 
estos sistemas, los modelos podrían ser muy grandes debido a que podrían ser usados en muchos lugares y por tanto 
contener muchos conjuntos de reglas y lógicas de negocio. A menudo esto desemboca en un código muy difícil de mantener 
ya que una simple modificación en el código puede afectar a muchos sitios diferentes. Para mantener el código más 
fácil de mantener, se puede seguir la siguiente estrategia:

* Definir un conjunto de clases modelo base que sean compartidas por diferentes 
  [aplicaciones](structure-applications.md) o [módulos](structure-modules.md). Estas clases modelo deben contener el 
  conjunto mínimo de reglas y lógica que sean comunes para todos sus usos.
* En cada [aplicación](structure-applications.md) o [módulo](structure-modules.md) que use un modelo, definir una 
  clase modelo concreta que extienda a la correspondiente clase modelo base. La clase modelo concreta debe contener 
  reglas y lógica que sean específicas para esa aplicación o módulo.

Por ejemplo, en la [Plantilla de Aplicación Avanzada](tutorial-advanced-app.md), definiendo una clase modelo base 
'common\models\Post'. Después en la aplicación front end, definiendo y usando una clase modelo concreta 
'frontend\models\Post' que extienda a 'common\models\Post'. Y de forma similar en la aplicación back end, definiendo 
'backend\models\Post'. Con esta estrategia, nos aseguramos que el código de 'frontend\models\Post' es específico para 
la aplicación front end, y si se efectúa algún cambio en el, no nos tenemos que preocupar de si el cambio afectará a 
la aplicación back end.