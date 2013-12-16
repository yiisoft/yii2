<?php

// configuration adjustments for codeception acceptance tests. Will be merged with web.php config.

return [
	'components' => [
		'db' => [
			'dsn' => 'mysql:host=localhost;dbname=yii2basic_acceptance',
		],
	],
];
