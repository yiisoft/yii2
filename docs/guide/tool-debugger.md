Debug toolbar and debugger
==========================

> Note: This section is under development.

Yii2 includes a handy toolbar, and built-in debugger, for faster development and debugging of your applications. The toolbar displays information
about the currently opened page, while the debugger can be used to analyze data you've previously collected (i.e., to confirm the values of variables).

Out of the box these tools allow you to:

- Quickly get the framework version, PHP version, response status, current controller and action, performance info and
  more via toolbar
- Browse the application and PHP configuration
- View the request data, request and response headers, session data, and environment variables
- See, search, and filter the logs
- View any profiling results
- View the database queries executed by the page
- View the emails sent by the application

All of this information will be available per request, allowing you to revisit the information for past requests as well.


Installing and configuring
--------------------------

To enable these features, add these lines to your configuration file to enable the debug module:

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => 'yii\debug\Module',
]
```

By default, the debug module only works when browsing the website from localhost. If you want to use it on a remote (staging)
server, add the parameter `allowedIPs` to the configuration to whitelist your IP:

```php
'bootstrap' => ['debug'],
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

> Note: the debugger stores information about each request in the `@runtime/debug` directory. If you have problems using
> The debugger such as weird error messages when using it or the toolbar not showing up or not showing any requests, check
> whether the web server has enough permissions to access this directory and the files located inside.


### Extra configuration for logging and profiling

Logging and profiling are simple but powerful tools that may help you to understand the execution flow of both the
framework and the application. These tools are useful for development and production environments alike.

While in a production environment, you should log only significantly important  messages manually, as described in
[logging guide section](logging.md). It hurts performance too much to continue to log all messages in production.

In a development environment, the more logging the better, and it's especially useful to record the execution trace.

In order to see the trace messages that will help you to understand what happens under the hood of the framework, you need to set the
trace level in the configuration file:

```php
return [
    // ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0, // <-- here
```

By default, the trace level is automatically set to `3` if Yii is running in debug mode, as determined by the presence of the following line in your `index.php` file:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> Note: Make sure to disable debug mode in production environments since it may have a significant and adverse performance effect. Further, the debug mode may expose sensitive information to end users.


Creating your own panels
------------------------

Both the toolbar and debugger are highly configurable and customizable. To do so, you can create your own panels that collect
and display the specific data you want. Below we'll describe the process of creating a simple custom panel that:

- Collects the views rendered during a request
- Shows the number of views rendered in the toolbar
- Allows you to check the view names in the debugger

The assumption is that you're using the basic application template.

First we need to implement the `Panel` class in `panels/ViewsPanel.php`:

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

The workflow for the code above is:

1. `init` is executed before any controller action is run. This method is the best place to attach handlers that will collect data during the controller action's execution.
2. `save` is called after controller action is executed. The data returned by this method will be stored in a data file. If nothing is returned by this method, the panel
   won't be rendered.
3. The data from the data file is loaded into `$this->data`. For the toolbar, this will always represent the latest data, For the debugger, this property may be set to be read from any previous data file as well.
4. The toolbar takes its contents from `getSummary`. There, we're showing the number of view files rendered. The debugger uses
   `getDetail` for the same purpose.

Now it's time to tell the debugger to use the new panel. In `config/web.php`, the debug configuration is modified to:

```php
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
            'views' => ['class' => 'app\panels\ViewsPanel'],
        ],
    ];

// ...
```

That's it. Now we have another useful panel without writing much code.
