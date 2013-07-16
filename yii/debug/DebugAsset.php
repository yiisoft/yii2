<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DebugAsset extends AssetBundle
{
	public $sourcePath = '@yii/debug/assets';
	public $css = array(
		'main.css',
	);
	public $depends = array(
		'yii\web\YiiAsset',
		'yii\bootstrap\ResponsiveAsset',
	);
}
