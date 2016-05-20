<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use \yii\base\InvalidConfigException;
/**
 * NotInstantiableException represents an exception caused by incorrect DI configuration / usage.
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.8
 */
class NotInstantiableException extends InvalidConfigException
{
    public function __construct($class, $message = null, $code = 0, Exception $previous = null)
    {
        if (!isset($message)) {
            $message = "$class is not instantiable.";
        }
        parent::__construct($message, $code, $previous);
    }


    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Class is not instantiable';
    }
}
