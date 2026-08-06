<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\web;

use yii\web\AssetBundle;

class AssetManagerTestSimpleBundle extends AssetBundle
{
    public $basePath = '@webroot/js';
    public $baseUrl = '@web/js';
    public $js = [
        'jquery.js',
    ];
}
