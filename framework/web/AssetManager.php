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
    /**
     * @var boolean whether to use symbolic link to publish asset files. Defaults to false, meaning
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
     * ~~~
     * Options FollowSymLinks
     * ~~~
     */
    public $linkAssets = false;
    /**
     * @var integer the permission to be set for newly published asset files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode;
    /**
     * @var integer the permission to be set for newly generated asset directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * Initializes the component.
     * @throws InvalidConfigException if [[basePath]] is invalid
     */
    public function init()
    {
        parent::init();
        $this->basePath = Yii::getAlias($this->basePath);
        if (!is_dir($this->basePath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        } elseif (!is_writable($this->basePath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
        } else {
            $this->basePath = realpath($this->basePath);
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
     * @param boolean $publish whether to publish the asset files in the asset bundle before it is returned.
     * If you set this false, you must manually call `AssetBundle::publish()` to publish the asset files.
     * @return AssetBundle the asset bundle instance
     * @throws InvalidConfigException if $name does not refer to a valid asset bundle
     */
    public function getBundle($name, $publish = true)
    {
        if (isset($this->bundles[$name])) {
            if ($this->bundles[$name] instanceof AssetBundle) {
                return $this->bundles[$name];
            } elseif (is_array($this->bundles[$name])) {
                $bundle = Yii::createObject(array_merge(['class' => $name], $this->bundles[$name]));
            } else {
                throw new InvalidConfigException("Invalid asset bundle: $name");
            }
        } else {
            $bundle = Yii::createObject($name);
        }
        if ($publish) {
            /* @var $bundle AssetBundle */
            $bundle->publish($this);
        }

        return $this->bundles[$name] = $bundle;
    }

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
     * discussion: http://code.google.com/p/yii/issues/detail?id=2579
     *
     * @param string $path the asset (file or directory) to be published
     * @param array $options the options to be applied when publishing a directory.
     * The following options are supported:
     *
     * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
     *   This option is used only when publishing a directory. If the callback returns false, the copy
     *   operation for the sub-directory or file will be cancelled.
     *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
     *   file to be copied from, while `$to` is the copy target.
     * - afterCopy: callback, a PHP callback that is called after a sub-directory or file is successfully copied.
     *   This option is used only when publishing a directory. The signature of the callback is similar to that
     *   of `beforeCopy`.
     * - forceCopy: boolean, whether the directory being published should be copied even if
     *   it is found in the target directory. This option is used only when publishing a directory.
     *   You may want to set this to be true during the development stage to make sure the published
     *   directory is always up-to-date. Do not set this to true on production servers as it will
     *   significantly degrade the performance.
     * @return array the path (directory or file path) and the URL that the asset is published as.
     * @throws InvalidParamException if the asset to be published does not exist.
     */
    public function publish($path, $options = [])
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path];
        }

        if (!is_string($path) || ($src = realpath($path)) === false) {
            throw new InvalidParamException("The file or directory to be published does not exist: $path");
        }

        if (is_file($src)) {
            $dir = $this->hash(dirname($src) . filemtime($src));
            $fileName = basename($src);
            $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

            if (!is_dir($dstDir)) {
                FileHelper::createDirectory($dstDir, $this->dirMode, true);
            }

            if ($this->linkAssets) {
                if (!is_file($dstFile)) {
                    symlink($src, $dstFile);
                }
            } elseif (@filemtime($dstFile) < @filemtime($src)) {
                copy($src, $dstFile);
                if ($this->fileMode !== null) {
                    @chmod($dstFile, $this->fileMode);
                }
            }

            return $this->_published[$path] = [$dstFile, $this->baseUrl . "/$dir/$fileName"];
        } else {
            $dir = $this->hash($src . filemtime($src));
            $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
            if ($this->linkAssets) {
                if (!is_dir($dstDir)) {
                    symlink($src, $dstDir);
                }
            } elseif (!is_dir($dstDir) || !empty($options['forceCopy'])) {
                $opts = [
                    'dirMode' => $this->dirMode,
                    'fileMode' => $this->fileMode,
                ];
                if (isset($options['beforeCopy'])) {
                    $opts['beforeCopy'] = $options['beforeCopy'];
                } else {
                    $opts['beforeCopy'] = function ($from, $to) {
                        return strncmp(basename($from), '.', 1) !== 0;
                    };
                }
                if (isset($options['afterCopy'])) {
                    $opts['afterCopy'] = $options['afterCopy'];
                }
                FileHelper::copyDirectory($src, $dstDir, $opts);
            }

            return $this->_published[$path] = [$dstDir, $this->baseUrl . '/' . $dir];
        }
    }

    /**
     * Returns the published path of a file path.
     * This method does not perform any publishing. It merely tells you
     * if the file or directory is published, where it will go.
     * @param string $path directory or file path being published
     * @return string the published file path. False if the file or directory does not exist
     */
    public function getPublishedPath($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][0];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            $base = $this->basePath . DIRECTORY_SEPARATOR;
            if (is_file($path)) {
                return $base . $this->hash(dirname($path) . filemtime($path)) . DIRECTORY_SEPARATOR . basename($path);
            } else {
                return $base . $this->hash($path . filemtime($path));
            }
        } else {
            return false;
        }
    }

    /**
     * Returns the URL of a published file path.
     * This method does not perform any publishing. It merely tells you
     * if the file path is published, what the URL will be to access it.
     * @param string $path directory or file path being published
     * @return string the published URL for the file or directory. False if the file or directory does not exist.
     */
    public function getPublishedUrl($path)
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][1];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            if (is_file($path)) {
                return $this->baseUrl . '/' . $this->hash(dirname($path) . filemtime($path)) . '/' . basename($path);
            } else {
                return $this->baseUrl . '/' . $this->hash($path . filemtime($path));
            }
        } else {
            return false;
        }
    }

    /**
     * Generate a CRC32 hash for the directory path. Collisions are higher
     * than MD5 but generates a much smaller hash string.
     * @param string $path string to be hashed.
     * @return string hashed string.
     */
    protected function hash($path)
    {
        return sprintf('%x', crc32($path . Yii::getVersion()));
    }
}
