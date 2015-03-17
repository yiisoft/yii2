Componentes de la Aplicación
============================

Las aplicaciones son [service locators](concept-service-locators.md) (localizadores de servicios). Ellas albergan
un grupo de los llamados *componentes de aplicación* que proveen diferentes servicios para procesar el `request` (petición).
Por ejemplo, el componente `urlManager` es responsable por rutear Web `requests` (peticiones) a los controladores apropiados;
el componente `db` provee servicios relacionados a base de datos; y así sucesivamente.

Cada componente de la aplicación tiene un ID que lo identifica de forma inequívoca de otros en la misma aplicación.
Puedes acceder a un componente de la aplicación con la siguiente expresión:

```php
\Yii::$app->ComponentID
```

Por ejemplo, puedes utilizar `\Yii::$app->db` para obtener la [[yii\db\Connection|conexión a la base de datos]],
y `\Yii::$app->cache` para obtener el [[yii\caching\Cache|cache primario]] registrado con la aplicación.

Estos componentes pueden ser cualquier objeto. Puedes registrarlos configurando la propiedad [[yii\base\Application::components]]
en las [configuraciones de la aplicación](structure-applications.md#application-configurations).
Por ejemplo:

```php
[
    'components' => [
        // registra el componente "cache" utilizando el nombre de clase
        'cache' => 'yii\caching\ApcCache',

        // registra el componente "db" utilizando un array de configuración
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // registra el componente "search" utilizando una función anónima
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Información: A pesar de que puedes registrar tantos componentes como desees, deberías hacerlo con criterio.
  Los componente de la aplicación son como variables globales. Abusando demasiado de ellos puede resultar en
  un código más difícil de mantener y testear. En muchos casos, puedes simplemente crear un componente local
  y utilizarlo únicamente cuando sea necesario.


## Componentes del Núcleo de la Aplicación <span id="core-application-components"></span>

Yii define un grupo de componentes del *núcleo* con IDs fijos y configuraciones por defecto. Por ejemplo,
el componente [[yii\web\Application::request|request]] es utilizado para recolectar información acerca
del `request` del usuario y resolverlo en una [ruta](runtime-routing.md); el componente [[yii\base\Application::db|db]]
representa una conexión a la base de datos a través del cual realizar consultas a la misma.
Es con ayuda de estos componentes del núcleo que Yii puede manejar los `request` del usuario.

A continuación, hay una lista de componentes predefinidos en el núcleo. Puedes configurarlos y personalizarlos
como lo haces con componentes normales de la aplicación. Cuando configuras un componente del núcleo,
si no especificas su nombre de clase, la clase por defecto será utilizada.

* [[yii\web\AssetManager|assetManager]]: maneja los `assets bundles` y su publicación.
  Consulta la sección [Menajando Assets](output-assets.md) para más detalles.
* [[yii\db\Connection|db]]: representa una conexión a la base de datos a través de la cual puedes realizar consultas a la misma.
  Ten en cuenta que cuando configuras este componente, debes especificar el nombre de clase así como otras
  propiedades requeridas por el mismo, como [[yii\db\Connection::dsn]].
  Por favor consulta la sección [Data Access Objects](db-dao.md) para más detalles.
* [[yii\base\Application::errorHandler|errorHandler]]: maneja errores y excepciones de PHP.
  Por favor consulta la sección [Handling Errors](tutorial-handling-errors.md) para más detalles.
* [[yii\base\Formatter|formatter]]: da formato a los datos cuando son mostrados a los usuarios. Por ejemplo, un número
  puede ser mostrado usando un separador de miles, una fecha en una forma extensa.
  Por favor consulta la sección [Formato de Datos](output-formatting.md) para más detalles.
* [[yii\i18n\I18N|i18n]]: soporta traducción y formato de mensajes.
  Por favor consulta la sección [Internacionalización](tutorial-i18n.md) para más detalles.
* [[yii\log\Dispatcher|log]]: maneja a dónde dirigir los logs.
  Por favor consulta la sección [Logging](tutorial-logging.md) para más detalles.
* [[yii\swiftmailer\Mailer|mail]]: soporta construcción y envío de emails.
  Por favor consulta la sección [Enviando Emails](tutorial-mailing.md) para más detalles.
* [[yii\base\Application::response|response]]: representa la respuesta enviada a los usuarios.
  Por favor consulta la sección [Responses](runtime-responses.md) para más detalles.
* [[yii\base\Application::request|request]]: representa el `request` recibido de los usuarios.
  Por favor consulta la sección [Requests](runtime-requests.md) para más detalles.
* [[yii\web\Session|session]]: representa la información de sesión. Este componente sólo está disponible
  en [[yii\web\Application|alpicaciones Web]].
  Por favor consulta la sección [Sessions and Cookies](runtime-sessions-cookies.md) para más detalles.
* [[yii\web\UrlManager|urlManager]]: soporta el parseo y generación de URLs.
  Por favor consulta la sección [URL Parsing and Generation](runtime-url-handling.md) para más detalles.
* [[yii\web\User|user]]: representa la información e autenticación del usuario. Este componente sólo está disponible
  en [[yii\web\Application|aplicaciones Web]]
  Por favor consulta la sección [Autenticación](security-authentication.md) para más detalles.
* [[yii\web\View|view]]: soporta el renderizado de las vistas.
  Por favor consulta la sección [Vistas](structure-views.md) para más detalles.
