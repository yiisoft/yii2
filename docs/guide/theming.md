Theming
=======

A theme is a directory of view and layout files. Each file of the theme overrides corresponding file of an application
when rendered. A single application may use multiple themes and each may provide totally different experience. At any
time only one theme can be active.

> Note: Themes usually do not meant to be redistributed since views are too application specific. If you want to
  redistribute customized look and feel consider CSS and JavaScript files in form of [asset bundles](assets.md) instead.

Configuring current theme
-------------------------

Theme configuration is specified via `view` component of the application. So in order to set it up the following should
be in your application config file:

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

In the above `pathMap` defines where to look for view files while `baseUrl` defines base URL for resources referenced
from these files. In our case `pathMap` is `['@app/views' => '@app/themes/basic']` so the themed version
for a view file `@app/views/site/index.php` will be `@app/themes/basic/site/index.php`.

Using multiple paths
--------------------

It is possible to map a single path to multiple paths. For example,

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

In this case, the view will be searched in `@app/themes/christmas/site/index.php` then if it's not found it will check
`@app/themes/basic/site/index.php`. If there's no view there as well application view will be used.

This ability is especially useful if you want to temporary or conditionally override some views.
