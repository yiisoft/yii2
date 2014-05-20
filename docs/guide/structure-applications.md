Applications
============

Applications represent execution contexts within which requests are being processed.
The main task of applications is to analyze requests and dispatch them to appropriate
[controllers](structure-controllers.md) for further processing. For this reason, applications
are also called *front controllers*.

There are two main types of applications: [[yii\web\Application|Web applications]] and
[[yii\console\Application|console applications]]. As the names indicate, the former mainly handles
Web requests while the latter console command requests.

Applications are usually instantiated in [entry scripts](structure-entry-scripts.md) and can be globally accessed
via the expression `Yii::$app`.


## Configurations

When applications are being instantiated in [entry scripts](structure-entry-scripts.md), they
need to be configured to well describe the execution contexts. For example, the applications need
to know where to look for controller classes, where to store temporary files, etc. These information
are typically represented in terms of [configurations](concept-configurations.md) and stored in
one or multiple configuration files, called application configuration files.

The following code in an entry script shows how an application instance is created and configured using
the configuration loaded from a configuration file `web.php`:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/../config/web.php');

// instantiate and configure the application
(new yii\web\Application($config))->run();
```



## Application Components

Applications are [service locators](concept-service-locators.md). They host a set of the so-called
*application components* that provide different services for request processing. For example,
the `urlManager` component is responsible for routing Web requests to appropriate controllers;
the `db` component provides DB-related services; and so on.

Each application component has an ID that uniquely identifies itself among other application components
in the same application. You can access an application component through the expression `$app->ID`,
where `$app` refers to an application instance, and `ID` stands for the ID of an application component.
For example, you can use `Yii::$app->db` to get the [[yii\db\Connection|DB connection]], and `Yii::$app->cache`
to get the [[yii\caching\Cache|primary cache]] registered with the application.

Application components can be any objects. You can register them with an application to make them
globally accessible. This is usually done by configuring the [[yii\base\Application::components]] property in the
application configuration like the following:

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
]
```

The array keys are the IDs of the application components, and the array values are the
[configurations](concept-configurations.md) for the corresponding application components.


Yii predefines a set of core application components to provide features
common among Web applications. For example, the
[request|CWebApplication::request] component is used to collect
information about a user request and provide information such as the
requested URL and cookies.  By configuring the properties of these core
components, we can change the default behavior of nearly every aspect
of Yii.

Here is a list the core components that are pre-declared by [CWebApplication]:

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
manages the publishing of private asset files.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - manages role-based access control (RBAC).

   - [cache|CApplication::cache]: [CCache] - provides data caching
functionality. Note, you must specify the actual class (e.g.
[CMemCache], [CDbCache]). Otherwise, null will be returned when you
access this component.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
manages client scripts (javascript and CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
provides translated core messages used by the Yii framework.

   - [db|CApplication::db]: [CDbConnection] - provides the database
connection. Note, you must configure its
[connectionString|CDbConnection::connectionString] property in order
to use this component.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - handles
uncaught PHP errors and exceptions.

   - [format|CApplication::format]: [CFormatter] - formats data values
for display purpose.

   - [messages|CApplication::messages]: [CPhpMessageSource] - provides
translated messages used by the Yii application.

   - [request|CWebApplication::request]: [CHttpRequest] - provides
information related to user requests.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
provides security-related services, such as hashing and encryption.

   - [session|CWebApplication::session]: [CHttpSession] - provides
session-related functionality.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
provides the mechanism for persisting global state.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - provides
URL parsing and creation functionality.

   - [user|CWebApplication::user]: [CWebUser] - carries identity-related
information about the current user.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - manages themes.


## Application Lifecycle


When handling a user request, an application will undergo the following
life cycle:

   0. Pre-initialize the application with [CApplication::preinit()];

   1. Set up the error handling;

   2. Register core application components;

   3. Load application configuration;

   4. Initialize the application with [CApplication::init()]
       - Register application behaviors;
	   - Load static application components;

   5. Raise an [onBeginRequest|CApplication::onBeginRequest] event;

   6. Process the user request:
	   - Collect information about the request;
	   - Create a controller;
	   - Run the controller;

   7. Raise an [onEndRequest|CApplication::onEndRequest] event;

