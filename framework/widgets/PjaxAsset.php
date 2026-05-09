<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files required by [[Pjax]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PjaxAsset extends AssetBundle
{
    public $sourcePath = '@bower/yii2-pjax';
    public $js = [
        'jquery.pjax.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
