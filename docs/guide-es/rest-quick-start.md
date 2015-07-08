Guía Breve
==========

Yii ofrece todo un conjunto de herramientas para simplificar la tarea de implementar un
servicio web APIs RESTful.
En particular, Yii soporta las siguientes características sobre APIs RESTful;

* Prototipado rápido con soporte para APIs comunes para [Active Record](db-active-record.md);
* Formato de respuesta de negocio (soporta JSON y XML por defecto);
* Personalización de objetos serializados con soporte para campos de salida seleccionables;
* Formateo apropiado de colecciones de datos y validación de errores;
* Soporte para [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS);
* Eficiente enrutamiento con una adecuada comprobación del verbo(verb) HTTP;
* Incorporado soporte para las `OPTIONS` y `HEAD` verbos;
* Autenticación y autorización;
* Cacheo de datos y cacheo HTTP;
* Limitación de rango;


A continuación, utilizamos un ejemplo para ilustrar como se puede construir un conjunto de APIs RESTful con un esfuerzo mínimo de codificación.

Supongamos que deseas exponer los datos de los usuarios vía APIs RESTful. Los datos de usuario son almacenados en la tabla DB `user`,
y ya tienes creado la clase [[yii\db\ActiveRecord|ActiveRecord]] `app\models\User` para acceder a los datos del usuario.


## Creando un controlador <span id="creating-controller"></span>

Primero, crea una clase controladora `app\controllers\UserController` como la siguiente,

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```

La clase controladora extiende de [[yii\rest\ActiveController]]. Especificado por [[yii\rest\ActiveController::modelClass|modelClass]]
como `app\models\User`, el controlador sabe que modelo puede ser usado para recoger y manipular sus datos.


## Configurando las reglas de las URL <span id="configuring-url-rules"></span>

A continuación, modifica la configuración del componente `urlManager` en la configuración de tu aplicación:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]
```

La configuración anterior principalmente añade una regla URL para el controlador `user` de manera
que los datos de user pueden ser accedidos y manipulados con URLs amigables y verbos HTTP significativos.


## Habilitando entradas JSON <span id="enabling-json-input"></span>

Para permitir que la API acepte datos de entrada con formato JSON, configura la propiedad [[yii\web\Request::$parsers|parsers]]
del componente de aplicación `request` para usar [[yii\web\JsonParser]] para entradas JSON:

```php
'request' => [
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
    ]
]
```

> Consejo: La configuración anterior es opcional. Sin la configuración anterior, la API sólo reconocería
  `application/x-www-form-urlencoded` y `multipart/form-data` como formatos de entrada.


## Probándolo <span id="trying-it-out"></span>

Con la mínima cantidad de esfuerzo, tienes ya finalizado tu tarea de crear las APIs RESTful
para acceder a los datos de user. Las APIs que tienes creado incluyen:

* `GET /users`: una lista de todos los usuarios página por página;
* `HEAD /users`: muestra la información general de la lista de usuarios;
* `POST /users`: crea un nuevo usuario;
* `GET /users/123`: devuelve los detalles del usuario 123;
* `HEAD /users/123`: muestra la información general del usuario 123;
* `PATCH /users/123` y `PUT /users/123`: actualiza el usuario 123;
* `DELETE /users/123`: elimina el usuario 123;
* `OPTIONS /users`: muestra los verbos compatibles respecto al punto final `/users`;
* `OPTIONS /users/123`: muestra los verbos compatibles respecto al punto final `/users/123`.

> Información: Yii automáticamente pluraliza los nombres de los controladores para usarlo en los puntos finales.
> Puedes configurar esto usando la propiedad [[yii\rest\UrlRule::$pluralize]].

Puedes acceder a tus APIs con el comando `curl` de la siguiente manera,

```
$ curl -i -H "Accept:application/json" "http://localhost/users"

HTTP/1.1 200 OK
...
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
    {
        "id": 1,
        ...
    },
    {
        "id": 2,
        ...
    },
    ...
]
```

Intenta cambiar el tipo de contenido aceptado para ser `application/xml`, y verá que el resultado
se devuelve en formato XML:

```
$ curl -i -H "Accept:application/xml" "http://localhost/users"

HTTP/1.1 200 OK
...
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response>
    <item>
        <id>1</id>
        ...
    </item>
    <item>
        <id>2</id>
        ...
    </item>
    ...
</response>
```

El siguiente comando creará un nuevo usuario mediante el envío de una petición POST con los datos del usuario en formato JSON:

```
$ curl -i -H "Accept:application/json" -H "Content-Type:application/json" -XPOST "http://localhost/users" -d '{"username": "example", "email": "user@example.com"}'

HTTP/1.1 201 Created
...
Location: http://localhost/users/1
Content-Length: 99
Content-Type: application/json; charset=UTF-8

{"id":1,"username":"example","email":"user@example.com","created_at":1414674789,"updated_at":1414674789}
```

> Consejo: También puedes acceder a tus APIs a través del navegador web  introduciendo la URL `http://localhost/users`.
  Sin embargo, es posible que necesites algunos plugins para el navegador para enviar cabeceras especificas en la petición.

Como se puede ver, en las cabeceras de la respuesta, hay información sobre la cuenta total, número de páginas, etc.
También hay enlaces que permiten navegar por otras páginas de datos. Por ejemplo, `http://localhost/users?page=2`
le daría la página siguiente de los datos de usuario.

Utilizando los parámetros `fields` y `expand`, puedes también especificar que campos deberían ser incluidos en el resultado.
Por ejemplo, la URL `http://localhost/users?fields=id,email` sólo devolverá los campos `id` y `email`.


> Información: Puedes haber notado que el resultado de `http://localhost/users` incluye algunos campos sensibles,
> tal como `password_hash`, `auth_key`. Seguramente no quieras que éstos aparecieran en el resultado de tu API.
> Puedes y deberías filtrar estos campos como se describe en la sección [Response Formatting](rest-response-formatting.md).


## Resumen <span id="summary"></span>

Utilizando el framework Yii API RESTful, implementa un punto final API en términos de una acción de un controlador, y utiliza
un controlador para organizar las acciones que implementan los puntos finales para un sólo tipo de recurso.

Los recursos son representados como modelos de datos que extienden de la clase [[yii\base\Model]].
Si estás trabajando con bases de datos (relacionales o NoSQL), es recomendable utilizar [[yii\db\ActiveRecord|ActiveRecord]]
para representar los recursos.

Puedes utilizar [[yii\rest\UrlRule]] para simplificar el enrutamiento de los puntos finales de tu API.

Aunque no es obligatorio, es recomendable que desarrolles tus APIs RESTful como una aplicación separada, diferente de
tu WEB front end y tu back end para facilitar el mantenimiento.
