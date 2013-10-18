<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class YiiAsset extends AssetBundle
{
	public $sourcePath = '@yii/assets';
	public $js = [
		'yii.js',
	];
	public $depends = [
		'yii\web\JqueryAsset',
	];
}
