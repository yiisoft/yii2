<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;


use yii\base\Component;
use yii\base\Exception;

class Context extends Component
{
	public $basePath;

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
		$file = new File($fileName, $this);
		$this->files[$fileName] = $file;
	}

	public function addClass($class)
	{
		if (isset($this->classes[$class->name])) {
			throw new Exception('Duplicate class definition: ' . $class->name . ' in file ' . $class->fileName);
		}
		$this->classes[$class->name] = $class;
	}

	public function addInterface($interface)
	{
		if (isset($this->interfaces[$interface->name])) {
			throw new Exception('Duplicate interface definition: ' . $interface->name . ' in file ' . $interface->fileName);
		}
		$this->interfaces[$interface->name] = $interface;
	}

	public function addTrait($trait)
	{
		if (isset($this->traits[$trait->name])) {
			throw new Exception('Duplicate trait definition: ' . $trait->name . ' in file ' . $trait->fileName);
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
		// TODO update interface and trait usages
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