<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * The IAssetConverter interface must be implemented by asset converter classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface IAssetConverter
{
	/**
	 * Converts a given asset file into a CSS or JS file.
	 * @param string $asset the asset file path, relative to $basePath
	 * @param string $basePath the directory the $asset is relative to.
	 * @param string $baseUrl the URL corresponding to $basePath
	 * @return string the URL to the converted asset file. If the given asset does not
	 * need conversion, "$baseUrl/$asset" should be returned.
	 */
	public function convert($asset, $basePath, $baseUrl);
}
