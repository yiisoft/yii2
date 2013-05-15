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
	 * @var boolean whether validation process should take into account IDN (internationalized domain
	 * names). Defaults to false meaning that validation of emails containing IDN will always fail.
	 */
	public $idn = false;


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is not a valid email address.');
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
		if (!$this->validateValue($value)) {
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
		// make sure string length is limited to avoid DOS attacks
		if (!is_string($value) || strlen($value) >= 255) {
			return false;
		}
		if (($atPosition = strpos($value, '@')) === false) {
			return false;
		}
		$domain = rtrim(substr($value, $atPosition + 1), '>');
		if ($this->idn) {
			$value = idn_to_ascii(ltrim(substr($value, 0, $atPosition), '<')) . '@' . idn_to_ascii($domain);
		}
		$valid = preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value);
		if ($valid) {
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
		$options = array(
			'pattern' => new JsExpression($this->pattern),
			'fullPattern' => new JsExpression($this->fullPattern),
			'allowName' => $this->allowName,
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
				'{value}' => $object->$attribute,
			))),
			'idn' => (boolean)$this->idn,
		);
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		return 'yii.validation.email(value, messages, ' . Json::encode($options) . ');';
	}
}
