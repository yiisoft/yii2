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
 * CaptchaValidator 验证属性值是否与 CAPTCHA 中显示的验证码相同。
 *
 * CaptchaValidator 应与 [[CaptchaAction]] 一起使用。
 *
 * 请注意，一旦 CAPTCHA 验证成功，将自动生成新的 CAPTCHA。
 * 因此，CAPTCHA 验证不应在AJAX验证模式下使用，因为即使用户输入的 CAPTCHA
 * 图像中显示的代码与最新的 CAPTCHA 代码实际不同，它也可能无法通过验证。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaValidator extends Validator
{
    /**
     * @var bool 如果输入为空，是否跳过此验证器。
     */
    public $skipOnEmpty = false;
    /**
     * @var bool 比较是否区分大小写。默认为 false。
     */
    public $caseSensitive = false;
    /**
     * @var string 渲染 CAPTCHA 图像的控制器动作的路径。
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
     * 从 [[captchaAction]] 指定的路径创建 CAPTCHA 动作对象。
     * @return \yii\captcha\CaptchaAction 动作对象
     * @throws InvalidConfigException
     */
    public function createCaptchaAction()
    {
        $ca = Yii::$app->createController($this->captchaAction);
        if ($ca !== false) {
            /* @var $controller \yii\base\Controller */
            list($controller, $actionID) = $ca;
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

        return 'yii.validation.captcha(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
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
