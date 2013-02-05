<?php
/**
 * Filesystem helper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use yii\base\Exception;
use yii\base\InvalidConfigException;

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
	 * @throws InvalidConfigException if the path does not refer to an existing directory.
	 */
	public static function ensureDirectory($path)
	{
		$p = \Yii::getAlias($path);
		if ($p !== false && ($p = realpath($p)) !== false && is_dir($p)) {
			return $p;
		} else {
			throw new InvalidConfigException('Directory does not exist: ' . $path);
		}
	}

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
			$language = \Yii::$app->getLanguage();
		}
		if ($sourceLanguage === null) {
			$sourceLanguage = \Yii::$app->sourceLanguage;
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
			if ($info && ($result = finfo_file($info, $file)) !== false) {
				return $result;
			}
		}

		return $checkExtension ? self::getMimeTypeByExtension($file) : null;
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
		if ($magicFile === null) {
			$magicFile = \Yii::getAlias('@yii/util/mimeTypes.php');
		}
		$mimeTypes = require($magicFile);
		if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '') {
			$ext = strtolower($ext);
			if (isset($mimeTypes[$ext])) {
				return $mimeTypes[$ext];
			}
		}
		return null;
	}

	/**
	 * Copies a list of files from one place to another.
	 * @param array $fileList the list of files to be copied (name=>spec).
	 * The array keys are names displayed during the copy process, and array values are specifications
	 * for files to be copied. Each array value must be an array of the following structure:
	 * <ul>
	 * <li>source: required, the full path of the file/directory to be copied from</li>
	 * <li>target: required, the full path of the file/directory to be copied to</li>
	 * <li>callback: optional, the callback to be invoked when copying a file. The callback function
	 *   should be declared as follows:
	 *   <pre>
	 *   function foo($source,$params)
	 *   </pre>
	 *   where $source parameter is the source file path, and the content returned
	 *   by the function will be saved into the target file.</li>
	 * <li>params: optional, the parameters to be passed to the callback</li>
	 * </ul>
	 * @see buildFileList
	 */
	public static function copyFiles($fileList)
	{
		$overwriteAll = false;
		foreach($fileList as $name=>$file) {
			$source = strtr($file['source'], '/\\', DIRECTORY_SEPARATOR);
			$target = strtr($file['target'], '/\\', DIRECTORY_SEPARATOR);
			$callback = isset($file['callback']) ? $file['callback'] : null;
			$params = isset($file['params']) ? $file['params'] : null;

			if(is_dir($source)) {
				try {
					self::ensureDirectory($target);
				}
				catch (Exception $e) {
					mkdir($target, true, 0777);
				}
				continue;
			}

			if($callback !== null) {
				$content = call_user_func($callback, $source, $params);
			}
			else {
				$content = file_get_contents($source);
			}
			if(is_file($target)) {
				if($content === file_get_contents($target)) {
					echo "  unchanged $name\n";
					continue;
				}
				if($overwriteAll) {
					echo "  overwrite $name\n";
				}
				else {
					echo "      exist $name\n";
					echo "            ...overwrite? [Yes|No|All|Quit] ";
					$answer = trim(fgets(STDIN));
					if(!strncasecmp($answer, 'q', 1)) {
						return;
					}
					elseif(!strncasecmp($answer, 'y', 1)) {
						echo "  overwrite $name\n";
					}
					elseif(!strncasecmp($answer, 'a', 1)) {
						echo "  overwrite $name\n";
						$overwriteAll = true;
					}
					else {
						echo "       skip $name\n";
						continue;
					}
				}
			}
			else {
				try {
					self::ensureDirectory(dirname($target));
				}
				catch (Exception $e) {
					mkdir(dirname($target), true, 0777);
				}
				echo "   generate $name\n";
			}
			file_put_contents($target, $content);
		}
	}

	/**
	 * Builds the file list of a directory.
	 * This method traverses through the specified directory and builds
	 * a list of files and subdirectories that the directory contains.
	 * The result of this function can be passed to {@link copyFiles}.
	 * @param string $sourceDir the source directory
	 * @param string $targetDir the target directory
	 * @param string $baseDir base directory
	 * @param array $ignoreFiles list of the names of files that should
	 * be ignored in list building process. Argument available since 1.1.11.
	 * @param array $renameMap hash array of file names that should be
	 * renamed. Example value: array('1.old.txt'=>'2.new.txt').
	 * @return array the file list (see {@link copyFiles})
	 */
	public static function buildFileList($sourceDir, $targetDir, $baseDir='', $ignoreFiles=array(), $renameMap=array())
	{
		$list = array();
		$handle = opendir($sourceDir);
		while(($file = readdir($handle)) !== false) {
			if(in_array($file, array('.', '..', '.svn', '.gitignore')) || in_array($file, $ignoreFiles)) {
				continue;
			}
			$sourcePath = $sourceDir.DIRECTORY_SEPARATOR.$file;
			$targetPath = $targetDir.DIRECTORY_SEPARATOR.strtr($file, $renameMap);
			$name = $baseDir === '' ? $file : $baseDir.'/'.$file;
			$list[$name] = array(
				'source' => $sourcePath,
				'target' => $targetPath,
			);
			if(is_dir($sourcePath)) {
				$list = array_merge($list, self::buildFileList($sourcePath, $targetPath, $name, $ignoreFiles, $renameMap));
			}
		}
		closedir($handle);
		return $list;
	}
}