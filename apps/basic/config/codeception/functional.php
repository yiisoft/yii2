<?php

// configuration adjustments for codeception functional tests. Will be merged with web.php config.

return [
	'components' => [
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2basic_functional',
		],
	],
];
