<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\config;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
	public $basePath = '@wwwroot';
	public $baseUrl = '@www';
	public $css = array(
		'css/site.css',
	);
	public $js = array(
	);
	public $depends = array(
		'yii\web\YiiAsset',
		'yii\bootstrap\ResponsiveAsset',
	);
}
