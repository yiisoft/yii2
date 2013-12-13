<?php

$config = require(__DIR__.'/../yii_bootstrap.php');

return yii\helpers\ArrayHelper::merge(
	$config,
	require(__DIR__ . '/../../config/codeception/unit.php')
);
