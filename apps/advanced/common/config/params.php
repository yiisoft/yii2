<?php

Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');

return [
	'adminEmail' => 'admin@example.com',
	'supportEmail' => 'support@example.com',

	'components.cache' => [
		'class' => 'yii\caching\FileCache',
	],

	'components.mail' => [
		'class' => 'yii\swiftmailer\Mailer',
		'viewPath' => '@common/mails',
	],
];
