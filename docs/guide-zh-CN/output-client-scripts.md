客户端脚本使用
===========================

现代 Web 应用程序，
除了呈现并发送到浏览器的静态 HTML 页面外，
还包含 JavaScript，
用于通过操纵现有元素或通过 AJAX 加载新内容来修改浏览器中的页面。
本节介绍 Yii 提供的用于向网站添加 JavaScript 和 CSS 以及动态调整它们的方法。

## 注册脚本 <span id="register-scripts"></span>

使用 [[yii\web\View]] 对象时，可以动态注册前端脚本。
这里有两个专门的方法：

- [[yii\web\View::registerJs()|registerJs()]] 用于内联脚本。
- [[yii\web\View::registerJsFile()|registerJsFile()]] 用于注册引入外部脚本文件。

### 注册内联脚本 <span id="inline-scripts"></span>

内联脚本对于配置，动态生成的代码以及由 [widgets](structure-widgets.md) 中包含的可重用前端代码创建的小片段非常有用。
[[yii\web\View::registerJs()|registerJs()]] 添加这些方法可以像如下使用：

```php
$this->registerJs(
    "$('#myButton').on('click', function() { alert('Button clicked!'); });",
    View::POS_READY,
    'my-button-handler'
);
```

第一个参数是我们想插入的实际 JS 代码。
它将被包含到 `<script>` 标签中。第二个参数确定脚本应插入页面的位置。
可能的值是：

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] 用在 HEAD 部分。
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] 用在 `<body>` 标签的右边。
- [[yii\web\View::POS_END|View::POS_END]] 用在 `</body>` 标签的左边。
- [[yii\web\View::POS_READY|View::POS_READY]] 为了在 [`ready` 事件](https://learn.jquery.com/using-jquery-core/document-ready/) 中执行代码。
  这里将自动注册 [[yii\web\JqueryAsset|jQuery]] 并将代码包装到适当的 jQuery 代码中。这是默认位置。
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] 为了在 [`load` 事件](https://learn.jquery.com/using-jquery-core/document-ready/) 中执行代码。
  与上面相同，这也将自动注册 [[yii\web\JqueryAsset|jQuery]]。

最后一个参数是一个唯一的脚本 ID，主要是用于标识一段代码块，在添加一段新的代码块时，如果当前页面已经存在同样 ID 代码块时，那么将会被新的替换。
如果你不传这个参数，JS 代码本身将会作为 ID 来使用。

### 注册脚本文件 <span id="script-files"></span>

[[yii\web\View::registerJsFile()|registerJsFile()]] 的参数类似于
[[yii\web\View::registerCssFile()|registerCssFile()]]。在以下示例中，
我们注册 `main.js` 文件，依赖于 [[yii\web\JqueryAsset]]。这意味着 `main.js`
文件将在 `jquery.js` 之后添加。没有这样的依赖规范，
`main.js` 和 `jquery.js` 之间的相对顺序将是未定义的，代码将不起作用。

外部脚本的引入使用像下面这样：

```php
$this->registerJsFile(
    '@web/js/main.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
```

这将为 [base URL](concept-aliases.md#predefined-aliases) 下的 `/js/main.js` 脚本添加一个标记。

我们强烈建议使用 [asset bundles](structure-assets.md) 来注册外部 JS 文件而非使用 [[yii\web\View::registerJsFile()|registerJsFile()]] 来注册。 因为这些允许更好的灵活性和更精细的依赖配置。
此外，使用资源包允许您组合和压缩多个 JS 文件，这对于高流量网站来说是比较理想的方式。

## 注册 CSS <span id="register-css"></span>

与 JavaScript 类似，您可以使用注册 CSS 使用
[[yii\web\View::registerCss()|registerCss()]] 或
[[yii\web\View::registerCssFile()|registerCssFile()]]。
前者注册一个 CSS 代码块，而后者注册一个外部 CSS 文件。

### 注册内联 CSS <span id="inline-css"></span>

```php
$this->registerCss("body { background: #f00; }");
```

上面的代码将导致将以下内容添加到页面的 `<head>` 部分：

```html
<style>
body { background: #f00; }
</style>
```

如果要指定样式标记的其他属性，请将 name-values 数组传递给第二个参数。
最后一个参数是一个唯一的 ID，用于标识样式块，并确保仅在代码中的不同位置注册相同样式时仅添加一次。

### 注册 CSS 文件 <span id="css-files"></span>

可以使用以下方法注册 CSS 文件：

```php
$this->registerCssFile("@web/css/themes/black-and-white.css", [
    'depends' => [\yii\bootstrap\BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

上面的代码将把 `/css/themes/black-and-white.css` 文件的链接添加到页面的 `<head>` 部分。

* 第一个参数指明被注册的 CSS 文件。
* 第二个参数指明 `<link>` 标签的 HTML 属性，选项 `depends` 是专门处理
  指明 CSS 文件依赖于哪个资源包。在这种情况下，依赖资源包就是
  [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]。这意味着 CSS 文件将
  被添加在 [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] *之后*。
* 最后一个参数指明一个 ID 来标识这个 CSS 文件。
  如果参数未提供，则将使用 CSS 文件的 URL。


我们强烈建议使用 [asset bundles](structure-assets.md) 来注册外部 CSS 文件，
而非使用 [[yii\web\View::registerCssFile()|registerCssFile()]] 来注册。
使用资源包允许你合并并且压缩多个 CSS 文件，对于高流量的网站来说，这是比较理想的方式。
它还提供了更大的灵活性，因为应用程序的所有资源依赖性都在一个位置配置。


## 注册资源包 <span id="asset-bundles"></span>

如前所述，建议使用资源包而不是直接注册 CSS 和 JavaScript 文件。
您可以获取有关如何定义资源包的详细信息在
["Assets" 部分](structure-assets.md).
至于使用已定义的资源包，它非常简单：

```php
\frontend\assets\AppAsset::register($this);
```

在上面的代码中，在视图文件的上下文中，`AppAsset` 包在当前视图上注册（由 `$this` 表示）。
从小部件中注册资产包时，您将传递小部件的
[[yii\base\Widget::$view|$view]] 来替代（`$this->view`）。


## 生成动态 Javascript <span id="dynamic-js"></span>

在视图文件中，HTML 代码通常不直接写出，
而是由某些依赖于视图变量的 PHP 代码生成。
为了使用 Javascript 操作生成的 HTML，JS 代码也必须包含动态部分，例如 jQuery 选择器的 ID。

要将PHP变量插入 JS 代码，
必须正确转义它们的值。
特别是当 JS 代码插入 HTML 而不是驻留在专用的 JS 文件中时。
为此，Yii 提供了 [[yii\helpers\Json|Json]] 助手类的 [[yii\helpers\Json::htmlEncode()|htmlEncode()]] 方法，其用法将在以下示例中显示。

### 注册全局 JavaScript 配置 <span id="js-configuration"></span>

在这个例子中，
我们使用一个数组将全局配置参数从应用程序的 PHP 部分传递给 JS 前端代码。

```php
$options = [
    'appName' => Yii::$app->name,
    'baseUrl' => Yii::$app->request->baseUrl,
    'language' => Yii::$app->language,
    // ...
];
$this->registerJs(
    "var yiiOptions = ".\yii\helpers\Json::htmlEncode($options).";",
    View::POS_HEAD,
    'yiiOptions'
);
```

上面的代码将注册一个包含 JavaScript 的 `<script>` 标签定义，
例如：

```javascript
var yiiOptions = {"appName":"My Yii Application","baseUrl":"/basic/web","language":"en"};
```

在您的 JavaScript 代码中，您现在可以像 `yiiOptions.baseUrl` 或 `yiiOptions.language` 一样访问它们。

### 传递翻译的消息 <span id="translated-messages"></span>

您可能会遇到 JavaScript 需要打印消息以响应某些事件的情况。在使用多种语言的应用程序中，此字符串必须转换为当前的应用程序语言。
实现此目的的一种方法是使用 Yii 提供的
[消息转换功能](tutorial-i18n.md#message-translation) 并将结果传递给 JavaScript 代码。

```php
$message = \yii\helpers\Json::htmlEncode(
    \Yii::t('app', 'Button clicked!')
);
$this->registerJs(<<<JS
    $('#myButton').on('click', function() { alert( $message ); });
JS
);
```

上面的示例代码使用 PHP
[Heredoc 语法](https://www.php.net/manual/zh/language.types.string.php#language.types.string.syntax.heredoc) 以获得更好的可读性。
这也可以在大多数 IDE 中实现更好的语法突出显示，因此它是编写内联 JavaScript 的首选方式，对于长于单行的代码尤其有用。变量 `$message` 是在 PHP
中创建的，感谢 [[yii\helpers\Json::htmlEncode|Json::htmlEncode]]
它包含有效 JS 语法中的字符串，可以将其插入到 JavaScript 代码中以放置 函数中的动态字符串调用 `alert()`。

> Note: 使用 Heredoc 时，请注意 JS 代码中的变量命名，
> 因为以 `$` 开头的变量可能会被解释为 PHP 变量，
> 这些变量将被其内容替换。
> jQuery 函数以 `$(` 或 `$.` 的形式不被解释为 PHP 变量，
> 可以安全地使用。

## `yii.js` 脚本 <span id="yii.js"></span>

> Note: 本节尚未编写。 它应该包含 `yii.js` 提供的功能的说明：
> 
> - Yii JavaScript 模块
> - CSRF 参数处理
> - `data-confirm` 处理
> - `data-method` 处理
> - 脚本过滤
> - 重定向处理

