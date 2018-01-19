<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * TooFewParametersException represents exception, caused by incomplete set of requiered arguments
 *
 * @author Igor Lis (ruwerefox666@gmail.com)
 * @since 2.0.14
 */
class TooFewArgumentsException extends UserException
{
    public function getName()
    {
        return 'Too Few Arguments';
    }
}
