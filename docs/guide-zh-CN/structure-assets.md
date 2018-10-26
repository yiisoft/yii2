资源
======

Yii中的资源是和Web页面相关的文件，可为CSS文件，JavaScript文件，图片或视频等，
资源放在Web可访问的目录下，直接被Web服务器调用。

通过程序自动管理资源更好一点，例如，当你在页面中使用 [[yii\jui\DatePicker]] 小部件时，
它会自动包含需要的CSS和JavaScript文件，
而不是要求你手工去找到这些文件并包含，
当你升级小部件时，它会自动使用新版本的资源文件，
在本教程中，我们会详述Yii提供的强大的资源管理功能。


## 资源包 <span id="asset-bundles"></span>

Yii在*资源包*中管理资源，资源包简单的说就是放在一个目录下的资源集合，
当在[视图](structure-views.md)中注册一个资源包，
在渲染Web页面时会包含包中的CSS和JavaScript文件。


## 定义资源包 <span id="defining-asset-bundles"></span>

资源包指定为继承[[yii\web\AssetBundle]]的PHP类，
包名为可[自动加载](concept-autoloading.md)的PHP类名，
在资源包类中，要指定资源所在位置，
包含哪些CSS和JavaScript文件以及和其他包的依赖关系。

如下代码定义[基础应用模板](start-installation.md)使用的主要资源包：

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

如上`AppAsset` 类指定资源文件放在 `@webroot` 目录下，对应的URL为
`@web`，资源包中包含一个CSS文件 `css/site.css`，没有JavaScript文件，
依赖其他两个包 [[yii\web\YiiAsset]] 和 [[yii\bootstrap\BootstrapAsset]]，
关于[[yii\web\AssetBundle]] 的属性的更多详细如下所述：

* [[yii\web\AssetBundle::sourcePath|sourcePath]]: 指定包包含资源文件的根目录，
  当根目录不能被Web访问时该属性应设置，否则，应设置
  [[yii\web\AssetBundle::basePath|basePath]] 属性和[[yii\web\AssetBundle::baseUrl|baseUrl]]。
  [路径别名](concept-aliases.md) 可在此处使用；
* [[yii\web\AssetBundle::basePath|basePath]]: 指定包含资源包中资源文件并可Web访问的目录，
  当指定[[yii\web\AssetBundle::sourcePath|sourcePath]] 属性，
  [资源管理器](#asset-manager) 会发布包的资源到一个可Web访问并覆盖该属性，
  如果你的资源文件在一个Web可访问目录下，应设置该属性，这样就不用再发布了。
  [路径别名](concept-aliases.md) 可在此处使用。
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: 指定对应到[[yii\web\AssetBundle::basePath|basePath]]目录的URL，
  和 [[yii\web\AssetBundle::basePath|basePath]] 类似，
  如果你指定 [[yii\web\AssetBundle::sourcePath|sourcePath]] 属性，
  [资源管理器](#asset-manager) 会发布这些资源并覆盖该属性，[路径别名](concept-aliases.md) 可在此处使用。
* [[yii\web\AssetBundle::js|js]]: 一个包含该资源包JavaScript文件的数组，
  注意正斜杠"/"应作为目录分隔符，
  每个JavaScript文件可指定为以下两种格式之一：
  - 相对路径表示为本地JavaScript文件 (如 `js/main.js`)，文件实际的路径在该相对路径前加上
    [[yii\web\AssetManager::basePath]]，文件实际的URL
    在该路径前加上[[yii\web\AssetManager::baseUrl]]。
  - 绝对URL地址表示为外部JavaScript文件，如
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` 或 
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::css|css]]: 一个包含该资源包CSS文件的数组，
  该数组格式和 [[yii\web\AssetBundle::js|js]] 相同。
* [[yii\web\AssetBundle::depends|depends]]: 一个列出该资源包依赖的
  其他资源包（后两节有详细介绍）。
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: 当调用[[yii\web\View::registerJsFile()]]注册该包 *每个* JavaScript文件时，
  指定传递到该方法的选项。
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: 当调用[[yii\web\View::registerCssFile()]]注册该包 *每个* css文件时，
  指定传递到该方法的选项。
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: 当调用[[yii\web\AssetManager::publish()]]发布该包资源文件到Web目录时
  指定传递到该方法的选项，仅在指定了
  [[yii\web\AssetBundle::sourcePath|sourcePath]]属性时使用。


### 资源位置 <span id="asset-locations"></span>

资源根据它们的位置可以分为：

* 源资源: 资源文件和PHP源代码放在一起，不能被Web直接访问，为了使用这些源资源，
  它们要拷贝到一个可Web访问的Web目录中
  成为发布的资源，这个过程称为*发布资源*，随后会详细介绍。
* 发布资源: 资源文件放在可通过Web直接访问的Web目录中；
* 外部资源: 资源文件放在与你的Web应用不同
  的Web服务器上；

当定义资源包类时候，如果你指定了[[yii\web\AssetBundle::sourcePath|sourcePath]] 属性，
就表示任何使用相对路径的资源会被当作源资源；
如果没有指定该属性，就表示这些资源为发布资源（因此应指定[[yii\web\AssetBundle::basePath|basePath]] 和
[[yii\web\AssetBundle::baseUrl|baseUrl]] 让Yii知道它们的位置）。

推荐将资源文件放到Web目录以避免不必要的发布资源过程，这就是之前的例子：指定
[[yii\web\AssetBundle::basePath|basePath]] 
而不是 [[yii\web\AssetBundle::sourcePath|sourcePath]].

对于 [扩展](structure-extensions.md)来说，
由于它们的资源和源代码都在不能Web访问的目录下，
在定义资源包类时必须指定[[yii\web\AssetBundle::sourcePath|sourcePath]]属性。

> Note: [[yii\web\AssetBundle::sourcePath|source path]] 属性不要用 `@webroot/assets`，该路径默认为
  [[yii\web\AssetManager|asset manager]]资源管理器将源资源发布后存储资源的路径，
  该路径的所有内容会认为是临时文件，
  可能会被删除。


### 资源依赖 <span id="asset-dependencies"></span>

当Web页面包含多个CSS或JavaScript文件时，
它们有一定的先后顺序以避免属性覆盖，
例如，Web页面在使用jQuery UI小部件前必须确保jQuery JavaScript文件已经被包含了，
我们称这种资源先后次序称为资源依赖。

资源依赖主要通过[[yii\web\AssetBundle::depends]] 属性来指定，
在`AppAsset` 示例中，资源包依赖其他两个资源包： 
[[yii\web\YiiAsset]] 和 [[yii\bootstrap\BootstrapAsset]]
也就是该资源包的CSS和JavaScript文件要在这两个依赖包的文件包含 *之后* 才包含。

资源依赖关系是可传递，也就是人说A依赖B，B依赖C，那么A也依赖C。


### 资源选项 <span id="asset-options"></span>

可指定[[yii\web\AssetBundle::cssOptions|cssOptions]] 和 [[yii\web\AssetBundle::jsOptions|jsOptions]]
属性来自定义页面包含CSS和JavaScript文件的方式，
这些属性值会分别传递给 [[yii\web\View::registerCssFile()]] 和 [[yii\web\View::registerJsFile()]] 方法，
在[视图](structure-views.md) 调用这些方法包含CSS和JavaScript文件时。

> Note: 在资源包类中设置的选项会应用到该包中 *每个* CSS/JavaScript 文件，
  如果想对每个文件使用不同的选项，
  应创建不同的资源包并在每个包中使用一个选项集。

例如，只想IE9或更高的浏览器包含一个CSS文件，可以使用如下选项：

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

这会是包中的CSS文件使用以下HTML标签包含进来：

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

为链接标签包含`<noscript>`可使用如下代码：

```php
public $cssOptions = ['noscript' => true];
```

为使JavaScript文件包含在页面head区域（JavaScript文件默认包含在body的结束处）
使用以下选项：

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

默认情况下，当发布资源包时，所有在 [[yii\web\AssetBundle::sourcePath]] 目录里的内容都会发布。
你可以通过配置 [[yii\web\AssetBundle::publishOptions|publishOptions]] 属性来自定义这种行为。
比如，为了只发布[[yii\web\AssetBundle::sourcePath]]其中的某些内容或子目录里的内容，
可以在资源类中试试下面的做法：

```php
<?php
namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle 
{
    public $sourcePath = '@bower/font-awesome'; 
    public $css = [ 
        'css/font-awesome.min.css', 
    ];
    public $publishOptions = [
        'only' => [
            'fonts/',
            'css/',
        ]
    ];
}  
```

上述的代码为 ["fontawesome" package](http://fontawesome.io/) 定义了资源包。
通过配置发布选项的 only 下标，只有 `fonts` 和 `css` 子目录会发布。


### Bower 和 NPM 资源 <span id="bower-npm-assets"></span>

大多数 JavaScript/CSS 包通过[Bower](http://bower.io/) 和/或 
[NPM](https://www.npmjs.org/)管理，
如果你的应用或扩展使用这些包，推荐你遵循以下步骤来管理库中的资源：

1. 修改应用或扩展的 `composer.json` 文件将包列入`require` 中，
   应使用`bower-asset/PackageName` (Bower包) 
   或 `npm-asset/PackageName` (NPM包)来对应库。
2. 创建一个资源包类并将你的应用或扩展要使用的JavaScript/CSS 文件列入到类中，
   应设置 [[yii\web\AssetBundle::sourcePath|sourcePath]] 属性为`@bower/PackageName` 或 `@npm/PackageName`，
   因为根据别名Composer会安装Bower或NPM包到对应的目录下。

> Note: 一些包会将它们分布式文件放到一个子目录中，对于这种情况，应指定子目录作为
  [[yii\web\AssetBundle::sourcePath|sourcePath]]属性值，
  例如，[[yii\web\JqueryAsset]]使用 `@bower/jquery/dist` 而不是 `@bower/jquery`。


## 使用资源包 <span id="using-asset-bundles"></span>

为使用资源包，在[视图](structure-views.md)中调用[[yii\web\AssetBundle::register()]]方法先注册资源，
例如，在视图模板可使用如下代码注册资源包：

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this 代表视图对象
```

> Info: [[yii\web\AssetBundle::register()]] 方法返回资源包对象，该对象包含了发布资源的信息比如 [[yii\web\AssetBundle::basePath|basePath]] 或 [[yii\web\AssetBundle::baseUrl|baseUrl]]。

如果在其他地方注册资源包，应提供视图对象，如在 [小部件](structure-widgets.md) 类中注册资源包，
可以通过 `$this->view` 获取视图对象。

当在视图中注册一个资源包时，在背后Yii会注册它所依赖的资源包，
如果资源包是放在Web不可访问的目录下，会被发布到可访问的目录，
后续当视图渲染页面时，
会生成这些注册包包含的CSS和JavaScript文件对应的`<link>` 和 `<script>` 标签，
这些标签的先后顺序取决于资源包的依赖关系以及在
[[yii\web\AssetBundle::css]]和[[yii\web\AssetBundle::js]] 的列出来的前后顺序。


### 自定义资源包 <span id="customizing-asset-bundles"></span>

Yii通过名为 `assetManager`的应用组件实现[[yii\web\AssetManager]] 来管理应用组件，
通过配置[[yii\web\AssetManager::bundles]] 属性，可以自定义资源包的行为，
例如，[[yii\web\JqueryAsset]] 资源包默认从jquery Bower包中使用`jquery.js` 文件，
为了提高可用性和性能，你可能需要从Google服务器上获取jquery文件，
可以在应用配置中配置`assetManager`，如下所示：

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // 一定不要发布该资源
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

可通过类似[[yii\web\AssetManager::bundles]]配置多个资源包，
数组的键应为资源包的类名（最开头不要反斜杠），
数组的值为对应的[配置数组](concept-configurations.md).

> Tip: 可以根据条件判断使用哪个资源，如下示例为如何在开发环境用`jquery.js`，
> 否则用`jquery.min.js`：
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

可以设置资源包的名称对应`false`来禁用想禁用的一个或多个资源包，
当视图中注册一个禁用资源包，
视图不会包含任何该包的资源以及不会注册它所依赖的包，
例如，为禁用[[yii\web\JqueryAsset]]，可以使用如下配置：

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```

可设置[[yii\web\AssetManager::bundles]]为`false`禁用 *所有* 的资源包。


### 资源部署 <span id="asset-mapping"></span>

有时你想"修复" 多个资源包中资源文件的错误/不兼容，例如包A使用1.11.1版本的`jquery.min.js`，
包B使用2.1.1版本的`jquery.js`，可自定义每个包来解决这个问题，
更好的方式是使用*资源部署*特性来部署不正确的资源为想要的，
为此，配置[[yii\web\AssetManager::assetMap]]属性，如下所示：

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```

[[yii\web\AssetManager::assetMap|assetMap]]的键为你想要修复的资源名，值为你想要使用的资源路径，
当视图注册资源包，在[[yii\web\AssetBundle::css|css]] 和
[[yii\web\AssetBundle::js|js]] 数组中每个相关资源文件会和该部署进行对比，
如果数组任何键对比为资源文件的最后文件名
（如果有的话前缀为 [[yii\web\AssetBundle::sourcePath]]），对应的值为替换原来的资源。
例如，资源文件`my/path/to/jquery.js` 匹配键 `jquery.js`.

> Note: 只有相对相对路径指定的资源对应到资源部署，替换的资源路径可以为绝对路径，
  也可为和[[yii\web\AssetManager::basePath]]相关的路径。


### 资源发布 <span id="asset-publishing"></span>

如前所述，如果资源包放在Web不能访问的目录，
当视图注册资源时资源会被拷贝到一个Web可访问的目录中，
这个过程称为*资源发布*，[[yii\web\AssetManager|asset manager]]会自动处理该过程。

资源默认会发布到`@webroot/assets`目录，对应的URL为`@web/assets`，
可配置[[yii\web\AssetManager::basePath|basePath]] 和 
[[yii\web\AssetManager::baseUrl|baseUrl]] 属性自定义发布位置。

除了拷贝文件方式发布资源，如果操作系统和Web服务器允许可以使用符号链接，该功能可以通过设置
[[yii\web\AssetManager::linkAssets|linkAssets]] 为 true 来启用。

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

使用以上配置，
资源管理器会创建一个符号链接到要发布的资源包源路径，
这比拷贝文件方式快并能确保发布的资源一直为最新的。


### 打破缓存 <span id="cache-busting"></span>

对于运行在生产模式的 Web 应用来说，通常的做法是为资源包和其他静态资源开启 http 缓存。
但这种做法有个不好的地方就是，当你更新某个资源并部署到生产环境时，
用户的客户端可能由于 http 缓存而仍然使用旧版本的资源。
为了克服该不足，你可以试试打破缓存特性，它由2.0.3版本引入，只需如下配置 [[yii\web\AssetManager]] 即可：
  
```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
    ],
];
```

通过上述配置后，每个发布资源的 url 都会添加一个最后更新时间戳信息。
比如，`yii.js` 的 url 可能是 `/assets/5515a87c/yii.js?v=1423448645"`这样的，
这里的参数 v 表示 `yii.js` 文件的最后更新时间戳。
现在一旦你更新了某个资源，它的 URL 也会改变进而强制客户端获取该资源的最新版本。


## 常用资源包 <span id="common-asset-bundles"></span>

Yii框架定义许多资源包，如下资源包是最常用，
可在你的应用或扩展代码中引用它们。

- [[yii\web\YiiAsset]]: 主要包含`yii.js` 文件，该文件完成模块JavaScript代码组织功能，
  也为 `data-method` 和 `data-confirm`属性提供特别支持和其他有用的功能。
- [[yii\web\JqueryAsset]]: 包含从jQuery bower 包的`jquery.js`文件。 
- [[yii\bootstrap\BootstrapAsset]]: 包含从Twitter Bootstrap 框架的CSS文件。
- [[yii\bootstrap\BootstrapPluginAsset]]: 包含从Twitter Bootstrap 框架的JavaScript 文件
  来支持Bootstrap JavaScript插件。
- [[yii\jui\JuiAsset]]: 包含从jQuery UI库的CSS 和 JavaScript 文件。

如果你的代码需要jQuery, jQuery UI 或 Bootstrap，应尽量使用这些预定义资源包而非自己创建，
如果这些包的默认配置不能满足你的需求，可以自定义配置，
详情参考[自定义资源包](#customizing-asset-bundles)。


## 资源转换 <span id="asset-conversion"></span>

除了直接编写CSS 和/或 JavaScript代码，开发人员经常使用扩展语法来编写，再使用特殊的工具将它们转换成CSS/Javascript。
例如，对于CSS代码可使用[LESS](http://lesscss.org/) 或 [SCSS](http://sass-lang.com/)，
对于JavaScript 可使用 [TypeScript](http://www.typescriptlang.org/)。

可将使用扩展语法的资源文件列到资源包的[[yii\web\AssetBundle::css|css]] 和 [[yii\web\AssetBundle::js|js]]中，如下所示：

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

当在视图中注册一个这样的资源包，[[yii\web\AssetManager|asset manager]]资源管理器会自动运行预处理工具将使用扩展语法
的资源转换成CSS/JavaScript，当视图最终渲染页面时，
在页面中包含的是CSS/Javascipt文件，
而不是原始的扩展语法代码文件。

Yii使用文件扩展名来表示资源使用哪种扩展语法，
默认可以识别如下语法和文件扩展名：

- [LESS](http://lesscss.org/): `.less`
- [SCSS](http://sass-lang.com/): `.scss`
- [Stylus](http://learnboost.github.io/stylus/): `.styl`
- [CoffeeScript](http://coffeescript.org/): `.coffee`
- [TypeScript](http://www.typescriptlang.org/): `.ts`

Yii依靠安装的预处理工具来转换资源，例如，
为使用[LESS](http://lesscss.org/)，应安装`lessc` 预处理命令。

可配置[[yii\web\AssetManager::converter]]自定义预处理命令和支持的扩展语法，
如下所示：

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

如上所示，通过[[yii\web\AssetConverter::commands]] 属性指定支持的扩展语法，
数组的键为文件扩展名（前面不要.），
数组的值为目标资源文件扩展名和执行资源转换的命令，
命令中的标记 `{from}` 和`{to}`会分别被源资源文件路径和目标资源文件路径替代。

> Info: 除了以上方式，也有其他的方式来处理扩展语法资源，
  例如，可使用编译工具如[grunt](http://gruntjs.com/)
  来监控并自动转换扩展语法资源，此时，
  应使用资源包中编译后的CSS/Javascript文件而不是原始文件。


## 合并和压缩资源 <span id="combining-compressing-assets"></span>

一个Web页面可以包含很多CSS 和/或 JavaScript 文件，为减少HTTP 请求和这些下载文件的大小，
通常的方式是在页面中合并并压缩多个CSS/JavaScript 文件为一个或很少的几个文件，
并使用压缩后的文件而不是原始文件。
 
> Info: 合并和压缩资源通常在应用在产品上线模式，
  在开发模式下使用原始的CSS/JavaScript更方便调试。

接下来介绍一种合并和压缩资源文件
而不需要修改已有代码的方式：

1. 找出应用中所有你想要合并和压缩的资源包，
2. 将这些包分成一个或几个组，注意每个包只能属于其中一个组，
3. 合并/压缩每个组里CSS文件到一个文件，同样方式处理JavaScript文件，
4. 为每个组定义新的资源包：
   * 设置[[yii\web\AssetBundle::css|css]] 和 [[yii\web\AssetBundle::js|js]] 
     属性分别为压缩后的CSS和JavaScript文件；
   * 自定义设置每个组内的资源包，设置资源包的[[yii\web\AssetBundle::css|css]] 
     和 [[yii\web\AssetBundle::js|js]] 属性为空, 
     并设置它们的 [[yii\web\AssetBundle::depends|depends]] 属性为每个组新创建的资源包。

使用这种方式，当在视图中注册资源包时，会自动触发原始包所属的组资源包的注册，
然后，页面就会包含以合并/压缩的资源文件，
而不是原始文件。


### 示例 <span id="example"></span>

使用一个示例来解释以上这种方式：

假定你的应用有两个页面X 和 Y，页面X使用资源包A，B和C，页面Y使用资源包B，C和D。

有两种方式划分这些资源包，一种使用一个组包含所有资源包，
另一种是将（A,B,C）放在组X，（B，C，D）放在组Y，
哪种方式更好？第一种方式优点是两个页面使用相同的已合并CSS和JavaScript文件使HTTP缓存更高效，
另一方面，由于单个组包含所有文件，
已合并的CSS和Javascipt文件会更大，因此会增加文件传输时间，在这个示例中，
我们使用第一种方式，也就是用一个组包含所有包。

> Info: 将资源包分组并不是无价值的，通常要求分析现实中不同页面各种资源的数据量，
  开始时为简便使用一个组。

在所有包中使用工具(例如 [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) 来合并和压缩CSS和JavaScript文件，
注意合并后的文件满足包间的先后依赖关系，
例如，如果包A依赖B，B依赖C和D，那么资源文件列表以C和D开始，
然后为B最后为A。

合并和压缩之后，会得到一个CSS文件和一个JavaScript文件，
假定它们的名称为`all-xyz.css` 和 `all-xyz.js`，
`xyz` 为使文件名唯一以避免HTTP缓存问题的时间戳或哈希值。
 
现在到最后一步了，在应用配置中配置[[yii\web\AssetManager|asset manager]] 
资源管理器如下所示：

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

如[自定义资源包](#customizing-asset-bundles) 小节中所述，如上配置改变每个包的默认行为，
特别是包A、B、C和D不再包含任何资源文件，
都依赖包含合并后的`all-xyz.css` 和 `all-xyz.js`文件的包`all`，
因此，对于页面X会包含这两个合并后的文件而不是包A、B、C的原始文件，
对于页面Y也是如此。

最后有个方法更好地处理上述方式，除了直接修改应用配置文件，
可将自定义包数组放到一个文件，在应用配置中根据条件包含该文件，
例如：

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'),  
        ],
    ],
];
```

如上所示，在产品上线模式下资源包数组存储在`assets-prod.php`文件中，
不是产品上线模式存储在`assets-dev.php`文件中。


### 使用 `asset` 命令 <span id="using-asset-command"></span>

Yii提供一个名为`asset`控制台命令来使上述操作自动处理。

为使用该命令，应先创建一个配置文件设置哪些资源包要合并以及分组方式，
可使用`asset/template` 子命令来生成一个模板，
然后修改模板成你想要的。

```
yii asset/template assets.php
```

该命令在当前目录下生成一个名为`assets.php`的文件，文件的内容类似如下：

```php
<?php
/**
 * 为控制台命令"yii asset"使用的配置文件
 * 注意在控制台环境下，一些路径别名如 '@webroot' 和 '@web' 不会存在
 * 请定义不存在的路径别名
 */
return [
    // 为JavaScript文件压缩修改 command/callback 
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // 为CSS文件压缩修改command/callback 
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // 要压缩的资源包列表
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // 资源包压缩后的输出
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // 资源管理器配置:
    'assetManager' => [
    ],
];
```

应修改该文件的`bundles`的选项指定哪些包你想要合并，
在`targets`选项中应指定这些包如何分组，
如前述的可以指定一个或多个组。

> Note: 由于在控制台应用别名 `@webroot` and `@web` 不可用，
应在配置中明确指定它们。

JavaScript文件会被合并压缩后写入到`js/all-{hash}.js`文件，
其中 {hash} 会被结果文件的哈希值替换。

`jsCompressor` 和 `cssCompressor` 选项指定控制台命令或PHP回调函数来执行JavaScript和CSS合并和压缩，
Yii默认使用[Closure Compiler](https://developers.google.com/closure/compiler/)来合并JavaScript文件， 
使用[YUI Compressor](https://github.com/yui/yuicompressor/)来合并CSS文件，
你应手工安装这些工具或修改选项使用你喜欢的工具。


根据配置文件，可执行`asset` 命令来合并和压缩资源文件
并生成一个新的资源包配置文件`assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

生成的配置文件可以在应用配置中包含，
如最后一小节所描述的。


> Info: 使用`asset` 命令并不是唯一一种自动合并和压缩过程的方法，
  可使用优秀的工具[grunt](http://gruntjs.com/)来完成这个过程。



### 资源包分组 <span id="grouping-asset-bundles"></span>

上一小节，介绍了如何压缩所有的资源包到一个文件，减少对应用中引用资源文件的 http 请求数，但是在实践中很少这样做。比如，应用有一个“前端”和一个“后端”，每一个都用了一个不同js和css文件集合。在这种情况下，把所有的资源包压缩到一个文件毫无意义，“前端”不会用到“后端”的资源文件，当请求“前端”页面时，“后端”的资源文件也会被发送过来，浪费网络带宽。

为了解决这个问题，可以吧资源包分成若干组，每个组里面有若干个资源包。下面的配置展示了如何对资源包分组：

```php
return [
    ...
    // Specify output bundles with groups:
    'targets' => [
        'allShared' => [
            'js' => 'js/all-shared-{hash}.js',
            'css' => 'css/all-shared-{hash}.css',
            'depends' => [
                // 包含由'backend' 和 'frontend' 共享的资源包
                'yii\web\YiiAsset',
                'app\assets\SharedAsset',
            ],
        ],
        'allBackEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [
                // 只包含 'backend' 资源:
                'app\assets\AdminAsset'
            ],
        ],
        'allFrontEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [], // 包含所有的剩余资源
        ],
    ],
    ...
];
```

如上所示，资源包分成了三个组：`allShared`，`allBackEnd` 和 `allFrontEnd`
它们每个都依赖各自的资源包集合。 比如， `allBackEnd` 依赖 `app\assets\AdminAsset`。
当对该配置运行 `asset` 命令时，将会根据各自依赖合并资源包。

> Info: 你也可以把某个分组的 `depends` 配置留空。 这样做得话，
这个分组将会依赖剩余的资源包，剩余资源包是指不被其他分组依赖的那些资源包。
