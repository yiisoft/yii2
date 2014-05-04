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
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
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
     * then this property must be set either manually or automatically (by [[AssetManager]] via
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
     * @var array list of bundle class names that this bundle depends on.
     *
     * For example:
     *
     * ```php
     * public $depends = [
     *    'yii\web\YiiAsset',
     *    'yii\bootstrap\BootstrapAsset',
     * ];
     * ```
     */
    public $depends = [];
    /**
     * @var array list of JavaScript files that this bundle contains. Each JavaScript file can
     * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
     * an external JavaScript file.
     *
     * Note that only forward slash "/" can be used as directory separator.
     */
    public $js = [];
    /**
     * @var array list of CSS files that this bundle contains. Each CSS file can
     * be either a file path (without leading slash) relative to [[basePath]] or a URL representing
     * an external CSS file.
     *
     * Note that only forward slash "/" can be used as directory separator.
     */
    public $css = [];
    /**
     * @var array the options that will be passed to [[View::registerJsFile()]]
     * when registering the JS files in this bundle.
     */
    public $jsOptions = [];
    /**
     * @var array the options that will be passed to [[View::registerCssFile()]]
     * when registering the CSS files in this bundle.
     */
    public $cssOptions = [];
    /**
     * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
     * is being published.
     */
    public $publishOptions = [];

    /**
     * @param View $view
     * @return static the registered asset bundle instance
     */
    public static function register($view)
    {
        return $view->registerAssetBundle(get_called_class());
    }

    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
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
     * @param \yii\web\View $view the view that the asset files are to be registered with.
     */
    public function registerAssetFiles($view)
    {
        foreach ($this->js as $js) {
            if ($js[0] !== '/' && $js[0] !== '.' && strpos($js, '://') === false) {
                $view->registerJsFile($this->baseUrl . '/' . $js, [], $this->jsOptions);
            } else {
                $view->registerJsFile($js, [], $this->jsOptions);
            }
        }
        foreach ($this->css as $css) {
            if ($css[0] !== '/' && $css[0] !== '.' && strpos($css, '://') === false) {
                $view->registerCssFile($this->baseUrl . '/' . $css, [], $this->cssOptions);
            } else {
                $view->registerCssFile($css, [], $this->cssOptions);
            }
        }
    }

    /**
     * Publishes the asset bundle if its source code is not under Web-accessible directory.
     * It will also try to convert non-CSS or JS files (e.g. LESS, Sass) into the corresponding
     * CSS or JS files using [[AssetManager::converter|asset converter]].
     * @param AssetManager $am the asset manager to perform the asset publishing
     */
    public function publish($am)
    {
        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list ($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }
        $converter = $am->getConverter();
        foreach ($this->js as $i => $js) {
            if (strpos($js, '/') !== 0 && strpos($js, '://') === false) {
                if (isset($this->basePath, $this->baseUrl)) {
                    $this->js[$i] = $converter->convert($js, $this->basePath);
                } else {
                    $this->js[$i] = '/' . $js;
                }
            }
        }
        foreach ($this->css as $i => $css) {
            if (strpos($css, '/') !== 0 && strpos($css, '://') === false) {
                if (isset($this->basePath, $this->baseUrl)) {
                    $this->css[$i] = $converter->convert($css, $this->basePath);
                } else {
                    $this->css[$i] = '/' . $css;
                }
            }
        }
    }
}
