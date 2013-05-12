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
	'yii/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets/bootstrap',
		'js' => array(
			'/js/bootstrap.min.js',
		),
		'css' => array(
			'css/bootstrap.css'
		),
		'depends' => array('yii'),
	),
	'yii/bootstrap-responsive' => array(
		'sourcePath' => __DIR__ . '/assets/bootstrap',
		'css' => array(
			'css/bootstrap-responsive.css'
		),
		'depends' => array('yii/bootstrap'),
	)
);
