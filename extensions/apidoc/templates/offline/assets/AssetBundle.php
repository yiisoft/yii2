<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\templates\offline\assets;
use yii\web\View;

/**
 * The asset bundle for the offline template.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class AssetBundle extends \yii\web\AssetBundle
{
	public $sourcePath = '@yii/apidoc/templates/offline/assets/css';
	public $css = [
		'api.css',
		'style.css',
	];
	public $depends = [
		'yii\web\JqueryAsset',
	];
	public $jsOptions = [
		'position' => View::POS_HEAD,
	];
}
