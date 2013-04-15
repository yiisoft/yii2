<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Object;

/**
 * Each asset bundle should be declared with the following structure:
 *
 * ~~~
 * array(
 *     'basePath' => '...',
 *     'baseUrl' => '...',  // if missing, the bundle will be published to the "www/assets" folder
 *     'js' => array(
 *         'js/main.js',
 *         'js/menu.js',
 *         'js/base.js' => self::POS_HEAD,
 *     'css' => array(
 *         'css/main.css',
 *         'css/menu.css',
 *     ),
 *     'depends' => array(
 *         'jquery',
 *         'yii',
 *         'yii/treeview',
 *     ),
 * )
 * ~~~
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetBundle extends Object
{
	public $basePath;
	public $baseUrl;  // if missing, the bundle will be published to the "www/assets" folder
	public $js = array();
	public $css = array();
	public $depends = array();

	/**
	 * @param \yii\base\ViewContent $content
	 */
	public function registerWith($content)
	{
		foreach ($this->depends as $name) {
			$content->registerAssetBundle($name);
		}
		foreach ($this->js as $js => $options) {
			if (is_array($options)) {
				$content->registerJsFile($js, $options);
			} else {
				$content->registerJsFile($options);
			}
		}
		foreach ($this->css as $css => $options) {
			if (is_array($options)) {
				$content->registerCssFile($css, $options);
			} else {
				$content->registerCssFile($options);
			}
		}
	}
}