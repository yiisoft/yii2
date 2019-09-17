主题
=======

主题是一种将当前的一套视图 [views](structure-views.md) 替换为另一套视图，而无需更改视图渲染代码的方法。
你可以使用主题来系统地更改应用的外观和体验。

要使用主题，你得配置 `view` 应用组件的 [[yii\base\View::theme|theme]] 属性。
这个属性配置了一个 [[yii\base\Theme]] 对象，这个对象用来控制视图文件怎样被替换。
你主要应该指明下面的 [[yii\base\Theme]] 属性：

- [[yii\base\Theme::basePath]]：指定包含主题资源（CSS, JS, images, 等等）的基准目录。
- [[yii\base\Theme::baseUrl]]：指定主题资源的基准URL。
- [[yii\base\Theme::pathMap]]：指定视图文件的替换规则。
  更多细节将在下面介绍。

例如，如果你在 `SiteController` 里面调用 `$this->render('about')`，那你将渲染
视图文件 `@app/views/site/about.php` 。然而，如果你在下面的应用配置中开启了主
题功能，那么 `@app/themes/basic/site/about.php` 文件将会被渲染。

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```

> Info: 主题支持路径别名。当我们在做视图替换的时候，
  路径别名将被转换成实际的文件路径或者URL。

你可以通过 [[yii\base\View::theme]] 属性访问 [[yii\base\Theme]] 对象。例如，在一个视图文件里，你可以写下面的代码，
因为 `$this` 指向视图对象：

```php
$theme = $this->theme;

// returns: $theme->baseUrl . '/img/logo.gif'
$url = $theme->getUrl('img/logo.gif');

// returns: $theme->basePath . '/img/logo.gif'
$file = $theme->getPath('img/logo.gif');
```

[[yii\base\Theme::pathMap]] 属性控制如何替换视图文件。它是一个键值对数组，其中，
键是原本的视图路径，而值是相应的主题视图路径。
替换是基于部分匹配的：如果视图路径以 [[yii\base\Theme::pathMap|pathMap]] 数组的
任何一个键为起始，那么匹配部分将被相应的值所替换。
使用上面配置的例子，因为 `@app/views/site/about.php` 中的起始部分与键 `@app/views` 匹配，
它将被替换成 `@app/themes/basic/site/about.php`。


## 主题化模块 <span id="theming-modules"></span>

要主题化模块，[[yii\base\Theme::pathMap]] 可以配置成下面这样：

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
],
```

它允许你将 `@app/modules/blog/views/comment/index.php` 主题化成 `@app/themes/basic/modules/blog/views/comment/index.php`。


## 主题化小部件 <span id="theming-widgets"></span>

要主题化小部件，你可以像下面这样配置 [[yii\base\Theme::pathMap]]：

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```

这将允许你将 `@app/widgets/currency/views/index.php` 主题化成 `@app/themes/basic/widgets/currency/index.php`。


## 主题继承 <span id="theme-inheritance"></span>

有的时候，你可能想要定义一个基本的主题，其中包含一个基本的应用外观和体验，然后根据当前的节日，你可能想要稍微地改变一下外观和体验。
这个时候，你就可以使用主题继承实现这一目标，主题继承是通过一个单视图路径去映射多个目标，
例如，

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

在这种情况下，视图 `@app/views/site/index.php` 将被主题化成
`@app/themes/christmas/site/index.php` 或者 `@app/themes/basic/site/index.php`，
这取决于哪个主题文件存在。假如都存在，那么第一个将被优先使用。在现实情况中，
你会将大部分的主题文件放在 `@app/themes/basic` 里，而一些自定义的放在 `@app/themes/christmas`里。
