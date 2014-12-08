<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 * Represents API documentation information for a `constant`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ConstDoc extends BaseDoc
{
    public $definedBy;
    public $value;


    /**
     * @param \phpDocumentor\Reflection\ClassReflector\ConstantReflector $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->value = $reflector->getValue();
    }
}
