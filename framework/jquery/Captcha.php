<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\captcha\CaptchaAction;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Captcha is an enhanced version of [[\yii\captcha\Captcha]], which allows refreshing CAPTCHA image on click via
 * underlying jQuery plugin.
 *
 * @see \yii\captcha\Captcha
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1
 */
class Captcha extends \yii\captcha\Captcha
{
    /**
     * Renders the widget.
     */
    public function run()
    {
        $this->registerClientScript();
        return parent::run();
    }

    /**
     * Registers the needed JavaScript.
     */
    public function registerClientScript()
    {
        $options = $this->getClientOptions();
        $options = empty($options) ? '' : Json::htmlEncode($options);
        $id = $this->imageOptions['id'];
        $view = $this->getView();
        CaptchaAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiCaptcha($options);");
    }

    /**
     * Returns the options for the captcha JS widget.
     * @return array the options
     */
    protected function getClientOptions()
    {
        $route = $this->captchaAction;
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