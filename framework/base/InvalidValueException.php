<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidValueException 表示由返回意外类型值的方法引起的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidValueException extends \UnexpectedValueException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
