<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\web\AssetBundle;

/**
 * Bootstrap 2 theme for Bootstrap 3
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BootstrapThemeAsset extends AssetBundle
{

    public $sourcePath = '@vendor/twbs/bootstrap/dist';

    public function init() {
        $this->css = YII_DEBUG ? ['js/bootstrap-theme.css'] : ['js/bootstrap-theme.min.css'];
        parent::init();
    }

}
