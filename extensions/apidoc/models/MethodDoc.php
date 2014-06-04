<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 * Represents API documentation information for a `method`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MethodDoc extends FunctionDoc
{
    public $isAbstract;
    public $isFinal;

    public $isStatic;

    public $visibility;

    // will be set by creating class
    public $definedBy;

    /**
     * @param \phpDocumentor\Reflection\ClassReflector\MethodReflector $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->isAbstract = $reflector->isAbstract();
        $this->isFinal = $reflector->isFinal();
        $this->isStatic = $reflector->isStatic();

        $this->visibility = $reflector->getVisibility();
    }
}
