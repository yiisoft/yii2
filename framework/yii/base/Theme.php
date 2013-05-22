<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

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
 * To use a theme, you should configure the [[View::theme|theme]] property of the "view" application
 * component like the following:
 *
 * ~~~
 * 'view' => array(
 *     'theme' => array(
 *         'basePath' => '@wwwroot/themes/basic',
 *         'baseUrl' => '@www/themes/basic',
 *     ),
 * ),
 * ~~~
 *
 * The above configuration specifies a theme located under the "themes/basic" directory of the Web folder
 * that contains the entry script of the application. If your theme is designed to handle modules,
 * you may configure the [[pathMap]] property like described above.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Theme extends Component
{
	/**
	 * @var string the root path or path alias of this theme. All resources of this theme are located
	 * under this directory. This property must be set if [[pathMap]] is not set.
	 * @see pathMap
	 */
	public $basePath;
	/**
	 * @var string the base URL (or path alias) for this theme. All resources of this theme are considered
	 * to be under this base URL. This property must be set. It is mainly used by [[getUrl()]].
	 */
	public $baseUrl;
	/**
	 * @var array the mapping between view directories and their corresponding themed versions.
	 * If not set, it will be initialized as a mapping from [[Application::basePath]] to [[basePath]].
	 * This property is used by [[applyTo()]] when a view is trying to apply the theme.
	 * Path aliases can be used when specifying directories.
	 */
	public $pathMap;


	/**
	 * Initializes the theme.
	 * @throws InvalidConfigException if [[basePath]] is not set.
	 */
	public function init()
	{
	 	parent::init();
		if (empty($this->pathMap)) {
			if ($this->basePath !== null) {
				$this->basePath = Yii::getAlias($this->basePath);
				$this->pathMap = array(Yii::$app->getBasePath() => $this->basePath);
			} else {
				throw new InvalidConfigException('The "basePath" property must be set.');
			}
		}
		$paths = array();
		foreach ($this->pathMap as $from => $to) {
			$from = FileHelper::normalizePath(Yii::getAlias($from));
			$to = FileHelper::normalizePath(Yii::getAlias($to));
			$paths[$from . DIRECTORY_SEPARATOR] = $to . DIRECTORY_SEPARATOR;
		}
		$this->pathMap = $paths;
		if ($this->baseUrl === null) {
			throw new InvalidConfigException('The "baseUrl" property must be set.');
		} else {
			$this->baseUrl = rtrim(Yii::getAlias($this->baseUrl), '/');
		}
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
	 * Converts a relative URL into an absolute URL using [[baseUrl]].
	 * @param string $url the relative URL to be converted.
	 * @return string the absolute URL
	 */
	public function getUrl($url)
	{
		return $this->baseUrl . '/' . ltrim($url, '/');
	}
}
