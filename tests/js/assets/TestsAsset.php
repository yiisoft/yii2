<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class TestsAsset extends AssetBundle
{
    public $depends = [
        'tests\js\assets\MochaAsset',
        'tests\js\assets\ChaiAsset',
        'tests\js\assets\LecheAsset',
        'tests\js\assets\SinonAsset',
    ];
}
