<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * This is an extension of the default PDO class of SQLSRV driver.
 * It provides workarounds for improperly implemented functionalities of the SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class SqlsrvPDO extends \PDO
{
    /**
     * Returns value of the last inserted ID.
     *
     * SQLSRV driver implements [[PDO::lastInsertId()]] method but with a single peculiarity:
     * when `$sequence` value is a null or an empty string it returns an empty string.
     * But when parameter is not specified it works as expected and returns actual
     * last inserted ID (like the other PDO drivers).
     * @param string|null $sequence the sequence name. Defaults to null.
     * @return string|false last inserted ID value.
     */
    #[\ReturnTypeWillChange]
    public function lastInsertId($sequence = null)
    {
        return !$sequence ? parent::lastInsertId() : parent::lastInsertId($sequence);
    }
}
