<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * This is an extension of default PDO class for MSSQL and DBLIB drivers. It provides workaround for improperly
 * implemented functionalities of the drivers.
 *
 * @author Timur Ruziev <qiang.xue@gmail.com>
 * @since 2.0
 */
class PDO extends \PDO
{
	/**
	 * Returns last inserted ID value.
	 *
	 * @param string|null sequence the sequence name. Defaults to null.
	 * @return integer last inserted ID value.
	 */
	public function lastInsertId($sequence = null)
	{
		return $this->query('SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)')->fetchColumn();
	}

	/**
	 * Begin a transaction.
	 *
	 * Is is necessary to override PDO's method as MSSQL PDO drivers does not support transactions.
	 *
	 * @return boolean
	 */
	public function beginTransaction()
	{
		$this->exec('BEGIN TRANSACTION');
		return true;
	}

	/**
	 * Commit a transaction.
	 *
	 * Is is necessary to override PDO's method as MSSQL PDO drivers does not support transactions.
	 *
	 * @return boolean
	 */
	public function commit()
	{
		$this->exec('COMMIT TRANSACTION');
		return true;
	}

	/**
	 * Rollback a transaction.
	 *
	 * Is is necessary to override PDO's method as MSSQL PDO drivers does not support transaction.
	 *
	 * @return boolean
	 */
	public function rollBack()
	{
		$this->exec('ROLLBACK TRANSACTION');
		return true;
	}
}
