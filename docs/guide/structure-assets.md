Assets
======

> Note: This section is under writing.

An asset in Yii is a file that may be referenced or linked in a Web page. It can be a CSS file, a JavaScript file,
an image or video file, etc. For simple Web applications, assets may be managed manually - you place them in a Web folder
and reference them using their URLs in your Web pages. However, if an application is complicated, or if it uses
many third-party extensions, manual management of assets can soon become a headache. For example, how will you ensure
one JavaScript file is always included before another and the same JavaScript file is not included twice?
How will you handle asset files required by an extension which you do not want to dig into its internals?
How will you combine and compress multiple CSS/JavaScript files into a single one when you deploy the application
to production? In this section, we will describe the asset management capability offered by Yii to help you alleviate
all these problems.


## Asset Bundles <a name="asset-bundles"></a>

Assets are organized in *bundles*. An asset bundle represents a collection of asset files located
under a single directory. It lists which CSS and JavaScript files are in this collection and should be included
in a page where the bundle is used.


### Defining Asset Bundles <a name="defining-asset-bundles"></a>

An asset bundle is defined in terms of a PHP class extending from [[yii\web\AssetBundle]].
In this class, you use certain class properties to specify where the asset files are located, what CSS/JavaScript
files the bundle contains, and so on. The class should be namespaced and autoloadable. Its name is used
as the name of the asset bundle.

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

The `AppAsset` class basically specifies that the asset files are located under the `@webroot` directory which
is corresponding to the URL `@web`. The bundle only contains an asset file named `css/site.css`. The bundle
depends on two other bundles: `yii\web\YiiAsset` and `yii\bootstrap\BootstrapAsset`.

The following list explains the possible properties that you can set in an asset bundle class:

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
    can be determined by prepending [[basePath]] to the relative path, and the actual URL
    of the file can be determined by prepending [[baseUrl]] to the relative path.
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
  [[yii\web\AssetManager::publish()]] method when it is called to publish source asset files to a Web folder.
  This is only used if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property.


#### Asset Locations <a name="asset-locations"></a>

Assets, based on their location, can be classified as:

* source assets: the asset files are located together with PHP source code which cannot be directly accessed via Web.
  In order for source assets to be Web accessible, they should be published and turned in *published assets*.
* published assets: the asset files are located in a Web folder and can thus be directly accessed via Web.
* external assets: the asset files are located on a Web server that is different from the one hosting your Web
  application.

For assets that directly belong to an application, it is recommended that you place them in a Web folder
to avoid the unnecessary asset publishing process. This is why `AppAsset` specifies [[yii\web\AssetBundle::basePath|basePath]]
without [[yii\web\AssetBundle::sourcePath|sourcePath]].

For assets belonging to an [extension](structure-extensions.md), as they are in a folder that is not Web accessible,
you have to specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property when declaring the corresponding
asset bundle.

> Note: Do not use `@webroot/assets` as the [[yii\web\AssetBundle::sourcePath|source path]].
  This folder is used by default by the [[yii\web\AssetManager|asset manager]] to keep the asset files
  published from their source location. Any content in this folder are considered temporarily and may be subject
  to removal.


#### Asset Dependencies <a name="asset-dependencies"></a>

When you include multiple CSS or JavaScript files on a Web page, they have to follow certain orders to avoid
unexpected overriding. For example, if you are using a jQuery UI widget in a Web page, you have to make sure
the jQuery JavaScript file is included before the jQuery UI JavaScript file is included.
We call such ordering the dependencies among assets.

Asset dependencies are mainly specified through the [[yii\web\AssetBundle::depends]] property of asset bundles.
In the `AppAsset` example, the asset bundle depends on two other asset bundles: `yii\web\YiiAsset` and
`yii\bootstrap\BootstrapAsset`. And for the jQuery UI example just described, the asset bundle is declared as follows:

```php
class JuiAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-ui';
    public $js = [
        'jquery-ui.js',
    ];
    public $css = [
        'themes/smoothness/jquery-ui.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
```


## Using Asset Bundles <a name="using-asset-bundles"></a>

To use an asset bundle, register it with a [view](structure-views.md) like the following in a view template:

```php
use app\assets\AppAsset;
AppAsset::register($this);
```

where `$this` refers to the [[yii\web\View|view]] object. If you are registering an asset bundle in a PHP class,
you should provide the needed the view object. For example, to register an asset bundle in
a [widget](structure-widgets.md) class, you can obtain the view object by `$this->view`.

When an asset bundle is registered, behind the scene Yii will register all its dependent asset bundles. And
when a page is being rendered, `<link>` and `<script>` tags will be generated in the page for the CSS and
JavaScript files in every registered asset bundle. During this process, if an asset bundle is not in
a Web accessible folder, it will be published first.


## Asset Publishing <a name="asset-publishing"></a>

When an asset file is located in a folder that is not Web accessible, it should be copied to a Web accessible
folder before being referenced or linked in a Web page. This process is called *asset publishing*, and is done
automatically by the [[yii\web\AssetManager|asset manager]].

### Enabling symlinks

Asset manager is able to use symlinks instead of copying files. It is turned off by default since symlinks are often
disabled on shared hosting. If your hosting environment supports symlinks you certainly should enable the feature via
application config:

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

There are two main benefits in enabling it. First it is faster since no copying is required and second is that assets
will always be up to date with source files.


#### Language-specific asset bundle

If you need to define an asset bundle that includes JavaScript file depending on the language you can do it the
following way:

```php
class LanguageAsset extends AssetBundle
{
    public static $language;
    public $sourcePath = '@app/assets/language';
    public $js = [
    ];

    public function init()
    {
        parent::init();
        $language = self::$language ? self::$language : Yii::$app->language;
        $this->js[] = 'language-' . $language . '.js';
    }
}
```

In order to set language use the following code when registering an asset bundle in a view:

```php
LanguageAsset::$language = $language;
LanguageAsset::register($this);
```


#### Setting special options <a name="setting-special-options"></a>

Asset bundles allow setting specific options for the files to be published.
This can be done by configuring the [[yii\web\AssetBundle::$jsOptions|$jsOptions]],
[[yii\web\AssetBundle::$cssOptions|$cssOptions]] or [[yii\web\AssetBundle::$publishOptions|$publishOptions]]
property of the asset bundle.

Some of these options are described in the following:

- For setting conditional comments for your CSS files you can set the following option:

  ```php
  public $cssOptions = ['condition' => 'lte IE9'];
  ```

  This will result in a link tag generated as follows: `<!--[if lte IE9]><link .../><![endif]-->`.
  You can only define one condition per asset bundle, if you have multiple files with different conditions,
  you have to define multiple assets bundles.

- For javascipt files you can define the position where they should be added in the HTML.
  You can choose one of the following positions:

  - [[yii\web\View::POS_HEAD]]: in the head section
  - [[yii\web\View::POS_BEGIN]]: at the beginning of the body section
  - [[yii\web\View::POS_END]]: at the end of the body section. This is the default value.

  Example for putting all javascript files to the end of the body.

  ```php
  public $jsOptions = ['position' => \yii\web\View::POS_END];
  ```

  This option is also reflected when resolving dependencies.

- For further javascript options, see [[yii\helpers\Html::jsFile()]].


#### Overriding asset bundles

Sometimes you need to override some asset bundles application wide. A good example is loading jQuery from CDN instead
of your own server. In order to do it we need to configure `assetManager` application component via config file. In case
of basic application it is `config/web.php`:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                     'sourcePath' => null,
                     'js' => ['//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js']
                ],
            ],
        ],
    ],
];
```

In the above we're adding asset bundle definitions to the [[yii\web\AssetManager::bundles|bundles]] property of asset manager. Keys are fully
qualified class names to asset bundle classes we want to override while values are key-value arrays of class properties
and corresponding values to set.

Setting `sourcePath` to `null` tells asset manager not to copy anything while `js` overrides local files with a link
to CDN.

> Tip: You may also use this procedure to configure different scripts dependent on the environment. For example
> use minified files in production and normal files in development:
>
>  ```php
'yii\web\JqueryAsset' => [
    'js' => [
        YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
    ]
],
```



Compressing and combining assets
--------------------------------

To improve application performance you can compress and then combine several CSS or JS files into lesser number of files
therefore reducing number of HTTP requests and overall download size needed to load a web page.  Yii provides a console
command that allows you to do both.

### Preparing configuration

In order to use `asset` command you should prepare a configuration first. A template for it can be generated using

```
yii asset/template /path/to/myapp/config.php
```

The template itself looks like the following:

```php
<?php
/**
 * Configuration file for the "yii asset" console command.
 * Note that in the console environment, some path aliases like '@webroot' and '@web' may not exist.
 * Please define these missing path aliases.
 */
return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // The list of asset bundles to compress:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'app\config\AllAsset' => [
            'basePath' => 'path/to/web',
            'baseUrl' => '',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
        'basePath' => __DIR__,
        'baseUrl' => '',
    ],
];
```

In the above keys are `properties` of `AssetController`. `bundles` list contains bundles that should be compressed. These are typically what's used by application.
`targets` contains a list of bundles that define how resulting files will be written. In our case we're writing
everything to `path/to/web` that can be accessed like `http://example.com/` i.e. it is website root directory.

> Note: in the console environment some path aliases like '@webroot' and '@web' may not exist,
  so corresponding paths inside the configuration should be specified directly.

JavaScript files are combined, compressed and written to `js/all-{hash}.js` where {hash} is replaced with the hash of
the resulting file.

`jsCompressor` and `cssCompressor` are console commands or PHP callbacks, which should perform JavaScript and CSS files
compression correspondingly. You should adjust these values according to your environment.
By default Yii relies on [Closure Compiler](https://developers.google.com/closure/compiler/) for JavaScript file compression,
and on [YUI Compressor](https://github.com/yui/yuicompressor/). You should install this utilities manually, if you wish to use them.

### Providing compression tools

The command relies on external compression tools that are not bundled with Yii so you need to provide CSS and JS
compressors which are correspondingly specified via `cssCompressor` and `jsCompression` properties. If compressor is
specified as a string it is treated as a shell command template which should contain two placeholders: `{from}` that
is replaced by source file name and `{to}` that is replaced by output file name. Another way to specify compressor is
to use any valid PHP callback.

By default for JavaScript compression Yii tries to use
[Google Closure compiler](https://developers.google.com/closure/compiler/) that is expected to be in a file named
`compiler.jar`.

For CSS compression Yii assumes that [YUI Compressor](https://github.com/yui/yuicompressor/) is looked up in a file
named `yuicompressor.jar`.

In order to compress both JavaScript and CSS, you need to download both tools and place them under the directory
containing your `yii` console bootstrap file. You also need to install JRE in order to run these tools.

You may customize the compression commands (e.g. changing the location of the jar files) in the `config.php` file
like the following,

```php
return [
       'cssCompressor' => 'java -jar path.to.file\yuicompressor.jar  --type css {from} -o {to}',
       'jsCompressor' => 'java -jar path.to.file\compiler.jar --js {from} --js_output_file {to}',
];
```

where `{from}` and `{to}` are tokens that will be replaced with the actual source and target file paths, respectively,
when the `asset` command is compressing every file.


### Performing compression

After configuration is adjusted you can run the `compress` action, using created config:

```
yii asset /path/to/myapp/config.php /path/to/myapp/config/assets_compressed.php
```

Now processing takes some time and finally finished. You need to adjust your web application config to use compressed
assets file like the following:

```php
'components' => [
    // ...
    'assetManager' => [
        'bundles' => require '/path/to/myapp/config/assets_compressed.php',
    ],
],
```

Using asset converter
---------------------

Instead of using CSS and JavaScript directly often developers are using their improved versions such as LESS or SCSS
for CSS or Microsoft TypeScript for JavaScript. Using these with Yii is easy.

First of all, corresponding compression tools should be installed and should be available from where `yii` console
bootstrap file is. The following lists file extensions and their corresponding conversion tool names that Yii converter
recognizes:

- LESS: `less` - `lessc`
- SCSS: `scss`, `sass` - `sass`
- Stylus: `styl` - `stylus`
- CoffeeScript: `coffee` - `coffee`
- TypeScript: `ts` - `tsc`

So if the corresponding tool is installed you can specify any of these in asset bundle:

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

In order to adjust conversion tool call parameters or add new ones you can use application config:

```php
// ...
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
```

In the above we've left two types of extra file extensions. First one is `less` that can be specified in `css` part
of an asset bundle. Conversion is performed via running `lessc {from} {to} --no-color` where `{from}` is replaced with
LESS file path while `{to}` is replaced with target CSS file path. Second one is `ts` that can be specified in `js` part
of an asset bundle. The command that is run during conversion is in the same format that is used for `less`.
