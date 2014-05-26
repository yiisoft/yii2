Applications
============

Applications are objects that govern the overall structure and lifecycle of Yii application systems.
Each Yii application system contains a single application object which is created in
the [entry script](structure-entry-scripts.md) and is globally accessible through the expression `\Yii::$app`.

> Info: Depending on the context, when we say "an application", it can mean either an application
  object or an application system.

There are two types of applications: [[yii\web\Application|Web applications]] and
[[yii\console\Application|console applications]]. As the names indicate, the former mainly handles
Web requests while the latter console command requests.


## Application Configurations

When an [entry script](structure-entry-scripts.md) creates an application, it will load
a [configuration](concept-configurations.md) and apply it to the application, like the following:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// load application configuration
$config = require(__DIR__ . '/../config/web.php');

// instantiate and configure the application
(new yii\web\Application($config))->run();
```

Like normal [configurations](concept-configurations.md), application configurations specify how
to initialize properties of application objects. Because application configurations are often
very complex, they usually are kept in [configuration files](concept-configurations.md#configuration-files),
like the `web.php` file in the above example.


## Application Properties

There are many important application properties that you should configure in application configurations.
These properties typically describe the environment that applications are running in.
For example, applications need to know how to load [controllers](structure-controllers.md),
where to store temporary files, etc. In the following, we will summarize these properties.


### Required Properties

In any application, you should at least configure two properties: [[yii\base\Application::id|id]]
and [[yii\base\Application::basePath|basePath]].

####
The [[yii\base\Application::id|id]] property specifies a unique ID that differentiates an application
from others. It is mainly used programmatically. Although not a requirement, for best interoperability
it is recommended that you use alphanumeric characters only when specifying an application ID.

The [[yii\base\Application::basePath|basePath]] property specifies the root directory of an application.
It is the directory that contains all protected source code of an application system. Under this directory,
you normally will see sub-directories such as `models`, `views`, `controllers`, which contain source code
corresponding to the MVC pattern.

You may configure the [[yii\base\Application::basePath|basePath]] property using a directory path
or a [path alias](concept-aliases.md). In both forms, the corresponding directory must exist, or an exception
will be thrown. The path will be normalized by calling the `realpath()` function.

The [[yii\base\Application::basePath|basePath]] property is often used to derive other important
paths (e.g. the runtime path). For this reason, a path alias named `@app` is predefined to represent this
path. Derived paths may then be formed using this alias (e.g. `@app/runtime` to refer to the runtime directory).


### Important Properties

You usually will configure the properties as their values differ across different applications.


#### [[yii\base\Application::aliases|aliases]]

This property allows you to define a set of [aliases](concept-aliases.md) in terms of an array.
The array keys are alias names, and the array values are the corresponding path definitions.
For example,

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

This property is provided such that you can define aliases in terms of application configurations instead of
the method calls [[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]]

This is a very useful property. It allows you to specify an array of components that should
be run during the application [[yii\base\Application::bootstrap()|bootstrapping process]].
For example, if you want a [module](structure-modules.md) to customize the [URL rules](runtime-url-handling.md),
you may list its ID as an element in this property.

Each component listed in this property may be specified in one of the following formats:

- an application component ID as specified via [components](#components).
- a module ID as specified via [modules](#modules).
- a class name.
- a configuration array.

For example,

```php
[
    'bootstrap' => [
        // an application component ID or module ID
        'demo',

        // a class name
        'app\components\TrafficMonitor',

        // a configuration array
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ]
    ],
]
```

During the bootstrapping process, each component will be instantiated. If the component class
implements [[yii\base\BootstrapInterface]], its [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method
will be also be called.


#### [[yii\base\Application::components|components]]

#### [[yii\base\Application::controllerMap|controllerMap]]

#### [[yii\base\Application::controllerNamespace|controllerNamespace]]

#### [[yii\base\Application::language|language]]

#### [[yii\base\Application::modules|modules]]

#### [[yii\base\Application::name|name]]

#### [[yii\base\Application::params|params]]

#### [[yii\base\Application::version|version]]


### Useful Properties

#### [[yii\base\Application::layoutPath|layoutPath]]

#### [[yii\base\Application::runtimePath|runtimePath]]

#### [[yii\base\Application::viewPath|viewPath]]

#### [[yii\base\Application::vendorPath|vendorPath]]

#### [[yii\base\Application::timeZone|timeZone]]

#### [[yii\base\Application::layout|layout]]

#### [[yii\base\Application::defaultRoute|defaultRoute]]

#### [[yii\base\Application::charset|charset]]

#### [[yii\base\Application::sourceLanguage|sourceLanguage]]

#### [[yii\base\Application::extensions|extensions]]

// WEB
    public $catchAll;

// Console
    public $enableCoreCommands = true;


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

