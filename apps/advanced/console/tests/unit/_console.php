<?php

return yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../../common/config/main.php'),
	require(__DIR__ . '/../../../common/config/main-local.php'),
	require(__DIR__ . '/../../config/main.php'),
	require(__DIR__ . '/../../config/main-local.php'),
	[
		'components' => [
			'db' => [
				'dsn' => 'pgsql:host=localhost;dbname=yii2_advanced_unit',
			],
		],
	]
);
