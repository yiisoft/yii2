Debug toolbar and debugger
==========================

Yii2 includes a handy toolbar to aid faster development and debugging as well as debugger. Toolbar displays information
about currently opened page while using debugger you can analyze data collected before.

Installing and configuring
--------------------------

Add these lines to your config file:

```php
'preload' => ['debug'],
'modules' => [
	'debug' => ['yii\debug\Module']
]
```

> Note: by default the debug module only works when browsing the website from the localhost. If you want to use it
> on a remote (staging) server, add the parameter allowedIPs to the config to whitelist your IP, e.g. :**

```php
'preload' => ['debug'],
'modules' => [
	'debug' => [
		'class' => 'yii\debug\Module',
		'allowedIPs' => ['1.2.3.4', '127.0.0.1', '::1']
	]
]
```

If you are using `enableStrictParsing` URL manager option, add the following to your `rules`:

```php
'urlManager' => [
	'enableStrictParsing' => true,
	'rules' => [
		// ...
		'debug/<controller>/<action>' => 'debug/<controller>/<action>',
	],
],
```

How to use it
-------------


Creating your own panels
------------------------

