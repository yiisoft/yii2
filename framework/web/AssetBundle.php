<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
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
	/**
	 * @var string the root directory of the asset files. If this is not set,
	 * the assets are considered to be located under a Web-accessible folder already
	 * and no asset publishing will be performed.
	 */
	public $basePath;
	/**
	 * @var string the base URL that will be prefixed to the asset files.
	 * This property must be set if [[basePath]] is not set.
	 * When this property is not set, it will be initialized as the base URL
	 * that the assets are published to.
	 */
	public $baseUrl;
	/**
	 * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
	 * be specified in one of the three formats:
	 *
	 * - a relative path: a path relative to [[basePath]] if [[basePath]] is set,
	 *   or a URL relative to [[baseUrl]] if [[basePath]] is not set;
	 * - an absolute URL;
	 * - a path alias that can be resolved into a relative path or an absolute URL.
	 *
	 * Note that you should not use backward slashes "\" to specify JavaScript files.
	 *
	 * A JavaScript file can be associated with the options: // todo
	 */
	public $js = array();
	public $css = array();
	/**
	 * @var array list of the bundle names that this bundle depends on
	 */
	public $depends = array();

	public function init()
	{
		if ($this->baseUrl !== null) {
			$this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
		}
		if ($this->basePath !== null) {
			$this->basePath = rtrim(Yii::getAlias($this->basePath), '/\\');
		}
	}

	/**
	 * @param \yii\base\ViewContent $content
	 */
	public function registerAssets($content)
	{
		foreach ($this->depends as $name) {
			$content->registerAssetBundle($name);
		}
		foreach ($this->js as $js => $options) {
			$js = is_string($options) ? $options : $js;
			if (strpos($js, '//') !== 0 && strpos($js, '://') === false) {
				$js = $this->baseUrl . '/' . ltrim($js, '/');
			}
			$content->registerJsFile($js, is_array($options) ? $options : array());
		}
		foreach ($this->css as $css => $options) {
			$css = is_string($options) ? $options : $css;
			if (strpos($css, '//') !== 0 && strpos($css, '://') === false) {
				$css = $this->baseUrl . '/' . ltrim($css, '/');
			}
			$content->registerCssFile($css, is_array($options) ? $options : array());
		}
	}

	/**
	 * @param \yii\web\AssetManager $assetManager
	 */
	public function publish($assetManager)
	{
		if ($this->basePath !== null) {
			$baseUrl = $assetManager->publish($this->basePath);
			if ($this->baseUrl === null) {
				$this->baseUrl = $baseUrl;
			}
		}
	}
}