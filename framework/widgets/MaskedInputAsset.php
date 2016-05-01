<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * The asset bundle for the [[MaskedInput]] widget.
 *
 * Includes client assets of [jQuery input mask plugin](https://github.com/RobinHerbots/jquery.inputmask).
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 2.0
 */
class MaskedInputAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/jquery.inputmask/dist';

    /**
     * @inheritdoc
     */
    public $js = ['jquery.inputmask.bundle.js'];

    /**
     * @inheritdoc
     */
    public $depends = [YiiAsset::class];
}
