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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetBundle extends Object
{
	/**
	 * @var string the root directory of the source asset files. If this is set,
	 * the source asset files will be published to [[basePath]] when the bundle
	 * is being used the first time.
	 */
	public $sourcePath;
	/**
	 * @var string the root directory of the public asset files. If this is not set
	 * while [[sourcePath]] is set, a default value will be set by [[AssetManager]]
	 * when it publishes the source asset files. If you set this property, please
	 * make sure the directory is Web accessible.
	 */
	public $basePath;
	/**
	 * @var string the base URL that will be prefixed to the asset files.
	 * This property must be set if you set [[basePath]] explicitly.
	 * When this property is not set, it will be initialized as the base URL
	 * that the assets are published to.
	 */
	public $baseUrl;
	/**
	 * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
	 * be specified in one of the three formats:
	 *
	 * - a relative file path: a path relative to [[basePath]];,
	 * - an absolute URL;
	 * - a path alias that can be resolved into a relative path or an absolute URL.
	 *
	 * Note that only forward slashes "/" should be used as directory separators.
	 *
	 * Each JavaScript file may be associated with options. In this case, the array key
	 * should be the JavaScript file path, while the corresponding array value should
	 * be the option array. The options will be passed to [[ViewContent::registerJsFile()]].
	 */
	public $js = array();
	/**
	 * @var array list of CSS files that this bundle contains. Each CSS file can
	 * be specified in one of the three formats:
	 *
	 * - a relative file path: a path relative to [[basePath]];,
	 * - an absolute URL;
	 * - a path alias that can be resolved into a relative path or an absolute URL.
	 *
	 * Note that only forward slashes "/" should be used as directory separators.
	 *
	 * Each CSS file may be associated with options. In this case, the array key
	 * should be the CSS file path, while the corresponding array value should
	 * be the option array. The options will be passed to [[ViewContent::registerCssFile()]].
	 */
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
		if ($this->sourcePath !== null) {
			$this->sourcePath = rtrim(Yii::getAlias($this->sourcePath), '/\\');
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
		if ($this->sourcePath !== null) {
			$baseUrl = $assetManager->publish($this->sourcePath);
			if ($this->baseUrl === null) {
				$this->baseUrl = $baseUrl;
			}
		}
	}
}