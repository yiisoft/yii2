<?php

defined('YII_DEBUG') ?: define('YII_DEBUG', false);

require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');

$config = [
	'id' => 'benchmark',
	'basePath' => __DIR__ . '/protected',
	'components' => [
		'urlManager' => [
			'enablePrettyUrl' => true,
		],
	],
];

$application = new yii\web\Application($config);
$application->run();
