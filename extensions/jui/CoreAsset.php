<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CoreAsset extends AssetBundle
{
    public $js = [
        'jquery.ui/ui/core.js',
        'jquery.ui/ui/widget.js',
        'jquery.ui/ui/position.js',
        'jquery.ui/ui/mouse.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
