<?php
/**
 * CaptchaValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

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
	 * @var string the ID of the action that renders the CAPTCHA image. Defaults to 'captcha',
	 * meaning the `captcha` action declared in the current controller.
	 * This can also be a route consisting of controller ID and action ID (e.g. 'site/captcha').
	 */
	public $captchaAction = 'captcha';
	/**
	 * @var boolean whether the attribute value can be null or empty.
	 * Defaults to false, meaning the attribute is invalid if it is empty.
	 */
	public $allowEmpty = false;

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
		$captcha = $this->getCaptchaAction();
		if (!$captcha->validate($value, $this->caseSensitive)) {
			$message = $this->message !== null ? $this->message : \Yii::t('yii|The verification code is incorrect.');
			$this->addError($object, $attribute, $message);
		}
	}

	/**
	 * Returns the CAPTCHA action object.
	 * @return CCaptchaAction the action object
	 */
	public function getCaptchaAction()
	{
		if (strpos($this->captchaAction, '/') !== false) {  // contains controller or module
			$ca = \Yii::$application->createController($this->captchaAction);
			if ($ca !== null) {
				list($controller, $actionID) = $ca;
				$action = $controller->createAction($actionID);
			}
		} else {
			$action = \Yii::$application->getController()->createAction($this->captchaAction);
		}

		if ($action === null) {
			throw new \yii\base\Exception('Invalid captcha action ID: ' . $this->captchaAction);
		}
		return $action;
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

