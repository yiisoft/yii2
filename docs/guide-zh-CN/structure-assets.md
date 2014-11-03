资源
======

Yii中的资源是和Web页面相关的文件，可为CSS文件，JavaScript文件，图片或视频等，
资源放在Web可访问的目录下，直接被Web服务器调用。
An asset in Yii is a file that may be referenced in a Web page. It can be a CSS file, a JavaScript file, an image
or video file, etc. Assets are located in Web-accessible directories and are directly served by Web servers.

通过程序自动管理资源更好一点，例如，当你在页面中使用 [[yii\jui\DatePicker]] 小部件时，
它会自动包含需要的CSS和JavaScript文件，而不是要求你手工去找到这些文件并包含，
当你升级小部件时，它会自动使用新版本的资源文件，在本教程中，我们会详述Yii提供的强大的资源管理功能。
It is often preferable to manage assets programmatically. For example, when you use the [[yii\jui\DatePicker]] widget
in a page, it will automatically include the required CSS and JavaScript files, instead of asking you to manually
find these files and include them. And when you upgrade the widget to a new version, it will automatically use
the new version of the asset files. In this tutorial, we will describe the powerful asset management capability
provided in Yii.


## 资源包 <a name="asset-bundles"></a>
## Asset Bundles <a name="asset-bundles"></a>

Yii在*资源包*中管理资源，资源包简单的说就是放在一个目录下的资源集合，
当在[视图](structure-views.md)中注册一个资源包，在渲染Web页面时会包含包中的CSS和JavaScript文件。
Yii manages assets in the unit of *asset bundle*. An asset bundle is simply a collection of assets located
in a directory. When you register an asset bundle in a [view](structure-views.md), it will include the CSS and
JavaScript files in the bundle in the rendered Web page.


## 定义资源包 <a name="defining-asset-bundles"></a>
## Defining Asset Bundles <a name="defining-asset-bundles"></a>

资源包指定为继承[[yii\web\AssetBundle]]的PHP类，包名为可[自动加载](concept-autoloading.md)的PHP类名，
在资源包类中，要指定资源所在位置，包含哪些CSS和JavaScript文件以及和其他包的依赖关系。
Asset bundles are specified as PHP classes extending from [[yii\web\AssetBundle]]. The name of a bundle is simply
its corresponding PHP class name which should be [autoloadable](concept-autoloading.md). In an asset bundle class,
you would typically specify where the assets are located, what CSS and JavaScript files the bundle contains, and
how the bundle depends on other bundles.

如下代码定义[基础应用模板](start-installation.md)使用的主要资源包：
The following code defines the main asset bundle used by [the basic application template](start-installation.md):

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
The above `AppAsset` class specifies that the asset files are located under the `@webroot` directory which
is corresponding to the URL `@web`; the bundle contains a single CSS file `css/site.css` and no JavaScript file;
the bundle depends on two other bundles: [[yii\web\YiiAsset]] and [[yii\bootstrap\BootstrapAsset]]. More detailed
explanation about the properties of [[yii\web\AssetBundle]] can be found in the following:

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
  和 [[yii\web\AssetBundle::basePath|basePath]] 类似，如果你指定 [[yii\web\AssetBundle::sourcePath|sourcePath]] 属性，
  [资源管理器](#asset-manager) 会发布这些资源并覆盖该属性，[路径别名](concept-aliases.md) 可在此处使用。
* [[yii\web\AssetBundle::js|js]]: 一个包含该资源包JavaScript文件的数组，注意正斜杠"/"应作为目录分隔符，
  每个JavaScript文件可指定为以下两种格式之一：
  - 相对路径表示为本地JavaScript文件 (如 `js/main.js`)，文件实际的路径在该相对路径前加上
    [[yii\web\AssetManager::basePath]]，文件实际的URL在该路径前加上[[yii\web\AssetManager::baseUrl]]。
  - 绝对URL地址表示为外部JavaScript文件，如
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` 或 
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::css|css]]: 一个包含该资源包JavaScript文件的数组，该数组格式和 [[yii\web\AssetBundle::js|js]] 相同。
* [[yii\web\AssetBundle::depends|depends]]: 一个列出该资源包依赖的其他资源包（后两节有详细介绍）。
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: 当调用[[yii\web\View::registerJsFile()]]注册该包 *每个* JavaScript文件时，
  指定传递到该方法的选项。
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: 当调用[[yii\web\View::registerCssFile()]]注册该包 *每个* css文件时，
  指定传递到该方法的选项。
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: 当调用[[yii\web\AssetManager::publish()]]发布该包资源文件到Web目录时
  指定传递到该方法的选项，仅在指定了[[yii\web\AssetBundle::sourcePath|sourcePath]]属性时使用。

* [[yii\web\AssetBundle::sourcePath|sourcePath]]: specifies the root directory that contains the asset files in
  this bundle. This property should be set if the root directory is not Web accessible. Otherwise, you should
  set the [[yii\web\AssetBundle::basePath|basePath]] property and [[yii\web\AssetBundle::baseUrl|baseUrl]], instead.
  [Path aliases](concept-aliases.md) can be used here.
* [[yii\web\AssetBundle::basePath|basePath]]: specifies a Web-accessible directory that contains the asset files in
  this bundle. When you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property,
  the [asset manager](#asset-manager) will publish the assets in this bundle to a Web-accessible directory
  and overwrite this property accordingly. You should set this property if your asset files are already in
  a Web-accessible directory and do not need asset publishing. [Path aliases](concept-aliases.md) can be used here.
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: specifies the URL corresponding to the directory
  [[yii\web\AssetBundle::basePath|basePath]]. Like [[yii\web\AssetBundle::basePath|basePath]],
  if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property, the [asset manager](#asset-manager)
  will publish the assets and overwrite this property accordingly. [Path aliases](concept-aliases.md) can be used here.
* [[yii\web\AssetBundle::js|js]]: an array listing the JavaScript files contained in this bundle. Note that only
  forward slash "/" should be used as directory separators. Each JavaScript file can be specified in one of the
  following two formats:
  - a relative path representing a local JavaScript file (e.g. `js/main.js`). The actual path of the file
    can be determined by prepending [[yii\web\AssetManager::basePath]] to the relative path, and the actual URL
    of the file can be determined by prepending [[yii\web\AssetManager::baseUrl]] to the relative path.
  - an absolute URL representing an external JavaScript file. For example,
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` or
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::css|css]]: an array listing the CSS files contained in this bundle. The format of this array
  is the same as that of [[yii\web\AssetBundle::js|js]].
* [[yii\web\AssetBundle::depends|depends]]: an array listing the names of the asset bundles that this bundle depends on
  (to be explained shortly).
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerJsFile()]] method when it is called to register *every* JavaScript file in this bundle.
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerCssFile()]] method when it is called to register *every* CSS file in this bundle.
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: specifies the options that will be passed to the
  [[yii\web\AssetManager::publish()]] method when it is called to publish source asset files to a Web directory.
  This is only used if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property.


### 资源位置 <a name="asset-locations"></a>
### Asset Locations <a name="asset-locations"></a>

Assets, based on their location, can be classified as:
资源根据它们的位置可以分为：

* 源资源: 资源文件和PHP源代码放在一起，不能被Web直接访问，为了使用这些源资源，它们要拷贝到一个可Web访问的Web目录中
  成为发布的资源，这个过程称为*发布资源*，随后会详细介绍。
* 发布资源: 资源文件放在可通过Web直接访问的Web目录中；
* 外部资源: 资源文件放在你的Web应用不同的Web服务器上；

When defining an asset bundle class, if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property,
it means any assets listed using relative paths will be considered as source assets. If you do not specify this property,
it means those assets are published assets (you should therefore specify [[yii\web\AssetBundle::basePath|basePath]] and
[[yii\web\AssetBundle::baseUrl|baseUrl]] to let Yii know where they are located.)
当定义资源包类时候，如果你指定了[[yii\web\AssetBundle::sourcePath|sourcePath]] 属性，就表示任何使用相对路径的资源会被
当作源资源；如果没有指定该属性，就表示这些资源为发布资源（因此应指定[[yii\web\AssetBundle::basePath|basePath]] 和
[[yii\web\AssetBundle::baseUrl|baseUrl]] 让Yii知道它们的位置）。

It is recommended that you place assets belonging to an application in a Web directory to avoid the unnecessary asset
publishing process. This is why `AppAsset` in the prior example specifies [[yii\web\AssetBundle::basePath|basePath]]
instead of [[yii\web\AssetBundle::sourcePath|sourcePath]].
推荐将资源文件放到Web目录以避免不必要的发布资源过程，这就是之前的例子指定
[[yii\web\AssetBundle::basePath|basePath]] 而不是 [[yii\web\AssetBundle::sourcePath|sourcePath]].

For [extensions](structure-extensions.md), because their assets are located together with their source code
in directories that are not Web accessible, you have to specify the [[yii\web\AssetBundle::sourcePath|sourcePath]]
property when defining asset bundle classes for them.
对于 [扩展](structure-extensions.md)来说，由于它们的资源和源代码都在不能Web访问的目录下，
在定义资源包类时必须指定[[yii\web\AssetBundle::sourcePath|sourcePath]]属性。

> Note: Do not use `@webroot/assets` as the [[yii\web\AssetBundle::sourcePath|source path]].
  This directory is used by default by the [[yii\web\AssetManager|asset manager]] to save the asset files
  published from their source location. Any content in this directory are considered temporarily and may be subject
  to removal.
> 注意:  [[yii\web\AssetBundle::sourcePath|source path]] 属性不要用`@webroot/assets`，该路径默认为
  [[yii\web\AssetManager|asset manager]]资源管理器将源资源发布后存储资源的路径，该路径的所有内容会认为是临时文件，
  可能会被删除。


### 资源依赖 <a name="asset-dependencies"></a>
### Asset Dependencies <a name="asset-dependencies"></a>

When you include multiple CSS or JavaScript files in a Web page, they have to follow certain orders to avoid
overriding issues. For example, if you are using a jQuery UI widget in a Web page, you have to make sure
the jQuery JavaScript file is included before the jQuery UI JavaScript file. We call such ordering the dependencies
among assets.
当Web页面包含多个CSS或JavaScript文件时，它们有一定的先后顺序以避免属性覆盖，
例如，Web页面在使用jQuery UI小部件前必须确保jQuery JavaScript文件已经被包含了，
我们称这种资源先后次序称为资源依赖。

Asset dependencies are mainly specified through the [[yii\web\AssetBundle::depends]] property.
In the `AppAsset` example, the asset bundle depends on two other asset bundles: [[yii\web\YiiAsset]] and
[[yii\bootstrap\BootstrapAsset]], which means the CSS and JavaScript files in `AppAsset` will be included *after*
those files in the two dependent bundles.
资源依赖主要通过[[yii\web\AssetBundle::depends]] 属性来指定，
在`AppAsset` 示例中，资源包依赖其他两个资源包： [[yii\web\YiiAsset]] 和 [[yii\bootstrap\BootstrapAsset]]
也就是该资源包的CSS和JavaScript文件要在这两个依赖包的文件包含 *之后* 才包含。

Asset dependencies are transitive. This means if bundle A depends on B which depends on C, A will depend on C, too.
资源依赖关系是可传递，也就是人说A依赖B，B依赖C，那么A也依赖C。


### Asset Options <a name="asset-options"></a>
### 资源选项 <a name="asset-options"></a>

You can specify the [[yii\web\AssetBundle::cssOptions|cssOptions]] and [[yii\web\AssetBundle::jsOptions|jsOptions]]
properties to customize the way that CSS and JavaScript files are included in a page. The values of these properties
will be passed to the [[yii\web\View::registerCssFile()]] and [[yii\web\View::registerJsFile()]] methods, respectively, when
they are called by the [view](structure-views.md) to include CSS and JavaScript files.
可指定[[yii\web\AssetBundle::cssOptions|cssOptions]] 和 [[yii\web\AssetBundle::jsOptions|jsOptions]]
属性来自定义页面包含CSS和JavaScript文件的方式，
这些属性值会分别传递给 [[yii\web\View::registerCssFile()]] 和 [[yii\web\View::registerJsFile()]] 方法，
在[视图](structure-views.md) 调用这些方法包含CSS和JavaScript文件时。

> Note: The options you set in a bundle class apply to *every* CSS/JavaScript file in the bundle. If you want to
  use different options for different files, you should create separate asset bundles, and use one set of options
  in each bundle.
> 注意: 在资源包类中设置的选项会应用到该包中 *每个* CSS/JavaScript 文件，如果想对每个文件使用不同的选项，
  应创建不同的资源包并在每个包中使用一个选项集。

For example, to conditionally include a CSS file for browsers that are IE9 or above, you can use the following option:
例如，只想IE9或更高的浏览器包含一个CSS文件，可以使用如下选项：

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

This will cause a CSS file in the bundle to be included using the following HTML tags:
这会是包中的CSS文件使用以下HTML标签包含进来：

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

To wrap link tag with `<noscript>` the following can be used:
为链接标签包含`<noscript>`可使用如下代码：

```php
public $cssOptions = ['noscript' => true];
```

To include a JavaScript file in the head section of a page (by default, JavaScript files are included at the end
of the body section), use the following option:
为使JavaScript文件包含在页面head区域（JavaScript文件默认包含在body的结束处）使用以下选项：

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```


### Bower and NPM Assets <a name="bower-npm-assets"></a>
### Bower 和 NPM 资源 <a name="bower-npm-assets"></a>

Most JavaScript/CSS package are managed by [Bower](http://bower.io/) and/or [NPM](https://www.npmjs.org/).
If your application or extension is using such a package, it is recommended that you follow these steps to manage
the assets in the library:
大多数 JavaScript/CSS 包通过[Bower](http://bower.io/) 和/或 [NPM](https://www.npmjs.org/)管理，
如果你的应用或扩展使用这些包，推荐你遵循以下步骤来管理库中的资源：

1. Modify the `composer.json` file of your application or extension and list the package in the `require` entry.
   You should use `bower-asset/PackageName` (for Bower packages) or `npm-asset/PackageName` (for NPM packages)
   to refer to the library.
1. 修改应用或扩展的 `composer.json` 文件将包列入`require` 中，
   应使用`bower-asset/PackageName` (Bower包) 或 `npm-asset/PackageName` (NPM包)来对应库。
2. Create an asset bundle class and list the JavaScript/CSS files that you plan to use in your application or extension.
   You should specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property as `@bower/PackageName` or `@npm/PackageName`.
   This is because Composer will install the Bower or NPM package in the directory corresponding to this alias.
2. 创建一个资源包类并将你的应用或扩展要使用的JavaScript/CSS 文件列入到类中，
   应设置 [[yii\web\AssetBundle::sourcePath|sourcePath]] 属性为`@bower/PackageName` 或 `@npm/PackageName`，
   因为根据别名Composer会安装Bower或NPM包到对应的目录下。

> Note: Some packages may put all their distributed files in a subdirectory. If this is the case, you should specify
  the subdirectory as the value of [[yii\web\AssetBundle::sourcePath|sourcePath]]. For example, [[yii\web\JqueryAsset]]
  uses `@bower/jquery/dist` instead of `@bower/jquery`.
> 注意: 一些包会将它们分布式文件放到一个子目录中，对于这种情况，应指定子目录作为
  [[yii\web\AssetBundle::sourcePath|sourcePath]]属性值，例如，[[yii\web\JqueryAsset]]使用 `@bower/jquery/dist` 而不是 `@bower/jquery`。


## Using Asset Bundles <a name="using-asset-bundles"></a>
## 使用资源包 <a name="using-asset-bundles"></a>

To use an asset bundle, register it with a [view](structure-views.md) by calling the [[yii\web\AssetBundle::register()]]
method. For example, in a view template you can register an asset bundle like the following:
为使用资源包，在[视图](structure-views.md)中调用[[yii\web\AssetBundle::register()]]方法先注册资源，
例如，在视图模板可使用如下代码注册资源包：

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this 代表视图对象
```

If you are registering an asset bundle in other places, you should provide the needed view object. For example,
to register an asset bundle in a [widget](structure-widgets.md) class, you can get the view object by `$this->view`.
如果在其他地方注册资源包，应提供视图对象，如在 [小部件](structure-widgets.md) 类中注册资源包，
可以通过 `$this->view` 获取视图对象。

When an asset bundle is registered with a view, behind the scene Yii will register all its dependent asset bundles.
And if an asset bundle is located in a directory inaccessible through the Web, it will be published to a Web directory.
Later when the view renders a page, it will generate `<link>` and `<script>` tags for the CSS and JavaScript files
listed in the registered bundles. The order of these tags is determined by the dependencies among
the registered bundles and the order of the assets listed in the [[yii\web\AssetBundle::css]] and [[yii\web\AssetBundle::js]]
properties.
当在视图中注册一个资源包时，在背后Yii会注册它所依赖的资源包，如果资源包是放在Web不可访问的目录下，会被发布到可访问的目录，
后续当视图渲染页面时，会生成这些注册包包含的CSS和JavaScript文件对应的`<link>` 和 `<script>` 标签，
这些标签的先后顺序取决于资源包的依赖关系以及在 [[yii\web\AssetBundle::css]]和[[yii\web\AssetBundle::js]] 的列出来的前后顺序。


### Customizing Asset Bundles <a name="customizing-asset-bundles"></a>
### 自定义资源包 <a name="customizing-asset-bundles"></a>

Yii manages asset bundles through an application component named `assetManager` which is implemented by [[yii\web\AssetManager]].
By configuring the [[yii\web\AssetManager::bundles]] property, it is possible to customize the behavior of an asset bundle.
For example, the default [[yii\web\JqueryAsset]] asset bundle uses the `jquery.js` file from the installed
jquery Bower package. To improve the availability and performance, you may want to use a version hosted by Google.
This can be achieved by configuring `assetManager` in the application configuration like the following:
Yii通过名为 `assetManager`的应用组件实现[[yii\web\AssetManager] 来管理应用组件，
通过配置[[yii\web\AssetManager::bundles]] 属性，可以自定义资源包的行为，
例如，[[yii\web\JqueryAsset]] 资源包默认从jquery Bower包中使用`jquery.js` 文件，
为了提高可用性和性能，你可能需要从Google服务器上获取jquery文件，可以在应用配置中配置`assetManager`，如下所示：

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

You can configure multiple asset bundles similarly through [[yii\web\AssetManager::bundles]]. The array keys
should be the class names (without the leading backslash) of the asset bundles, and the array values should
be the corresponding [configuration arrays](concept-configurations.md).
可通过类似[[yii\web\AssetManager::bundles]]配置多个资源包，数组的键应为资源包的类名（最开头不要反斜杠），
数组的值为对应的[配置数组](concept-configurations.md).

> 提示: 可以根据条件判断使用哪个资源，如下示例为如何在开发环境用`jquery.js`，否则用`jquery.min.js`：
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

You can disable one or multiple asset bundles by associating `false` with the names of the asset bundles
that you want to disable. When you register a disabled asset bundle with a view, none of its dependent bundles
will be registered, and the view will also not include any of the assets in the bundle in the page it renders.
For example, to disable [[yii\web\JqueryAsset]], you can use the following configuration:
可以设置资源包的名称对应`false`来禁用想禁用的一个或多个资源包，当视图中注册一个禁用资源包，
视图不会包含任何该包的资源以及不会注册它所依赖的包，例如，为禁用[[yii\web\JqueryAsset]]，可以使用如下配置：

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

You can also disable *all* asset bundles by setting [[yii\web\AssetManager::bundles]] as `false`.
可设置[[yii\web\AssetManager::bundles]]为`false`禁用 *所有* 的资源包。


### Asset Mapping <a name="asset-mapping"></a>
### 资源部署 <a name="asset-mapping"></a>

Sometimes you want to "fix" incorrect/incompatible asset file paths used in multiple asset bundles. For example,
bundle A uses `jquery.min.js` of version 1.11.1, and bundle B uses `jquery.js` of version 2.1.1. While you can
fix the problem by customizing each bundle, an easier way is to use the *asset map* feature to map incorrect assets
to the desired ones. To do so, configure the [[yii\web\AssetManager::assetMap]] property like the following:
有时你想"修复" 多个资源包中资源文件的错误/不兼容，例如包A使用1.11.1版本的`jquery.min.js`，
包B使用2.1.1版本的`jquery.js`，可自定义每个包来解决这个问题，更好的方式是使用*资源部署*特性来不熟不正确的资源为想要的，
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

The keys of [[yii\web\AssetManager::assetMap|assetMap]] are the asset names that you want to fix, and the values
are the desired asset paths. When you register an asset bundle with a view, each relative asset file in its
[[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] arrays will be examined against this map.
If any of the keys is found to be the last part of an asset file (which is prefixed with [[yii\web\AssetBundle::sourcePath]]
if available), the corresponding value will replace the asset and be registered with the view.
For example, an asset file `my/path/to/jquery.js` matches a key `jquery.js`.
[[yii\web\AssetManager::assetMap|assetMap]]的键为你想要修复的资源名，值为你想要使用的资源路径，
当视图注册资源包，在[[yii\web\AssetBundle::css|css]] 和 [[yii\web\AssetBundle::js|js]] 数组中每个相关资源文件会和该部署进行对比，
如果数组任何键对比为资源文件的最后文件名（如果有的话前缀为 [[yii\web\AssetBundle::sourcePath]]），对应的值为替换原来的资源。
例如，资源文件`my/path/to/jquery.js` 匹配键 `jquery.js`.

> Note: Only assets specified using relative paths are subject to asset mapping. And the target asset paths
  should be either absolute URLs or paths relative to [[yii\web\AssetManager::basePath]].
> 注意: 只有相对相对路径指定的资源对应到资源部署，替换的资源路径可以为绝对路径，也可为和[[yii\web\AssetManager::basePath]]相关的路径。


### Asset Publishing <a name="asset-publishing"></a>
### 资源发布 <a name="asset-publishing"></a>

As aforementioned, if an asset bundle is located in a directory that is not Web accessible, its assets will be copied
to a Web directory when the bundle is being registered with a view. This process is called *asset publishing*, and is done
automatically by the [[yii\web\AssetManager|asset manager]].
如前所述，如果资源包放在Web不能访问的目录，当视图注册资源时资源会被拷贝到一个Web可访问的目录中，
这个过程称为*资源发布*，[[yii\web\AssetManager|asset manager]]会自动处理该过程。

By default, assets are published to the directory `@webroot/assets` which corresponds to the URL `@web/assets`.
You may customize this location by configuring the [[yii\web\AssetManager::basePath|basePath]] and
[[yii\web\AssetManager::baseUrl|baseUrl]] properties.
资源默认会发布到`@webroot/assets`目录，对应的URL为`@web/assets`，
可配置[[yii\web\AssetManager::basePath|basePath]] 和 [[yii\web\AssetManager::baseUrl|baseUrl]] 属性自定义发布位置。

Instead of publishing assets by file copying, you may consider using symbolic links, if your OS and Web server allow.
This feature can be enabled by setting [[yii\web\AssetManager::linkAssets|linkAssets]] to be true.
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

With the above configuration, the asset manager will create a symbolic link to the source path of an asset bundle
when it is being published. This is faster than file copying and can also ensure that the published assets are
always up-to-date.
使用以上配置，资源管理器会创建一个符号链接到要发布的资源包源路径，这比拷贝文件方式快并能确保发布的资源一直为最新的。


## Commonly Used Asset Bundles <a name="common-asset-bundles"></a>
## 常用资源包 <a name="common-asset-bundles"></a>

The core Yii code has defined many asset bundles. Among them, the following bundles are commonly used and may
be referenced in your application or extension code.
Yii框架定义许多资源包，如下资源包是最常用，可在你的应用或扩展代码中引用它们。

- [[yii\web\YiiAsset]]: It mainly includes the `yii.js` file which implements a mechanism of organizing JavaScript code
  in modules. It also provides special support for `data-method` and `data-confirm` attributes and other useful features.
- [[yii\web\JqueryAsset]]: It includes the `jquery.js` file from the jQuery bower package.
- [[yii\bootstrap\BootstrapAsset]]: It includes the CSS file from the Twitter Bootstrap framework.
- [[yii\bootstrap\BootstrapPluginAsset]]: It includes the JavaScript file from the Twitter Bootstrap framework for
  supporting Bootstrap JavaScript plugins.
- [[yii\jui\JuiAsset]]: It includes the CSS and JavaScript files from the jQuery UI library.
- [[yii\web\YiiAsset]]: 主要包含`yii.js` 文件，该文件完成模块JavaScript代码组织功能，
  也为 `data-method` 和 `data-confirm`属性提供特别支持和其他有用的功能。
- [[yii\web\JqueryAsset]]: 包含从jQuery bower 包的`jquery.js`文件。 
- [[yii\bootstrap\BootstrapAsset]]: 包含从Twitter Bootstrap 框架的CSS文件。
- [[yii\bootstrap\BootstrapPluginAsset]]: 包含从Twitter Bootstrap 框架的JavaScript 文件来支持Bootstrap JavaScript插件。
- [[yii\jui\JuiAsset]]: 包含从jQuery UI库的CSS 和 JavaScript 文件。

If your code depends on jQuery, jQuery UI or Bootstrap, you should use these predefined asset bundles rather than
creating your own versions. If the default setting of these bundles do not satisfy your needs, you may customize them 
as described in the [Customizing Asset Bundle](#customizing-asset-bundles) subsection. 
如果你的代码需要jQuery, jQuery UI 或 Bootstrap，应尽量使用这些预定义资源包而非自己创建，
如果这些包的默认配置不能满足你的需求，可以自定义配置，详情参考[自定义资源包](#customizing-asset-bundles)。


## Asset Conversion <a name="asset-conversion"></a>
## 资源转换 <a name="asset-conversion"></a>

Instead of directly writing CSS and/or JavaScript code, developers often write them in some extended syntax and
use special tools to convert it into CSS/JavaScript. For example, for CSS code you may use [LESS](http://lesscss.org/)
or [SCSS](http://sass-lang.com/); and for JavaScript you may use [TypeScript](http://www.typescriptlang.org/).
除了直接编写CSS 和/或 JavaScript代码，开发人员经常使用扩展语法来编写，再使用特殊的工具将它们转换成CSS/Javascript。
例如，对于CSS代码可使用[LESS](http://lesscss.org/) 或 [SCSS](http://sass-lang.com/)，
对于JavaScript 可使用 [TypeScript](http://www.typescriptlang.org/)。

You can list the asset files in extended syntax in [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]]
in an asset bundle. For example,
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

When you register such an asset bundle with a view, the [[yii\web\AssetManager|asset manager]] will automatically
run the pre-processor tools to convert assets in recognized extended syntax into CSS/JavaScript. When the view
finally renders a page, it will include the CSS/JavaScript files in the page, instead of the original assets
in extended syntax.
当在视图中注册一个这样的资源包，[[yii\web\AssetManager|asset manager]]资源管理器会自动运行预处理工具将使用扩展语法
的资源转换成CSS/JavaScript，当视图最终渲染页面时，在页面中包含的是CSS/Javascipt文件，而不是原始的扩展语法代码文件。
in extended syntax.

Yii uses the file name extensions to identify which extended syntax an asset is in. By default it recognizes
the following syntax and file name extensions:
Yii使用文件扩展名来表示资源使用哪种扩展语法，默认可以识别如下语法和文件扩展名：

- [LESS](http://lesscss.org/): `.less`
- [SCSS](http://sass-lang.com/): `.scss`
- [Stylus](http://learnboost.github.io/stylus/): `.styl`
- [CoffeeScript](http://coffeescript.org/): `.coffee`
- [TypeScript](http://www.typescriptlang.org/): `.ts`

Yii relies on the installed pre-processor tools to convert assets. For example, to use [LESS](http://lesscss.org/)
you should install the `lessc` pre-processor command.
Yii依靠安装的预处理公斤来转换资源，例如，为使用[LESS](http://lesscss.org/)，应安装`lessc` 预处理命令。

You can customize the pre-processor commands and the supported extended syntax by configuring
[[yii\web\AssetManager::converter]] like the following:
可配置[[yii\web\AssetManager::converter]]自定义预处理命令和支持的扩展语法，如下所示：

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

In the above we specify the supported extended syntax via the [[yii\web\AssetConverter::commands]] property.
The array keys are the file extension names (without leading dot), and the array values are the resulting
asset file extension names and the commands for performing the asset conversion. The tokens `{from}` and `{to}`
in the commands will be replaced with the source asset file paths and the target asset file paths.
如上所示，通过[[yii\web\AssetConverter::commands]] 属性指定支持的扩展语法，
数组的键为文件扩展名（前面不要.），数组的值为目标资源文件扩展名和执行资源转换的命令，
命令中的标记 `{from}` 和`{to}`会分别被源资源文件路径和目标资源文件路径替代。

> Info: There are other ways of working with assets in extended syntax, besides the one described above.
  For example, you can use build tools such as [grunt](http://gruntjs.com/) to monitor and automatically
  convert assets in extended syntax. In this case, you should list the resulting CSS/JavaScript files in
  asset bundles rather than the original files.
> 补充: 除了以上方式，也有其他的方式来处理扩展语法资源，例如，可使用编译工具如[grunt](http://gruntjs.com/)
  来监控并自动转换扩展语法资源，此时，应使用资源包中编译后的CSS/Javascript文件而不是原始文件。


## Combining and Compressing Assets <a name="combining-compressing-assets"></a>
## 合并和压缩资源 <a name="combining-compressing-assets"></a>

A Web page can include many CSS and/or JavaScript files. To reduce the number of HTTP requests and the overall
download size of these files, a common practice is to combine and compress multiple CSS/JavaScript files into 
one or very few files, and then include these compressed files instead of the original ones in the Web pages.  
一个Web页面可以包含很多CSS 和/或 JavaScript 文件，为减少HTTP 请求和这些下载文件的大小，
通常的方式是在页面中合并并压缩多个CSS/JavaScript 文件为一个或很少的几个文件，并使用压缩后的文件而不是原始文件。
 
> Info: Combining and compressing assets is usually needed when an application is in production mode. 
  In development mode, using the original CSS/JavaScript files is often more convenient for debugging purpose.
> 补充: 合并和压缩资源通常在应用在产品上线模式，在开发模式下使用原始的CSS/JavaScript更方便调试。

In the following, we introduce an approach to combine and compress asset files without the need of modifying
your existing application code.
接下来介绍一种合并和压缩资源文件而不需要修改已有代码的方式：

1. Find out all asset bundles in your application that you plan to combine and compress.
2. Divide these bundles into one or a few groups. Note that each bundle can only belong to a single group.
3. Combine/compress the CSS files in each group into a single file. Do this similarly for the JavaScript files.
4. Define a new asset bundle for each group:
   * Set the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties to be
     the combined CSS and JavaScript files, respectively.
   * Customize the asset bundles in each group by setting their [[yii\web\AssetBundle::css|css]] and 
     [[yii\web\AssetBundle::js|js]] properties to be empty, and setting their [[yii\web\AssetBundle::depends|depends]]
     property to be the new asset bundle created for the group.
1. 找出应用中所有你想要合并和压缩的资源包，
2. 将这些包分成一个或几个组，注意每个包只能属于其中一个组，
3. 合并/压缩每个组里CSS文件到一个文件，同样方式处理JavaScript文件，
4. 为每个组定义新的资源包：
   * 设置[[yii\web\AssetBundle::css|css]] 和 [[yii\web\AssetBundle::js|js]] 属性分别为压缩后的CSS和JavaScript文件；
   * 自定义设置每个组内的资源包，设置资源包的[[yii\web\AssetBundle::css|css]] 和 [[yii\web\AssetBundle::js|js]] 属性为空, 
     并设置它们的 [[yii\web\AssetBundle::depends|depends]] 属性为每个组新创建的资源包。

Using this approach, when you register an asset bundle in a view, it causes the automatic registration of
the new asset bundle for the group that the original bundle belongs to. And as a result, the combined/compressed 
asset files are included in the page, instead of the original ones.
使用这种方式，当在视图中注册资源包时，会自动触发原始包所属的组资源包的注册，然后，页面就会包含以合并/压缩的资源文件，
而不是原始文件。


### An Example <a name="example"></a>
### 示例 <a name="example"></a>

Let's use an example to further explain the above approach. 
使用一个示例来解释以上这种方式：

Assume your application has two pages X and Y. Page X uses asset bundle A, B and C, while Page Y uses asset bundle B, C and D. 
鉴定你的应用有两个页面X 和 Y，页面X使用资源包A，B和C，页面Y使用资源包B，C和D。

You have two ways to divide these asset bundles. One is to use a single group to include all asset bundles, the
other is to put (A, B, C) in Group X, and (B, C, D) in Group Y. Which one is better? It depends. The first way
has the advantage that both pages share the same combined CSS and JavaScript files, which makes HTTP caching
more effective. On the other hand, because the single group contains all bundles, the size of the combined CSS and 
JavaScript files will be bigger and thus increase the initial file transmission time. In this example, we will use 
the first way, i.e., use a single group to contain all bundles.
有两种方式划分这些资源包，一种使用一个组包含所有资源包，另一种是将（A,B,C）放在组X，（B，C，C）放在组Y，
哪种方式更好？第一种方式优点是两个页面使用相同的已合并CSS和JavaScript文件使HTTP缓存更高效，另一方面，由于单个组包含所有文件，
已合并的CSS和Javascipt文件会更大，因此会增加文件传输时间，在这个示例中，我们使用第一种方式，也就是用一个组包含所有包。

> Info: Dividing asset bundles into groups is not trivial task. It usually requires analysis about the real world
  traffic data of various assets on different pages. At the beginning, you may start with a single group for simplicity. 
> 补充: 将资源包分组并不是无价值的，通常要求分析现实中不同页面各种资源的数据量，开始时为简便使用一个组。

Use existing tools (e.g. [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) to combine and compress CSS and JavaScript files in 
all the bundles. Note that the files should be combined in the order that satisfies the dependencies among the bundles. 
For example, if Bundle A depends on B which depends on both C and D, then you should list the asset files starting 
from C and D, followed by B and finally A. 
在所有包中使用工具(例如 [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) 来合并和压缩CSS和JavaScript文件，
注意合并后的文件满足包间的先后依赖关系，例如，如果包A依赖B，B依赖C和D，那么资源文件列表以C和D开始，然后为B最后为A。

After combining and compressing, we get one CSS file and one JavaScript file. Assume they are named as 
`all-xyz.css` and `all-xyz.js`, where `xyz` stands for a timestamp or a hash that is used to make the file name unique
to avoid HTTP caching problem.
合并和压缩之后，会得到一个CSS文件和一个JavaScript文件，假定它们的名称为`all-xyz.css` 和 `all-xyz.js`，
`xyz` 为使文件名唯一以避免HTTP缓存问题的时间戳或哈希值。
 
现在到最后一步了，在应用配置中配置[[yii\web\AssetManager|asset manager]] 资源管理器如下所示：

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

As explained in the [Customizing Asset Bundles](#customizing-asset-bundles) subsection, the above configuration
changes the default behavior of each bundle. In particular, Bundle A, B, C and D no longer have any asset files.
They now all depend on the `all` bundle which contains the combined `all-xyz.css` and `all-xyz.js` files.
Consequently, for Page X, instead of including the original source files from Bundle A, B and C, only these
two combined files will be included; the same thing happens to Page Y.
如[自定义资源包](#customizing-asset-bundles) 小节中所述，如上配置改变每个包的默认行为，
特别是包A、B、C和D不再包含任何资源文件，都依赖包含合并后的`all-xyz.css` 和 `all-xyz.js`文件的包`all`，
因此，对于页面X会包含这两个合并后的文件而不是包A、B、C的原始文件，对于页面Y也是如此。

There is one final trick to make the above approach work more smoothly. Instead of directly modifying the
application configuration file, you may put the bundle customization array in a separate file and conditionally
include this file in the application configuration. For example,
最后有个方法更好地处理上述方式，除了直接修改应用配置文件，可将自定义包数组放到一个文件，在应用配置中根据条件包含该文件，例如：

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php')),  
        ],
    ],
];
```

That is, the asset bundle configuration array is saved in `assets-prod.php` for production mode, and
`assets-dev.php` for non-production mode.
如上所示，在产品上线模式下资源包数组存储在`assets-prod.php`文件中，不是产品上线模式存储在`assets-dev.php`文件中。


### Using the `asset` Command <a name="using-asset-command"></a>
### 使用 `asset` 命令 <a name="using-asset-command"></a>

Yii provides a console command named `asset` to automate the approach that we just described.
Yii提供一个名为`asset`控制台命令来使上述操作自动处理。

To use this command, you should first create a configuration file to describe what asset bundles should
be combined and how they should be grouped. You can use the `asset/template` sub-command to generate
a template first and then modify it to fit for your needs.
为使用该命令，应先创建一个配置文件设置哪些资源包要合并以及分组方式，可使用`asset/template` 子命令来生成一个模板，
然后修改模板成你想要的。

```
yii asset/template assets.php
```

The command generates a file named `assets.php` in the current directory. The content of this file looks like the following:
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

You should modify this file and specify which bundles you plan to combine in the `bundles` option. In the `targets` 
option you should specify how the bundles should be divided into groups. You can specify one or multiple groups, 
as aforementioned.
应修改该文件的`bundles`的选项指定哪些包你想要合并，在`targets`选项中应指定这些包如何分组，如前述的可以指定一个或多个组。

> Note: Because the alias `@webroot` and `@web` are not available in the console application, you should
  explicitly define them in the configuration.
> 注意: 由于在控制台应用别名 `@webroot` and `@web` 不可用，应在配置中明确指定它们。

JavaScript files are combined, compressed and written to `js/all-{hash}.js` where {hash} is replaced with the hash of
the resulting file.
JavaScript文件会被合并压缩后写入到`js/all-{hash}.js`文件，其中 {hash} 会被结果文件的哈希值替换。

The `jsCompressor` and `cssCompressor` options specify the console commands or PHP callbacks for performing
JavaScript and CSS combining/compressing. By default Yii uses [Closure Compiler](https://developers.google.com/closure/compiler/) 
for combining JavaScript files and [YUI Compressor](https://github.com/yui/yuicompressor/) for combining CSS files. 
You should install tools manually or adjust these options to use your favorite tools.
`jsCompressor` 和 `cssCompressor` 选项指定控制台命令或PHP回调函数来执行JavaScript和CSS合并和压缩，
Yii默认使用[Closure Compiler](https://developers.google.com/closure/compiler/)来合并JavaScript文件， 
使用[YUI Compressor](https://github.com/yui/yuicompressor/)来合并CSS文件，
你应手工安装这些工具或修改选项使用你喜欢的工具。


With the configuration file, you can run the `asset` command to combine and compress the asset files
and then generate a new asset bundle configuration file `assets-prod.php`:
根据配置文件，可执行`asset` 命令来合并和压缩资源文件并生成一个新的资源包配置文件`assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

The generated configuration file can be included in the application configuration, like described in
the last subsection.
生成的配置文件可以在应用配置中包含，如最后一小节所描述的。


> Info: Using the `asset` command is not the only option to automate the asset combining and compressing process.
  You can use the excellent task runner tool [grunt](http://gruntjs.com/) to achieve the same goal.
> 补充: 使用`asset` 命令并不是唯一一种自动合并和压缩过程的方法，可使用优秀的工具[grunt](http://gruntjs.com/)来完成这个过程。
