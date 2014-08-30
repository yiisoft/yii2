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

/**
 * AssetManager manages asset bundle configuration and loading.
 *
 * AssetManager is configured as an application component in [[\yii\web\Application]] by default.
 * You can access that instance via `Yii::$app->assetManager`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as shown in the following example:
 *
 * ```php
 * 'assetManager' => [
 *     'bundles' => [
 *         // you can override AssetBundle configs here
 *     ],
 * ]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetManager extends Component
{
    /**
     * @var array|boolean list of asset bundle configurations. This property is provided to customize asset bundles.
     * When a bundle is being loaded by [[getBundle()]], if it has a corresponding configuration specified here,
     * the configuration will be applied to the bundle.
     *
     * The array keys are the asset bundle names, which typically are asset bundle class names without leading backslash.
     * The array values are the corresponding configurations. If a value is false, it means the corresponding asset
     * bundle is disabled and [[getBundle()]] should return null.
     *
     * If this this property is false, it means the whole asset bundle feature is disabled and [[getBundle()]]
     * will always return null.
     *
     * The following example shows how to disable the bootstrap css file used by Bootstrap widgets
     * (because you want to use your own styles):
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
    /**
     * @var array mapping from source asset files (keys) to target asset files (values).
     * When an asset bundle is being loaded by [[getBundle()]], each of its asset files (listed in either
     * [[AssetBundle::css]] or [[AssetBundle::js]] will be examined to see if it matches any key
     * in this map. If so, the corresponding value will be used to replace the asset file.
     *
     * Note that the target asset files should be either absolute URLs or paths relative to [[baseUrl]] and [[basePath]].
     *
     * In the following example, any occurrence of `jquery.min.js` will be replaced with `jquery/dist/jquery.js`.
     *
     * ```php
     * [
     *     'jquery.min.js' => 'jquery/dist/jquery.js',
     * ]
     * ```
     */
    public $assetMap = [];

    private $_dummyBundles = [];


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
            return $this->loadDummyBundle($name);
        } elseif (!isset($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name);
        } elseif ($this->bundles[$name] instanceof AssetBundle) {
            return $this->bundles[$name];
        } elseif (is_array($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, $this->bundles[$name]);
        } elseif ($this->bundles[$name] === false) {
            return $this->loadDummyBundle($name);
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

    protected function loadDummyBundle($name)
    {
        if (!isset($this->_dummyBundles[$name])) {
            $this->_dummyBundles[$name] = $this->loadBundle($name, [
                'js' => [],
                'css' => [],
                'depends' => [],
            ]);
        }
        return $this->_dummyBundles[$name];
    }

    public function resolveAsset($asset)
    {
        if (isset($this->assetMap[$asset])) {
            return $this->assetMap[$asset];
        }

        $n = strlen($asset);
        foreach ($this->assetMap as $from => $to) {
            $n2 = strlen($from);
            if ($n2 <= $n && substr_compare($asset, $from, $n - $n2, $n2) === 0) {
                return $to;
            }
        }

        return false;
    }

    public function getAssetUrl($asset)
    {
        return $this->baseUrl . '/' . ltrim($asset, '/');
    }

    public function getAssetPath($asset)
    {
        return $this->basePath . '/' . ltrim($asset, '/');
    }
}
