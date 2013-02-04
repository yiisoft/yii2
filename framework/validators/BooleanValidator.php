<?php
/**
 * BooleanValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * BooleanValidator checks if the attribute value is a boolean value.
 *
 * Possible boolean values can be configured via the [[trueValue]] and [[falseValue]] properties.
 * And the comparison can be either [[strict]] or not.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BooleanValidator extends Validator
{
	/**
	 * @var mixed the value representing true status. Defaults to '1'.
	 */
	public $trueValue = '1';
	/**
	 * @var mixed the value representing false status. Defaults to '0'.
	 */
	public $falseValue = '0';
	/**
	 * @var boolean whether the comparison to [[trueValue]] and [[falseValue]] is strict.
	 * When this is true, the attribute value and type must both match those of [[trueValue]] or [[falseValue]].
	 * Defaults to false, meaning only the value needs to be matched.
	 */
	public $strict = false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}
		if (!$this->strict && $value != $this->trueValue && $value != $this->falseValue
				|| $this->strict && $value !== $this->trueValue && $value !== $this->falseValue) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii:{attribute} must be either {true} or {false}.');
			$this->addError($object, $attribute, $message, array(
				'{true}' => $this->trueValue,
				'{false}' => $this->falseValue,
			));
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$message = ($this->message !== null) ? $this->message : \Yii::t('yii:{attribute} must be either {true} or {false}.');
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
			'{true}' => $this->trueValue,
			'{false}' => $this->falseValue,
		));
		return "
if(" . ($this->allowEmpty ? "$.trim(value)!='' && " : '') . "value!=" . json_encode($this->trueValue) . " && value!=" . json_encode($this->falseValue) . ") {
	messages.push(" . json_encode($message) . ");
}
";
	}
}
