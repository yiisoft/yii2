<?php
return [
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'extensions' => require(__DIR__ . '/../../vendor/yiisoft/extensions.php'),
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'mail' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mails',
		],
	],
];
