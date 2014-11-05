Autenticación
==============

A diferencia de las aplicaciones Web, las API RESTful son usualmente sin estado (stateless), lo que permite que las sesiones o las cookies no sean usadas. Por lo tanto, cada petición debe llevar alguna suerte de credenciales de autenticación, porque la autenticación del usuario no puede ser mantenida por las sesiones o las cookies. Una práctica común es enviar una pieza (token) secreta de acceso con cada petición para autenticar al usuario. Dado que una pieza de autenticación puede ser usada para identificar y autenticar solamente a un usuario, **el API de peticiones tiene que ser siempre enviado vía HTTPS para prevenir ataques que intervengan en la transmisión "man-in-the-middle" (MitM) **.
> Tip: Sin estado (stateless) se refiere a un protocolo en el que cada petición es una transacción independiente del resto de peticiones y la comunicación consiste en pares de peticion y respuesta, por lo que no es necesario retener información en la sesión.

Hay muchas maneras de enviar una pieza (token) de acceso:

* [Autorización Básica HTTP (HTTP Basic Auth)](http://en.wikipedia.org/wiki/Basic_access_authentication): la pieza de acceso es enviada como nombre de usuario. Esto sólo debe de ser usado cuando la pieza de acceso puede ser guardada de forma segura en la parte del API del consumidor. Por ejemplo, el API del consumidor es un programa ejecutándose en un servidor.
* Parámetro de la consulta: la pieza de acceso es enviada como un parámetro de la consulta en la URL de la API, p.e.,
  `https://example.com/users?access-token=xxxxxxxx`. Debido que muchos servidores dejan los parámetros de consulta en los logs del servidor esta aproximación suele ser usada principalmente para servir peticiones `JSONP` que  no usen las cabeceras HTTP para enviar piezas de acceso.
* [OAuth 2](http://oauth.net/2/): la pieza de acceso es obtenida por el consumidor por medio de una autorización del servidor y enviada al API del servidor según el protocolo OAuth 2 [Piezas HTTP de la portadora (HTTP Bearer Tokens)](http://tools.ietf.org/html/rfc6750).

Yii soporta todos los métodos anteriores de autenticación. Puedes crear nuevos métodos de autenticación de una forma fácil.

Para activar la autenticación para tus APIs, sigue los pasos siguientes:

1. Configura la propiedad [[yii\web\User::enableSession|enableSession]] de el componente  `user`  de la aplicación a false.
2. Especifica cuál método de autenticación planeas usar configurando la funcionalidad  `authenticator` en las clases de la controladora REST.
3. Implementa [[yii\web\IdentityInterface::findIdentityByAccessToken()]] en tu [[yii\web\User::identityClass|clase de identidad de usuario]].

El paso 1 no es necesario pero sí recomendable para las APIs RESTful, pues son sin estado (stateless). Cuando [[yii\web\User::enableSession|enableSession]]
es false, el estado de autenticación del usuario puede NO ser mantenido (persisted) durante varias peticiones usando sesiones. Si embargo, la autenticación puede ser realizada para cada petición, la cual es realizada por los pasos 2 y 3.

> Tip: Puede configurar [[yii\web\User::enableSession|enableSession]] del componente de la aplicación `user` en la configuración de las aplicaciones si estás desarrollando APIs RESTful en términos de un aplicación. Si desarrollas un módulo de las APIs RESTful, puedes poner la siguiente línea en el método del módulo `init()`, tal y como sigue:
> ```php
public function init()
{
    parent::init();
    \Yii::$app->user->enableSession = false;
}
```

Por ejemplo, para usar HTTP Basic Auth, puedes configurar `authenticator` como sigue,

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::className(),
    ];
    return $behaviors;
}
```

Si quires implementar las tres autenticaciones explicadas antes, puedes usar `CompositeAuth` de la siguiente manera,

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            HttpBasicAuth::className(),
            HttpBearerAuth::className(),
            QueryParamAuth::className(),
        ],
    ];
    return $behaviors;
}
```

Cada elemento en `authMethods` debe de ser el nombre de un método de autenticación de una clase o un array de configuración.


La implementación de `findIdentityByAccessToken()` es específico de la aplicación. Por ejemplo, en escenarios simples cuando cada usuario sólo puede terner una pieza (token) de acceso, puedes almacenar la pieza de acceso en la columna `access_token`  en la tabla de usuario. El método debe de ser inmediatamente implementado en la clase  `User` como sigue,

```php
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
}
```

Después que la autenticación es activada, tal y como se describe arriba, para cada petición de la API, la controladora solicitada puede intentar autenticar al usuario  en su paso  `beforeAction()`.

Si la autenticación ocurre, la controladora puede realizar otras comprobaciones (como son límite del ratio, autorización) y entonces ejecutar la acción. La identidad del usuario autenticado puede ser recogida via `Yii::$app->user->identity`.

Si la autenticación falla, una respuesta con estado HTTP 401 puede ser devuelta junto con otras cabeceras apropiadas (como son la cabecera para autenticación básica HTTP `WWW-Authenticate` ).


## Autorización <a name="authorization"></a>

Después de que un usuario se ha autenticado, probablementer querrás comprobar si él o ella tiene los permisos para realizar la acción pedida para el recurso pedido. Este proceso es llamado *autorización (authorization)* y está cubierto en detalle en la [Sección de Autorización](security-authorization.md).

Si tus controladoras extienden de [[yii\rest\ActiveController]], puedes sobreescribir (override) el método [[yii\rest\Controller::checkAccess()|checkAccess()]] para realizar la comprobación de la autorización. El método será llamado por las acciones contenidas en [[yii\rest\ActiveController]].
