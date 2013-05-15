<?php

return array(
	'jquery' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.min.js',
		),
	),
	'yii' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.js',
		),
		'depends' => array('jquery'),
	),
	'yii/validation' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.validation.js',
		),
		'depends' => array('yii'),
	),
	'yii/form' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.activeForm.js',
		),
		'depends' => array('yii', 'yii/validation'),
	),
	'yii/captcha' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.captcha.js',
		),
		'depends' => array('yii'),
	),
	'yii/debug' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.debug.js',
		),
		'depends' => array('yii'),
	),
	'punycode' => array(
		'sourcePath' => __DIR__ . '/vendor/bestiejs/punycode.js',
		'js' => array(
			'punycode.min.js',
		),
	),
);
