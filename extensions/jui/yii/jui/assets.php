<?php

return array(
	yii\jui\CoreAsset::className(),
	yii\jui\WidgetAsset::className(),
	'yii/jui/accordion' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.accordion.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/effect/all'),
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
		'depends' => array('yii/jui/core', 'yii/jui/effect/all'),
	),
	'yii/jui/datepicker/i18n/af' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-af.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ar' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ar.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ar_DZ' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ar-DZ.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/az' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-az.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/be' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-be.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/bg' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-bg.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/bs' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-bs.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ca' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ca.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/cs' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-cs.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/cy_GB' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-cy-GB.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/da' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-da.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/de' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-de.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/el' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-el.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/en_AU' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-en-AU.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/en_GB' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-en-GB.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/en_NZ' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-en-NZ.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/eo' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-eo.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/es' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-es.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/et' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-et.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/eu' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-eu.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fa' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fa.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fi' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fi.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fo' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fo.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fr' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fr.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fr_CA' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fr-CA.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/fr_CH' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-fr-CH.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/gl' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-gl.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/he' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-he.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/hi' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-hi.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/hr' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-hr.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/hu' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-hu.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/hy' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-hy.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/id' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-id.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/is' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-is.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/it' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-it.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ja' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ja.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ka' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ka.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/kk' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-kk.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/km' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-km.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ko' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ko.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ky' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ky.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/lb' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-lb.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/lt' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-lt.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/lv' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-lv.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/mk' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-mk.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ml' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ml.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ms.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/nb' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-nb.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/nl' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-nl.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/nl_BE' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-nl-BE.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/nn' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-nn.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/no' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-no.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/pl' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-pl.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/pt' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-pt.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/pt_BR' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-pt-BR.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/rm' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-rm.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ro' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ro.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ru' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ru.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sk' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sk.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sl' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sl.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sq' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sq.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sr' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sr.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sr_SR' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sr-SR.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/sv' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-sv.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/ta' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-ta.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/th' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-th.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/tj' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-tj.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/tr' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-tr.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/uk' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-uk.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/vi' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-vi.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/zh_CN' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-zh-CN.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/zh_HK' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-zh-HK.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/datepicker/i18n/zh_TW' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'i18n/jquery.ui.datepicker-zh-TW.js',
		),
		'depends' => array('yii/jui/datepicker'),
	),
	'yii/jui/dialog' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.dialog.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/button', 'yii/jui/draggable', 'yii/jui/mouse', 'yii/jui/position', 'yii/jui/resizable', 'yii/jui/effect/all'),
	),
	'yii/jui/draggable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.draggable.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/mouse'),
	),
	'yii/jui/droppable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.droppable.js',
		),
		'depends' => array('yii/jui/draggable'),
	),
	'yii/jui/effect' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/jui/effect/all' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.effect.js',
		),
		'depends' => array('yii/jui/effect/blind', 'yii/jui/effect/bounce', 'yii/jui/effect/clip', 'yii/jui/effect/drop', 'yii/jui/effect/explode', 'yii/jui/effect/fade', 'yii/jui/effect/fold', 'yii/jui/effect/highlight', 'yii/jui/effect/pulsate', 'yii/jui/effect/scale', 'yii/jui/effect/shake', 'yii/jui/effect/slide', 'yii/jui/effect/transfer'),
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
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/position'),
	),
	'yii/jui/mouse' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.mouse.js',
		),
		'depends' => array('yii/jui/widget'),
	),
	'yii/jui/position' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.position.js',
		),
		'depends' => array('yii/jquery'),
	),
	'yii/jui/progressbar' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.progressbar.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget'),
	),
	'yii/jui/resizable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.resizable.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/mouse'),
	),
	'yii/jui/selectable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.selectable.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/mouse'),
	),
	'yii/jui/slider' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.slider.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/mouse'),
	),
	'yii/jui/sortable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.sortable.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/mouse'),
	),
	'yii/jui/spinner' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.spinner.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/button'),
	),
	'yii/jui/tabs' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.tabs.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/effect/all'),
	),
	'yii/jui/tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'js' => array(
			'jquery.ui.tooltip.js',
		),
		'depends' => array('yii/jui/core', 'yii/jui/widget', 'yii/jui/position', 'yii/jui/effect/all'),
	),
	'yii/jui/theme/base' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.theme.css',
		),
	),
	'yii/jui/theme/base/core' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.core.css',
		),
		'depends' => array('yii/jui/theme/base'),
	),
	'yii/jui/theme/base/accordion' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.accordion.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/autocomplete' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.autocomplete.css',
		),
		'depends' => array('yii/jui/theme/base/core', 'yii/jui/theme/base/menu'),
	),
	'yii/jui/theme/base/button' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.button.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/datepicker' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.datepicker.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/dialog' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.dialog.css',
		),
		'depends' => array('yii/jui/theme/base/core', 'yii/jui/theme/base/button', 'yii/jui/theme/base/resizable'),
	),
	'yii/jui/theme/base/menu' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.menu.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/progressbar' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.progressbar.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/resizable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.resizable.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/selectable' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.selectable.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/slider' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.slider.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/spinner' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.spinner.css',
		),
		'depends' => array('yii/jui/theme/base/core', 'yii/jui/theme/base/button'),
	),
	'yii/jui/theme/base/tabs' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.tabs.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
	'yii/jui/theme/base/tooltip' => array(
		'sourcePath' => __DIR__ . '/assets',
		'css' => array(
			'themes/base/jquery.ui.tooltip.css',
		),
		'depends' => array('yii/jui/theme/base/core'),
	),
);
