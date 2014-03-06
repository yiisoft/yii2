Debug toolbar and debugger
==========================

Yii2 includes a handy toolbar to aid faster development and debugging as well as debugger. Toolbar displays information
about currently opened page while using debugger you can analyze data collected before.

Out of the box it allows you to:

- Quickly getting framework version, PHP version, response status, current controller and action, performance info and
  more via toolbar.
- Browsing application and PHP configuration.
- Browsing request data, request and response headers, session data and environment.
- Viewing, searching, filtering logs.
- View profiling results.
- View database queries.
- View emails sent.

All these are available per request so you can browse past requests as well.

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

### Extra config for logging and profiling

Logging and profiling are simple but very powerful tools that may help you to understand execution flow of both the
framework and the application. These are useful both for development and production environments.

While in production environment you should log only important enough messages manually as described in
[logging guide section](logging.md), in development environment it's especially useful to get execution trace.

In order to get trace messages that help you to understand what happens under the hood of the framework, you need to set
trace level in the config:

```php
return [
	// ...
	'components' => [
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0, // <-- here
```

By default it's automatically set to `3` if Yii is run in debug mode i.e. your `index.php` file contains the following:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> Note: Make sure to disable debug mode on production since it may have significan performance effect and expose sensible
information to end users.

Creating your own panels
------------------------

Both toolbar and debugger are highly configurable and customizable. You can create your own panels that could collect
and display extra data. Below we'll describe a process of creation of a simple custom panel that collects views rendered
during request, shows a number in the toolbar and allows you checking view names in debugger. Below we're assuming
basic application template.

First we need to implement panel class in `panels/ViewsPanel.php`:

```php
<?php
namespace app\panels;

use yii\base\Event;
use yii\base\View;
use yii\base\ViewEvent;
use yii\debug\Panel;


class ViewsPanel extends Panel
{
	private $_viewFiles = [];

	public function init()
	{
		parent::init();
		Event::on(View::className(), View::EVENT_BEFORE_RENDER, function (ViewEvent $event) {
			$this->_viewFiles[] = $event->sender->getViewFile();
		});
	}


	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return 'Views';
	}

	/**
	 * @inheritdoc
	 */
	public function getSummary()
	{
		$url = $this->getUrl();
		$count = count($this->data);
		return "<div class=\"yii-debug-toolbar-block\"><a href=\"$url\">Views <span class=\"label\">$count</span></a></div>";
	}

	/**
	 * @inheritdoc
	 */
	public function getDetail()
	{
		return '<ol><li>' . implode('<li>', $this->data) . '</ol>';
	}

	/**
	 * @inheritdoc
	 */
	public function save()
	{
		return $this->_viewFiles;
	}
}
```

The workflow for the code above is the following:

1. `init` is executed before running any controller action. Best place to attach handlers that will collect data.
2. `save` is called after controller action is executed. Data returned is stored in data file. If nothing returned panel
   won't render.
3. Data from data file is loaded into `$this->data`. For toolbar it's always latest data, for debugger it may be selected
   to be read from any previous data file.
4. Toolbar takes its contents from `getSummary`. There we're showing a number of view files rendered. Debugger uses
   `getDetail` for the same purpose.

Now it's time to tell debugger to use our new panel. In `config/web.php` debug configuration is modified to be the
following:

```php
if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['preload'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
		'panels' => [
			'views' => ['class' => 'app\panels\ViewsPanel'],
		],
	];

// ...
```

That's it. Now we have another useful panel without writing much code.
