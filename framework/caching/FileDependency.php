<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * FileDependency represents a dependency based on a file's last modification time.
 *
 * If th last modification time of the file specified via [[fileName]] is changed,
 * the dependency is considered as changed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileDependency extends Dependency
{
	/**
	 * @var string the name of the file whose last modification time is used to
	 * check if the dependency has been changed.
	 */
	public $fileName;

	/**
	 * Constructor.
	 * @param string $fileName name of the file whose change is to be checked.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($fileName = null, $config = array())
	{
		$this->fileName = $fileName;
		parent::__construct($config);
	}

	/**
	 * Generates the data needed to determine if dependency has been changed.
	 * This method returns the file's last modification time.
	 * @return mixed the data needed to determine if dependency has been changed.
	 */
	protected function generateDependencyData()
	{
		return @filemtime($this->fileName);
	}
}
