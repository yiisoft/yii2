<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * This asset bundle provides the javascript files required by [[Pjax]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PjaxAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/yii2-pjax';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.pjax.js',];

    /**
     * @inheritdoc
     */
    public $depends = [YiiAsset::class];
}
