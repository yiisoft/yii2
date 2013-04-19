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
interface IAssetConverter
{
	public function convert($asset, $basePath, $baseUrl);
}