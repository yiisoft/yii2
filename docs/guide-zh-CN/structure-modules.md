模块
=======

模块是独立的软件单元，由[模型](structure-models.md)，[视图](structure-views.md)，
[控制器](structure-controllers.md)和其他支持组件组成，
终端用户可以访问在[应用主体](structure-applications.md)中已安装的模块的控制器，
模块被当成小应用主体来看待，和[应用主体](structure-applications.md)不同的是，
模块不能单独部署，必须属于某个应用主体。


## 创建模块 <span id="creating-modules"></span>

模块被组织成一个称为 [[yii\base\Module::basePath|base path]] 的目录，
在该目录中有子目录如 `controllers`，`models`，`views` 分别为对应控制器，模型，视图和其他代码，和应用非常类似。
如下例子显示一个模型的目录结构：

```
forum/
    Module.php                   模块类文件
    controllers/                 包含控制器类文件
        DefaultController.php    default 控制器类文件
    models/                      包含模型类文件
    views/                       包含控制器视图文件和布局文件
        layouts/                 包含布局文件
        default/                 包含 DefaultController 控制器视图文件
            index.php            index 视图文件
```


### 模块类 <span id="module-classes"></span>

每个模块都有一个继承 [[yii\base\Module]] 的模块类，
该类文件直接放在模块的 [[yii\base\Module::basePath|base path]] 目录下，
并且能被 [自动加载](concept-autoloading.md)。当一个模块被访问，
和 [应用主体实例](structure-applications.md)
类似会创建该模块类唯一实例，模块实例用来帮模块内代码共享数据和组件。

以下示例一个模块类大致定义：

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
可将他们保存在[配置](concept-configurations.md) 并在 `init()` 中使用以下代码加载：

```php
public function init()
{
    parent::init();
    // 从config.php 加载配置来初始化模块
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

`config.php` 配置文件可能包含以下内容，类似
[应用主体配置](structure-applications.md#application-configurations)。

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


### 模块中的控制器 <span id="controllers-in-modules"></span>

创建模块的控制器时，惯例是将控制器类放在模块类命名空间的 `controllers` 子命名空间中，
也意味着要将控制器类文件放在模块
[[yii\base\Module::basePath|base path]] 目录中的 `controllers` 子目录中。
例如，上小节中要在 `forum` 模块中创建 `post` 控制器，
应像如下申明控制器类：

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

可配置 [[yii\base\Module::controllerNamespace]] 属性来自定义控制器类的命名空间，
如果一些控制器不再该命名空间下，可配置 [[yii\base\Module::controllerMap]] 属性让它们能被访问，
这类似于 [应用主体配置](structure-applications.md#controller-map) 所做的。


### 模块中的视图 <span id="views-in-modules"></span>

视图应放在模块的 [[yii\base\Module::basePath|base path]] 对应目录下的 `views` 目录，
对于模块中控制器对应的视图文件应放在 `views/ControllerID` 目录下，
其中 `ControllerID` 对应 [控制器 ID](structure-controllers.md#routes)。
例如，假定控制器类为 `PostController`，
目录对应模块[[yii\base\Module::basePath|base path]]目录下的 `views/post` 目录。

模块可指定 [布局](structure-views.md#layouts)，它用在模块的控制器视图渲染。
布局文件默认放在 `views/layouts` 目录下，
可配置 [[yii\base\Module::layout]] 属性指定布局名，
如果没有配置 `layout` 属性名，默认会使用应用的布局。


### 模块中的控制台命令 <span id="console-commands-in-modules"></span>

您的模块也可以声明命令，这将通过 [控制台](tutorial-console.md) 模式可用。

当 Yii 在控制台模式下执行并将其指向命令的命名空间时。想要在命令行中查看你的命令，
你需要更改 [[yii\base\Module::controllerNamespace]] 属性。

一种实现方法是在模块的 `init()` 方法中测试Yii应用程序的实例类型：

```php
public function init()
{
    parent::init();
    if (Yii::$app instanceof \yii\console\Application) {
        $this->controllerNamespace = 'app\modules\forum\commands';
    }
}
```

然后您的命令将从命令行使用以下路由：

```
yii <module_id>/<command>/<sub_command>
```

## 使用模块 <span id="using-modules"></span>

要在应用中使用模块，只需要将模块加入到应用主体配置的[[yii\base\Application::modules|modules]]属性的列表中，
如下代码的[应用主体配置](structure-applications.md#application-configurations) 
使用 `forum` 模块：

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

[[yii\base\Application::modules|modules]] 属性使用模块配置数组，
每个数组键为*模块 ID*，它标识该应用中唯一的模块，
数组的值为用来创建模块的 [配置](concept-configurations.md)。


### 路由 <span id="routes"></span>

和访问应用的控制器类似，[路由](structure-controllers.md#routes) 
也用在模块中控制器的寻址，
模块中控制器的路由必须以模块 ID 开始，接下来为控制器 ID 和操作 ID。
例如，假定应用使用一个名为 `forum` 模块，
路由`forum/post/index` 代表模块中 `post` 控制器的 `index` 操作，
如果路由只包含模块ID，默认为 `default` 的
[[yii\base\Module::defaultRoute]] 属性来决定使用哪个控制器/操作，
也就是说路由 `forum` 可能代表 `forum` 模块的 `default` 控制器。

在 [[yii\web\UrlManager::parseRequest()]] 被触发之前应该添加模块 URL 管理器规则。
这就意味着在模块的 `init()` 将不会起作用，因为模块将在路由开始处理时被初始化。
因此应该在 [bootstrap stage](structure-extensions.md#bootstrapping-classes) 添加规则。 
使用 [[\yii\web\GroupUrlRule]] 去实现模块的 URL 规则也是一种很好的做法。  

如果一个模块用于 [version API](rest-versioning.md)，
它的 URL 规则应该直接添加到应用程序配置的 `urlManager` 中。


### 访问模块 <span id="accessing-modules"></span>

在模块中，可能经常需要获取[模块类](#module-classes)的实例来访问模块ID，模块参数，模块组件等，
可以使用如下语句来获取：

```php
$module = MyModuleClass::getInstance();
```

其中 `MyModuleClass` 对应你想要的模块类，
`getInstance()` 方法返回当前请求的模块类实例，
如果模块没有被请求，该方法会返回空，注意不需要手动创建一个模块类，
因为手动创建的和Yii处理请求时自动创建的不同。

> Info: 当开发模块时，你不能假定模块使用固定的 ID，
  因为在应用或其他没模块中，模块可能会对应到任意的 ID，
  为了获取模块 ID，应使用上述代码获取模块实例，
  然后通过 `$module->id` 获取模块 ID。

也可以使用如下方式访问模块实例:

```php
// 获取ID为 "forum" 的模块
$module = \Yii::$app->getModule('forum');

// 获取处理当前请求控制器所属的模块
$module = \Yii::$app->controller->module;
```

第一种方式仅在你知道模块 ID 的情况下有效，
第二种方式在你知道处理请求的控制器下使用。

一旦获取到模块实例，可访问注册到模块的参数和组件，例如：

```php
$maxPostCount = $module->params['maxPostCount'];
```


### 引导启动模块 <span id="bootstrapping-modules"></span>

有些模块在每个请求下都有运行，[[yii\debug\Module|debug]] 模块就是这种，
为此将这种模块加入到应用主体的 [[yii\base\Application::bootstrap|bootstrap]] 属性中。

例如，如下示例的应用主体配置会确保 `debug` 模块每次都被加载：

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


## 模块嵌套 <span id="nested-modules"></span>

模块可无限级嵌套，也就是说，模块可以包含另一个包含模块的模块，我们称前者为*父模块*，后者为*子模块*，
子模块必须在父模块的 [[yii\base\Module::modules|modules]] 属性中申明，
例如：

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

在嵌套模块中的控制器，它的路由应包含它所有祖先模块的ID，
例如`forum/admin/dashboard/index` 代表
在模块`forum`中子模块`admin`中`dashboard`控制器的`index`操作。

> Info: [[yii\base\Module::getModule()|getModule()]] 方法只返回子模块的直属的
父模块。[[yii\base\Application::loadedModules]] 保存了已加所有载模块的属性，包括两者的子模块和
嵌套模块，并用他们的类名进行索引。

## 从模块内部访问组件

从 2.0.13 版本开始模块支持 [tree traversal](concept-service-locator.md#tree-traversal)。
这允发模块开发人员通过作为其模块的服务定位器去引用（应用程序）组件。
这意味着最好使用 `$module->get('db')` 而不是 `Yii::$app->get('db')`。
在需要不同组件（配置）的情况下，
模块开发者能够指定要用于模块的特定组件。

例如，以下是部分应用程序的配置：

```php
'components' => [
    'db' => [
        'tablePrefix' => 'main_',
        'class' => Connection::class,
        'enableQueryCache' => false
    ],
],
'modules' => [
    'mymodule' => [
        'components' => [
            'db' => [
                'tablePrefix' => 'module_',
                'class' => Connection::class
            ],
        ],
    ],
],
```

应用程序数据表将会以 `main_` 作为前缀，而所有模块表都将以 `module_` 为前缀。
注意上面的配置并未合并；例如模块的组件都将启用查询缓存，因为这是默认的设置。

## 最佳实践 <span id="best-practices"></span>

模块在大型项目中常备使用，这些项目的特性可分组，
每个组包含一些强相关的特性，
每个特性组可以做成一个模块由特定的开发人员和开发组来开发和维护。

在特性组上，使用模块也是重用代码的好方式，
一些常用特性，如用户管理，评论管理，可以开发成模块，
这样在相关项目中非常容易被重用。
