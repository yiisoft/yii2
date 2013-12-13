<?php
// Here you can initialize variables that will for your tests

$config = require(__DIR__.'/../yii_bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
	$config,
	require(__DIR__ . '/../../config/codeception/acceptance.php')
);

$application = new yii\web\Application($config);
