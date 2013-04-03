<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;

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
	 * check if the dependency has been changed. This property must be always set,
	 * otherwise an exception would be raised.
	 */
	public $fileName;

	/**
	 * Initializes the database dependency object.
	 */
	public function init()
	{
		if ($this->file === null) {
			throw new InvalidConfigException('FileDependency::fileName must be set.');
		}
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
