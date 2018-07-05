<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use yii\base\InvalidConfigException;

/**
 * NotInstantiableException is thrown when container tries to resolve
 * object having circular reference in configuration.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 * @since 3.0.0
 */
class CircularReferenceException extends InvalidConfigException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($class, $message = null, $code = 0, \Exception $previous = null)
    {
        if ($message === null) {
            $message = "Circular reference in $class.";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Circular reference';
    }
}
