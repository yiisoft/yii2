<?php
/**
 * application configurations shared by all test types
 */
return [
	'components' => [
		'mail' => [
			'useFileTransport' => true,
		],
		'urlManager' => [
			'showScriptName' => true,
		],
	],
];
