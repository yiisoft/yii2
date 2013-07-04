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
class MysqlMutex extends Mutex
{
	/**
	 * Initializes MySQL specific mutex component implementation.
	 * @throws InvalidConfigException if [[db]] is not MySQL connection.
	 */
	public function init()
	{
		parent::init();
		if ($this->db->driverName !== 'mysql') {
			throw new InvalidConfigException('In order to use MysqlMutex connection must be configured to use MySQL database.');
		}
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
	 */
	protected function acquireLock($name, $timeout = 0)
	{
		return (boolean)$this->db
			->createCommand('SELECT GET_LOCK(:name, :timeout)', array(':name' => $name, ':timeout' => $timeout))
			->queryScalar();
	}

	/**
	 * This method should be extended by concrete mutex implementations. Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_release-lock
	 */
	protected function releaseLock($name)
	{
		return (boolean)$this->db
			->createCommand('SELECT RELEASE_LOCK(:name)', array(':name' => $name))
			->queryScalar();
	}
}
