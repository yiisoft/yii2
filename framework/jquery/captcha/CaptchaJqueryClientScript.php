<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\jquery\captcha;

use yii\base\BaseObject;
use yii\captcha\Captcha;
use yii\captcha\CaptchaAction;
use yii\captcha\CaptchaAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\client\ClientScriptInterface;
use yii\web\View;

/**
 * CaptchaJqueryClientScript provides client-side script registration for CAPTCHA widgets using jQuery.
 *
 * This class implements {@see ClientScriptInterface} to supply client-side options and register the corresponding
 * JavaScript code for CAPTCHA widgets in Yii2 forms using jQuery.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.2.0
 */
class CaptchaJqueryClientScript implements ClientScriptInterface
{
    public function register(BaseObject $object, View $view): void
    {
        $options = $this->getClientOptions($object);
        $options = empty($options) ? '' : Json::htmlEncode($options);

        $id = $object->imageOptions['id'];
        $view = $object->getView();

        CaptchaAsset::register($view);

        $view->registerJs("jQuery('#$id').yiiCaptcha($options);");
    }

    public function getClientOptions(BaseObject $object): array
    {
        if (!$object instanceof Captcha) {
            return [];
        }

        $route = $object->captchaAction;

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
