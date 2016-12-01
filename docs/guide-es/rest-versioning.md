Versionado
==========

Una buena API ha de ser *versionada*: los cambios y las nuevas características son implementadas en las nuevas versiones del API, en vez de estar continuamente modificando sólo una versión. Al contrario que en las aplicaciones Web, en las cuales tienes total control del código de ambas partes lado del cliente y lado del servidor,
las APIs están destinadas a ser usadas por los clientes fuera de tu control. Por esta razón, compatibilidad hacia atrás (BC Backward compatibility)
de las APIs ha de ser mantenida siempre que sea posible. Si es necesario un cambio que puede romper la BC, debes de introducirla en la nueva versión del API, e incrementar el número de versión. Los clientes que la usan pueden continuar usando la antigua versión de trabajo del API; los nuevos y actualizados clientes pueden obtener la nueva funcionalidad de la nueva versión del API.

> Tip: referirse a [Semántica del versionado](http://semver.org/)
para más información en el diseño del número de versión del API.

Una manera común de implementar el versionado de la API es embeber el número de versión en las URLs de la  API.
Por ejemplo, `http://example.com/v1/users` se refiere al punto final `/users` de la versión 1 de la API. 

Otro método de versionado de la API,
la cual está ganando predominancia recientemente, es poner el número de versión en las cabeceras de la petición HTTP. Esto se suele hacer típicamente a través la cabecera `Accept`:

```
// vía parámetros
Accept: application/json; version=v1
// vía de el tipo de contenido del proveedor
Accept: application/vnd.company.myapp-v1+json
```

Ambos métodos tienen sus pros y sus contras, y hay gran cantidad de debates sobre cada uno. Debajo puedes ver una estrategia
práctica para el versionado de la API que es una mezcla de estos dos métodos:

* Pon cada versión superior de la implementación de la API en un módulo separado cuyo ID es el número de la versión mayor (p.e. `v1`, `v2`).
  Naturalmente, las URLs de la API contendrán números de versión mayores.
* Dentro de cada versión mayor (y por lo tanto, dentro del correspondiente módulo), usa la cabecera de HTTP `Accept`
  para determinar el número de la versión menor y escribe código condicional para responder a la menor versión como corresponde.

Para cada módulo sirviendo una versión mayor, el módulo debe incluir las clases de recursos y y controladores
que especifican la versión. Para separar mejor la responsabilidad del código, puedes conservar un conjunto común de
clases base de recursos y controladores, y hacer subclases de ellas en cada versión individual del módulo. Dentro de las subclases,
impementa el código concreto como por ejemplo `Model::fields()`.

Tu código puede estar organizado como lo que sigue:

```
api/
    common/
        controllers/
            UserController.php
            PostController.php
        models/
            User.php
            Post.php
    modules/
        v1/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
```

La configuración de tu aplicación puede tener este aspecto:

```php
return [
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'controllerNamespace' => 'app\modules\v1\controllers',
        ],
        'v2' => [
            'basePath' => '@app/modules/v2',
            'controllerNamespace' => 'app\modules\v2\controllers',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
            ],
        ],
    ],
];
```

Como consecuencia del código anterior, `http://example.com/v1/users` devolverá la lista de usuarios en la versión 1, mientras
`http://example.com/v2/users` devolverá la versión 2 de los usuarios.

Gracias a los módulos, el código de las diferentes principales versiones puede ser aislado. Pero los módulos hacen posible
reutilizar el código a través de los módulos vía clases base comunes y otros recursos compartidos.

Para tratar con versiones menores, puedes tomar ventaja de la característica de negociación de contenido
provista por el comportamiento (behavior) [[yii\filters\ContentNegotiator|contentNegotiator]]. El comportamiento `contentNegotiator`
definirá la propiedad [[yii\web\Response::acceptParams]] cuando determina qué tipo
de contenido soportar.

Por ejemplo, si una petición es enviada con la cabecera HTTP `Accept: application/json; version=v1`,
después de la negociación de contenido, [[yii\web\Response::acceptParams]] contendrá el valor `['version' => 'v1']`.

Basado en la información de versión contenida en `acceptParams`, puedes escribir código condicional en lugares
como acciones, clases de recursos, serializadores, etc. para proveer la funcionalidad apropiada.

Dado que por definición las versiones menores requireren mantener la compatibilidad hacia atrás, con suerte no tendrás demasiadas
comprobaciones de versión en tu código. De otra manera, probablemente puede ocurrir que necesites crear una versión mayor.
