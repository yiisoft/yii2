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
	'yii/bootstrap' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'bootstrap/css/bootstrap.css' : 'bootstrap/css/bootstrap.min.css',
		),
	),
	'yii/bootstrap-responsive' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			YII_DEBUG ? 'bootstrap/css/bootstrap-responsive.css' : 'bootstrap/css/bootstrap-responsive.min.css',
		),
		'depends' => array('yii/bootstrap'),
	),
	'yii/bootstrap-js' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			YII_DEBUG ? 'bootstrap/js/bootstrap.js' : 'bootstrap/js/bootstrap.min.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-affix' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-affix.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-alert' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-alert.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-button.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-carousel' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-carousel.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-collapse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-collapse.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-dropdown' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-dropdown.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-modal' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-modal.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-popover' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-popover.js',
		),
		'depends' => array('yii/jquery','yii/bootstrap-js-tooltip'),
	),
	'yii/bootstrap-js-scrollspy' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-scrollspy.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-tab' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-tab.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-tooltip.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-transition' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-transition.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/bootstrap-js-typeahead' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'bootstrap/js/bootstrap-typeahead.js',
		),
		'depends' => array('yii/jquery'),
	),
);
