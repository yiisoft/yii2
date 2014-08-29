<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\web\AssetBundle;

/**
 * AuthChoiceAsset is an asset bundle for [[AuthChoice]] widget.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class AuthChoiceAsset extends AssetBundle
{
    public $js = [
        'yii2-authclient/assets/authchoice.js',
    ];
    public $css = [
        'yii2-authclient/assets/authchoice.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
