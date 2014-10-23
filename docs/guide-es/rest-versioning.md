Versionado
==========

Una buena API ha de ser *versionada*: los cambios y las nuevas características son implementadas en las nuevas versiones del API, en vez de estar continuamente modificando sólo una versión. Al contrario que en las aplicaciones Web, en las cuales tienes total control del código de ambas partes lado del cliente y lado del servidor, las APIs están destinadas a ser usadas por los clientes fuera de tu control. Por esta razón, compatibilidades hacia atrás (BC Backward compatibility) de las APIs ha de ser mantenida siempre que sea posible. Si es necesario un cambio que puede romper la BC, debes de introducirla en la nueva versión del API, e incrementar el número de versión. Los clientes que la usan pueden continuar usando la antigua versión de trabajo del API; los nuevos y actualizados clientes pueden obtener la nueva funcionalidad de la nueva versión del API.

> Tip: referirse a [Semántica del versionado](http://semver.org/) para más información en el diseño del número de versión del API.

Una manera común de implementar el versionado de la API es embeber el número de versión en las URLs del AP.
Por ejemplo, `http://example.com/v1/users` se inicia por la versión 1 de la API del la parte final `/users`.

Otro método de versionado de la API , la cual está ganando predominancia recientemente, es poner el número de versión en las cabeceras de la petición HTTP. Esto se suele hacer típicamente a través la cabecera `Accept` :

```
// vía parámetros
Accept: application/json; version=v1
// vía de el tipo de contenido del vendedor
Accept: application/vnd.company.myapp-v1+json
```

Ambos métodos tienen sus pros y sus contras, y hay gran cantidad de debates sobre cada uno. Debajo puedes ver una estrategia práctica para el versionado de la API que es una mezcla de estos dos métodos:

* Pon cada versión superior del API en un módulo separado cuya ID es el número de la versión principal. (p.e. `v1`, `v2`).
  Naturalmente, las URLs del API pueden contener números de versión superiores.
* Dentro de cada versión superior (y por tanto, dentro del correspondiente módulo), usa la cabecera de HTTP `Accept` para determinar el número de la menor versión y escribe código condicional para responder a la menor versión en consecuencia.

Para cada módulo sirviendo una versión superior, el módulo debe incluir los recursos y la clase controladora que especifican la versión. Para mejor separar la responsabilidad del código, puedes conservar un conjunto de recursos base y clases de controladores comunes, y hacer subclases de ellas en cada uno de los módulos de versión individual. Dentro de las subclases, impementa el código concreto como es `Model::fields()`.

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

La configuración de su aplicación puede tener este aspecto:

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

Como consecuencia de el anterior código, `http://example.com/v1/users` puede devolver la lista de usuarios de la versión 1, mientras
`http://example.com/v2/users` puede devolver la versión 2 de los usuarios.

Gracias a los módulos, el código de las diferentes principales versiónes puede ser aislado. Pero, los módulos, hacen posible reusar el código a través de los módulos vía clases base comunes y otros recursos compartidos.

Para traficar con los números de versión menores, puede obtener las ventajas de el contenido de las capacidades de las conductas de negociación provistas por el [[yii\filters\ContentNegotiator|contentNegotiator]]. La conducta `contentNegotiator` puede poner la propiead [[yii\web\Response::acceptParams]] cuando determina cuál tipo de contenido a soportar.

Por ejemplo, si una peticiónes enviada con la cabecera HTTP `Accept: application/json; version=v1`, entonces la conducta de negociación, [[yii\web\Response::acceptParams]] puede contener el valor `['version' => 'v1']`.

Basado en la información de versión contenida en `acceptParams`, puedes escribir código condicional en lugares como acciones, clases de recursos, serializadores, etc. para proveer la funcionalidad apropiada.

Desde la menor versión, por definición, es necesario mantener la compatibilidad hacia atrás, con suerte no tendrás demasiadas versiones a comporbar en tu código. De otra manera, probablemente puede ocurrir que necesites crear una versión principal.
