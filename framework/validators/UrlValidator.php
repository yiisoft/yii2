<?php
/**
 * UrlValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

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
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

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
		if (($value = $this->validateValue($value)) !== false) {
			$object->$attribute = $value;
		} else {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii:{attribute} is not a valid URL.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Validates a static value to see if it is a valid URL.
	 * Note that this method does not respect [[allowEmpty]] property.
	 * This method is provided so that you can call it directly without going through the model validation rule mechanism.
	 * @param mixed $value the value to be validated
	 * @return mixed false if the the value is not a valid URL, otherwise the possibly modified value ({@see defaultScheme})
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

			if (preg_match($pattern, $value)) {
				return $value;
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
		$message = ($this->message !== null) ? $this->message : \Yii::t('yii:{attribute} is not a valid URL.');
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		));

		if (strpos($this->pattern, '{schemes}') !== false) {
			$pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
		} else {
			$pattern = $this->pattern;
		}

		$js = "
if(!value.match($pattern)) {
	messages.push(" . json_encode($message) . ");
}
";
		if ($this->defaultScheme !== null) {
			$js = "
if(!value.match(/:\\/\\//)) {
	value=" . json_encode($this->defaultScheme) . "+'://'+value;
}
$js
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

