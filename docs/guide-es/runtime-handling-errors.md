Gestión de Errores
==================

Yii incluye un [[yii\web\ErrorHandler|error handler]] que permite una gestión de errores mucho más práctica que
anteriormente. En particular, el gestor de errores de Yii hace lo siguiente para mejorar la gestión de errores:

* Todos los errores no fatales (ej. advertencias (warning), avisos (notices)) se convierten en excepciones
  capturables.
* Las excepciones y los errores fatales de PHP se muestran con una pila de llamadas (call stack) de información
  detallada y lineas de código fuente.
* Soporta el uso de [acciones de controlador](structure-controllers.md#actions) dedicadas para mostrar errores.
* Soporta diferentes formatos de respuesta (response) de errores.

El [[yii\web\ErrorHandler|error handler]] esta habilitado de forma predeterminada. Se puede deshabilitar definiendo la
constante `YII_ENABLE_ERROR_HANDLER` con valor false en el
[script de entrada (entry script)](structure-entry-scripts.md) de la aplicación.

## Uso del Gestor de Errores <span id="using-error-handler"></span>

El [[yii\web\ErrorHandler|error handler]] se registra como un
[componente de aplicación](structure-application-components.md) llamado `errorHandler`. Se puede configurar en la
configuración de la aplicación como en el siguiente ejemplo:

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

Con la anterior configuración, el numero del lineas de código fuente que se mostrará en las páginas de excepciones
será como máximo de 20.

Como se ha mencionado, el gestor de errores convierte todos los errores de PHP no fatales en excepciones capturables.
Esto significa que se puede usar el siguiente código para tratar los errores PHP:

```php
use Yii;
use yii\base\ErrorException;

try {
    10/0;
} catch (ErrorException $e) {
    Yii::warning("Division by zero.");
}

// la ejecución continua ...
```

Si se quiere mostrar una página de error que muestra al usuario que su petición no es válida o no es la esperada, se
puede simplemente lanzar una excepción de tipo [[yii\web\HttpException|HTTP exception]], como podría ser
[[yii\web\NotFoundHttpException]]. El gestor de errores establecerá correctamente el código de estado HTTP de la
respuesta y usará la vista de error apropiada para mostrar el mensaje.

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```

## Personalizar la Visualización de Errores <span id="customizing-error-display"></span>

El [[yii\web\ErrorHandler|error handler]] ajusta la visualización del error conforme al valor de la constante
`YII_DEBUG`. Cuando `YII_DEBUG` es `true` (es decir, en modo depuración (debug)), el gestor de errores mostrara las
excepciones con una pila detallada de información y con lineas de código fuente para ayudar a depurar. Y cuando la
variable `YII_DEBUG` es `false`, solo se mostrará el mensaje de error para prevenir la revelación de información
sensible de la aplicación.

> Información: Si una excepción es descendiente de [[yii\base\UserException]], no se mostrará la pila de llamadas
  independientemente del valor de `YII_DEBUG`. Esto es debido a que se considera que estas excepciones se deben a
  errores cometidos por los usuarios y los desarrolladores no necesitan corregirlas.

De forma predeterminada, el [[yii\web\ErrorHandler|error handler]] muestra los errores usando dos
[vistas](structure-views.md):

* `@yii/views/errorHandler/error.php`: se usa cuando deben mostrarse los errores SIN la información de la pila de
  llamadas. Cuando `YII_DEBUG` es falos, este es el único error que se mostrara.
* `@yii/views/errorHandler/exception.php`: se usa cuando los errores deben mostrarse CON la información de la pila de
  llamadas.

Se pueden configurar las propiedades [[yii\web\ErrorHandler::errorView|errorView]] y
[[yii\web\ErrorHandler::exceptionView|exceptionView]] el gestor de errores para usar nuestros propias vistas para
personalizar la visualización de los errores.

### Uso de Acciones de Error <span id="using-error-actions"></span>

Una mejor manera de personalizar la visualización de errores es usar un [acción](structure-controllers.md) de error
dedicada. Para hacerlo, primero se debe configurar la propiedad [[yii\web\ErrorHandler::errorAction|errorAction]] del
componente `errorHandler` como en el siguiente ejemplo:

```php
return [
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ]
];
```

La propiedad [[yii\web\ErrorHandler::errorAction|errorAction]] vincula una [ruta](structure-controllers.md#routes) a
una acción. La configuración anterior declara que cuando un error tiene que mostrarse sin la pila de información de
llamadas, se debe ejecutar la acción `site/error`.

Se puede crear una acción `site/error` como se hace a continuación,

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
```

El código anterior define la acción `error` usando la clase [[yii\web\ErrorAction]] que renderiza un error usando la
vista llamada `error`.

Además, usando [[yii\web\ErrorAction]], también se puede definir la acción `error` usando un método de acción como en
el siguiente ejemplo,

```php
public function actionError()
{
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

Ahora se debe crear un archivo de vista ubicado en `views/sites/error.php`. En este archivo de vista, se puede acceder
a las siguientes variables si se define el error como un [[yii\web\ErrorAction]]:

* `name`: el nombre del error;
* `message`: el mensaje del error;
* `exception`: el objeto de excepción a través del cual se puede obtener más información útil, tal como el código de
  estado HTTP, el código de error, la pila de llamadas del error, etc.

> Información: Tanto la [plantilla de aplicación básica](start-installation.md) como la
  [plantilla de aplicación avanzada](tutorial-advanced-app.md), ya incorporan la acción de error y la vista de error.

### Personalizar el Formato de Respuesta de Error <span id="error-format"></span>

El gestor de errores muestra los errores de siguiente la configuración del formato de las
[respuestas](runtime-responses.md). Si el [[yii\web\Response::format response format]] es `html`, se usará la vista de
error o excepción para mostrar los errores tal y como se ha descrito en la anterior subsección. Para otros tipos de
formatos de respuesta, el gestor de errores asignara la representación del array de la excepción a la propiedad
[[yii\web\Response::data]] que posteriormente podrá convertirse al formato deseado. Por ejemplo, si el formato de
respuesta es `json`, obtendremos la siguiente respuesta:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

Se puede personalizar el formato de respuestas de error respondiendo al evento `beforeSend` del componente `response`
en la configuración de la aplicación:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

El código anterior reformateará la respuesta de error como en el siguiente ejemplo:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```