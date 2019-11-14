<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use yii\web\AssetBundle;

/**
 * 此资源包提供 [[Captcha]] 小部件所需的 javascript 文件。
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
