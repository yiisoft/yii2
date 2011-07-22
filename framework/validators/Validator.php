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
 * Child classes must implement the {@link validateAttribute} method.
 *
 * The following properties are defined in CValidator:
 * <ul>
 * <li>{@link attributes}: array, list of attributes to be validated;</li>
 * <li>{@link message}: string, the customized error message. The message
 *   may contain placeholders that will be replaced with the actual content.
 *   For example, the "{attribute}" placeholder will be replaced with the label
 *   of the problematic attribute. Different validators may define additional
 *   placeholders.</li>
 * <li>{@link on}: string, in which scenario should the validator be in effect.
 *   This is used to match the 'on' parameter supplied when calling {@link CModel::validate}.</li>
 * </ul>
 *
 * When using {@link createValidator} to create a validator, the following aliases
 * are recognized as the corresponding built-in validator classes:
 * <ul>
 * <li>required: {@link CRequiredValidator}</li>
 * <li>filter: {@link CFilterValidator}</li>
 * <li>match: {@link CRegularExpressionValidator}</li>
 * <li>email: {@link CEmailValidator}</li>
 * <li>url: {@link CUrlValidator}</li>
 * <li>unique: {@link CUniqueValidator}</li>
 * <li>compare: {@link CCompareValidator}</li>
 * <li>length: {@link CStringValidator}</li>
 * <li>in: {@link CRangeValidator}</li>
 * <li>numerical: {@link CNumberValidator}</li>
 * <li>captcha: {@link CCaptchaValidator}</li>
 * <li>type: {@link CTypeValidator}</li>
 * <li>file: {@link CFileValidator}</li>
 * <li>default: {@link CDefaultValueValidator}</li>
 * <li>exist: {@link CExistValidator}</li>
 * <li>boolean: {@link CBooleanValidator}</li>
 * <li>date: {@link CDateValidator}</li>
 * <li>safe: {@link CSafeValidator}</li>
 * <li>unsafe: {@link CUnsafeValidator}</li>
 * </ul>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CValidator.php 3160 2011-04-03 01:08:23Z qiang.xue $
 * @package system.validators
 * @since 2.0
 */
abstract class Validator extends \yii\base\Component
{
	/**
	 * @var array list of built-in validators (name => class or configuration)
	 */
	public static $builtInValidators = array(
		'required' => '\yii\validators\RequiredValidator',
		'filter' => '\yii\validators\FilterValidator',
		'match' => '\yii\validators\RegularExpressionValidator',
		'email' => '\yii\validators\EmailValidator',
		'url' => '\yii\validators\UrlValidator',
		'compare' => '\yii\validators\CompareValidator',
		'length' => '\yii\validators\StringValidator',
		'in' => '\yii\validators\RangeValidator',
		'numerical' => '\yii\validators\NumberValidator',

		'boolean' => '\yii\validators\BooleanValidator',
		'integer' => '\yii\validators\IntegerValidator',
		'float' => '\yii\validators\FloatValidator',
		'string' => '\yii\validators\StringValidator',
		'date' => '\yii\validators\DateValidator',

		'captcha' => '\yii\validators\CaptchaValidator',
		'type' => '\yii\validators\TypeValidator',
		'file' => '\yii\validators\FileValidator',
		'default' => '\yii\validators\DefaultValueValidator',

		'unique' => '\yii\validators\UniqueValidator',
		'exist' => '\yii\validators\ExistValidator',

		'safe' => '\yii\validators\SafeValidator',
		'unsafe' => '\yii\validators\UnsafeValidator',
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
	 * already has some validation error according to the previous rules. Defaults to false.
	 */
	public $skipOnError = false;
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
	 * Validates a value.
	 * Child classes should override this method to implement the actual validation logic.
	 * @param mixed $value the value being validated.
	 * @return boolean whether the value is valid.
	 */
	abstract public function validateValue($value);

	/**
	 * Validates a single attribute.
	 * The default implementation will call [[validateValue]] to determine if
	 * the attribute value is valid or not. If not, the [[message|error message]]
	 * will be added to the model object.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
	public function validateAttribute($object, $attribute)
	{
		if (!$this->validateValue($object->$attribute)) {
			$this->addError($object, $attribute, $this->message);
		}
	}

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
		$validator = \Yii::createComponent($config);
		foreach ($params as $name => $value) {
			$validator->$name = $value;
		}

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
	protected function addError($object, $attribute, $message, $params = array())
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
	protected function isEmpty($value, $trim = false)
	{
		return $value === null || $value === array() || $value === ''
				|| $trim && is_scalar($value) && trim($value) === '';
	}
}
