<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * NotSupportedException 表示访问不受支持的功能导致的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NotSupportedException extends Exception
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
