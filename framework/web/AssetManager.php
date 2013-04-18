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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AssetManager extends Component
{
	/**
	 * @var array list of asset bundles. The keys are the bundle names, and the values are the configuration
	 * arrays for creating [[AssetBundle]] objects. Besides the bundles listed here, the asset manager
	 * may look for bundles declared in extensions. For more details, please refer to [[getBundle()]].
	 */
	public $bundles;
	public $bundleClass;
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
	 * @var array list of directories and files which should be excluded from the publishing process.
	 * Defaults to exclude '.svn', '.gitignore' and '.hgignore' files only. This option has no effect if {@link linkAssets} is enabled.
	 **/
	public $excludeFiles = array('.svn', '.gitignore', '.hgignore');
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
	 * @param string $name
	 * @return AssetBundle
	 * @throws InvalidParamException
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
				throw new InvalidParamException("Unable to find the asset bundle: $name");
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
	 * Note: On rare scenario, a race condition can develop that will lead to a
	 * one-time-manifestation of a non-critical problem in the creation of the directory
	 * that holds the published assets. This problem can be avoided altogether by 'requesting'
	 * in advance all the resources that are supposed to trigger a 'publish()' call, and doing
	 * that in the application deployment phase, before system goes live. See more in the following
	 * discussion: http://code.google.com/p/yii/issues/detail?id=2579
	 *
	 * @param string $path the asset (file or directory) to be published
	 * @param boolean $forceCopy whether the asset should ALWAYS be copied even if it is found
	 * in the target directory. This parameter is mainly useful during the development stage
	 * when the original assets are being constantly changed. The consequence is that the performance
	 * is degraded, which is not a concern during development, however.
	 * @return array the path (directory or file path) and the URL that the asset is published as.
	 * @throws InvalidParamException if the asset to be published does not exist.
	 */
	public function publish($path, $forceCopy = false)
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
				@mkdir($dstDir, $this->dirMode, true);
			}


			if ($this->linkAssets) {
				if (!is_file($dstFile)) {
					symlink($src, $dstFile);
				}
			} elseif (@filemtime($dstFile) < @filemtime($src) || $forceCopy) {
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
			} elseif (!is_dir($dstDir) || $forceCopy) {
				FileHelper::copyDirectory($src, $dstDir, array(
					'dirMode' => $this->dirMode,
					'fileMode' => $this->fileMode,
				));
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
