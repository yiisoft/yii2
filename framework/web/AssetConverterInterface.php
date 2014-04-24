<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * The AssetConverterInterface must be implemented by asset converter classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface AssetConverterInterface
{
    /**
     * Converts a given asset file into a CSS or JS file.
     * @param string $asset the asset file path, relative to $basePath
     * @param string $srcPath the asset source directory.
     * @param string $dstPath the asset destination directory.
     * @return string|boolean the converted asset file path, relative to $basePath,
     * if no conversion is made `false` will be returned.
     */
    public function convert($asset, $srcPath, $dstPath);
}
