<?php
/**
 * CDefaultValueValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CDefaultValueValidator sets the attributes with the specified value.
 * It does not do validation. Its existence is mainly to allow
 * specifying attribute default values in a dynamic way.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDefaultValueValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.0.2
 */
class CDefaultValueValidator extends Validator
{
	/**
	 * @var mixed the default value to be set to the specified attributes.
	 */
	public $value;
	/**
	 * @var boolean whether to set the default value only when the attribute value is null or empty string.
	 * Defaults to true. If false, the attribute will always be assigned with the default value,
	 * even if it is already explicitly assigned a value.
	 */
	public $setOnEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if (!$this->setOnEmpty)
			$object->$attribute = $this->value;
		else
		{
			$value = $object->$attribute;
			if ($value === null || $value === '')
				$object->$attribute = $this->value;
		}
	}
}

