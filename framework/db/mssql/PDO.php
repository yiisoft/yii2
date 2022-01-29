<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * This is an extension of the default PDO class of MSSQL and DBLIB drivers.
 * It provides workarounds for improperly implemented functionalities of the MSSQL and DBLIB drivers.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class PDO extends \PDO
{
    /**
     * Returns value of the last inserted ID.
     * @param string|null $sequence the sequence name. Defaults to null.
     * @return int last inserted ID value.
     */
    #[\ReturnTypeWillChange]
    public function lastInsertId($sequence = null)
    {
        return $this->query('SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)')->fetchColumn();
    }

    /**
     * Starts a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not
     * natively support transactions.
     * @return bool the result of a transaction start.
     */
    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        $this->exec('BEGIN TRANSACTION');

        return true;
    }

    /**
     * Commits a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not
     * natively support transactions.
     * @return bool the result of a transaction commit.
     */
    #[\ReturnTypeWillChange]
    public function commit()
    {
        $this->exec('COMMIT TRANSACTION');

        return true;
    }

    /**
     * Rollbacks a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not
     * natively support transactions.
     * @return bool the result of a transaction roll back.
     */
    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        $this->exec('ROLLBACK TRANSACTION');

        return true;
    }

    /**
     * Retrieve a database connection attribute.
     *
     * It is necessary to override PDO's method as some MSSQL PDO driver (e.g. dblib) does not
     * support getting attributes.
     * @param int $attribute One of the PDO::ATTR_* constants.
     * @return mixed A successful call returns the value of the requested PDO attribute.
     * An unsuccessful call returns null.
     */
    #[\ReturnTypeWillChange]
    public function getAttribute($attribute)
    {
        try {
            return parent::getAttribute($attribute);
        } catch (\PDOException $e) {
            switch ($attribute) {
                case self::ATTR_SERVER_VERSION:
                    return $this->query("SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR)")->fetchColumn();
                default:
                    throw $e;
            }
        }
    }
}
