应用主体
============

应用主体是管理Yii应用系统整体结构和生命周期的对象。
每个Yii应用系统只能包含一个应用主体，应用主体在 [入口脚本](structure-entry-scripts.md) 中创建并能通过表达式 `\Yii::$app` 全局范围内访问。
Applications are objects that govern the overall structure and lifecycle of Yii application systems.
Each Yii application system contains a single application object which is created in
the [entry script](structure-entry-scripts.md) and is globally accessible through the expression `\Yii::$app`.

> 补充: 当我们说"一个应用"，它可能是一个应用对象，也可能是一个应用系统，是根据上下文来决定。
> Info: Depending on the context, when we say "an application", it can mean either an application
  object or an application system.

Yii有两种应用主体: [[yii\web\Application|网页应用主体]] and
[[yii\console\Application|控制台应用主体]]， 如名称所示，前者主要处理网页请求，后者处理控制台请求。
There are two types of applications: [[yii\web\Application|Web applications]] and
[[yii\console\Application|console applications]]. As the names indicate, the former mainly handles
Web requests while the latter console command requests.


## 应用主体配置 <a name="application-configurations"></a>
## Application Configurations <a name="application-configurations"></a>

如下所示，当 [入口脚本](structure-entry-scripts.md) 创建了一个应用主体，它会加载一个 [配置](concept-configurations.md) 文件并传给应用主体。
When an [entry script](structure-entry-scripts.md) creates an application, it will load
a [configuration](concept-configurations.md) and apply it to the application, like the following:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// 加载应用主体配置
$config = require(__DIR__ . '/../config/web.php');

// 实例化应用主体、配置应用主体
(new yii\web\Application($config))->run();
```

类似其他 [配置](concept-configurations.md) 文件, 应用主体配置文件标明如何设置应用对象初始属性。
由于应用主体配置比较复杂，一般保存在多个类似如上web.php的 [配置文件](concept-configurations.md#configuration-files) 当中。
Like normal [configurations](concept-configurations.md), application configurations specify how
to initialize properties of application objects. Because application configurations are often
very complex, they usually are kept in [configuration files](concept-configurations.md#configuration-files),
like the `web.php` file in the above example.

## 应用主体属性 <a name="application-properties"></a>
## Application Properties <a name="application-properties"></a>

应用主体配置文件中有许多重要的属性要配置，这些属性指定应用主体的运行环境。
比如，应用主体需要知道如何加载 [控制器](structure-controllers.md) ，临时文件保存到哪儿等等。
以下我们简述这些属性。
There are many important application properties that you should configure in application configurations.
These properties typically describe the environment that applications are running in.
For example, applications need to know how to load [controllers](structure-controllers.md),
where to store temporary files, etc. In the following, we will summarize these properties.

### 必要属性 <a name="required-properties"></a>
### Required Properties <a name="required-properties"></a>

在一个应用中，至少要配置2个属性: [[yii\base\Application::id|id]] 和 [[yii\base\Application::basePath|basePath]]。
In any application, you should at least configure two properties: [[yii\base\Application::id|id]]
and [[yii\base\Application::basePath|basePath]].


#### [[yii\base\Application::id|id]] <a name="id"></a>

[[yii\base\Application::id|id]] 属性用来区分其他应用的唯一标识ID。主要给程序使用。
为了方便协作，最好使用数字作为应用主体ID，但不强制要求为数字。
The [[yii\base\Application::id|id]] property specifies a unique ID that differentiates an application
from others. It is mainly used programmatically. Although not a requirement, for best interoperability
it is recommended that you use alphanumeric characters only when specifying an application ID.


#### [[yii\base\Application::basePath|basePath]] <a name="basePath"></a>


[[yii\base\Application::basePath|basePath]] 指定该应用的根目录。根目录包含应用系统所有受保护的源代码。
在根目录下可以看到对应MVC设计模式的`models`, `views`, `controllers`等子目录。
The [[yii\base\Application::basePath|basePath]] property specifies the root directory of an application.
It is the directory that contains all protected source code of an application system. Under this directory,
you normally will see sub-directories such as `models`, `views`, `controllers`, which contain source code
corresponding to the MVC pattern.

可以使用路径或 [路径别名](concept-aliases.md) 来在配置 [[yii\base\Application::basePath|basePath]] 属性。
两种格式所对应的目录都必须存在，否则系统会抛出一个异常。 系统会使用 `realpath()` 函数规范化配置的路径.
You may configure the [[yii\base\Application::basePath|basePath]] property using a directory path
or a [path alias](concept-aliases.md). In both forms, the corresponding directory must exist, or an exception
will be thrown. The path will be normalized by calling the `realpath()` function.

[[yii\base\Application::basePath|basePath]] 属性经常用于派生一些其他重要路径（如runtime路径），因此，系统预定义 `@app` 代表这个路径。
派生路径可以通过这个别名组成（如`@app/runtime`代表runtime的路径）。
The [[yii\base\Application::basePath|basePath]] property is often used to derive other important
paths (e.g. the runtime path). For this reason, a path alias named `@app` is predefined to represent this
path. Derived paths may then be formed using this alias (e.g. `@app/runtime` to refer to the runtime directory).


### 重要属性 <a name="important-properties"></a>
### Important Properties <a name="important-properties"></a>

本小节所描述的属性通常需要设置，因为不用的应用属性不同。
The properties described in this subsection often need to be configured because they differ across
different applications.


#### [[yii\base\Application::aliases|aliases]] <a name="aliases"></a>

该属性允许你用一个数组定义多个 [别名](concept-aliases.md)。数组的key为别名名称，值为对应的路径。例如：
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

使用这个属性来定义别名，代替 [[Yii::setAlias()]] 方法来设置。
This property is provided such that you can define aliases in terms of application configurations instead of
the method calls [[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]] <a name="bootstrap"></a>

这个属性很实用，它允许你用数组指定启动阶段[[yii\base\Application::bootstrap()|bootstrapping process]]需要运行的组件。
比如，如果你希望一个 [模块](structure-modules.md) 自定义 [URL 规则](runtime-url-handling.md)，你可以将模块ID加入到bootstrap数组中。
This is a very useful property. It allows you to specify an array of components that should
be run during the application [[yii\base\Application::bootstrap()|bootstrapping process]].
For example, if you want a [module](structure-modules.md) to customize the [URL rules](runtime-url-handling.md),
you may list its ID as an element in this property.

属性中的每个组件需要指定以下一项:
Each component listed in this property may be specified in one of the following formats:

- 应用 [组件](#components) ID.
- [模块](#modules) ID.
- 类名.
- 配置数组.
- 创建并返回一个组件的无名称函数.
- an application component ID as specified via [components](#components).
- a module ID as specified via [modules](#modules).
- a class name.
- a configuration array.
- an anonymous function that creates and returns a component.

例如：
For example,

```php
[
    'bootstrap' => [
        // 应用组件ID或模块ID
        'demo',

        // 类名
        'app\components\Profiler',

        // 配置数组
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // 无名称函数
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> 补充: 如果模块ID和应用组件ID同名，优先使用应用组件ID，如果你想用模块ID，可以使用如下无名称函数返回模块ID。
> Info: If a module ID is the same as an application component ID, the application component will be used during
  the bootstrapping process. If you want to use the module instead, you may specify it using an anonymous function
  like the following:
>```php
[
    function () {
        return Yii::$app->getModule('user');
    },
]
```


在启动阶段，每个组件都会实例化。如果组件类实现接口 [[yii\base\BootstrapInterface]], 也会调用 [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] 方法。
During the bootstrapping process, each component will be instantiated. If the component class
implements [[yii\base\BootstrapInterface]], its [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method
will also be called.

举一个实际的例子，[Basic Application Template](start-installation.md) 应用主体配置中，开发环境下会在启动阶段运行 `debug` 和 `gii` 模块。
Another practical example is in the application configuration for the [Basic Application Template](start-installation.md),
where the `debug` and `gii` modules are configured as bootstrapping components when the application is running
in development environment,

```php
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> 注: 启动太多的组件会降低系统性能，因为每次请求都需要重新运行启动组件，因此谨慎配置启动组件。
> Note: Putting too many components in `bootstrap` will degrade the performance of your application because
  for each request, the same set of components need to be run. So use bootstrapping components judiciously.


#### [[yii\web\Application::catchAll|catchAll]] <a name="catchAll"></a>

该属性仅 [[yii\web\Application|Web applications]] 网页应用支持。
它指定一个要处理所有用户请求的 [控制器方法](structure-controllers.md)，通常在维护模式下使用，同一个方法处理所有用户请求。
This property is supported by [[yii\web\Application|Web applications]] only. It specifies
a [controller action](structure-controllers.md) which should handle all user requests. This is mainly
used when the application is in maintenance mode and needs to handle all incoming requests via a single action.

该配置为一个数组，第一项指定动作的路由，剩下的数组项(key-value 成对)指定传递给动作的参数，例如：
The configuration is an array whose first element specifies the route of the action.
The rest of the array elements (key-value pairs) specify the parameters to be bound to the action. For example,

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <a name="components"></a>

这是最重要的属性，它允许你注册多个在其他地方使用的[应用组件](#structure-application-components.md). 例如
This is the single most important property. It allows you to register a list of named components
called [application components](#structure-application-components.md) that you can use in other places. For example,

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

每一个应用组件指定一个key-value对的数组，key代表组件ID，value代表组件类名或 [配置](concept-configurations.md)。
Each application component is specified as a key-value pair in the array. The key represents the component ID,
while the value represents the component class name or [configuration](concept-configurations.md).

在应用中可以任意注册组件，并可以通过表达式 `\Yii::$app->ComponentID` 全局访问。
You can register any component with an application, and the component can later be accessed globally
using the expression `\Yii::$app->ComponentID`.

详情请阅读 [应用组件](structure-application-components.md) 一节.


#### [[yii\base\Application::controllerMap|controllerMap]] <a name="controllerMap"></a>

该属性允许你指定一个控制器ID到任意控制器类。Yii遵循一个默认的 [规则](#controllerNamespace) 指定控制器ID到任意控制器类（如`post`对应`app\controllers\PostController`）。
通过配置这个属性，可以打破这个默认规则，在下面的例子中，`account`对应到`app\controllers\UserController`，
`article` 对应到 `app\controllers\PostController`。
This property allows you to map a controller ID to an arbitrary controller class. By default, Yii maps
controller IDs to controller classes based on a [convention](#controllerNamespace) (e.g. the ID `post` would be mapped
to `app\controllers\PostController`). By configuring this property, you can break the convention for
specific controllers. In the following example, `account` will be mapped to
`app\controllers\UserController`, while `article` will be mapped to `app\controllers\PostController`.

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

数组的键代表控制器ID，数组的值代表对应的类名。
The array keys of this property represent the controller IDs, while the array values represent the corresponding
controller class names or [configurations](concept-configurations.md).


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <a name="controllerNamespace"></a>

该属性指定控制器类默认的命名空间，默认为`app\controllers`。比如控制器ID为 `post` 默认对应 `PostController` （不带命名空间），
类全名为 `app\controllers\PostController`。
This property specifies the default namespace under which controller classes should be located. It defaults to
`app\controllers`. If a controller ID is `post`, by convention the corresponding controller class name (without
namespace) would be `PostController`, and the fully qualified class name would be `app\controllers\PostController`.

控制器类文件可能放在这个命名空间对应目录的子目录下，例如，控制器ID `admin/post` 对应的控制器类全名为 `app\controllers\admin\PostController`。
Controller classes may also be located under sub-directories of the directory corresponding to this namespace.
For example, given a controller ID `admin/post`, the corresponding fully qualified controller class would
be `app\controllers\admin\PostController`.

控制器类全面能被 [自动加载](concept-autoloading.md)，这点是非常重要的，控制器类的实际命名空间对应这个属性，
否则，访问时你会收到"Page Not Found"[译：页面找不到]。
It is important that the fully qualified controller classes should be [autoloadable](concept-autoloading.md)
and the actual namespace of your controller classes match the value of this property. Otherwise,
you will receive "Page Not Found" error when accessing the application.

如果你想打破上述的规则，可以配置 [controllerMap](#controllerMap) 属性。
In case you want to break the convention as described above, you may configure the [controllerMap](#controllerMap)
property.


#### [[yii\base\Application::language|language]] <a name="language"></a>

该属性指定应用展示给终端用户的语言，默认为 `en` 标识英文。如果需要之前其他语言可以配置该属性。
This property specifies the language in which the application should display content to end users.
The default value of this property is `en`, meaning English. You should configure this property
if your application needs to support multiple languages.

该属性影响各种 [国际化](tutorial-i18n.md) ，包括信息翻译、日期格式、数字格式等。
例如 [[yii\jui\DatePicker]] 小部件会根据该属性展示对应语言的日历以及日期格式。
The value of this property determines various [internationalization](tutorial-i18n.md) aspects,
including message translation, date formatting, number formatting, etc. For example, the [[yii\jui\DatePicker]] widget
will use this property value by default to determine in which language the calendar should be displayed and how
should the date be formatted.

推荐遵循 [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag) 来设置语言，例如 `en` 代表英文， `en-US` 代表英文(美国).
It is recommended that you specify a language in terms of an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag).
For example, `en` stands for English, while `en-US` stands for English (United States).

该属性的更多信息可参考 [国际化](tutorial-i18n.md) 一节.


#### [[yii\base\Application::modules|modules]] <a name="modules"></a>

该属性指定应用所包含的 [模块](structure-modules.md)。
This property specifies the [modules](structure-modules.md) that the application contains.

该属性使用数组包含多个模块类 [配置](concept-configurations.md)，数组的键为模块ID，例：
The property takes an array of module classes or [configurations](concept-configurations.md) with the array keys
being the module IDs. For example,

```php
[
    'modules' => [
        // "booking" 模块以及对应的类
        'booking' => 'app\modules\booking\BookingModule',

        // "comment" 模块以及对应的配置数组
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

更多详情请参考 [模块](structure-modules.md) 一节。


#### [[yii\base\Application::name|name]] <a name="name"></a>

该属性指定你可能想展示给终端用户的应用名称，不同于需要唯一性的 [[yii\base\Application::id|id]] 属性，
该属性可以不唯一，该属性用于显示应用的用途。
This property specifies the application name that may be displayed to end users. Unlike the
[[yii\base\Application::id|id]] property which should take a unique value, the value of this property is mainly for
display purpose and does not need to be unique.

如果其他地方的代码没有用到，可以不配置该属性。
You do not always need to configure this property if none of your code is using it.


#### [[yii\base\Application::params|params]] <a name="params"></a>

该属性为一个数组，指定可以全局访问的参数，代替程序中硬编码的数字和字符，应用中的参数定义到一个单独的文件并随时可以访问是一个好习惯。
例如用参数定义缩略图的长宽如下：
This property specifies an array of globally accessible application parameters. Instead of using hardcoded
numbers and strings everywhere in your code, it is a good practice to define them as application parameters
in a single place and use the parameters in places where needed. For example, you may define the thumbnail
image size as a parameter like the following:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

然后简单的使用如下代码即可获取到你需要的长宽参数：
Then in your code where you need to use the size value, you can simply use the code like the following:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

以后想修改缩略图长宽，只需要修改该参数而不需要相关的代码。
Later if you decide to change the thumbnail size, you only need to modify it in the application configuration
without touching any dependent code.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <a name="sourceLanguage"></a>

该属性指定应用代码的语言，默认为 `'en-US'` 标识英文（美国），如果应用不是英文请修改该属性。
This property specifies the language that the application code is written in. The default value is `'en-US'`,
meaning English (United States). You should configure this property if the text content in your code is not in English.

和 [语言](#language) 属性类似，配置该属性需遵循 [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag). 例如 `en` 代表英文， `en-US` 代表英文(美国)。
Like the [language](#language) property, you should configure this property in terms of
an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands for English,
while `en-US` stands for English (United States).

该属性的更多信息可参考 [国际化](tutorial-i18n.md) 一节.
More details about this property can be found in the [Internationalization](tutorial-i18n.md) section.


#### [[yii\base\Application::timeZone|timeZone]] <a name="timeZone"></a>

该属性提供一种方式修改PHP运行环境中的默认时区，配置该属性本质上就是调用PHP函数
[date_default_timezone_set()](http://php.net/manual/en/function.date-default-timezone-set.php)，例如：
This property is provided as an alternative way of setting the default time zone of PHP runtime.
By configuring this property, you are essentially calling the PHP function
[date_default_timezone_set()](http://php.net/manual/en/function.date-default-timezone-set.php). For example,

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <a name="version"></a>

该属性指定应用的版本，默认为`'1.0'`，其他代码不使用的话可以不配置。
This property specifies the version of the application. It defaults to `'1.0'`. You do not always need to configure
this property if none of your code is using it.


### 实用属性 <a name="useful-properties"></a>

本小节描述的属性不经常设置，通常使用系统默认值。如果你想改变默认值，可以配置这些属性。
The properties described in this subsection are not commonly configured because their default values
stipulate common conventions. However, you may still configure them in case you want to break the conventions.


#### [[yii\base\Application::charset|charset]] <a name="charset"></a>

该属性指定应用使用的字符集，默认值为 `'UTF-8'`，绝大部分应用都在使用，除非已有的系统大量使用非unicode数据才需要更改该属性。
This property specifies the charset that the application uses. The default value is `'UTF-8'` which should
be kept as is for most applications unless you are working with some legacy systems that use a lot of non-unicode data.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <a name="defaultRoute"></a>

该属性指定未配置的请求的响应 [路由](runtime-routing.md) 规则，路由规则可能包含模块ID，控制器ID，动作ID。
例如`help`, `post/create`, `admin/post/create`，如果动作ID没有指定，会使用[[yii\base\Controller::defaultAction]]中指定的默认值。
This property specifies the [route](runtime-routing.md) that an application should use when a request
does not specify one. The route may consist of child module ID, controller ID, and/or action ID.
For example, `help`, `post/create`, `admin/post/create`. If action ID is not given, it will take the default
value as specified in [[yii\base\Controller::defaultAction]].

对于 [[yii\web\Application|Web applications]] 网页应用，默认值为 `'site'` 对应 `SiteController` 控制器，并使用默认的动作。
因此你不带路由的访问应用，默认会显示 `app\controllers\SiteController::actionIndex()` 的结果。
For [[yii\web\Application|Web applications]], the default value of this property is `'site'`, which means
the `SiteController` controller and its default action should be used. As a result, if you access
the application without specifying a route, it will show the result of `app\controllers\SiteController::actionIndex()`.

对于 [[yii\console\Application|console applications]] 控制台应用，默认值为 `'help'` 对应 [[yii\console\controllers\HelpController::actionIndex()]]。
因此，如果执行的命令不带参数，默认会显示帮助信息。
For [[yii\console\Application|console applications]], the default value is `'help'`, which means the core command
[[yii\console\controllers\HelpController::actionIndex()]] should be used. As a result, if you run the command `yii`
without providing any arguments, it will display the help information.


#### [[yii\base\Application::extensions|extensions]] <a name="extensions"></a>

该属性用数组列表指定应用安装和使用的 [扩展](structure-extensions.md)，默认使用`@vendor/yiisoft/extensions.php`文件返回的数组。
当你使用 [Composer](http://getcomposer.org) 安装扩展，`extensions.php` 会被自动生成和维护更新。
所以大多数情况下，不需要配置该属性。
This property specifies the list of [extensions](structure-extensions.md) that are installed and used by the application.
By default, it will take the array returned by the file `@vendor/yiisoft/extensions.php`. The `extensions.php` file
is generated and maintained automatically when you use [Composer](http://getcomposer.org) to install extensions.
So in most cases, you do not need to configure this property.

特殊情况下你想自己手动维护扩展，可以参照如下配置该属性：
In the special case when you want to maintain extensions manually, you may configure this property like the following:

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // 可选配，可为配置数组
            'alias' => [  // 可选配
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... 更多像上面的扩展 ...

    ],
]
```

如上所示，该属性包含一个扩展定义数组，每个扩展为一个包含 `name` 和 `version` 项的数组。
如果扩展要在 [引导启动](runtime-bootstrapping.md) 阶段运行，需要配置 `bootstrap`以及对应的引导启动类名或 [configuration](concept-configurations.md) 数组。
扩展也可以定义 [别名](concept-aliases.md)
As you can see, the property takes an array of extension specifications. Each extension is specified with an array
consisting of `name` and `version` elements. If an extension needs to run during the [bootstrap](runtime-bootstrapping.md)
process, a `bootstrap` element may be specified with a bootstrapping class name or a [configuration](concept-configurations.md)
array. An extension may also define a few [aliases](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <a name="layout"></a>

该属性指定渲染 [视图](structure-views.md) 默认使用的布局名字，默认值为 `'main'` 对应[布局路径](#layoutPath)下的 `main.php` 文件，
如果 [布局路径](#layoutPath) 和 [视图路径](#viewPath) 都是默认值，默认布局文件可以使用路径别名`@app/views/layouts/main.php`
This property specifies the name of the default layout that should be used when rendering a [view](structure-views.md).
The default value is `'main'`, meaning the layout file `main.php` under the [layout path](#layoutPath) should be used.
If both of the [layout path](#layoutPath) and the [view path](#viewPath) are taking the default values,
the default layout file can be represented as the path alias `@app/views/layouts/main.php`.

如果不想设置默认布局文件，可以设置该属性为 `false`，这种做法比较罕见。
You may configure this property to be `false` if you want to disable layout by default, although this is very rare.


#### [[yii\base\Application::layoutPath|layoutPath]] <a name="layoutPath"></a>

该属性指定查找布局文件的路径，默认值为 [视图路径](#viewPath) 下的 `layouts` 子目录。
如果 [视图路径](#viewPath) 使用默认值，默认的布局路径别名为`@app/views/layouts`。
This property specifies the path where layout files should be looked for. The default value is
the `layouts` sub-directory under the [view path](#viewPath). If the [view path](#viewPath) is taking
its default value, the default layout path can be represented as the path alias `@app/views/layouts`.

该属性需要配置成一个目录或 路径 [别名](concept-aliases.md)。
You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::runtimePath|runtimePath]] <a name="runtimePath"></a>

该属性指定临时文件如日志文件、缓存文件等保存路径，默认值为带别名的 `@app/runtime`。
This property specifies the path where temporary files, such as log files, cache files, can be generated.
The default value is the directory represented by the alias `@app/runtime`.

可以配置该属性为一个目录或者路径 [别名](concept-aliases.md)，注意应用运行时有对该路径的写入权限，
以及终端用户不能访问改路径因为临时文件可能包含一些敏感信息。
You may configure it as a directory or a path [alias](concept-aliases.md). Note that the runtime path must
be writable by the process running the application. And the path should be protected from being accessed
by end users because the temporary files under it may contain sensitive information.

为了简化访问该路径，Yii预定义别名 `@runtime` 代表该路径。
To simplify accessing to this path, Yii has predefined a path alias named `@runtime` for it.


#### [[yii\base\Application::viewPath|viewPath]] <a name="viewPath"></a>

该路径指定视图文件的根目录，默认值为带别名的 `@app/views`，可以配置它为一个目录或者路径 [别名](concept-aliases.md).
This property specifies the root directory where view files are located. The default value is the directory
represented by the alias `@app/views`. You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::vendorPath|vendorPath]] <a name="vendorPath"></a>

该属性指定 [Composer](http://getcomposer.org) 管理的供应商路径，该路径包含应用使用的包括Yii框架在内的所有第三方库。
默认值为带别名的 `@app/vendor` 。
This property specifies the vendor directory managed by [Composer](http://getcomposer.org). It contains
all third party libraries used by your application, including the Yii framework. The default value is
the directory represented by the alias `@app/vendor`.

可以配置它为一个目录或者路径 [别名](concept-aliases.md)，当你修改时，务必修改对应的 Composer 配置。
You may configure this property as a directory or a path [alias](concept-aliases.md). When you modify
this property, make sure you also adjust the Composer configuration accordingly.

为了简化访问该路径，Yii预定义别名 `@vendor` 代表该路径。
To simplify accessing to this path, Yii has predefined a path alias named `@vendor` for it.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <a name="enableCoreCommands"></a>

该属性仅 [[yii\console\Application|console applications]] 控制台应用支持， 用来指定是否启用Yii中的核心命令，默认值为 `true`。
This property is supported by [[yii\console\Application|console applications]] only. It specifies
whether the core commands included in the Yii release should be enabled. The default value is `true`.


## Application Events <a name="application-events"></a>

应用在处理请求过程中会触发事件，可以在配置文件配置事件处理代码，如下所示：
An application triggers several events during the lifecycle of handling an request. You may attach event
handlers to these events in application configurations like the following,

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

`on eventName` 语法的用法在 [Configurations](concept-configurations.md#configuration-format) 一节有详细描述.
The use of the `on eventName` syntax is described in the [Configurations](concept-configurations.md#configuration-format)
section.

另外，在应用主体实例化后，你可以在[引导启动](runtime-bootstrapping.md) 阶段附加事件处理代码，例如：
Alternatively, you may attach event handlers during the [bootstrapping process](runtime-bootstrapping.md) process
after the application instance is created. For example,

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <a name="beforeRequest"></a>

该事件在应用处理请求*before*之前，实际的事件名为 `beforeRequest`。
This event is triggered *before* an application handles a request. The actual event name is `beforeRequest`.

在事件触发前，应用主体已经实例化并配置好了，所以通过事件机制将你的代码嵌入到请求处理过程中非常不错。
例如在事件处理中根据某些参数动态设置[[yii\base\Application::language]]语言属性。 
When this event is triggered, the application instance has been configured and initialized. So it is a good place
to insert your custom code via the event mechanism to intercept the request handling process. For example,
in the event handler, you may dynamically set the [[yii\base\Application::language]] property based on some parameters.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_REQUEST]] <a name="afterRequest"></a>

该事件在应用处理请求*after*之后但在返回响应*before*之前触发，实际的事件名为`afterRequest`。
This event is triggered *after* an application finishes handling a request but *before* sending the response.
The actual event name is `afterRequest`.

该事件触发时，请求已经被处理完，可以做一些请求后处理或自定义响应。 
When this event is triggered, the request handling is completed and you may take this chance to do some postprocessing
of the request or customize the response.

注意 [[yii\web\Response|response]] 组件在发送响应给终端用户时也会触发一些事件，这些事件都在本事件*after*之后触发。
Note that the [[yii\web\Response|response]] component also triggers some events while it is sending out
response content to end users. Those events are triggered *after* this event.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_ACTION]] <a name="beforeAction"></a>

该事件在每个 [控制器动作](structure-controllers.md) 运行*before*之前会被触发，实际的事件名为 `beforeAction`.

事件的参数为一个 [[yii\base\ActionEvent]] 实例，
事件处理中可以设置[[yii\base\ActionEvent::isValid]] 为 `false` 停止运行后续动作，例如：
The event parameter is an instance of [[yii\base\ActionEvent]]. An event handler may set
the [[yii\base\ActionEvent::isValid]] property to be `false` to stop running the action.
For example,

```php
[
    'on beforeAction' => function ($event) {
        if (some condition) {
            $event->isValid = false;
        } else {
        }
    },
]
```

注意 [模块](structure-modules.md) 和 [控制器](structure-controllers.md) 都会触发 `beforeAction` 事件。
应用主体对象首先触发该事件，然后模块触发（如果存在模块），最后控制器触发。
任何一个事件处理中设置 [[yii\base\ActionEvent::isValid]] 设置为 `false` 会停止触发后面的事件。
Note that the same `beforeAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). Application objects are the first ones
triggering this event, followed by modules (if any), and finally controllers. If an event handler
sets [[yii\base\ActionEvent::isValid]] to be `false`, all the following events will NOT be triggered.


### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_AFTER_ACTION]] <a name="afterAction"></a>

该事件在每个 [控制器动作](structure-controllers.md) 运行*after*之后会被触发，实际的事件名为 `afterAction`.
This event is triggered *after* running every [controller action](structure-controllers.md).
The actual event name is `afterAction`.

该事件的参数为 [[yii\base\ActionEvent]] 实例，通过[[yii\base\ActionEvent::result]]属性，事件处理可以访问和修改动作的结果。例如：
The event parameter is an instance of [[yii\base\ActionEvent]]. Through
the [[yii\base\ActionEvent::result]] property, an event handler may access or modify the action result.
For example,

```php
[
    'on afterAction' => function ($event) {
        if (some condition) {
            // 修改 $event->result
        } else {
        }
    },
]
```

注意 [模块](structure-modules.md) 和 [控制器](structure-controllers.md) 都会触发 `afterAction` 事件。
这些对象的触发顺序和 `beforeAction` 相反，也就是说，控制器最先触发，然后是模块（如果有模块），最后为应用主体。Note that the same `afterAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). These objects trigger this event in the reverse order
as for that of `beforeAction`. That is, controllers are the first objects triggering this event,
followed by modules (if any), and finally applications.


## 应用主体生命周期 <a name="application-lifecycle"></a>
## Application Lifecycle <a name="application-lifecycle"></a>

当运行 [入口脚本](structure-entry-scripts.md) 处理请求时，应用主体会经历以下生命周期:
When an [entry script](structure-entry-scripts.md) is being executed to handle a request,
an application will undergo the following lifecycle:

1. 入口脚本加载应用主体配置数组。
2. 入口脚本创建一个应用主体实例：
  * 调用 [[yii\base\Application::preInit()|preInit()]] 配置几个高级别应用主体属性，比如[[yii\base\Application::basePath|basePath]]。
  * 注册 [[yii\base\Application::errorHandler|error handler]] 错误处理方法.
  * 配置应用主体属性.
  * 调用 [[yii\base\Application::init()|init()]] 初始化，该函数会调用 [[yii\base\Application::bootstrap()|bootstrap()]] 运行引导启动组件.
3. 入口脚本调用 [[yii\base\Application::run()]] 运行应用主体:
  * 触发 [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] 事件。
  * 处理请求：解析请求 [路由](runtime-routing.md) 和相关参数；创建路由指定的模块、控制器和动作对应的类，并运行动作。
  * 触发 [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] 事件。
  * 发送响应到终端用户.
4. 入口脚本接收应用主体传来的退出状态并完成请求的处理。
1. The entry script loads the application configuration as an array.
2. The entry script creates a new instance of the application:
  * [[yii\base\Application::preInit()|preInit()]] is called, which configures some high priority
    application properties, such as [[yii\base\Application::basePath|basePath]].
  * Register the [[yii\base\Application::errorHandler|error handler]].
  * Configure application properties.
  * [[yii\base\Application::init()|init()]] is called which further calls
    [[yii\base\Application::bootstrap()|bootstrap()]] to run bootstrapping components.
3. The entry script calls [[yii\base\Application::run()]] to run the application:
  * Trigger the [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] event.
  * Handle the request: resolve the request into a [route](runtime-routing.md) and the associated parameters;
    create the module, controller and action objects as specified by the route; and run the action.
  * Trigger the [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] event.
  * Send response to the end user.
4. The entry script receives the exit status from the application and completes the request processing.
