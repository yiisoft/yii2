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
	'twitter/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'bootstrap/css/bootstrap.css',
		),
		'js' => array(
			'bootstrap/js/bootstrap.js',
		),
		'depends' => array('jquery'),
	),
	'twitter/bootstrap-responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'bootstrap/css/bootstrap-responsive.css',
		),
		'depends' => array('twitter/bootstrap'),
	),
	'punycode' => array(
		'sourcePath' => __DIR__ . '/vendor/bestiejs/punycode.js',
		'js' => array(
			'punycode.min.js',
		),
	),
);
