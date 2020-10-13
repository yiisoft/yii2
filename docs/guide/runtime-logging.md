Logging
=======

Yii provides a powerful logging framework that is highly customizable and extensible. Using this framework, you
can easily log various types of messages, filter them, and gather them at different targets, such as files, databases,
emails. 

Using the Yii logging framework involves the following steps:
 
* Record [log messages](#log-messages) at various places in your code;
* Configure [log targets](#log-targets) in the application configuration to filter and export log messages;
* Examine the filtered logged messages exported by different targets (e.g. the [Yii debugger](tool-debugger.md)).

In this section, we will mainly describe the first two steps.


## Log Messages <span id="log-messages"></span>

Recording log messages is as simple as calling one of the following logging methods:

* [[Yii::debug()]]: record a message to trace how a piece of code runs. This is mainly for development use.
* [[Yii::info()]]: record a message that conveys some useful information.
* [[Yii::warning()]]: record a warning message that indicates something unexpected has happened.
* [[Yii::error()]]: record a fatal error that should be investigated as soon as possible.

These logging methods record log messages at various *severity levels* and *categories*. They share
the same function signature `function ($message, $category = 'application')`, where `$message` stands for
the log message to be recorded, while `$category` is the category of the log message. The code in the following
example records a trace message under the default category `application`:

```php
Yii::debug('start calculating average revenue');
```

> Info: Log messages can be strings as well as complex data, such as arrays or objects. It is the responsibility
of [log targets](#log-targets) to properly deal with log messages. By default, if a log message is not a string,
it will be exported as a string by calling [[yii\helpers\VarDumper::export()]].

To better organize and filter log messages, it is recommended that you specify an appropriate category for each
log message. You may choose a hierarchical naming scheme for categories, which will make it easier for 
[log targets](#log-targets) to filter messages based on their categories. A simple yet effective naming scheme
is to use the PHP magic constant `__METHOD__` for the category names. This is also the approach used in the core 
Yii framework code. For example,

```php
Yii::debug('start calculating average revenue', __METHOD__);
```

The `__METHOD__` constant evaluates as the name of the method (prefixed with the fully qualified class name) where 
the constant appears. For example, it is equal to the string `'app\controllers\RevenueController::calculate'` if 
the above line of code is called within this method.

> Info: The logging methods described above are actually shortcuts to the [[yii\log\Logger::log()|log()]] method 
of the [[yii\log\Logger|logger object]] which is a singleton accessible through the expression `Yii::getLogger()`. When
enough messages are logged or when the application ends, the logger object will call a 
[[yii\log\Dispatcher|message dispatcher]] to send recorded log messages to the registered [log targets](#log-targets).


## Log Targets <span id="log-targets"></span>

A log target is an instance of the [[yii\log\Target]] class or its child class. It filters the log messages by their
severity levels and categories and then exports them to some medium. For example, a [[yii\log\DbTarget|database target]]
exports the filtered log messages to a database table, while an [[yii\log\EmailTarget|email target]] exports
the log messages to specified email addresses.

You can register multiple log targets in an application by configuring them through the `log` [application component](structure-application-components.md)
in the application configuration, like the following:

```php
return [
    // the "log" component must be loaded during bootstrapping time
    'bootstrap' => ['log'],
    // the "log" component process messages with timestamp. Set PHP timezone to create correct timestamp
    'timeZone' => 'America/Los_Angeles',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error'],
                    'categories' => ['yii\db\*'],
                    'message' => [
                       'from' => ['log@example.com'],
                       'to' => ['admin@example.com', 'developer@example.com'],
                       'subject' => 'Database errors at example.com',
                    ],
                ],
            ],
        ],
    ],
];
```

> Note: The `log` component must be loaded during [bootstrapping](runtime-bootstrapping.md) time so that
it can dispatch log messages to targets promptly. That is why it is listed in the `bootstrap` array as shown above.

In the above code, two log targets are registered in the [[yii\log\Dispatcher::targets]] property: 

* the first target selects error and warning messages and saves them in a database table;
* the second target selects error messages under the categories whose names start with `yii\db\`, and sends
  them in an email to both `admin@example.com` and `developer@example.com`.

Yii comes with the following built-in log targets. Please refer to the API documentation about these classes to 
learn how to configure and use them. 

* [[yii\log\DbTarget]]: stores log messages in a database table.
* [[yii\log\EmailTarget]]: sends log messages to pre-specified email addresses.
* [[yii\log\FileTarget]]: saves log messages in files.
* [[yii\log\SyslogTarget]]: saves log messages to syslog by calling the PHP function `syslog()`.

In the following, we will describe the features common to all log targets.

  
### Message Filtering <span id="message-filtering"></span>

For each log target, you can configure its [[yii\log\Target::levels|levels]] and 
[[yii\log\Target::categories|categories]] properties to specify which severity levels and categories of the messages the target should process.

The [[yii\log\Target::levels|levels]] property takes an array consisting of one or several of the following values:

* `error`: corresponding to messages logged by [[Yii::error()]].
* `warning`: corresponding to messages logged by [[Yii::warning()]].
* `info`: corresponding to messages logged by [[Yii::info()]].
* `trace`: corresponding to messages logged by [[Yii::debug()]].
* `profile`: corresponding to messages logged by [[Yii::beginProfile()]] and [[Yii::endProfile()]], which will
be explained in more details in the [Profiling](#performance-profiling) subsection.

If you do not specify the [[yii\log\Target::levels|levels]] property, it means the target will process messages
of *any* severity level.

The [[yii\log\Target::categories|categories]] property takes an array consisting of message category names or patterns.
A target will only process messages whose category can be found or match one of the patterns in this array.
A category pattern is a category name prefix with an asterisk `*` at its end. A category name matches a category pattern
if it starts with the same prefix of the pattern. For example, `yii\db\Command::execute` and `yii\db\Command::query`
are used as category names for the log messages recorded in the [[yii\db\Command]] class. They both match
the pattern `yii\db\*`.

If you do not specify the [[yii\log\Target::categories|categories]] property, it means the target will process
messages of *any* category.

In addition to specifying allowed categories using the [[yii\log\Target::categories|categories]] property, you may also
exclude certain categories by the [[yii\log\Target::except|except]] property. If the category of a message
is found or matches one of the patterns in this property, it will NOT be processed by the target.
 
The following target configuration specifies that the target should only process error and warning messages
under the categories whose names match either `yii\db\*` or `yii\web\HttpException:*`, but not `yii\web\HttpException:404`.

```php
[
    'class' => 'yii\log\FileTarget',
    'levels' => ['error', 'warning'],
    'categories' => [
        'yii\db\*',
        'yii\web\HttpException:*',
    ],
    'except' => [
        'yii\web\HttpException:404',
    ],
]
```

> Info: When an HTTP exception is caught by the [error handler](runtime-handling-errors.md), an error message
  will be logged with the category name in the format of `yii\web\HttpException:ErrorCode`. For example,
  the [[yii\web\NotFoundHttpException]] will cause an error message of category `yii\web\HttpException:404`.


### Message Formatting <span id="message-formatting"></span>

Log targets export the filtered log messages in a certain format. For example, if you install
a log target of the class [[yii\log\FileTarget]], you may find a log message similar to the following in the
`runtime/log/app.log` file:

```
2014-10-04 18:10:15 [::1][][-][trace][yii\base\Module::getModule] Loading module: debug
```

By default, log messages will be formatted as follows by the [[yii\log\Target::formatMessage()]]:

```
Timestamp [IP address][User ID][Session ID][Severity Level][Category] Message Text
```

You may customize this format by configuring the [[yii\log\Target::prefix]] property which takes a PHP callable
returning a customized message prefix. For example, the following code configures a log target to prefix each
log message with the current user ID (IP address and Session ID are removed for privacy reasons).

```php
[
    'class' => 'yii\log\FileTarget',
    'prefix' => function ($message) {
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        $userID = $user ? $user->getId(false) : '-';
        return "[$userID]";
    }
]
```

Besides message prefixes, log targets also append some context information to each batch of log messages.
By default, the values of these global PHP variables are included: `$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`,
`$_SESSION` and `$_SERVER`. You may adjust this behavior by configuring the [[yii\log\Target::logVars]] property
with the names of the global variables that you want to include by the log target. For example, the following
log target configuration specifies that only the value of the `$_SERVER` variable will be appended to the log messages.

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
]
```

You may configure `logVars` to be an empty array to totally disable the inclusion of context information.
Or if you want to implement your own way of providing context information, you may override the
[[yii\log\Target::getContextMessage()]] method.

In case some of your request fields contain sensitive information you would not like to log (e.g. passwords, access tokens),
you may additionally configure `maskVars` property. By default, the following request parameters will be masked with `***`:
`$_SERVER[HTTP_AUTHORIZATION]`, `$_SERVER[PHP_AUTH_USER]`, `$_SERVER[PHP_AUTH_PW]`, but you can set your own:

```php
[
    'class' => 'yii\log\FileTarget',
    'logVars' => ['_SERVER'],
    'maskVars' => ['_SERVER.HTTP_X_PASSWORD']
]
```

### Message Trace Level <span id="trace-level"></span>

During development, it is often desirable to see where each log message is coming from. This can be achieved by
configuring the [[yii\log\Dispatcher::traceLevel|traceLevel]] property of the `log` component like the following:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [...],
        ],
    ],
];
```

The above application configuration sets [[yii\log\Dispatcher::traceLevel|traceLevel]] to be 3 if `YII_DEBUG` is on
and 0 if `YII_DEBUG` is off. This means, if `YII_DEBUG` is on, each log message will be appended with at most 3
levels of the call stack at which the log message is recorded; and if `YII_DEBUG` is off, no call stack information
will be included.

> Info: Getting call stack information is not trivial. Therefore, you should only use this feature during development
or when debugging an application.


### Message Flushing and Exporting <span id="flushing-exporting"></span>

As aforementioned, log messages are maintained in an array by the [[yii\log\Logger|logger object]]. To limit the
memory consumption by this array, the logger will flush the recorded messages to the [log targets](#log-targets)
each time the array accumulates a certain number of log messages. You can customize this number by configuring
the [[yii\log\Dispatcher::flushInterval|flushInterval]] property of the `log` component:


```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 100,   // default is 1000
            'targets' => [...],
        ],
    ],
];
```

> Info: Message flushing also occurs when the application ends, which ensures log targets can receive complete log messages.

When the [[yii\log\Logger|logger object]] flushes log messages to [log targets](#log-targets), they do not get exported
immediately. Instead, the message exporting only occurs when a log target accumulates certain number of the filtered
messages. You can customize this number by configuring the [[yii\log\Target::exportInterval|exportInterval]]
property of individual [log targets](#log-targets), like the following,

```php
[
    'class' => 'yii\log\FileTarget',
    'exportInterval' => 100,  // default is 1000
]
```

Because of the flushing and exporting level setting, by default when you call `Yii::debug()` or any other logging
method, you will NOT see the log message immediately in the log targets. This could be a problem for some long-running
console applications. To make each log message appear immediately in the log targets, you should set both
[[yii\log\Dispatcher::flushInterval|flushInterval]] and [[yii\log\Target::exportInterval|exportInterval]] to be 1,
as shown below:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                ],
            ],
        ],
    ],
];
```

> Note: Frequent message flushing and exporting will degrade the performance of your application.


### Toggling Log Targets <span id="toggling-log-targets"></span>

You can enable or disable a log target by configuring its [[yii\log\Target::enabled|enabled]] property.
You may do so via the log target configuration or by the following PHP statement in your code:

```php
Yii::$app->log->targets['file']->enabled = false;
```

The above code requires you to name a target as `file`, as shown below by using string keys in the
`targets` array:

```php
return [
    'bootstrap' => ['log'],
    'components' => [
        'log' => [
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                ],
                'db' => [
                    'class' => 'yii\log\DbTarget',
                ],
            ],
        ],
    ],
];
```

Since version 2.0.13, you may configure [[yii\log\Target::enabled|enabled]] with a callable to
define a dynamic condition for whether the log target should be enabled or not.
See the documentation of [[yii\log\Target::setEnabled()]] for an example.

### Creating New Targets <span id="new-targets"></span>

Creating a new log target class is very simple. You mainly need to implement the [[yii\log\Target::export()]] method
sending the content of the [[yii\log\Target::messages]] array to a designated medium. You may call the
[[yii\log\Target::formatMessage()]] method to format each message. For more details, you may refer to any of the
log target classes included in the Yii release.

> Tip: Instead of creating your own loggers you may try any PSR-3 compatible logger such
  as [Monolog](https://github.com/Seldaek/monolog) by using
  [PSR log target extension](https://github.com/samdark/yii2-psr-log-target).

## Performance Profiling <span id="performance-profiling"></span>

Performance profiling is a special type of message logging that is used to measure the time taken by certain
code blocks and find out what are the performance bottlenecks. For example, the [[yii\db\Command]] class uses
performance profiling to find out the time taken by each DB query.

To use performance profiling, first identify the code blocks that need to be profiled. Then enclose each
code block like the following:

```php
\Yii::beginProfile('myBenchmark');

...code block being profiled...

\Yii::endProfile('myBenchmark');
```

where `myBenchmark` stands for a unique token identifying a code block. Later when you examine the profiling
result, you will use this token to locate the time spent by the corresponding code block.

It is important to make sure that the pairs of `beginProfile` and `endProfile` are properly nested.
For example,

```php
\Yii::beginProfile('block1');

    // some code to be profiled

    \Yii::beginProfile('block2');
        // some other code to be profiled
    \Yii::endProfile('block2');

\Yii::endProfile('block1');
```

If you miss `\Yii::endProfile('block1')` or switch the order of `\Yii::endProfile('block1')` and
`\Yii::endProfile('block2')`, the performance profiling will not work.

For each code block being profiled, a log message with the severity level `profile` is recorded. You can configure
a [log target](#log-targets) to collect such messages and export them. The [Yii debugger](tool-debugger.md) has
a built-in performance profiling panel showing the profiling results.
