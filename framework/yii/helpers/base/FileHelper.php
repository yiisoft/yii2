<?php
/**
 * Filesystem helper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

use Yii;
use yii\helpers\StringHelper;

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
	 * Normalizes a file/directory path.
	 * After normalization, the directory separators in the path will be `DIRECTORY_SEPARATOR`,
	 * and any trailing directory separators will be removed. For example, '/home\demo/' on Linux
	 * will be normalized as '/home/demo'.
	 * @param string $path the file/directory path to be normalized
	 * @param string $ds the directory separator to be used in the normalized result. Defaults to `DIRECTORY_SEPARATOR`.
	 * @return string the normalized file/directory path
	 */
	public static function normalizePath($path, $ds = DIRECTORY_SEPARATOR)
	{
		return rtrim(strtr($path, array('/' => $ds, '\\' => $ds)), $ds);
	}

	/**
	 * Returns the localized version of a specified file.
	 *
	 * The searching is based on the specified language code. In particular,
	 * a file with the same name will be looked for under the subdirectory
	 * whose name is the same as the language code. For example, given the file "path/to/view.php"
	 * and language code "zh_CN", the localized file will be looked for as
	 * "path/to/zh_CN/view.php". If the file is not found, the original file
	 * will be returned.
	 *
	 * If the target and the source language codes are the same,
	 * the original file will be returned.
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
			$language = Yii::$app->language;
		}
		if ($sourceLanguage === null) {
			$sourceLanguage = Yii::$app->sourceLanguage;
		}
		if ($language === $sourceLanguage) {
			return $file;
		}
		$desiredFile = dirname($file) . DIRECTORY_SEPARATOR . $sourceLanguage . DIRECTORY_SEPARATOR . basename($file);
		return is_file($desiredFile) ? $desiredFile : $file;
	}

	/**
	 * Determines the MIME type of the specified file.
	 * This method will first try to determine the MIME type based on
	 * [finfo_open](http://php.net/manual/en/function.finfo-open.php). If this doesn't work, it will
	 * fall back to [[getMimeTypeByExtension()]].
	 * @param string $file the file name.
	 * @param string $magicFile name of the optional magic database file, usually something like `/path/to/magic.mime`.
	 * This will be passed as the second parameter to [finfo_open](http://php.net/manual/en/function.finfo-open.php).
	 * @param boolean $checkExtension whether to use the file extension to determine the MIME type in case
	 * `finfo_open()` cannot determine it.
	 * @return string the MIME type (e.g. `text/plain`). Null is returned if the MIME type cannot be determined.
	 */
	public static function getMimeType($file, $magicFile = null, $checkExtension = true)
	{
		if (function_exists('finfo_open')) {
			$info = finfo_open(FILEINFO_MIME_TYPE, $magicFile);
			if ($info) {
				$result = finfo_file($info, $file);
				finfo_close($info);
				if ($result !== false) {
					return $result;
				}
			}
		}

		return $checkExtension ? static::getMimeTypeByExtension($file) : null;
	}

	/**
	 * Determines the MIME type based on the extension name of the specified file.
	 * This method will use a local map between extension names and MIME types.
	 * @param string $file the file name.
	 * @param string $magicFile the path of the file that contains all available MIME type information.
	 * If this is not set, the default file aliased by `@yii/util/mimeTypes.php` will be used.
	 * @return string the MIME type. Null is returned if the MIME type cannot be determined.
	 */
	public static function getMimeTypeByExtension($file, $magicFile = null)
	{
		static $mimeTypes = array();
		if ($magicFile === null) {
			$magicFile = __DIR__ . '/mimeTypes.php';
		}
		if (!isset($mimeTypes[$magicFile])) {
			$mimeTypes[$magicFile] = require($magicFile);
		}
		if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
			$ext = strtolower($ext);
			if (isset($mimeTypes[$magicFile][$ext])) {
				return $mimeTypes[$magicFile][$ext];
			}
		}
		return null;
	}

	/**
	 * Copies a whole directory as another one.
	 * The files and sub-directories will also be copied over.
	 * @param string $src the source directory
	 * @param string $dst the destination directory
	 * @param array $options options for directory copy. Valid options are:
	 *
	 * - dirMode: integer, the permission to be set for newly copied directories. Defaults to 0777.
	 * - fileMode:  integer, the permission to be set for newly copied files. Defaults to the current environment setting.
	 * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
	 *   If the callback returns false, the copy operation for the sub-directory or file will be cancelled.
	 *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
	 *   file to be copied from, while `$to` is the copy target.
	 * - afterCopy: callback, a PHP callback that is called after a sub-directory or file is successfully copied.
	 *   The signature of the callback is similar to that of `beforeCopy`.
	 */
	public static function copyDirectory($src, $dst, $options = array())
	{
		if (!is_dir($dst)) {
			mkdir($dst, isset($options['dirMode']) ? $options['dirMode'] : 0777, true);
		}

		$handle = opendir($src);
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$from = $src . DIRECTORY_SEPARATOR . $file;
			$to = $dst . DIRECTORY_SEPARATOR . $file;
			if (!isset($options['beforeCopy']) || call_user_func($options['beforeCopy'], $from, $to)) {
				if (is_file($from)) {
					copy($from, $to);
					if (isset($options['fileMode'])) {
						@chmod($to, $options['fileMode']);
					}
				} else {
					static::copyDirectory($from, $to, $options);
				}
				if (isset($options['afterCopy'])) {
					call_user_func($options['afterCopy'], $from, $to);
				}
			}
		}
		closedir($handle);
	}

	/**
	 * Removes a directory (and all its content) recursively.
	 * @param string $dir the directory to be deleted recursively.
	 */
	public static function removeDirectory($dir)
	{
		$items = glob($dir . DIRECTORY_SEPARATOR . '{,.}*', GLOB_MARK | GLOB_BRACE);
		foreach ($items as $item) {
			$itemBaseName = basename($item);
			if ($itemBaseName === '.' || $itemBaseName === '..') {
				continue;
			}
			if (StringHelper::substr($item, -1, 1) == DIRECTORY_SEPARATOR) {
				static::removeDirectory($item);
			} else {
				unlink($item);
			}
		}
		if (is_dir($dir)) {
			rmdir($dir);
		}
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * @param string $dir the directory under which the files will be looked for.
	 * @param array $options options for file searching. Valid options are:
	 *
	 * - filter: callback, a PHP callback that is called for each sub-directory or file.
	 *   If the callback returns false, the the sub-directory or file will not be placed to the result set.
	 *   The signature of the callback should be: `function ($base, $name, $isFile)`, where `$base` is the name of directory,
	 *   which contains file or sub-directory, `$name` file or sub-directory name, `$isFile` indicates if object is a file or a directory.
	 * - fileTypes: array, list of file name suffix (without dot). Only files with these suffixes will be returned.
	 * - only: array, list of path names that the files or directories should match if they want to be put in the result set.
	 *   The matching is done in a partial manner. For example, the '.svn' will match all files and directories whose name ends with '.svn'.
	 *   And the name '/a/b' will match all files and directories ending with '/a/b'.
	 *   Note, that '/' should be used as separator regardless of the value of the DIRECTORY_SEPARATOR constant.
	 *   If a file/directory matches both a name in "only" and "except", it will NOT be put in the result set.
	 * - except: array, list of path names that the files or directories should NOT match if they want to be put in the result set.
	 * - recursive: boolean, whether the files should be looked recursively under all subdirectories.
	 *   Defaults to true.
	 * @return array files found under the directory. The file list is sorted.
	 */
	public static function findFiles($dir, $options = array())
	{
		$list = array();
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (static::validatePath($path, $options)) {
				if (is_file($path)) {
					$list[] = $path;
				} elseif (!isset($options['recursive']) || $options['recursive']) {
					$list = array_merge($list, static::findFiles($path, $options));
				}
			}
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Validates a file or directory, checking if it match given conditions.
	 * @param string $path the path of the file or directory to be validated
	 * @param array $options options for file searching.
	 * @return boolean whether the file or directory is valid
	 */
	protected static function validatePath($path, $options)
	{
		if (isset($options['filter']) && !call_user_func($options['filter'], $path)) {
			return false;
		}
		$path = str_replace('\\', '/', $path);
		$n = strlen($path);
		if (!empty($options['except'])) {
			foreach ($options['except'] as $name) {
				if (strrpos($path, $name) === $n - strlen($name)) {
					return false;
				}
			}
		}
		if (!empty($options['only'])) {
			foreach ($options['only'] as $name) {
				if (strrpos($path, $name) !== $n - strlen($name)) {
					return false;
				}
			}
		}
		if (!empty($options['fileTypes'])) {
			if (!is_file($path)) {
				return true;
			}
			if (($type = pathinfo($path, PATHINFO_EXTENSION)) !== '') {
				return in_array($type, $options['fileTypes']);
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Shared environment safe version of mkdir. Supports recursive creation.
	 * For avoidance of umask side-effects chmod is used.
	 *
	 * @param string $path path to be created.
	 * @param integer $mode  the permission to be set for created directory. If not set  0777 will be used.
	 * @param boolean $recursive whether to create directory structure recursive if parent dirs do not exist.
	 * @return boolean result of mkdir.
	 * @see mkdir
	 */
	public static function mkdir($path, $mode = null, $recursive = false)
	{
		$prevDir = dirname($path);
		if ($recursive && !is_dir($path) && !is_dir($prevDir)) {
			static::mkdir(dirname($path), $mode, true);
		}
		$mode = isset($mode) ? $mode : 0777;
		$result = mkdir($path, $mode);
		chmod($path, $mode);
		return $result;
	}
}
