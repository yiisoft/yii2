<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use yii\web\AssetBundle;

/**
 * Highlight asset bundle
 * 
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class HighlightAsset extends AssetBundle
{

    public $sourcePath = '@app/vendor/scrivo/highlight.php/styles';

    public $css = [
        'github.css',
    ];

}
