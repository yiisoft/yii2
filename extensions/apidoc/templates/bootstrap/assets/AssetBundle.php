<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap\assets;

use yii\web\View;

/**
 * The asset bundle for the offline template.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class AssetBundle extends \yii\web\AssetBundle
{
    public $sourcePath = '@yii/apidoc/templates/bootstrap/assets/css';
    public $css = [
//		'api.css',
        'style.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
