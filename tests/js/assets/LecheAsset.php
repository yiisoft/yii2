<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class LecheAsset extends AssetBundle
{
    public $sourcePath = '@bower/leche/dist';
    public $js = ['leche.min.js'];
}
