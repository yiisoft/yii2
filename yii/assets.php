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
			'jquery.js',
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
	'yii/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'bootstrap/css/bootstrap.css',
		),
		'js' => array(
			'bootstrap/js/bootstrap.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'bootstrap/css/bootstrap-responsive.css',
		),
		'depends' => array('yii/bootstrap'),
	),
	'yii/punycode' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'punycode/punycode.js',
		),
	),
);
