Respuestas
==========

Cuando una aplicación finaliza la gestión de una [petición (request)](runtime-requests.md), genera un objeto 
[[yii\web\Response|response]] y lo envía al usuario final. El objeto response contiene información tal como el código 
de estado (status code) HTTP, las cabeceras (headers) HTTP y el cuerpo (body). El objetivo final del desarrollo de una 
aplicación Web es esencialmente construir objetos response para varias peticiones.

En la mayoría de casos principalmente se debe tratar con 
[componentes de aplicación](structure-application-components.md) de tipo `response` que, por defecto, son una 
instancia de [[yii\web\Response]]. Sin embargo, Yii permite crear sus propios objetos `response` y enviarlos al 
usuario final tal y como se explica a continuación.

En esta sección, se describirá como generar y enviar respuestas a usuarios finales.

## Códigos de Estado <span id="status-code"></span>

Una de las primeras cosas que debería hacerse cuando se genera una respuesta es indicar si la petición se ha 
gestionado correctamente. Esto se indica asignando la propiedad [[yii\web\Response::statusCode]] a la que se le puede 
asignar cualquier valor válido dentro de los 
[códigos de estado HTTP](https://tools.ietf.org/html/rfc2616#section-10). Por ejemplo, para indicar que la 
petición se ha gestionado correctamente, se puede asignar el código de estado a 200, como en el siguiente ejemplo:

```php
Yii::$app->response->statusCode = 200;
```

Sin embargo, en la mayoría de casos nos es necesario asignar explícitamente el código de estado. Esto se debe a que el 
valor por defecto de [[yii\web\Response::statusCode]] es 200. Y si se quiere indicar que la petición ha fallado, se 
puede lanzar una excepción HTTP apropiada como en el siguiente ejemplo:

```php
throw new \yii\web\NotFoundHttpException;
```

Cuando el [error handler](runtime-handling-errors.md) captura una excepción, obtendrá el código de estado de la 
excepción y lo asignará a la respuesta. En el caso anterior, la excepción [[yii\web\NotFoundHttpException]] está 
asociada al estado HTTP 404. En Yii existen las siguientes excepciones predefinidas.

* [[yii\web\BadRequestHttpException]]: código de estado 400.
* [[yii\web\ConflictHttpException]]: código de estado 409.
* [[yii\web\ForbiddenHttpException]]: código de estado 403.
* [[yii\web\GoneHttpException]]: código de estado 410.
* [[yii\web\MethodNotAllowedHttpException]]: código de estado 405.
* [[yii\web\NotAcceptableHttpException]]: código de estado 406. 
* [[yii\web\NotFoundHttpException]]: código de estado 404.
* [[yii\web\ServerErrorHttpException]]: código de estado 500.
* [[yii\web\TooManyRequestsHttpException]]: código de estado 429.
* [[yii\web\UnauthorizedHttpException]]: código de estado 401.
* [[yii\web\UnsupportedMediaTypeHttpException]]: código de estado 415.

Si la excepción que se quiere lanzar no se encuentra en la lista anterior, se puede crear una extendiendo 
[[yii\web\HttpException]], o directamente lanzando un código de estado, por ejemplo:

```php
throw new \yii\web\HttpException(402);
```

## Cabeceras HTTP <span id="http-headers"></span> 

Se puede enviar cabeceras HTTP modificando el [[yii\web\Response::headers|header collection]] en el componente 
`response`. Por ejemplo:

```php
$headers = Yii::$app->response->headers;

// añade una cabecera Pragma. Las cabeceras Pragma existentes NO se sobrescribirán.
$headers->add('Pragma', 'no-cache');

// asigna una cabecera Pragma. Cualquier cabecera Pragma existente será descartada.
$headers->set('Pragma', 'no-cache');

// Elimina las cabeceras Pragma y devuelve los valores de las eliminadas en un array
$values = $headers->remove('Pragma');
```

> Info: Los nombres de las cabeceras case insensitive, es decir, no discriminan entre mayúsculas y minúsculas. 
Además, las nuevas cabeceras registradas no se enviarán al usuario hasta que se llame al método 
[[yii\web\Response::send()]].

## Cuerpo de la Respuesta<span id="response-body"></span>

La mayoría de las respuestas deben tener un cuerpo que contenga el contenido que se quiere mostrar a los usuarios 
finales.

Si ya se tiene un texto de cuerpo con formato, se puede asignar a la propiedad [[yii\web\Response::content]] del 
response. Por ejemplo:

```php
Yii::$app->response->content = 'hello world!';
```

Si se tiene que dar formato a los datos antes de enviarlo al usuario final, se deben asignar las propiedades 
[[yii\web\Response::format|format]] y [[yii\web\Response::data|data]]. La propiedad [[yii\web\Response::format|format]]
 especifica que formato debe tener [[yii\web\Response::data|data]]. Por ejemplo:

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii soporta a los siguientes formatos de forma predeterminada, cada uno de ellos implementado por una clase 
[[yii\web\ResponseFormatterInterface|formatter]]. Se pueden personalizar los formatos o añadir nuevos sobrescribiendo 
la propiedad [[yii\web\Response::formatters]].

* [[yii\web\Response::FORMAT_HTML|HTML]]: implementado por [[yii\web\HtmlResponseFormatter]].
* [[yii\web\Response::FORMAT_XML|XML]]: implementado por [[yii\web\XmlResponseFormatter]].
* [[yii\web\Response::FORMAT_JSON|JSON]]: implementado por [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: implementado por [[yii\web\JsonResponseFormatter]].

Mientras el cuerpo de la respuesta puede ser mostrado de forma explicita como se muestra a en el anterior ejemplo, en 
la mayoría de casos se puede asignar implícitamente por el valor de retorno de los métodos de 
[acción](structure-controllers.md). El siguiente, es un ejemplo de uso común:

```php
public function actionIndex()
{
    return $this->render('index');
}
```

La acción `index` anterior, devuelve el resultado renderizado de la vista `index`. El valor devuelto será recogido por 
el componente `response`, se le aplicará formato y se enviará al usuario final.

Por defecto, el formato de respuesta es [[yii\web\Response::FORMAT_HTML|HTML]], sólo se debe devolver un string en un 
método de acción.  Si se quiere usar un formato de respuesta diferente, se debe asignar antes de devolver los datos. 
Por ejemplo:

```php
public function actionInfo()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
        'message' => 'hello world',
        'code' => 100,
    ];
}
```

Como se ha mencionado, además de usar el componente de aplicación `response` predeterminado, también se pueden crear 
objetos response propios y enviarlos a los usuarios finales. Se puede hacer retornando un objeto en el método de 
acción, como en el siguiente ejemplo:

```php
public function actionInfo()
{
    return \Yii::createObject([
        'class' => 'yii\web\Response',
        'format' => \yii\web\Response::FORMAT_JSON,
        'data' => [
            'message' => 'hello world',
            'code' => 100,
        ],
    ]);
}
```

> Note: Si se crea un objeto response propio, no se podrán aprovechar las configuraciones asignadas para el componente 
`response` en la configuración de la aplicación. Sin embargo, se puede usar la 
[inyección de dependencias](concept-di-container.md) para aplicar la configuración común al nuevo objeto response.

## Redirección del Navegador <span id="browser-redirection"></span>

La redirección del navegador se basa en el envío de la cabecera HTTP `Location`. Debido a que esta característica se 
usa comúnmente, Yii proporciona soporte especial para ello.

Se puede redirigir el navegador a una URL llamando al método [[yii\web\Response::redirect()]]. El método asigna la 
cabecera de `Location` apropiada con la URL proporcionada y devuelve el objeto response él mismo. En un método de 
acción, se puede acceder a él mediante el acceso directo [[yii\web\Controller::redirect()]] como en el siguiente 
ejemplo:

```php
public function actionOld()
{
    return $this->redirect('https://example.com/new', 301);
}
```

En el ejemplo anterior, el método de acción devuelve el resultado del método `redirect()`. Como se ha explicado antes, 
el objeto response devuelto por el método de acción se usará como respuesta enviándola al usuario final.

En otros sitios que no sean los métodos de acción, se puede llamar a [[yii\web\Response::redirect()]] directamente 
seguido por una llamada al método [[yii\web\Response::send()]] para asegurar que no habrá contenido extra en la 
respuesta.

```php
\Yii::$app->response->redirect('https://example.com/new', 301)->send();
```

> Info: De forma predeterminada, el método [[yii\web\Response::redirect()]] asigna el estado de respuesta al 
código de estado 302 que indica al navegador que recurso solicitado está *temporalmente* alojado en una URI diferente. 
Se puede enviar un código de estado 301 para expresar que el recurso se ha movido de forma *permanente*.

Cuando la petición actual es de una petición AJAX, el hecho de enviar una cabecera `Location` no causará una 
redirección del navegador automática. Para resolver este problema, el método [[yii\web\Response::redirect()]] asigna 
una cabecera `X-Redirect` con el valor de la URL de redirección. En el lado del cliente se puede escribir código 
JavaScript para leer la esta cabecera y redireccionar el navegador como corresponda.

> Info: Yii contiene el archivo JavaScript `yii.js` que proporciona un conjunto de utilidades comunes de 
JavaScript, incluyendo la redirección de navegador basada en la cabecera `X-Redirect`. Por tanto, si se usa este 
fichero JavaScript (registrándolo *asset bundle* [[yii\web\YiiAsset]]), no se necesitará escribir nada más para tener 
soporte en redirecciones AJAX.

## Enviar Archivos <span id="sending-files"></span>

Igual que con la redirección, el envío de archivos es otra característica que se basa en cabeceras HTTP especificas. 
Yii proporciona un conjunto de métodos para dar soporte a varias necesidades del envío de ficheros. Todos ellos 
incorporan soporte para el rango de cabeceras HTTP.

* [[yii\web\Response::sendFile()]]: envía un fichero existente al cliente.
* [[yii\web\Response::sendContentAsFile()]]: envía un string al cliente como si fuera un archivo.
* [[yii\web\Response::sendStreamAsFile()]]: envía un *file stream* existente al cliente como si fuera un archivo.

Estos métodos tienen la misma firma de método con el objeto response como valor de retorno. Si el archivo que se envía 
es muy grande, se debe considerar usar [[yii\web\Response::sendStreamAsFile()]] porque es más efectivo en términos de 
memoria. El siguiente ejemplo muestra como enviar un archivo en una acción de controlador.

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('ruta/del/fichero.txt');
}
```

Si se llama al método de envío de ficheros fuera de un método de acción, también se debe llamar al método 
[[yii\web\Response::send()]] después para asegurar que no se añada contenido extra a la respuesta.

```php
\Yii::$app->response->sendFile('ruta/del/fichero.txt')->send();
```

Algunos servidores Web tienen un soporte especial para enviar ficheros llamado *X-Sendfile*. La idea es redireccionar 
la petición para un fichero a un servidor Web que servirá el fichero directamente. Como resultado, la aplicación Web 
puede terminar antes mientras el servidor Web envía el fichero. Para usar esta funcionalidad, se puede llamar a 
[[yii\web\Response::xSendFile()]]. La siguiente lista resume como habilitar la característica `X-Sendfile` para 
algunos servidores Web populares.

- Apache: [X-Sendfile](https://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](https://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](https://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/)
- Cherokee: [X-Sendfile and X-Accel-Redirect](https://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)

## Enviar la Respuesta <span id="sending-response"></span>

El contenido en una respuesta no se envía al usuario hasta que se llama al método [[yii\web\Response::send()]]. De 
forma predeterminada, se llama a este método automáticamente al final de [[yii\base\Application::run()]]. Sin embargo, 
se puede llamar explícitamente a este método forzando el envío de la respuesta inmediatamente.

El método [[yii\web\Response::send()]] sigue los siguientes pasos para enviar una respuesta:

1. Lanza el evento [[yii\web\Response::EVENT_BEFORE_SEND]].
2. Llama a [[yii\web\Response::prepare()]] para convertir el [[yii\web\Response::data|response data]] en 
   [[yii\web\Response::content|response content]].
3. Lanza el evento [[yii\web\Response::EVENT_AFTER_PREPARE]].
4. Llama a [[yii\web\Response::sendHeaders()]] para enviar las cabeceras HTTP registradas.
5. Llama a [[yii\web\Response::sendContent()]] para enviar el contenido del cuerpo de la respuesta.
6. Lanza el evento [[yii\web\Response::EVENT_AFTER_SEND]].

Después de llamar a [[yii\web\Response::send()]] por primera vez, cualquier llamada a este método será ignorada. Esto 
significa que una vez se envíe una respuesta, no se le podrá añadir más contenido.

Como se puede observar, el método [[yii\web\Response::send()]] lanza varios eventos útiles. Al responder a estos 
eventos, es posible ajustar o decorar la respuesta.
