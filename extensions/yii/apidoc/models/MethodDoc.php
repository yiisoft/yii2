<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

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
	 * @param array $config
	 */
	public function __construct($reflector = null, $config = [])
	{
		parent::__construct($reflector, $config);

		if ($reflector === null) {
			return;
		}

		$this->isAbstract = $reflector->isAbstract();
		$this->isFinal = $reflector->isFinal();
		$this->isStatic = $reflector->isStatic();

		$this->visibility = $reflector->getVisibility();
	}
}
