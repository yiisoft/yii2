<?php

return array(
	'yii' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/jquery' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			YII_DEBUG ? 'jquery.js' : 'jquery.min.js',
		),
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
		'depends' => array('yii'),
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
	'yii/punycode' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			YII_DEBUG ? 'punycode/punycode.js' : 'punycode/punycode.min.js',
		),
	),
);
