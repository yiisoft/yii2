<?php
/**
 * CFileCacheDependency class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFileCacheDependency represents a dependency based on a file's last modification time.
 *
 * CFileCacheDependency performs dependency checking based on the
 * last modification time of the file specified via {@link fileName}.
 * The dependency is reported as unchanged if and only if the file's
 * last modification time remains unchanged.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching.dependencies
 * @since 1.0
 */
class CFileCacheDependency extends CCacheDependency
{
	/**
	 * @var string the name of the file whose last modification time is used to
	 * check if the dependency has been changed.
	 */
	public $fileName;

	/**
	 * Constructor.
	 * @param string $fileName name of the file whose change is to be checked.
	 */
	public function __construct($fileName=null)
	{
		$this->fileName=$fileName;
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the file's last modification time.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependentData()
	{
		if($this->fileName!==null)
			return @filemtime($this->fileName);
		else
			throw new CException(Yii::t('yii','CFileCacheDependency.fileName cannot be empty.'));
	}
}
