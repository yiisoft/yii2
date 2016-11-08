<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class ChaiAsset extends AssetBundle
{
    public $sourcePath = '@bower/chai';
    public $js = ['chai.js'];
}
