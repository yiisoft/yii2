<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewContextInterface is the interface that should implemented by classes who want to support relative view names.
 *
 * The method [[getViewPath()]] should be implemented to return the view path that may be prefixed to a relative view name.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
interface ViewContextInterface
{
    /**
     * @return string the view path that may be prefixed to a relative view name.
     */
    public function getViewPath();
}
