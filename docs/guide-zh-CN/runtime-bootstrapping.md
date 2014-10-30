启动引导（Bootstrapping）
=============

启动引导是指：在应用开始解析并处理新接受请求之前，一个预先准备环境的过程。启动引导会在两个地方具体进行：[入口脚本(Entry Script)](structure-entry-scripts.md)
和 [应用主体（application）](structure-applications.md)。

在[入口脚本](structure-entry-scripts.md)里，需注册各个类库的类文件自动加载器（Class Autoloader，简称自动加载器）。这主要包括通过其 `autoload.php` 文件加载的
Composer 自动加载器，以及通过 `Yii` 类加载的 Yii 自动加载器。之后，入口脚本会加载应用的
[配置（configuration）](concept-configurations.md)
并创建一个 [应用主体](structure-applications.md) 的实例。

在应用主体的构造函数中，会执行以下引导工作：

1. 调用 [[yii\base\Application::preInit()|preInit()]]（预初始化）方法，配置一些高优先级的应用属性，比如 [[yii\base\Application::basePath|basePath]] 属性。
2. 注册[[yii\base\Application::errorHandler|错误处理器（ErrorHandler）]]。
3. 通过给定的应用配置初始化应用的各属性。
4. 通过调用 [[yii\base\Application::init()|init()]]（初始化）方法，它会顺次调用
   [[yii\base\Application::bootstrap()|bootstrap()]] 从而运行引导组件。
   - 加载扩展清单文件(extension manifest file) `vendor/yiisoft/extensions.php`。
   - 创建并运行各个扩展声明的 [引导组件（bootstrap components）](structure-extensions.md#bootstrapping-classes)。
   - 创建并运行各个 [应用组件](structure-application-components.md) 以及在应用的 [Bootstrap 属性](structure-applications.md#bootstrap)中声明的各个
     [模块（modules）组件](structure-modules.md)（如果有）。

因为引导工作必须在处理**每一次**请求之前都进行一遍，因此让该过程尽可能轻量化就异常重要，请尽可能地优化这一步骤。

请尽量不要注册太多引导组件。只有他需要在 HTTP 请求处理的全部生命周期中都作用时才需要使用它。举一个用到它的范例：一个模块需要注册额外的 URL 解析规则，就应该把它列在应用的
[bootstrap 属性](structure-applications.md#bootstrap)之中，这样该 URL 解析规则才能在解析请求之前生效。（译注：换言之，为了性能需要，除了 URL 
解析等少量操作之外，绝大多数组件都应该按需加载，而不是都放在引导过程中。）

在生产环境中，可以开启字节码缓存，比如 APC，来进一步最小化加载和解析 PHP 文件所需的时间。

一些大型应用都包含有非常复杂的应用[配置](concept-configurations.md)，它们会被分割到许多更小的配置文件中。此时，可以考虑将整个配置数组缓存起来，并在入口脚本创建应用实例之前直接从缓存中加载。
