模块
Modules
=======

模块是独立的软件单元，由[模型](structure-models.md), [视图](structure-views.md),
[控制器](structure-controllers.md)和其他支持组件组成，
终端用户可以访问在[应用主体](structure-applications.md)中已安装的模块的控制器，
模块被当成小应用主体来看待，和[应用主体](structure-applications.md)不同的是，
模块不能单独部署，必须属于某个应用主体。


## 创建模块 <a name="creating-modules"></a>
## Creating Modules <a name="creating-modules"></a>

模块被组织成一个称为[[yii\base\Module::basePath|base path]]的目录，
在该目录中有子目录如`controllers`, `models`, `views` 分别为对应控制器，模型，视图和其他代码，和应用非常类似。
如下例子显示一个模型的目录结构：

```
forum/
    Module.php                   模块类文件
    controllers/                 包含控制器类文件
        DefaultController.php    default 控制器类文件
    models/                      包含模型类文件
    views/                       包含控制器视图文件和布局文件
        layouts/                 包含布局文件
        default/                 包含DefaultController控制器视图文件
            index.php            index视图文件
```


### 模块类 <a name="module-classes"></a>
### Module Classes <a name="module-classes"></a>

每个模块都有一个继承[[yii\base\Module]]的模块类，该类文件直接放在模块的[[yii\base\Module::basePath|base path]]目录下，
并且能被 [自动加载](concept-autoloading.md)。当一个模块被访问，和 [application instances](structure-applications.md)
类似会创建该模块类唯一实例，模块实例用来帮模块内代码共享数据和组件。
Each module should have a module class which extends from [[yii\base\Module]]. The class should be located
directly under the module's [[yii\base\Module::basePath|base path]] and should be [autoloadable](concept-autoloading.md).
When a module is being accessed, a single instance of the corresponding module class will be created.
Like [application instances](structure-applications.md), module instances are used to share data and components
for code within modules.

以下示例一个模块类大致定义：
The following is an example how a module class may look like:

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  其他初始化代码 ...
    }
}
```

如果 `init()` 方法包含很多初始化模块属性代码，
可将他们保存在[配置](concept-configurations.md) 并在`init()`中使用以下代码加载：
If the `init()` method contains a lot of code initializing the module's properties, you may also save them in terms
of a [configuration](concept-configurations.md) and load it with the following code in `init()`:

```php
public function init()
{
    parent::init();
    // 从config.php加载配置来初始化模块
    \Yii::configure($this, require(__DIR__ . '/config.php'));
}
```

`config.php`配置文件可能包含以下内容，类似[应用主体配置](structure-applications.md#application-configurations).
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


### 模块中的控制器 <a name="controllers-in-modules"></a>
### Controllers in Modules <a name="controllers-in-modules"></a>

创建模块的控制器时，惯例是将控制器类放在模块类命名空间的`controllers`子命名空间中，
也意味着要将控制器类文件放在模块[[yii\base\Module::basePath|base path]]目录中的`controllers`子目录中。
例如，上小节中要在`forum`模块中创建`post`控制器，应像如下申明控制器类：

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

可配置[[yii\base\Module::controllerNamespace]]属性来自定义控制器类的命名空间，
如果一些控制器不再该命名空间下，可配置[[yii\base\Module::controllerMap]]属性让它们能被访问，
这类似于 [应用主体配置](structure-applications.md#controller-map) 所做的。
You may customize the namespace of controller classes by configuring the [[yii\base\Module::controllerNamespace]]
property. In case when some of the controllers are out of this namespace, you may make them accessible
by configuring the [[yii\base\Module::controllerMap]] property, similar to [what you do in an application](structure-applications.md#controller-map).


### 模块中的视图 <a name="views-in-modules"></a>
### Views in Modules <a name="views-in-modules"></a>

视图应放在模块的[[yii\base\Module::basePath|base path]]对应目录下的 `views` 目录，
对于模块中控制器对应的视图文件应放在 `views/ControllerID` 目录下，
其中`ControllerID`对应 [控制器 ID](structure-controllers.md#routes). For example, if
例如，假定控制器类为`PostController`，目录对应模块[[yii\base\Module::basePath|base path]]目录下的 `views/post` 目录。

模块可指定 [布局](structure-views.md#layouts)，它用在模块的控制器视图渲染。
布局文件默认放在 `views/layouts` 目录下，可配置[[yii\base\Module::layout]]属性指定布局名，
如果没有配置 `layout` 属性名，默认会使用应用的布局。
A module can specify a [layout](structure-views.md#layouts) that is applied to the views rendered by the module's
controllers. The layout should be put in the `views/layouts` directory by default, and you should configure
the [[yii\base\Module::layout]] property to point to the layout name. If you do not configure the `layout` property,
the application's layout will be used instead.


## 使用模块 <a name="using-modules"></a>
## Using Modules <a name="using-modules"></a>

要在应用中使用模块，只需要将模块加入到应用主体配置的[[yii\base\Application::modules|modules]]属性的列表中，
如下代码的[应用主体配置](structure-applications.md#application-configurations) 使用 `forum` 模块:
To use a module in an application, simply configure the application by listing the module in
the [[yii\base\Application::modules|modules]] property of the application. The following code in the
[application configuration](structure-applications.md#application-configurations) uses the `forum` module:

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... 模块其他配置 ...
        ],
    ],
]
```

[[yii\base\Application::modules|modules]] 属性使用模块配置数组，每个数组键为*模块 ID*，
它标识该应用中唯一的模块，数组的值为用来创建模块的 [配置](concept-configurations.md)。
The [[yii\base\Application::modules|modules]] property takes an array of module configurations. Each array key
represents a *module ID* which uniquely identifies the module among all modules in the application, and the corresponding
array value is a [configuration](concept-configurations.md) for creating the module.


### 路由 <a name="routes"></a>
### Routes <a name="routes"></a>

和访问应用的控制器类似，[路由](structure-controllers.md#routes) 也用在模块中控制器的寻址，
模块中控制器的路由必须以模块ID开始，接下来为控制器ID和操作ID。
例如，假定应用使用一个名为 `forum` 模块，路由`forum/post/index` 代表模块中 `post` 控制器的 `index` 操作，
如果路由只包含模块ID，默认为 `default` 的[[yii\base\Module::defaultRoute]] 属性来决定使用哪个控制器/操作，
也就是说路由 `forum` 可能代表 `forum` 模块的 `default` 控制器。
Like accessing controllers in an application, [routes](structure-controllers.md#routes) are used to address
controllers in a module. A route for a controller within a module must begin with the module ID followed by
the controller ID and action ID. For example, if an application uses a module named `forum`, then the route
`forum/post/index` would represent the `index` action of the `post` controller in the module. If the route
only contains the module ID, then the [[yii\base\Module::defaultRoute]] property, which defaults to `default`,
will determine which controller/action should be used. This means a route `forum` would represent the `default`
controller in the `forum` module.


### 访问模块 <a name="accessing-modules"></a>
### Accessing Modules <a name="accessing-modules"></a>

在模块中，可能经常需要获取[模块类](#module-classes)的实例来访问模块ID，模块参数，模块组件等，
可以使用如下语句来获取：
Within a module, you may often need to get the instance of the [module class](#module-classes) so that you can
access the module ID, module parameters, module components, etc. You can do so by using the following statement:

```php
$module = MyModuleClass::getInstance();
```

其中 `MyModuleClass` 对应你想要的模块类，`getInstance()` 方法返回当前请求的模块类实例，
如果模块没有被请求，该方法会返回空，注意不需要手动创建一个模块类，因为手动创建的和Yii处理请求时自动创建的不同。

> 补充: 当开发模块时，你不能假定模块使用固定的ID，因为在应用或其他没模块中，模块可能会对应到任意的ID，
  为了获取模块ID，应使用上述代码获取模块实例，然后通过`$module->id`获取模块ID。
> Info: When developing a module, you should not assume the module will use a fixed ID. This is because a module
  can be associated with an arbitrary ID when used in an application or within another module. In order to get
  the module ID, you should use the above approach to get the module instance first, and then get the ID via
  `$module->id`.

也可以使用如下方式访问模块实例:
You may also access the instance of a module using the following approaches:

```php
// 获取ID为 "forum" 的模块
$module = \Yii::$app->getModule('forum');

// 获取处理当前请求控制器所属的模块
$module = \Yii::$app->controller->module;
```

第一种方式仅在你知道模块ID的情况下有效，第二种方式在你知道处理请求的控制器下使用。
The first approach is only useful when you know the module ID, while the second approach is best used when you
know about the controllers being requested.

一旦获取到模块实例，可访问注册到模块的参数和组件，例如：
Once getting hold of a module instance, you can access parameters or components registered with the module. For example,

```php
$maxPostCount = $module->params['maxPostCount'];
```


### 引导启动模块 <a name="bootstrapping-modules"></a>
### Bootstrapping Modules <a name="bootstrapping-modules"></a>

有些模块在每个请求下都有运行， [[yii\debug\Module|debug]] 模块就是这种，
为此将这种模块加入到应用主体的 [[yii\base\Application::bootstrap|bootstrap]] 属性中。
Some modules may need to be run for every request. The [[yii\debug\Module|debug]] module is such an example.
To do so, list the IDs of such modules in the [[yii\base\Application::bootstrap|bootstrap]] property of the application.

例如，如下示例的应用主体配置会确保`debug`模块每次都被加载：
For example, the following application configuration makes sure the `debug` module is always load:

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


## 模块嵌套 <a name="nested-modules"></a>
## Nested Modules <a name="nested-modules"></a>

模块可无限级嵌套，也就是说，模块可以包含另一个包含模块的模块，我们称前者为*父模块*，后者为*子模块*，
子模块必须在父模块的[[yii\base\Module::modules|modules]]属性中申明，例如：

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // 此处应考虑使用一个更短的命名空间
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

在嵌套模块中的控制器，它的路由应包含它所有祖先模块的ID，例如`forum/admin/dashboard/index` 代表
在模块`forum`中子模块`admin`中`dashboard`控制器的`index`操作。


## 最佳实践 <a name="best-practices"></a>
## Best Practices <a name="best-practices"></a>

模块在大型项目中常备使用，这些项目的特性可分组，每个组包含一些强相关的特性，
每个特性组可以做成一个模块由特定的开发人员和开发组来开发和维护。
Modules are best used in large applications whose features can be divided into several groups, each consisting of
a set of closely related features. Each such feature group can be developed as a module which is developed and
maintained by a specific developer or team.

在特性组上，使用模块也是重用代码的好方式，一些常用特性，如用户管理，评论管理，可以开发成模块，
这样在相关项目中非常容易被重用。
Modules are also a good way of reusing code at the feature group level. Some commonly used features, such as
user management, comment management, can all be developed in terms of modules so that they can be reused easily
in future projects.
