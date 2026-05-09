<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\web\AssetBundle;

/**
 * The asset bundle for the [[ActiveForm]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFormAsset extends AssetBundle
{
    public $sourcePath = '@yii/assets';
    public $js = [
        'yii.activeForm.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
