<?php
/**
 * CompareValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;
use Yii;
use yii\base\InvalidConfigException;

/**
 * CompareValidator compares the specified attribute value with another value and validates if they are equal.
 *
 * The value being compared with can be another attribute value
 * (specified via [[compareAttribute]]) or a constant (specified via
 * [[compareValue]]. When both are specified, the latter takes
 * precedence. If neither is specified, the attribute will be compared
 * with another attribute whose name is by appending "_repeat" to the source
 * attribute name.
 *
 * The comparison can be either [[strict]] or not.
 *
 * CompareValidator supports different comparison operators, specified
 * via the [[operator]] property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CompareValidator extends Validator
{
	/**
	 * @var string the name of the attribute to be compared with. When both this property
	 * and [[compareValue]] are set, the latter takes precedence. If neither is set,
	 * it assumes the comparison is against another attribute whose name is formed by
	 * appending '_repeat' to the attribute being validated. For example, if 'password' is
	 * being validated, then the attribute to be compared would be 'password_repeat'.
	 * @see compareValue
	 */
	public $compareAttribute;
	/**
	 * @var string the constant value to be compared with. When both this property
	 * and [[compareAttribute]] are set, this property takes precedence.
	 * @see compareAttribute
	 */
	public $compareValue;
	/**
	 * @var boolean whether the comparison is strict (both value and type must be the same.)
	 * Defaults to false.
	 */
	public $strict = false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to false.
	 * If this is true, it means the attribute is considered valid when it is empty.
	 */
	public $allowEmpty = false;
	/**
	 * @var string the operator for comparison. Defaults to '='.
	 * The followings are valid operators:
	 * 
	 * - `=` or `==`: validates to see if the two values are equal. If [[strict]] is true, the comparison
	 *   will be done in strict mode (i.e. checking value type as well).
	 * - `!=`: validates to see if the two values are NOT equal. If [[strict]] is true, the comparison
	 *   will be done in strict mode (i.e. checking value type as well).
	 * - `>`: validates to see if the value being validated is greater than the value being compared with.
	 * - `>=`: validates to see if the value being validated is greater than or equal to the value being compared with.
	 * - `<`: validates to see if the value being validated is less than the value being compared with.
	 * - `<=`: validates to see if the value being validated is less than or equal to the value being compared with.
	 */
	public $operator = '=';

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws InvalidConfigException if CompareValidator::operator is invalid
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}
		if ($this->compareValue !== null) {
			$compareLabel = $compareValue = $this->compareValue;
		} else {
			$compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
			$compareValue = $object->$compareAttribute;
			$compareLabel = $object->getAttributeLabel($compareAttribute);
		}

		switch ($this->operator) {
			case '=':
			case '==':
				if (($this->strict && $value !== $compareValue) || (!$this->strict && $value != $compareValue)) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must be repeated exactly.');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel));
				}
				break;
			case '!=':
				if (($this->strict && $value === $compareValue) || (!$this->strict && $value == $compareValue)) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must not be equal to "{compareValue}".');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel, '{compareValue}' => $compareValue));
				}
				break;
			case '>':
				if ($value <= $compareValue) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must be greater than "{compareValue}".');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel, '{compareValue}' => $compareValue));
				}
				break;
			case '>=':
				if ($value < $compareValue) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must be greater than or equal to "{compareValue}".');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel, '{compareValue}' => $compareValue));
				}
				break;
			case '<':
				if ($value >= $compareValue) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must be less than "{compareValue}".');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel, '{compareValue}' => $compareValue));
				}
				break;
			case '<=':
				if ($value > $compareValue) {
					$message = ($this->message !== null) ? $this->message : Yii::t('yii:{attribute} must be less than or equal to "{compareValue}".');
					$this->addError($object, $attribute, $message, array('{compareAttribute}' => $compareLabel, '{compareValue}' => $compareValue));
				}
				break;
			default:
				throw new InvalidConfigException("Unknown operator: {$this->operator}");
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated
	 * @return string the client-side validation script
	 * @throws InvalidConfigException if CompareValidator::operator is invalid
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if ($this->compareValue !== null) {
			$compareLabel = $this->compareValue;
			$compareValue = json_encode($this->compareValue);
		} else {
			$compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
			$compareValue = "\$('#" . (CHtml::activeId($object, $compareAttribute)) . "').val()";
			$compareLabel = $object->getAttributeLabel($compareAttribute);
		}

		$message = $this->message;
		switch ($this->operator) {
			case '=':
			case '==':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must be repeated exactly.');
				}
				$condition = 'value!=' . $compareValue;
				break;
			case '!=':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must not be equal to "{compareValue}".');
				}
				$condition = 'value==' . $compareValue;
				break;
			case '>':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must be greater than "{compareValue}".');
				}
				$condition = 'value<=' . $compareValue;
				break;
			case '>=':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must be greater than or equal to "{compareValue}".');
				}
				$condition = 'value<' . $compareValue;
				break;
			case '<':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must be less than "{compareValue}".');
				}
				$condition = 'value>=' . $compareValue;
				break;
			case '<=':
				if ($message === null) {
					$message = Yii::t('yii:{attribute} must be less than or equal to "{compareValue}".');
				}
				$condition = 'value>' . $compareValue;
				break;
			default:
				throw new InvalidConfigException("Unknown operator: {$this->operator}");
		}

		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{compareValue}' => $compareLabel,
		));

		return "
if (" . ($this->allowEmpty ? "$.trim(value)!='' && " : '') . $condition . ") {
	messages.push(" . json_encode($message) . ");
}
";
	}
}
