Sesiones (Sessions) y Cookies
=============================

Las sesiones y las cookies permiten la persistencia de datos a través de múltiples peticiones de usuario. En PHP plano, debes acceder a ellos a través de las variables globales `$_SESSION` y `$_COOKIE`, respectivamente. Yii encapsula las sesiones y las cookies como objetos y por lo tanto te permite acceder a ellos de manera orientada a objetos con estupendas mejoras adicionales.


## Sesiones <span id="sessions"></span>

Como las [peticiones](runtime-requests.md) y las [respuestas](runtime-responses.md), puedes acceder a las sesiones vía el [componente de la aplicación](structure-application-components.md) `session`  el cual es una instancia de [[yii\web\Session]], por defecto.


### Abriendo y cerrando sesiones <span id="opening-closing-sessions"></span>

Para abrir y cerrar una sesión, puedes hacer lo siguiente:

```php
$session = Yii::$app->session;

// comprueba si una sesión está ya abierta
if ($session->isActive) ...

// abre una sesión
$session->open();

// cierra una sesión
$session->close();

// destruye todos los datos registrados por la sesión.
$session->destroy();
```

Puedes llamar a [[yii\web\Session::open()|open()]] y [[yii\web\Session::close()|close()]] múltiples veces sin causar errores. Esto ocurre porque internamente los métodos verificarán primero si la sesión está ya abierta.


### Accediendo a los datos de sesión <span id="access-session-data"></span>

Para acceder a los datos almacenados en sesión, puedes hacer lo siguiente:

```php
$session = Yii::$app->session;

// devuelve una variable de sesión. Los siguientes usos son equivalentes:
$language = $session->get('language');
$language = $session['language'];
$language = isset($_SESSION['language']) ? $_SESSION['language'] : null;

// inicializa una variable de sesión. Los siguientes usos son equivalentes:
$session->set('language', 'en-US');
$session['language'] = 'en-US';
$_SESSION['language'] = 'en-US';

// remueve la variable de sesión. Los siguientes usos son equivalentes:
$session->remove('language');
unset($session['language']);
unset($_SESSION['language']);

// comprueba si una variable de sesión existe. Los siguientes usos son equivalentes:
if ($session->has('language')) ...
if (isset($session['language'])) ...
if (isset($_SESSION['language'])) ...

// recorre todas las variables de sesión. Los siguientes usos son equivalentes:
foreach ($session as $name => $value) ...
foreach ($_SESSION as $name => $value) ...
```

> Info: Cuando accedas a los datos de sesión a través del componente `session`, una sesión será automáticamente abierta si no lo estaba antes. Esto es diferente accediendo a los datos de sesión a través de `$_SESSION`, el cual requiere llamar explícitamente a `session_start()`.

Cuando trabajas con datos de sesiones que son arrays, el componte `session` tiene una limitación que te previene directamente de modificar un elemento del array. Por ejemplo,

```php
$session = Yii::$app->session;

// el siguiente código no funciona
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// el siguiente código funciona:
$session['captcha'] = [
    'number' => 5,
    'lifetime' => 3600,
];

// el siguiente código también funciona:
echo $session['captcha']['lifetime'];
```

Puedes usar las siguientes soluciones para arreglar este problema:

```php
$session = Yii::$app->session;

// directamente usando $_SESSION (asegura te de que Yii::$app->session->open() ha sido llamado)
$_SESSION['captcha']['number'] = 5;
$_SESSION['captcha']['lifetime'] = 3600;

// devuelve el valor del array, lo modifica y a continuación lo guarda
$captcha = $session['captcha'];
$captcha['number'] = 5;
$captcha['lifetime'] = 3600;
$session['captcha'] = $captcha;

// usa un ArrayObject en vez de un array
$session['captcha'] = new \ArrayObject;
...
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// almacena los datos en un array con un prefijo común para las claves
$session['captcha.number'] = 5;
$session['captcha.lifetime'] = 3600;
```

Para un mejor rendimiento y legibilidad del código, recomendamos la última solución. Es decir, en vez de almacenar un array como una única variable de sesión, almacena cada elemento del array como una variable de sesión que comparta el mismo prefijo clave con otros elementos del array.


### Personalizar el almacenamiento de sesión <span id="custom-session-storage"></span>

Por defecto la clase [[yii\web\Session]] almacena los datos de sesión como ficheros en el servidor. Yii también provee de las siguientes clases de sesión que implementan diferentes almacenamientos de sesión:

* [[yii\web\DbSession]]: almacena los datos de sesión en una tabla en la base de datos.
* [[yii\web\CacheSession]]: almacena los datos de sesión en una caché con la ayuda de la configuración del [componente caché](caching-data.md#cache-components).
* [[yii\redis\Session]]: almacena los datos de sesión usando [redis](http://redis.io/) como medio de almacenamiento.
* [[yii\mongodb\Session]]: almacena los datos de sesión en [MongoDB](http://www.mongodb.org/).

Todas estas clases de sesión soportan los mismos métodos de la API. Como consecuencia, puedes cambiar el uso de diferentes almacenamientos de sesión sin la necesidad de modificar el código de tu aplicación que usa sesiones.

> Note: si quieres acceder a los datos de sesión vía `$_SESSION` mientras estás usando un almacenamiento de sesión personalizado, debes asegurar te que la sesión está ya empezada por [[yii\web\Session::open()]]. Esto ocurre porque los manipuladores de almacenamiento de sesión personalizado son registrados sin este método.

Para aprender como configurar y usar estas clases de componentes, por favor consulte la documentación de la API. Abajo está un ejemplo que muestra como configurar [[yii\web\DbSession]] en la configuración de la aplicación para usar una tabla en la base de datos como almacenamiento de sesión:

```php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // el identificador del componente de aplicación DB connection. Por defecto'db'.
            // 'sessionTable' => 'my_session', // nombre de la tabla de sesión. Por defecto 'session'.
        ],
    ],
];
```

También es necesario crear la siguiente tabla de la base de datos para almacenar los datos de sesión:

```sql
CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

donde 'BLOB' se refiere al BLOB-type de tu DBMS preferida. Abajo está el tipo BLOB que puedes usar para algunos DBMS populares:

- MySQL: LONGBLOB
- PostgreSQL: BYTEA
- MSSQL: BLOB

> Note: De acuerdo con la configuración de php.ini `session.hash_function`, puedes necesitar ajustar el tamaño de la columna `id`. Por ejemplo, si `session.hash_function=sha256`, deberías usar el tamaño 64 en vez de 40.


### Flash Data <span id="flash-data"></span>

Flash data es una clase especial de datos de sesión que, una vez se inicialice en la primera petición, estará sólo disponible durante la siguiente petición y automáticamente se borrará después. Flash data es comúnmente usado para implementar mensajes que deberían ser mostrados una vez a usuarios finales, tal como mostrar un mensaje de confirmación después de que un usuario envíe un formulario con éxito.

Puedes inicializar y acceder a flash data a través del componente de aplicación `session`. Por ejemplo,

```php
$session = Yii::$app->session;

// Petición #1
// inicializa el mensaje flash nombrado como "postDeleted"
$session->setFlash('postDeleted', 'You have successfully deleted your post.');

// Petición #2
// muestra el mensaje flash nombrado "postDeleted"
echo $session->getFlash('postDeleted');

// Petición #3
// $result será `false` ya que el mensaje flash ha sido borrado automáticamente
$result = $session->hasFlash('postDeleted');
```

Al igual que los datos de sesión regulares, puede almacenar datos arbitrarios como flash data.

Cuando llamas a [[yii\web\Session::setFlash()]], sobrescribirá cualquier Flash data que tenga el mismo nombre.
Para añadir un nuevo flash data a el/los existes con el mismo nombre, puedes llamar a [[yii\web\Session::addFlash()]].
Por ejemplo:

```php
$session = Yii::$app->session;

// Petición #1
// añade un pequeño mensaje flash bajo el nombre de "alerts"
$session->addFlash('alerts', 'You have successfully deleted your post.');
$session->addFlash('alerts', 'You have successfully added a new friend.');
$session->addFlash('alerts', 'You are promoted.');

// Petición #2
// $alerts es un array de mensajes flash bajo el nombre de "alerts"
$alerts = $session->getFlash('alerts');
```

> Note: Intenta no usar a la vez [[yii\web\Session::setFlash()]] con [[yii\web\Session::addFlash()]] para flash data
  del mismo nombre. Esto ocurre porque el último método elimina el flash data dentro del array así que puedes añadir un nuevo flash data con el mismo nombre. Como resultado, cuando llamas a [[yii\web\Session::getFlash()]], puedes encontrarte algunas veces que te está devolviendo un array mientras que otras veces te está devolviendo un string, esto depende del orden que invoques a estos dos métodos.


## Cookies <span id="cookies"></span>

Yii representa cada cookie como un objeto de [[yii\web\Cookie]]. Tanto [[yii\web\Request]] como [[yii\web\Response]]
mantienen una colección de cookies vía la propiedad de llamada `cookies`. La colección de cookie en la antigua representación son enviadas en una petición, mientras la colección de cookie en esta última representa las cookies que van a ser enviadas al usuario.


### Leyendo Cookies <span id="reading-cookies"></span>

Puedes recuperar las cookies en la petición actual usando el siguiente código:

```php
// devuelve la colección de cookie (yii\web\CookieCollection) del componente "request"
$cookies = Yii::$app->request->cookies;

// devuelve el valor "language" de la cookie. Si la cookie no existe, retorna "en" como valor por defecto.
$language = $cookies->getValue('language', 'en');

// una manera alternativa de devolver el valor "language" de la cookie
if (($cookie = $cookies->get('language')) !== null) {
    $language = $cookie->value;
}

// puedes también usar $cookies como un array
if (isset($cookies['language'])) {
    $language = $cookies['language']->value;
}

// comprueba si hay una cookie con el valor "language"
if ($cookies->has('language')) ...
if (isset($cookies['language'])) ...
```


### Enviando Cookies <span id="sending-cookies"></span>

Puedes enviar cookies a usuarios finales usando el siguiente código:

```php
// devuelve la colección de cookie (yii\web\CookieCollection) del componente "response"
$cookies = Yii::$app->response->cookies;

// añade una nueva cookie a la respuesta que se enviará
$cookies->add(new \yii\web\Cookie([
    'name' => 'language',
    'value' => 'zh-CN',
]));

// remueve una cookie
$cookies->remove('language');
// equivalente a lo siguiente
unset($cookies['language']);
```

Además de [[yii\web\Cookie::name|name]], [[yii\web\Cookie::value|value]] las propiedades que se muestran en los anteriores ejemplos, la clase [[yii\web\Cookie]] también define otras propiedades para representar toda la información posible de las cookies, tal como [[yii\web\Cookie::domain|domain]], [[yii\web\Cookie::expire|expire]]. Puedes configurar estas propiedades según sea necesario para preparar una cookie y luego añadirlo a la colección de cookies de la respuesta.

> Note: Para mayor seguridad, el valor por defecto de [[yii\web\Cookie::httpOnly]] es `true`. Esto ayuda a mitigar el riesgo del acceso a la cookie protegida por script desde el lado del cliente (si el navegador lo soporta). Puedes leer el [httpOnly wiki article](https://www.owasp.org/index.php/HttpOnly) para más detalles.


### Validación de la Cookie <span id="cookie-validation"></span>

Cuando estás leyendo y enviando cookies a través de los componentes `request` y `response` como mostramos en las dos últimas subsecciones, cuentas con el añadido de seguridad de la validación de cookies el cual protege las cookies de ser modificadas en el lado del cliente. Esto se consigue con la firma de cada cookie con una cadena hash, el cual permite a la aplicación saber si una cookie ha sido modificada en el lado del cliente o no. Si es así, la cookie no será accesible a través de [[yii\web\Request::cookies|cookie collection]] del componente `request`.

> Info: Si falla la validación de una cookie, aún puedes acceder a la misma a través de `$_COOKIE`. Esto sucede porque librerías de terceros pueden manipular de forma propia las cookies, lo cual no implica la validación de las mismas.

La validación de cookies es habilitada por defecto. Puedes desactivar lo ajustando la propiedad [[yii\web\Request::enableCookieValidation]] a `false`, aunque se recomienda encarecidamente que no lo haga.

> Note: Las cookies que son directamente leídas/enviadas vía `$_COOKIE` y `setcookie()` no serán validadas.

Cuando estás usando la validación de cookie, puedes especificar una [[yii\web\Request::cookieValidationKey]] el cual se usará para generar los strings hash mencionados anteriormente. Puedes hacerlo mediante la configuración del componente `request` en la configuración de la aplicación:

```php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'fill in a secret key here',
        ],
    ],
];
```

> Info: [[yii\web\Request::cookieValidationKey|cookieValidationKey]] es crítico para la seguridad de tu aplicación.
  Sólo debería ser conocido por personas de confianza. No lo guardes en sistemas de control de versiones.
