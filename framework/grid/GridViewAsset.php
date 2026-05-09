<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\grid;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{
    public $sourcePath = '@yii/assets';
    public $js = [
        'yii.gridView.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
