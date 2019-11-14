<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * 资源编译器必须继承 AssetConverterInterface。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface AssetConverterInterface
{
    /**
     * 将所给的资源文件编译成 JS 或者 CSS 文件。
     * @param string $asset 资源文件路径，相对于 $basePath。
     * @param string $basePath 资源 $asset 相对于的目录。
     * @return string 编译成的资源文件路径，相对于 $basePath。
     */
    public function convert($asset, $basePath);
}
