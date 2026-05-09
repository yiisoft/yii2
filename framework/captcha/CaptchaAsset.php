<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\captcha;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[Captcha]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaAsset extends AssetBundle
{
    public $sourcePath = '@yii/assets';
    public $js = [
        'yii.captcha.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
