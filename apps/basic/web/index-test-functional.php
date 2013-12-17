<?php

// this file is used as the entry script for codeception functional testing

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/web.php'),
	require(__DIR__ . '/../config/codeception/functional.php')
);

$config['class'] = 'yii\web\Application';
return $config;
