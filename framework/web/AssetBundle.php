<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetBundle extends Object
{
	/**
	 * @var string the root directory of the source asset files. A source asset file
	 * is a file that is part of your source code repository of your Web application.
	 *
	 * You must set this property if the directory containing the source asset files
	 * is not Web accessible (this is usually the case for extensions).
	 *
	 * By setting this property, the asset manager will publish the source asset files
	 * to a Web-accessible directory [[basePath]].
	 *
	 * You can use either a directory or an alias of the directory.
	 */
	public $sourcePath;
	/**
	 * @var string the Web-accessible directory that contains the asset files in this bundle.
	 *
	 * If [[sourcePath]] is set, this property will be *overwritten* by [[AssetManager]]
	 * when it publishes the asset files from [[sourcePath]].
	 *
	 * If the bundle contains any assets that are specified in terms of relative file path,
	 * then this property must be set either manually or automatically (by asset manager via
	 * asset publishing).
	 *
	 * You can use either a directory or an alias of the directory.
	 */
	public $basePath;
	/**
	 * @var string the base URL that will be prefixed to the asset files for them to
	 * be accessed via Web server.
	 *
	 * If [[sourcePath]] is set, this property will be *overwritten* by [[AssetManager]]
	 * when it publishes the asset files from [[sourcePath]].
	 *
	 * If the bundle contains any assets that are specified in terms of relative file path,
	 * then this property must be set either manually or automatically (by asset manager via
	 * asset publishing).
	 *
	 * You can use either a URL or an alias of the URL.
	 */
	public $baseUrl;
	/**
	 * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
	 * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
	 * an external JavaScript file.
	 *
	 * Note that only forward slash "/" can be used as directory separator.
	 *
	 * Each JavaScript file may be associated with options. In this case, the array key
	 * should be the JavaScript file path, while the corresponding array value should
	 * be the option array. The options will be passed to [[ViewContent::registerJsFile()]].
	 */
	public $js = array();
	/**
	 * @var array list of CSS files that this bundle contains. Each CSS file can
	 * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
	 * an external CSS file.
	 *
	 * Note that only forward slash "/" can be used as directory separator.
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
	/**
	 * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
	 * is being published.
	 */
	public $publishOption = array();

	/**
	 * Initializes the bundle.
	 */
	public function init()
	{
		if ($this->sourcePath !== null) {
			$this->sourcePath = rtrim(Yii::getAlias($this->sourcePath), '/\\');
		}
		if ($this->basePath !== null) {
			$this->basePath = rtrim(Yii::getAlias($this->basePath), '/\\');
		}
		if ($this->baseUrl !== null) {
			$this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
		}
	}

	/**
	 * @param \yii\base\ViewContent $page
	 * @param AssetManager $am
	 * @throws InvalidConfigException
	 */
	public function registerAssets($page, $am)
	{
		foreach ($this->depends as $name) {
			$page->registerAssetBundle($name);
		}

		if ($this->sourcePath !== null) {
			list ($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOption);
		}

		foreach ($this->js as $js => $options) {
			$js = is_string($options) ? $options : $js;
			if (strpos($js, '/') !== 0 && strpos($js, '://') === false) {
				if (isset($this->basePath, $this->baseUrl)) {
					$js = $am->processAsset(ltrim($js, '/'), $this->basePath, $this->baseUrl);
				} else {
					throw new InvalidConfigException('Both of the "baseUrl" and "basePath" properties must be set.');
				}
			}
			$page->registerJsFile($js, is_array($options) ? $options : array());
		}
		foreach ($this->css as $css => $options) {
			$css = is_string($options) ? $options : $css;
			if (strpos($css, '//') !== 0 && strpos($css, '://') === false) {
				if (isset($this->basePath, $this->baseUrl)) {
					$css = $am->processAsset(ltrim($css, '/'), $this->basePath, $this->baseUrl);
				} else {
					throw new InvalidConfigException('Both of the "baseUrl" and "basePath" properties must be set.');
				}
			}
			$page->registerCssFile($css, is_array($options) ? $options : array());
		}
	}
}