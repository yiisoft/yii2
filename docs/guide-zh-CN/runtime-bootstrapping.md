启动引导（Bootstrapping）
=============

启动引导是指在一个应用开始解析并处理新增请求之前的环境准备的过程。引导主要在两个地方具体进行：[入口脚本(Entry Script)](structure-entry-scripts.md)
以及 [应用主体（application）](structure-applications.md)。

Bootstrapping refers to the process of preparing the environment before an application starts
to resolve and process an incoming request. Bootstrapping is done in two places:
the [entry script](structure-entry-scripts.md) and the [application](structure-applications.md).

在[入口脚本](structure-entry-scripts.md)里，需注册各个类库的类文件自动加载器（Class Autoloader）。这主要包括通过其 `autoload.php` 文件加载的
Composer 自动加载器，以及通过 `Yii` 类文件加载的 Yii 自动加载器。之后，入口脚本会加载应用的
[配置（configuration）](concept-configurations.md)
并创建一个 [应用主体](structure-applications.md) 的实例。

In the [entry script](structure-entry-scripts.md), class autoloaders for different libraries are
registered. This includes the Composer autoloader through its `autoload.php` file and the Yii
autoloader through its `Yii` class file. The entry script then loads the application
[configuration](concept-configurations.md) and creates an [application](structure-applications.md) instance.

在应用主体的构造器中，会执行以下引导工作：

In the constructor of the application, the following bootstrapping work are done:

1. 调用 [[yii\base\Application::preInit()|preInit()]]（预初始化）方法，配置一些高优先级的应用属性，比如 [[yii\base\Application::basePath|basePath]]。
2. 注册 [[yii\base\Application::errorHandler|错误处理器（ErrorHandler）]]。
3. 通过给定应用配置初始化各应用属性。
4. 通过调用 [[yii\base\Application::init()|init()]]（初始化）方法，它会依次调用
   [[yii\base\Application::bootstrap()|bootstrap()]] 从而运行引导组件。
   - 加载扩展清单文件(extension manifest file) `vendor/yiisoft/extensions.php`。
   - 创建并运行各个扩展声明的 [引导组件（bootstrap components）](structure-extensions.md#bootstrapping-classes)。
   - 创建并运行各个 [应用组件](structure-application-components.md) 以及在应用的 [Bootstrap 属性](structure-applications.md#bootstrap)中声明的各个
     [模块（modules）组件](structure-modules.md)（如果有）。

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

因为引导工作必须在处理**每一次**请求之前都进行一遍，因此让该过程尽可能轻量化异常关键，请尽可能地优化这一步骤。

Because the bootstrapping work has to be done before handling *every* request, it is very important
to keep this process light and optimize it as much as possible.

请尽量不要注册太多引导组件。只有他需要在 HTTP 请求处理的全部生命周期中都作用时才需要使用它。举一个用到它的范例：一个模块需要注册额外的 URL 解析规则，就应该把它列在应用的
[bootstrap 属性](structure-applications.md#bootstrap)之中，这样该 URL 解析规则才能在解析请求之前生效。（译者注：换言之，为了性能需要，除了 URL 
解析等少量操作之外，绝大多数组件都应该按需加载，而不是都放在引导过程中。）

Try not to register too many bootstrapping components. A bootstrapping component is needed only
if it wants to participate the whole life cycle of requesting handling. For example, if a module
needs to register additional URL parsing rules, it should be listed in the
[bootstrap property](structure-applications.md#bootstrap) so that the new URL rules can take effect
before they are used to resolve requests.

在胜出安静中，可以开启字节码缓存，比如 APC，来进一步最小化加载和解析 PHP 文件所需的时间。

In production mode, enable bytecode cache, such as APC, to minimize the time needed for including
and parsing PHP files.

一些大型应用都包含有非常复杂的应用[配置](concept-configurations.md)，它们会被分割到许多更小的配置文件中。此时，可以考虑将整个配置数组缓存起来，并在入口脚本创建应用实例之前直接从缓存中加载。

Some large applications have very complex application [configurations](concept-configurations.md)
which are divided into many smaller configuration files. If this is the case, consider caching
the whole configuration array and loading it directly from cache before creating the application instance
in the entry script.
