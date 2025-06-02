<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\helpers\Url;

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
 * For more details and usage information on AssetManager, see the [guide article on assets](guide:structure-assets).
 *
 * @property AssetConverterInterface $converter The asset converter. Note that the type of this property
 * differs in getter and setter. See [[getConverter()]] and [[setConverter()]] for details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @phpstan-type PublishOptions array{
 *     only?: string[],
 *     except?: string[],
 *     caseSensitive?: bool,
 *     beforeCopy?: callable,
 *     afterCopy?: callable,
 *     forceCopy?: bool,
 * }
 *
 * @psalm-type PublishOptions = array{
 *     only?: string[],
 *     except?: string[],
 *     caseSensitive?: bool,
 *     beforeCopy?: callable,
 *     afterCopy?: callable,
 *     forceCopy?: bool,
 * }
 */
class AssetManager extends Component
{
    /**
     * @var array|false list of asset bundle configurations. This property is provided to customize asset bundles.
     * When a bundle is being loaded by [[getBundle()]], if it has a corresponding configuration specified here,
     * the configuration will be applied to the bundle.
     *
     * The array keys are the asset bundle names, which typically are asset bundle class names without leading backslash.
     * The array values are the corresponding configurations. If a value is false, it means the corresponding asset
     * bundle is disabled and [[getBundle()]] should return null.
     *
     * If this property is false, it means the whole asset bundle feature is disabled and [[getBundle()]]
     * will always return null.
     *
     * The following example shows how to disable the bootstrap css file used by Bootstrap widgets
     * (because you want to use your own styles):
     *
     * ```php
     * [
     *     'yii\bootstrap\BootstrapAsset' => [
     *         'css' => [],
     *     ],
     * ]
     * ```
     */
    public $bundles = [];
    /**
     * @var string the root directory storing the published asset files.
     */
    public $basePath = '@webroot/assets';
    /**
     * @var string the base URL through which the published asset files can be accessed.
     */
    public $baseUrl = '@web/assets';
    /**
     * @var string[] mapping from source asset files (keys) to target asset files (values).
     *
     * This property is provided to support fixing incorrect asset file paths in some asset bundles.
     * When an asset bundle is registered with a view, each relative asset file in its [[AssetBundle::css|css]]
     * and [[AssetBundle::js|js]] arrays will be examined against this map. If any of the keys is found
     * to be the last part of an asset file (which is prefixed with [[AssetBundle::sourcePath]] if available),
     * the corresponding value will replace the asset and be registered with the view.
     * For example, an asset file `my/path/to/jquery.js` matches a key `jquery.js`.
     *
     * Note that the target asset files should be absolute URLs, domain relative URLs (starting from '/') or paths
     * relative to [[baseUrl]] and [[basePath]].
     *
     * In the following example, any assets ending with `jquery.min.js` will be replaced with `jquery/dist/jquery.js`
     * which is relative to [[baseUrl]] and [[basePath]].
     *
     * ```php
     * [
     *     'jquery.min.js' => 'jquery/dist/jquery.js',
     * ]
     * ```
     *
     * You may also use aliases while specifying map value, for example:
     *
     * ```php
     * [
     *     'jquery.min.js' => '@web/js/jquery/jquery.js',
     * ]
     * ```
     */
    public $assetMap = [];
    /**
     * @var bool whether to use symbolic link to publish asset files. Defaults to false, meaning
     * asset files are copied to [[basePath]]. Using symbolic links has the benefit that the published
     * assets will always be consistent with the source assets and there is no copy operation required.
     * This is especially useful during development.
     *
     * However, there are special requirements for hosting environments in order to use symbolic links.
     * In particular, symbolic links are supported only on Linux/Unix, and Windows Vista/2008 or greater.
     *
     * Moreover, some Web servers need to be properly configured so that the linked assets are accessible
     * to Web users. For example, for Apache Web server, the following configuration directive should be added
     * for the Web folder:
     *
     * ```apache
     * Options FollowSymLinks
     * ```
     */
    public $linkAssets = false;
    /**
     * @var int|null the permission to be set for newly published asset files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var int the permission to be set for newly generated asset directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;
    /**
     * @var callable|null a PHP callback that is called before copying each sub-directory or file.
     * This option is used only when publishing a directory. If the callback returns false, the copy
     * operation for the sub-directory or file will be cancelled.
     *
     * The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
     * file to be copied from, while `$to` is the copy target.
     *
     * This is passed as a parameter `beforeCopy` to [[\yii\helpers\FileHelper::copyDirectory()]].
     */
    public $beforeCopy;
    /**
     * @var callable|null a PHP callback that is called after a sub-directory or file is successfully copied.
     * This option is used only when publishing a directory. The signature of the callback is the same as
     * for [[beforeCopy]].
     * This is passed as a parameter `afterCopy` to [[\yii\helpers\FileHelper::copyDirectory()]].
     */
    public $afterCopy;
    /**
     * @var bool whether the directory being published should be copied even if
     * it is found in the target directory. This option is used only when publishing a directory.
     * You may want to set this to be `true` during the development stage to make sure the published
     * directory is always up-to-date. Do not set this to true on production servers as it will
     * significantly degrade the performance.
     */
    public $forceCopy = false;
    /**
     * @var bool whether to append a timestamp to the URL of every published asset. When this is true,
     * the URL of a published asset may look like `/path/to/asset?v=timestamp`, where `timestamp` is the
     * last modification time of the published asset file.
     * You normally would want to set this property to true when you have enabled HTTP caching for assets,
     * because it allows you to bust caching when the assets are updated.
     * @since 2.0.3
     */
    public $appendTimestamp = false;
    /**
     * @var callable|null a callback that will be called to produce hash for asset directory generation.
     * The signature of the callback should be as follows:
     *
     * ```
     * function ($path)
     * ```
     *
     * where `$path` is the asset path. Note that the `$path` can be either directory where the asset
     * files reside or a single file. For a CSS file that uses relative path in `url()`, the hash
     * implementation should use the directory path of the file instead of the file path to include
     * the relative asset files in the copying.
     *
     * If this is not set, the asset manager will use the default CRC32 and filemtime in the `hash`
     * method.
     *
     * Example of an implementation using MD4 hash:
     *
     * ```php
     * function ($path) {
     *     return hash('md4', $path);
     * }
     * ```
     *
     * @since 2.0.6
     */
    public $hashCallback;

    /**
     * @var array
     */
    private $_dummyBundles = [];


    /**
     * Initializes the component.
     * @throws InvalidConfigException if [[basePath]] does not exist.
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);

        $this->basePath = realpath($this->basePath);
        $this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
    }

    /**
     * @var bool|null
     */
    private $_isBasePathPermissionChecked;

    /**
     * Check whether the basePath exists and is writeable.
     *
     * @since 2.0.40
     */
    public function checkBasePathPermission()
    {
        // if the check is been done already, skip further checks
        if ($this->_isBasePathPermissionChecked) {
            return;
        }

        if (!is_dir($this->basePath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        }

        if (!is_writable($this->basePath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
        }

        $this->_isBasePathPermissionChecked = true;
    }

    /**
     * Returns the named asset bundle.
     *
     * This method will first look for the bundle in [[bundles]]. If not found,
     * it will treat `$name` as the class of the asset bundle and create a new instance of it.
     *
     * @param string $name the class name of the asset bundle (without the leading backslash)
     * @param bool $publish whether to publish the asset files in the asset bundle before it is returned.
     * If you set this false, you must manually call `AssetBundle::publish()` to publish the asset files.
     * @return AssetBundle the asset bundle instance
     * @throws InvalidConfigException if $name does not refer to a valid asset bundle
     */
    public function getBundle($name, $publish = true)
    {
        if ($this->bundles === false) {
            return $this->loadDummyBundle($name);
        } elseif (!isset($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, [], $publish);
        } elseif ($this->bundles[$name] instanceof AssetBundle) {
            return $this->bundles[$name];
        } elseif (is_array($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, $this->bundles[$name], $publish);
        } elseif ($this->bundles[$name] === false) {
            return $this->loadDummyBundle($name);
        }

        throw new InvalidConfigException("Invalid asset bundle configuration: $name");
    }

    /**
     * Loads asset bundle class by name.
     *
     * @param string $name bundle name
     * @param array $config bundle object configuration
     * @param bool $publish if bundle should be published
     * @return AssetBundle
     * @throws InvalidConfigException if configuration isn't valid
     */
    protected function loadBundle($name, $config = [], $publish = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = $name;
        }
        /** @var AssetBundle $bundle */
        $bundle = Yii::createObject($config);
        if ($publish) {
            $bundle->publish($this);
        }

        return $bundle;
    }

    /**
     * Loads dummy bundle by name.
     *
     * @param string $name
     * @return AssetBundle
     */
    protected function loadDummyBundle($name)
    {
        if (!isset($this->_dummyBundles[$name])) {
            $bundle = Yii::createObject(['class' => $name]);
            $bundle->sourcePath = null;
            $bundle->js = [];
            $bundle->css = [];

            $this->_dummyBundles[$name] = $bundle;
        }

        return $this->_dummyBundles[$name];
    }

    /**
     * Returns the actual URL for the specified asset.
     * The actual URL is obtained by prepending either [[AssetBundle::$baseUrl]] or [[AssetManager::$baseUrl]] to the given asset path.
     * @param AssetBundle $bundle the asset bundle which the asset file belongs to
     * @param string $asset the asset path. This should be one of the assets listed in [[AssetBundle::$js]] or [[AssetBundle::$css]].
     * @param bool|null $appendTimestamp Whether to append timestamp to the URL.
     * @return string the actual URL for the specified asset.
     */
    public function getAssetUrl($bundle, $asset, $appendTimestamp = null)
    {
        $assetUrl = $this->getActualAssetUrl($bundle, $asset);
        $assetPath = $this->getAssetPath($bundle, $asset);

        $withTimestamp = $this->appendTimestamp;
        if ($appendTimestamp !== null) {
            $withTimestamp = $appendTimestamp;
        }

        if ($withTimestamp && $assetPath && ($timestamp = @filemtime($assetPath)) > 0) {
            return "$assetUrl?v=$timestamp";
        }

        return $assetUrl;
    }

    /**
     * Returns the actual file path for the specified asset.
     * @param AssetBundle $bundle the asset bundle which the asset file belongs to
     * @param string $asset the asset path. This should be one of the assets listed in [[AssetBundle::$js]] or [[AssetBundle::$css]].
     * @return string|false the actual file path, or `false` if the asset is specified as an absolute URL
     */
    public function getAssetPath($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            return Url::isRelative($actualAsset) ? $this->basePath . '/' . $actualAsset : false;
        }

        return Url::isRelative($asset) ? $bundle->basePath . '/' . $asset : false;
    }

    /**
     * @param AssetBundle $bundle
     * @param string $asset
     * @return string|false
     */
    protected function resolveAsset($bundle, $asset)
    {
        if (isset($this->assetMap[$asset])) {
            return $this->assetMap[$asset];
        }
        if ($bundle->sourcePath !== null && Url::isRelative($asset)) {
            $asset = $bundle->sourcePath . '/' . $asset;
        }

        $n = mb_strlen($asset, Yii::$app->charset);
        foreach ($this->assetMap as $from => $to) {
            $n2 = mb_strlen($from, Yii::$app->charset);
            if ($n2 <= $n && substr_compare($asset, $from, $n - $n2, $n2) === 0) {
                return $to;
            }
        }

        return false;
    }

    /**
     * @var AssetConverterInterface
     */
    private $_converter;

    /**
     * Returns the asset converter.
     * @return AssetConverterInterface the asset converter.
     */
    public function getConverter()
    {
        if ($this->_converter === null) {
            $this->_converter = Yii::createObject(AssetConverter::className());
        } elseif (is_array($this->_converter) || is_string($this->_converter)) {
            if (is_array($this->_converter) && !isset($this->_converter['class'])) {
                $this->_converter['class'] = AssetConverter::className();
            }
            $this->_converter = Yii::createObject($this->_converter);
        }

        return $this->_converter;
    }

    /**
     * Sets the asset converter.
     * @param array|AssetConverterInterface $value the asset converter. This can be either
     * an object implementing the [[AssetConverterInterface]], or a configuration
     * array that can be used to create the asset converter object.
     */
    public function setConverter($value)
    {
        $this->_converter = $value;
    }

    /**
     * @var array published assets
     */
    private $_published = [];

    /**
     * Publishes a file or a directory.
     *
     * This method will copy the specified file or directory to [[basePath]] so that
     * it can be accessed via the Web server.
     *
     * If the asset is a file, its file modification time will be checked to avoid
     * unnecessary file copying.
     *
     * If the asset is a directory, all files and subdirectories under it will be published recursively.
     * Note, in case $forceCopy is false the method only checks the existence of the target
     * directory to avoid repetitive copying (which is very expensive).
     *
     * By default, when publishing a directory, subdirectories and files whose name starts with a dot "."
     * will NOT be published. If you want to change this behavior, you may specify the "beforeCopy" option
     * as explained in the `$options` parameter.
     *
     * Note: On rare scenario, a race condition can develop that will lead to a
     * one-time-manifestation of a non-critical problem in the creation of the directory
     * that holds the published assets. This problem can be avoided altogether by 'requesting'
     * in advance all the resources that are supposed to trigger a 'publish()' call, and doing
     * that in the application deployment phase, before system goes live. See more in the following
     * discussion: https://code.google.com/archive/p/yii/issues/2579
     *
     * @param string $path the asset (file or directory) to be published
     * @param array $options the options to be applied when publishing a directory.
     * The following options are supported:
     *
     * - only: array, list of patterns that the file paths should match if they want to be copied.
     * - except: array, list of patterns that the files or directories should match if they want to be excluded from being copied.
     * - caseSensitive: boolean, whether patterns specified at "only" or "except" should be case sensitive. Defaults to true.
     * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
     *   This overrides [[beforeCopy]] if set.
     * - afterCopy: callback, a PHP callback that is called after a sub-directory or file is successfully copied.
     *   This overrides [[afterCopy]] if set.
     * - forceCopy: boolean, whether the directory being published should be copied even if
     *   it is found in the target directory. This option is used only when publishing a directory.
     *   This overrides [[forceCopy]] if set.
     *
     * @return array the path (directory or file path) and the URL that the asset is published as.
     * @throws InvalidArgumentException if the asset to be published does not exist.
     * @throws InvalidConfigException if the target directory [[basePath]] is not writeable.
     *
     * @phpstan-param PublishOptions $options
     * @psalm-param PublishOptions $options
     */
    public function publish($path, $options = [])
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path];
        }

        if (!is_string($path) || ($src = realpath($path)) === false) {
            throw new InvalidArgumentException("The file or directory to be published does not exist: $path");
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException("The file or directory to be published is not readable: $path");
        }

        if (is_file($src)) {
            return $this->_published[$path] = $this->publishFile($src);
        }

        return $this->_published[$path] = $this->publishDirectory($src, $options);
    }

    /**
     * Publishes a file.
     * @param string $src the asset file to be published
     * @return string[] the path and the URL that the asset is published as.
     * @throws InvalidArgumentException if the asset to be published does not exist.
     */
    protected function publishFile($src)
    {
        $this->checkBasePathPermission();

        $dir = $this->hash($src);
        $fileName = basename($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($dstDir)) {
            FileHelper::createDirectory($dstDir, $this->dirMode, true);
        }

        if ($this->linkAssets) {
            if (!is_file($dstFile)) {
                try { // fix #6226 symlinking multi threaded
                    symlink($src, $dstFile);
                } catch (\Exception $e) {
                    if (!is_file($dstFile)) {
                        throw $e;
                    }
                }
            }
        } elseif (@filemtime($dstFile) < @filemtime($src)) {
            copy($src, $dstFile);
            if ($this->fileMode !== null) {
                @chmod($dstFile, $this->fileMode);
            }
        }

        if ($this->appendTimestamp && ($timestamp = @filemtime($dstFile)) > 0) {
            $fileName = $fileName . "?v=$timestamp";
        }

        return [$dstFile, $this->baseUrl . "/$dir/$fileName"];
    }

    /**
     * Publishes a directory.
     * @param string $src the asset directory to be published
     * @param array $options the options to be applied when publishing a directory.
     * The following options are supported:
     *
     * - only: array, list of patterns that the file paths should match if they want to be copied.
     * - except: array, list of patterns that the files or directories should match if they want to be excluded from being copied.
     * - caseSensitive: boolean, whether patterns specified at "only" or "except" should be case sensitive. Defaults to true.
     * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
     *   This overrides [[beforeCopy]] if set.
     * - afterCopy: callback, a PHP callback that is called after a sub-directory or file is successfully copied.
     *   This overrides [[afterCopy]] if set.
     * - forceCopy: boolean, whether the directory being published should be copied even if
     *   it is found in the target directory. This option is used only when publishing a directory.
     *   This overrides [[forceCopy]] if set.
     *
     * @return string[] the path directory and the URL that the asset is published as.
     * @throws InvalidArgumentException if the asset to be published does not exist.
     */
    protected function publishDirectory($src, $options)
    {
        $this->checkBasePathPermission();

        $dir = $this->hash($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        if ($this->linkAssets) {
            if (!is_dir($dstDir)) {
                FileHelper::createDirectory(dirname($dstDir), $this->dirMode, true);
                try { // fix #6226 symlinking multi threaded
                    symlink($src, $dstDir);
                } catch (\Exception $e) {
                    if (!is_dir($dstDir)) {
                        throw $e;
                    }
                }
            }
        } elseif (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !is_dir($dstDir)) {
            $opts = array_merge(
                $options,
                [
                    'dirMode' => $this->dirMode,
                    'fileMode' => $this->fileMode,
                    'copyEmptyDirectories' => false,
                ]
            );
            if (!isset($opts['beforeCopy'])) {
                if ($this->beforeCopy !== null) {
                    $opts['beforeCopy'] = $this->beforeCopy;
                } else {
                    $opts['beforeCopy'] = function ($from, $to) {
                        return strncmp(basename($from), '.', 1) !== 0;
                    };
                }
            }
            if (!isset($opts['afterCopy']) && $this->afterCopy !== null) {
                $opts['afterCopy'] = $this->afterCopy;
            }
            FileHelper::copyDirectory($src, $dstDir, $opts);
        }

        return [$dstDir, $this->baseUrl . '/' . $dir];
    }

    /**
     * Returns the published path of a file path.
     * This method does not perform any publishing. It merely tells you
     * if the file or directory is published, where it will go.
     * @param string $path directory or file path being published
     * @return string|false string the published file path. False if the file or directory does not exist
     */
    public function getPublishedPath($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][0];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->basePath . DIRECTORY_SEPARATOR . $this->hash($path) . (is_file($path) ? DIRECTORY_SEPARATOR . basename($path) : '');
        }

        return false;
    }

    /**
     * Returns the URL of a published file path.
     * This method does not perform any publishing. It merely tells you
     * if the file path is published, what the URL will be to access it.
     * @param string $path directory or file path being published
     * @return string|false string the published URL for the file or directory. False if the file or directory does not exist.
     */
    public function getPublishedUrl($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][1];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->baseUrl . '/' . $this->hash($path) . (is_file($path) ? '/' . basename($path) : '');
        }

        return false;
    }

    /**
     * Generate a CRC32 hash for the directory path. Collisions are higher
     * than MD5 but generates a much smaller hash string.
     * @param string $path string to be hashed.
     * @return string hashed string.
     */
    protected function hash($path)
    {
        if (is_callable($this->hashCallback)) {
            return call_user_func($this->hashCallback, $path);
        }
        $path = (is_file($path) ? dirname($path) : $path) . filemtime($path);
        return sprintf('%x', crc32($path . Yii::getVersion() . '|' . $this->linkAssets));
    }

    /**
     * Returns the actual URL for the specified asset. Without parameters.
     * The actual URL is obtained by prepending either [[AssetBundle::$baseUrl]] or [[AssetManager::$baseUrl]] to the given asset path.
     * @param AssetBundle $bundle the asset bundle which the asset file belongs to
     * @param string $asset the asset path. This should be one of the assets listed in [[AssetBundle::$js]] or [[AssetBundle::$css]].
     * @return string the actual URL for the specified asset.
     * @since 2.0.39
     */
    public function getActualAssetUrl($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            if (strncmp($actualAsset, '@web/', 5) === 0) {
                $asset = substr($actualAsset, 5);
                $baseUrl = Yii::getAlias('@web');
            } else {
                $asset = Yii::getAlias($actualAsset);
                $baseUrl = $this->baseUrl;
            }
        } else {
            $baseUrl = $bundle->baseUrl;
        }

        if (!Url::isRelative($asset) || strncmp($asset, '/', 1) === 0) {
            return $asset;
        }

        return "$baseUrl/$asset";
    }
}
