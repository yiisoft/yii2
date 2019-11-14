<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidArgumentException 表示由传递给方法的无效参数引起的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.14
 */
class InvalidArgumentException extends InvalidParamException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Invalid Argument';
    }
}
