<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=yii2advanced',
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		],
		'mail' => [
			'class' => 'yii\swiftmailer\Mailer',
			'viewPath' => '@common/mails',
		],
	],
];
