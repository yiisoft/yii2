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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetManager extends Component
{
	/**
	 * @var array list of available asset bundles. The keys are the bundle names, and the values are the configuration
	 * arrays for creating the [[AssetBundle]] objects.
	 */
	public $bundles;
	/**
	 * @return string the root directory storing the published asset files.
	 */
	public $basePath = '@wwwroot/assets';
	/**
	 * @return string the base URL through which the published asset files can be accessed.
	 */
	public $baseUrl = '@www/assets';
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
	 * This value will be used by PHP chmod() function.
	 * If not set, the permission will be determined by the current environment.
	 */
	public $fileMode;
	/**
	 * @var integer the permission to be set for newly generated asset directories.
	 * This value will be used by PHP chmod() function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 */
	public $dirMode = 0777;

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

		foreach (require(YII_PATH . '/assets.php') as $name => $bundle) {
			if (!isset($this->bundles[$name])) {
				$this->bundles[$name] = $bundle;
			}
		}
	}

	/**
	 * Returns the named bundle.
	 * This method will first look for the bundle in [[bundles]]. If not found,
	 * it will attempt to find the bundle from an installed extension using the following procedure:
	 *
	 * 1. Convert the bundle into a path alias;
	 * 2. Determine the root alias and use it to locate the bundle manifest file "assets.php";
	 * 3. Look for the bundle in the manifest file.
	 *
	 * For example, given the bundle name "foo/button", the method will first convert it
	 * into the path alias "@foo/button"; since "@foo" is the root alias, it will look
	 * for the bundle manifest file "@foo/assets.php". The manifest file should return an array
	 * that lists the bundles used by the "foo/button" extension. The array format is the same as [[bundles]].
	 *
	 * @param string $name the bundle name
	 * @return AssetBundle the loaded bundle object. Null is returned if the bundle does not exist.
	 */
	public function getBundle($name)
	{
		if (!isset($this->bundles[$name])) {
			$rootAlias = Yii::getRootAlias("@$name");
			if ($rootAlias !== false) {
				$manifest = Yii::getAlias("$rootAlias/assets.php", false);
				if ($manifest !== false && is_file($manifest)) {
					foreach (require($manifest) as $bn => $config) {
						$this->bundles[$bn] = $config;
					}
				}
			}
			if (!isset($this->bundles[$name])) {
				return null;
			}
		}
		if (is_array($this->bundles[$name])) {
			$config = $this->bundles[$name];
			if (!isset($config['class'])) {
				$config['class'] = 'yii\\web\\AssetBundle';
				$this->bundles[$name] = Yii::createObject($config);
			}
		}

		return $this->bundles[$name];
	}

	private $_converter;

	/**
	 * Returns the asset converter.
	 * @return IAssetConverter the asset converter.
	 */
	public function getConverter()
	{
		if ($this->_converter === null) {
			$this->_converter = Yii::createObject(array(
				'class' => 'yii\\web\\AssetConverter',
			));
		} elseif (is_array($this->_converter) || is_string($this->_converter)) {
			$this->_converter = Yii::createObject($this->_converter);
		}
		return $this->_converter;
	}

	/**
	 * Sets the asset converter.
	 * @param array|IAssetConverter $value the asset converter. This can be either
	 * an object implementing the [[IAssetConverter]] interface, or a configuration
	 * array that can be used to create the asset converter object.
	 */
	public function setConverter($value)
	{
		$this->_converter = $value;
	}

	/**
	 * @var array published assets
	 */
	private $_published = array();

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
	 * @param array $options the options to	be applied when publishing a directory.
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
	public function publish($path, $options = array())
	{
		if (isset($this->_published[$path])) {
			return $this->_published[$path];
		}

		$src = realpath($path);
		if ($src === false) {
			throw new InvalidParamException("The file or directory to be published does not exist: $path");
		}

		if (is_file($src)) {
			$dir = $this->hash(dirname($src) . filemtime($src));
			$fileName = basename($src);
			$dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
			$dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

			if (!is_dir($dstDir)) {
				mkdir($dstDir, $this->dirMode, true);
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

			return $this->_published[$path] = array($dstFile, $this->baseUrl . "/$dir/$fileName");
		} else {
			$dir = $this->hash($src . filemtime($src));
			$dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
			if ($this->linkAssets) {
				if (!is_dir($dstDir)) {
					symlink($src, $dstDir);
				}
			} elseif (!is_dir($dstDir) || !empty($options['forceCopy'])) {
				$opts = array(
					'dirMode' => $this->dirMode,
					'fileMode' => $this->fileMode,
				);
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

			return $this->_published[$path] = array($dstDir, $this->baseUrl . '/' . $dir);
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
		if (($path = realpath($path)) !== false) {
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
		if (isset($this->_published[$path])) {
			return $this->_published[$path];
		}
		if (($path = realpath($path)) !== false) {
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
