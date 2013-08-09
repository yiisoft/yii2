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
	 * @var integer|array specifies the length limit of the value to be validated.
	 * This can be specified in one of the following forms:
	 *
	 * - an integer: the exact length that the value should be of;
	 * - an array of one element: the minimum length that the value should be of. For example, `array(8)`.
	 *   This will overwrite [[min]].
	 * - an array of two elements: the minimum and maximum lengths that the value should be of.
	 *   For example, `array(8, 128)`. This will overwrite both [[min]] and [[max]].
	 */
	public $length;
	/**
	 * @var integer maximum length. If not set, it means no maximum length limit.
	 */
	public $max;
	/**
	 * @var integer minimum length. If not set, it means no minimum length limit.
	 */
	public $min;
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
		if (is_array($this->length)) {
			if (isset($this->length[0])) {
				$this->min = $this->length[0];
			}
			if (isset($this->length[1])) {
				$this->max = $this->length[1];
			}
			$this->length = null;
		}
		if ($this->encoding === null) {
			$this->encoding = Yii::$app->charset;
		}
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} must be a string.');
		}
		if ($this->min !== null && $this->tooShort === null) {
			$this->tooShort = Yii::t('yii', '{attribute} should contain at least {min} characters.');
		}
		if ($this->max !== null && $this->tooLong === null) {
			$this->tooLong = Yii::t('yii', '{attribute} should contain at most {max} characters.');
		}
		if ($this->length !== null && $this->notEqual === null) {
			$this->notEqual = Yii::t('yii', '{attribute} should contain {length} characters.');
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
			$this->addError($object, $attribute, $this->message);
			return;
		}

		$length = mb_strlen($value, $this->encoding);

		if ($this->min !== null && $length < $this->min) {
			$this->addError($object, $attribute, $this->tooShort, array('{min}' => $this->min));
		}
		if ($this->max !== null && $length > $this->max) {
			$this->addError($object, $attribute, $this->tooLong, array('{max}' => $this->max));
		}
		if ($this->length !== null && $length !== $this->length) {
			$this->addError($object, $attribute, $this->notEqual, array('{length}' => $this->length));
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
			&& ($this->length === null || $length === $this->length);
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @param \yii\base\View $view the view object that is going to be used to render views or view files
	 * containing a model form with this validator applied.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$label = $object->getAttributeLabel($attribute);
		$value = $object->$attribute;

		$options = array(
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $label,
				'{value}' => $value,
			))),
		);

		if ($this->min !== null) {
			$options['min'] = $this->min;
			$options['tooShort'] = Html::encode(strtr($this->tooShort, array(
				'{attribute}' => $label,
				'{value}' => $value,
				'{min}' => $this->min,
			)));
		}
		if ($this->max !== null) {
			$options['max'] = $this->max;
			$options['tooLong'] = Html::encode(strtr($this->tooLong, array(
				'{attribute}' => $label,
				'{value}' => $value,
				'{max}' => $this->max,
			)));
		}
		if ($this->length !== null) {
			$options['is'] = $this->length;
			$options['notEqual'] = Html::encode(strtr($this->notEqual, array(
				'{attribute}' => $label,
				'{value}' => $value,
				'{length}' => $this->is,
			)));
		}
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		ValidationAsset::register($view);
		return 'yii.validation.string(value, messages, ' . json_encode($options) . ');';
	}
}
