<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

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
	public $skipOnEmpty = false;
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to false.
	 */
	public $caseSensitive = false;
	/**
	 * @var string the route of the controller action that renders the CAPTCHA image.
	 */
	public $captchaAction = 'site/captcha';


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', 'The verification code is incorrect.');
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
		$captcha = $this->getCaptchaAction();
		return !is_array($value) && $captcha->validate($value, $this->caseSensitive);
	}

	/**
	 * Returns the CAPTCHA action object.
	 * @throws InvalidConfigException
	 * @return \yii\web\CaptchaAction the action object
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
	 * @param \yii\base\View $view the view object that is going to be used to render views or view files
	 * containing a model form with this validator applied.
	 * @return string the client-side validation script.
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$captcha = $this->getCaptchaAction();
		$code = $captcha->getVerifyCode(false);
		$hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
		$options = array(
			'hash' => $hash,
			'hashKey' => 'yiiCaptcha/' . $this->captchaAction,
			'caseSensitive' => $this->caseSensitive,
			'message' => Html::encode(strtr($this->message, array(
				'{attribute}' => $object->getAttributeLabel($attribute),
				'{value}' => $object->$attribute,
			))),
		);
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		$view->registerAssetBundle('yii/validation');
		return 'yii.validation.captcha(value, messages, ' . json_encode($options) . ');';
	}
}

