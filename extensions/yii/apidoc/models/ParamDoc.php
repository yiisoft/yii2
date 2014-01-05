<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use yii\base\Object;

class ParamDoc extends Object
{
	public $name;
	public $typeHint;
	public $isOptional;
	public $defaultValue;
	public $isPassedByReference;

	// will be set by creating class
	public $description;
	public $type;
	public $types;

	/**
	 * @param \phpDocumentor\Reflection\FunctionReflector\ArgumentReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector, $config = [])
	{
		parent::__construct($config);

		$this->name = $reflector->getName();
		$this->typeHint = $reflector->getType();
		$this->isOptional = $reflector->getDefault() !== null;
		$this->defaultValue = $reflector->getDefault(); // TODO what about null value?
		$this->isPassedByReference = $reflector->isByRef();
	}
}