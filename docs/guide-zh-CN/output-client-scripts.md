客户端脚本使用
===========================

> Note: 此部分应用于开发环境

### 注册脚本

你可以使用 [[yii\web\View]] 对象注册脚本。这里有两个专门的方法：
（1）[[yii\web\View::registerJs()|registerJs()]] 用于内联脚本。
（2）[[yii\web\View::registerJsFile()|registerJsFile()]] 用于注册引入外部脚本文件。
内联脚本通常用于配置和动态生成代码。
这个方法的使用可以像下面这样：

```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

第一个参数是我们想插入的实际JS代码。
第二个参数确定了JS代码插入页面的位置。可用的值如下：

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] 用在HEAD部分。
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] 用在 `<body>` 标签的右边。
- [[yii\web\View::POS_END|View::POS_END]] 用在 `</body>` 标签的左边。
- [[yii\web\View::POS_READY|View::POS_READY]] 为了在 `ready` 事件中执行代码，这里将自动注册[[yii\web\JqueryAsset|jQuery]]。
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] 为了在 `load` 事件中执行代码，这里将自动注册[[yii\web\JqueryAsset|jQuery]]。

最后一个参数是一个唯一的脚本ID，主要是用于标识一段代码块，在添加一段新的代码块时，如果当前页面已经存在同样ID代码块时，那么将会被新的替换。
如果你不传这个参数，JS代码本身将会作为ID来使用。

外部脚本的引入使用像下面这样：

```php
$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::class]]);
```

[[yii\web\View::registerJsFile()|registerJsFile()]] 中参数的使用与 
[[yii\web\View::registerCssFile()|registerCssFile()]] 中的参数使用类似。
在上面的例子中,我们注册了 `main.js` 文件，并且依赖于 `JqueryAsset` 类。
这意味着 `main.js` 文件将被添加在 `jquery.js` 的后面。
如果没有这个依赖规范的话，`main.js`和 `jquery.js` 两者之间的顺序将不会被定义。

和 [[yii\web\View::registerCssFile()|registerCssFile()]] 一样，我们强烈建议您使用 [asset bundles](structure-assets.md) 来注册外部JS文件，而非使用
[[yii\web\View::registerJsFile()|registerJsFile()]] 来注册。


### 注册资源包

正如前面所提到的，我们推荐优先使用资源包而非直接使用CSS和JavaScript。
你可以在资源管理器 [asset manager](structure-assets.md) 部分查看更多细节。
至于怎样使用已经定义的资源包，这很简单：

```php
\frontend\assets\AppAsset::register($this);
```



### 注册 CSS

你可以使用 [[yii\web\View::registerCss()|registerCss()]] 或者 [[yii\web\View::registerCssFile()|registerCssFile()]] 来注册CSS。
前者是注册一段CSS代码块，而后者则是注册引入外部的CSS文件，例如：

```php
$this->registerCss("body { background: #f00; }");
```

上面的代码执行结果相当于在页面头部中添加了下面的代码：

```html
<style>
body { background: #f00; }
</style>
```

如果你想指定样式标记的附加属性，通过一个名值对的数组添加到第三个参数。
如果你需要确保只有一个单样式标签，则需要使用第四个参数作为meta标签的描述。

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

上面的代码将在页面的头部添加一个link引入CSS文件。

* 第一个参数指明被注册的CSS文件。
* 第二个参数指明 `<link>` 标签的HTML属性，选项 `depends` 是专门处理
  指明CSS文件依赖于哪个资源包。在这种情况下，依赖资源包就是
  [[yii\bootstrap\BootstrapAsset|BootstrapAsset]]。这意味着CSS文件将
  被添加在 [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] 之后。
* 最后一个参数指明一个ID来标识这个CSS文件。假如这个参数未传，
  CSS文件的URL将被作为ID来替代。


我们强烈建议使用 [asset bundles](structure-assets.md) 来注册外部CSS文件，
而非使用 [[yii\web\View::registerCssFile()|registerCssFile()]] 来注册。
使用资源包允许你合并并且压缩多个CSS文件，对于高流量的网站来说，这是比较理想的方式。
