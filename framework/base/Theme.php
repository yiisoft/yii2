<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\base\InvalidConfigException;
use yii\util\FileHelper;

/**
 * Theme represents an application theme.
 *
 * A theme is directory consisting of view and layout files which are meant to replace their
 * non-themed counterparts.
 *
 * Theme uses [[pathMap]] to achieve the file replacement. A view or layout file will be replaced
 * with its themed version if part of its path matches one of the keys in [[pathMap]].
 * Then the matched part will be replaced with the corresponding array value.
 *
 * For example, if [[pathMap]] is `array('/www/views' => '/www/themes/basic')`,
 * then the themed version for a view file `/www/views/site/index.php` will be
 * `/www/themes/basic/site/index.php`.
 *
 * @property string $baseUrl the base URL for this theme. This is mainly used by [[getUrl()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Theme extends Component
{
	/**
	 * @var string the root path of this theme.
	 * @see pathMap
	 */
	public $basePath;
	/**
	 * @var array the mapping between view directories and their corresponding themed versions.
	 * If not set, it will be initialized as a mapping from [[Application::basePath]] to [[basePath]].
	 * This property is used by [[applyTo()]] when a view is trying to apply the theme.
	 * Path aliases can be used when specifying directories.
	 */
	public $pathMap;

	private $_baseUrl;

	/**
	 * Initializes the theme.
	 * @throws InvalidConfigException if [[basePath]] is not set.
	 */
	public function init()
	{
	 	parent::init();
		if (empty($this->pathMap)) {
			if ($this->basePath !== null) {
				$this->basePath = FileHelper::ensureDirectory($this->basePath);
				$this->pathMap = array(Yii::$app->getBasePath() => $this->basePath);
			} else {
				throw new InvalidConfigException("Theme::basePath must be set.");
			}
		}
		$paths = array();
		foreach ($this->pathMap as $from => $to) {
			$from = FileHelper::normalizePath(Yii::getAlias($from));
			$to = FileHelper::normalizePath(Yii::getAlias($to));
			$paths[$from . DIRECTORY_SEPARATOR] = $to . DIRECTORY_SEPARATOR;
		}
		$this->pathMap = $paths;
	}

	/**
	 * Returns the base URL for this theme.
	 * The method [[getUrl()]] will prefix this to the given URL.
	 * @return string the base URL for this theme.
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * Sets the base URL for this theme.
	 * @param string $value the base URL for this theme.
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl = rtrim(Yii::getAlias($value), '/');
	}

	/**
	 * Converts a file to a themed file if possible.
	 * If there is no corresponding themed file, the original file will be returned.
	 * @param string $path the file to be themed
	 * @return string the themed file, or the original file if the themed version is not available.
	 */
	public function applyTo($path)
	{
		$path = FileHelper::normalizePath($path);
		foreach ($this->pathMap as $from => $to) {
			if (strpos($path, $from) === 0) {
				$n = strlen($from);
				$file = $to . substr($path, $n);
				if (is_file($file)) {
					return $file;
				}
			}
		}
		return $path;
	}

	/**
	 * Converts a relative URL into an absolute URL using [[basePath]].
	 * @param string $url the relative URL to be converted.
	 * @return string the absolute URL
	 */
	public function getUrl($url)
	{
		return $this->baseUrl . '/' . ltrim($url, '/');
	}
}
