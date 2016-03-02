<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\bootstrap\assets;

<<<<<<< HEAD
=======
use yii\web\AssetBundle;
>>>>>>> yiichina/master
use yii\web\View;

/**
 * The asset bundle for the offline template.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
<<<<<<< HEAD
class JsSearchAsset extends \yii\web\AssetBundle
=======
class JsSearchAsset extends AssetBundle
>>>>>>> yiichina/master
{
    public $sourcePath = '@vendor/cebe/js-search';
    public $js = [
        'jssearch.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}
