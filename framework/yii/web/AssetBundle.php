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
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles
 * used in an application.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
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
	 * @var array list of the bundle names that this bundle depends on
	 */
	public $depends = array();
	/**
	 * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
	 * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
	 * an external JavaScript file.
	 *
	 * Note that only forward slash "/" can be used as directory separator.
	 */
	public $js = array();
	/**
	 * @var array list of CSS files that this bundle contains. Each CSS file can
	 * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
	 * an external CSS file.
	 *
	 * Note that only forward slash "/" can be used as directory separator.
	 */
	public $css = array();
	/**
	 * @var array the options that will be passed to [[\yii\base\View::registerJsFile()]]
	 * when registering the JS files in this bundle.
	 */
	public $jsOptions = array();
	/**
	 * @var array the options that will be passed to [[\yii\base\View::registerCssFile()]]
	 * when registering the CSS files in this bundle.
	 */
	public $cssOptions = array();
	/**
	 * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
	 * is being published.
	 */
	public $publishOptions = array();

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
	 * Registers the CSS and JS files with the given view.
	 * This method will first register all dependent asset bundles.
	 * It will then try to convert non-CSS or JS files (e.g. LESS, Sass) into the corresponding
	 * CSS or JS files using [[AssetManager::converter|asset converter]].
	 * @param \yii\base\View $view the view that the asset files to be registered with.
	 * @throws InvalidConfigException if [[baseUrl]] or [[basePath]] is not set when the bundle
	 * contains internal CSS or JS files.
	 */
	public function registerAssets($view)
	{
		foreach ($this->depends as $name) {
			$view->registerAssetBundle($name);
		}

		$this->publish($view->getAssetManager());

		foreach ($this->js as $js) {
			$view->registerJsFile($this->baseUrl . '/' . $js, $this->jsOptions);
		}
		foreach ($this->css as $css) {
			$view->registerCssFile($this->baseUrl . '/' . $css, $this->cssOptions);
		}
	}

	/**
	 * Publishes the asset bundle if its source code is not under Web-accessible directory.
	 * @param AssetManager $am the asset manager to perform the asset publishing
	 * @throws InvalidConfigException if [[baseUrl]] or [[basePath]] is not set when the bundle
	 * contains internal CSS or JS files.
	 */
	public function publish($am)
	{
		if ($this->sourcePath !== null) {
			list ($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
		}
		$converter = $am->getConverter();
		foreach ($this->js as $i => $js) {
			if (strpos($js, '/') !== 0 && strpos($js, '://') === false) {
				if (isset($this->basePath, $this->baseUrl)) {
					$this->js[$i] = $converter->convert($js, $this->basePath, $this->baseUrl);
				} else {
					throw new InvalidConfigException('Both of the "baseUrl" and "basePath" properties must be set.');
				}
			}
		}
		foreach ($this->css as $i => $css) {
			if (strpos($css, '/') !== 0 && strpos($css, '://') === false) {
				if (isset($this->basePath, $this->baseUrl)) {
					$this->css[$i] = $converter->convert($css, $this->basePath, $this->baseUrl);
				} else {
					throw new InvalidConfigException('Both of the "baseUrl" and "basePath" properties must be set.');
				}
			}
		}
	}
}
