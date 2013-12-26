<?php

// add unit testing specific bootstrap code here

yii\codeception\TestCase::$appConfig = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/web.php'),
	require(__DIR__ . '/../../config/codeception/unit.php')
);
