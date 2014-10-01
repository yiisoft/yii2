应用
============

应用是管理整个 Yii 应用系统总体结构和生命周期的对象。每个 Yii 应用系统都包含一个由[入口脚本](structure-entry-scripts.md)创建的单例对象，可以在全局范围通过表达式 `\Yii:$app` 访问。

> 补充：取决于上下文环境不同，当讲到“一个应用”时，它可能是一个应用对象的意思，也可能指整个应用系统。

应用有两种类型：[[yii\web\Application|Web 应用]] 和 [[yii\console\Application|控制台应用]]。顾名思义，前者主要处理 Web 请求而后者则处理控制台命令请求。


## 应用配置 <a name="application-configurations"></a>

当[入口脚本](structure-entry-scripts.md)创建一个应用，它将载入[配置](concept-configurations.md)并将其应用在应用中，如下：

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// 载入应用配置
$config = require(__DIR__ . '/../config/web.php');

// 实例化并配置应用
(new yii\web\Application($config))->run();
```

正如[配置](concept-configurations.md)所述，应用配置指定了如何初始化应用对象的属性。应用配置往往比较复杂，所以它们被保存在[配置文件](concept-configurations.md#configuration-files)中，例如上述代码中的 `web.php`。


## 应用属性 <a name="application-properties"></a>

在应用中有相当多的重要属性需要配置。这些属性通常描述了应用运行的环境。例如，应用需要知道如何载入[控制器](structure-controllers.md)，哪里储存临时文件，等等。下面我们将总结这些属性。


### 必需属性 <a name="required-properties"></a>

在任何应用中，你应该至少配置两个属性：[[yii\base\Application::id|id]] 和 [[yii\base\Application::basePath|basePath]]。


#### [[yii\base\Application::id|id]] <a name="id"></a>

[[yii\base\Application::id|id]] 属性指定了一个应用区别于其它应用的唯一 ID。主要以编程方式使用它。尽管不是必要条件，但为了最佳互通性，还是建议你仅使用字母和数字去指定应用 ID。


#### [[yii\base\Application::basePath|基本路径（basePath）]] <a name="basePath"></a>

[[yii\base\Application::basePath|basePath]] 属性指定了一个应用的根目录。这个目录包含了应用系统的所有受保护源码。在此目录下，你通常会看到诸如 `models`，`views`，`controllers` 之类的子目录，这些子目录包含了与 MVC 模式对应的源码。

你可以使用目录路径或[路径别名](concept-aliases.md)去配置 [[yii\base\Application::basePath|basePath]]。这两种形式配置时，对应的目录都必须存在，否则会抛出异常。路径将会通过 
调用 `realpath()` 函数实现标准化。

[[yii\base\Application::basePath|basePath]] 通常用来派生出其它重要路径（例如 runtime 路径）。鉴于此，路径别名 `@app` 被预定义指向这个路径。这样前面所说的派生出的路径就可以使用这个别名来访问（例如 `@app/runtime` 指向 bashPath 下的 runtime 目录）。


### 重要属性 <a name="important-properties"></a>

这部分涉及到的属性往往需要被配置，因为它们在不同应用中通常是不同的。


#### [[yii\base\Application::aliases|别名（aliases）]] <a name="aliases"></a>

这个属性让你通过一个数组的形式定义一组[别名](concept-aliases.md)。数组的键是别名的名称，数组的值是对应的路径定义。例如：

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

这个属性提供了一种通过调用 [[Yii::setAlias()]] 方法之外的，通过应用配置定义别名的方式。


#### [[yii\base\Application::bootstrap|引导（bootstrap）]] <a name="bootstrap"></a>

这是相当有用的属性。它允许你以数组形式指定一些组件在应用 [[yii\base\Application::bootstrap()|引导过程]]就执行。例如：如果想让一个[模型](structure-modules.md)自定义[ URL 规则](runtime-url-handling.md) 你可以以数组元素的形式列出它的 ID。

每个属性中都可以用下述任意格式列出组件：

- 按照[组件](#components)指定的一个应用组件 ID。
- 按照[模块](#modules)指定的一个模块 ID。
- 一个类名。
- 一个配置数组。
- 一个创建并返回组件的匿名函数。

例如：

```php
[
    'bootstrap' => [
        // 一个应用组件 ID 或模块 ID
        'demo',

        // 一个类名
        'app\components\Profiler',

        // 一个配置数组
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // 一个匿名函数
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> 补充：如果一个模块 ID 和应用组件 ID 重复，应用组件 ID 将被用于应用引导过程中执行。如果你想使用模块 ID，可以使用匿名函数返回它：
  
>```php
[
    function () {
        return Yii::$app->getModule('user');
    },
]
```


在应用引导过程期间，每个组件都会被实例化。如果组件类实现了 [[yii\base\BootstrapInterface]]，它的 [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] 也将被调用。

另一个例子是[基础应用模版](start-installation.md)中的应用配置，当应用运行在开发环境中时 `debug` 和 `gii` 两个模块将被配制成引导组件：

```php
if (YII_ENV_DEV) {
    // 针对 `dev` 环境所做的配置调整
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> 注意：放置太多组件在 `bootstrap` 过程中将会降低应用性能，因为每个请求都要运行相同的一系列组件。所以，请明智地使用引导组件。


#### [[yii\web\Application::catchAll|捕获所有请求（catchAll）]] <a name="catchAll"></a>

这个属性仅支持 [[yii\web\Application|Web 应用]]。它指定了一个处理所有用户请求的[控制器操作](structure-controllers.md)。这主要在应用处于维护模式，或使用单独一个操作响应所有用户请求时使用。

配置项是一个数组，第一个元素指定操作的路由。剩下的数组元素（键值对）指定绑定到操作上的参数。例如：

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|组件（components）]] <a name="components"></a>

这是最重要的属性。它可以让你注册一组命名的被称为[应用组件](#structure-application-components.md)的组件列表，以便你在应用中使用。例如：

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
]
```

每个应用组件都由一个键值对组成的数组指定。键代表组件 ID，值则表示组件类名或[配置](concept-configuration.md)。

你可以随着一个应用注册任何组件，组件可以在随后使用表达式 `\Yii->$app->ComponentID` 全局访问。

请参考[应用组件](structure-application-components.md)章节了解更多信息。


#### [[yii\base\Application::controllerMap|控制器映射（controllerMap）]] <a name="controllerMap"></a>

这个属性允许你映射控制器 ID 至任意控制器类。默认情况下，Yii 将控制器 ID 基于[控制器命名约定](#controllerNamespace)进行映射（例如，ID `post` 将被映射到 `app\controllers\PostController`）。通过配置这个属性，你可以针对特定控制器打破这种约定。下述例子中，`account` 将被映射到 `app\controllers\UserController`，而 `article` 将被映射到 `app\controllers\PostController`。

```php
[
    'controllerMap' => [
        [
            'account' => 'app\controllers\UserController',
            'article' => [
                'class' => 'app\controllers\PostController',
                'enableCsrfValidation' => false,
            ],
        ],
    ],
]
```

这个属性的数组键名代表控制器 ID，而数组值代表对应的控制器名或[配置](concept-configurations.md)。


#### [[yii\base\Application::controllerNamespace|控制器命名空间（controllerNamespace）]] <a name="controllerNamespace"></a>

这个属性指定了从哪个命名空间下寻找控制器类。默认值为 `app\controllers`。如果一个控制器的 ID 是 `post`，按惯例对应的控制器类名（没有命名空间）将是 `PostController`，完全限定类名将是 `app\controllers\PostController`。

控制器类也许会被定位到此命名空间对应目录下的子目录。例如，给定一个控制器类 `admin/post`，对应的控制器完全限定类名将会是 `app\controllers\admin\PostController`。

控制器的完全限定名能被[自动加载](concept-autoloading.md)并且控制器类实际的命名空间与此属性匹配非常重要。否则你在访问控制器时将会收到 “Page Not Found”错误。

如果你想要打破上述约定，可以配置[控制器映射](#controllerMap)属性。


#### [[yii\base\Application::language|语言（language）]] <a name="language"></a>

这个属性指定了应用应该以何种语言显示给最终用户。默认值是 `en`，即英语。如果你的应用需要支持多种语言，应该配置这个属性。

这个属性的值确定了[国际化](tutorial-i18n.md)方面的多个内容，包括消息翻译，日期格式，数字格式，等等。例如，小部件 [[yii\jui\DatePicker]] 将使用这个属性的值来确定日历应该显示哪种语言以及日期使用哪种格式。

建议你根据 [IETF 语言标签](http://en.wikipedia.org/wiki/IETF_language_tag)去指定语言。例如，`en` 代表英语，而 `en-US` 代表英语（美国）。

更多关于该属性的细节请查看[国际化](tutorial-i18n.md)章节。

#### [[yii\base\Application::modules|模块（modules)]] <a name="modules"></a>

这个属性指定了应用中包含的[模块](structure-modules.md)。

这个属性接受一个模块类或[配置](concept-configurations.md)的数组，数组键名作为模块 ID。例如：

```php
[
    'modules' => [
        // 通过模块类指定一个 “booking” 模块
        'booking' => 'app\modules\booking\BookingModule',

        // 通过配置数组指定一个 “comment” 模块
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

请参考[模块](structure-modules.md)章节了解详情。


#### [[yii\base\Application::name|名称（name）]] <a name="name"></a>

这个属性指定了可能显示给最终用户的应用名称。和 [[yii\base\Application::id|id]] 只能接受唯一值不同，这个属性主要用作显示目的，所以不必唯一。

如果你的代码没有使用它就不必配置。


#### [[yii\base\Application::params|参数（params）]] <a name="params"></a>

这个属性指定了一个全局可访问的应用参数。可以代替在应用中四处硬编码数字和字符，在一处定义参数，并在其它地方按需使用是良好的实践。例如，你可能会以参数形式定义缩略图尺寸：

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

然后在你的代码中需要用到这些值的地方，可以简单的使用它们：

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

今后如果你决定更改缩略图尺寸，只需在应用配置中修改它即可，无需改动任何依赖的代码。


#### [[yii\base\Application::sourceLanguage|源码语言（sourceLanguage）]] <a name="sourceLanguage"></a>

这个属性指定了应用代码以何种语言写就。默认值是 `en-US`，即英语（美国）。如果代码中的文字内容不是英语你就该配置它。

正如[语言（language）](#language)属性一样，你应该根据 [IETF 语言标签](http://en.wikipedia.org/wiki/IETF_language_tag)去指定语言。例如，`en` 代表英语，而 `en-US` 代表英语（美国）。

更多关于此属性的细节请查看[国际化](tutorial-i18n.md)章节。


#### [[yii\base\Application::timeZone|时区（timeZone）]] <a name="timeZone"></a>

这个属性提供了一个设置 PHP 运行时默认时区的可选方法。配置这个属性，本质上就是调用 PHP 函数 [date_default_timezone_set()](http://php.net/manual/en/function.date-default-timezone-set.php)。例如：

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|版本（version）]] <a name="version"></a>

这个属性指定了应用的版本。默认为 `'1.0'`。如果代码中没有用到它就不需要配置。


### 有用属性 <a name="useful-properties"></a>

这部分提到的属性并不常用，因为它们的默认配置比较符合通用约定。如果想打破这种约定，仍然可以配置它们。


#### [[yii\base\Application::charset|编码（charset）]] <a name="charset"></a>

这个属性定义了应用使用的编码。默认值是 `utf-8`，除非你要整合遗留系统的非 Unicode 数据，否则应该保留默认值。


#### [[yii\base\Application::defaultRoute|默认路由（defaultRoute）]] <a name="defaultRoute"></a>

这个属性指定了当一个请求没有指定路由时应用默认使用的[路由](runtime-routing.md)。 路由可能由模块 ID，控制器 ID，操作 ID组成。例如，`help`，`post/create`，`admin/post/create`。如果没有给定操作 ID，它将使用 [[yii\base\Controller::defaultAction]] 指定的值作为默认值。

对于 [[yii\web\Application|Web 应用]]，这个属性的默认值是 `'site'`，即 `SiteController` 控制器和它默认操作将被使用。因此，当你访问应用而不指定任何路由时，它将显示 `app\controllers\SiteController::actionIndex()` 的执行结果。

对于 [[yii\console\Application|控制台应用]]，这个属性的默认值是 `'help'`，即核心命令 [[yii\console\controllers\HelpController::actionIndex()]]  将被使用。因此，当你执行 `yii` 命令而不提供任何参数时，它将显示帮助信息。


#### [[yii\base\Application::extensions|扩展（extension）]] <a name="extensions"></a>

这个属性指定了已被应用安装和使用的[扩展](structure-extensions.md)列表。默认情况下，它接受的是从文件 `@vendor/yiisoft/extension.php` 返回的数组。 `extension.php` 文件是当你使用 [Composer](http://getcomposer.org) 安装扩展时自动生成和维护的。所以多数情况下，无需配置这个属性。

特殊情况下你也许想要手动维护扩展，可以配置这个属性：

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // 可选项，也可以是一个配置数组
            'alias' => [  // optional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... 和上面格式一样的更多扩展 ...

    ],
]
```

如你所见，该属性接受一个包含扩展说明的数组。每个扩展由包含 `name` 和 `version` 元素的数组指定。如果一个扩展需要在[引导](runtime-bootstrapping.md)过程中执行，还可以指定一个 `bootstrap` 元素，其值可以是引导类名或[配置](concept-configurations.md)数组。一个扩展同样可以定义[别名](concept-aliases.md)。


#### [[yii\base\Application::layout|布局（layout）]] <a name="layout"></a>

这个属性指定了渲染[视图](structure-views.md)时的默认布局文件名。默认值为 `'main'`，意思是[布局路径](#layoutPath)下的 `main.php` 文件会被使用。如果[布局路径](#layoutPath)和[视图路径](#viewPath)都使用默认值，则默认布局文件可以用别名`@app/views/layouts/main.php`表示。

尽管这很不常用，但你可以通过配置此属性为 `false` 去禁用默认布局。


#### [[yii\base\Application::layoutPath|布局路径（layoutPath)]] <a name="layoutPath"></a>

这个属性指定了布局文件的寻找路径。默认值为[视图路径](#viewPath)下的 `layouts` 子目录。如果[视图路径](#viewPath)使用的是默认值，则默认布局路径可以用别名 `@app/views/layouts` 表示。

你可以使用目录地址或路径[别名](concept-aliases.md)配置本属性。


#### [[yii\base\Application::runtimePath|运行时路径（runtimePath）]] <a name="runtimePath"></a>

这个属性指定了临时文件生成的路径，如日志文件，缓存文件。属性默认值被表示为 `@app/runtime`。

你可以使用目录地址或路径[别名](concept-aliases.md)配置它。请注意，运行时路径必须有执行应用进程的写入权限。并且应该保证终端用户没有访问权限，因为路径中的临时文件可能包含敏感信息。

为了简化此路径访问，Yii 为此路径预定义了一个别名 `@runtime`。


#### [[yii\base\Application::viewPath|视图路径（viewPath）]] <a name="viewPath"></a>

这个属性指定了视图文件存储的根目录。默认值是别名 `@app/views` 代表的目录。你可以用目录地址或路径[别名](concept-aliases.md)配置它。


#### [[yii\base\Application::vendorPath|供应商目录（vendorPath）]] <a name="vendorPath"></a>

这个属性指定了 [Composer](http://getcomposer.org) 负责维护的供应商目录。它包含了应用运行需要的所有第三方库，包括 Yii 框架核心。默认值是别名 `@app/vendor` 代表的目录。

你可以用目录地址或路径[别名](concept-aliases.md)配置它。如果你修改了此属性默认值，请确保你同样相应调整了 Composer 配置。

为了简化此路径访问，Yii 为此路径预定义了一个别名 `@vendor`。


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <a name="enableCoreCommands"></a>

这个属性只被[[yii\console\Application|控制台应用]]所支持。它定义了 Yii 发行包中所包含的核心命令是否可以使用。默认值为 `true`。


## 应用事件 <a name="application-events"></a>

一个应用在处理请求的生命周期中会触发几个事件。你可以在应用配置中附加对应的事件处理器给这些事件：

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

`on eventName` 的使用语法在[配置](concept-configuration.md#configuration-format)章节有所描述。

另一种方式，你可以在应用实例化后的[引导过程](runtime-bootstrapping.md)附加事件处理器。例如：

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <a name="beforeRequest"></a>

这个事件将在应用处理一个请求**之前**被触发。真实的事件名是 `beforeRequest`。

当此事件被触发时，应用实例已经被配置并初始化。所以这是个绝佳位置去通过事件机制插入自定义代码截获请求处理过程。例如，在事件处理器中，你可以基于某些参数动态设置应用的[[yii\base\Application::（语言）language]]。


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_REQUEST]] <a name="afterRequest"></a>

这个事件将在应用处理完成一个请求**之后**并且在发送响应内容**之前**被触发。真实的事件名是 `afterRequest`。

当此事件被触发时，请求处理过程已经完成，并且你可能借此机会对请求做一些后期加工或自定义响应内容。

请注意[[yii\web\Response|响应（response）]]组件同样可以在向用户发送响应内容时触发事件。那些事件将在此事件**之后**被触发。


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_ACTION]] <a name="beforeAction"></a>

这个事件将在每个[控制器操作](structure-controllers.md)运行**之前**被触发。真实的事件名是 `beforeAction`。

事件参数是一个 [[yii\base\ActionEvent]] 类的实例。一个事件处理器可以设置 [[yii\base\ActionEvent::isValid]] 属性为 `false` 去阻止操作执行。例如：

```php
[
    'on beforeAction' => function ($event) {
        if (一些条件) {
            $event->isValid = false;
        } else {
        }
    },
]
```

请注意 `beforeAction` 事件同样能被[模块](structure-modules.md)和[控制器](structure-controllers.md)触发。应用对象第一个触发此事件，紧接着是模块（如果有），最后是控制器。如果一个事件处理器设置了 [[yii\base\ActionEvent::isVaild]] 为 `false`，所有其后的事件都将**不被**触发。


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_ACTION]] <a name="afterAction"></a>

这个事件将在每个[控制器操作](structure-controllers.md)运行**之后**被触发。真实的事件名是 `afterAction`。

事件参数是一个 [[yii\base\ActionEvent]] 类的实例。通过 [[yii\base\ActionEvent::result]] 属性，一个事件处理器可以访问或修改操作返回的结果。例如：

```php
[
    'on afterAction' => function ($event) {
        if (一些条件) {
            // 修改 $event->result
        } else {
        }
    },
]
```

请注意 `afterAction` 事件同样能被[模块](structure-modules.md)和[控制器](structure-controllers.md)触发。那些对象以与 `beforeAction` 相反的顺序触发这个事件。也就是控制器第一个触发此事件，紧接着是模块（如果有），最后是应用对象。


## 应用生命周期 <a name="application-lifecycle"></a>

当[入口脚本](structure-entry-scripts.md)被执行来处理一个请求，应用对象将历经以下生命周期：

1. 入口脚本以数组形式加载应用配置。
2. 入口脚本创建一个新的应用实例：
  * [[yii\base\Application::preInit()|preInit()]] 被调用，用来配置一些高优先级的应用属性，例如[[yii\base\Application::basePath|（基本路径）]]。
  * 注册[[yii\base\Application::errorHandler|错误处理器]]。
  * 配置应用属性。
  * [[yii\base\Application::init()|init()]] 被调用后进而调用 [[yii\base\Application::bootstrap()|bootstrap()]] 执行组件引导。
3. 入口脚本调用 [[yii\base\Application::run()]] 执行应用：
  * 触发 [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] 事件。
  * 处理请求：解析请求至[路由](runtime-routing.md)和相关参数；依照路由指定创建模型，控制器和操作对象；执行操作。
  * 触发 [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] 事件。
  * 发送响应内容给用户。
4. 入口脚本从应用接受退出状态并且完成请求过程。
