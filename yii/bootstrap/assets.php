<?php

return array(
	'yii/bootstrap/css' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
		),
	),
	'yii/bootstrap/css-responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap-responsive.css' : 'css/bootstrap-responsive.min.css',
		),
		'depends' => array('yii/bootstrap'),
	),
	'yii/bootstrap/all' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			YII_DEBUG ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/affix' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-affix.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/alert' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-alert.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-button.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/carousel' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-carousel.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/collapse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-collapse.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/dropdown' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-dropdown.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/modal' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-modal.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/popover' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-popover.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/tooltip', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/scrollspy' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-scrollspy.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/tab' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tab.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tooltip.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/transition' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-transition.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
	'yii/bootstrap/typeahead' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-typeahead.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/css'),
	),
);
