<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ViewNotFoundException 表示由找不到视图文件引起的异常。
 *
 * @author Alexander Makarov
 * @since 2.0.10
 */
class ViewNotFoundException extends InvalidArgumentException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'View not Found';
    }
}
