<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex;

use Yii;
use yii\base\InvalidConfigException;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class MssqlMutex extends Mutex
{
	/**
	 * Initializes Microsoft SQL Server specific mutex component implementation.
	 * @throws InvalidConfigException if [[db]] is not Microsoft SQL Server connection.
	 */
	public function init()
	{
		parent::init();
		$driverName = $this->db->driverName;
		if ($driverName !== 'sqlsrv' && $driverName !== 'dblib' && $driverName !== 'mssql') {
			throw new InvalidConfigException('In order to use MssqlMutex connection must be configured to use MS SQL Server database.');
		}
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 * @throws \BadMethodCallException not implemented yet.
	 * @see http://msdn.microsoft.com/en-us/library/ms189823.aspx
	 */
	protected function acquireLock($name, $timeout = 0)
	{
		throw new \BadMethodCallException('Not implemented yet.');
	}

	/**
	 * This method should be extended by concrete mutex implementations. Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 * @throws \BadMethodCallException not implemented yet.
	 * @see http://msdn.microsoft.com/en-us/library/ms178602.aspx
	 */
	protected function releaseLock($name)
	{
		throw new \BadMethodCallException('Not implemented yet.');
	}
}
