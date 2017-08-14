Modules
=======

Modules are self-contained software units that consist of [models](structure-models.md), [views](structure-views.md),
[controllers](structure-controllers.md), and other supporting components. End users can access the controllers
of a module when it is installed in [application](structure-applications.md). For these reasons, modules are
often viewed as mini-applications. Modules differ from [applications](structure-applications.md) in that
modules cannot be deployed alone and must reside within applications.


## Creating Modules <span id="creating-modules"></span>

A module is organized as a directory which is called the [[yii\base\Module::basePath|base path]] of the module.
Within the directory, there are sub-directories, such as `controllers`, `models`, `views`, which hold controllers,
models, views, and other code, just like in an application. The following example shows the content within a module:

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


### Module Classes <span id="module-classes"></span>

Each module should have a unique module class which extends from [[yii\base\Module]]. The class should be located
directly under the module's [[yii\base\Module::basePath|base path]] and should be [autoloadable](concept-autoloading.md).
When a module is being accessed, a single instance of the corresponding module class will be created.
Like [application instances](structure-applications.md), module instances are used to share data and components
for code within modules.

The following is an example how a module class may look like:

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

If the `init()` method contains a lot of code initializing the module's properties, you may also save them in terms
of a [configuration](concept-configurations.md) and load it with the following code in `init()`:

```php
public function init()
{
    parent::init();
    // initialize the module with the configuration loaded from config.php
    \Yii::configure($this, require __DIR__ . '/config.php');
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


### Controllers in Modules <span id="controllers-in-modules"></span>

When creating controllers in a module, a convention is to put the controller classes under the `controllers`
sub-namespace of the namespace of the module class. This also means the controller class files should be
put in the `controllers` directory within the module's [[yii\base\Module::basePath|base path]].
For example, to create a `post` controller in the `forum` module shown in the last subsection, you should
declare the controller class like the following:

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

You may customize the namespace of controller classes by configuring the [[yii\base\Module::controllerNamespace]]
property. In case some of the controllers are outside of this namespace, you may make them accessible
by configuring the [[yii\base\Module::controllerMap]] property, similar to [what you do in an application](structure-applications.md#controller-map).


### Views in Modules <span id="views-in-modules"></span>

Views in a module should be put in the `views` directory within the module's [[yii\base\Module::basePath|base path]].
For views rendered by a controller in the module, they should be put under the directory `views/ControllerID`,
where `ControllerID` refers to the [controller ID](structure-controllers.md#routes). For example, if
the controller class is `PostController`, the directory would be `views/post` within the module's
[[yii\base\Module::basePath|base path]].

A module can specify a [layout](structure-views.md#layouts) that is applied to the views rendered by the module's
controllers. The layout should be put in the `views/layouts` directory by default, and you should configure
the [[yii\base\Module::layout]] property to point to the layout name. If you do not configure the `layout` property,
the application's layout will be used instead.


### Console commands in Modules <span id="console-commands-in-modules"></span>

Your module may also declare commands, that will be available through the [Console](tutorial-console.md) mode.

In order for the command line utility to see your commands, you will need to change the [[yii\base\Module::controllerNamespace]]
property, when Yii is executed in the console mode, and point it to your commands namespace.

One way to achieve that is to test the instance type of the Yii application in the module's `init()` method:

```php
public function init()
{
    parent::init();
    if (Yii::$app instanceof \yii\console\Application) {
        $this->controllerNamespace = 'app\modules\forum\commands';
    }
}
```

Your commands will then be available from the command line using the following route:

```
yii <module_id>/<command>/<sub_command>
```

## Using Modules <span id="using-modules"></span>

To use a module in an application, simply configure the application by listing the module in
the [[yii\base\Application::modules|modules]] property of the application. The following code in the
[application configuration](structure-applications.md#application-configurations) uses the `forum` module:

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... other configurations for the module ...
        ],
    ],
]
```

The [[yii\base\Application::modules|modules]] property takes an array of module configurations. Each array key
represents a *module ID* which uniquely identifies the module among all modules in the application, and the corresponding
array value is a [configuration](concept-configurations.md) for creating the module.


### Routes <span id="routes"></span>

Like accessing controllers in an application, [routes](structure-controllers.md#routes) are used to address
controllers in a module. A route for a controller within a module must begin with the module ID followed by
the [controller ID](structure-controllers.md#controller-ids) and [action ID](structure-controllers.md#action-ids).
For example, if an application uses a module named `forum`, then the route
`forum/post/index` would represent the `index` action of the `post` controller in the module. If the route
only contains the module ID, then the [[yii\base\Module::defaultRoute]] property, which defaults to `default`,
will determine which controller/action should be used. This means a route `forum` would represent the `default`
controller in the `forum` module.


### Accessing Modules <span id="accessing-modules"></span>

Within a module, you may often need to get the instance of the [module class](#module-classes) so that you can
access the module ID, module parameters, module components, etc. You can do so by using the following statement:

```php
$module = MyModuleClass::getInstance();
```

where `MyModuleClass` refers to the name of the module class that you are interested in. The `getInstance()` method
will return the currently requested instance of the module class. If the module is not requested, the method will
return `null`. Note that you do not want to manually create a new instance of the module class because it will be
different from the one created by Yii in response to a request.

> Info: When developing a module, you should not assume the module will use a fixed ID. This is because a module
  can be associated with an arbitrary ID when used in an application or within another module. In order to get
  the module ID, you should use the above approach to get the module instance first, and then get the ID via
  `$module->id`.

You may also access the instance of a module using the following approaches:

```php
// get the child module whose ID is "forum"
$module = \Yii::$app->getModule('forum');

// get the module to which the currently requested controller belongs
$module = \Yii::$app->controller->module;
```

The first approach is only useful when you know the module ID, while the second approach is best used when you
know about the controllers being requested.

Once you have the module instance, you can access parameters and components registered with the module. For example,

```php
$maxPostCount = $module->params['maxPostCount'];
```


### Bootstrapping Modules <span id="bootstrapping-modules"></span>

Some modules may need to be run for every request. The [[yii\debug\Module|debug]] module is such an example.
To do so, list the IDs of such modules in the [[yii\base\Application::bootstrap|bootstrap]] property of the application.

For example, the following application configuration makes sure the `debug` module is always loaded:

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```


## Nested Modules <span id="nested-modules"></span>

Modules can be nested in unlimited levels. That is, a module can contain another module which can contain yet
another module. We call the former *parent module* while the latter *child module*. Child modules must be declared
in the [[yii\base\Module::modules|modules]] property of their parent modules. For example,

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // you should consider using a shorter namespace here!
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

For a controller within a nested module, its route should include the IDs of all its ancestor modules.
For example, the route `forum/admin/dashboard/index` represents the `index` action of the `dashboard` controller
in the `admin` module which is a child module of the `forum` module.

> Info: The [[yii\base\Module::getModule()|getModule()]] method only returns the child module directly belonging
to its parent. The [[yii\base\Application::loadedModules]] property keeps a list of loaded modules, including both
direct children and nested ones, indexed by their class names.


## Best Practices <span id="best-practices"></span>

Modules are best used in large applications whose features can be divided into several groups, each consisting of
a set of closely related features. Each such feature group can be developed as a module which is developed and
maintained by a specific developer or team.

Modules are also a good way of reusing code at the feature group level. Some commonly used features, such as
user management, comment management, can all be developed in terms of modules so that they can be reused easily
in future projects.
