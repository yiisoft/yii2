<?php

namespace tests\js\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $sourcePath = '@js-tests/assets/js';
    public $depends = [
        'tests\js\assets\TestsAsset',
        'yii\validators\ValidationAsset',
        'yii\validators\PunycodeAsset',
    ];
    public $js = [
        'utils.js',
        'setup.js',
        'yii.validation.test.js',
        'run.js',
    ];
}
