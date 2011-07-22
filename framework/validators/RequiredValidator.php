<?php
/**
 * CRequiredValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRequiredValidator validates that the specified attribute does not have null or empty value.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CRequiredValidator.php 3157 2011-04-02 19:21:06Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CRequiredValidator extends CValidator
{
	/**
	 * @var mixed the desired value that the attribute must have.
	 * If this is null, the validator will validate that the specified attribute does not have null or empty value.
	 * If this is set as a value that is not null, the validator will validate that
	 * the attribute has a value that is the same as this property value.
	 * Defaults to null.
	 * @since 1.0.10
	 */
	public $requiredValue;
	/**
	 * @var boolean whether the comparison to {@link requiredValue} is strict.
	 * When this is true, the attribute value and type must both match those of {@link requiredValue}.
	 * Defaults to false, meaning only the value needs to be matched.
	 * This property is only used when {@link requiredValue} is not null.
	 * @since 1.0.10
	 */
	public $strict = false;
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
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
if(value!=" . CJSON::encode($this->requiredValue) . ") {
	messages.push(" . CJSON::encode($message) . ");
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
	messages.push(" . CJSON::encode($message) . ");
}
";
		}
	}
}
