Logging
=======

Yii provides flexible and extensible logger that is able to handle messages according to serverity level or their type.
You may filter messages by multiple criteria and forward them to files, email, debugger etc.

Logging basics
--------------

Basic logging is as simple as calling one method:

```php
\Yii::info('Hello, I am a test log message');
```

### Message category

Additionally to the message itself message category could be specified in order to allow filtering such messages and
handing these differently. Message category is passed as a second argument of logging methods and is `application` by
default.

### Severity levels

There are multiple severity levels and corresponding methods available:

- `\Yii::trace` used maily for development purpose to indicate workflow of some code. Note that it only works in
  development mode when `YII_DEBUG` is set to `true`.
- `\Yii::error` used when there's unrecoverable error.
- `\Yii::warning` used when an error occured but execution can be continued.
- `\Yii::info` used to keep record of important events such as administrator logins.

Log targets
-----------

When one of the logging methods is called, message is passed to `\yii\log\Logger` component also accessible as
`Yii::$app->log`. Logger accumulates messages in memory and then when there are enough messages or when current
request finishes, sends them to different log targets, such as file or email.

You may configure the targets in application configuration, like the following:

```php
[
	'components' => [
		'log' => [
			'targets' => [
				'file' => [
					'class' => 'yii\log\FileTarget',
					'levels' => ['trace', 'info'],
					'categories' => ['yii\*'],
				],
				'email' => [
					'class' => 'yii\log\EmailTarget',
					'levels' => ['error', 'warning'],
					'emails' => ['admin@example.com'],
				],
			],
		],
	],
]
```

In the config above we are defining two log targets: file and email. In both cases we are filtering messages handles by
these targets by severity. In case of file target we're additionally filter by category. `yii\*` means all categories
starting with `yii\`.

Each log target can have a name and can be referenced via the [[targets]] property as follows:

```php
Yii::$app->log->targets['file']->enabled = false;
```

When the application ends or [[flushInterval]] is reached, Logger will call [[flush()]] to send logged messages to
different log targets, such as file, email, Web.


Configuring context information
-------------------------------

Profiling
---------

TBD

- [[Yii::beginProfile()]]
- [[Yii::endProfile()]]


