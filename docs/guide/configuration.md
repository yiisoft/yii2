Configuration
=============

Yii applications rely upon components to perform most of the common tasks, such as connecting to a database, routing browser
requests, and handling sessions. How these stock components behave can be adjusted by *configuring* your Yii application.
The majority of components have sensible default settings, so it's unlikely that you'll do a lot of configuration. Still, there are some mandatory configuration settings that you will have to establish, such as the database connection.

How an application is configured depends upon the application template in use, but there are some general principles that apply in every Yii case.

Configuring options in the bootstrap file
-----------------------------------------

For each application in Yii there is at least one bootstrap file: a PHP script through which all requests are handled. For web applications, the bootstrap file is  typically `index.php`; for
console applications, the bootstrap file is `yii`. Both bootstrap files perform nearly the same job:

1. Setting common constants.
2. Including the Yii framework itself.
3. Including [Composer autoloader](http://getcomposer.org/doc/01-basic-usage.md#autoloading).
4. Reading the configuration file into `$config`.
5. Creating a new application instance, configured via `$config`, and running that instance.

Like any resource in your Yii application, the bootstrap file can be edited to fit your needs. A typical change is to the value of `YII_DEBUG`. This constant should be `true` during development, but always `false` on production sites.

The default bootstrap structure sets `YII_DEBUG` to `false` if not defined:

```php
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

During development, you can change this to `true`:

```php
define('YII_DEBUG', true); // Development only 
defined('YII_DEBUG') or define('YII_DEBUG', false);
```

Configuring the application instance
------------------------------------

An application instance is configured when it's created in the bootstrap file. The configuration is typically
stored in a PHP file stored in the `/config` application directory. The file has this structure to begin:

```php
<?php
return [
    'id' => 'applicationId',
    'basePath' => dirname(__DIR__),
    'components' => [
        // configuration of application components goes here...
    ],
    'params' => require(__DIR__ . '/params.php'),
];
```

The configuration is a large array of key-value pairs. In the above, the array keys are the names of application properties. Depending upon the application type, you can configure the properties of
either [[yii\web\Application]] or [[yii\console\Application]] classes. Both classes extend  [[yii\base\Application]].

Note that you can configure not only public class properties, but any property accessible via a setter. For example, to
  configure the runtime path, you can use a key named `runtimePath`. There's no such property in the application class, but
  since the class has a corresponding setter named `setRuntimePath`, `runtimePath` becomes configurable.
  The ability to configure properties via setters is available to any class that extends from [[yii\base\Object]], which is nearly every class in the Yii framework.

Configuring application components
----------------------------------

The majority of the Yii functionality comes from application components. These components are attached to the application instance via the instance's `components` property:

```php
<?php
return [
    'id' => 'applicationId',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => ['class' => 'yii\caching\FileCache'],
        'user' => ['identityClass' => 'app\models\User'],
        'errorHandler' => ['errorAction' => 'site/error'],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    // ...
];
```

In the above code, four components are configured: `cache`, `user`, `errorHandler`, `log`. Each entry's key is a component ID. The values are subarrays used to configure that component. The component ID is also used to access the component anywhere within the application, using code like `\Yii::$app->myComponent`.

The configuration array has one special key named `class` that identifies the component's base class. The rest of the keys and values are used
to configure component properties in the same way as top-level keys are used to configure the application's properties.

Each application has a predefined set of components. To configure one of these, the `class` key can be omitted to use the default Yii class for that component. You can check the `coreComponents()` method of the application you are using
to get a list of component IDs and corresponding classes.

Note that Yii is smart enough to only configure the component when it's actually being used: for example, if you configure the `cache` component in your configuration file but never use the `cache` component in your code, no instance of that component will be created and no time is wasted configuring it.

Setting component defaults class-wide
------------------------------------

For each component you can specify class-wide defaults. For example, if you want to change the class used for all `LinkPager`
widgets without specifying the class for every widget usage, you can do the following:

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'options' => [
        'class' => 'pagination',
    ],
]);
```

The code above should be executed once before `LinkPager` widget is used. It can be done in `index.php`, the application
configuration file, or anywhere else.
