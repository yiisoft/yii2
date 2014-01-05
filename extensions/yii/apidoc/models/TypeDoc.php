<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\AuthorTag;
use yii\base\Exception;
use yii\helpers\StringHelper;

class TypeDoc extends BaseDoc
{
	public $authors = [];
	/**
	 * @var MethodDoc[]
	 */
	public $methods = [];
	/**
	 * @var PropertyDoc[]
	 */
	public $properties = [];

	public $namespace;

	/**
	 * @return MethodDoc[]
	 */
	public function getNativeMethods()
	{
		return $this->getFilteredMethods(null, $this->name);
	}

	/**
	 * @return MethodDoc[]
	 */
	public function getPublicMethods()
	{
		return $this->getFilteredMethods('public');
	}

	/**
	 * @return MethodDoc[]
	 */
	public function getProtectedMethods()
	{
		return $this->getFilteredMethods('protected');
	}

	/**
	 * @param null $visibility
	 * @param null $definedBy
	 * @return MethodDoc[]
	 */
	private function getFilteredMethods($visibility = null, $definedBy = null)
	{
		$methods = [];
		foreach($this->methods as $name => $method) {
			if ($visibility !== null && $method->visibility != $visibility) {
				continue;
			}
			if ($definedBy !== null && $method->definedBy != $definedBy) {
				continue;
			}
			$methods[$name] = $method;
		}
		return $methods;
	}

	/**
	 * @return PropertyDoc[]
	 */
	public function getNativeProperties()
	{
		return $this->getFilteredProperties(null, $this->name);
	}

	/**
	 * @return PropertyDoc[]
	 */
	public function getPublicProperties()
	{
		return $this->getFilteredProperties('public');
	}

	/**
	 * @return PropertyDoc[]
	 */
	public function getProtectedProperties()
	{
		return $this->getFilteredProperties('protected');
	}

	/**
	 * @param null $visibility
	 * @param null $definedBy
	 * @return PropertyDoc[]
	 */
	private function getFilteredProperties($visibility = null, $definedBy = null)
	{
		if ($this->properties === null) {
			return [];
		}
		$properties = [];
		foreach($this->properties as $name => $property) {
			if ($visibility !== null && $property->visibility != $visibility) {
				continue;
			}
			if ($definedBy !== null && $property->definedBy != $definedBy) {
				continue;
			}
			$properties[$name] = $property;
		}
		return $properties;
	}

	/**
	 * @param \phpDocumentor\Reflection\InterfaceReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector = null, $config = [])
	{
		parent::__construct($reflector, $config);

		$this->namespace = StringHelper::basename($this->name);

		if ($reflector === null) {
			return;
		}

		foreach($this->tags as $i => $tag) {
			if ($tag instanceof AuthorTag) {
				$this->authors[$tag->getAuthorName()] = $tag->getAuthorEmail();
				unset($this->tags[$i]);
			}
		}

		foreach($reflector->getProperties() as $propertyReflector) {
			if ($propertyReflector->getVisibility() != 'private') {
				$property = new PropertyDoc($propertyReflector);
				$property->definedBy = $this->name;
				$this->properties[$property->name] = $property;
			}
		}

		foreach($reflector->getMethods() as $methodReflector) {
			if ($methodReflector->getVisibility() != 'private') {
				$method = new MethodDoc($methodReflector);
				$method->definedBy = $this->name;

				// TODO only set property when subclass of Object
				if (!strncmp($method->name, 'set', 3)) {
					$propertyName = lcfirst(substr($method->name, 3));
					if (isset($this->properties[$propertyName])) {
						$property = $this->properties[$propertyName];
						if ($property->getter === null && $property->setter === null) {
							throw new Exception("Property $propertyName conflicts with a defined setter {$method->name}.");
						}
						$property->setter = $method;
					} else {
//						$this->properties[$propertyName] = new PropertyDoc(null, [
//							'name' => $propertyName,
//							// TODO set description and short description
//						]);
					}
				} elseif (!strncmp($method->name, 'get', 3)) {
					// TODO add property
				}
				$this->methods[$method->name] = $method;
			}
		}
	}
}