<?php

return yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/console.php'),
	require(__DIR__ . '/../../config/codeception/unit.php')
);
