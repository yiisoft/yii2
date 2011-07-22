<?php
/**
 * InlineValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * InlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * The validation method must have the following signature:
 *
 * ~~~
 * function foo($attribute, $params)
 * ~~~
 *
 * where `$attribute` refers to the name of the attribute being validated, while `$params`
 * is an array representing the additional parameters supplied in the validation rule.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineValidator extends Validator
{
	/**
	 * @var string the name of the validation method defined in the active record class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the validation method
	 */
	public $params;

	/**
	 * Validates the attribute of the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$method = $this->method;
		$object->$method($attribute, $this->params);
	}
}
