Managing assets
===============

An asset in Yii is a file that is included into the page. It could be CSS, JavaScript or
any other file. The framework provides many ways to work with assets from basics such as adding `<script src="...">` tags
for a file which is covered by the [View section](view.md), to advanced usage such as publishing files that are not
under the webservers document root, resolving JavaScript dependencies or minifying CSS, which we will cover in the following.


Declaring asset bundles
-----------------------

In order to define a set of assets the belong together and should be used on the website you declare a class called
an "asset bundle". The bundle defines a set of asset files and their dependencies on other asset bundles.

Asset files can be located under the webservers accessable directory but also hidden inside of application or vendor
directories. If the latter, the asset bundle will care for publishing them to a directory accessible by the webserver
so they can be included in the website. This feature is useful for extensions so that they can ship all content in one
directory and make installation easier for you.

To define an asset you create a class extending from [[yii\web\AssetBundle]] and set the properties according to your needs.
Here you can see an example asset definition which is part of the basic application template, the
`AppAsset` asset bundle class. It defines assets required application wide:

```php
<?php

use yii\web\AssetBundle as AssetBundle;

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

In the above `$basePath` specifies web-accessible directory assets are served from. It is a base for relative
`$css` and `$js` paths i.e. `@webroot/css/site.css` for `css/site.css`. Here `@webroot` is an [alias][] that points to
application's `web` directory.

`$baseUrl` is used to specify base URL for the same relative `$css` and `$js` i.e. `@web/css/site.css` where `@web`
is an [alias][] that corresponds to your website base URL such as `http://example.com/`.

In case you have asset files under a non web accessible directory, that is the case for any extension, you need
to specify `$sourcePath` instead of `$basePath` and `$baseUrl`. Files will be copied or symlinked from source path
to the `web/assets` directory of your application prior to being registered.
In this case `$basePath` and `$baseUrl` are generated automatically at the time of publishing the asset bundle.

Dependencies on other asset bundles are specified via `$depends` property. It is an array that contains fully qualified
class names of bundle classes that should be published in order for this bundle to work properly.
Javascript and CSS files for `AppAsset` are added to the header after the files of [[yii\web\YiiAsset]] and
[[yii\bootstrap\BootstrapAsset]] in this example.

Here [[yii\web\YiiAsset]] adds Yii's JavaScript library while [[yii\bootstrap\BootstrapAsset]] includes
[Bootstrap](http://getbootstrap.com) frontend framework.

Asset bundles are regular classes so if you need to define another one, just create alike class with unique name. This
class can be placed anywhere but the convention for it is to be under `assets` directory of the application.

Additionally you may specify `$jsOptions`, `$cssOptions` and `$publishOptions` that will be passed to
[[yii\web\View::registerJsFile()]], [[yii\web\View::registerCssFile()]] and [[yii\web\AssetManager::publish()]]
respectively during registering and publising an asset.

[alias]: basics.md#path-aliases "Yii Path alias"


### Language-specific asset bundle

If you need to define an asset bundle that includes JavaScript file depending on the language you can do it the
following way:

```php
class LanguageAsset extends AssetBundle
{
    public $language;
    public $sourcePath = '@app/assets/language';
    public $js = [
    ];

    public function registerAssetFiles($view)
    {
        $language = $this->language ? $this->language : Yii::$app->language;
        $this->js[] = 'language-' . $language . '.js';
        parent::registerAssetFiles($view);
    }
}
```

In order to set language use the following code when registering an asset bundle in a view:

```php
LanguageAsset::register($this)->language = $language;
```


Registering asset bundle
------------------------

Asset bundle classes are typically registered in view files or [widgets](view.md#widgets) that depend on the css or
javascript files for providing its functionality. An exception to this is the `AppAsset` class defined above which is
added in the applications main layout file to be registered on any page of the application.
Registering an asset bundle is as simple as calling the [[yii\web\AssetBundle::register()|register()]] method:

```php
use app\assets\AppAsset;
AppAsset::register($this);
```

Since we're in a view context `$this` refers to `View` class.
To register an asset inside of a widget, the view instance is available as `$this->view`:

```php
AppAsset::register($this->view);
```

> Note: If there is a need to modify third party asset bundles it is recommended to create your own bundles depending
  on third party ones and use CSS and JavaScript features to modify behavior instead of editing files directly or
  copying them over.


Overriding asset bundles
------------------------

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


Enabling symlinks
-----------------

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
            'js' => 'js/all-{ts}.js',
            'css' => 'css/all-{ts}.css',
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

JavaScript files are combined, compressed and written to `js/all-{ts}.js` where {ts} is replaced with current UNIX
timestamp.

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
