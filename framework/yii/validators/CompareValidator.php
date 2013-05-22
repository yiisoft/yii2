<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

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
	 * @var string the operator for comparison. The following operators are supported:
	 *
	 * - '==': validates to see if the two values are equal. The comparison is done is non-strict mode.
	 * - '===': validates to see if the two values are equal. The comparison is done is strict mode.
	 * - '!=': validates to see if the two values are NOT equal. The comparison is done is non-strict mode.
	 * - '!==': validates to see if the two values are NOT equal. The comparison is done is strict mode.
	 * - `>`: validates to see if the value being validated is greater than the value being compared with.
	 * - `>=`: validates to see if the value being validated is greater than or equal to the value being compared with.
	 * - `<`: validates to see if the value being validated is less than the value being compared with.
	 * - `<=`: validates to see if the value being validated is less than or equal to the value being compared with.
	 */
	public $operator = '=';
	/**
	 * @var string the user-defined error message. It may contain the following placeholders which
	 * will be replaced accordingly by the validator:
	 *
	 * - `{attribute}`: the label of the attribute being validated
	 * - `{value}`: the value of the attribute being validated
	 * - `{compareValue}`: the value or the attribute label to be compared with
	 */
	public $message;


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			switch ($this->operator) {
				case '==':
					$this->message = Yii::t('yii', '{attribute} must be repeated exactly.');
					break;
				case '===':
					$this->message = Yii::t('yii', '{attribute} must be repeated exactly.');
					break;
				case '!=':
					$this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValue}".');
					break;
				case '!==':
					$this->message = Yii::t('yii', '{attribute} must not be equal to "{compareValue}".');
					break;
				case '>':
					$this->message = Yii::t('yii', '{attribute} must be greater than "{compareValue}".');
					break;
				case '>=':
					$this->message = Yii::t('yii', '{attribute} must be greater than or equal to "{compareValue}".');
					break;
				case '<':
					$this->message = Yii::t('yii', '{attribute} must be less than "{compareValue}".');
					break;
				case '<=':
					$this->message = Yii::t('yii', '{attribute} must be less than or equal to "{compareValue}".');
					break;
				default:
					throw new InvalidConfigException("Unknown operator: {$this->operator}");
			}
		}
	}

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
		if (is_array($value)) {
			$this->addError($object, $attribute, Yii::t('yii', '{attribute} is invalid.'));
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
			case '==': $valid = $value == $compareValue; break;
			case '===': $valid = $value === $compareValue; break;
			case '!=': $valid = $value != $compareValue; break;
			case '!==': $valid = $value !== $compareValue; break;
			case '>': $valid = $value > $compareValue; break;
			case '>=': $valid = $value >= $compareValue; break;
			case '<': $valid = $value < $compareValue; break;
			case '<=': $valid = $value <= $compareValue; break;
			default: $valid = false; break;
		}
		if (!$valid) {
			$this->addError($object, $attribute, $this->message, array(
				'{compareAttribute}' => $compareLabel,
				'{compareValue}' => $compareValue,
			));
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 * @throws InvalidConfigException if [[compareValue]] is not set.
	 */
	public function validateValue($value)
	{
		if ($this->compareValue === null) {
			throw new InvalidConfigException('CompareValidator::compareValue must be set.');
		}

		switch ($this->operator) {
			case '==': return $value == $this->compareValue;
			case '===': return $value === $this->compareValue;
			case '!=': return $value != $this->compareValue;
			case '!==': return $value !== $this->compareValue;
			case '>': return $value > $this->compareValue;
			case '>=': return $value >= $this->compareValue;
			case '<': return $value < $this->compareValue;
			case '<=': return $value <= $this->compareValue;
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated
	 * @return string the client-side validation script
	 * @param \yii\base\View $view the view object that is going to be used to render views or view files
	 * containing a model form with this validator applied.
	 * @throws InvalidConfigException if CompareValidator::operator is invalid
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$options = array('operator' => $this->operator);

		if ($this->compareValue !== null) {
			$options['compareValue'] = $this->compareValue;
			$compareValue = $this->compareValue;
		} else {
			$compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
			$compareValue = $object->getAttributeLabel($compareAttribute);
			$options['compareAttribute'] = Html::getInputId($object, $compareAttribute);
		}

		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		$options['message'] = Html::encode(strtr($this->message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
			'{compareValue}' => $compareValue,
		)));

		$view->registerAssetBundle('yii/validation');
		return 'yii.validation.compare(value, messages, ' . json_encode($options) . ');';
	}
}
