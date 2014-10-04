Logging
=======

Yii provides a powerful logging framework that is highly customizable and extensible. Using this framework, you
can easily log various types of messages, filter them, and gather them at different targets, such as files, databases,
emails. 

Using the Yii logging framework involves the following steps of work:
 
* Record [log messages](#log-messages) at various places in your code;
* Configure [log targets](#log-targets) in the application configuration to filter and gather log messages;
* Examine the filtered logged messages at different targets (e.g. the [Yii debugger](tool-debugger.md)).

In this section, we will mainly describe the first two steps.


## Log Messages <a name="log-messages"></a>

Recording log messages is as simple as calling one of the following logging methods:

* [[Yii::trace()]]: record a message to trace how a piece of code runs. This is mainly for development use.
* [[Yii::info()]]: record a message that conveys some useful information.
* [[Yii::warning()]]: record a warning message that indicates something unexpected has happened.
* [[Yii::error()]]: record a fatal error that should be investigated as soon as possible.

These logging methods record log messages at various *severity levels* and *categories*. They share
the same function signature `function ($message, $category = 'application')`, where `$message` stands for
the log message to be recorded, while `$category` is the category of the log message. The code in the following
example records a trace message under the default category `application`:

```php
Yii::trace('start calculating average revenue');
```

> Info: Log messages can be strings as well as complex data, such as arrays or objects. It is the responsibility
of [log targets](#log-targets) to properly deal with log messages. By default, if a log message is not a string,
it will be exported as a string by calling [[yii\helpers\VarDumper::export()]].

To better organize and filter log messages, it is recommended that you specify an appropriate category for each
log message. You may choose a hierarchical naming scheme for categories, which will make it easier for 
[log targets](#log-targets) to filter messages based on their categories. A simple yet effective naming scheme
is to use the PHP magic constant `__METHOD__` as category names. This is also the approached used in the core 
Yii framework code. For example,

```php
Yii::trace('start calculating average revenue', __METHOD__);
```

The `__METHOD__` constant evaluates as the name of the method (prefixed with the fully qualified class name) where 
the constant appears. For example, it equals to the string `'app\controllers\RevenueController::calculate'` if 
the above line of code is called within this method.

> Info: The logging methods described above are actually shortcuts to the [[yii\log\Logger::log()|log()]] method 
of the [[yii\log\Logger|logger object]] which is a singleton accessible through the expression `Yii::getLogger()`. When
enough messages are logged or when the application ends, the logger object will call a  
[[yii\log\Dispatcher|message dispatcher]] to send recorded log messages to the registered [log targets](#log-targets).


## Log Targets <a name="log-targets"></a>

A log target is an instance of [[yii\log\Target]] class or its child class. It filters the log messages by their
severity levels and categories, and then processes them in a particular way. For example, a 
[[yii\log\DbTarget|database target]] keeps the log messages in a database table, while 
a [[yii\log\EmailTarget|email target]] sends the log messages to pre-specified email addresses.

You can register multiple log targets in an application by configuring them through the `log` application component 
in the application configuration, like the following:

```php
return [
    // the "log" component must be loaded during bootstrapping time
    'bootstrap' => ['log'],
    
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
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

Note that the `log` component must be loaded during [bootstrapping](runtime-bootstrapping.md) time so that
it can capture the log messages flushed from the [[yii\log\Logger|message logger]] as early as possible.

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

  
### Message Filtering <a name="message-filtering"></a>

For each log target, you can configure its [[yii\log\Target::levels|levels]] and 
[[yii\log\Target::categories|categories]] properties to specify which severity levels and categories of the messages
that you want the target to process.

The [[yii\log\Target::levels|levels]] property takes an array consisting of one or several of the following values:

* `error`: corresponding to messages logged by [[Yii::error()]].
* `warning`: corresponding to messages logged by [[Yii::warning()]].
* `info`: corresponding to messages logged by [[Yii::info()]].
* `trace`: corresponding to messages logged by [[Yii::trace()]].
* `profile`: corresponding to messages logged by [[Yii::beginProfile()]], which will be explained in more details
in the [Profiling](#profiling) subsection.

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

Besides whitelisting the categories by the [[yii\log\Target::categories|categories]] property, you may also
blacklisting certain categories by the [[yii\log\Target::except|except]] property. If the category of a message
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


### Message Formatting <a name="message-formatting"></a>


### Message Flushing and Exporting <a name="flushing-exporting"></a>


Each log target can have a name and can be referenced via the [[yii\log\Logger::targets|targets]] property as follows:

```php
Yii::$app->log->targets['file']->enabled = false;
```

When the application ends or [[yii\log\Logger::flushInterval|flushInterval]] is reached, Logger will call
[[yii\log\Logger::flush()|flush()]] to send logged messages to different log targets, such as file, email, web.


### Creating New Targets <a name="new-targets"></a>



## Profiling <a name="profiling"></a>

Performance profiling is a special type of message logging that can be used to measure the time needed for the
specified code blocks to execute and find out what the performance bottleneck is.

To use it we need to identify which code blocks need to be profiled. Then we mark the beginning and the end of each code
block by inserting the following methods:

```php
\Yii::beginProfile('myBenchmark');
...code block being profiled...
\Yii::endProfile('myBenchmark');
```

where `myBenchmark` uniquely identifies the code block.

Note, code blocks need to be nested properly such as

```php
\Yii::beginProfile('block1');
    // some code to be profiled
    \Yii::beginProfile('block2');
        // some other code to be profiled
    \Yii::endProfile('block2');
\Yii::endProfile('block1');
```

Profiling results [could be displayed in debugger](module-debug.md).

