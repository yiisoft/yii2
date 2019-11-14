<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidCallException 表示以错误方式调用方法导致的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Invalid Call';
    }
}
