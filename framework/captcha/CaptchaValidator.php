<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\captcha;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\Json;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\Controller;

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
     * @var bool whether to skip this validator if the input is empty.
     */
    public $skipOnEmpty = false;
    /**
     * @var bool whether the comparison is case sensitive. Defaults to false.
     */
    public $caseSensitive = false;
    /**
     * @var string the route of the controller action that renders the CAPTCHA image.
     */
    public $captchaAction = 'site/captcha';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The verification code is incorrect.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = !is_array($value) && $captcha->validate($value, $this->caseSensitive);

        return $valid ? null : [$this->message, []];
    }

    /**
     * Creates the CAPTCHA action object from the route specified by [[captchaAction]].
     * @return CaptchaAction the action object
     * @throws InvalidConfigException
     *
     * @phpstan-return CaptchaAction<Controller<Module>>
     * @psalm-return CaptchaAction<Controller<Module>>
     */
    public function createCaptchaAction()
    {
        $ca = Yii::$app->createController($this->captchaAction);
        if ($ca !== false) {
            /**
             * @var Controller $controller
             * @phpstan-var Controller<Module> $controller
             */
            list($controller, $actionID) = $ca;
            /**
             * @var CaptchaAction|null
             * @phpstan-var CaptchaAction<Controller<Module>>|null
             */
            $action = $controller->createAction($actionID);
            if ($action !== null) {
                return $action;
            }
        }
        throw new InvalidConfigException('Invalid CAPTCHA action ID: ' . $this->captchaAction);
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.captcha(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $captcha = $this->createCaptchaAction();
        $code = $captcha->getVerifyCode(false);
        $hash = $captcha->generateValidationHash($this->caseSensitive ? $code : strtolower($code));
        $options = [
            'hash' => $hash,
            'hashKey' => 'yiiCaptcha/' . $captcha->getUniqueId(),
            'caseSensitive' => $this->caseSensitive,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Yii::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
