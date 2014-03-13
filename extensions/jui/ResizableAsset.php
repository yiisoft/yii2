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
class ResizableAsset extends AssetBundle
{
    public $sourcePath = '@yii/jui/assets';
    public $js = [
        'jquery.ui.resizable.js',
    ];
    public $depends = [
        'yii\jui\CoreAsset',
    ];
}
