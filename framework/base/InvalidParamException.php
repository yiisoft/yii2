<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidParamException 表示由传递给方法的无效参数引起的异常。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @deprecated since 2.0.14. 请使用 [[InvalidArgumentException]] 代替。
 */
class InvalidParamException extends \BadMethodCallException
{
    /**
     * @return string 此异常的用户友好名称
     */
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
