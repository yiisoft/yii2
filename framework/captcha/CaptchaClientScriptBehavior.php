<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use yii\base\Behavior;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * CaptchaClientScriptBehavior is a behavior for [[Captcha]] widget, which allows refreshing CAPTCHA image on click via
 * underlying jQuery plugin.
 *
 * Usage example:
 * 
 * ```php
 * <?= $form->field($model, 'captcha')->widget(\yii\captcha\Captcha::class, [
 *     'as clientSide' => \yii\captcha\CaptchaClientScriptBehavior::class,
 *     // configure additional widget properties here
 * ]) ?>
 * ```
 *
 * @see Captcha
 *
 * @property Captcha $owner the owner of this behavior.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class CaptchaClientScriptBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Widget::EVENT_BEFORE_RUN => 'beforeRun'
        ];
    }

    /**
     * Handles [[Widget::EVENT_BEFORE_RUN]] event, registering related client script.
     * @param \yii\base\Event $event event instance.
     */
    public function beforeRun($event)
    {
        $options = $this->getClientOptions();
        $options = empty($options) ? '' : Json::htmlEncode($options);
        $id = $this->owner->imageOptions['id'];
        $view = $this->owner->getView();
        CaptchaAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiCaptcha($options);");
    }

    /**
     * Returns the options for the captcha JS widget.
     * @return array the options
     */
    protected function getClientOptions()
    {
        $route = $this->owner->captchaAction;
        if (is_array($route)) {
            $route[CaptchaAction::REFRESH_GET_VAR] = 1;
        } else {
            $route = [$route, CaptchaAction::REFRESH_GET_VAR => 1];
        }

        $options = [
            'refreshUrl' => Url::toRoute($route),
            'hashKey' => 'yiiCaptcha/' . trim($route[0], '/'),
        ];

        return $options;
    }
}