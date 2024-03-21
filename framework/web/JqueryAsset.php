<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * This asset bundle provides the [jQuery](https://jquery.com/) JavaScript library.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JqueryAsset extends AssetBundle
{
    public $sourcePath = '@npm/jquery/dist';
    public $js = [
        YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
    ];
}
