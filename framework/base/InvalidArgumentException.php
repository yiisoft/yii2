<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidArgumentException represents an exception caused by invalid arguments passed to a method.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.14
 */
class InvalidArgumentException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Argument';
    }
}
