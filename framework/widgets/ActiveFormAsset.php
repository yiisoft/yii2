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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFormAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@yii/assets';

    /**
     * @inheritdoc
     */
    public $js = ['yii.activeForm.js'];

    /**
     * @inheritdoc
     */
    public $depends = [YiiAsset::class];
}
