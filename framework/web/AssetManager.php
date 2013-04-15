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
	/**
	 * @var
	 */
	public $bundleMap;
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
	 * Defaults to exclude '.svn' and '.gitignore' files only. This option has no effect if {@link linkAssets} is enabled.
	 * @since 1.1.6
	 **/
	public $excludeFiles = array('.svn', '.gitignore');
	/**
	 * @var integer the permission to be set for newly generated asset files.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0666, meaning the file is read-writable by all users.
	 * @since 1.1.8
	 */
	public $newFileMode = 0666;
	/**
	 * @var integer the permission to be set for newly generated asset directories.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 * @since 1.1.8
	 */
	public $newDirMode = 0777;
	/**
	 * @var array published assets
	 */
	private $_published = array();

	public function init()
	{
		parent::init();
		$this->basePath = Yii::getAlias($this->basePath);
		if (!is_dir($this->basePath)) {
			throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
		} elseif (!is_writable($this->basePath)) {
			throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
		} else {
			$this->base = realpath($this->basePath);
		}
		$this->baseUrl = rtrim(Yii::getAlias($this->getBaseUrl), '/');
	}

	/**
	 * @param string $name
	 * @return AssetBundle
	 * @throws InvalidParamException
	 */
	public function getBundle($name)
	{
		if (!isset($this->bundles[$name])) {
			$manifest = Yii::getAlias("@{$name}/assets.php", false);
			if ($manifest === false) {
				throw new InvalidParamException("Unable to find the asset bundle: $name");
			}
			$this->bundles[$name] = require($manifest);
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
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, in case $forceCopy is false the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 *
	 * Note: On rare scenario, a race condition can develop that will lead to a
	 * one-time-manifestation of a non-critical problem in the creation of the directory
	 * that holds the published assets. This problem can be avoided altogether by 'requesting'
	 * in advance all the resources that are supposed to trigger a 'publish()' call, and doing
	 * that in the application deployment phase, before system goes live. See more in the following
	 * discussion: http://code.google.com/p/yii/issues/detail?id=2579
	 *
	 * @param string $path the asset (file or directory) to be published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @param integer $level level of recursive copying when the asset is a directory.
	 * Level -1 means publishing all subdirectories and files;
	 * Level 0 means publishing only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @param boolean $forceCopy whether we should copy the asset file or directory even if it is already published before.
	 * This parameter is set true mainly during development stage when the original
	 * assets are being constantly changed. The consequence is that the performance
	 * is degraded, which is not a concern during development, however.
	 * This parameter has been available since version 1.1.2.
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 */
	public function publish($path, $hashByName = false, $level = -1, $forceCopy = false)
	{
		if (isset($this->_published[$path])) {
			return $this->_published[$path];
		} else {
			if (($src = realpath($path)) !== false) {
				if (is_file($src)) {
					$dir = $this->hash($hashByName ? basename($src) : dirname($src) . filemtime($src));
					$fileName = basename($src);
					$dstDir = $this->getBasePath() . DIRECTORY_SEPARATOR . $dir;
					$dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

					if ($this->linkAssets) {
						if (!is_file($dstFile)) {
							if (!is_dir($dstDir)) {
								mkdir($dstDir);
								@chmod($dstDir, $this->newDirMode);
							}
							symlink($src, $dstFile);
						}
					} else {
						if (@filemtime($dstFile) < @filemtime($src)) {
							if (!is_dir($dstDir)) {
								mkdir($dstDir);
								@chmod($dstDir, $this->newDirMode);
							}
							copy($src, $dstFile);
							@chmod($dstFile, $this->newFileMode);
						}
					}

					return $this->_published[$path] = $this->getBaseUrl() . "/$dir/$fileName";
				} else {
					if (is_dir($src)) {
						$dir = $this->hash($hashByName ? basename($src) : $src . filemtime($src));
						$dstDir = $this->getBasePath() . DIRECTORY_SEPARATOR . $dir;

						if ($this->linkAssets) {
							if (!is_dir($dstDir)) {
								symlink($src, $dstDir);
							}
						} else {
							if (!is_dir($dstDir) || $forceCopy) {
								CFileHelper::copyDirectory($src, $dstDir, array(
									'exclude' => $this->excludeFiles,
									'level' => $level,
									'newDirMode' => $this->newDirMode,
									'newFileMode' => $this->newFileMode,
								));
							}
						}

						return $this->_published[$path] = $this->getBaseUrl() . '/' . $dir;
					}
				}
			}
		}
		throw new CException(Yii::t('yii|The asset "{asset}" to be published does not exist.',
			array('{asset}' => $path)));
	}

	/**
	 * Returns the published path of a file path.
	 * This method does not perform any publishing. It merely tells you
	 * if the file or directory is published, where it will go.
	 * @param string $path directory or file path being published
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published file path. False if the file or directory does not exist
	 */
	public function getPublishedPath($path, $hashByName = false)
	{
		if (($path = realpath($path)) !== false) {
			$base = $this->getBasePath() . DIRECTORY_SEPARATOR;
			if (is_file($path)) {
				return $base . $this->hash($hashByName ? basename($path) : dirname($path) . filemtime($path)) . DIRECTORY_SEPARATOR . basename($path);
			} else {
				return $base . $this->hash($hashByName ? basename($path) : $path . filemtime($path));
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
	 * @param boolean $hashByName whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hash taken from dirname of the path being published and path mtime.
	 * Defaults to false. Set true if the path being published is shared among
	 * different extensions.
	 * @return string the published URL for the file or directory. False if the file or directory does not exist.
	 */
	public function getPublishedUrl($path, $hashByName = false)
	{
		if (isset($this->_published[$path])) {
			return $this->_published[$path];
		}
		if (($path = realpath($path)) !== false) {
			if (is_file($path)) {
				return $this->getBaseUrl() . '/' . $this->hash($hashByName ? basename($path) : dirname($path) . filemtime($path)) . '/' . basename($path);
			} else {
				return $this->getBaseUrl() . '/' . $this->hash($hashByName ? basename($path) : $path . filemtime($path));
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
