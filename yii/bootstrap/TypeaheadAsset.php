<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TypeaheadAsset extends AssetBundle
{
	public $sourcePath = '@yii/bootstrap/assets';
	public $js = array(
		'js/bootstrap-typeahead.js',
	);
	public $depends = array(
		'yii\bootstrap\TransitionAsset',
		'yii\bootstrap\BootstrapAsset',
		'yii\web\JqueryAsset',
	);
}
