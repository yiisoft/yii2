<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 * Class ClassDoc
 */
class ClassDoc extends TypeDoc
{
	public $parentClass;

	public $isAbstract;
	public $isFinal;

	public $interfaces = [];
	public $traits = [];
	// will be set by Context::updateReferences()
	public $subclasses = [];

	/**
	 * @var EventDoc[]
	 */
	public $events = [];
	/**
	 * @var ConstDoc[]
	 */
	public $constants = [];


	/**
	 * @param \phpDocumentor\Reflection\ClassReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector, $config = [])
	{
		parent::__construct($reflector, $config);

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
		foreach($reflector->getConstants() as $constantReflector) {
			if (strncmp($constantReflector->getShortName(), 'EVENT_', 6) == 0) {
				$event = new EventDoc($constantReflector);
				$event->definedBy = $this->name;
				$this->events[$event->name] = $event;
			} else {
				$constant = new ConstDoc($constantReflector);
				$constant->definedBy = $this->name;
				$this->constants[$constant->name] = $constant;
			}
		}
	}
}