<?php
/**
 * Filesystem helper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;

/**
 * BaseFileHelper provides concrete implementation for [[FileHelper]].
 *
 * Do not use BaseFileHelper. Use [[FileHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseFileHelper
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
		return rtrim(strtr($path, ['/' => $ds, '\\' => $ds]), $ds);
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
		$desiredFile = dirname($file) . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . basename($file);
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
		static $mimeTypes = [];
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
	 * - dirMode: integer, the permission to be set for newly copied directories. Defaults to 0775.
	 * - fileMode:  integer, the permission to be set for newly copied files. Defaults to the current environment setting.
	 * - filter: callback, a PHP callback that is called for each directory or file.
	 *   The signature of the callback should be: `function ($path)`, where `$path` refers the full path to be filtered.
	 *   The callback can return one of the following values:
	 *
	 *   * true: the directory or file will be copied (the "only" and "except" options will be ignored)
	 *   * false: the directory or file will NOT be copied (the "only" and "except" options will be ignored)
	 *   * null: the "only" and "except" options will determine whether the directory or file should be copied
	 *
	 * - only: array, list of patterns that the file paths should match if they want to be copied.
	 *   A path matches a pattern if it contains the pattern string at its end.
	 *   For example, '.php' matches all file paths ending with '.php'.
	 *   Note, the '/' characters in a pattern matches both '/' and '\' in the paths.
	 *   If a file path matches a pattern in both "only" and "except", it will NOT be copied.
	 * - except: array, list of patterns that the files or directories should match if they want to be excluded from being copied.
	 *   A path matches a pattern if it contains the pattern string at its end.
	 *   Patterns ending with '/' apply to directory paths only, and patterns not ending with '/'
	 *   apply to file paths only. For example, '/a/b' matches all file paths ending with '/a/b';
	 *   and '.svn/' matches directory paths ending with '.svn'. Note, the '/' characters in a pattern matches
	 *   both '/' and '\' in the paths.
	 * - recursive: boolean, whether the files under the subdirectories should also be copied. Defaults to true.
	 * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
	 *   If the callback returns false, the copy operation for the sub-directory or file will be cancelled.
	 *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
	 *   file to be copied from, while `$to` is the copy target.
	 * - afterCopy: callback, a PHP callback that is called after each sub-directory or file is successfully copied.
	 *   The signature of the callback should be: `function ($from, $to)`, where `$from` is the sub-directory or
	 *   file copied from, while `$to` is the copy target.
	 */
	public static function copyDirectory($src, $dst, $options = [])
	{
		if (!is_dir($dst)) {
			static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
		}

		$handle = opendir($src);
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$from = $src . DIRECTORY_SEPARATOR . $file;
			$to = $dst . DIRECTORY_SEPARATOR . $file;
			if (static::filterPath($from, $options)) {
				if (isset($options['beforeCopy']) && !call_user_func($options['beforeCopy'], $from, $to)) {
					continue;
				}
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
		if (!is_dir($dir) || !($handle = opendir($dir))) {
			return;
		}
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (is_file($path)) {
				unlink($path);
			} else {
				static::removeDirectory($path);
			}
		}
		closedir($handle);
		rmdir($dir);
	}

	/**
	 * Returns the files found under the specified directory and subdirectories.
	 * @param string $dir the directory under which the files will be looked for.
	 * @param array $options options for file searching. Valid options are:
	 *
	 * - filter: callback, a PHP callback that is called for each directory or file.
	 *   The signature of the callback should be: `function ($path)`, where `$path` refers the full path to be filtered.
	 *   The callback can return one of the following values:
	 *
	 *   * true: the directory or file will be returned (the "only" and "except" options will be ignored)
	 *   * false: the directory or file will NOT be returned (the "only" and "except" options will be ignored)
	 *   * null: the "only" and "except" options will determine whether the directory or file should be returned
	 *
	 * - only: array, list of patterns that the file paths should match if they want to be returned.
	 *   A path matches a pattern if it contains the pattern string at its end.
	 *   For example, '.php' matches all file paths ending with '.php'.
	 *   Note, the '/' characters in a pattern matches both '/' and '\' in the paths.
	 *   If a file path matches a pattern in both "only" and "except", it will NOT be returned.
	 * - except: array, list of patterns that the file paths or directory paths should match if they want to be excluded from the result.
	 *   A path matches a pattern if it contains the pattern string at its end.
	 *   Patterns ending with '/' apply to directory paths only, and patterns not ending with '/'
	 *   apply to file paths only. For example, '/a/b' matches all file paths ending with '/a/b';
	 *   and '.svn/' matches directory paths ending with '.svn'. Note, the '/' characters in a pattern matches
	 *   both '/' and '\' in the paths.
	 * - recursive: boolean, whether the files under the subdirectories should also be looked for. Defaults to true.
	 * @return array files found under the directory. The file list is sorted.
	 */
	public static function findFiles($dir, $options = [])
	{
		$list = [];
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (static::filterPath($path, $options)) {
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
	 * Checks if the given file path satisfies the filtering options.
	 * @param string $path the path of the file or directory to be checked
	 * @param array $options the filtering options. See [[findFiles()]] for explanations of
	 * the supported options.
	 * @return boolean whether the file or directory satisfies the filtering options.
	 */
	public static function filterPath($path, $options)
	{
		if (isset($options['filter'])) {
			$result = call_user_func($options['filter'], $path);
			if (is_bool($result)) {
				return $result;
			}
		}

		if (empty($options['except']) && empty($options['only'])) {
			return true;
		}

		$path = str_replace('\\', '/', $path);
		if ($isDir = is_dir($path)) {
			$path .= '/';
		}
		$n = StringHelper::byteLength($path);

		if (!empty($options['except'])) {
			foreach ($options['except'] as $name) {
				if (StringHelper::byteSubstr($path, -StringHelper::byteLength($name), $n) === $name) {
					return false;
				}
			}
		}

		if (!$isDir && !empty($options['only'])) {
			foreach ($options['only'] as $name) {
				if (StringHelper::byteSubstr($path, -StringHelper::byteLength($name), $n) === $name) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * Creates a new directory.
	 *
	 * This method is similar to the PHP `mkdir()` function except that
	 * it uses `chmod()` to set the permission of the created directory
	 * in order to avoid the impact of the `umask` setting.
	 *
	 * @param string $path path of the directory to be created.
	 * @param integer $mode the permission to be set for the created directory.
	 * @param boolean $recursive whether to create parent directories if they do not exist.
	 * @return boolean whether the directory is created successfully
	 */
	public static function createDirectory($path, $mode = 0775, $recursive = true)
	{
		if (is_dir($path)) {
			return true;
		}
		$parentDir = dirname($path);
		if ($recursive && !is_dir($parentDir)) {
			static::createDirectory($parentDir, $mode, true);
		}
		$result = mkdir($path, $mode);
		chmod($path, $mode);
		return $result;
	}
}
