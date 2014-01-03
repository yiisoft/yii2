<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * Validator is the base class for all validators.
 *
 * Child classes should override the [[validateValue()]] and/or [[validateAttribute()]] methods to provide the actual
 * logic of performing data validation. Child classes may also override [[clientValidateAttribute()]]
 * to provide client-side validation support.
 *
 * Validator declares a set of [[builtInValidators|built-in validators] which can
 * be referenced using short names. They are listed as follows:
 *
 * - `boolean`: [[BooleanValidator]]
 * - `captcha`: [[CaptchaValidator]]
 * - `compare`: [[CompareValidator]]
 * - `date`: [[DateValidator]]
 * - `default`: [[DefaultValueValidator]]
 * - `double`: [[NumberValidator]]
 * - `email`: [[EmailValidator]]
 * - `exist`: [[ExistValidator]]
 * - `file`: [[FileValidator]]
 * - `filter`: [[FilterValidator]]
 * - `image`: [[ImageValidator]]
 * - `in`: [[RangeValidator]]
 * - `integer`: [[NumberValidator]]
 * - `match`: [[RegularExpressionValidator]]
 * - `required`: [[RequiredValidator]]
 * - `safe`: [[SafeValidator]]
 * - `string`: [[StringValidator]]
 * - `trim`: [[FilterValidator]]
 * - `unique`: [[UniqueValidator]]
 * - `url`: [[UrlValidator]]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Validator extends Component
{
	/**
	 * @var array list of built-in validators (name => class or configuration)
	 */
	public static $builtInValidators = [
		'boolean' => 'yii\validators\BooleanValidator',
		'captcha' => 'yii\captcha\CaptchaValidator',
		'compare' => 'yii\validators\CompareValidator',
		'date' => 'yii\validators\DateValidator',
		'default' => 'yii\validators\DefaultValueValidator',
		'double' => 'yii\validators\NumberValidator',
		'email' => 'yii\validators\EmailValidator',
		'exist' => 'yii\validators\ExistValidator',
		'file' => 'yii\validators\FileValidator',
		'filter' => 'yii\validators\FilterValidator',
		'image' => 'yii\validators\ImageValidator',
		'in' => 'yii\validators\RangeValidator',
		'integer' => [
			'class' => 'yii\validators\NumberValidator',
			'integerOnly' => true,
		],
		'match' => 'yii\validators\RegularExpressionValidator',
		'number' => 'yii\validators\NumberValidator',
		'required' => 'yii\validators\RequiredValidator',
		'safe' => 'yii\validators\SafeValidator',
		'string' => 'yii\validators\StringValidator',
		'trim' => [
			'class' => 'yii\validators\FilterValidator',
			'filter' => 'trim',
		],
		'unique' => 'yii\validators\UniqueValidator',
		'url' => 'yii\validators\UrlValidator',
	];

	/**
	 * @var array|string attributes to be validated by this validator. For multiple attributes,
	 * please specify them as an array; for single attribute, you may use either a string or an array.
	 */
	public $attributes = [];
	/**
	 * @var string the user-defined error message. It may contain the following placeholders which
	 * will be replaced accordingly by the validator:
	 *
	 * - `{attribute}`: the label of the attribute being validated
	 * - `{value}`: the value of the attribute being validated
	 */
	public $message;
	/**
	 * @var array|string scenarios that the validator can be applied to. For multiple scenarios,
	 * please specify them as an array; for single scenario, you may use either a string or an array.
	 */
	public $on = [];
	/**
	 * @var array|string scenarios that the validator should not be applied to. For multiple scenarios,
	 * please specify them as an array; for single scenario, you may use either a string or an array.
	 */
	public $except = [];
	/**
	 * @var boolean whether this validation rule should be skipped if the attribute being validated
	 * already has some validation error according to some previous rules. Defaults to true.
	 */
	public $skipOnError = true;
	/**
	 * @var boolean whether this validation rule should be skipped if the attribute value
	 * is null or an empty string.
	 */
	public $skipOnEmpty = true;
	/**
	 * @var boolean whether to enable client-side validation for this validator.
	 * The actual client-side validation is done via the JavaScript code returned
	 * by [[clientValidateAttribute()]]. If that method returns null, even if this property
	 * is true, no client-side validation will be done by this validator.
	 */
	public $enableClientValidation = true;


	/**
	 * Creates a validator object.
	 * @param string $type the validator type. This can be a method name,
	 * a built-in validator name, a class name, or a path alias of the validator class.
	 * @param \yii\base\Model $object the data object to be validated.
	 * @param array|string $attributes list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the validator properties
	 * @return Validator the validator
	 */
	public static function createValidator($type, $object, $attributes, $params = [])
	{
		$params['attributes'] = $attributes;

		if (method_exists($object, $type)) {
			// method-based validator
			$params['class'] = __NAMESPACE__ . '\InlineValidator';
			$params['method'] = $type;
		} else {
			if (isset(static::$builtInValidators[$type])) {
				$type = static::$builtInValidators[$type];
			}
			if (is_array($type)) {
				foreach ($type as $name => $value) {
					$params[$name] = $value;
				}
			} else {
				$params['class'] = $type;
			}
		}

		return Yii::createObject($params);
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->attributes = (array)$this->attributes;
		$this->on = (array)$this->on;
		$this->except = (array)$this->except;
	}

	/**
	 * Validates the specified object.
	 * @param \yii\base\Model $object the data object being validated
	 * @param array|null $attributes the list of attributes to be validated.
	 * Note that if an attribute is not associated with the validator,
	 * it will be ignored.
	 * If this parameter is null, every attribute listed in [[attributes]] will be validated.
	 */
	public function validateAttributes($object, $attributes = null)
	{
		if (is_array($attributes)) {
			$attributes = array_intersect($this->attributes, $attributes);
		} else {
			$attributes = $this->attributes;
		}
		foreach ($attributes as $attribute) {
			$skip = $this->skipOnError && $object->hasErrors($attribute)
				|| $this->skipOnEmpty && $this->isEmpty($object->$attribute);
			if (!$skip) {
				$this->validateAttribute($object, $attribute);
			}
		}
	}

	/**
	 * Validates a single attribute.
	 * Child classes must implement this method to provide the actual validation logic.
	 * @param \yii\base\Model $object the data object to be validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
	public function validateAttribute($object, $attribute)
	{
		$result = $this->validateValue($object->$attribute);
		if (!empty($result)) {
			$this->addError($object, $attribute, $result[0], $result[1]);
		}
	}

	/**
	 * Validates a given value.
	 * You may use this method to validate a value out of the context of a data model.
	 * @param mixed $value the data value to be validated.
	 * @param string $error the error message to be returned, if the validation fails.
	 * @return boolean whether the data is valid.
	 */
	public function validate($value, &$error = null)
	{
		$result = $this->validateValue($value);
		if (empty($result)) {
			return true;
		} else {
			list($message, $params) = $result;
			$params['attribute'] = Yii::t('yii', 'the input value');
			$params['value'] = is_array($value) ? 'array()' : $value;
			$error = Yii::$app->getI18n()->format($message, $params, Yii::$app->language);
			return false;
		}
	}

	/**
	 * Validates a value.
	 * A validator class can implement this method to support data validation out of the context of a data model.
	 * @param mixed $value the data value to be validated.
	 * @return array|null the error message and the parameters to be inserted into the error message.
	 * Null should be returned if the data is valid.
	 * @throws NotSupportedException if the validator does not supporting data validation without a model
	 */
	protected function validateValue($value)
	{
		throw new NotSupportedException(get_class($this) . ' does not support validateValue().');
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
	 * @param \yii\web\View $view the view object that is going to be used to render views or view files
	 * containing a model form with this validator applied.
	 * @return string the client-side validation script. Null if the validator does not support
	 * client-side validation.
	 * @see \yii\web\ActiveForm::enableClientValidation
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		return null;
	}

	/**
	 * Returns a value indicating whether the validator is active for the given scenario and attribute.
	 *
	 * A validator is active if
	 *
	 * - the validator's `on` property is empty, or
	 * - the validator's `on` property contains the specified scenario
	 *
	 * @param string $scenario scenario name
	 * @return boolean whether the validator applies to the specified scenario.
	 */
	public function isActive($scenario)
	{
		return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
	}

	/**
	 * Adds an error about the specified attribute to the model object.
	 * This is a helper method that performs message selection and internationalization.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the attribute being validated
	 * @param string $message the error message
	 * @param array $params values for the placeholders in the error message
	 */
	public function addError($object, $attribute, $message, $params = [])
	{
		$value = $object->$attribute;
		$params['attribute'] = $object->getAttributeLabel($attribute);
		$params['value'] = is_array($value) ? 'array()' : $value;
		$object->addError($attribute, Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
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
		return $value === null || $value === [] || $value === ''
		|| $trim && is_scalar($value) && trim($value) === '';
	}
}
