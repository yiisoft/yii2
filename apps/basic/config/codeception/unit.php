<?php

return [
	'components' => [
		'fixture'	=>	[
			'class'		=>	'yii\test\DbFixtureManager',
			'basePath'	=>	'@app/tests/unit/fixtures',
		],
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2basic_unit',
		],
	],
];
