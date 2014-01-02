<?php

return yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../../config/web.php'),
	require(__DIR__ . '/../_config.php'),
	[
		'components' => [
			'fixture' => [
				'class' => 'yii\test\DbFixtureManager',
				'basePath' => '@tests/unit/fixtures',
			],
			'db' => [
				'dsn' => 'mysql:host=localhost;dbname=yii2_basic_unit',
			],
		],
	]
);
