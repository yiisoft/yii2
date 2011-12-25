<?php
/**
 * Validator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * Validator is the base class for all validators.
 *
 * Child classes must override the [[validateAttribute]] method to provide the actual
 * logic of performing data validation. Child classes may also override [[clientValidateAttribute]]
 * to provide client-side validation support.
 *
 * Validator defines the following properties that are common among concrete validators:
 *
 * - [[attributes]]: array, list of attributes to be validated;
 * - [[message]]: string, the error message used when validation fails;
 * - [[on]]: string, scenarios on which the validator applies.
 *
 * Validator also declares a set of [[builtInValidators|built-in validators] which can
 * be referenced using short names. They are listed as follows:
 *
 * - `required`: [[RequiredValidator]]
 * - `filter`: [[FilterValidator]]
 * - `match`: [[RegularExpressionValidator]]
 * - `email`: [[EmailValidator]]
 * - `url`: [[UrlValidator]]
 * - `unique`: [[UniqueValidator]]
 * - `compare`: [[CompareValidator]]
 * - `in`: [[RangeValidator]]
 * - `boolean`: [[BooleanValidator]]
 * - `string`: [[StringValidator]]
 * - `integer`: [[NumberValidator]]
 * - `double`: [[NumberValidator]]
 * - `date`: [[DateValidator]]
 * - `file`: [[FileValidator]]
 * - `captcha`: [[CaptchaValidator]]
 * - `default`: [[DefaultValueValidator]]
 * - `exist`: [[ExistValidator]]
 * - `safe`: [[SafeValidator]]
 * - `unsafe`: [[UnsafeValidator]]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Validator extends \yii\base\Component
{
	/**
	 * @var array list of built-in validators (name => class or configuration)
	 */
	public static $builtInValidators = array(
		'required' => '\yii\validators\RequiredValidator',
		'match' => '\yii\validators\RegularExpressionValidator',
		'email' => '\yii\validators\EmailValidator',
		'url' => '\yii\validators\UrlValidator',
		'safe' => '\yii\validators\SafeValidator',
		'unsafe' => '\yii\validators\UnsafeValidator',
		'filter' => '\yii\validators\FilterValidator',
		'captcha' => '\yii\validators\CaptchaValidator',
		'default' => '\yii\validators\DefaultValueValidator',
		'in' => '\yii\validators\RangeValidator',
		'boolean' => '\yii\validators\BooleanValidator',
		'string' => '\yii\validators\StringValidator',
		'integer' => '\yii\validators\IntegerValidator',
		'double' => '\yii\validators\NumberValidator',
		'compare' => '\yii\validators\CompareValidator',

		'file' => '\yii\validators\FileValidator',
		'date' => '\yii\validators\DateValidator',
		'unique' => '\yii\validators\UniqueValidator',
		'exist' => '\yii\validators\ExistValidator',

	);

	/**
	 * @var array list of attributes to be validated.
	 */
	public $attributes;
	/**
	 * @var string the user-defined error message. Error message may contain some placeholders
	 * that will be replaced with the actual values by the validator.
	 * The `{attribute}` and `{value}` are placeholders supported by all validators.
	 * They will be replaced with the attribute label and value, respectively.
	 */
	public $message;
	/**
	 * @var array list of scenarios that the validator should be applied.
	 * Each array value refers to a scenario name with the same name as its array key.
	 */
	public $on;
	/**
	 * @var boolean whether this validation rule should be skipped if the attribute being validated
	 * already has some validation error according to the previous rules. Defaults to true.
	 */
	public $skipOnError = true;
	/**
	 * @var boolean whether attributes listed with this validator should be considered safe for
	 * massive assignment. Defaults to true.
	 */
	public $safe = true;
	/**
	 * @var boolean whether to enable client-side validation. Defaults to true.
	 * Please refer to [[\yii\web\ActiveForm::enableClientValidation]] for more details about
	 * client-side validation.
	 */
	public $enableClientValidation = true;

	/**
	 * Validates a single attribute.
	 * Child classes must implement this method to provide the actual validation logic.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
	abstract public function validateAttribute($object, $attribute);

	/**
	 * Creates a validator object.
	 * @param string $type the validator type. This can be a method name,
	 * a built-in validator name, a class name, or a path alias of validator class.
	 * @param \yii\base\Model $object the data object being validated.
	 * @param mixed $attributes list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the validator properties
	 * @return Validator the validator
	 */
	public static function createValidator($type, $object, $attributes, $params = array())
	{
		if (!is_array($attributes)) {
			$attributes = preg_split('/[\s,]+/', $attributes, -1, PREG_SPLIT_NO_EMPTY);
		}

		if (isset($params['on'])) {
			if (is_array($params['on'])) {
				$on = $params['on'];
			}
			else {
				$on = preg_split('/[\s,]+/', $params['on'], -1, PREG_SPLIT_NO_EMPTY);
			}
			$params['on'] = empty($on) ? array() : array_combine($on, $on);
		}
		else {
			$params['on'] = array();
		}

		if (method_exists($object, $type)) {  // method-based validator
			$config = array(
				'class'	=> '\yii\validators\InlineValidator',
				'method' => $type,
				'attributes' => $attributes,
			);
		}
		else {
			if (is_string($type) && isset(self::$builtInValidators[$type])) {
				$type = self::$builtInValidators[$type];
			}
			$config = array(
				'class'	=> $type,
				'attributes' => $attributes,
			);
		}
		foreach ($params as $name => $value) {
			$config[$name] = $value;
		}
		$validator = \Yii::createObject($config);

		return $validator;
	}

	/**
	 * Validates the specified object.
	 * @param \yii\base\Model $object the data object being validated
	 * @param array $attributes the list of attributes to be validated. Defaults to null,
	 * meaning every attribute listed in [[attributes]] will be validated.
	 */
	public function validate($object, $attributes = null)
	{
		if (is_array($attributes)) {
			$attributes = array_intersect($this->attributes, $attributes);
		}
		else {
			$attributes = $this->attributes;
		}
		foreach ($attributes as $attribute) {
			if (!$this->skipOnError || !$object->hasErrors($attribute)) {
				$this->validateAttribute($object, $attribute);
			}
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 *
	 * You may override this method to return the JavaScript validation code if
	 * the validator can support client-side validation.
	 *
	 * The following JavaScript variables are predefined and can be used in the validation code:
	 *
	 * - `attribute`: the name of the attribute being validated.
	 * - `value`: the value being validated.
	 * - `messages`: an array used to hold the validation error messages for the attribute.
	 *
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script. Null if the validator does not support
	 * client-side validation.
	 * @see enableClientValidation
	 * @see \yii\web\ActiveForm::enableClientValidation
	 */
	public function clientValidateAttribute($object, $attribute)
	{
	}

	/**
	 * Returns a value indicating whether the validator applies to the specified scenario.
	 * A validator applies to a scenario as long as any of the following conditions is met:
	 *
	 * - the validator's `on` property is empty
	 * - the validator's `on` property contains the specified scenario
	 *
	 * @param string $scenario scenario name
	 * @return boolean whether the validator applies to the specified scenario.
	 */
	public function applyTo($scenario)
	{
		return empty($this->on) || isset($this->on[$scenario]);
	}

	/**
	 * Adds an error about the specified attribute to the model object.
	 * This is a helper method that performs message selection and internationalization.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the attribute being validated
	 * @param string $message the error message
	 * @param array $params values for the placeholders in the error message
	 */
	public function addError($object, $attribute, $message, $params = array())
	{
		$params['{attribute}'] = $object->getAttributeLabel($attribute);
		$params['{value}'] = $object->$attribute;
		$object->addError($attribute, strtr($message, $params));
	}

	/**
	 * Checks if the given value is empty.
	 * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
	 * Note that this method is different from PHP empty(). It will return false when the value is 0.
	 * @param mixed $value the value to be checked
	 * @param boolean $trim whether to perform trimming before checking if the string is empty. Defaults to false.
	 * @return boolean whether the value is empty
	 */
	public function isEmpty($value, $trim = false)
	{
		return $value === null || $value === array() || $value === ''
				|| $trim && is_scalar($value) && trim($value) === '';
	}
}
