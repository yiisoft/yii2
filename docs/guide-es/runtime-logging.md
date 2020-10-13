Registro de anotaciones
=======================

Yii proporciona un poderoso framework dedicado al registro de anotaciones (logging) que es altamente personalizable y
extensible. Usando este framework se pueden guardar fácilmente anotaciones (logs) de varios tipos de mensajes,
filtrarlos, y unificarlos en diferentes destinos que pueden ser archivos, bases de datos o emails.


Usar el framework de registro de anotaciones de Yii involucra los siguientes pasos:

* Registrar [mensajes de las anotaciones](#log-messages) en distintos lugares del código;
* Configurar los [destinos de las anotaciones](#log-targets) en la configuración de la aplicación para filtrar y
  exportar los mensajes de las anotaciones;
* Examinar los mensajes filtrados de los las anotaciones exportadas para diferentes destinos
  (ej. [Yii debugger](tool-debugger.md)).

En esta sección, se describirán principalmente los dos primeros pasos.

## Anotación de Messages <span id="log-messages"></span>

Registrar mensajes de anotación es tan simple como llamar a uno de los siguientes métodos de registro de anotaciones.

* [[Yii::debug()]]: registra un mensaje para trazar el funcionamiento de una sección de código. Se usa principalmente
  para tareas de desarrollo.
* [[Yii::info()]]: registra un mensaje que transmite información útil.
* [[Yii::warning()]]: registra un mensaje de advertencia que indica que ha sucedido algo inesperado.
* [[Yii::error()]]: registra un error fatal que debe ser investigado tan pronto como sea posible.

Estos métodos registran mensajes de varios *niveles de severidad* y *categorías*. Comparten el mismo registro de
función `function ($message, $category = 'application')`, donde `$message` representa el mensaje del registro que
tiene que ser registrado, mientras que `$category` es la categoría del registro de mensaje. El código del siguiente
ejemplo registra la huella del mensaje para la categoría `application`:

```php
Yii::debug('start calculating average revenue');
```

> Info: Los mensajes de registro pueden ser tanto cadenas de texto como datos complejos, como arrays u objetos.
  Es responsabilidad de los [destinos de registros](#log-targets) tratar los mensajes de registro de manera apropiada.
  De forma predeterminada, si un mensaje de registro no es una cadena de texto, se exporta como si fuera un string
  llamando a [[yii\helpers\VarDumper::export()]].

Para organizar mejor y filtrar los mensajes de registro, se recomienda especificar una categoría apropiada para cada
mensaje de registro. Se puede elegir un sistema de nombres jerárquicos por categorías que facilite a los
[destino de registros](#log-targets) el filtrado de mensajes basándose en categorías. Una manera simple pero
efectiva de organizarlos es usar la constante predefinida (magic constant) de PHP `__METHOD__` como nombre de
categoría. Además este es el enfoque que se usa en el código del núcleo (core) del framework Yii. Por ejemplo,

```php
Yii::debug('start calculating average revenue', __METHOD__);
```

La constante `__METHOD__` equivale al nombre del método (con el prefijo del nombre completo del nombre de clase) donde
se encuentra la constante. Por ejemplo, es igual a la cadena `'app\controllers\RevenueController::calculate'` si la
linea anterior de código se llamara dentro de este método.

> Info: Los métodos de registro de anotaciones descritos anteriormente en realidad son accesos directos al
  método [[yii\log\Logger::log()|log()]] del [[yii\log\Logger|logger object]] que es un singleton accesible a través
  de la expresión `Yii::getLogger()`. Cuando se hayan registrado suficientes mensajes o cuando la aplicación haya
  finalizado, el objeto de registro llamará [[yii\log\Dispatcher|message dispatcher]] para enviar los mensajes de
  registro registrados a los [destiinos de registros](#log-targets).

## Destino de Registros <span id="log-targets"></span>

Un destino de registro es una instancia de la clase [[yii\log\Target]] o de una clase hija. Este filtra los
mensajes de registro por sus niveles de severidad y sus categorías y después los exporta a algún medio. Por ejemplo,
un [[yii\log\DbTarget|database target]] exporta los mensajes de registro filtrados a una tabla de base de datos,
mientras que un [[yii\log\EmailTarget|email target]] exporta los mensajes de registro a una dirección de correo
electrónico específica.

Se pueden registrar múltiples destinos de registros en una aplicación configurándolos en la
[aplicación de componente](structure-application-components.md) `log` dentro de la configuración de aplicación, como
en el siguiente ejemplo:

```php
return [
    // el componente log tiene que cargarse durante el proceso de bootstrapping
    'bootstrap' => ['log'],

    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['log@example.com'],
                       'to' => ['admin@example.com', 'developer@example.com'],
                       'subject' => 'Database errors at example.com',
                    ],
                ],
            ],
        ],
    ],
];
```

> Note: El componente `log` debe cargarse durante el proceso de [bootstrapping](runtime-bootstrapping.md) para que
pueda enviar los mensajes de registro a los destinos inmediatamente. Este es el motivo por el que se lista en el
array `bootstrap` como se muestra más arriba.

En el anterior código, se registran dos destinos de registros en la propiedad [[yii\log\Dispatcher::targets]]

* el primer destino gestiona los errores y las advertencias y las guarda en una tabla de la base de datos;
* el segundo destino gestiona mensajes los mensajes de error de las categorías cuyos nombres empiecen por
  `yii\db\` y los envía por email a las direcciones `admin@example.com` y `developer@example.com`.

Yii incluye los siguientes destinos. En la API de documentación se pueden referencias a estas clases e
información de configuración y uso.

* [[yii\log\DbTarget]]: almacena los mensajes de registro en una tabla de la base de datos.
* [[yii\log\EmailTarget]]: envía los mensajes de registro a direcciones de correo preestablecidas.
* [[yii\log\FileTarget]]: guarda los menajes de registro en archivos.
* [[yii\log\SyslogTarget]]: guarda los mensajes de registro en el syslog llamando a la función PHP `syslog()`.

A continuación, se describirá las características más comunes de todos los destinos de registros.

### Filtrado de Mensajes <span id="message-filtering"></span>

Se pueden configurar las propiedades [[yii\log\Target::levels|levels]] y [[yii\log\Target::categories|categories]]
para cada destino de registros, con estas se especifican los niveles de severidad y las categorías de mensajes que
deberán procesar sus destinos.

La propiedad [[yii\log\Target::levels|levels]] es un array que consta de uno o varios de los siguientes valores:

* `error`: correspondiente a los mensajes registrados por [[Yii::error()]].
* `warning`: correspondiente a los mensajes registrados por [[Yii::warning()]].
* `info`: correspondiente a los mensajes registrados por [[Yii::info()]].
* `trace`: correspondiente a los mensajes registrados por [[Yii::debug()]].
* `profile`: correspondiente a los mensajes registrados por [[Yii::beginProfile()]] y [[Yii::endProfile()]], que se
  explicará más detalladamente en la subsección [Perfiles](#performance-profiling).

Si no se especifica la propiedad [[yii\log\Target::levels|levels]], significa que el destino procesará los
mensajes de *cualquier* nivel de severidad.

La propiedad [[yii\log\Target::categories|categories]] es un array que consta de categorías de mensaje o patrones. El
destino sólo procesará mensajes de las categorías que se puedan encontrar o si coinciden con algún patrón listado
en el array. Un patrón de categoría es un nombre de categoría al que se le añade un asterisco `*` al final. Un nombre
de categoría coincide con un patrón si empieza por el mismo prefijo que el patrón. Por ejemplo,
`yii\db\Command::execute` y `yii\db\Command::query` que se usan como nombres de categoría para los mensajes
registrados en la clase [[yii\db\Command]], coinciden con el patrón `yii\db\*`.

Si no se especifica la propiedad [[yii\log\Target::categories|categories]], significa que el destino procesará
los mensajes de *todas* las categorías.

Además añadiendo las categorías en listas blancas (whitelisting) mediante la propiedad
[[yii\log\Target::categories|categories]], también se pueden añadir ciertas categorías en listas negras (blacklist)
configurando la propiedad [[yii\log\Target::except|except]]. Si se encuentra la categoría de un mensaje o coincide
algún patrón con esta propiedad, NO será procesada por el destino.

La siguiente configuración de destinos especifica que el destino solo debe procesar los mensajes de error y
de advertencia de las categorías que coincidan con alguno de los siguientes patrones `yii\db\*` o
`yii\web\HttpException:*`, pero no con `yii\web\HttpException:404`.

```php
[
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning'],
    'categories' => [
        'yii\db\*',
        'yii\web\HttpException:*',
    ],
    'except' => [
        'yii\web\HttpException:404',
    ],
]
```

> Info: Cuando se captura una excepción de tipo HTTP por el [gestor de errores](runtime-handling-errors.md), se
  registrará un mensaje de error con el nombre de categoría con formato `yii\web\HttpException:ErrorCode`. Por
  ejemplo, la excepción [[yii\web\NotFoundHttpException]] causará un mensaje de error del tipo
  `yii\web\HttpException:404`.

### Formato de los Mensajes <span id="message-formatting"></span>

Los destinos exportan los mensajes de registro filtrados en cierto formato. Por ejemplo, is se instala un
destino de registros de la calse [[yii\log\FileTarget]], encontraremos un registro similar en el archivo de
registro `runtime/log/app.log`:

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

De forma predeterminada los mensajes de registro se formatearan por [[yii\log\Target::formatMessage()]] como en el
siguiente ejemplo:

```
Timestamp [IP address][User ID][Session ID][Severity Level][Category] Message Text
```

Se puede personalizar el formato configurando la propiedad [[yii\log\Target::prefix]] que es un PHP ejecutable y
devuelve un prefijo de mensaje personalizado. Por ejemplo, el siguiente código configura un destino de registro
anteponiendo a cada mensaje de registro el ID de usuario (se eliminan la dirección IP y el ID por razones de
privacidad).

```php
[
    'class' => 'yii\log\FileTarget',
    'prefix' => function ($message) {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        return "[$userID]";
    }
]
```

Además de prefijos de mensaje, destinos de registros también añaden alguna información de contexto en cada lote
de mensajes de registro. De forma predeterminada, se incluyen los valores de las siguientes variables globales de
PHP: `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, `$_SESSION` y `$_SERVER`. Se puede ajustar el comportamiento
configurando la propiedad [[yii\log\Target::logVars]] con los nombres de las variables globales que se quieran incluir
con el destino del registro. Por ejemplo, la siguiente configuración de destino de registros especifica que
sólo se añadirá al mensaje de registro el valor de la variable `$_SERVER`.

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
]
```

Se puede configurar `logVars` para que sea un array vacío para deshabilitar totalmente la inclusión de información de
contexto. O si se desea implementar un método propio de proporcionar información de contexto se puede sobrescribir el
método [[yii\log\Target::getContextMessage()]].

### Nivel de Seguimiento de Mensajes <span id="trace-level"></span>

Durante el desarrollo, a veces se quiere visualizar de donde proviene cada mensaje de registro. Se puede lograr
configurando la propiedad [[yii\log\Dispatcher::traceLevel|traceLevel]] del componente `log` como en el siguiente
ejemplo:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [...],
        ],
    ],
];
```

La configuración de aplicación anterior establece el [[yii\log\Dispatcher::traceLevel|traceLevel]] para que sea 3 si
`YII_DEBUG` esta habilitado y 0 si esta deshabilitado. Esto significa que si `YII_DEBUG` esta habilitado, a cada
mensaje de registro se le añadirán como mucho 3 niveles de la pila de llamadas del mensaje que se este registrando; y
si `YII_DEBUG` está deshabilitado, no se incluirá información de la pila de llamadas.

> Info: Obtener información de la pila de llamadas no es trivial. Por lo tanto, sólo se debe usar esta
  característica durante el desarrollo o cuando se depura la aplicación.

### Liberación (Flushing) y Exportación de Mensajes <span id="flushing-exporting"></span>

Como se ha comentado anteriormente, los mensajes de registro se mantienen en un array por el
[[yii\log\Logger|logger object]]. Para limitar el consumo de memoria de este array, el componente encargado del
registro de mensajes enviará los mensajes registrados a los [destinos de registros](#log-targets) cada vez que el
array acumule un cierto número de mensajes de registro. Se puede personalizar el número configurando la propiedad
[[yii\log\Dispatcher::flushInterval|flushInterval]] del componente `log`:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 100,   // el valor predeterminado es 1000
            'targets' => [...],
        ],
    ],
];
```

> Info: También se produce la liberación de mensajes cuando la aplicación finaliza, esto asegura que los
  destinos de los registros reciban los mensajes de registro.

Cuando el [[yii\log\Logger|logger object]] libera los mensajes de registro enviándolos a los
[destinos de registros](#log-targets), estos no se exportan inmediatamente. La exportación de mensajes solo se
produce cuando un destino de registros acumula un cierto número de mensajes filtrados. Se puede personalizar este
número configurando la propiedad [[yii\log\Target::exportInterval|exportInterval]] de un
[destinos de registros](#log-targets) individual, como se muestra a continuación,

```php
[
    'class' => 'yii\log\FileTarget',
    'exportInterval' => 100,  // el valor predeterminado es 1000
]
```

Debido al nivel de configuración de la liberación y exportación de mensajes, de forma predeterminada cuando se llama a
`Yii::debug()` o cualquier otro método de registro de mensajes, NO veremos el registro de mensaje inmediatamente en
los destinos de registros. Esto podría ser un problema para algunas aplicaciones de consola de ejecución
prolongada (long-running). Para hacer que los mensajes de registro aparezcan inmediatamente en los destinos de
registro se deben establecer [[yii\log\Dispatcher::flushInterval|flushInterval]] y
[[yii\log\Target::exportInterval|exportInterval]] para que tengan valor 1 como se muestra a continuación:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                ],
            ],
        ],
    ],
];
```

> Note: El uso frecuente de liberación y exportación puede degradar el rendimiento de la aplicación.

### Conmutación de Destinos de Registros <span id="toggling-log-targets"></span>

Se puede habilitar o deshabilitar un destino de registro configuración su propiedad
[[yii\log\Target::enabled|enabled]]. Esto se puede llevar a cabo a mediante la configuración del destino de
registros o con la siguiente declaración PHP de código:

```php
Yii::$app->log->targets['file']->enabled = false;
```

El código anterior requiere que se asocie un destino como `file`, como se muestra a continuación usando las
claves de texto en el array `targets`:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                ],
                'db' => [
                    'class' => 'yii\log\DbTarget',
                ],
            ],
        ],
    ],
];
```

### Creación de Nuevos Destinos <span id="new-targets"></span>

La creación de nuevas clases de destinos de registro es muy simple. Se necesita implementar el método
[[yii\log\Target::export()]] enviando el contenido del array [[yii\log\Target::messages]] al medio designado. Se puede
llamar al método [[yii\log\Target::formatMessage()]] para formatear los mensajes. Se pueden encontrar más detalles de
destinos de registros en las clases incluidas en la distribución de Yii.

## Perfilado de Rendimiento <span id="performance-profiling"></span>

El Perfilado de rendimiento es un tipo especial de registro de mensajes que se usa para medir el tiempo que tardan en
ejecutarse ciertos bloques de código y encontrar donde están los cuellos de botella de rendimiento. Por ejemplo, la
clase [[yii\db\Command]] utiliza el perfilado de rendimiento para encontrar conocer el tiempo que tarda cada consulta
a la base de datos.

Para usar el perfilado de rendimiento, primero debemos identificar los bloques de código que tienen que ser
perfilados, para poder enmarcar su contenido como en el siguiente ejemplo:

```php
\Yii::beginProfile('myBenchmark');

... Empieza el perfilado del bloque de código ...

\Yii::endProfile('myBenchmark');
```

Donde `myBenchmark` representa un token único para identificar el bloque de código. Después cuando se examine el
resulte del perfilado, se podrá usar este token para encontrar el tiempo que ha necesitado el correspondiente bloque
de código.

Es importante asegurarse de que los pares de `beginProfile` y `endProfile` estén bien anidados. Por ejemplo,

```php
\Yii::beginProfile('block1');

    // código que será perfilado

    \Yii::beginProfile('block2');
        // más código para perfilar
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

Si nos dejamos el `\Yii::endProfile('block1')` o lo intercambiamos `\Yii::endProfile('block1')` con
`\Yii::endProfile('block2')`, el perfilado de rendimiento no funcionará.

Se registra un mensaje de registro con el nivel de severidad `profile` para cada bloque de código que se haya
perfilado. Se puede configurar el [destino del registro](#log-targets) para reunir todos los mensajes y exportarlos.
El [depurador de Yii](tool-debugger.md) incluye un panel de perfilado de rendimiento que muestra los resultados de
perfilado.
