<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class SinonAsset extends AssetBundle
{
    public $sourcePath = '@node_modules/sinon/pkg';
    public $js = ['sinon.js'];
}
