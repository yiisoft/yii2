Theming
=======

<<<<<<< HEAD
> Note: This section is under development.

A theme is a directory of view and layout files. Each file of the theme overrides corresponding file of an application
when rendered. A single application may use multiple themes and each may provide totally different experiences. At any
time only one theme can be active.

> Note: Themes are usually not meant to be redistributed since views are too application specific. If you want to
  redistribute a customized look and feel, consider CSS and JavaScript files in the form of [asset bundles](structure-assets.md) instead.

Configuring a theme
-------------------

Theme configuration is specified via the `view` component of the application. In order to set up a theme to work with basic
application views, the following should be in your application config file:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/views' => '@app/themes/basic'],
            'baseUrl' => '@web/themes/basic',
        ],
    ],
],
```

In the above, `pathMap` defines a map of original paths to themed paths while `baseUrl` defines the base URL for
resources referenced by theme files.

In our case `pathMap` is `['@app/views' => '@app/themes/basic']`. That means that every view in `@app/views` will be
first searched under `@app/themes/basic` and if a view exists in the theme directory it will be used instead of the
original view.

For example, with a configuration above a themed version of a view file `@app/views/site/index.php` will be
`@app/themes/basic/site/index.php`. It basically replaces `@app/views` in `@app/views/site/index.php` with
`@app/themes/basic`.

In order to configure theme runtime you can use the following code before rendering a view. Typically it will be
placed in controller:

```php
$this->getView()->theme = Yii::createObject([
    'class' => '\yii\base\Theme',
    'pathMap' => ['@app/views' => '@app/themes/basic'],
    'baseUrl' => '@web/themes/basic',
]);
```

### Theming modules

In order to theme modules, `pathMap` may look like the following:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@app/views' => '@app/themes/basic',
                '@app/modules' => '@app/themes/basic/modules', // <-- !!!
            ],
        ],
    ],
],
```

It will allow you to theme `@app/modules/blog/views/comment/index.php` with `@app/themes/basic/modules/blog/views/comment/index.php`.

### Theming widgets

In order to theme a widget view located at `@app/widgets/currency/views/index.php`, you need the following configuration for
the view component theme:

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/widgets' => '@app/themes/basic/widgets'],
        ],
    ],
],
```

With the configuration above you can create a themed version of the `@app/widgets/currency/index.php` view in
`@app/themes/basic/widgets/currency/index.php`.

Using multiple paths
--------------------

It is possible to map a single path to multiple theme paths. For example,
=======
Theming is a way to replace a set of [views](structure-views.md) with another without the need of touching
the original view rendering code. You can use theming to systematically change the look and feel of an application.

To use theming, you should configure the [[yii\base\View::theme|theme]] property of the `view` application component.
The property configures a [[yii\base\Theme]] object which governs how view files are being replaced. You should
mainly specify the following properties of [[yii\base\Theme]]:

- [[yii\base\Theme::basePath]]: specifies the base directory that contains the themed resources (CSS, JS, images, etc.)
- [[yii\base\Theme::baseUrl]]: specifies the base URL of the themed resources.
- [[yii\base\Theme::pathMap]]: specifies the replacement rules of view files. More details will be given in the following
  subsections.
 
For example, if you call `$this->render('about')` in `SiteController`, you will be rendering the view file
`@app/views/site/about.php`. However, if you enable theming in the following application configuration,
the view file `@app/themes/basic/site/about.php` will be rendered, instead. 

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic'
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```

> Info: Path aliases are supported by themes. When doing view replacement, path aliases will be turned into 
  the actual file paths or URLs.

You can access the [[yii\base\Theme]] object through the [[yii\base\View::theme]] property. For example,
in a view file, you can write the following code because `$this` refers to the view object:

```php
$theme = $this->theme;

// returns: $theme->baseUrl . '/img/logo.gif'
$url = $theme->getUrl('img/logo.gif');

// returns: $theme->basePath . '/img/logo.gif'
$file = $theme->getPath('img/logo.gif');
```

The [[yii\base\Theme::pathMap]] property governs how view files should be replaced. It takes an array of 
key-value pairs, where the keys are the original view paths to be replaced and the values are the corresponding 
themed view paths. The replacement is based on partial match: if a view path starts with any key in 
the [[yii\base\Theme::pathMap|pathMap]] array, that matching part will be replaced with the corresponding array value.
Using the above configuration example, because `@app/views/site/about.php` partially matches the key
`@app/views`, it will be replaced as `@app/themes/basic/site/about.php`.


## Theming Modules <span id="theming-modules"></span>

In order to theme modules, [[yii\base\Theme::pathMap]] can be configured like the following:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
],
```

It will allow you to theme `@app/modules/blog/views/comment/index.php` into `@app/themes/basic/modules/blog/views/comment/index.php`.


## Theming Widgets <span id="theming-widgets"></span>

In order to theme widgets, you can configure [[yii\base\Theme::pathMap]] in the following way:

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```

This will allow you to theme `@app/widgets/currency/views/index.php` into `@app/themes/basic/widgets/currency/index.php`.


## Theme Inheritance <span id="theme-inheritance"></span>

Sometimes you may want to define a basic theme which contains a basic look and feel of the application, and then
based on the current holiday, you may want to vary the look and feel slightly. You can achieve this goal using
theme inheritance which is done by mapping a single view path to multiple targets. For example,
>>>>>>> yiichina/master

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

<<<<<<< HEAD
In this case, first the view will be searched for in `@app/themes/christmas/site/index.php` then if it's not found it will check
`@app/themes/basic/site/index.php`. If there's no view there as well, then the application view will be used.

This ability is especially useful if you want to temporarily or conditionally override some views.
=======
In this case, the view `@app/views/site/index.php` would be themed as either `@app/themes/christmas/site/index.php` 
or `@app/themes/basic/site/index.php`, depending on which themed file exists. If both themed files exist, the first
one will take precedence. In practice, you would keep most themed view files in `@app/themes/basic` and customize
some of them in `@app/themes/christmas`.
>>>>>>> yiichina/master
