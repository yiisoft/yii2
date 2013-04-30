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
			'yii-validation.js',
		),
		'depends' => array('yii'),
	),
	'yii/form' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'yii-form.js',
		),
		'depends' => array('yii', 'yii/validation'),
	),
);