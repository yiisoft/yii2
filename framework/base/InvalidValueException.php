<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InvalidValueException represents an exception caused by a function returning a value of unexpected type.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InvalidValueException extends \UnexpectedValueException
{
    /**
      * @return string the user-friendly name of this exception
      */
     public function getName()
     {
         return 'Invalid Return Value';
     }
}
