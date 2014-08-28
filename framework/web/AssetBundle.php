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
     * @var string the directory that contains the asset files in this bundle.
     *
     * The value of this property can be prefixed to every relative asset file path listed in [[js]] and [[css]]
     * to form an absolute file path. If this property is null (meaning not set), it will be filled with the value of
     * [[AssetManager::basePath]] when the bundle is being loaded by [[AssetManager::getBundle()]].
     *
     * You can use either a directory or an alias of the directory.
     */
    public $basePath;
    /**
     * @var string the base URL for the relative asset files listed in [[js]] and [[css]].
     *
     * The value of this property will be prefixed to every relative asset file path listed in [[js]] and [[css]]
     * when they are being registered in a view so that they can be Web accessible.
     *  If this property is null (meaning not set), it will be filled with the value of
     * [[AssetManager::baseUrl]] when the bundle is being loaded by [[AssetManager::getBundle()]].
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
     * @var array list of JavaScript files that this bundle contains. Each JavaScript file can be
     * specified in one of the following formats:
     *
     * - an absolute URL representing an external asset. For example,
     *   `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` or
     *   `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
     * - a path relative to [[basePath]] and [[baseUrl]]: for example, `js/main.js`. There should be no leading slash.
     * - a path relative to [[AssetManager::basePath]] and [[AssetManager::baseUrl]]: for example,
     *   `@/jquery/dist/jquery.js`. The path must begin with `@/`.
     *
     * Note that only forward slash "/" should be used as directory separators.
     */
    public $js = [];
    /**
     * @var array list of CSS files that this bundle contains. Each CSS file can be specified
     * in one of the three formats as explained in [[js]].
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
     * Registers this asset bundle with a view.
     * @param View $view the view to be registered with
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
        if ($this->basePath !== null) {
            $this->basePath = rtrim(Yii::getAlias($this->basePath), '/\\');
        }
        if ($this->baseUrl !== null) {
            $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
        }
    }
}
