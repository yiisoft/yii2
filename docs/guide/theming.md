Theming
=======

TBD

Configuring current theme
-------------------------

Theme configuration is specified via `view` component of the application. So in order to set it up the following should
be in your application config file:

```php
'components' => [
	'view' => [
		'theme' => [
			'pathMap' => ['@app/views' => '@webroot/themes/basic'],
			'baseUrl' => '@web/themes/basic',
		],
	],
],
```