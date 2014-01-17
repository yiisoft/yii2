<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\validators\ValidationAsset;
use yii\validators\Validator;

/**
 * CaptchaValidator validates that the attribute value is the same as the verification code displayed in the CAPTCHA.
 *
 * CaptchaValidator should be used together with [[CaptchaAction]].
 *
 * Note that once CAPTCHA validation succeeds, a new CAPTCHA will be generated automatically. As a result,
 * CAPTCHA validation should not be used in AJAX validation mode because it may fail the validation
 * even if a user enters the same code as shown in the CAPTCHA image which is actually different from the latest CAPTCHA code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaValidator extends Validator
{
	/**
	 * @var boolean whether to skip this validator if the input is empty.
	 */
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
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', 'The verification code is incorrect.');
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value)
	{
		$captcha = $this->createCaptchaAction();
		$valid = !is_array($value) && $captcha->validate($value, $this->caseSensitive);
		return $valid ? null : [$this->message, []];
	}

	/**
	 * Creates the CAPTCHA action object from the route specified by [[captchaAction]].
	 * @return \yii\captcha\CaptchaAction the action object
	 * @throws InvalidConfigException
	 */
	public function createCaptchaAction()
	{
		$ca = Yii::$app->createController(ltrim($this->captchaAction, '/'));
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
	 * @inheritdoc
	 */
	public function clientValidateAttribute($object, $attribute, $view)
	{
		$captcha = $this->createCaptchaAction();
		$code = $captcha->getVerifyCode(false);
		$hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
		$options = [
			'hash' => $hash,
			'hashKey' => 'yiiCaptcha/' . $this->captchaAction,
			'caseSensitive' => $this->caseSensitive,
			'message' => strtr($this->message, [
				'{attribute}' => $object->getAttributeLabel($attribute),
			]),
		];
		if ($this->skipOnEmpty) {
			$options['skipOnEmpty'] = 1;
		}

		ValidationAsset::register($view);
		return 'yii.validation.captcha(value, messages, ' . json_encode($options) . ');';
	}
}
