<?php
/**
 * NumberValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * NumberValidator validates that the attribute value is a number.
 *
 * The format of the number must match the regular expression specified in [[pattern]].
 * Optionally, you may configure the [[max]] and [[min]] properties to ensure the number
 * is within certain range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class NumberValidator extends Validator
{
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;
	/**
	 * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
	 */
	public $max;
	/**
	 * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
	 */
	public $min;
	/**
	 * @var string user-defined error message used when the value is bigger than [[max]].
	 */
	public $tooBig;
	/**
	 * @var string user-defined error message used when the value is smaller than [[min]].
	 */
	public $tooSmall;
	/**
	 * @var string the regular expression for matching numbers. It defaults to a pattern
	 * that matches floating numbers with optional exponential part (e.g. -1.23e-10).
	 */
	public $pattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


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
		if (!preg_match($this->pattern, "$value")) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii', '{attribute} must be a number.');
			$this->addError($object, $attribute, $message);
		}
		if ($this->min !== null && $value < $this->min) {
			$message = ($this->tooSmall !== null) ? $this->tooSmall : \Yii::t('yii', '{attribute} is too small (minimum is {min}).');
			$this->addError($object, $attribute, $message, array('{min}' => $this->min));
		}
		if ($this->max !== null && $value > $this->max) {
			$message = ($this->tooBig !== null) ? $this->tooBig : \Yii::t('yii', '{attribute} is too big (maximum is {max}).');
			$this->addError($object, $attribute, $message, array('{max}' => $this->max));
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
		$label = $object->getAttributeLabel($attribute);
		$value = $object->$attribute;

		if (($message = $this->message) === null) {
			$message = \Yii::t('yii', '{attribute} must be a number.');
		}
		$message = strtr($message, array(
			'{attribute}' => $label,
			'{value}' => $value,
		));

		if (($tooBig = $this->tooBig) === null) {
			$tooBig = \Yii::t('yii', '{attribute} is too big (maximum is {max}).');
		}
		$tooBig = strtr($tooBig, array(
			'{attribute}' => $label,
			'{value}' => $value,
			'{max}' => $this->max,
		));

		if (($tooSmall = $this->tooSmall) === null) {
			$tooSmall = \Yii::t('yii', '{attribute} is too small (minimum is {min}).');
		}
		$tooSmall = strtr($tooSmall, array(
			'{attribute}' => $label,
			'{value}' => $value,
			'{min}' => $this->min,
		));

		$js = "
if(!value.match({$this->pattern})) {
	messages.push(" . json_encode($message) . ");
}
";
		if ($this->min !== null) {
			$js .= "
if(value< {$this->min}) {
	messages.push(" . json_encode($tooSmall) . ");
}
";
		}
		if ($this->max !== null) {
			$js .= "
if(value> {$this->max}) {
	messages.push(" . json_encode($tooBig) . ");
}
";
		}

		if ($this->allowEmpty) {
			$js = "
if($.trim(value)!='') {
	$js
}
";
		}

		return $js;
	}
}
