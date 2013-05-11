<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\base\Exception;

/**
 * This command creates an Yii Web application at the specified location.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class AppController extends Controller
{
	private $_rootPath;
	private $_config;

	/**
	 * @var string custom template path. If specified, templates will be
	 * searched there additionally to `framework/console/webapp`.
	 */
	public $templatesPath;

	/**
	 * @var string application type. If not specified default application
	 * skeleton will be used.
	 */
	public $type = 'default';

	public function init()
	{
		parent::init();

		if ($this->templatesPath && !is_dir($this->templatesPath)) {
			throw new Exception('--templatesPath "'.$this->templatesPath.'" does not exist or can not be read.');
		}
	}

	public function globalOptions()
	{
		return array('templatesPath', 'type');
	}

	public function actionIndex()
	{
		$this->forward('help/index', array('-args' => array('app/create')));
	}

	/**
	 * Generates Yii application at the path specified via appPath parameter.
	 *
	 * @param string $path the directory where the new application will be created.
	 * If the directory does not exist, it will be created. After the application
	 * is created, please make sure the directory has enough permissions.
	 *
	 * @throws \yii\base\Exception if path specified is not valid
	 * @return integer the exit status
	 */
	public function actionCreate($path)
	{
		$path = strtr($path, '/\\', DIRECTORY_SEPARATOR);
		if (strpos($path, DIRECTORY_SEPARATOR) === false) {
			$path = '.'.DIRECTORY_SEPARATOR.$path;
		}
		$dir = rtrim(realpath(dirname($path)), '\\/');
		if ($dir === false || !is_dir($dir)) {
			throw new Exception("The directory '$path' is not valid. Please make sure the parent directory exists.");
		}

		if (basename($path) === '.') {
			$this->_rootPath = $path = $dir;
		} else {
			$this->_rootPath = $path = $dir.DIRECTORY_SEPARATOR.basename($path);
		}

		if ($this->confirm("Create \"$this->type\" application under '$path'?")) {
			$sourceDir = $this->getSourceDir();
			$config = $this->getConfig();

			$list = $this->buildFileList($sourceDir, $path);

			if (is_array($config)) {
				foreach ($config as $file => $settings) {
					if (isset($settings['handler'])) {
						$list[$file]['callback'] = $settings['handler'];
					}
				}
			}

			$this->copyFiles($list);

			if (is_array($config)) {
				foreach ($config as $file => $settings) {
					if (isset($settings['permissions'])) {
						@chmod($path.'/'.$file, $settings['permissions']);
					}
				}
			}

			echo "\nYour application has been created successfully under {$path}.\n";
		}
	}

	/**
	 * @throws \yii\base\Exception if source directory wasn't located
	 * @return string
	 */
	protected function getSourceDir()
	{
		$customSource = realpath($this->templatesPath.'/'.$this->type);
		$defaultSource = realpath($this->getDefaultTemplatesPath().'/'.$this->type);

		if ($customSource) {
			return $customSource;
		} elseif ($defaultSource) {
			return $defaultSource;
		} else {
			throw new Exception('Unable to locate the source directory for "'.$this->type.'".');
		}
	}

	/**
	 * @return string default templates path
	 */
	protected function getDefaultTemplatesPath()
	{
		return realpath(__DIR__.'/../webapp');
	}

	/**
	 * @return array|null template configuration
	 */
	protected function getConfig()
	{
		if ($this->_config===null) {
			$this->_config = require $this->getDefaultTemplatesPath().'/config.php';
			if ($this->templatesPath && file_exists($this->templatesPath)) {
				$this->_config = array_merge($this->_config, require $this->templatesPath.'/config.php');
			}
		}
		if (isset($this->_config[$this->type])) {
			return $this->_config[$this->type];
		}
	}

	/**
	 * @param string $source path to source file
	 * @param string $pathTo path to file we want to get relative path for
	 * @param string $varName variable name w/o $ to replace value with relative path for
	 *
	 * @return string target file contents
	 */
	public function replaceRelativePath($source, $pathTo, $varName)
	{
		$content = file_get_contents($source);
		$relativeFile = str_replace($this->getSourceDir(), '', $source);

		$relativePath = $this->getRelativePath($pathTo, $this->_rootPath.$relativeFile);
		$relativePath = str_replace('\\', '\\\\', $relativePath);

		return preg_replace('/\$'.$varName.'\s*=(.*?);/', "\$".$varName."=$relativePath;", $content);
	}

	/**
	 * @param string $path1 absolute path
	 * @param string $path2 absolute path
	 *
	 * @return string relative path
	 */
	protected function getRelativePath($path1, $path2)
	{
		$segs1 = explode(DIRECTORY_SEPARATOR, $path1);
		$segs2 = explode(DIRECTORY_SEPARATOR, $path2);
		$n1 = count($segs1);
		$n2 = count($segs2);

		for ($i = 0; $i < $n1 && $i < $n2; ++$i) {
			if ($segs1[$i] !== $segs2[$i]) {
				break;
			}
		}

		if ($i===0) {
			return "'".$path1."'";
		}
		$up='';
		for($j=$i;$j<$n2-1;++$j) {
			$up.='/..';
		}
		for(; $i<$n1-1; ++$i) {
			$up.='/'.$segs1[$i];
		}

		return '__DIR__.\''.$up.'/'.basename($path1).'\'';
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
	protected function copyFiles($fileList)
	{
		$overwriteAll = false;
		foreach ($fileList as $name => $file) {
			$source = strtr($file['source'], '/\\', DIRECTORY_SEPARATOR);
			$target = strtr($file['target'], '/\\', DIRECTORY_SEPARATOR);
			$callback = isset($file['callback']) ? $file['callback'] : null;
			$params = isset($file['params']) ? $file['params'] : null;

			if (is_dir($source)) {
				if (!is_dir($target)) {
					mkdir($target, 0777, true);
				}
				continue;
			}

			if ($callback !== null) {
				$content = call_user_func($callback, $source, $params);
			} else {
				$content = file_get_contents($source);
			}
			if (is_file($target)) {
				if ($content === file_get_contents($target)) {
					echo "  unchanged $name\n";
					continue;
				}
				if ($overwriteAll) {
					echo "  overwrite $name\n";
				}
				else {
					echo "      exist $name\n";
					echo "            ...overwrite? [Yes|No|All|Quit] ";
					$answer = trim(fgets(STDIN));
					if (!strncasecmp($answer, 'q', 1)) {
						return;
					} elseif (!strncasecmp($answer, 'y', 1)) {
						echo "  overwrite $name\n";
					} elseif (!strncasecmp($answer, 'a', 1)) {
						echo "  overwrite $name\n";
						$overwriteAll = true;
					} else {
						echo "       skip $name\n";
						continue;
					}
				}
			}
			else {
				if (!is_dir(dirname($target))) {
					mkdir(dirname($target), 0777, true);
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
	 * be ignored in list building process.
	 * @param array $renameMap hash array of file names that should be
	 * renamed. Example value: array('1.old.txt'=>'2.new.txt').
	 * @return array the file list (see {@link copyFiles})
	 */
	protected function buildFileList($sourceDir, $targetDir, $baseDir='', $ignoreFiles=array(), $renameMap=array())
	{
		$list = array();
		$handle = opendir($sourceDir);
		while (($file = readdir($handle)) !== false) {
			if (in_array($file, array('.', '..', '.svn', '.gitignore', '.hgignore')) || in_array($file, $ignoreFiles)) {
				continue;
			}
			$sourcePath = $sourceDir.DIRECTORY_SEPARATOR.$file;
			$targetPath = $targetDir.DIRECTORY_SEPARATOR.strtr($file, $renameMap);
			$name = $baseDir === '' ? $file : $baseDir.'/'.$file;
			$list[$name] = array(
				'source' => $sourcePath,
				'target' => $targetPath,
			);
			if (is_dir($sourcePath)) {
				$list = array_merge($list, self::buildFileList($sourcePath, $targetPath, $name, $ignoreFiles, $renameMap));
			}
		}
		closedir($handle);
		return $list;
	}
}
