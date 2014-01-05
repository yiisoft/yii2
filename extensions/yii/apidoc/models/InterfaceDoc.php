<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

class InterfaceDoc extends BaseDoc
{
	public $parentInterfaces = [];

	public $implementedBy = [];

	public $methods = [];

	/**
	 * @param \phpDocumentor\Reflection\InterfaceReflector $reflector
	 * @param Context $context
	 * @param array $config
	 */
	public function __construct($reflector, $context = null, $config = [])
	{
		// base properties
		$this->name = ltrim($reflector->getName(), '\\');
		$this->startLine = $reflector->getNode()->getAttribute('startLine');
		$this->endLine = $reflector->getNode()->getAttribute('endLine');

		foreach($reflector->getParentInterfaces() as $interface) {
			$this->parentInterfaces[] = ltrim($interface, '\\');
		}

		// TODO methods

		// TODO docblock

		if ($context !== null) {
			$context->addInterface($this);
		}

		parent::__construct($config);
	}

}