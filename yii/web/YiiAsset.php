<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class YiiAsset extends AssetBundle
{
	public $sourcePath = '@yii/assets';
	public $js = array(
		'yii.js',
	);
	public $depends = array(
		'yii\web\JqueryAsset',
	);
}
