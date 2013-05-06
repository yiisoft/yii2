<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * This is an extension of default PDO class for MSSQL SQLSRV driver. It provides workaround for improperly
 * implemented functionalities of the PDO SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class SqlsrvPDO extends \PDO
{
	/**
	 * Returns last inserted ID value.
	 *
	 * SQLSRV driver supports PDO::lastInsertId() with one peculiarity: when $sequence value is null
	 * or empty string it returns empty string. But when parameter is not specified it's working
	 * as expected and returns actual last inserted ID (like the other PDO drivers).
	 *
	 * @param string|null $sequence the sequence name. Defaults to null.
	 * @return integer last inserted ID value.
	 */
	public function lastInsertId($sequence = null)
	{
		return !$sequence ? parent::lastInsertId() : parent::lastInsertId($sequence);
	}
}
