<?php

namespace yii\phpdoc\models;

use phpDocumentor\Reflection\FileReflector;
use yii\base\Object;

class File extends Object
{
	public $name;

	public $namespaces = [];
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

	private $_reflection;

	public function __construct($fileName, $context, $config = [])
	{
		$this->name = $fileName;
		$this->_reflection = new FileReflector($fileName, true);
		$this->_reflection->process();

		foreach($this->_reflection->getClasses() as $class) {
			$class = new ClassDoc($class, $context);
			$class->sourceFile = $fileName;
			$this->classes[] = $class;
		}
		foreach($this->_reflection->getInterfaces() as $interface) {
			$this->interfaces[] = new InterfaceDoc($interface, $context);
		}
		foreach($this->_reflection->getTraits() as $trait) {
			$this->traits[] = new TraitDoc($trait, $context);
		}

		parent::__construct($config);
	}
}