<?php
/**
 * CreateController class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use yii\console\Controller;
use yii\util\FileHelper;

/**
 * This command creates an Yii Web application at the specified location.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CreateController extends Controller
{
	const EXIT_UNABLE_TO_LOCATE_SOURCE = 1;

	private $_rootPath;

	/**
	 * Generates Yii application at the path specified via appPath parameter.
	 *
	 * @param string $path the directory where the new application will be created.
	 * If the directory does not exist, it will be created. After the application
	 * is created, please make sure the directory has enough permissions.
	 * @param string $type application type. If not specified default application
	 * skeleton will be used.
	 * @return integer the exit status
	 */
	public function actionIndex($path, $type = 'default')
	{
		$path = strtr($path, '/\\', DIRECTORY_SEPARATOR);
		if(strpos($path, DIRECTORY_SEPARATOR) === false) {
			$path = '.'.DIRECTORY_SEPARATOR.$path;
		}
		$dir = rtrim(realpath(dirname($path)), '\\/');
		if($dir === false || !is_dir($dir)) {
			$this->usageError("The directory '$path' is not valid. Please make sure the parent directory exists.");
		}
		if(basename($path) === '.') {
			$this->_rootPath = $path = $dir;
		}
		else {
			$this->_rootPath = $path = $dir.DIRECTORY_SEPARATOR.basename($path);
		}
		if($this->confirm("Create \"$type\" application under '$path'?")) {
			$sourceDir = realpath(__DIR__.'/../create/'.$type);
			if($sourceDir === false) {
				echo "\nUnable to locate the source directory for \"$type\".\n";
				return self::EXIT_UNABLE_TO_LOCATE_SOURCE;
			}
			$list = FileHelper::buildFileList($sourceDir, $path);
			$list['index.php']['callback'] = array($this, 'generateIndex');
			$list['index-test.php']['callback'] = array($this, 'generateIndex');
			$list['protected/tests/bootstrap.php']['callback'] = array($this, 'generateTestBoostrap');
			$list['protected/yiic.php']['callback'] = array($this, 'generateYiic');
			FileHelper::copyFiles($list);
			//@chmod($path.'/assets', 0777);
			//@chmod($path.'/protected/runtime', 0777);
			//@chmod($path.'/protected/yiic', 0755);
			echo "\nYour application has been created successfully under {$path}.\n";
		}
	}

	/**
	 * Generates index.php file contents
	 *
	 * @param string $source path to index.php template
	 * @param array $params
	 *
	 * @return string final index.php file contents
	 */
	public function generateIndex($source, $params)
	{
		$content = file_get_contents($source);
		$yii = realpath(dirname(__FILE__).'/../../yii.php');
		$yii = $this->getRelativePath($yii, $this->_rootPath.DIRECTORY_SEPARATOR.'index.php');
		$yii = str_replace('\\', '\\\\', $yii);
		return preg_replace('/\$yii\s*=(.*?);/', "\$yii=$yii;", $content);
	}

	/**
	 * Generates index-test.php file contents
	 *
	 * @param string $source path to index-test.php template
	 * @param array $params
	 *
	 * @return string final index-test.php file contents
	 */
	public function generateTestBoostrap($source, $params)
	{
		$content = file_get_contents($source);
		$yii = realpath(dirname(__FILE__).'/../../yiit.php');
		$yii = $this->getRelativePath($yii, $this->_rootPath.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'bootstrap.php');
		$yii = str_replace('\\', '\\\\', $yii);
		return preg_replace('/\$yiit\s*=(.*?);/', "\$yiit=$yii;", $content);
	}

	/**
	 * Generates yiic.php file contents
	 *
	 * @param string $source path to yiic.php template
	 * @param array $params
	 *
	 * @return string final yiic.php file contents
	 */
	public function generateYiic($source, $params)
	{
		$content = file_get_contents($source);
		$yiic = realpath(dirname(__FILE__).'/../../yiic.php');
		$yiic = $this->getRelativePath($yiic, $this->_rootPath.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'yiic.php');
		$yiic = str_replace('\\', '\\\\', $yiic);
		return preg_replace('/\$yiic\s*=(.*?);/', "\$yiic=$yiic;", $content);
	}

	/**
	 * @param string $path1 abosolute path
	 * @param string $path2 abosolute path
	 *
	 * @return string relative path
	 */
	protected function getRelativePath($path1, $path2)
	{
		$segs1 = explode(DIRECTORY_SEPARATOR, $path1);
		$segs2 = explode(DIRECTORY_SEPARATOR, $path2);
		$n1 = count($segs1);
		$n2 = count($segs2);

		for($i=0; $i<$n1 && $i<$n2; ++$i) {
			if($segs1[$i] !== $segs2[$i]) {
				break;
			}
		}

		if($i===0) {
			return "'".$path1."'";
		}
		$up='';
		for($j=$i;$j<$n2-1;++$j) {
			$up.='/..';
		}
		for(; $i<$n1-1; ++$i) {
			$up.='/'.$segs1[$i];
		}

		return 'dirname(__FILE__).\''.$up.'/'.basename($path1).'\'';
	}
}