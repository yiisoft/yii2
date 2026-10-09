<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\web;

use yii\web\AssetBundle;

class AssetBundleTestScssSourceBundle extends AssetBundle
{
    public $sourcePath = '@testScssSourcePath';
    public $css = ['scss/main.scss'];
}
