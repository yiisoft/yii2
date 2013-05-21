<?php

return array(
	'yii/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap.css' : 'css/bootstrap.min.css',
		),
	),
	'yii/bootstrap-responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'css/bootstrap-responsive.css' : 'css/bootstrap-responsive.min.css',
		),
		'depends' => array('yii/bootstrap'),
	),
	'yii/bootstrap-js' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			YII_DEBUG ? 'js/bootstrap.js' : 'js/bootstrap.min.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-affix' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-affix.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-alert' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-alert.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-button.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-carousel' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-carousel.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-collapse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-collapse.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-dropdown' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-dropdown.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-modal' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-modal.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-popover' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-popover.js',
		),
		'depends' => array('yii/jquery','yii/bootstrap-js-tooltip'),
	),
	'yii/bootstrap-js-scrollspy' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-scrollspy.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-tab' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tab.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-tooltip.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-transition' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-transition.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-typeahead' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'js/bootstrap-typeahead.js',
		),
		'depends' => array('yii/jquery'),
	),
);
