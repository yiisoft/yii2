<?php
/**
 * EmailValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * EmailValidator validates that the attribute value is a valid email address.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class EmailValidator extends Validator
{
	/**
	 * @var string the regular expression used to validate the attribute value.
	 * @see http://www.regular-expressions.info/email.html
	 */
	public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
	/**
	 * @var string the regular expression used to validate email addresses with the name part.
	 * This property is used only when [[allowName]] is true.
	 * @see allowName
	 */
	public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
	/**
	 * @var boolean whether to allow name in the email address (e.g. "John Smith <john.smith@example.com>"). Defaults to false.
	 * @see fullPattern
	 */
	public $allowName = false;
	/**
	 * @var boolean whether to check the MX record for the email address.
	 * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
	 * exists in your PHP installation.
	 */
	public $checkMX = false;
	/**
	 * @var boolean whether to check port 25 for the email address.
	 * Defaults to false.
	 */
	public $checkPort = false;
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
		if (!$this->validateValue($value)) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} is not a valid email address.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Validates a static value to see if it is a valid email.
	 * Note that this method does not respect [[allowEmpty]] property.
	 * This method is provided so that you can call it directly without going through the model validation rule mechanism.
	 * @param mixed $value the value to be validated
	 * @return boolean whether the value is a valid email
	 */
	public function validateValue($value)
	{
		// make sure string length is limited to avoid DOS attacks
		$valid = is_string($value) && strlen($value) <= 254
			&& (preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value));
		if ($valid) {
			$domain = rtrim(substr($value, strpos($value, '@') + 1), '>');
			if ($this->checkMX && function_exists('checkdnsrr')) {
				$valid = checkdnsrr($domain, 'MX');
			}
			if ($valid && $this->checkPort && function_exists('fsockopen')) {
				$valid = fsockopen($domain, 25) !== false;
			}
		}
		return $valid;
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$message = ($this->message !== null) ? $this->message : \Yii::t('yii|{attribute} is not a valid email address.');
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		));

		$condition = "!value.match( {$this->pattern})";
		if ($this->allowName) {
			$condition .= " && !value.match( {$this->fullPattern})";
		}

		return "
if(" . ($this->allowEmpty ? "$.trim(value)!='' && " : '') . $condition . ") {
	messages.push(" . json_encode($message) . ");
}
";
	}
}
