Enrutamiento
============

Con las clases de controlador y recurso preparadas, puedes acceder a los recursos usando una URL como
`http://localhost/index.php?r=user/create`, parecida a la que usas con aplicaciones Web normales.

En la práctica, querrás usualmente usar URLs limpias y obtener ventajas de los verbos HTTP.
Por ejemplo, una petición `POST /users` significaría acceder a la acción `user/create`.
Esto puede realizarse fácilmente configurando el componente de la aplicación `urlManager`
como sigue:

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

En comparación con la gestión de URL en las aplicaciones Web, lo principalmente nuevo de lo anterior es el uso de
[[yii\rest\UrlRule]] para el enrutamiento de las peticiones con el API RESTful. Esta clase especial de regla URL creará
un conjunto completo de reglas URL hijas para soportar el enrutamiento y creación de URL para el/los controlador/es especificados.
Por ejemplo, el código anterior es equivalente a las siguientes reglas:

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

Y los siguientes puntos finales del API son mantenidos por esta regla:

* `GET /users`: lista de todos los usuarios página a página;
* `HEAD /users`: muestra ĺa información resumén del usuario listado;
* `POST /users`: crea un nuevo usuario;
* `GET /users/123`: devuelve los detalles del usuario 123;
* `HEAD /users/123`: muestra la información resúmen del usuario 123;
* `PATCH /users/123` y `PUT /users/123`: actualizan al usuario 123;
* `DELETE /users/123`: borra el usuario 123;
* `OPTIONS /users`: muestra los verbos soportados de acuerdo al punto final `/users`;
* `OPTIONS /users/123`: muestra los verbos soportados de acuerdo al punto final `/users/123`.

Puedes configurar las opciones  `only` y `except` para explícitamente listar cuáles acciones a soportar o cuáles
deshabilitar, respectivamente. Por ejemplo,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],
```

También puedes configurar las propiedades `patterns` o `extraPatterns` para redifinir patrones existentes o añadir nuevos soportados por esta regla.
Por ejemplo, para soportar una nueva acción `search` por  el punto final `GET /users/search`, configura la opción `extraPatterns` como sigue,

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
]
```

Puedes haber notado que el ID del controlador `user` aparece en formato plural `users` en los puntos finales de las URLs.
Esto se debe a que [[yii\rest\UrlRule]] automáticamente pluraliza los IDs de los controladores al crear reglas URL hijas.
Puedes desactivar este comportamiento definiendo la propiedad [[yii\rest\UrlRule::pluralize]] como false. 

> Info: La pluralización de los IDs de los controladores es realizada por [[yii\helpers\Inflector::pluralize()]]. Este método respeta
  reglas especiales de pluralización. Por ejemplo, la palabra `box` (caja) será pluralizada como `boxes` en vez de `boxs`.

En caso de que la pluralización automática no encaje en tus requerimientos, puedes además configurar la propiedad 
[[yii\rest\UrlRule::controller]] para especificar exlpícitamente cómo mapear un nombre utilizado en un punto final URL
a un ID de controlador. Por ejemplo, el siguiente código mapea el nombre `u` al ID del controlador `user`.  
 
```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['u' => 'user'],
]
```
