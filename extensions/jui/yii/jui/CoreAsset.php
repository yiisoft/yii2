<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CoreAsset extends AssetBundle
{
	public $sourcePath = '@yii/jui/assets';
	public $js = array(
		'jquery.ui.core.js',
	);
	public $depends = array(
		'yii\web\JqueryAsset',
	);
}
