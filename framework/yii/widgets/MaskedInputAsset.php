<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MaskedInputAsset extends AssetBundle
{
	public $sourcePath = '@yii/assets';
	public $js = [
		'jquery.maskedinput.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
	];
}
