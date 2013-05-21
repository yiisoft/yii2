<?php

return array(
	'yii/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
		),
	),
	'yii/bootstrap/responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap-responsive.css' : 'css/bootstrap-responsive.min.css',
		),
		'depends' => array('yii/bootstrap'),
	),
	'yii/bootstrap/affix' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-affix.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/alert' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-alert.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-button.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/carousel' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-carousel.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/collapse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-collapse.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/dropdown' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-dropdown.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/modal' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-modal.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/popover' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-popover.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap/tooltip', 'yii/bootstrap'),
	),
	'yii/bootstrap/scrollspy' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-scrollspy.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/tab' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tab.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tooltip.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/transition' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-transition.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
	'yii/bootstrap/typeahead' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-typeahead.js',
		),
		'depends' => array('yii/jquery', 'yii/bootstrap'),
	),
);
