<?php

return array(
	'yii/jui' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.theme.css',
		),
		'js' => array(
			'jquery.ui.core.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/jui/accordion' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.accordion.css',
		),
		'js' => array(
			'jquery.ui.accordion.js',
		),
		'depends' => array('yii/jui'),
	),
);
