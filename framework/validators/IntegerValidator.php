<?php
/**
 * IntegerValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * IntegerValidator validates that the attribute value is an integer.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class IntegerValidator extends NumberValidator
{
	/**
	 * @var string the regular expression for matching integers.
	 */
	public $pattern = '/^\s*[+-]?\d+\s*$/';

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if ($this->message === null) {
			$this->message = \Yii::t('yii', '{attribute} must be an integer.');
		}
		parent::validateAttribute($object, $attribute);
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if ($this->message === null) {
			$this->message = \Yii::t('yii', '{attribute} must be an integer.');
		}
		return parent::clientValidateAttribute($object, $attribute);
	}
}
