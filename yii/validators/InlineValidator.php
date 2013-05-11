<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * InlineValidator represents a validator which is defined as a method in the object being validated.
 *
 * The validation method must have the following signature:
 *
 * ~~~
 * function foo($attribute, $params)
 * ~~~
 *
 * where `$attribute` refers to the name of the attribute being validated, while `$params`
 * is an array representing the additional parameters supplied in the validation rule.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineValidator extends Validator
{
	/**
	 * @var string|\Closure an anonymous function or the name of a model class method that will be
	 * called to perform the actual validation. Note that if you use anonymous function, you cannot
	 * use `$this` in it unless you are using PHP 5.4 or above.
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the validation method
	 */
	public $params;
	/**
	 * @var string|\Closure an anonymous function or the name of a model class method that returns the client validation code.
	 * The signature of the method should be like the following:
	 *
	 * ~~~
	 * function foo($attribute)
	 * {
	 *     return "javascript";
	 * }
	 * ~~~
	 *
	 * where `$attribute` refers to the attribute name to be validated.
	 *
	 * Please refer to [[clientValidateAttribute()]] for details on how to return client validation code.
	 */
	public $clientValidate;

	/**
	 * Validates the attribute of the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$method = $this->method;
		if (is_string($method)) {
			$method = array($object, $method);
		}
		call_user_func($method, $attribute, $this->params);
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
		if ($this->clientValidate !== null) {
			$method = $this->clientValidate;
			if (is_string($method)) {
				$method = array($object, $method);
			}
			return call_user_func($method, $attribute);
		} else {
			return null;
		}
	}
}
