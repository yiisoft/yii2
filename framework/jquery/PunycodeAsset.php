<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jquery;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[\yii\validators\EmailValidator]]s client validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PunycodeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/punycode';
    /**
     * @inheritdoc
     */
    public $js = ['punycode.js'];
}
