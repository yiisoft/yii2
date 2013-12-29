<?php

use yii\helpers\ArrayHelper;

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = TEST_ENTRY_URL;

$config = require(__DIR__ . '/../../config/web.php');

return ArrayHelper::merge($config, [
	'components' => [
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2_basic_functional',
		],
		'urlManager' => [
			'showScriptName' => true,
		],
	],
]);
