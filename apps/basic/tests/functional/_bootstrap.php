<?php

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/web.php'),
	require(__DIR__ . '/../../config/codeception/functional.php')
);

$application = new yii\web\Application($config);
