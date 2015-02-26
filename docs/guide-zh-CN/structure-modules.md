模块
=======

模块是独立的软件单元，由[模型](structure-models.md), [视图](structure-views.md),
[控制器](structure-controllers.md)和其他支持组件组成，
终端用户可以访问在[应用主体](structure-applications.md)中已安装的模块的控制器，
模块被当成小应用主体来看待，和[应用主体](structure-applications.md)不同的是，
模块不能单独部署，必须属于某个应用主体。


## 创建模块 <span id="creating-modules"></span>

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


### 模块类 <span id="module-classes"></span>

每个模块都有一个继承[[yii\base\Module]]的模块类，该类文件直接放在模块的[[yii\base\Module::basePath|base path]]目录下，
并且能被 [自动加载](concept-autoloading.md)。当一个模块被访问，和 [应用主体实例](structure-applications.md)
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
可将他们保存在[配置](concept-configurations.md) 并在`init()`中使用以下代码加载：

```php
public function init()
{
    parent::init();
    // 从config.php加载配置来初始化模块
    \Yii::configure($this, require(__DIR__ . '/config.php'));
}
```

`config.php`配置文件可能包含以下内容，类似[应用主体配置](structure-applications.md#application-configurations).

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


### 模块中的视图 <span id="views-in-modules"></span>

视图应放在模块的[[yii\base\Module::basePath|base path]]对应目录下的 `views` 目录，
对于模块中控制器对应的视图文件应放在 `views/ControllerID` 目录下，
其中`ControllerID`对应 [控制器 ID](structure-controllers.md#routes). For example, if
例如，假定控制器类为`PostController`，目录对应模块[[yii\base\Module::basePath|base path]]目录下的 `views/post` 目录。

模块可指定 [布局](structure-views.md#layouts)，它用在模块的控制器视图渲染。
布局文件默认放在 `views/layouts` 目录下，可配置[[yii\base\Module::layout]]属性指定布局名，
如果没有配置 `layout` 属性名，默认会使用应用的布局。


## 使用模块 <span id="using-modules"></span>

要在应用中使用模块，只需要将模块加入到应用主体配置的[[yii\base\Application::modules|modules]]属性的列表中，
如下代码的[应用主体配置](structure-applications.md#application-configurations) 使用 `forum` 模块:

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


### 路由 <span id="routes"></span>

和访问应用的控制器类似，[路由](structure-controllers.md#routes) 也用在模块中控制器的寻址，
模块中控制器的路由必须以模块ID开始，接下来为控制器ID和操作ID。
例如，假定应用使用一个名为 `forum` 模块，路由`forum/post/index` 代表模块中 `post` 控制器的 `index` 操作，
如果路由只包含模块ID，默认为 `default` 的[[yii\base\Module::defaultRoute]] 属性来决定使用哪个控制器/操作，
也就是说路由 `forum` 可能代表 `forum` 模块的 `default` 控制器。


### 访问模块 <span id="accessing-modules"></span>

在模块中，可能经常需要获取[模块类](#module-classes)的实例来访问模块ID，模块参数，模块组件等，
可以使用如下语句来获取：

```php
$module = MyModuleClass::getInstance();
```

其中 `MyModuleClass` 对应你想要的模块类，`getInstance()` 方法返回当前请求的模块类实例，
如果模块没有被请求，该方法会返回空，注意不需要手动创建一个模块类，因为手动创建的和Yii处理请求时自动创建的不同。

> 补充: 当开发模块时，你不能假定模块使用固定的ID，因为在应用或其他没模块中，模块可能会对应到任意的ID，
  为了获取模块ID，应使用上述代码获取模块实例，然后通过`$module->id`获取模块ID。

也可以使用如下方式访问模块实例:

```php
// 获取ID为 "forum" 的模块
$module = \Yii::$app->getModule('forum');

// 获取处理当前请求控制器所属的模块
$module = \Yii::$app->controller->module;
```

第一种方式仅在你知道模块ID的情况下有效，第二种方式在你知道处理请求的控制器下使用。

一旦获取到模块实例，可访问注册到模块的参数和组件，例如：

```php
$maxPostCount = $module->params['maxPostCount'];
```


### 引导启动模块 <span id="bootstrapping-modules"></span>

有些模块在每个请求下都有运行， [[yii\debug\Module|debug]] 模块就是这种，
为此将这种模块加入到应用主体的 [[yii\base\Application::bootstrap|bootstrap]] 属性中。

例如，如下示例的应用主体配置会确保`debug`模块每次都被加载：

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


## 最佳实践 <span id="best-practices"></span>

模块在大型项目中常备使用，这些项目的特性可分组，每个组包含一些强相关的特性，
每个特性组可以做成一个模块由特定的开发人员和开发组来开发和维护。

在特性组上，使用模块也是重用代码的好方式，一些常用特性，如用户管理，评论管理，可以开发成模块，
这样在相关项目中非常容易被重用。
