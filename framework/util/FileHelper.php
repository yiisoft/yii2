<?php
/**
 * Filesystem helper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use yii\base\Exception;

/**
 * Filesystem helper
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class FileHelper
{
	/**
	 * Returns the extension name of a file path.
	 * For example, the path "path/to/something.php" would return "php".
	 * @param string $path the file path
	 * @return string the extension name without the dot character.
	 */
	public static function getExtension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Checks the given path and ensures it is a directory.
	 * This method will call `realpath()` to "normalize" the given path.
	 * If the given path does not refer to an existing directory, an exception will be thrown.
	 * @param string $path the given path. This can also be a path alias.
	 * @return string the normalized path
	 * @throws Exception if the path does not refer to an existing directory.
	 */
	public static function ensureDirectory($path)
	{
		$p = \Yii::getAlias($path);
		if ($p !== false && ($p = realpath($p)) !== false && is_dir($p)) {
			return $p;
		} else {
			throw new Exception('Directory does not exist: ' . $path);
		}
	}

	/**
	 * Returns the localized version of a specified file.
	 *
	 * The searching is based on the specified language code. In particular,
	 * a file with the same name will be looked for under the subdirectory
	 * whose name is same as the language code. For example, given the file "path/to/view.php"
	 * and language code "zh_cn", the localized file will be looked for as
	 * "path/to/zh_cn/view.php". If the file is not found, the original file
	 * will be returned.
	 *
	 * If the target and the source language codes are the same,
	 * the original file will be returned.
	 *
	 * For consistency, it is recommended that the language code is given
	 * in lower case and in the format of LanguageID_RegionID (e.g. "en_us").
	 *
	 * @param string $file the original file
	 * @param string $language the target language that the file should be localized to.
	 * If not set, the value of [[\yii\base\Application::language]] will be used.
	 * @param string $sourceLanguage the language that the original file is in.
	 * If not set, the value of [[\yii\base\Application::sourceLanguage]] will be used.
	 * @return string the matching localized file, or the original file if the localized version is not found.
	 * If the target and the source language codes are the same, the original file will be returned.
	 */
	public static function localize($file, $language = null, $sourceLanguage = null)
	{
		if ($language === null) {
			$language = \Yii::$application->getLanguage();
		}
		if ($sourceLanguage === null) {
			$sourceLanguage = \Yii::$application->sourceLanguage;
		}
		if ($language === $sourceLanguage) {
			return $file;
		}
		$desiredFile = dirname($file) . DIRECTORY_SEPARATOR . $sourceLanguage . DIRECTORY_SEPARATOR . basename($file);
		return is_file($desiredFile) ? $desiredFile : $file;
	}
}
