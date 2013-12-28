<?php

use yii\helpers\ArrayHelper;

$config = require(__DIR__ . '/../../config/web.php');

return ArrayHelper::merge($config, [
	'components' => [
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2_basic_acceptance',
		],
	],
]);
