<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Exception represents a generic exception for all purposes.
 *
 * For more details and usage information on Exception, see the [guide article on handling errors](guide:runtime-handling-errors).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Exception extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Exception';
    }
}
