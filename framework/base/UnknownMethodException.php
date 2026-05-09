<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * UnknownMethodException represents an exception caused by accessing an unknown object method.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UnknownMethodException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Method';
    }
}
