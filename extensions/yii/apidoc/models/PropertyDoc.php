<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\VarTag;

class PropertyDoc extends BaseDoc
{
	public $visibility;
	public $isStatic;

	public $type;
	public $types;
	public $defaultValue;

	// will be set by creating class
	public $getter;
	public $setter;

	// will be set by creating class
	public $definedBy;

	public function getIsReadOnly()
	{
		return $this->getter !== null && $this->setter === null;
	}

	public function getIsWriteOnly()
	{
		return $this->getter === null && $this->setter !== null;
	}

	/**
	 * @param \phpDocumentor\Reflection\ClassReflector\PropertyReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector = null, $config = [])
	{
		parent::__construct($reflector, $config);

		if ($reflector === null) {
			return;
		}

		$this->visibility = $reflector->getVisibility();
		$this->isStatic = $reflector->isStatic();

		$this->defaultValue = $reflector->getDefault();

		foreach($this->tags as $i => $tag) {
			if ($tag instanceof VarTag) {
				$this->type = $tag->getType();
				$this->types = $tag->getTypes();
				$this->description = ucfirst($tag->getDescription());
				if (($pos = strpos($this->description, '.')) !== false) {
					$this->shortDescription = substr($this->description, 0, $pos);
				} else {
					$this->shortDescription = $this->description;
				}
			}
		}
	}
}