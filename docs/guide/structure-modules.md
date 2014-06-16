Modules
=======

> Note: This section is under development.

Modules are self-contained software units that consist of [models](structure-models.md), [views](structure-views.md),
[controllers](structure-controllers.md), and other supporting components. End users can access the controllers
of a module when it is installed in [application](structure-applications.md). Modules differ from
[applications](structure-applications.md) in that the former cannot be deployed alone and must reside within the latter.


## Creating Modules

A module is organized as a directory which is called the [[yii\base\Module::basePath|base path]] of the module.
The content in this directory is like the following:

```
forum/
    Module.php                   the module class file
    controllers/                 containing controller class files
        DefaultController.php    the default controller class file
    models/                      containing model class files
    views/                       containing controller view and layout files
        layouts/                 containing layout view files
        default/                 containing view files for DefaultController
            index.php            the index view file
```

As you can see, within the module's [[yii\base\Module::basePath|base path]], you can create sub-directories,
such as `controllers`, `models`, `views`, to hold controllers, models, views that belong to the module.


### Module Classes

Each module should have a module class which extends from [[yii\base\Module]] and is located directly under
the module's [[yii\base\Module::basePath|base path]]. When a module is being accessed, a single instance
of the corresponding module class will be created and made accessible by the code within the module.
Like [application instances](structure-applications.md), module instances are mainly used to share data and components
for code within modules.

The following is an example of a module class:

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  other initialization code ...
    }
}
```

If the code in `init()` deals with a lot of module property initialization, you may also save them in terms
of a [configuration](concept-configurations.md) and load it with the following code in `init()`:

```php
public function init()
{
    parent::init();
    // initialize the module with the configuration loaded from config.php
    \Yii::configure($this, require(__DIR__ . '/config.php'));
}
```

where the configuration file `config.php` may contain the following content, similar to that in an
[application configuration](structure-applications.md#application-configurations).

```php
<?php
return [
    'components' => [
        // list of component configurations
    ],
    'params' => [
        // list of parameters
    ],
];
```

> Note: Make sure that module classes are named in a way such that they are [autoloadable](concept-autoloading.md).


### Controllers in Modules

You may create a module class

Like [application instances](structure-applications.md) are used to
The module class serves as the central place for storing information shared among the module code. For example,
we can use [CWebModule::params] to store module parameters, and use [CWebModule::components] to share
[application components](/doc/guide/basics.application#application-component) at the module level.


### Views in Modules


## Using Modules

To use a module, first place the module directory under `modules` of the [application base directory](/doc/guide/basics.application#application-base-directory). Then declare the module ID in the [modules|CWebApplication::modules] property of the application. For example, in order to use the above `forum` module, we can use the following [application configuration](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

A module can also be configured with initial property values. The usage is very similar to configuring [application components](/doc/guide/basics.application#application-component). For example, the `forum` module may have a property named `postPerPage` in its module class which can be configured in the [application configuration](/doc/guide/basics.application#application-configuration) as follows:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

The module instance may be accessed via the [module|CController::module] property of the currently active controller. Through the module instance, we can then access information that are shared at the module level. For example, in order to access the above `postPerPage` information, we can use the following expression:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// or the following if $this refers to the controller instance
// $postPerPage=$this->module->postPerPage;
~~~

A controller action in a module can be accessed using the [route](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. For example, assuming the above `forum` module has a controller named `PostController`, we can use the [route](/doc/guide/basics.controller#route) `forum/post/create` to refer to the `create` action in this controller. The corresponding URL for this route would be `http://www.example.com/index.php?r=forum/post/create`.

> Tip: If a controller is in a sub-directory of `controllers`, we can still use the above [route](/doc/guide/basics.controller#route) format. For example, assuming `PostController` is under `forum/controllers/admin`, we can refer to the `create` action using `forum/admin/post/create`.


## Nested Modules

Modules can be nested in unlimited levels. That is, a module can contain another module which can contain yet another module. We call the former *parent module* while the latter *child module*. Child modules must be declared in the [modules|CWebModule::modules] property of their parent module, like we declare modules in the application configuration shown as above.

To access a controller action in a child module, we should use the route `parentModuleID/childModuleID/controllerID/actionID`.


## Best Practices

Users can access the controllers in a module like they do with normal application controllers.

Modules are useful in several scenarios. For a large-scale application, we may divide it into several modules,
each being developed and maintained separately. Some commonly used features, such as user management,
comment management, may be developed in terms of modules so that they can be reused easily in future projects.


