Autenticación
=============

A diferencia de las aplicaciones Web, las API RESTful son usualmente sin estado (stateless), lo que permite que las sesiones o las cookies
no sean usadas. Por lo tanto, cada petición debe llevar alguna suerte de credenciales de autenticación,
porque la autenticación del usuario no puede ser mantenida por las sesiones o las cookies. Una práctica común
es enviar una pieza (token) secreta de acceso con cada petición para autenticar al usuario. Dado que una pieza de autenticación
puede ser usada para identificar y autenticar solamente a un usuario, **la API de peticiones tiene que ser siempre enviado
vía HTTPS para prevenir ataques tipo "man-in-the-middle" (MitM) **.

Hay muchas maneras de enviar una token (pieza) de acceso:

* [Autenticación Básica HTTP](https://es.wikipedia.org/wiki/Autenticaci%C3%B3n_de_acceso_b%C3%A1sica): la pieza de acceso
  es enviada como nombre de usuario. Esto sólo debe de ser usado cuando la pieza de acceso puede ser guardada
  de forma segura en la parte del API del consumidor. Por ejemplo, el API del consumidor es un programa ejecutándose en un servidor.
* Parámetro de la consulta: la pieza de acceso es enviada como un parámetro de la consulta en la URL de la API, p.e.,
  `https://example.com/users?access-token=xxxxxxxx`. Debido que muchos servidores dejan los parámetros de consulta en los logs del servidor,
  esta aproximación suele ser usada principalmente para servir peticiones `JSONP`
  que no usen las cabeceras HTTP para enviar piezas de acceso.
* [OAuth 2](https://oauth.net/2/): la pieza de acceso es obtenida por el consumidor por medio de una autorización del servidor
  y enviada al API del servidor según el protocolo
  OAuth 2 [tokens HTTP del portador](https://datatracker.ietf.org/doc/html/rfc6750).

Yii soporta todos los métodos anteriores de autenticación. Puedes crear nuevos métodos de autenticación de una forma fácil.

Para activar la autenticación para tus APIs, sigue los pasos siguientes:

1. Configura el componente `user` de la aplicación:
   - Define la propiedad [[yii\web\User::enableSession|enableSession]] como `false`.
   - Define la propiedad [[yii\web\User::loginUrl|loginUrl]] como `null` para mostrar un error HTTP 403 en vez de redireccionar a la pantalla de login. 
2. Especifica cuál método de autenticación planeas usar configurando el comportamiento (behavior) `authenticator` en tus
   clases de controladores REST.
3. Implementa [[yii\web\IdentityInterface::findIdentityByAccessToken()]] en tu [[yii\web\User::identityClass|clase de identidad de usuarios]].

El paso 1 no es necesario pero sí recomendable para las APIs RESTful, pues son sin estado (stateless).
Cuando [[yii\web\User::enableSession|enableSession]] es `false`, el estado de autenticación del usuario puede NO persistir entre peticiones usando sesiones.
Si embargo, la autenticación será realizada para cada petición, lo que se consigue en los pasos 2 y 3.

> Tip:Puedes configurar [[yii\web\User::enableSession|enableSession]] del componente de la aplicación `user` en la configuración
> de las aplicaciones si estás desarrollando APIs RESTful en términos de un aplicación. Si desarrollas un módulo de las APIs RESTful,
> puedes poner la siguiente línea en el método del módulo `init()`, tal y como sigue:
>
> ```php
> public function init()
> {
>     parent::init();
>     \Yii::$app->user->enableSession = false;
> }
> ```

Por ejemplo, para usar HTTP Basic Auth, puedes configurar el comportamiento (behavior) `authenticator` como sigue,

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::class,
    ];
    return $behaviors;
}
```

Si quieres implementar los tres métodos de autenticación explicados antes, puedes usar `CompositeAuth` de la siguiente manera,

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => CompositeAuth::class,
        'authMethods' => [
            HttpBasicAuth::class,
            HttpBearerAuth::class,
            QueryParamAuth::class,
        ],
    ];
    return $behaviors;
}
```

Cada elemento en `authMethods` debe de ser el nombre de una clase de método de autenticación o un array de configuración.


La implementación de `findIdentityByAccessToken()` es específico de la aplicación. Por ejemplo, en escenarios simples
cuando cada usuario sólo puede tener un token de acceso, puedes almacenar este token en la columna `access_token`
en la tabla de usuario. El método debe de ser inmediatamente implementado en la clase  `User` como sigue,

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

Después que la autenticación es activada, tal y como se describe arriba, para cada petición de la API, el controlador solicitado
puede intentar autenticar al usuario en su evento `beforeAction()`.

Si la autenticación tiene éxito, el controlador realizará otras comprobaciones (como son límite del ratio, autorización)
y entonces ejecutar la acción. La identidad del usuario autenticado puede ser recuperada via `Yii::$app->user->identity`.

Si la autenticación falla, una respuesta con estado HTTP 401 será devuelta junto con otras cabeceras apropiadas
(tal como la cabecera para autenticación básica HTTP `WWW-Authenticate`).


## Autorización <span id="authorization"></span>

Después de que un usuario se ha autenticado, probablementer querrás comprobar si él o ella tiene los permisos para realizar
la acción solicitada. Este proceso es llamado *autorización (authorization)* y está cubierto en detalle
en la [Sección de Autorización](security-authorization.md).

Si tus controladores extienden de [[yii\rest\ActiveController]], puedes sobreescribir
el método [[yii\rest\ActiveController::checkAccess()|checkAccess()]] para realizar la comprobación de la autorización.
El método será llamado por las acciones contenidas en [[yii\rest\ActiveController]].
