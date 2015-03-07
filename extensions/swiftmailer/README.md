SwiftMailer Extension for Yii 2
===============================

This extension provides a [SwiftMailer](http://swiftmailer.org/) mail solution for Yii 2.

This repository is a git submodule of <https://github.com/yiisoft/yii2>.
Please submit issue reports and pull requests to the main repository.
For license information check the [LICENSE](LICENSE.md)-file.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-swiftmailer
```

or add

```json
"yiisoft/yii2-swiftmailer": "~2.0.0"
```

to the require section of your composer.json.

Usage
-----

To use this extension,  simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
```

You can then send an email as follows:

```php
Yii::$app->mailer->compose('contact/html')
     ->setFrom('from@domain.com')
     ->setTo($form->email)
     ->setSubject($form->subject)
     ->send();
```

### Activating a logger
In your application configuration:
```php
'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            ...
            'useFileTransport' => false,
            'transport' => [
                ...
                'plugins' => [
                    [
                        'class' => 'Swift_Plugins_LoggerPlugin',
                        'constructArgs' => [new Swift_Plugins_Loggers_ArrayLogger],
                    ],
                ],
            ],
        ],
```

You can use any implementation of the `Swift_Plugin_Logger` like `Swift_Plugins_Loggers_ArrayLogger`
or `Swift_Plugins_Loggers_EchoLogger`. Use the Array Logger if you want to get the
logs in a specific point of your application execution. Use the Echo Logger if you
want to get them in real time. (Please, refer to the Swift Mailer
[documentation](http://swiftmailer.org/docs/plugins.html#logger-plugin) for details).

Now, you can get the logs this way:

```php
$mailer = Yii::$app->mailer;
$logger = $mailer->getLogger();

$mailer->compose()...
        ->send();

$myLogs = $logger->dump();
```
---

For further instructions refer to the related section in the Yii Definitive Guide.
