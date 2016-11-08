<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class MochaAsset extends AssetBundle
{
    public $sourcePath = '@bower/mocha';
    public $css = ['mocha.css'];
    public $js = ['mocha.js'];
}
