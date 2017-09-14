Validadores del framework
=========================

Yii provee en su núcleo un conjunto de validadores de uso común, que se pueden encontrar principalmente bajo el espacio de nombres (namespace) `yii\validators`.
En vez de utilizar interminables nombres de clases para los validadores, puedes usar *alias* para especificar el uso de esos validadores del núcleo. Por ejemplo, puedes usar el alias `required` para referirte a la clase [[yii\validators\RequiredValidator]] :

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

La propiedad [[yii\validators\Validator::builtInValidators]] declara todos los aliases de los validadores soportados.

A continuación, vamos a describir el uso principal y las propiedades de cada validador del núcleo.


## [[yii\validators\BooleanValidator|boolean]] <span id="boolean"></span>

```php
[
    // comprueba si "selected" es 0 o 1, sin mirar el tipo de dato
    ['selected', 'boolean'],

    // comprueba si "deleted" es del tipo booleano, alguno entre `true` o `false`
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

Este validador comprueba si el valor de la entrada (input) es booleano.

- `trueValue`: El valor representando `true`. Valor por defecto a `'1'`.
- `falseValue`: El valor representando `false`. Valor por defecto a `'0'`.
- `strict`: Si el tipo del valor de la entrada (input) debe corresponder con `trueValue` y `falseValue`. Valor por defecto a `false`.


> Note: Ya que los datos enviados con la entrada, vía formularios HTML,son todos cadenas (strings), usted debe normalmente dejar la propiedad  [[yii\validators\BooleanValidator::strict|strict]] a `false`.


## [[yii\captcha\CaptchaValidator|captcha]] <span id="captcha"></span>

```php
[
    ['verificationCode', 'captcha'],
]
```

Este validador es usualmente usado junto con [[yii\captcha\CaptchaAction]] y [[yii\captcha\Captcha]] para asegurarse que una entrada es la misma que lo es el código de verificación que enseña el widget [[yii\captcha\Captcha|CAPTCHA]].

- `caseSensitive`: cuando la comparación del código de verificación depende de que sean mayúsculas y minúsculas (case sensitive). Por defecto a `false`.
- `captchaAction`: la [ruta](structure-controllers.md#routes) correspondiente a
  [[yii\captcha\CaptchaAction|CAPTCHA action]] que representa (render) la imagen CAPTCHA. Por defecto`'site/captcha'`.
- `skipOnEmpty`: cuando la validación puede saltarse si la entrada está vacía. Por defecto a `false`, lo caul permite que la entrada sea necesaria (required).
  

## [[yii\validators\CompareValidator|compare]] <span id="compare"></span>

```php
[
    // valida si el valor del atributo "password" es igual al  "password_repeat"
    ['password', 'compare'],

    // valida si la edad es mayor que o igual que 30
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

Este validador compara el valor especificado por la entrada con otro valor y, se asegura si su relación es la especificada por la propiedad `operator`.

- `compareAttribute`: El nombre del valor del atributo con el cual debe compararse. Cuando el validador está siendo usado para validar un atributo, el valor por defecto de esta propiedad debe de ser el nombre de el atributo con el sufijo `_repeat`. Por  ejemplo, si el atributo a ser validado es `password`, entonces esta propiedad contiene por defecto `password_repeat`.
- `compareValue`: un valor constante con el que el valor de entrada debe ser comparado. Cuando ambos, esta propiedad y `compareAttribute` son especificados, esta preferencia tiene precedencia.
- `operator`: el operador de comparación. Por defecto vale `==`, permitiendo comprobar si el valor de entrada es igual al de `compareAttribute` o `compareValue`. Los siguientes operadores son soportados:
     * `==`: comprueba si dos valores son iguales. La comparación se realiza en modo no estricto.
     * `===`: comprueba si dos valores son iguales. La comparación se realiza en modo estricto.
     * `!=`: comprueba si dos valores NO son iguales. La comparación se realiza en modo no estricto.
     * `!==`: comprueba si dos valores NO son iguales. La comparación se realiza en modo estricto.
     * `>`: comprueba si el valor siendo validado es mayor que el valor con el que se compara.
     * `>=`: comprueba si el valor siendo validado es mayor o igual que el valor con el que se compara
     * `<`: comprueba si el valor siendo validado es menor que el valor con el que se compara
     * `<=`: comprueba si el valor siendo validado es menor o igual que el valor con el que se compara


## [[yii\validators\DateValidator|date]] <span id="date"></span>

```php
[
    [['from', 'to'], 'date'],
]
```

Este validador comprueba si el valor de entrada es una fecha, tiempo or fecha/tiempo y tiempo en el formato correcto.
Opcionalmente, puede convertir el valor de entrada en una fecha/tiempo UNIX y almacenarla en un atributo especificado vía [[yii\validators\DateValidator::timestampAttribute|timestampAttribute]].

- `format`: el formato fecha/tiempo en el que debe estar el valor a ser validado. 
   Esto tiene que ser un patrón fecha/tiempo descrito en [manual ICU](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
   Alternativamente tiene que ser una cadena con el prefijo `php:` representando un formato que ha de ser reconocido por la clase `Datetime` de PHP. Por favor, refiérase a <http://php.net/manual/en/datetime.createfromformat.php> sobre los formatos soportados.
   Si no tiene ningún valor, ha de coger el valor de `Yii::$app->formatter->dateFormat`.
- `timestampAttribute`: el nombre del atributo al cual este validador puede asignar el fecha/hora UNIX convertida desde la entrada fecha/hora.


## [[yii\validators\DefaultValueValidator|default]] <span id="default"></span>

```php
[
    // pone el valor de "age" a null si está vacío
    ['age', 'default', 'value' => null],

    // pone el valor de "country" a "USA" si está vacío
    ['country', 'default', 'value' => 'USA'],

    // asigna "from" y "to" con una fecha 3 días y 6 días a partir de hoy, si está vacía
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' : '+6 days'));
    }],
]
```

Este validador no valida datos. En cambio, asigna un valor por defecto a los atributos siendo validados, si los atributos están vacíos.

- `value`: el valor por defecto o un elemento llamable de PHP que devuelva el valor por defecto, el cual, va a ser asignado a los atributos siendo validados, si estos están vacíos. La signatura de la función PHP tiene que ser como sigue,

```php
function foo($model, $attribute) {
    // ... calcula $value ...
    return $value;
}
```

> Info: Cómo determinar si un valor está vacío o no, es un tópico separado cubierto en la sección [Valores Vacíos](input-validation.md#handling-empty-inputs) .


## [[yii\validators\NumberValidator|double]] <span id="double"></span>

```php
[
    // comprueba si  "salary" es un número de tipo doble
    ['salary', 'double'],
]
```

Esta validador comprueba si el valor de entrada es un número de tipo doble. Es equivalente a el validador [Número](#number) .

- `max`: el valor límite superior (incluido) de el valor. Si no tiene valor, significa que no se comprueba el valor superior.
- `min`: el valor límite inferior (incluido) de el valor. Si no tiene valor, significa que no se comprueba el valor inferior.


## [[yii\validators\EmailValidator|email]] <span id="email"></span>

```php
[
    // comprueba si "email" es una dirección válida de email
    ['email', 'email'],
]
```

Este validador comprueba si el valor de entrada es una dirección válida de email.

- `allowName`: indica cuando permitir el nombre en la dirección de email (p.e. `John Smith <john.smith@example.com>`). Por defecto a `false`.
- `checkDNS`, comprobar cuando el dominio del email existe y tiene cualquier registro  A o MX.
  Es necesario ser consciente que esta comprobación puede fallar debido a problemas temporales de  DNS, incluso si el la dirección es válida actualmente.
  Por defecto a `false`.
- `enableIDN`, indica cuando el proceso de validación debe tener en cuenta el informe de IDN (internationalized domain names).
  Por defecto a `false`. Dese cuenta que para poder usar la validación de IDN has de instalar y activar la extensión de PHP `intl`,  o será lanzada una excepción.


## [[yii\validators\ExistValidator|exist]] <span id="exist"></span>

```php
[
    // a1 necesita que exista una columna con el atributo "a1" 
    ['a1', 'exist'],

    // a1 necesita existir,pero su valor puede usar a2 para comprobar la existencia
    ['a1', 'exist', 'targetAttribute' => 'a2'],

    // a1 y a2 necesitan existir ambos, y ambos pueden recibir un mensaje de error
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 y a2 necesitan existir ambos, sólo a1 puede recibir el mensaje de error
    ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 necesita existir comprobando la existencia ambos a2 y a3 (usando el valor a1)
    ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],

    // a1 necesita existir. Si a1 es un array, cada elemento de él tiene que existir.
    ['a1', 'exist', 'allowArray' => true],
]
```

Este validador comprueba si el valor de entrada puede ser encontrado en una columna de una tabla. Sólo funciona con los atributos del modelo [Registro Activo (Active Record)](db-active-record.md). Soporta validación tanto con una simple columna o múltiples columnas.

- `targetClass`: el nombre de la clase [Registro Activo (Active Record)](db-active-record.md) debe de ser usada para mirar por el valor de entrada siendo validado. Si no tiene valor, la clase del modelo actualmente siendo validado puede ser usada.
- `targetAttribute`: el nombre del atributo en `targetClass` que debe de ser usado para validar la existencia del valor de entrada. Si no tiene valor, puede usar el nombra del atributoactualmente siendo validado.
  Puede usar una array para validar la existencia de múltiples columnas al mismo tiempo. El array de valores son los atributos que pueden ser usados para validar la existencia, mientras que las claves del array son los atributos a ser validados. Si la clave y el valor son los mismos, solo en ese momento puedes especificar el valor.
- `filter`: filtro adicional a aplicar a la consulta de la base de datos usado para comprobar la existencia de una valor de entrada.
  Esto puede ser una cadena o un array representando la condición de la consulta (referirse a [[yii\db\Query::where()]] sobre el formato de la condición de consulta), o una función anónima con la signatura `function ($query)`, donde `$query` es el objeto [[yii\db\Query|Query]] que puedes modificar en la función.
- `allowArray`: indica cuando permitir que el valor de entrada sea un array. Por defecto a `false`.Si la propiedad es `true` y la entrada es un array, cada elemento del array debe existir en la columna destino. Nota que esta propiedad no puede ser `true` si estás validando, por el contrario, múltiple columnas poniendo el valor del atributo `targetAttribute` como que es un array.


## [[yii\validators\FileValidator|file]] <span id="file"></span>

```php
[
    // comprueba si "primaryImage" es un fichero mde imagen en formato PNG, JPG o GIF.
    // el tamaño del fichero ha de ser menor de 1MB
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024*1024],
]
```

Este validador comprueba que el fichero subido es el adecuado.

- `extensions`: una lista de extensiones de ficheros que pueden ser subidos. Esto puede ser tanto un array o una cadena conteniendo nombres de extensiones de ficheros separados por un espacio o coma (p.e. "gif, jpg").
  Los nombres de las extensiones no diferencian mayúsculas de minúsculas (case-insensitive). Por defecto a `null`, permitiendo todas los nombres de extensiones de fichero.
- `mimeTypes`: una lista de tipos de ficheros MIME  que están permitidos subir. Esto puede ser tanto un array como una cadena conteniendo tipos de fichero MIME separados por un espacio o una coma (p.e. "image/jpeg, image/png").
  Los tipos Mime no diferencian mayúsculas de minúsculas (case-insensitive). Por defecto a `null`, permitiendo todos los tipos MIME.
- `minSize`: el número de bytes mínimo requerido para el fichero subido. El tamaño del fichero ha de ser superior a este valor. Por defecto a `null`, lo que significa sin límite inferior. 
- `maxSize`: El número máximo de bytes del fichero a subir. El tamaño del fichero ha de ser inferior a este valor. Por defecto a `null`, significando no tener límite superior.
- `maxFiles`: el máximo número de ficheros que determinado atributo puede manejar. Por defecto a 1, lo que significa que la entrada debe de ser sólo un fichero. Si es mayor que 1, entonces la entrada tiene que ser un array conteniendo como máximo el número `maxFiles` de elementos que representan los ficheros a subir.
- `checkExtensionByMimeType`: cuando comprobar la extensión del fichero por el tipo  MIME. Si la extensión producida por la comprobación del tipo MIME difiere la extensión del fichero subido, el fichero será considerado como no válido. Por defecto a `true`, significando que realiza este tipo de comprobación.

`FileValidator` es usado con [[yii\http\UploadedFile]]. Por favor, refiérase a la sección [Subida de ficheros](input-file-upload.md) para una completa cobertura sobre la subida de ficheros y llevar a cabo la validación de los ficheros subidos.


## [[yii\validators\FilterValidator|filter]] <span id="filter"></span>

```php
[
    // recorta (trim) las entradas "username" y "email"
    [['username', 'email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],

    // normaliza la entrada de  "phone"
    ['phone', 'filter', 'filter' => function ($value) {
        // normaliza la entrada del teléfono aquí
        return $value;
    }],
]
```

Este validador no valida datos. En su lugar, aplica un filtro sobre el valor de entrada y le asigna de nuevo el atributo siendo validado.

- `filter`: una retrollamada (callback) de PHP que define un filtro. Tiene que ser un nombre de función global, una función anónima, etc.
  La forma de la función ha de ser `function ($value) { return $newValue; }`. Tiene que contener un valor esta propiedad.
- `skipOnArray`: cuando evitar el filtro si el valor de la entrada es un array. Por defecto a `false`.
  A tener en cuenta que si el filtro no puede manejar una entrada de un array, debes poner esta propiedad a `true`. En otro caso algún error PHP puede ocurrir.

> Consejo (Tip): Si quieres recortar los valores de entrada, puedes usar directamente el validador [Recorte (trim)](#trim).


## [[yii\validators\ImageValidator|image]] <span id="image"></span>

```php
[
    // comprueba si "primaryImage"  es una imágen vaĺida con el tamaño adecuado
    ['primaryImage', 'image', 'extensions' => 'png, jpg',
        'minWidth' => 100, 'maxWidth' => 1000,
        'minHeight' => 100, 'maxHeight' => 1000,
    ],
]
```

Este validador comprueba si el valor de entrada representa un fichero de imagen válido. Extiende al validador [Fichero (file)](#file) y, por lo tanto, hereda todas sus propiedades. Además, soporta las siguientes propiedades adicionales específicas para la validación de imágenes:

- `minWidth`: el mínimo ancho de la imagen. Por defecto a `null`, indicando que no hay límite inferior.
- `maxWidth`: el máximo ancho de la imagen. Por defecto a `null`, indicando que no hay límite superior.
- `minHeight`: el mínimo alto de la imagen. Por defecto a `null`, indicando que no hay límite inferior.
- `maxHeight`: el máximo alto de la imagen. Por defecto a `null`, indicando que no hay límite superior.


## [[yii\validators\RangeValidator|in]] <span id="in"></span>

```php
[
    // comprueba si "level" es 1, 2 o 3
    ['level', 'in', 'range' => [1, 2, 3]],
]
```

Este validador comprueba si el valor de entrada puede encontrarse entre determinada lista de valores.

- `range`: una lista de determinados valores dentro de los cuales el valor de entrada debe de ser mirado.
- `strict`: cuando la comparación entre el valor de entrada y los valores determinados debe de ser estricta (ambos el tipo y el valor han de ser iguales). Por defecto a `false`.
- `not`: cuando el resultado de la validación debe de ser invertido. Por defecto a `false`. Cuando esta propiedad está a `true`, el validador comprueba que el valor de entrada NO ESTÁ en la determinada lista de valores.
- `allowArray`: si se permite que el valor de entrada sea un array. Cuando es `true` y el valor de entrada es un array, cada elemento en el array debe de ser encontrado en la lista de valores determinada,o la validación fallará.


## [[yii\validators\NumberValidator|integer]] <span id="integer"></span>

```php
[
    // comrpueba si "age" es un entero
    ['age', 'integer'],
]
```

Esta validador comprueba si el valor de entrada es un entero.

- `max`: el valor superior  (incluido) . Si no tiene valor, significa que el validador no comprueba el límite superior.
- `min`: el valor inferior (incluido). Si no tiene valor, significa que el validador no comprueba el límite inferior.


## [[yii\validators\RegularExpressionValidator|match]] <span id="match"></span>

```php
[
    // comprueba si "username" comienza con una letra y contiene solamente caracteres en sus palabras
    ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

Este validador comprueba si el valor de entrada coincide con la expresión regular especificada.

- `pattern`: la expresión regular conla que el valor de entrada debe coincidir. Esta propiedad no puede estar vacía, o se lanzará una excepción.
- `not`: indica cuando invertir el resultado de la validación. Por defecto a `false`, significando que la validación es exitosa solamente si el valor de entrada coincide con el patrón. Si esta propiedad está a `true`, la validación es exitosa solamente si el valor de entrada NO coincide con el patrón.


## [[yii\validators\NumberValidator|number]] <span id="number"></span>

```php
[
    // comprueba si "salary" es un número
    ['salary', 'number'],
]
```

Este validador comprueba si el valor de entrada es un número. Es equivalente al validador [Doble precisión (double)](#double).

- `max`: el valor superior límite (incluido) . Si no tiene valor, significa que el validador no comprueba el valor límite superior.
- `min`: el valor inferior límite (incluido) . Si no tiene valor, significa que el validador no comprueba el valor límite inferior.


## [[yii\validators\RequiredValidator|required]] <span id="required"></span>

```php
[
    // comprueba si ambos "username" y "password" no están vacíos
    [['username', 'password'], 'required'],
]
```

El validador comprueba si el valor de entrada es provisto y no está vacío.

- `requiredValue`: el valor deseado que la entrada debería tener. Si no tiene valor, significa que la entrada no puede estar vacía.
- `strict`: indica como comprobar los tipos de los datos al validar un valor. Por defecto a `false`.
  Cuando `requiredValue` no tiene valor, si esta propiedad es `true`, el validador comprueba si el valor de entrada no es estrictamente `null`; si la propiedad es `false`, el validador puede usar una regla suelta para determinar si el valor está vacío o no.
  Cuando `requiredValue` tiene valor, la comparación entre la entrada y  `requiredValue` comprobará tambien los tipos de los datos si esta propiedad es `true`.

> Info: Como determinar si un valor está vacío o no es un tópico separado cubierto en la sección [Valores vacíos](input-validation.md#handling-empty-inputs).


## [[yii\validators\SafeValidator|safe]] <span id="safe"></span>

```php
[
    // marca  "description" como un atributo seguro
    ['description', 'safe'],
]
```

Este validador no realiza validación de datos. En lugar de ello, es usado para marcar un atributo como seguro [atributos seguros](structure-models.md#safe-attributes).


## [[yii\validators\StringValidator|string]] <span id="string"></span>

```php
[
    // comprueba si "username" es una cadena cuya longitud está entre 4 Y 24
    ['username', 'string', 'length' => [4, 24]],
]
```

Este validador comprueba si el valor de entrada es una cadena válida con determinada longitud.

- `length`: especifica la longitud límite de la cadena de entrada a validar. Esto tiene que ser especificado del las siguientes formas:
     * un entero: la longitud exacta que la cadena debe de tener;
     * un array de un elemento: la longitud mínima de la cadena de entrada (p.e.`[8]`). Esto puede sobre escribir `min`.
     * un array de dos elementos: las longitudes mínima y mmáxima de la cadena de entrada (p.e. `[8, 128]`).
     Esto sobreescribe ambos valores de `min` y `max`.
- `min`: el mínimo valor de longitud de la cadena de entrada. Si no tiene valor, significa que no hay límite para longitud mínima.
- `max`: el máximo valor de longitud de la cadena de entrada. Si no tiene valor, significa que no hay límite para longitud máxima.
- `encoding`: la codificación de la cadena de entrada a ser validada. Si no tiene valor, usará el valor de la aplicación [[yii\base\Application::charset|charset]]  que por defecto es `UTF-8`.


## [[yii\validators\FilterValidator|trim]] <span id="trim"></span>

```php
[
    // recorta (trim) los espacios en blanco que rodean a "username" y "email"
    [['username', 'email'], 'trim'],
]
```

Este validador no realiza validación de datos. En cambio, recorta los espacios que rodean el valor de entrada. Nota que si el valor de entrada es un array, se ignorará este validador.


## [[yii\validators\UniqueValidator|unique]] <span id="unique"></span>

```php
[
    // a1 necesita ser único en la columna representada por el atributo "a1"
    ['a1', 'unique'],

    // a1 necesita ser único, pero la columna a2 puede ser usado para comprobar la unicidad del valor a1
    ['a1', 'unique', 'targetAttribute' => 'a2'],

    // a1 y a2 necesitan ambos ser únicos, y ambospueden recibir el mensaje de error
    [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a1 y a2 necesitan ser unicos ambos, solamente uno recibirá el mensaje de error
    ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a1 necesita ser único comprobando la unicidad de ambos a2 y a3 (usando el valor)
    ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']],
]
```

Este validador comprueba si el valor de entrada es único en una columna de una tabla. Solo funciona con los atributos del modelo [Registro Activo (Active Record)](db-active-record.md). Soporta validación contra cualquiera de los casos, una columna o múltiples columnas.

- `targetClass`: el nombre de la clase [Registro Activo (Active Record)](db-active-record.md) que debe de ser usada para mirar por el valor de entrada que está siendo validado. Si no tiene valor, la clase del modelo actualmente validado será usada.
- `targetAttribute`: el nombre de el atributo en `targetClass`que debe de ser usado para validar la unicidad de el valor de entrada. Si no tiene valor, puede usar el nombre del atributo actualmente siendo validado.
  Puedes usar un array para validar la unicidad de múltiples columnas al mismo tiempo. Los valores del array son atributos que pueden ser usados para validar la unicidad, mientras que las claves del array son los atributos que cuyos valores van a ser validados. Si la clave y el valor son el mismo, entonces puedes especificar el valor.
- `filter`: filtro adicional puede ser aplicado a la consulta de la base de datos usado para comprobar la unicidad del valor de entrada.
  Esto puede ser una cadena o un array representando la condición adicional a la consulta (Referirse a [[yii\db\Query::where()]] para el formato de la condición de la consulta), o una función anónima de la forma  `function ($query)`, donde `$query` es el objeto [[yii\db\Query|Query]] que puedes modificar en la función.


## [[yii\validators\UrlValidator|url]] <span id="url"></span>

```php
[
    // comprueba si "website" es una URL válida. Prefija con "http://" al atributo  "website"
    // si no tiene un esquema URI
    ['website', 'url', 'defaultScheme' => 'http'],
]
```

Este validador comprueba si el valor de entrada es una URL válida.

- `validSchemes`: un array especificando el esquema URI que debe ser considerado válido. Por defecto contiene `['http', 'https']`, significando que ambas URLS `http` y `https` son consideradas válidas.
- `defaultScheme`: el esquema de URI a poner como prefijo a la entrada si no tiene la parte del esquema.
  Por defecto a `null`, significando que no modifica el valor de entrada.
- `enableIDN`: Si el validador debe formar parte del registro IDN (internationalized domain names).
  Por defecto a `false`. Nota que para usar la validación IDN tienes que instalar y activar la extensión PHP `intl`, en otro caso una excepción será lanzada.

