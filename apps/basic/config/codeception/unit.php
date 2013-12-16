<?php

// configuration adjustments for codeception unit tests. Will be merged with web.php config.

return [
	'components' => [
		'fixture'	=>	[
			'class'		=>	'yii\test\DbFixtureManager',
			'basePath'	=>	'@tests/unit/fixtures',
		],
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2basic_unit',
		],
	],
];
