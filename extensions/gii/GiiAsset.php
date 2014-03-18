<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use yii\web\AssetBundle;

/**
 * This declares the asset files required by Gii.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GiiAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@yii/gii/assets';
    /**
     * @inheritdoc
     */
    public $css = [
        'main.css',
        'typeahead.js-bootstrap.css',
    ];
    /**
     * @inheritdoc
     */
    public $js = [
        'gii.js',
        'typeahead.js',
    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}
