<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\web\AssetBundle;

/**
 * Debugger asset bundle
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DebugAsset extends AssetBundle
{
    public $css = [
        'yii2-debug\assets\main.css',
        'yii2-debug\assets\toolbar.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
