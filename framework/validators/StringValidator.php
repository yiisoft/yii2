<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;

/**
 * StringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StringValidator extends Validator
{
	/**
	 * @var integer maximum length. Defaults to null, meaning no maximum limit.
	 */
	public $max;
	/**
	 * @var integer minimum length. Defaults to null, meaning no minimum limit.
	 */
	public $min;
	/**
	 * @var integer exact length. Defaults to null, meaning no exact length limit.
	 */
	public $is;
	/**
	 * @var string user-defined error message used when the value is not a string
	 */
	public $message;
	/**
	 * @var string user-defined error message used when the length of the value is smaller than [[min]].
	 */
	public $tooShort;
	/**
	 * @var string user-defined error message used when the length of the value is greater than [[max]].
	 */
	public $tooLong;
	/**
	 * @var string user-defined error message used when the length of the value is not equal to [[is]].
	 */
	public $notEqual;
	/**
	 * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
	 * If this property is not set, [[\yii\base\Application::charset]] will be used.
	 */
	public $encoding;


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->encoding === null) {
			$this->encoding = Yii::$app->charset;
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

		if (!is_string($value)) {
			$message = ($this->message !== null) ? $this->message : Yii::t('yii|{attribute} must be a string.');
			$this->addError($object, $attribute, $message);
			return;
		}

		$length = mb_strlen($value, $this->encoding);

		if ($this->min !== null && $length < $this->min) {
			$message = ($this->tooShort !== null) ? $this->tooShort : Yii::t('yii|{attribute} should contain at least {min} characters.');
			$this->addError($object, $attribute, $message, array('{min}' => $this->min));
		}
		if ($this->max !== null && $length > $this->max) {
			$message = ($this->tooLong !== null) ? $this->tooLong : Yii::t('yii|{attribute} should contain at most {max} characters.');
			$this->addError($object, $attribute, $message, array('{max}' => $this->max));
		}
		if ($this->is !== null && $length !== $this->is) {
			$message = ($this->notEqual !== null) ? $this->notEqual : Yii::t('yii|{attribute} should contain {length} characters.');
			$this->addError($object, $attribute, $message, array('{length}' => $this->is));
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		if (!is_string($value)) {
			return false;
		}
		$length = mb_strlen($value, $this->encoding);
		return ($this->min === null || $length >= $this->min)
			&& ($this->max === null || $length <= $this->max)
			&& ($this->is === null || $length === $this->is);
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

		if (($notEqual = $this->notEqual) === null) {
			$notEqual = Yii::t('yii|{attribute} should contain {length} characters.');
		}
		$notEqual = strtr($notEqual, array(
			'{attribute}' => $label,
			'{value}' => $value,
			'{length}' => $this->is,
		));

		if (($tooShort = $this->tooShort) === null) {
			$tooShort = Yii::t('yii|{attribute} should contain at least {min} characters.');
		}
		$tooShort = strtr($tooShort, array(
			'{attribute}' => $label,
			'{value}' => $value,
			'{min}' => $this->min,
		));

		if (($tooLong = $this->tooLong) === null) {
			$tooLong = Yii::t('yii|{attribute} should contain at most {max} characters.');
		}
		$tooLong = strtr($tooLong, array(
			'{attribute}' => $label,
			'{value}' => $value,
			'{max}' => $this->max,
		));

		$js = '';
		if ($this->min !== null) {
			$js .= "
if(value.length< {$this->min}) {
	messages.push(" . json_encode($tooShort) . ");
}
";
		}
		if ($this->max !== null) {
			$js .= "
if(value.length> {$this->max}) {
	messages.push(" . json_encode($tooLong) . ");
}
";
		}
		if ($this->is !== null) {
			$js .= "
if(value.length!= {$this->is}) {
	messages.push(" . json_encode($notEqual) . ");
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

