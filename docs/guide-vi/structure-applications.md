Ứng dụng
============

Mỗi ứng dụng là một đối tượng giúp quản lý tổng thể cấu trúc và vòng đời của ứng dụng Yii.
Mỗi ứng dụng Yii đều chứa một đối tượng ứng dụng, đối tượng này được khởi tạo tại mục
[entry script](structure-entry-scripts.md) và đồng thời được truy cập qua biểu thức `\Yii::$app`.

> Gợi ý: Phụ thuộc vào từng ngữ cảnh, có khi chúng ta gọi là "một application", có nghĩa là một đối tượng ứng dụng
  hoặc một hệ thống ứng dụng.

Có 2 kiểu ứng dụng: [[yii\web\Application|Ứng dụng Web]] và
[[yii\console\Application|ứng dụng giao diện dòng lệnh]]. Tương tự như vậy, ứng dụng Web xử lý với các yêu cầu về Web,
, ứng dụng còn lại sẽ xử lý với các yêu cầu ở giao diện dòng lệnh.


## Cấu hình ứng dụng <span id="application-configurations"></span>

Mỗi khi [entry script](structure-entry-scripts.md) tạo ứng dụng mới, nó sẽ tải thêm thông tin về
[cấu hình](concept-configurations.md) và gán vào trong ứng dụng, như sau:

```php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

// tải các cấu hình ứng dụng
$config = require(__DIR__ . '/../config/web.php');

// gán cấu hình và khởi tạo ứng dụng
(new yii\web\Application($config))->run();
```

Thông thường việc [cấu hình](concept-configurations.md), ứng dụng sẽ xác định làm thế nào để
khởi tạo các thuộc tính và đối tượng ứng dụng. Do việc cấu hình ứng dụng khá phức tạp nên vậy
, chúng thường được lưu giữ tại [các file cấu hình](concept-configurations.md#configuration-files),
như file `web.php` ở ví dụ trên.


## Các thuộc tính của ứng dụng <span id="application-properties"></span>

Có nhiều thuộc tính quan trọng mà bạn cần phải cấu hình trong ứng dụng. Những thuộc tính này
thường được mô tả về môi trường mà ứng dụng đang chạy. Chẳng hạn, ứng dụng cần biết làm thế nào để tải các [controllers](structure-controllers.md),
nơi lưu trữ các file tạm, vv. Trong phần dưới này, chúng ta sẽ tổng hợp thông tin về thuộc tính.


### Thuộc tính bắt buộc <span id="required-properties"></span>

Ở mỗi ứng dụng, bạn cần cấu hình ít nhất 2 thuộc tính là: [[yii\base\Application::id|id]]
và [[yii\base\Application::basePath|basePath]].


#### [[yii\base\Application::id|id]] <span id="id"></span>

Thuộc tính [[yii\base\Application::id|id]] giúp đặc tả một định danh ID để phân biệt với các ứng dụng khác
. Thuộc tính chủ yếu được sử dụng trong chương trình. Mặc dù nó không được yêu cầu, để thích hợp cho khả năng tương tác
nên chỉ sử dụng các chữ cái chữ số khi mô tả một định danh của ứng dụng.


#### [[yii\base\Application::basePath|basePath]] <span id="basePath"></span>

Thuộc tính [[yii\base\Application::basePath|basePath]] dùng để mô tả thư mục gốc của ứng dụng.
Nó là thư mục chứa tất cả mã nguồn của ứng dụng. Bên trong thư mục,
bạn sẽ thấy các thư mục con như `models`, `views`, và `controllers`, các thư mục con này chứa các mã nguồn
tương ứng với các thành phần trong mô hình MVC.

Bạn phải cấu hình thuộc tính [[yii\base\Application::basePath|basePath]] bằng sử dụng các đường dẫn trực tiếp
hoặc [một bí danh](concept-aliases.md). Trong các trường hợp, các thư mục tương ứng phải tồn tại, nếu không sẽ phát sinh ra lỗi
. Đường dẫn trực tiếp được lấy qua việc gọi hàm `realpath()` .

Thuộc tính [[yii\base\Application::basePath|basePath]] thường được dùng để lấy được các đường dẫn quan trọng khác
(vd đường dẫn dành cho thực thi). Vì vậy, bí danh `@app` được xác định là đường dẫn gốc 
. Các đường dẫn trong ứng dụng được lấy từ bí danh (vd `@app/runtime` tương ứng tới đường dẫn mục runtime).


### Các thuộc tính quan trọng <span id="important-properties"></span>

Các thuộc tính được mô tả trong phần này thường cần được cấu hình bởi vì mỗi ứng dụng có
các thuộc tính khác nhau.


#### [[yii\base\Application::aliases|aliases]] <span id="aliases"></span>

Thuộc tính cho phép khai báo các [bí danh(aliases)](concept-aliases.md) vào trong một mảng.
Các khóa lưu trữ tên bí danh, và giá trị trong mảng tương ứng với đường dẫn được khai báo.
Ví dụ:

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

Thuộc tính này được cung cấp cho bạn việc khai báo các bí danh trong cấu hình ứng dụng thay vì gọi phương thức
[[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]] <span id="bootstrap"></span>

Thuộc tính này khá quan trọng. Nó cung cấp cho bạn thông tin về mảng các thành phần (components) mà cần được 
chạy trong suốt chu trình ứng dụng [[yii\base\Application::bootstrap()|bootstrapping process]].
Ví dụ, nếu bạn muốn một [module](structure-modules.md) dùng để tùy biến các [URL](runtime-routing.md),
bạn có thể tùy biến các ID như phần tử trong các thuộc tính.

Mỗi thành phần được liệt kê ra có thể khai báo một trong các định dạng sau:

- một đinh danh về thành phần được tuân thủ qua [components](#components),
- một định danh về module tuân thủ theo quy định về [modules](#modules),
- một tên class,
- một mảng các cấu hình,
- một hàm dùng để khởi tạo và trả về một thành phần.

Ví dụ:

```php
[
    'bootstrap' => [
        // một định danh về thành phần hoặc module
        'demo',

        // tên class
        'app\components\Profiler',

        // mảng cấu hình
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // hàm trả về một thành phần
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> Lưu ý: Nếu định danh của module trùng với định danh của thành phần , ứng dụng sẽ sử dụng
> trong suốt tiền trình xử lý. Nếu bạn muốn chỉ sử dụng mỗi module, bạn cần lấy nó ở một hàm khác
> như sau:
>
> ```php
> [
>     function () {
>         return Yii::$app->getModule('user');
>     },
> ]
> ```


Trong suốt quá trình xử lý, mỗi thành phần sẽ được khởi tạo. nếu lớp thành phần được hiện thực từ giao diện
[[yii\base\BootstrapInterface]], thì phương thức [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]]
sẽ đồng thời được gọi.

Một ví dụ khác trong việc cấu hình ứng dụng trong [Mẫu Basic Project](start-installation.md),
module `debug` và `gii`  được cấu hình như những thành phần khi ứng dụng khởi chạy
ở môi trường phát triển:

```php
if (YII_ENV_DEV) {
    // cấu hình được thiết lập trong môi trường phát triển 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> Lưu ý: Việc đưa quá nhiều các thành phần vào `bootstrap` sẽ làm giảm hiệu năng trong ứng dụng, bởi vì
  mỗi khi có yêu cầu, các thành phần sẽ được chạy. Vì vậy việc sử dụng các thành phần cần sử dụng một cách khôn ngoan.


#### [[yii\web\Application::catchAll|catchAll]] <span id="catchAll"></span>

Thuộc tính này chỉ được hỗ trợ với [[yii\web\Application| ứng dụng Web]]. Nó mô tả một
 [hành động](structure-controllers.md) which should handle all user requests. This is mainly
used when the application is in maintenance mode and needs to handle all incoming requests via a single action.

The configuration is an array whose first element specifies the route of the action.
The rest of the array elements (key-value pairs) specify the parameters to be bound to the action. For example:

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <span id="components"></span>

This is the single most important property. It allows you to register a list of named components
called [application components](structure-application-components.md) that you can use in other places. For example:

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

Each application component is specified as a key-value pair in the array. The key represents the component ID,
while the value represents the component class name or [configuration](concept-configurations.md).

You can register any component with an application, and the component can later be accessed globally
using the expression `\Yii::$app->componentID`.

Please read the [Application Components](structure-application-components.md) section for details.


#### [[yii\base\Application::controllerMap|controllerMap]] <span id="controllerMap"></span>

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

The array keys of this property represent the controller IDs, while the array values represent the corresponding
controller class names or [configurations](concept-configurations.md).


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <span id="controllerNamespace"></span>

This property specifies the default namespace under which controller classes should be located. It defaults to
`app\controllers`. If a controller ID is `post`, by convention the corresponding controller class name (without
namespace) would be `PostController`, and the fully qualified class name would be `app\controllers\PostController`.

Controller classes may also be located under sub-directories of the directory corresponding to this namespace.
For example, given a controller ID `admin/post`, the corresponding fully qualified controller class would
be `app\controllers\admin\PostController`.

It is important that the fully qualified controller classes should be [autoloadable](concept-autoloading.md)
and the actual namespace of your controller classes match the value of this property. Otherwise,
you will receive a "Page Not Found" error when accessing the application.

In case you want to break the convention as described above, you may configure the [controllerMap](#controllerMap)
property.


#### [[yii\base\Application::language|language]] <span id="language"></span>

This property specifies the language in which the application should display content to end users.
The default value of this property is `en`, meaning English. You should configure this property
if your application needs to support multiple languages.

The value of this property determines various [internationalization](tutorial-i18n.md) aspects,
including message translation, date formatting, number formatting, etc. For example, the [[yii\jui\DatePicker]] widget
will use this property value by default to determine in which language the calendar should be displayed and how
the date should be formatted.

It is recommended that you specify a language in terms of an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag).
For example, `en` stands for English, while `en-US` stands for English (United States).

More details about this property can be found in the [Internationalization](tutorial-i18n.md) section.


#### [[yii\base\Application::modules|modules]] <span id="modules"></span>

This property specifies the [modules](structure-modules.md) that the application contains.

The property takes an array of module classes or [configurations](concept-configurations.md) with the array keys
being the module IDs. For example:

```php
[
    'modules' => [
        // a "booking" module specified with the module class
        'booking' => 'app\modules\booking\BookingModule',

        // a "comment" module specified with a configuration array
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

Please refer to the [Modules](structure-modules.md) section for more details.


#### [[yii\base\Application::name|name]] <span id="name"></span>

This property specifies the application name that may be displayed to end users. Unlike the
[[yii\base\Application::id|id]] property, which should take a unique value, the value of this property is mainly for
display purposes; it does not need to be unique.

You do not always need to configure this property if none of your code is using it.


#### [[yii\base\Application::params|params]] <span id="params"></span>

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

Then in your code where you need to use the size value, you can simply use code like the following:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

Later if you decide to change the thumbnail size, you only need to modify it in the application configuration;
you don't need to touch any dependent code.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <span id="sourceLanguage"></span>

This property specifies the language that the application code is written in. The default value is `'en-US'`,
meaning English (United States). You should configure this property if the text content in your code is not in English.

Like the [language](#language) property, you should configure this property in terms of
an [IETF language tag](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands for English,
while `en-US` stands for English (United States).

More details about this property can be found in the [Internationalization](tutorial-i18n.md) section.


#### [[yii\base\Application::timeZone|timeZone]] <span id="timeZone"></span>

This property is provided as an alternative way of setting the default time zone of the PHP runtime.
By configuring this property, you are essentially calling the PHP function
[date_default_timezone_set()](http://php.net/manual/en/function.date-default-timezone-set.php). For example:

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <span id="version"></span>

This property specifies the version of the application. It defaults to `'1.0'`. You do not need to configure
this property if none of your code is using it.


### Useful Properties <span id="useful-properties"></span>

The properties described in this subsection are not commonly configured because their default values
derive from common conventions. However, you may still configure them in case you want to break the conventions.


#### [[yii\base\Application::charset|charset]] <span id="charset"></span>

This property specifies the charset that the application uses. The default value is `'UTF-8'`, which should
be kept as-is for most applications unless you are working with a legacy system that uses a lot of non-Unicode data.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <span id="defaultRoute"></span>

This property specifies the [route](runtime-routing.md) that an application should use when a request
does not specify one. The route may consist of a child module ID, a controller ID, and/or an action ID.
For example, `help`, `post/create`, or `admin/post/create`. If an action ID is not given, this property will take
the default value specified in [[yii\base\Controller::defaultAction]].

For [[yii\web\Application|Web applications]], the default value of this property is `'site'`, which means
the `SiteController` controller and its default action should be used. As a result, if you access
the application without specifying a route, it will show the result of `app\controllers\SiteController::actionIndex()`.

For [[yii\console\Application|console applications]], the default value is `'help'`, which means the core command
[[yii\console\controllers\HelpController::actionIndex()]] should be used. As a result, if you run the command `yii`
without providing any arguments, it will display the help information.


#### [[yii\base\Application::extensions|extensions]] <span id="extensions"></span>

This property specifies the list of [extensions](structure-extensions.md) that are installed and used by the application.
By default, it will take the array returned by the file `@vendor/yiisoft/extensions.php`. The `extensions.php` file
is generated and maintained automatically when you use [Composer](https://getcomposer.org) to install extensions.
So in most cases, you do not need to configure this property.

In the special case when you want to maintain extensions manually, you may configure this property as follows:

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // optional, may also be a configuration array
            'alias' => [  // optional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... more extensions like the above ...

    ],
]
```

As you can see, the property takes an array of extension specifications. Each extension is specified with an array
consisting of `name` and `version` elements. If an extension needs to run during the [bootstrap](runtime-bootstrapping.md)
process, a `bootstrap` element may be specified with a bootstrapping class name or a [configuration](concept-configurations.md)
array. An extension may also define a few [aliases](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <span id="layout"></span>

This property specifies the name of the default layout that should be used when rendering a [view](structure-views.md).
The default value is `'main'`, meaning the layout file `main.php` under the [layout path](#layoutPath) should be used.
If both of the [layout path](#layoutPath) and the [view path](#viewPath) are taking the default values,
the default layout file can be represented as the path alias `@app/views/layouts/main.php`.

You may configure this property to be `false` if you want to disable layout by default, although this is very rare.


#### [[yii\base\Application::layoutPath|layoutPath]] <span id="layoutPath"></span>

This property specifies the path where layout files should be looked for. The default value is
the `layouts` sub-directory under the [view path](#viewPath). If the [view path](#viewPath) is taking
its default value, the default layout path can be represented as the path alias `@app/views/layouts`.

You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::runtimePath|runtimePath]] <span id="runtimePath"></span>

This property specifies the path where temporary files, such as log files and cache files, can be generated.
The default value is the directory represented by the alias `@app/runtime`.

You may configure it as a directory or a path [alias](concept-aliases.md). Note that the runtime path must
be writable by the process running the application. And the path should be protected from being accessed
by end users, because the temporary files under it may contain sensitive information.

To simplify access to this path, Yii has predefined a path alias named `@runtime` for it.


#### [[yii\base\Application::viewPath|viewPath]] <span id="viewPath"></span>

This property specifies the root directory where view files are located. The default value is the directory
represented by the alias `@app/views`. You may configure it as a directory or a path [alias](concept-aliases.md).


#### [[yii\base\Application::vendorPath|vendorPath]] <span id="vendorPath"></span>

This property specifies the vendor directory managed by [Composer](https://getcomposer.org). It contains
all third party libraries used by your application, including the Yii framework. The default value is
the directory represented by the alias `@app/vendor`.

You may configure this property as a directory or a path [alias](concept-aliases.md). When you modify
this property, make sure you also adjust the Composer configuration accordingly.

To simplify access to this path, Yii has predefined a path alias named `@vendor` for it.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <span id="enableCoreCommands"></span>

This property is supported by [[yii\console\Application|console applications]] only. It specifies
whether the core commands included in the Yii release should be enabled. The default value is `true`.


## Application Events <span id="application-events"></span>

An application triggers several events during the lifecycle of handling a request. You may attach event
handlers to these events in application configurations as follows:

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

The use of the `on eventName` syntax is described in the [Configurations](concept-configurations.md#configuration-format)
section.

Alternatively, you may attach event handlers during the [bootstrapping process](runtime-bootstrapping.md)
after the application instance is created. For example:

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <span id="beforeRequest"></span>

This event is triggered *before* an application handles a request. The actual event name is `beforeRequest`.

When this event is triggered, the application instance has been configured and initialized. So it is a good place
to insert your custom code via the event mechanism to intercept the request handling process. For example,
in the event handler, you may dynamically set the [[yii\base\Application::language]] property based on some parameters.


### [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] <span id="afterRequest"></span>

This event is triggered *after* an application finishes handling a request but *before* sending the response.
The actual event name is `afterRequest`.

When this event is triggered, the request handling is completed and you may take this chance to do some postprocessing
of the request or customize the response.

Note that the [[yii\web\Response|response]] component also triggers some events while it is sending out
response content to end users. Those events are triggered *after* this event.


### [[yii\base\Application::EVENT_BEFORE_ACTION|EVENT_BEFORE_ACTION]] <span id="beforeAction"></span>

This event is triggered *before* running every [controller action](structure-controllers.md).
The actual event name is `beforeAction`.

The event parameter is an instance of [[yii\base\ActionEvent]]. An event handler may set
the [[yii\base\ActionEvent::isValid]] property to be `false` to stop running the action.
For example:

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

Note that the same `beforeAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). Application objects are the first ones
triggering this event, followed by modules (if any), and finally controllers. If an event handler
sets [[yii\base\ActionEvent::isValid]] to be `false`, all of the subsequent events will NOT be triggered.


### [[yii\base\Application::EVENT_AFTER_ACTION|EVENT_AFTER_ACTION]] <span id="afterAction"></span>

This event is triggered *after* running every [controller action](structure-controllers.md).
The actual event name is `afterAction`.

The event parameter is an instance of [[yii\base\ActionEvent]]. Through
the [[yii\base\ActionEvent::result]] property, an event handler may access or modify the action result.
For example:

```php
[
    'on afterAction' => function ($event) {
        if (some condition) {
            // modify $event->result
        } else {
        }
    },
]
```

Note that the same `afterAction` event is also triggered by [modules](structure-modules.md)
and [controllers](structure-controllers.md). These objects trigger this event in the reverse order
as for that of `beforeAction`. That is, controllers are the first objects triggering this event,
followed by modules (if any), and finally applications.


## Vòng đời ứng dụng <span id="application-lifecycle"></span>

![Vòng đời ứng dụng](images/application-lifecycle.png)

When an [entry script](structure-entry-scripts.md) is being executed to handle a request,
an application will undergo the following lifecycle:

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
    create the module, controller, and action objects as specified by the route; and run the action.
  * Trigger the [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] event.
  * Send response to the end user.
4. The entry script receives the exit status from the application and completes the request processing.
