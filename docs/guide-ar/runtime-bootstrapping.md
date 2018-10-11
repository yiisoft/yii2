Bootstrapping
=============

Bootstrapping refers to the process of preparing the environment before an application starts
to resolve and process an incoming request. Bootstrapping is done in two places:
the [entry script](structure-entry-scripts.md) and the [application](structure-applications.md).

In the [entry script](structure-entry-scripts.md), class autoloaders for different libraries are
registered. This includes the Composer autoloader through its `autoload.php` file and the Yii
autoloader through its `Yii` class file. The entry script then loads the application
[configuration](concept-configurations.md) and creates an [application](structure-applications.md) instance.

In the constructor of the application, the following bootstrapping work is done:

1. [[yii\base\Application::preInit()|preInit()]] is called, which configures some high priority
   application properties, such as [[yii\base\Application::basePath|basePath]].
2. Register the [[yii\base\Application::errorHandler|error handler]].
3. Initialize application properties using the given application configuration.
4. [[yii\base\Application::init()|init()]] is called which in turn calls
   [[yii\base\Application::bootstrap()|bootstrap()]] to run bootstrapping components.
   - Include the extension manifest file `vendor/yiisoft/extensions.php`.
   - Create and run [bootstrap components](structure-extensions.md#bootstrapping-classes)
     declared by extensions.
   - Create and run [application components](structure-application-components.md) and/or
     [modules](structure-modules.md) that are declared in the application's
     [bootstrap property](structure-applications.md#bootstrap).

Because the bootstrapping work has to be done before handling *every* request, it is very important
to keep this process light and optimize it as much as possible.

Try not to register too many bootstrapping components. A bootstrapping component is needed only
if it wants to participate the whole life cycle of requesting handling. For example, if a module
needs to register additional URL parsing rules, it should be listed in the
[bootstrap property](structure-applications.md#bootstrap) so that the new URL rules can take effect
before they are used to resolve requests.

In production mode, enable a bytecode cache, such as [PHP OPcache] or [APC], to minimize the time needed for including
and parsing PHP files.

[PHP OPcache]: http://php.net/manual/en/intro.opcache.php
[APC]: http://php.net/manual/en/book.apc.php

Some large applications have very complex application [configurations](concept-configurations.md)
which are divided into many smaller configuration files. If this is the case, consider caching
the whole configuration array and loading it directly from cache before creating the application instance
in the entry script.
