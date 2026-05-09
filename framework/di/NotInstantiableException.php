<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\di;

use yii\base\InvalidConfigException;

/**
 * NotInstantiableException represents an exception caused by incorrect dependency injection container
 * configuration or usage.
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
class NotInstantiableException extends InvalidConfigException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($class, $message = null, $code = 0, $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not instantiable';
    }
}
