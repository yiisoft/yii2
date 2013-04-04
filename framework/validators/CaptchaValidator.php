<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * CaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 *
 * CaptchaValidator should be used together with [[CaptchaAction]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaValidator extends Validator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to false.
	 */
	public $caseSensitive = false;
	/**
	 * @var string the route of the controller action that renders the CAPTCHA image.
	 */
	public $captchaAction = 'site/captcha';


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
			$message = $this->message !== null ? $this->message : Yii::t('yii|The verification code is incorrect.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 */
	public function validateValue($value)
	{
		$captcha = $this->getCaptchaAction();
		return $captcha->validate($value, $this->caseSensitive);
	}

	/**
	 * Returns the CAPTCHA action object.
	 * @return CaptchaAction the action object
	 */
	public function getCaptchaAction()
	{
		$ca = Yii::$app->createController($this->captchaAction);
		if ($ca !== false) {
			/** @var \yii\base\Controller $controller */
			list($controller, $actionID) = $ca;
			$action = $controller->createAction($actionID);
			if ($action !== null) {
				return $action;
			}
		}
		throw new InvalidConfigException('Invalid CAPTCHA action ID: ' . $this->captchaAction);
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		$captcha = $this->getCaptchaAction();
		$message = $this->message !== null ? $this->message : \Yii::t('yii|The verification code is incorrect.');
		$message = strtr($message, array(
			'{attribute}' => $object->getAttributeLabel($attribute),
			'{value}' => $object->$attribute,
		));
		$code = $captcha->getVerifyCode(false);
		$hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
		$js = "
var hash = $('body').data(' {$this->captchaAction}.hash');
if (hash == null)
	hash = $hash;
else
	hash = hash[" . ($this->caseSensitive ? 0 : 1) . "];
for(var i=value.length-1, h=0; i >= 0; --i) h+=value." . ($this->caseSensitive ? '' : 'toLowerCase().') . "charCodeAt(i);
if(h != hash) {
	messages.push(" . json_encode($message) . ");
}
";

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

