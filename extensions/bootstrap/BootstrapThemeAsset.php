<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\web\AssetBundle;

/**
 * Asset bundle for the Twitter bootstrap default theme.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BootstrapThemeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/twbs/bootstrap/dist';
    public $css = [
        'css/bootstrap-theme.css',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}
