<?php

return array(
	'yii/jui/core' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.core.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/jui/widget' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.widget.js',
		),
	),
	'yii/jui/accordion' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.accordion.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget'),
	),
	'yii/jui/autocomplete' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.autocomplete.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/position', 'yii/jui/menu'),
	),
	'yii/jui/button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.button.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget'),
	),
	'yii/jui/datepicker' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.datepicker.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/dialog' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.dialog.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/button', 'yii/jui/draggable', 'yii/jui/mouse', 'yii/jui/position', 'yii/jui/resizeable'),
	),
	//@todo next depencies
	'yii/jui/draggable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.draggable.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/droppable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.droppable.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/effect' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/effect/blind' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-blind.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/bounce' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-bounce.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/clip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-clip.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/drop' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-drop.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/explode' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-explode.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/fade' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-fade.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/fold' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-fold.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/highlight' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-highlight.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/pulsate' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-pulsate.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/scale' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-scale.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/shake' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-shake.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/slide' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-slide.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/effect/transfer' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect-transfer.js',
		),
		'depends' => array('yii/jui/effect'),
	),
	'yii/jui/menu' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.menu.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/mouse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.mouse.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/position' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.position.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/progressbar' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.progressbar.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/resizable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.resizable.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/selectable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.selectable.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/slider' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.slider.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/sortable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.sortable.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/spinner' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.spinner.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/tabs' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.tabs.js',
		),
		'depends' => array('yii/jui/core'),
	),
	'yii/jui/tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.tooltip.js',
		),
		'depends' => array('yii/jui/core'),
	),
);
