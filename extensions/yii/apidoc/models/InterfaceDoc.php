<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class InterfaceDoc extends TypeDoc
{
	public $parentInterfaces = [];

	// will be set by Context::updateReferences()
	public $implementedBy = [];

	/**
	 * @param \phpDocumentor\Reflection\InterfaceReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector = null, $config = [])
	{
		parent::__construct($reflector, $config);

		if ($reflector === null) {
			return;
		}

		foreach($reflector->getParentInterfaces() as $interface) {
			$this->parentInterfaces[] = ltrim($interface, '\\');
		}

		// interface can not have properties
		$this->properties = null;
	}
}