<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewContextInterface 是应该由希望支持相对视图名称的类实现的接口。
 *
 * 应该实现方法 [[getViewPath()]] 以返回可以作为相对视图名称前缀的视图路径。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ViewContextInterface
{
    /**
     * @return string 可以为相对视图名称添加前缀的视图路径。
     */
    public function getViewPath();
}
