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
class AutoCompleteAsset extends AssetBundle
{
    public $sourcePath = '@yii/jui/assets';
    public $js = [
        'jquery.ui.autocomplete.js',
    ];
    public $depends = [
        'yii\jui\CoreAsset',
        'yii\jui\MenuAsset',
    ];
}
