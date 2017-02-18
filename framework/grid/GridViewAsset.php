<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\grid;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@yii/assets';

    /**
     * @inheritdoc
     */
    public $js = ['yii.gridView.js'];

    /**
     * @inheritdoc
     */
    public $depends = [YiiAsset::class];
}
