<?php

$config = require(__DIR__.'/../yii_bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
	$config,
	require(__DIR__ . '/../../config/codeception/functional.php')
);

$application = new yii\web\Application($config);
