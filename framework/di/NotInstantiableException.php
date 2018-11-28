<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use yii\base\InvalidConfigException;

/**
 * NotInstantiableException 表示由不正确的依赖项注入容器配置或使用
 * 引起的异常。
 *
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 */
class NotInstantiableException extends InvalidConfigException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($class, $message = null, $code = 0, \Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string 此异常的用户友好的名称
     */
    public function getName()
    {
        return 'Not instantiable';
    }
}
