Enrutado
=======

Con los recursos y las clases controladoras preparadas, puedes acceder a los recursos usando una URL como `http://localhost/index.php?r=user/create`, parecida a la que usas con aplicaciones Web normales.

En la práctica, querrás usualmente usar URLs más bonitas y obtener ventajas de los comandos de acciones (verbos) HTTP.
Por ejemplo, una petición `POST /users` puede permitir el acceso a la acción `user/create`.
Esto puede realizarse fácilmente configurando el componente de la aplicación `urlManager` en la configuración tal y como sigue:

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

En comparación con la gestión de URL en las aplicaciones Web, lo nuevo de lo anterior es el uso de [[yii\rest\UrlRule]] para el enrutado de las peticiones con el API RESTful. Esta clase especial que contiene la norma para gestionar las URLs puede crear todo un conjunto de URLs hijas para mantener el enrutado y la creación de URLs para la/s especificada/s controlador/as.
Por ejemplo, el código anterior es aproximadamente equivalente a las siguientes reglas:

```php
[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]
```

Y los siguientes puntos finales del API son mantenidos por la siguiente regla:

* `GET /users`: listado de todos los usuarios página a página;
* `HEAD /users`: enseña ĺa información resumén del usuario listado;
* `POST /users`: crea un nuevo usuario;
* `GET /users/123`: devuelve los detalles del usuario 123;
* `HEAD /users/123`: enseña la información resúmen del usuario 123;
* `PATCH /users/123` y `PUT /users/123`: actualizan al usuario 123;
* `DELETE /users/123`: borra el usuario 123;
* `OPTIONS /users`: presenta las acciones finales soportadas por `/users`;
* `OPTIONS /users/123`: presenta las acciones finales que soporta `/users/123`.

Puedes configurar las opciones  `only` y `except` para explícitamente listar las acciones a soportar y cuales desabilitar, respectivamente. Por ejemplo,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],
```

También puedes configurar `patterns` o `extraPatterns` para redifinir patrones existentes o añadir nuevos patrones que soportan esta regla.
Por ejemplo, para soportar la nueva acción `search` por `GET /users/search`, configura la opción `extraPatterns` como sigue,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
```

Queda advertido que la ID de la controladora `user` aparece finalmente en plural  tal que`users`.
Esto es debido a que [[yii\rest\UrlRule]] pluraliza de forma automáticalos IDs de las controladoras para ser usadas en los puntos finales.
Puedes desactivar este comportamiento poniendo a false [[yii\rest\UrlRule::pluralize]] , o si quieres usar algunos nombres especiales, debes configurar la propiedad [[yii\rest\UrlRule::controller]]. Dése cuenta que la pluralización de puntos finales del RESTful no siempre añade simplemente una "s" l final de la id de la controladora. Una controladora cuyo ID termina en "x", por ejemplo "BoxController" (con ID `box`), tiene el punto final del RESTful pluralizada  a `boxes` por [[yii\rest\UrlRule]].
