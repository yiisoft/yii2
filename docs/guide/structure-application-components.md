Application Components
======================

Applications are [service locators](concept-service-locator.md). They host a set of the so-called
*application components* that provide different services for processing requests. For example,
the `urlManager` component is responsible for routing Web requests to appropriate controllers;
the `db` component provides DB-related services; and so on.

Each application component has an ID that uniquely identifies itself among other application components
in the same application. You can access an application component through the expression:

```php
\Yii::$app->componentID
```

For example, you can use `\Yii::$app->db` to get the [[yii\db\Connection|DB connection]],
and `\Yii::$app->cache` to get the [[yii\caching\Cache|primary cache]] registered with the application.

An application component is created the first time it is accessed through the above expression. Any
further accesses will return the same component instance.

Application components can be any objects. You can register them by configuring
the [[yii\base\Application::components]] property in [application configurations](structure-applications.md#application-configurations).
For example,

```php
[
    'components' => [
        // register "cache" component using a class name
        'cache' => 'yii\caching\ApcCache',

        // register "db" component using a configuration array
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // register "search" component using an anonymous function
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Info: While you can register as many application components as you want, you should do this judiciously.
  Application components are like global variables. Using too many application components can potentially
  make your code harder to test and maintain. In many cases, you can simply create a local component
  and use it when needed.


## Bootstrapping Components <span id="bootstrapping-components"></span>

As mentioned above, an application component will only be instantiated when it is being accessed the first time.
If it is not accessed at all during a request, it will not be instantiated. Sometimes, however, you may want
to instantiate an application component for every request, even if it is not explicitly accessed.
To do so, you may list its ID in the [[yii\base\Application::bootstrap|bootstrap]] property of the application.

You can also use Closures to bootstrap customized components. Returning a instantiated component is not 
required. A Closure can also be used simply for running code after [[yii\base\Application]] instantiation.

For example, the following application configuration makes sure the `log` component is always loaded:

```php
[
    'bootstrap' => [
        'log',
        function($app){
            return new ComponentX();
        },
        function($app){
            // some code
           return;
        }
    ],
    'components' => [
        'log' => [
            // configuration for "log" component
        ],
    ],
]
```


## Core Application Components <span id="core-application-components"></span>

Yii defines a set of *core* application components with fixed IDs and default configurations. For example,
the [[yii\web\Application::request|request]] component is used to collect information about
a user request and resolve it into a [route](runtime-routing.md); the [[yii\base\Application::db|db]]
component represents a database connection through which you can perform database queries.
It is with help of these core application components that Yii applications are able to handle user requests.

Below is the list of the predefined core application components. You may configure and customize them
like you do with normal application components. When you are configuring a core application component,
if you do not specify its class, the default one will be used.

* [[yii\web\AssetManager|assetManager]]: manages asset bundles and asset publishing.
  Please refer to the [Managing Assets](structure-assets.md) section for more details.
* [[yii\db\Connection|db]]: represents a database connection through which you can perform DB queries.
  Note that when you configure this component, you must specify the component class as well as other required
  component properties, such as [[yii\db\Connection::dsn]].
  Please refer to the [Data Access Objects](db-dao.md) section for more details.
* [[yii\base\Application::errorHandler|errorHandler]]: handles PHP errors and exceptions.
  Please refer to the [Handling Errors](runtime-handling-errors.md) section for more details.
* [[yii\i18n\Formatter|formatter]]: formats data when they are displayed to end users. For example, a number
  may be displayed with thousand separator, a date may be formatted in long format.
  Please refer to the [Data Formatting](output-formatting.md) section for more details.
* [[yii\i18n\I18N|i18n]]: supports message translation and formatting.
  Please refer to the [Internationalization](tutorial-i18n.md) section for more details.
* [[yii\log\Dispatcher|log]]: manages log targets.
  Please refer to the [Logging](runtime-logging.md) section for more details.
* [[yii\swiftmailer\Mailer|mailer]]: supports mail composing and sending.
  Please refer to the [Mailing](tutorial-mailing.md) section for more details.
* [[yii\base\Application::response|response]]: represents the response being sent to end users.
  Please refer to the [Responses](runtime-responses.md) section for more details.
* [[yii\base\Application::request|request]]: represents the request received from end users.
  Please refer to the [Requests](runtime-requests.md) section for more details.
* [[yii\web\Session|session]]: represents the session information. This component is only available
  in [[yii\web\Application|Web applications]].
  Please refer to the [Sessions and Cookies](runtime-sessions-cookies.md) section for more details.
* [[yii\web\UrlManager|urlManager]]: supports URL parsing and creation.
  Please refer to the [Routing and URL Creation](runtime-routing.md) section for more details.
* [[yii\web\User|user]]: represents the user authentication information. This component is only available
  in [[yii\web\Application|Web applications]].
  Please refer to the [Authentication](security-authentication.md) section for more details.
* [[yii\web\View|view]]: supports view rendering.
  Please refer to the [Views](structure-views.md) section for more details.
