Validación de Entrada
=====================

Como regla básica, nunca debes confiar en los datos recibidos de un usuario final y deberías validarlo siempre
antes de ponerlo en uso.

Dado un [modelo](structure-models.md) poblado con entradas de usuarios, puedes validar esas entradas llamando al
método [[yii\base\Model::validate()]]. Dicho método devolverá un valor booleano indicando si la validación
tuvo éxito o no. En caso de que no, puedes obtener los mensajes de error de la propiedad [[yii\base\Model::errors]]. Por ejemplo,

```php
$model = new \app\models\ContactForm();

// poblar los atributos del modelo desde la entrada del usuario
$model->load(\Yii::$app->request->post());
// lo que es equivalente a:
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // toda la entrada es válida
} else {
    // la validación falló: $errors es un array que contienen los mensajes de error
    $errors = $model->errors;
}
```


## Declarar Reglas <span id="declaring-rules"></span>

Para hacer que `validate()` realmente funcione, debes declarar reglas de validación para los atributos que planeas validar.
Esto debería hacerse sobrescribiendo el método [[yii\base\Model::rules()]]. El siguiente ejemplo muestra cómo
son declaradas las reglas de validación para el modelo `ContactForm`:

```php
public function rules()
{
    return [
        // los atributos name, email, subject y body son obligatorios
        [['name', 'email', 'subject', 'body'], 'required'],

        // el atributo email debe ser una dirección de email válida
        ['email', 'email'],
    ];
}
```

El método [[yii\base\Model::rules()|rules()]] debe devolver un array de reglas, la cual cada una
tiene el siguiente formato:

```php
[
    // requerido, especifica qué atributos deben ser validados por esta regla.
    // Para un sólo atributo, puedes utilizar su nombre directamente
    // sin tenerlo dentro de un array
    ['attribute1', 'attribute2', ...],

    // requerido, especifica de qué tipo es la regla.
    // Puede ser un nombre de clase, un alias de validador, o el nombre de un método de validación
    'validator',

    // opcional, especifica en qué escenario/s esta regla debe aplicarse
    // si no se especifica, significa que la regla se aplica en todos los escenarios
    // Puedes también configurar la opción "except" en caso de que quieras aplicar la regla
    // en todos los escenarios salvo los listados
    'on' => ['scenario1', 'scenario2', ...],

    // opcional, especifica atributos adicionales para el objeto validador
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Por cada regla debes especificar al menos a cuáles atributos aplica la regla y cuál es el tipo de la regla.
Puedes especificar el tipo de regla de las siguientes maneras:

* el alias de un validador propio del framework, tal como `required`, `in`, `date`, etc. Por favor consulta
  [Validadores del núcleo](tutorial-core-validators.md) para la lista completa de todos los validadores incluidos.
* el nombre de un método de validación en la clase del modelo, o una función anónima. Consulta la
  subsección [Validadores en Línea](#inline-validators) para más detalles.
* el nombre completo de una clase de validador. Por favor consulta la subsección [Validadores Independientes](#standalone-validators)
  para más detalles.

Una regla puede ser utilizada para validar uno o varios atributos, y un atributo puede ser validado por una o varias reglas.
Una regla puede ser aplicada en ciertos [escenarios](structure-models.md#scenarios) con tan sólo especificando la opción `on`.
Si no especificas una opción `on`, significa que la regla se aplicará en todos los escenarios.

Cuando el método `validate()` es llamado, este sigue los siguientes pasos para realiza la validación:

1. Determina cuáles atributos deberían ser validados obteniendo la lista de atributos de [[yii\base\Model::scenarios()]]
   utilizando el [[yii\base\Model::scenario|scenario]] actual. Estos atributos son llamados *atributos activos*.
2. Determina cuáles reglas de validación deberían ser validados obteniendo la lista de reglas de [[yii\base\Model::rules()]]
   utilizando el [[yii\base\Model::scenario|scenario]] actual. Estas reglas son llamadas *reglas activas*.
3. Utiliza cada regla activa para validar cada atributo activo que esté asociado a la regla.
   Las reglas de validación son evaluadas en el orden en que están listadas.

De acuerdo a los pasos de validación mostrados arriba, un atributo será validado si y sólo si
es un atributo activo declarado en `scenarios()` y está asociado a una o varias reglas activas
declaradas en `rules()`.

> Note: Es práctico darle nombre a las reglas, por ej:
>
> ```php
> public function rules()
> {
>     return [
>         // ...
>         'password' => [['password'], 'string', 'max' => 60],
>     ];
> }
> ```
>
> Puedes utilizarlas en una subclase del modelo:
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }


### Personalizar Mensajes de Error <span id="customizing-error-messages"></span>

La mayoría de los validadores tienen mensajes de error por defecto que serán agregados al modelo siendo validado cuando sus atributos
fallan la validación. Por ejemplo, el validador [[yii\validators\RequiredValidator|required]] agregará
el mensaje "Username no puede estar vacío." a un modelo cuando falla la validación del atributo `username` al utilizar esta regla.

Puedes especificar el mensaje de error de una regla especificado la propiedad `message` al declarar la regla,
como a continuación,

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Por favor escoge un nombre de usuario.'],
    ];
}
```

Algunos validadores pueden soportar mensajes de error adicionales para describir más precisamente las causas
del fallo de validación. Por ejemplo, el validador [[yii\validators\NumberValidator|number]] soporta
[[yii\validators\NumberValidator::tooBig|tooBig]] y [[yii\validators\NumberValidator::tooSmall|tooSmall]]
para describir si el fallo de validación es porque el valor siendo validado es demasiado grande o demasiado pequeño, respectivamente.
Puedes configurar estos mensajes de error tal como cualquier otroa propiedad del validador en una regla de validación.


### Eventos de Validación <span id="validation-events"></span>

Cuando el método [[yii\base\Model::validate()]] es llamado, este llamará a dos métodos que puedes sobrescribir para personalizar
el proceso de validación:

* [[yii\base\Model::beforeValidate()]]: la implementación por defecto lanzará un evento [[yii\base\Model::EVENT_BEFORE_VALIDATE]].
  Puedes tanto sobrescribir este método o responder a este evento para realizar algún trabajo de pre procesamiento
  (por ej. normalizar datos de entrada) antes de que ocurra la validación en sí. El método debe devolver un booleano que indique
  si la validación debe continuar o no.
* [[yii\base\Model::afterValidate()]]: la implementación por defecto lanzará un evento [[yii\base\Model::EVENT_AFTER_VALIDATE]].
  uedes tanto sobrescribir este método o responder a este evento para realizar algún trabajo de post procesamiento después
  de completada la validación.


### Validación Condicional <span id="conditional-validation"></span>

Para validar atributos sólo en determinadas condiciones, por ej. la validación de un atributo depende
del valor de otro atributo puedes utilizar la propiedad [[yii\validators\Validator::when|when]]
para definir la condición. Por ejemplo,

```php
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }]
```

La propiedad [[yii\validators\Validator::when|when]] toma un método invocable PHP con la siguiente firma:

```php
/**
 * @param Model $model el modelo siendo validado
 * @param string $attribute al atributo siendo validado
 * @return bool si la regla debe ser aplicada o no
 */
function ($model, $attribute)
```

Si también necesitas soportar validación condicional del lado del cliente, debes configurar
la propiedad [[yii\validators\Validator::whenClient|whenClient]], que toma un string que representa una función JavaScript
cuyo valor de retorno determina si debe aplicarse la regla o no. Por ejemplo,

```php
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"]
```


### Filtro de Datos <span id="data-filtering"></span>

La entrada del usuario a menudo debe ser filtrada o pre procesada. Por ejemplo, podrías querer eliminar los espacions alrededor
de la entrada `username`. Puedes utilizar reglas de validación para lograrlo.

Los siguientes ejemplos muestran cómo eliminar esos espacios en la entrada y cómo transformar entradas vacías en `null` utilizando
los validadores del framework [trim](tutorial-core-validators.md#trim) y [default](tutorial-core-validators.md#default):

```php
return [
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
];
```

También puedes utilizar el validador más general [filter](tutorial-core-validators.md#filter) para realizar filtros
de datos más complejos.

Como puedes ver, estas reglas de validación no validan la entrada realmente. En cambio, procesan los valores
y los guardan en el atributo siendo validado.


### Manejando Entradas Vacías <span id="handling-empty-inputs"></span>

Cuando los datos de entrada son enviados desde formularios HTML, a menudo necesitas asignar algunos valores por defecto a las entradas
si estas están vacías. Puedes hacerlo utilizando el validador [default](tutorial-core-validators.md#default). Por ejemplo,

```php
return [
    // convierte "username" y "email" en `null` si estos están vacíos
    [['username', 'email'], 'default'],

    // convierte "level" a 1 si está vacío
    ['level', 'default', 'value' => 1],
];
```

Por defecto, una entrada se considera vacía si su valor es un string vacío, un array vacío o `null`.
Puedes personalizar la lógica de detección de valores vacíos configurando la propiedad [[yii\validators\Validator::isEmpty]]
con una función PHP invocable. Por ejemplo,

```php
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }]
```

> Note: La mayoría de los validadores no manejan entradas vacías si su propiedad [[yii\validators\Validator::skipOnEmpty]] toma
  el valor por defecto `true`. Estas serán simplemente salteadas durante la validación si sus atributos asociados reciben una entrada vacía.
  Entre los [validadores del framework](tutorial-core-validators.md), sólo `captcha`, `default`, `filter`,
  `required`, y `trim` manejarán entradas vacías.


## Validación Ad Hoc <span id="ad-hoc-validation"></span>

A veces necesitas realizar *validación ad hoc* para valores que no están ligados a ningún modelo.

Si sólo necesitas realizar un tipo de validación (por ej: validar direcciones de email), podrías llamar
al método [[yii\validators\Validator::validate()|validate()]] de los validadores deseados, como a continuación:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email válido.';
} else {
    echo $error;
}
```

> Note: No todos los validadores soportan este tipo de validación. Un ejemplo es el validador del framework [unique](tutorial-core-validators.md#unique),
  que está diseñado para trabajar sólo con un modelo.

Si necesitas realizar varias validaciones contro varios valores, puedes utilizar [[yii\base\DynamicModel]],
que soporta declarar tanto los atributos como las reglas sobre la marcha. Su uso es como a continuación:

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // validación fallida
    } else {
        // validación exitosa
    }
}
```

El método [[yii\base\DynamicModel::validateData()]] crea una instancia de `DynamicModel`, define los atributos
utilizando los datos provistos (`name` e `email` en este ejemplo), y entonces llama a [[yii\base\Model::validate()]]
con las reglas provistas.

Alternativamente, puedes utilizar la sintaxis más "clásica" para realizar la validación ad hoc:

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // validación fallida
    } else {
        // validación exitosa
    }
}
```

Después de la validación, puedes verificar si la validación tuvo éxito o no llamando al
método [[yii\base\DynamicModel::hasErrors()|hasErrors()]], obteniendo así los errores de validación de la
propiedad [[yii\base\DynamicModel::errors|errors]], como haces con un modelo normal.
Puedes también acceder a los atributos dinámicos definidos a través de la instancia del modelo, por ej.,
`$model->name` y `$model->email`.


## Crear Validadores <span id="creating-validators"></span>

Además de los [validadores del framework](tutorial-core-validators.md) incluidos en los lanzamientos de Yii, puedes también
crear tus propios validadores. Puedes crear validadores en línea o validadores independientes.


### Validadores en Línea <span id="inline-validators"></span>

Un validador en línea es uno definido en términos del método de un modelo o una función anónima. La firma
del método/función es:

```php
/**
 * @param string $attribute el atributo siendo validado actualmente
 * @param mixed $params el valor de los "parámetros" dados en la regla
 */
function ($attribute, $params)
```

Si falla la validación de un atributo, el método/función debería llamar a [[yii\base\Model::addError()]] para guardar
el mensaje de error en el modelo de manera que pueda ser recuperado más tarde y presentado a los usuarios finales.

Debajo hay algunos ejemplos:

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // un validador en línea definido como el método del modelo validateCountry()
            ['country', 'validateCountry'],

            // un validador en línea definido como una función anónima
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'El token debe contener letras y dígitos.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'El país debe ser "USA" o "Web".');
        }
    }
}
```

> Note: Por defecto, los validadores en línea no serán aplicados si sus atributos asociados reciben entradas vacías
  o si alguna de sus reglas de validación ya falló. Si quieres asegurarte de que una regla siempre sea aplicada,
  puedes configurar las reglas [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] y/o [[yii\validators\Validator::skipOnError|skipOnError]]
  como `false` en las declaraciones de las reglas. Por ejemplo:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Validadores Independientes <span id="standalone-validators"></span>

Un validador independiente es una clase que extiende de [[yii\validators\Validator]] o sus sub clases. Puedes implementar
su lógica de validación sobrescribiendo el método [[yii\validators\Validator::validateAttribute()]]. Si falla la validación
de un atributo, llama a [[yii\base\Model::addError()]] para guardar el mensaje de error en el modelo, tal como haces
con los [validadores en línea](#inline-validators).


Por ejemplo, el validador en línea de arriba podría ser movida a una nueva clase [[components/validators/CountryValidator]].

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($model, $attribute, 'El país debe ser "USA" o "Web".');
        }
    }
}
```

Si quieres que tu validador soporte la validación de un valor sin modelo, deberías también sobrescribir
el método[[yii\validators\Validator::validate()]]. Puedes también sobrescribir [[yii\validators\Validator::validateValue()]]
en vez de `validateAttribute()` y `validate()` porque por defecto los últimos dos métodos son implementados
llamando a `validateValue()`.

Debajo hay un ejemplo de cómo podrías utilizar la clase del validador de arriba dentro de tu modelo.

```php
namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\CountryValidator;

class EntryForm extends Model
{
    public $name;
    public $email;
    public $country;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['country', CountryValidator::class],
            ['email', 'email'],
        ];
    }
}
```


## Validación del Lado del Cliente <span id="client-side-validation"></span>

La validación del lado del cliente basada en JavaScript es deseable cuando la entrada del usuario proviene de formularios HTML, dado que
permite a los usuarios encontrar errores más rápido y por lo tanto provee una mejor experiencia. Puedes utilizar o implementar
un validador que soporte validación del lado del cliente *en adición a* validación del lado del servidor.

> Info: Si bien la validación del lado del cliente es deseable, no es una necesidad. Su principal propósito es proveer al usuario una mejor
  experiencia. Al igual que datos de entrada que vienen del los usuarios finales, nunca deberías confiar en la validación del lado del cliente. Por esta razón,
  deberías realizar siempre la validación del lado del servidor llamando a [[yii\base\Model::validate()]], como
  se describió en las subsecciones previas.


### Utilizar Validación del Lado del Cliente <span id="using-client-side-validation"></span>

Varios [validadores del framework](tutorial-core-validators.md) incluyen validación del lado del cliente. Todo lo que necesitas hacer
es solamente utilizar [[yii\widgets\ActiveForm]] para construir tus formularios HTML. Por ejemplo, `LoginForm` mostrado abajo declara dos
reglas: una utiliza el validador del framework [required](tutorial-core-validators.md#required), el cual es soportado tanto en
lado del cliente como del servidor; y el otro usa el validador en línea `validatePassword`, que es sólo soportado de lado
del servidor.

```php
namespace app\models;

use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // username y password son ambos requeridos
            [['username', 'password'], 'required'],

            // password es validado por validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Username o password incorrecto.');
        }
    }
}
```

El formulario HTML creado en el siguiente código contiene dos campos de entrada: `username` y `password`.
Si envias el formulario sin escribir nada, encontrarás que los mensajes de error requiriendo que
escribas algo aparecen sin que haya comunicación alguna con el servidor.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

Detrás de escena, [[yii\widgets\ActiveForm]] leerá las reglas de validación declaradas en el modelo
y generará el código JavaScript apropiado para los validadores que soportan validación del lado del cliente. Cuando un usuario
cambia el valor de un campo o envia el formulario, se lanzará la validación JavaScript del lado del cliente.

Si quieres deshabilitar la validación del lado del cliente completamente, puedes configurar
la propiedad [[yii\widgets\ActiveForm::enableClientValidation]] como `false`. También puedes deshabilitar la validación
del lado del cliente de campos individuales configurando su propiedad [[yii\widgets\ActiveField::enableClientValidation]]
como `false`. Cuando `enableClientValidation` es configurado tanto a nivel de campo como a nivel de formulario,
tendrá prioridad la primera.

### Implementar Validación del Lado del Cliente <span id="implementing-client-side-validation"></span>


Para crear validadores que soportan validación del lado del cliente, debes implementar
el método [[yii\validators\Validator::clientValidateAttribute()]], que devuelve una pieza de código JavaScript
que realiza dicha validación. Dentro del código JavaScript, puedes utilizar las siguientes
variables predefinidas:

- `attribute`: el nombre del atributo siendo validado.
- `value`: el valor siendo validado.
- `messages`: un array utilizado para contener los mensajes de error de validación para el atributo.
- `deferred`: un array con objetos diferidos puede ser insertado (explicado en la subsección siguiente).

En el siguiente ejemplo, creamos un `StatusValidator` que valida si la entrada es un status válido
contra datos de status existentes. El validador soporta tato tanto validación del lado del servidor como del lado del cliente.

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'Entrada de Status Inválida.';
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!Status::find()->where(['id' => $value])->exists()) {
            $model->addError($attribute, $this->message);
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $statuses = json_encode(Status::find()->select('id')->asArray()->column());
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if ($.inArray(value, $statuses) === -1) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: El código de arriba muestra principalmente cómo soportar validación del lado del cliente. En la práctica,
> puedes utilizar el validador del framework [in](tutorial-core-validators.md#in) para alcanzar el mismo objetivo. Puedes
> escribir la regla de validación como a como a continuación:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

> Tip: Si necesitas trabajar con validación del lado del cliente manualmente, por ejemplo, agregar campos dinámicamente o realizar alguna lógica de UI,
> consulta [Trabajar con ActiveForm vía JavaScript](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-activeform-js.md)
> en el Yii 2.0 Cookbook.

### Validación Diferida <span id="deferred-validation"></span>

Si necesitas realizar validación del lado del cliente asincrónica, puedes crear [Objetos Diferidos](https://api.jquery.com/category/deferred-object/).
Por ejemplo, para realizar validación AJAX personalizada, puedes utilizar el siguiente código:

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.push($.get("/check", {value: value}).done(function(data) {
            if ('' !== data) {
                messages.push(data);
            }
        }));
JS;
}
```

Arriba, la variable `deferred` es provista por Yii, y es un array de Objetos Diferidos. El método `$.get()`
de jQuery crea un Objeto Diferido, el cual es insertado en el array `deferred`.

Puedes también crear un Objeto Diferito explícitamente y llamar a su método `resolve()` cuando la llamada asincrónica
tiene lugar. El siguiente ejemplo muestra cómo validar las dimensiones de un archivo de imagen del lado del cliente.

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('Imagen demasiado ancha!!');
            }
            def.resolve();
        }
        var reader = new FileReader();
        reader.onloadend = function() {
            img.src = reader.result;
        }
        reader.readAsDataURL(file);

        deferred.push(def);
JS;
}
```

> Note: El método `resolve()` debe ser llamado después de que el atributo ha sido validado. De otra manera la validación
  principal del formulario no será completada.

Por simplicidad, el array `deferred` está equipado con un método de atajo, `add()`, que automáticamente crea un
Objeto Diferido y lo agrega al array `deferred`. Utilizando este método, puedes simplificar el ejemplo de arriba de esta manera,

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
                    messages.push('Imagen demasiado ancha!!');
                }
                def.resolve();
            }
            var reader = new FileReader();
            reader.onloadend = function() {
                img.src = reader.result;
            }
            reader.readAsDataURL(file);
        });
JS;
}
```


### Validación AJAX <span id="ajax-validation"></span>

Algunas validaciones sólo pueden realizarse del lado del servidor, debido a que sólo el servidor tiene la información necesaria.
Por ejemplo, para validar si un nombre de usuario es único o no, es necesario revisar la tabla de usuarios del lado del servidor.
Puedes utilizar validación basada en AJAX en este caso. Esta lanzará una petición AJAX de fondo para validar
la entrada mientras se mantiene la misma experiencia de usuario como en una validación del lado del cliente regular.

Para habilitar la validación AJAX individualmente un campo de entrada, configura la propiedad [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]]
de ese campo como `true` y especifica un único `id` de formulario:

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

Para habiliar la validación AJAX en el formulario entero, configura [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]]
como `true` a nivel del formulario:

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: Cuando la propiedad `enableAjaxValidation` es configurada tanto a nivel de campo como a nivel de formulario,
  la primera tendrá prioridad.

Necesitas también preparar el servidor para que pueda manejar las peticiones AJAX.
Esto puede alcanzarse con una porción de código como la siguiente en las acciones del controlador:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

El código de arriba chequeará si la petición actual es AJAX o no. Si lo es, responderá
esta petición ejecutando la validación y devolviendo los errores en formato JSON.

> Info: Puedes también utilizar [Validación Diferida](#deferred-validation) para realizar validación AJAX.
  De todos modos, la característica de validación AJAX descrita aquí es más sistemática y requiere menos esfuerzo de escritura de código.

Cuando tanto `enableClientValidation` como `enableAjaxValidation` son definidas como `true`, la petición de validación AJAX será lanzada
sólo después de una validación del lado del cliente exitosa.
