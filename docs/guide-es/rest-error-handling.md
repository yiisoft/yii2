Manejo de errores
=================

Cuando se maneja una petición de API RESTful, si ocurre un error en la petición del usuario o si algo inesperado
ocurre en el servidor, simplemente puedes lanzar una excepción para notificar al usuario que algo erróneo ocurrió.
Si puedes identificar la causa del error (p.e., el recurso solicitado no existe), debes considerar lanzar una excepción
con el código HTTP de estado apropiado (p.e., [[yii\web\NotFoundHttpException]] representa un código de estado 404).
Yii enviará la respuesta a continuación con el correspondiente código de estado HTTP y el texto. Yii puede incluir también
la representación serializada de la excepción en el cuerpo de la respuesta.
Por ejemplo:

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

La siguiente lista sumariza los códigos de estado HTTP que son usados por el framework REST:

* `200`: OK. Todo ha funcionado como se esperaba.
* `201`: El recurso ha creado con éxito en respuesta a la petición `POST`. La cabecera de situación `Location`
   contiene la URL apuntando al nuevo recurso creado.
* `204`: La petición ha sido manejada con éxito y el cuerpo de la respuesta no tiene contenido (como una petición `DELETE`).
* `304`: El recurso no ha sido modificado. Puede usar la versión en caché.
* `400`: Petición errónea. Esto puede estar causado por varias acciones de el usuario, como proveer un JSON no válido
   en el cuerpo de la petición, proveyendo parámetros de acción no válidos, etc.
* `401`: Autenticación fallida.
* `403`: El usuario autenticado no tiene permitido acceder a la API final.
* `404`: El recurso pedido no existe.
* `405`: Método no permitido. Por favor comprueba la cabecera `Allow` por los métodos HTTP permitidos.
* `415`: Tipo de medio no soportado. El tipo de contenido pedido o el número de versión no es válido.
* `422`: La validación de datos ha fallado (en respuesta a una petición `POST` , por ejemplo). Por favor, comprueba en el cuerpo de la respuesta el mensaje detallado.
* `429`: Demasiadas peticiones. La petición ha sido rechazada debido a un limitación de rango.
* `500`: Error interno del servidor. Esto puede estar causado por errores internos del programa.


## Personalizar la Respuesta al Error <span id="customizing-error-response"></span>

A veces puedes querer personalizar el formato de la respuesta del error por defecto . Por ejemplo, en lugar de depender
del uso de diferentes estados HTTP para indicar los diferentes errores, puedes querer usar siempre el estado HTTP 200
y encapsular el código de estado HTTP real como parte de una estructura JSON en la respuesta, como se muestra a continuación,

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

Para lograrlo, puedes responder al evento `beforeSend` del componente `response` en la configuración de la aplicación:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null && Yii::$app->request->get('suppress_response_code')) {
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

El anterior código reformateará la respuesta (sea exitosa o fallida) como se explicó cuando
`suppress_response_code` es pasado como un parámetro `GET`.
