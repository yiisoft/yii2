<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient\widgets;

use yii\web\AssetBundle;

/**
 * ChoiceAsset is an asset bundle for [[Choice]] widget.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ChoiceAsset extends AssetBundle
{
	public $sourcePath = '@yii/authclient/widgets/assets';
	public $js = [
		'authchoice.js',
	];
	public $css = [
		'authchoice.css',
	];
	public $depends = [
		'yii\web\YiiAsset',
	];
}