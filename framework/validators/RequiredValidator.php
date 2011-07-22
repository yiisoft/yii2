<?php
/**
 * RequiredValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * RequiredValidator validates that the specified attribute does not have null or empty value.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequiredValidator extends Validator
{
	/**
	 * @var mixed the desired value that the attribute must have.
	 * If this is null, the validator will validate that the specified attribute is not empty.
	 * If this is set as a value that is not null, the validator will validate that
	 * the attribute has a value that is the same as this property value.
	 * Defaults to null.
	 * @see strict
	 */
	public $requiredValue;
	/**
	 * @var boolean whether the comparison between the attribute value and [[requiredValue]] is strict.
	 * When this is true, both the values and types must match.
	 * Defaults to false, meaning only the values need to match.
	 * Note that when [[requiredValue]] is null, if this property is true, the validator will check
	 * if the attribute value is null; If this property is false, the validator will call [[isEmpty]]
	 * to check if the attribute value is empty.
	 */
	public $strict = false;

	/**
	 * Validates a value.
	 * @param mixed $value the value being validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		if ($this->requiredValue !== null) {
			if (!$this->strict && $value != $this->requiredValue || $this->strict && $value !== $this->requiredValue)
			{
				$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} must be {value}.',
					array('{value}' => $this->requiredValue));
				$this->addError($object, $attribute, $message);
			}
		}
		elseif ($this->isEmpty($value, true)) {
			$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} cannot be blank.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->requiredValue !== null)
		{
			if (!$this->strict && $value != $this->requiredValue || $this->strict && $value !== $this->requiredValue)
			{
				$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} must be {value}.',
					array('{value}' => $this->requiredValue));
				$this->addError($object, $attribute, $message);
			}
		}
		elseif ($this->isEmpty($value, true))
		{
			$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} cannot be blank.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$message = $this->message;
		if ($this->requiredValue !== null)
		{
			if ($message === null)
				$message = Yii::t('yii', '{attribute} must be {value}.');
			$message = strtr($message, array(
				'{value}' => $this->requiredValue,
				'{attribute}' => $object->getAttributeLabel($attribute),
			));
			return "
if(value!=" . json_encode($this->requiredValue) . ") {
	messages.push(" . json_encode($message) . ");
}
";
		}
		else
		{
			if ($message === null)
				$message = Yii::t('yii', '{attribute} cannot be blank.');
			$message = strtr($message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
			));
			return "
if($.trim(value)=='') {
	messages.push(" . json_encode($message) . ");
}
";
		}
	}
}
