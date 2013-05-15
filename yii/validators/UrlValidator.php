<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\Json;

/**
 * UrlValidator validates that the attribute value is a valid http or https URL.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlValidator extends Validator
{
	/**
	 * @var string the regular expression used to validate the attribute value.
	 * The pattern may contain a `{schemes}` token that will be replaced
	 * by a regular expression which represents the [[validSchemes]].
	 */
	public $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
	/**
	 * @var array list of URI schemes which should be considered valid. By default, http and https
	 * are considered to be valid schemes.
	 **/
	public $validSchemes = array('http', 'https');
	/**
	 * @var string the default URI scheme. If the input doesn't contain the scheme part, the default
	 * scheme will be prepended to it (thus changing the input). Defaults to null, meaning a URL must
	 * contain the scheme part.
	 **/
	public $defaultScheme;
	/**
	 * @var boolean whether validation process should take into account IDN (internationalized
	 * domain names). Defaults to false meaning that validation of URLs containing IDN will always
	 * fail.
	 */
	public $idn = false;


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is not a valid URL.');
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
		if ($this->validateValue($value)) {
			if ($this->defaultScheme !== null && strpos($value, '://') === false) {
				$object->$attribute = $this->defaultScheme . '://' . $value;
			}
		} else {
			$this->addError($object, $attribute, $this->message);
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		// make sure the length is limited to avoid DOS attacks
		if (is_string($value) && strlen($value) < 2000) {
			if ($this->defaultScheme !== null && strpos($value, '://') === false) {
				$value = $this->defaultScheme . '://' . $value;
			}

			if (strpos($this->pattern, '{schemes}') !== false) {
				$pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
			} else {
				$pattern = $this->pattern;
			}

			if ($this->idn) {
				$value = preg_replace_callback('/:\/\/([^\/]+)/', function($matches) {
					return '://' . idn_to_ascii($matches[1]);
				}, $value);
			}

			if (preg_match($pattern, $value)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 * @see \yii\Web\ActiveForm::enableClientValidation
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		if (strpos($this->pattern, '{schemes}') !== false) {
			$pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
		} else {
			$pattern = $this->pattern;
		}

		$options = array(
			'pattern' => new JsExpression($pattern),
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
				'{value}' => $object->$attribute,
			))),
			'idn' => (boolean)$this->idn,
		);
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}
		if ($this->defaultScheme !== null) {
			$options['defaultScheme'] = $this->defaultScheme;
		}

		return 'yii.validation.url(value, messages, ' . Json::encode($options) . ');';
	}
}
