<?php

namespace yii\phpdoc\models;

/**
 * Class ClassDoc
 */
class ClassDoc extends BaseDoc
{
	public $parentClass;

	public $isAbstract;
	public $isFinal;

	public $interfaces = [];
	public $traits = [];

	public $properties = [];
	public $methods = [];
	public $events = [];
	public $constants = [];

//	public $protectedPropertyCount=0;
//	public $publicPropertyCount=0;
//	public $protectedMethodCount=0;
//	public $publicMethodCount=0;
//
//	public $nativePropertyCount=0;
//	public $nativeMethodCount=0;
//	public $nativeEventCount=0;

	public $sinceVersion;

	public $subclasses = [];


	/**
	 * @param \phpDocumentor\Reflection\ClassReflector $reflector
	 * @param Context $context
	 * @param array $config
	 */
	public function __construct($reflector, $context = null, $config = [])
	{
		// base properties
		$this->name = ltrim($reflector->getName(), '\\');
		$this->startLine = $reflector->getNode()->getAttribute('startLine');
		$this->endLine = $reflector->getNode()->getAttribute('endLine');

		$this->parentClass = ltrim($reflector->getParentClass(), '\\');
		if (empty($this->parentClass)) {
			$this->parentClass = null;
		}
		$this->isAbstract = $reflector->isAbstract();
		$this->isFinal = $reflector->isFinal();

		foreach($reflector->getInterfaces() as $interface) {
			$this->interfaces[] = ltrim($interface, '\\');
		}
		foreach($reflector->getTraits() as $trait) {
			$this->traits[] = ltrim($trait, '\\');
		}

		// TODO methods

		// TODO properties

		// TODO docblock

		if ($context !== null) {
			$context->addClass($this);
		}

		parent::__construct($config);
	}
}