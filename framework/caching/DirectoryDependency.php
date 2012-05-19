<?php
/**
 * CDirectoryCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDirectoryCacheDependency represents a dependency based on change of a directory.
 *
 * CDirectoryCacheDependency performs dependency checking based on the
 * modification time of the files contained in the specified directory.
 * The directory being checked is specified via {@link directory}.
 *
 * By default, all files under the specified directory and subdirectories
 * will be checked. If the last modification time of any of them is changed
 * or if different number of files are contained in a directory, the dependency
 * is reported as changed. By specifying {@link recursiveLevel},
 * one can limit the checking to a certain depth of the directory.
 *
 * Note, dependency checking for a directory is expensive because it involves
 * accessing modification time of multiple files under the directory.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching.dependencies
 * @since 1.0
 */
class CDirectoryCacheDependency extends CCacheDependency
{
	/**
	 * @var string the directory whose change is used to determine if the dependency has been changed.
	 * If any of the files under the directory is changed, the dependency is considered as changed.
	 */
	public $directory;
	/**
	 * @var integer the depth of the subdirectories to be recursively checked.
	 * If the value is less than 0, it means unlimited depth.
	 * If the value is 0, it means checking the files directly under the specified directory.
	 */
	public $recursiveLevel=-1;
	/**
	 * @var string the regular expression matching valid file/directory names.
	 * Only the matching files or directories will be checked for changes.
	 * Defaults to null, meaning all files/directories will qualify.
	 */
	public $namePattern;

	/**
	 * Constructor.
	 * @param string $directory the directory to be checked
	 */
	public function __construct($directory=null)
	{
		$this->directory=$directory;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the modification timestamps for files under the directory.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		if($this->directory!==null)
			return $this->generateTimestamps($this->directory);
		else
			throw new CException(Yii::t('yii','CDirectoryCacheDependency.directory cannot be empty.'));
	}

	/**
	 * Determines the last modification time for files under the directory.
	 * This method may go recursively into subdirectories if {@link recursiveLevel} is not 0.
	 * @param string $directory the directory name
	 * @param integer $level level of the recursion
	 * @return array list of file modification time indexed by the file path
	 */
	protected function generateTimestamps($directory,$level=0)
	{
		if(($dir=@opendir($directory))===false)
			throw new CException(Yii::t('yii','"{path}" is not a valid directory.',
				array('{path}'=>$directory)));
		$timestamps=array();
		while(($file=readdir($dir))!==false)
		{
			$path=$directory.DIRECTORY_SEPARATOR.$file;
			if($file==='.' || $file==='..')
				continue;
			if($this->namePattern!==null && !preg_match($this->namePattern,$file))
				continue;
			if(is_file($path))
			{
				if($this->validateFile($path))
					$timestamps[$path]=filemtime($path);
			}
			else
			{
				if(($this->recursiveLevel<0 || $level<$this->recursiveLevel) && $this->validateDirectory($path))
					$timestamps=array_merge($timestamps, $this->generateTimestamps($path,$level+1));
			}
		}
		closedir($dir);
		return $timestamps;
	}

	/**
	 * Checks to see if the file should be checked for dependency.
	 * This method is invoked when dependency of the whole directory is being checked.
	 * By default, it always returns true, meaning the file should be checked.
	 * You may override this method to check only certain files.
	 * @param string $fileName the name of the file that may be checked for dependency.
	 * @return boolean whether this file should be checked.
	 */
	protected function validateFile($fileName)
	{
		return true;
	}

	/**
	 * Checks to see if the specified subdirectory should be checked for dependency.
	 * This method is invoked when dependency of the whole directory is being checked.
	 * By default, it always returns true, meaning the subdirectory should be checked.
	 * You may override this method to check only certain subdirectories.
	 * @param string $directory the name of the subdirectory that may be checked for dependency.
	 * @return boolean whether this subdirectory should be checked.
	 */
	protected function validateDirectory($directory)
	{
		return true;
	}
}
