Assets
======

An asset in Yii is a file that may be referenced in a Web page. It can be a CSS file, a JavaScript file, an image
or video file, etc. Assets are located in Web-accessible directories and are directly served by Web servers.

It is often preferable to manage assets programmatically. For example, when you use the [[yii\jui\DatePicker]] widget
in a page, it will automatically include the required CSS and JavaScript files, instead of asking you to manually
find these files and include them. And when you upgrade the widget to a new version, it will automatically use
the new version of the asset files. In this tutorial, we will describe the powerful asset management capability
provided in Yii.


## Asset Bundles <span id="asset-bundles"></span>

Yii manages assets in the unit of *asset bundle*. An asset bundle is simply a collection of assets located
in a directory. When you register an asset bundle in a [view](structure-views.md), it will include the CSS and
JavaScript files in the bundle in the rendered Web page.


## Defining Asset Bundles <span id="defining-asset-bundles"></span>

Asset bundles are specified as PHP classes extending from [[yii\web\AssetBundle]]. The name of a bundle is simply
its corresponding fully qualified PHP class name (without the leading backslash). An asset bundle class should
be [autoloadable](concept-autoloading.md). It usually specifies where the assets are located, what CSS and 
JavaScript files the bundle contains, and how the bundle depends on other bundles.

The following code defines the main asset bundle used by [the basic project template](start-installation.md):

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
        ['css/print.css', 'media' => 'print'],
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

The above `AppAsset` class specifies that the asset files are located under the `@webroot` directory which
corresponds to the URL `@web`; the bundle contains a single CSS file `css/site.css` and no JavaScript file;
the bundle depends on two other bundles: [[yii\web\YiiAsset]] and [[yii\bootstrap\BootstrapAsset]]. More detailed
explanation about the properties of [[yii\web\AssetBundle]] can be found in the following:

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
* [[yii\web\AssetBundle::css|css]]: an array listing the CSS files contained in this bundle. Note that only forward slash "/"
  should be used as directory separators. Each file can be specified on its own as a string or in an array together with
  attribute tags and their values.
* [[yii\web\AssetBundle::js|js]]: an array listing the JavaScript files contained in this bundle. The format of this array
  is the same as that of [[yii\web\AssetBundle::css|css]]. Each JavaScript file can be specified in one of the following two
  formats:
  - a relative path representing a local JavaScript file (e.g. `js/main.js`). The actual path of the file
    can be determined by prepending [[yii\web\AssetManager::basePath]] to the relative path, and the actual URL
    of the file can be determined by prepending [[yii\web\AssetManager::baseUrl]] to the relative path.
  - an absolute URL representing an external JavaScript file. For example,
    `https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` or
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::depends|depends]]: an array listing the names of the asset bundles that this bundle depends on
  (to be explained shortly).
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerJsFile()]] method when it is called to register *every* JavaScript file in this bundle.
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: specifies the options that will be passed to the
  [[yii\web\View::registerCssFile()]] method when it is called to register *every* CSS file in this bundle.
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: specifies the options that will be passed to the
  [[yii\web\AssetManager::publish()]] method when it is called to publish source asset files to a Web directory.
  This is only used if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property.


### Asset Locations <span id="asset-locations"></span>

Assets, based on their location, can be classified as:

* source assets: the asset files are located together with PHP source code which cannot be directly accessed via Web.
  In order to use source assets in a page, they should be copied to a Web directory and turned into the so-called
  published assets. This process is called *asset publishing* which will be described in detail shortly.
* published assets: the asset files are located in a Web directory and can thus be directly accessed via Web.
* external assets: the asset files are located on a Web server that is different from the one hosting your Web
  application.

When defining an asset bundle class, if you specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property,
it means any assets listed using relative paths will be considered as source assets. If you do not specify this property,
it means those assets are published assets (you should therefore specify [[yii\web\AssetBundle::basePath|basePath]] and
[[yii\web\AssetBundle::baseUrl|baseUrl]] to let Yii know where they are located).

It is recommended that you place assets belonging to an application in a Web directory to avoid the unnecessary asset
publishing process. This is why `AppAsset` in the prior example specifies [[yii\web\AssetBundle::basePath|basePath]]
instead of [[yii\web\AssetBundle::sourcePath|sourcePath]].

For [extensions](structure-extensions.md), because their assets are located together with their source code
in directories that are not Web accessible, you have to specify the [[yii\web\AssetBundle::sourcePath|sourcePath]]
property when defining asset bundle classes for them.

> Note: Do not use `@webroot/assets` as the [[yii\web\AssetBundle::sourcePath|source path]].
  This directory is used by default by the [[yii\web\AssetManager|asset manager]] to save the asset files
  published from their source location. Any content in this directory is considered temporarily and may be subject
  to removal.


### Asset Dependencies <span id="asset-dependencies"></span>

When you include multiple CSS or JavaScript files in a Web page, they have to follow a certain order to avoid
overriding issues. For example, if you are using a jQuery UI widget in a Web page, you have to make sure
the jQuery JavaScript file is included before the jQuery UI JavaScript file. We call such ordering the dependencies
among assets.

Asset dependencies are mainly specified through the [[yii\web\AssetBundle::depends]] property.
In the `AppAsset` example, the asset bundle depends on two other asset bundles: [[yii\web\YiiAsset]] and
[[yii\bootstrap\BootstrapAsset]], which means the CSS and JavaScript files in `AppAsset` will be included *after*
those files in the two dependent bundles.

Asset dependencies are transitive. This means if bundle A depends on B which depends on C, A will depend on C, too.


### Asset Options <span id="asset-options"></span>

You can specify the [[yii\web\AssetBundle::cssOptions|cssOptions]] and [[yii\web\AssetBundle::jsOptions|jsOptions]]
properties to customize the way that CSS and JavaScript files are included in a page. The values of these properties
will be passed to the [[yii\web\View::registerCssFile()]] and [[yii\web\View::registerJsFile()]] methods, respectively, when
they are called by the [view](structure-views.md) to include CSS and JavaScript files.

> Note: The options you set in a bundle class apply to *every* CSS/JavaScript file in the bundle. If you want to
  use different options for different files, you should use the format mentioned [[yii\web\AssetBundle::css|above]] or create
  separate asset bundles, and use one set of options in each bundle.

For example, to conditionally include a CSS file for browsers that are IE9 or below, you can use the following option:

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

This will cause a CSS file in the bundle to be included using the following HTML tags:

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

To wrap the generated CSS link tags within `<noscript>`, you can configure `cssOptions` as follows,

```php
public $cssOptions = ['noscript' => true];
```

To include a JavaScript file in the head section of a page (by default, JavaScript files are included at the end
of the body section), use the following option:

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

By default, when an asset bundle is being published, all contents in the directory specified by [[yii\web\AssetBundle::sourcePath]]
will be published. You can customize this behavior by configuring the [[yii\web\AssetBundle::publishOptions|publishOptions]] 
property. For example, to publish only one or a few subdirectories of [[yii\web\AssetBundle::sourcePath]], 
you can do the following in the asset bundle class:

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
            'fonts/*',
            'css/*',
        ]
    ];
}  
```

The above example defines an asset bundle for the ["fontawesome" package](https://fontawesome.com/). By specifying 
the `only` publishing option, only the `fonts` and `css` subdirectories will be published.


### Bower and NPM Assets installation <span id="bower-npm-assets"></span>

Most JavaScript/CSS packages are managed by [Bower](https://bower.io/) and/or [NPM](https://www.npmjs.com/) package 
managers. In PHP world we have Composer, that manages PHP dependencies, but it is possible to load
both Bower and NPM packages using `composer.json` just as PHP packages.

To achieve this, we should configure our composer a bit. There are two options to do that:

___

#### Using asset-packagist repository

This way will satisfy requirements of the majority of projects, that need NPM or Bower packages.

> Note: Since 2.0.13 both Basic and Advanced application templates are pre-configured to use asset-packagist
 by default, so you can skip this section.

In the `composer.json` of your project, add the following lines:

```json
"repositories": [
    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    }
]
```

Adjust `@npm` and `@bower` [aliases](concept-aliases.md) in you [application configuration](concept-configurations.md):

```php
$config = [
    ...
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    ...
];
```

Visit [asset-packagist.org](https://asset-packagist.org) to know, how it works.

#### Using fxp/composer-asset-plugin

Compared to asset-packagist, composer-asset-plugin does not require any changes to application config. Instead, it
requires global installation of a special Composer plugin by running the following command:

```bash
composer global require "fxp/composer-asset-plugin:^1.4.1"
```

This command installs [composer asset plugin](https://github.com/fxpio/composer-asset-plugin/) globally
which allows managing Bower and NPM package dependencies through Composer. After the plugin installation, 
every single project on your computer will support Bower and NPM packages through `composer.json`. 

Add the following lines to `composer.json` of your project to adjust directories where the installed packages 
will be placed, if you want to publish them using Yii:

```json
"config": {
    "fxp-asset": {
        "installer-paths": {
            "npm-asset-library": "vendor/npm",
            "bower-asset-library": "vendor/bower"
        }
    }
}
```

> Note: `fxp/composer-asset-plugin` significantly slows down the `composer update` command in comparison
  to asset-packagist.
 
____
 
After configuring Composer to support Bower and NPM:

1. Modify the `composer.json` file of your application or extension and list the package in the `require` entry.
   You should use `bower-asset/PackageName` (for Bower packages) or `npm-asset/PackageName` (for NPM packages)
   to refer to the library.
2. Run `composer update`
3. Create an asset bundle class and list the JavaScript/CSS files that you plan to use in your application or extension.
   You should specify the [[yii\web\AssetBundle::sourcePath|sourcePath]] property as `@bower/PackageName` or `@npm/PackageName`.
   This is because Composer will install the Bower or NPM package in the directory corresponding to this alias.

> Note: Some packages may put all their distributed files in a subdirectory. If this is the case, you should specify
  the subdirectory as the value of [[yii\web\AssetBundle::sourcePath|sourcePath]]. For example, [[yii\web\JqueryAsset]]
  uses `@bower/jquery/dist` instead of `@bower/jquery`.


## Using Asset Bundles <span id="using-asset-bundles"></span>

To use an asset bundle, register it with a [view](structure-views.md) by calling the [[yii\web\AssetBundle::register()]]
method. For example, in a view template you can register an asset bundle like the following:

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this represents the view object
```

> Info: The [[yii\web\AssetBundle::register()]] method returns an asset bundle object containing the information
  about the published assets, such as [[yii\web\AssetBundle::basePath|basePath]] or [[yii\web\AssetBundle::baseUrl|baseUrl]].

If you are registering an asset bundle in other places, you should provide the needed view object. For example,
to register an asset bundle in a [widget](structure-widgets.md) class, you can get the view object by `$this->view`.

When an asset bundle is registered with a view, behind the scenes Yii will register all its dependent asset bundles.
And if an asset bundle is located in a directory inaccessible through the Web, it will be published to a Web directory.
Later, when the view renders a page, it will generate `<link>` and `<script>` tags for the CSS and JavaScript files
listed in the registered bundles. The order of these tags is determined by the dependencies among
the registered bundles and the order of the assets listed in the [[yii\web\AssetBundle::css]] and [[yii\web\AssetBundle::js]]
properties.


### Dynamic Asset Bundles <span id="dynamic-asset-bundles"></span>

Being a regular PHP class asset bundle can bear some extra logic related to it and may adjust its internal parameters dynamically.
For example: you may use some sophisticated JavaScript library, which provides some internationalization packed in separated
source files: each per each supported language. Thus you will need to add particular '.js' file to your page in order to
make library translation work. This can be achieved overriding [[yii\web\AssetBundle::init()]] method:

```php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class SophisticatedAssetBundle extends AssetBundle
{
    public $sourcePath = '/path/to/sophisticated/src';
    public $js = [
        'sophisticated.js' // file, which is always used
    ];

    public function init()
    {
        parent::init();
        $this->js[] = 'i18n/' . Yii::$app->language . '.js'; // dynamic file added
    }
}
```

Particular asset bundle can also be adjusted via its instance returned by [[yii\web\AssetBundle::register()]].
For example:

```php
use app\assets\SophisticatedAssetBundle;
use Yii;

$bundle = SophisticatedAssetBundle::register(Yii::$app->view);
$bundle->js[] = 'i18n/' . Yii::$app->language . '.js'; // dynamic file added
```

> Note: although dynamic adjustment of the asset bundles is supported, it is a **bad** practice, which may lead to
  unexpected side effects, and should be avoided if possible.


### Customizing Asset Bundles <span id="customizing-asset-bundles"></span>

Yii manages asset bundles through an application component named `assetManager` which is implemented by [[yii\web\AssetManager]].
By configuring the [[yii\web\AssetManager::bundles]] property, it is possible to customize the behavior of an asset bundle.
For example, the default [[yii\web\JqueryAsset]] asset bundle uses the `jquery.js` file from the installed
jquery Bower package. To improve the availability and performance, you may want to use a version hosted by Google.
This can be achieved by configuring `assetManager` in the application configuration like the following:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // do not publish the bundle
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

> Tip: You can conditionally choose which assets to use in an asset bundle. The following example shows how
> to use `jquery.js` in the development environment and `jquery.min.js` otherwise:
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
will be registered, and the view also will not include any of the assets in the bundle in the page it renders.
For example, to disable [[yii\web\JqueryAsset]], you can use the following configuration:

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

Keep in mind that customization made via [[yii\web\AssetManager::bundles]] is applied at the creation of the asset bundle, e.g.
at object constructor stage. Thus any adjustments made to the bundle object after that will override the mapping setup at [[yii\web\AssetManager::bundles]] level.
In particular: adjustments made inside [[yii\web\AssetBundle::init()]]
method or over the registered bundle object will take precedence over `AssetManager` configuration.
Here are the examples, where mapping set via [[yii\web\AssetManager::bundles]] makes no effect:

```php
// Program source code:

namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class LanguageAssetBundle extends AssetBundle
{
    // ...

    public function init()
    {
        parent::init();
        $this->baseUrl = '@web/i18n/' . Yii::$app->language; // can NOT be handled by `AssetManager`!
    }
}
// ...

$bundle = \app\assets\LargeFileAssetBundle::register(Yii::$app->view);
$bundle->baseUrl = YII_DEBUG ? '@web/large-files': '@web/large-files/minified'; // can NOT be handled by `AssetManager`!


// Application config :

return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'app\assets\LanguageAssetBundle' => [
                    'baseUrl' => 'https://some.cdn.com/files/i18n/en' // makes NO effect!
                ],
                'app\assets\LargeFileAssetBundle' => [
                    'baseUrl' => 'https://some.cdn.com/files/large-files' // makes NO effect!
                ],
            ],
        ],
    ],
];
```


### Asset Mapping <span id="asset-mapping"></span>

Sometimes you may want to "fix" incorrect/incompatible asset file paths used in multiple asset bundles. For example,
bundle A uses `jquery.min.js` version 1.11.1, and bundle B uses `jquery.js` version 2.1.1. While you can
fix the problem by customizing each bundle, an easier way is to use the *asset map* feature to map incorrect assets
to the desired ones. To do so, configure the [[yii\web\AssetManager::assetMap]] property like the following:

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
If any of the keys are found to be the last part of an asset file (which is prefixed with [[yii\web\AssetBundle::sourcePath]]
if available), the corresponding value will replace the asset and be registered with the view.
For example, the asset file `my/path/to/jquery.js` matches the key `jquery.js`.

> Note: Only assets specified using relative paths are subject to asset mapping. The target asset paths
  should be either absolute URLs or paths relative to [[yii\web\AssetManager::basePath]].


### Asset Publishing <span id="asset-publishing"></span>

As aforementioned, if an asset bundle is located in a directory that is not Web accessible, its assets will be copied
to a Web directory when the bundle is being registered with a view. This process is called *asset publishing*, and is done
automatically by the [[yii\web\AssetManager|asset manager]].

By default, assets are published to the directory `@webroot/assets` which corresponds to the URL `@web/assets`.
You may customize this location by configuring the [[yii\web\AssetManager::basePath|basePath]] and
[[yii\web\AssetManager::baseUrl|baseUrl]] properties.

Instead of publishing assets by file copying, you may consider using symbolic links, if your OS and Web server allow.
This feature can be enabled by setting [[yii\web\AssetManager::linkAssets|linkAssets]] to be `true`.

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


### Cache Busting <span id="cache-busting"></span>

For Web application running in production mode, it is a common practice to enable HTTP caching for assets and other
static resources. A drawback of this practice is that whenever you modify an asset and deploy it to production, a user
client may still use the old version due to the HTTP caching. To overcome this drawback, you may use the cache busting
feature, which was introduced in version 2.0.3, by configuring [[yii\web\AssetManager]] like the following:
  
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

By doing so, the URL of every published asset will be appended with its last modification timestamp. For example,
the URL to `yii.js` may look like `/assets/5515a87c/yii.js?v=1423448645"`, where the parameter `v` represents the
last modification timestamp of the `yii.js` file. Now if you modify an asset, its URL will be changed, too, which causes
the client to fetch the latest version of the asset.


## Commonly Used Asset Bundles <span id="common-asset-bundles"></span>

The core Yii code has defined many asset bundles. Among them, the following bundles are commonly used and may
be referenced in your application or extension code.

- [[yii\web\YiiAsset]]: It mainly includes the `yii.js` file which implements a mechanism of organizing JavaScript code
  in modules. It also provides special support for `data-method` and `data-confirm` attributes and other useful features.
  More information about `yii.js` can be found in the [Client Scripts Section](output-client-scripts.md#yii.js).
- [[yii\web\JqueryAsset]]: It includes the `jquery.js` file from the jQuery Bower package.
- [[yii\bootstrap\BootstrapAsset]]: It includes the CSS file from the Twitter Bootstrap framework.
- [[yii\bootstrap\BootstrapPluginAsset]]: It includes the JavaScript file from the Twitter Bootstrap framework for
  supporting Bootstrap JavaScript plugins.
- [[yii\jui\JuiAsset]]: It includes the CSS and JavaScript files from the jQuery UI library.

If your code depends on jQuery, jQuery UI or Bootstrap, you should use these predefined asset bundles rather than
creating your own versions. If the default setting of these bundles do not satisfy your needs, you may customize them 
as described in the [Customizing Asset Bundle](#customizing-asset-bundles) subsection. 


## Asset Conversion <span id="asset-conversion"></span>

Instead of directly writing CSS and/or JavaScript code, developers often write them in some extended syntax and
use special tools to convert it into CSS/JavaScript. For example, for CSS code you may use [LESS](https://lesscss.org/)
or [SCSS](https://sass-lang.com/); and for JavaScript you may use [TypeScript](https://www.typescriptlang.org/).

You can list the asset files in extended syntax in the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties of an asset bundle. For example,

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

Yii uses the file name extensions to identify which extended syntax an asset is in. By default it recognizes
the following syntax and file name extensions:

- [LESS](https://lesscss.org/): `.less`
- [SCSS](https://sass-lang.com/): `.scss`
- [Stylus](https://stylus-lang.com/): `.styl`
- [CoffeeScript](https://coffeescript.org/): `.coffee`
- [TypeScript](https://www.typescriptlang.org/): `.ts`

Yii relies on the installed pre-processor tools to convert assets. For example, to use [LESS](https://lesscss.org/)
you should install the `lessc` pre-processor command.

You can customize the pre-processor commands and the supported extended syntax by configuring
[[yii\web\AssetManager::converter]] like the following:

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

In the above, we specify the supported extended syntax via the [[yii\web\AssetConverter::commands]] property.
The array keys are the file extension names (without leading dot), and the array values are the resulting
asset file extension names and the commands for performing the asset conversion. The tokens `{from}` and `{to}`
in the commands will be replaced with the source asset file paths and the target asset file paths.

> Info: There are other ways of working with assets in extended syntax, besides the one described above.
  For example, you can use build tools such as [grunt](https://gruntjs.com/) to monitor and automatically
  convert assets in extended syntax. In this case, you should list the resulting CSS/JavaScript files in
  asset bundles rather than the original files.


## Combining and Compressing Assets <span id="combining-compressing-assets"></span>

A Web page can include many CSS and/or JavaScript files. To reduce the number of HTTP requests and the overall
download size of these files, a common practice is to combine and compress multiple CSS/JavaScript files into 
one or very few files, and then include these compressed files instead of the original ones in the Web pages.  
 
> Info: Combining and compressing assets are usually needed when an application is in production mode. 
  In development mode, using the original CSS/JavaScript files is often more convenient for debugging purposes.

In the following, we introduce an approach to combine and compress asset files without the need to modify
your existing application code.

1. Find all the asset bundles in your application that you plan to combine and compress.
2. Divide these bundles into one or a few groups. Note that each bundle can only belong to a single group.
3. Combine/compress the CSS files in each group into a single file. Do this similarly for the JavaScript files.
4. Define a new asset bundle for each group:
   * Set the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties to be
     the combined CSS and JavaScript files, respectively.
   * Customize the asset bundles in each group by setting their [[yii\web\AssetBundle::css|css]] and 
     [[yii\web\AssetBundle::js|js]] properties to be empty, and setting their [[yii\web\AssetBundle::depends|depends]]
     property to be the new asset bundle created for the group.

Using this approach, when you register an asset bundle in a view, it causes the automatic registration of
the new asset bundle for the group that the original bundle belongs to. And as a result, the combined/compressed 
asset files are included in the page, instead of the original ones.


### An Example <span id="example"></span>

Let's use an example to further explain the above approach. 

Assume your application has two pages, X and Y. Page X uses asset bundles A, B and C, while Page Y uses asset bundles B, C and D. 

You have two ways to divide these asset bundles. One is to use a single group to include all asset bundles, the
other is to put A in Group X, D in Group Y, and (B, C) in Group S. Which one is better? It depends. The first way
has the advantage that both pages share the same combined CSS and JavaScript files, which makes HTTP caching
more effective. On the other hand, because the single group contains all bundles, the size of the combined CSS and 
JavaScript files will be bigger and thus increase the initial file transmission time. For simplicity in this example, 
we will use the first way, i.e., use a single group to contain all bundles.

> Info: Dividing asset bundles into groups is not trivial task. It usually requires analysis about the real world
  traffic data of various assets on different pages. At the beginning, you may start with a single group for simplicity. 

Use existing tools (e.g. [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) to combine and compress CSS and JavaScript files in 
all the bundles. Note that the files should be combined in the order that satisfies the dependencies among the bundles. 
For example, if Bundle A depends on B which depends on both C and D, then you should list the asset files starting 
from C and D, followed by B and finally A. 

After combining and compressing, we get one CSS file and one JavaScript file. Assume they are named as 
`all-xyz.css` and `all-xyz.js`, where `xyz` stands for a timestamp or a hash that is used to make the file name unique
to avoid HTTP caching problems.
 
We are at the last step now. Configure the [[yii\web\AssetManager|asset manager]] as follows in the application
configuration:

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

There is one final trick to make the above approach work more smoothly. Instead of directly modifying the
application configuration file, you may put the bundle customization array in a separate file and conditionally
include this file in the application configuration. For example,

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'),  
        ],
    ],
];
```

That is, the asset bundle configuration array is saved in `assets-prod.php` for production mode, and
`assets-dev.php` for non-production mode.

> Note: this asset combining mechanism is based on the ability of [[yii\web\AssetManager::bundles]] to override the properties
  of the registered asset bundles. However, as it already has been said above, this ability does not cover asset bundle
  adjustments, which are performed at [[yii\web\AssetBundle::init()]] method or after bundle is registered. You should
  avoid usage of such dynamic bundles during the asset combining.


### Using the `asset` Command <span id="using-asset-command"></span>

Yii provides a console command named `asset` to automate the approach that we just described.

To use this command, you should first create a configuration file to describe what asset bundles should
be combined and how they should be grouped. You can use the `asset/template` sub-command to generate
a template first and then modify it to fit for your needs.

```
yii asset/template assets.php
```

The command generates a file named `assets.php` in the current directory. The content of this file looks like the following:

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
    // Whether to delete asset source after compression:
    'deleteSource' => false,
    // The list of asset bundles to compress:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
    ],
];
```

You should modify this file and specify which bundles you plan to combine in the `bundles` option. In the `targets` 
option you should specify how the bundles should be divided into groups. You can specify one or multiple groups, 
as aforementioned.

> Note: Because the alias `@webroot` and `@web` are not available in the console application, you should
  explicitly define them in the configuration.

JavaScript files are combined, compressed and written to `js/all-{hash}.js` where {hash} is replaced with the hash of
the resulting file.

The `jsCompressor` and `cssCompressor` options specify the console commands or PHP callbacks for performing
JavaScript and CSS combining/compressing. By default, Yii uses [Closure Compiler](https://developers.google.com/closure/compiler/) 
for combining JavaScript files and [YUI Compressor](https://github.com/yui/yuicompressor/) for combining CSS files. 
You should install those tools manually or adjust these options to use your favorite tools.


With the configuration file, you can run the `asset` command to combine and compress the asset files
and then generate a new asset bundle configuration file `assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

The generated configuration file can be included in the application configuration, like described in
the last subsection.

> Note: in case you customize asset bundles for your application via [[yii\web\AssetManager::bundles]] or
  [[yii\web\AssetManager::assetMap]] and want this customization to be applied for the compression source files,
  you should include these options to the `assetManager` section inside asset command configuration file.

> Note: while specifying the compression source, you should avoid the use of asset bundles whose parameters may be
  adjusted dynamically (e.g. at `init()` method or after registration), since they may work incorrectly after compression.


> Info: Using the `asset` command is not the only option to automate the asset combining and compressing process.
  You can use the excellent task runner tool [grunt](https://gruntjs.com/) to achieve the same goal.


### Grouping Asset Bundles <span id="grouping-asset-bundles"></span>

In the last subsection, we have explained how to combine all asset bundles into a single one in order to minimize
the HTTP requests for asset files referenced in an application. This is not always desirable in practice. For example,
imagine your application has a "front end" as well as a "back end", each of which uses a different set of JavaScript 
and CSS files. In this case, combining all asset bundles from both ends into a single one does not make sense, 
because the asset bundles for the "front end" are not used by the "back end" and it would be a waste of network
bandwidth to send the "back end" assets when a "front end" page is requested.
 
To solve the above problem, you can divide asset bundles into groups and combine asset bundles for each group.
The following configuration shows how you can group asset bundles: 

```php
return [
    ...
    // Specify output bundles with groups:
    'targets' => [
        'allShared' => [
            'js' => 'js/all-shared-{hash}.js',
            'css' => 'css/all-shared-{hash}.css',
            'depends' => [
                // Include all assets shared between 'backend' and 'frontend'
                'yii\web\YiiAsset',
                'app\assets\SharedAsset',
            ],
        ],
        'allBackEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                'app\assets\AdminAsset'
            ],
        ],
        'allFrontEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [], // Include all remaining assets
        ],
    ],
    ...
];
```

As you can see, the asset bundles are divided into three groups: `allShared`, `allBackEnd` and `allFrontEnd`.
They each depends on an appropriate set of asset bundles. For example, `allBackEnd` depends on `app\assets\AdminAsset`.
When running `asset` command with this configuration, it will combine asset bundles according to the above specification.

> Info: You may leave the `depends` configuration empty for one of the target bundle. By doing so, that particular
  asset bundle will depend on all of the remaining asset bundles that other target bundles do not depend on.
