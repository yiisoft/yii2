<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;


use phpDocumentor\Reflection\FileReflector;
use yii\base\Component;
use yii\base\Exception;

class Context extends Component
{
	/**
	 * @var array list of php files that have been added to this context.
	 */
	public $files = [];
	/**
	 * @var ClassDoc[]
	 */
	public $classes = [];
	/**
	 * @var InterfaceDoc[]
	 */
	public $interfaces = [];
	/**
	 * @var TraitDoc[]
	 */
	public $traits = [];


	public function addFile($fileName)
	{
		if (isset($this->files[$fileName])) {
			return;
		}
		$this->files[$fileName] = $fileName;

		$reflection = new FileReflector($fileName, true);
		$reflection->process();

		foreach($reflection->getClasses() as $class) {
			$class = new ClassDoc($class, $this);
			$class->sourceFile = $fileName;
			$this->addClass($class);
		}
		foreach($reflection->getInterfaces() as $interface) {
			$interface = new InterfaceDoc($interface, $this);
			$interface->sourceFile = $fileName;
			$this->addInterface($interface);
		}
		foreach($reflection->getTraits() as $trait) {
			$trait = new TraitDoc($trait, $this);
			$trait->sourceFile = $fileName;
			$this->addTrait($trait);
		}
	}

	/**
	 * @param ClassDoc $class
	 * @throws \yii\base\Exception when class is already part of this context
	 */
	public function addClass($class)
	{
		if (isset($this->classes[$class->name])) {
			throw new Exception('Duplicate class definition: ' . $class->name . ' in file ' . $class->sourceFile . '.');
		}
		$this->classes[$class->name] = $class;
	}

	/**
	 * @param InterfaceDoc $interface
	 * @throws \yii\base\Exception when interface is already part of this context
	 */
	public function addInterface($interface)
	{
		if (isset($this->interfaces[$interface->name])) {
			throw new Exception('Duplicate interface definition: ' . $interface->name . ' in file ' . $interface->sourceFile);
		}
		$this->interfaces[$interface->name] = $interface;
	}

	/**
	 * @param TraitDoc $trait
	 * @throws \yii\base\Exception when trait is already part of this context
	 */
	public function addTrait($trait)
	{
		if (isset($this->traits[$trait->name])) {
			throw new Exception('Duplicate trait definition: ' . $trait->name . ' in file ' . $trait->sourceFile);
		}
		$this->traits[$trait->name] = $trait;
	}

	public function updateReferences()
	{
		// update all subclass references
		foreach($this->classes as $class) {
			$className = $class->name;
			while (isset($this->classes[$class->parentClass])) {
				$class = $this->classes[$class->parentClass];
				$class->subclasses[] = $className;
			}
		}
		// update interfaces of subclasses
		foreach($this->classes as $class) {
			$this->updateSubclassInferfacesTraits($class);
		}
		// update implementedBy and usedBy for interfaces and traits
		foreach($this->classes as $class) {
			foreach($class->interfaces as $interface) {
				if (isset($this->interfaces[$interface])) {
					$this->interfaces[$interface]->implementedBy[] = $class->name;
				}
			}
			foreach($class->traits as $trait) {
				if (isset($this->traits[$trait])) {
					$this->traits[$trait]->usedBy[] = $class->name;
				}
			}
		}
	}

	/**
	 * Add implemented interfaces and used traits to subclasses
	 * @param ClassDoc $class
	 */
	protected function updateSubclassInferfacesTraits($class)
	{
		foreach($class->subclasses as $subclass) {
			$subclass = $this->classes[$subclass];
			$subclass->interfaces = array_unique(array_merge($subclass->interfaces, $class->interfaces));
			$subclass->traits = array_unique(array_merge($subclass->traits, $class->traits));
			$this->updateSubclassInferfacesTraits($subclass);
		}
	}
}