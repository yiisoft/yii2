<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Html;

/**
 * RequiredValidator validates that the specified attribute does not have null or empty value.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RequiredValidator extends Validator
{
	/**
	 * @var boolean whether to skip this validator if the value being validated is empty.
	 */
	public $skipOnEmpty = false;
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
	 * @var string the user-defined error message. It may contain the following placeholders which
	 * will be replaced accordingly by the validator:
	 *
	 * - `{attribute}`: the label of the attribute being validated
	 * - `{value}`: the value of the attribute being validated
	 * - `{requiredValue}`: the value of [[requiredValue]]
	 */
	public $message;

	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = $this->requiredValue === null ? Yii::t('yii|{attribute} cannot be blank.')
				: Yii::t('yii|{attribute} must be "{requiredValue}".');
		}
	}

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if (!$this->validateValue($object->$attribute)) {
			if ($this->requiredValue === null) {
				$this->addError($object, $attribute, $this->message);
			} else {
				$this->addError($object, $attribute, $this->message, array(
					'{requiredValue}' => $this->requiredValue,
				));
			}
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		if ($this->requiredValue === null) {
			if ($this->strict && $value !== null || !$this->strict && !$this->isEmpty($value, true)) {
				return true;
			}
		} elseif (!$this->strict && $value == $this->requiredValue || $this->strict && $value === $this->requiredValue) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$options = array();
		if ($this->requiredValue !== null) {
			$options['message'] = strtr($this->message, array(
				'{requiredValue}' => $this->requiredValue,
			));
			$options['requiredValue'] = $this->requiredValue;
		} else {
			$options['message'] = $this->message;
		}
		if ($this->strict) {
			$options['strict'] = 1;
		}

		$options['message'] = Html::encode(strtr($options['message'], array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		)));

		return 'yii.validation.required(value, messages, ' . json_encode($options) . ');';
	}
}
