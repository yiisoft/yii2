<?php
/**
 * CreateController class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console\controllers;

use yii\console\Controller;
use yii\util\FileHelper;
use yii\base\Exception;

/**
 * This command creates an Yii Web application at the specified location.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class CreateController extends Controller
{
	private $_rootPath;
	private $_config;

	/**
	 * @var string custom template path. If specified, templates will be
	 * searched there additionally to `framework/console/create`.
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

		if($this->templatesPath && !is_dir($this->templatesPath)) {
			throw new Exception('--templatesPath "'.$this->templatesPath.'" does not exist or can not be read.');
		}
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
	public function actionIndex($path)
	{
		$path = strtr($path, '/\\', DIRECTORY_SEPARATOR);
		if(strpos($path, DIRECTORY_SEPARATOR) === false) {
			$path = '.'.DIRECTORY_SEPARATOR.$path;
		}
		$dir = rtrim(realpath(dirname($path)), '\\/');
		if($dir === false || !is_dir($dir)) {
			throw new Exception("The directory '$path' is not valid. Please make sure the parent directory exists.");
		}

		if(basename($path) === '.') {
			$this->_rootPath = $path = $dir;
		}
		else {
			$this->_rootPath = $path = $dir.DIRECTORY_SEPARATOR.basename($path);
		}

		if($this->confirm("Create \"$this->type\" application under '$path'?")) {
			$sourceDir = $this->getSourceDir();
			$config = $this->getConfig();

			$list = FileHelper::buildFileList($sourceDir, $path);

			if(is_array($config)) {
				foreach($config as $file => $settings) {
					if(isset($settings['handler'])) {
						$list[$file]['callback'] = $settings['handler'];
					}
				}
			}

			FileHelper::copyFiles($list);

			if(is_array($config)) {
				foreach($config as $file => $settings) {
					if(isset($settings['permissions'])) {
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

		if($customSource) {
			return $customSource;
		}
		elseif($defaultSource) {
			return $defaultSource;
		}
		else {
			throw new Exception('Unable to locate the source directory for "'.$this->type.'".');
		}
	}

	/**
	 * @return string default templates path
	 */
	protected function getDefaultTemplatesPath()
	{
		return realpath(__DIR__.'/../create');
	}

	/**
	 * @return array|null template configuration
	 */
	protected function getConfig()
	{
		if($this->_config===null) {
			$this->_config = require $this->getDefaultTemplatesPath().'/config.php';
			if($this->templatesPath && file_exists($this->templatesPath)) {
				$this->_config = array_merge($this->_config, require $this->templatesPath.'/config.php');
			}
		}
		if(isset($this->_config[$this->type])) {
			return $this->_config[$this->type];
		}
	}

	/**
	 * @param string $source path to source file
	 * @param string $pathTo path to file we want to get relative path for
	 * @param string $varName variable name w/o $ to replace value with relative path for
	 *
	 * @return string target file contetns
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

		return '__DIR__.\''.$up.'/'.basename($path1).'\'';
	}
}