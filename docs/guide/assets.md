Managing assets
===============

An asset in Yii is a file that is included into the page. It could be CSS, JavaScript or
any other file. Framework provides many ways to work with assets from basics such as adding `<script src="` tag
for a file that is [handled by View](view.md) section to advanced usage such as pusblishing files that are not
under webserve document root, resolving JavaScript dependencies or minifying CSS.

Declaring asset bundle
----------------------

In order to publish some assets you should declare an asset bundle first. The bundle defines a set of asset files or
directories to be published and their dependencies on other asset bundles.

Both basic and advanced application templates contain `AppAsset` asset bundle class that defines assets required
applicationwide. Let's review basic application asset bundle class:

```php
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
`$css` and `$js` paths i.e. `@webroot/css/site.css` for `css/site.css`. Here `@webroot` is an alias that points to
application's `web` directory.

`$baseUrl` is used to specify base URL for the same relative `$css` and `$js` i.e. `@web/css/site.css` where `@web`
is an alias that corresponds to your website base URL such as `http://example.com/`.

In case you have asset files under non web accessible directory, that is the case for any extension, you need
to additionally specify `$sourcePath`. Files will be copied or symlinked from source bath to base path prior to being
registered. In case source path is used `baseUrl` is generated automatically at the time of publising asset bundle.

Dependencies on other asset bundles are specified via `$depends` property. It is an array that contains fully qualified
names of bundle classes that should be published in order for this bundle to work properly.

Here `yii\web\YiiAsset` adds Yii's JavaScript library while `yii\bootstrap\BootstrapAsset` includes
[Bootstrap](http://getbootstrap.com) frontend framework.

Asset bundles are regular classes so if you need to define another one, just create alike class with unique name. This
class can be placed anywhere but the convention for it is to be under `assets` directory of the applicaiton.

Registering asset bundle
------------------------

Asset bundle classes are typically registered in views or, if it's main application asset, in layout. Doing it is
as simple as:

```php
use app\assets\AppAsset;
AppAsset::register($this);
```

Since we're in a view context `$this` refers to `View` class.

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

In the above we're adding asset bundle definitions to `bunldes` property of asset manager. Keys there are fully
qualified class paths to asset bundle classes we want to override while values are key-value arrays of class properties
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