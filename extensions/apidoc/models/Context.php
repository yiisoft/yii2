<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\FileReflector;
use yii\base\Component;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
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

	public $errors = [];


	public function getType($type)
	{
		$type = ltrim($type, '\\');
		if (isset($this->classes[$type])) {
			return $this->classes[$type];
		} elseif (isset($this->interfaces[$type])) {
			return $this->interfaces[$type];
		} elseif (isset($this->traits[$type])) {
			return $this->traits[$type];
		}
		return null;
	}

	public function addFile($fileName)
	{
		$this->files[$fileName] = sha1_file($fileName);

		$reflection = new FileReflector($fileName, true);
		$reflection->process();

		foreach ($reflection->getClasses() as $class) {
			$class = new ClassDoc($class, $this, ['sourceFile' => $fileName]);
			$this->classes[$class->name] = $class;
		}
		foreach ($reflection->getInterfaces() as $interface) {
			$interface = new InterfaceDoc($interface, $this, ['sourceFile' => $fileName]);
			$this->interfaces[$interface->name] = $interface;
		}
		foreach ($reflection->getTraits() as $trait) {
			$trait = new TraitDoc($trait, $this, ['sourceFile' => $fileName]);
			$this->traits[$trait->name] = $trait;
		}
	}

	public function updateReferences()
	{
		// update all subclass references
		foreach ($this->classes as $class) {
			$className = $class->name;
			while (isset($this->classes[$class->parentClass])) {
				$class = $this->classes[$class->parentClass];
				$class->subclasses[] = $className;
			}
		}
		// update interfaces of subclasses
		foreach ($this->classes as $class) {
			$this->updateSubclassInferfacesTraits($class);
		}
		// update implementedBy and usedBy for interfaces and traits
		foreach ($this->classes as $class) {
			foreach ($class->traits as $trait) {
				if (isset($this->traits[$trait])) {
					$trait = $this->traits[$trait];
					$trait->usedBy[] = $class->name;
					$class->properties = array_merge($trait->properties, $class->properties);
					$class->methods = array_merge($trait->methods, $class->methods);
				}
			}
			foreach ($class->interfaces as $interface) {
				if (isset($this->interfaces[$interface])) {
					$this->interfaces[$interface]->implementedBy[] = $class->name;
					if ($class->isAbstract) {
						// add not implemented interface methods
						foreach ($this->interfaces[$interface]->methods as $method) {
							if (!isset($class->methods[$method->name])) {
								$class->methods[$method->name] = $method;
							}
						}
					}
				}
			}
		}
		// inherit properties, methods, contants and events to subclasses
		foreach ($this->classes as $class) {
			$this->updateSubclassInheritance($class);
		}
		// add properties from getters and setters
		foreach ($this->classes as $class) {
			$this->handlePropertyFeature($class);
		}

		// TODO reference exceptions to methods where they are thrown
	}

	/**
	 * Add implemented interfaces and used traits to subclasses
	 * @param ClassDoc $class
	 */
	protected function updateSubclassInferfacesTraits($class)
	{
		foreach ($class->subclasses as $subclass) {
			$subclass = $this->classes[$subclass];
			$subclass->interfaces = array_unique(array_merge($subclass->interfaces, $class->interfaces));
			$subclass->traits = array_unique(array_merge($subclass->traits, $class->traits));
			$this->updateSubclassInferfacesTraits($subclass);
		}
	}

	/**
	 * Add implemented interfaces and used traits to subclasses
	 * @param ClassDoc $class
	 */
	protected function updateSubclassInheritance($class)
	{
		foreach ($class->subclasses as $subclass) {
			$subclass = $this->classes[$subclass];
			$subclass->events = array_merge($class->events, $subclass->events);
			$subclass->constants = array_merge($class->constants, $subclass->constants);
			$subclass->properties = array_merge($class->properties, $subclass->properties);
			$subclass->methods = array_merge($class->methods, $subclass->methods);
			$this->updateSubclassInheritance($subclass);
		}
	}

	/**
	 * Add properties for getters and setters if class is subclass of [[\yii\base\Object]].
	 * @param ClassDoc $class
	 */
	protected function handlePropertyFeature($class)
	{
		if (!$this->isSubclassOf($class, 'yii\base\Object')) {
			return;
		}
		foreach ($class->getPublicMethods() as $name => $method) {
			if ($method->isStatic) {
				continue;
			}
			if (!strncmp($name, 'get', 3) && $this->paramsOptional($method)) {
				$propertyName = '$' . lcfirst(substr($method->name, 3));
				if (isset($class->properties[$propertyName])) {
					$property = $class->properties[$propertyName];
					if ($property->getter === null && $property->setter === null) {
						$this->errors[] = [
							'line' => $property->startLine,
							'file' => $class->sourceFile,
							'message' => "Property $propertyName conflicts with a defined getter {$method->name} in {$class->name}.",
						];
					}
					$property->getter = $method;
				} else {
					$class->properties[$propertyName] = new PropertyDoc(null, $this, [
						'name' => $propertyName,
						'definedBy' => $class->name,
						'sourceFile' => $class->sourceFile,
						'visibility' => 'public',
						'isStatic' => false,
						'type' => $method->returnType,
						'types' => $method->returnTypes,
						'shortDescription' => (($pos = strpos($method->return, '.')) !== false) ?
								substr($method->return, 0, $pos) : $method->return,
						'description' => $method->return,
						'getter' => $method
						// TODO set default value
					]);
				}
			}
			if (!strncmp($name, 'set', 3) && $this->paramsOptional($method, 1)) {
				$propertyName = '$' . lcfirst(substr($method->name, 3));
				if (isset($class->properties[$propertyName])) {
					$property = $class->properties[$propertyName];
					if ($property->getter === null && $property->setter === null) {
						$this->errors[] = [
							'line' => $property->startLine,
							'file' => $class->sourceFile,
							'message' => "Property $propertyName conflicts with a defined setter {$method->name} in {$class->name}.",
						];
					}
					$property->setter = $method;
				} else {
					$param = $this->getFirstNotOptionalParameter($method);
					$class->properties[$propertyName] = new PropertyDoc(null, $this, [
						'name' => $propertyName,
						'definedBy' => $class->name,
						'sourceFile' => $class->sourceFile,
						'visibility' => 'public',
						'isStatic' => false,
						'type' => $param->type,
						'types' => $param->types,
						'shortDescription' => (($pos = strpos($param->description, '.')) !== false) ?
								substr($param->description, 0, $pos) : $param->description,
						'description' => $param->description,
						'setter' => $method
					]);
				}
			}
		}
	}

	/**
	 * @param MethodDoc $method
	 * @param integer $number number of not optional parameters
	 * @return bool
	 */
	private function paramsOptional($method, $number = 0)
	{
		foreach ($method->params as $param) {
			if (!$param->isOptional && $number-- <= 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param MethodDoc $method
	 * @return ParamDoc
	 */
	private function getFirstNotOptionalParameter($method)
	{
		foreach ($method->params as $param) {
			if (!$param->isOptional) {
				return $param;
			}
		}
		return null;
	}

	/**
	 * @param ClassDoc $classA
	 * @param ClassDoc $classB
	 */
	protected function isSubclassOf($classA, $classB)
	{
		if (is_object($classB)) {
			$classB = $classB->name;
		}
		if ($classA->name == $classB) {
			return true;
		}
		while ($classA->parentClass !== null && isset($this->classes[$classA->parentClass])) {
			$classA = $this->classes[$classA->parentClass];
			if ($classA->name == $classB) {
				return true;
			}
		}
		return false;
	}
}