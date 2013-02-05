<?php
/**
 * RangeValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the attribute value should NOT be among the list of values defined via [[range]].
	 **/
 	public $not = false;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws InvalidConfigException if the "range" property is not an array
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}
		if (!is_array($this->range)) {
			throw new InvalidConfigException('The "range" property must be specified as an array.');
		}
		if (!$this->not && !in_array($value, $this->range, $this->strict)) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} should be in the list.');
			$this->addError($object, $attribute, $message);
		} elseif ($this->not && in_array($value, $this->range, $this->strict)) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} should NOT be in the list.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @throws InvalidConfigException if the "range" property is not an array
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if (!is_array($this->range)) {
			throw new InvalidConfigException('The "range" property must be specified as an array.');
		}

		if (($message = $this->message) === null) {
			$message = $this->not ? \Yii::t('yii|{attribute} should NOT be in the list.') : \Yii::t('yii|{attribute} should be in the list.');
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
