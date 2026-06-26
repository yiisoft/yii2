<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use PDOException;

/**
 * This is an extension of the default PDO class of MSSQL and DBLIB drivers.
 *
 * It provides workarounds for improperly implemented functionalities of the MSSQL and DBLIB drivers.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class PDO extends \PDO
{
    /**
     * Returns value of the last inserted ID.
     *
     * @param string|null $sequence The sequence name. Defaults to `null`.
     *
     * @return string|false Last inserted ID value.
     */
    public function lastInsertId(string|null $sequence = null): string|false
    {
        $sql = <<<SQL
        SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)
        SQL;

        $id = $this->query($sql)->fetchColumn();

        return $id === null ? false : (string) $id;
    }

    /**
     * Starts a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not natively support
     * transactions.
     *
     * @return bool The result of a transaction start.
     */
    public function beginTransaction(): bool
    {
        $this->exec('BEGIN TRANSACTION');

        return true;
    }

    /**
     * Commits a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not natively support
     * transactions.
     *
     * @return bool The result of a transaction commit.
     */
    public function commit(): bool
    {
        $this->exec('COMMIT TRANSACTION');

        return true;
    }

    /**
     * Rollbacks a transaction. It is necessary to override PDO's method as MSSQL PDO driver does not natively support
     * transactions.
     *
     * @return bool The result of a transaction roll back.
     */
    public function rollBack(): bool
    {
        $this->exec('ROLLBACK TRANSACTION');

        return true;
    }

    /**
     * Retrieve a database connection attribute.
     *
     * It is necessary to override PDO's method as some MSSQL PDO driver (for example, dblib) does not support getting
     * attributes.
     *
     * @param int $attribute One of the PDO::ATTR_* constants.
     *
     * @return mixed A successful call returns the value of the requested PDO attribute. An unsuccessful call returns
     * `null`.
     */
    public function getAttribute(int $attribute): mixed
    {
        $sql = <<<SQL
        SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR)
        SQL;

        try {
            return parent::getAttribute($attribute);
        } catch (PDOException $e) {
            switch ($attribute) {
                case self::ATTR_SERVER_VERSION:
                    return $this->query($sql)->fetchColumn();
                default:
                    throw $e;
            }
        }
    }
}
