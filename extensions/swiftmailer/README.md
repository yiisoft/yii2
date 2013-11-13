SwiftMailer Extension for Yii 2
===============================

This extension provides a `SwiftMailer` mail solution for Yii 2.

To use this extension,  simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'mail' => [
			'class' => 'yii\swiftmailer\Mailer',
		],
	],
];
```

You can then send an email as follows:

```php
Yii::$app->mail->compose('contact/html')
     ->from('from@domain.com')
     ->to($form->email)
     ->subject($form->subject)
     ->send();
```

For further instructions refer to the related section in the Yii Definitive Guide.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require yiisoft/yii2-swiftmailer "*"
```

or add

```json
"yiisoft/yii2-swiftmailer": "*"
```

to the require section of your composer.json.
