<?php

$kernel = AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'excludePaths' => [
		__DIR__.'/../tests',
		__DIR__.'/../mails',
		__DIR__.'/../runtime',
		__DIR__.'/../config',
		__DIR__.'/../controllers',
		__DIR__.'/../assets',
	],
]);

$kernel->loadFile(__DIR__ . '/../../vendor/yiisoft/yii2/yii/Yii.php');
