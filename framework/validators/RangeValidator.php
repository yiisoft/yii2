<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;
use yii\base\InvalidConfigException;

/**
 * RangeValidator validates that the attribute value is among a list of values.
 *
 * The range can be specified via the [[range]] property.
 * If the [[not]] property is set true, the validator will ensure the attribute value
 * is NOT among the specified range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RangeValidator extends Validator
{
	/**
	 * @var array list of valid values that the attribute value should be among
	 */
	public $range;
	/**
	 * @var boolean whether the comparison is strict (both type and value must be the same)
	 */
	public $strict = false;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via [[range]].
	 **/
 	public $not = false;

	/**
	 * Initializes the validator.
	 * @throws InvalidConfigException if [[range]] is not set.
	 */
	public function init()
	{
		parent::init();
		if (!is_array($this->range)) {
			throw new InvalidConfigException('The "range" property must be set.');
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
		$value = $object->$attribute;
		$message = $this->message !== null ? $this->message : \Yii::t('yii|{attribute} is invalid.');
		if (!$this->not && !in_array($value, $this->range, $this->strict)) {
			$this->addError($object, $attribute, $message);
		} elseif ($this->not && in_array($value, $this->range, $this->strict)) {
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		return !$this->not && in_array($value, $this->range, $this->strict)
			|| $this->not && !in_array($value, $this->range, $this->strict);
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if (($message = $this->message) === null) {
			$message = \Yii::t('yii|{attribute} is invalid.');
		}
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		));

		$range = array();
		foreach ($this->range as $value) {
			$range[] = (string)$value;
		}
		$range = json_encode($range);

		return "
if (" . ($this->allowEmpty ? "$.trim(value)!='' && " : '') . ($this->not ? "$.inArray(value, $range)>=0" : "$.inArray(value, $range)<0") . ") {
	messages.push(" . json_encode($message) . ");
}
";
	}
}
