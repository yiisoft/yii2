<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * AssetManager manages asset bundles and asset publishing.
 *
 * AssetManager is configured as an application component in [[\yii\web\Application]] by default.
 * You can access that instance via `Yii::$app->assetManager`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ~~~
 * 'assetManager' => [
 *     'bundles' => [
 *         // you can override AssetBundle configs here
 *     ],
 *     //'linkAssets' => true,
 *     // ...
 * ]
 * ~~~
 *
 * @property AssetConverterInterface $converter The asset converter. Note that the type of this property
 * differs in getter and setter. See [[getConverter()]] and [[setConverter()]] for details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetManager extends Component
{
    /**
     * @var array list of available asset bundles. The keys are the class names (**without leading backslash**)
     * of the asset bundles, and the values are either the configuration arrays for creating the [[AssetBundle]]
     * objects or the corresponding asset bundle instances. For example, the following code disables
     * the bootstrap css file used by Bootstrap widgets (because you want to use your own styles):
     *
     * ~~~
     * [
     *     'yii\bootstrap\BootstrapAsset' => [
     *         'css' => [],
     *     ],
     * ]
     * ~~~
     */
    public $bundles = [];
    /**
     * @return string the root directory storing the published asset files.
     */
    public $basePath = '@webroot/assets';
    /**
     * @return string the base URL through which the published asset files can be accessed.
     */
    public $baseUrl = '@web/assets';
    public $assetMap = [];

    /**
     * Initializes the component.
     * @throws InvalidConfigException if [[basePath]] is invalid
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);
        if (is_dir($this->basePath)) {
            $this->basePath = realpath($this->basePath);
        } else {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        }
        $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
    }

    /**
     * Returns the named asset bundle.
     *
     * This method will first look for the bundle in [[bundles]]. If not found,
     * it will treat `$name` as the class of the asset bundle and create a new instance of it.
     *
     * @param string $name the class name of the asset bundle
     * @return AssetBundle the asset bundle instance
     * @throws InvalidConfigException if $name does not refer to a valid asset bundle
     */
    public function getBundle($name)
    {
        if ($this->bundles === false) {
            return null;
        } elseif (!isset($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name);
        } elseif ($this->bundles[$name] instanceof AssetBundle) {
            return $this->bundles[$name];
        } elseif (is_array($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, $this->bundles[$name]);
        } elseif ($this->bundles[$name] === false) {
            return null;
        } else {
            throw new InvalidConfigException("Invalid asset bundle configuration: $name");
        }
    }

    protected function loadBundle($name, $config = [])
    {
        if (!isset($config['class'])) {
            $config['class'] = $name;
        }
        $bundle = Yii::createObject($config);
        if ($bundle->basePath === null) {
            $bundle->basePath = $this->basePath;
        }
        if ($bundle->baseUrl === null) {
            $bundle->baseUrl = $this->baseUrl;
        }
        return $bundle;
    }

    /**
     * @param View $view
     * @param AssetBundle $bundle
     */
    public function registerAssetFiles($view, $bundle)
    {
        foreach ($bundle->js as $js) {
            $view->registerJsFile($this->getAssetUrl($bundle, $js), $bundle->jsOptions);
        }
        foreach ($bundle->css as $css) {
            $view->registerCssFile($this->getAssetUrl($bundle, $css), $bundle->cssOptions);
        }
    }

    protected function getAssetUrl($bundle, $file)
    {
        if (strncmp($file, '@/', 2) === 0) {
            $file = $this->baseUrl . substr($file, 1);
        } elseif (Url::isRelative($file)) {
            $file = $bundle->baseUrl . '/' . $file;
        }
        // todo: assetMap
        return $file;
    }
}
