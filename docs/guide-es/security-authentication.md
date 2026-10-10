Authentication
==============

La autenticación es el proceso de verificar la identidad de un usuario. Usualmente se usa un identificador (ej. un `username` o una dirección de correo electrónico) y una token secreto (ej. una contraseña o un token de acceso) para juzgar si el usuario es quien dice ser. La autenticación es la base de la función de inicio de sesión.

Yii proporciona un marco de autenticación que conecta varios componentes para soportar el inicio de sesión. Para utilizar este marco, usted necesita principalmente hacer el siguiente trabajo:

* Configurar el componente de la aplicación [[yii\web\User|user]];
* Crear una clase que implemente la interfaz [[yii\web\IdentityInterface]].

## Configurando [[yii\web\User]] <span id="configuring-user"></span>

El componente [[yii\web\User|user]] gestiona el estado de autenticación del usuario. Requiere que especifiques una [[yii\web\User::identityClass|clase de identidad]] la cual contiene la lógica de autenticación. En la siguiente configuración de la aplicación, la [[yii\web\User::identityClass|clase identity]] para [[yii\web\User|user]] está configurada para ser `app\models\User` cuya implementación se explica en la siguiente subsección:

```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```

## Implementando [[yii\web\IdentityInterface]] <span id="implementing-identity"></span>
La [[yii\web\User::identityClass|clase identity]] debe implementar la [[yii\web\IdentityInterface]] que contiene los siguientes métodos:
* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: busca una instancia de la clase identidad usando el ID de usuario especificado. Este método se utiliza cuando se necesita mantener el estado de inicio de sesión (login) a través de la sesión.

* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: busca una instancia de la clase de identidad usando el token de acceso especificado. Este método se utiliza cuando se necesita autenticar a un usuario mediante un único token secreto (ej. en una aplicación RESTful sin estado).
* [[yii\web\IdentityInterface::getId()|getId()]]: devuelve el ID del usuario representado por esta instancia de identidad.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: devuelve una clave utilizada para validar la sesión y el auto-login en caso de que esté habilitado.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: implementa la lógica para verificar la clave de autenticación.

Si no se necesita un método en particular, se podría implementar con un cuerpo vacío, Por ejemplo, Si un método en particular no es necesario, puedes implementarlo con un cuerpo vacío. Por ejemplo, si tu aplicación es una aplicación RESTful pura sin estado, sólo necesitarás implementar [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]] y [[yii\web\IdentityInterface::getId()|getId()]] dejando el resto de métodos con un cuerpo vacío. O si tu aplicación utiliza autenticación sólo de sesión, necesitarías implementar todos los métodos excepto findIdentityByAccessToken().

En el siguiente ejemplo, una clase [[yii\web\User::identityClass|identity]] es implementada como una clase [Active Record](db-active-record.md) asociada con la tabla de base de datos `user`.

```php
<?php

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    /**
     * Buscar una identidad por el ID dado.
     *
     * @param string|int $id ID que debe buscarse
     * @return IdentityInterface|null objeto de identidad que coincide con el ID dado.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Buscar una identidad por el token dado..
     *
     * @param string $token token que debe buscarse
     * @return IdentityInterface|null objeto de identidad que coincide con el token dado.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string ID del usuario actual
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null llave de autenticación del usuario actual
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool|null si la llave de autenticación es válida para el usuario actual
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

Puede utilizar el siguiente código para generar una clave de autenticación para cada usuario y almacenarla en la tabla `user`:

```php
class User extends ActiveRecord implements IdentityInterface
{
    ......

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
```

> Nota: No confundas la clase de identidad `User` con [[yii\web\User]]. La primera es la clase que implementa la lógica de autenticación. Suele implementarse como una clase [Active Record](db-active-record.md) asociada a algún almacenamiento persistente para guardar la información de las credenciales del usuario. Esta última es una clase de componente de aplicación responsable de gestionar el estado de autenticación del usuario.

## Usando [[yii\web\User]] <span id="using-user"></span>
Principalmente se usa [[yii\web\User]] en términos del componente de aplicación `user`.

Puede detectar la identidad del usuario actual usando la expresión `Yii::$app->user->identity`. Devuelve una instancia de la clase [[yii\web\User::identityClass|identity]] que representa al usuario actualmente conectado, o `null` si el usuario actual no está autenticado (es decir, es un invitado). El siguiente código muestra como recuperar otra información relacionada con la autenticación desde [[yii\web\User]]:

```php
// El usuario actual identificado. `null` si el usuario no esta autenticado.
$identity = Yii::$app->user->identity;

// El ID del usuario actual. `null` si el usuario no esta autenticado.
$id = Yii::$app->user->id;

// si el usuario actual es un invitado (No autenticado)
$isGuest = Yii::$app->user->isGuest;
```

Para acceder a un usuario, puede utilizar el siguiente código:

```php
// encontrar una identidad de usuario con el nombre de usuario especificado.
// tenga en cuenta que es posible que desee comprobar la contraseña si es necesario
$identity = User::findOne(['username' => $username]);

// inicia la sesión del usuario.
Yii::$app->user->login($identity);
```

El método [[yii\web\User::login()]] establece la identidad del usuario actual a [[yii\web\User]]. Si la sesión es [[yii\web\User::enableSession|habilitada]], mantendrá la identidad en la sesión para que el estado de autenticación del usuario se mantenga durante toda la sesión. Si el login basado en cookies (es decir, inicio de sesión "recordarme") está [[yii\web\User::enableAutoLogin|habilitado]], también guardará la identidad en una cookie para que el estado de autenticación del usuario pueda ser recuperado desde la cookie mientras la cookie permanezca válida.

Para habilitar el login basado en cookies, necesita configurar [[yii\web\User::enableAutoLogin]] como `true` en la configuración de la aplicación. También necesita proporcionar un parámetro de tiempo de duración cuando llame al método [[yii\web\User::login()]].

Para cerrar la sesión de un usuario, basta con llamar a:

```php
Yii::$app->user->logout();
```

Tenga en cuenta que cerrar la sesión de un usuario sólo tiene sentido cuando la sesión está activada. El método limpiará el estado de autenticación del usuario tanto de la memoria como de la sesión. Y por defecto, también destruirá *todos* los datos de sesión del usuario. Si desea mantener los datos de sesión, debe llamar a `Yii::$app->user->logout(false)`, en su lugar.

## Eventos de Autenticación <span id="auth-events"></span>
La clase [[yii\web\User]] genera algunos eventos durante los procesos de inicio y cierre de sesión.
* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: levantado al comienzo de [[yii\web\User::login()]]. Si el manejador del evento establece la propiedad [[yii\web\UserEvent::isValid|isValid]] del objeto evento a `false`, el proceso de inicio de sesión será cancelado.
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: se produce después de un inicio de sesión exitoso.
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: levantado al comienzo de [[yii\web\User::logout()]]. Si el manejador del evento establece la propiedad [[yii\web\UserEvent::isValid|isValid]] del objeto evento a `false`, el proceso de cierre de sesión será cancelado.
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: se produce después de un cierre de sesión exitoso.
Usted puede responder a estos eventos para implementar características como auditoria de inicio de sesión, estadísticas de usuarios en línea. Por ejemplo, en el manejador para [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]], puede registrar la hora de inicio de sesión y la dirección IP en la tabla `user`.
