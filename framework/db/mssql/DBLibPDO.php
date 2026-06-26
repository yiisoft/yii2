<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use PDO;
use PDOException;

/**
 * This is an extension of the default PDO class of DBLIB drivers.
 * It provides workarounds for improperly implemented functionalities of the DBLIB drivers.
 *
 * @author Bert Brunekreeft <bbrunekreeft@gmail.com>
 * @since 2.0.41
 */
class DBLibPDO extends PDO
{
    /**
     * Returns value of the last inserted ID.
     *
     * @param string|null $name The sequence name. Defaults to null.
     *
     * @return string|false Last inserted ID value.
     */
    public function lastInsertId(string|null $name = null): string|false
    {
        $sql = <<<SQL
        SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)
        SQL;

        $id = $this->query($sql)->fetchColumn();

        return $id === null ? false : (string) $id;
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
    public function getAttribute($attribute): mixed
    {
        $sql = <<<SQL
        SELECT CAST(SERVERPROPERTY('productversion') AS VARCHAR)
        SQL;

        try {
            return parent::getAttribute($attribute);
        } catch (PDOException $e) {
            switch ($attribute) {
                case self::ATTR_SERVER_VERSION:
                    // Reached only on the `dblib` driver (throws on `getAttribute()`); never on `sqlsrv`/CI.
                    // @codeCoverageIgnoreStart
                    return $this->query($sql)->fetchColumn();
                    // @codeCoverageIgnoreEnd
                default:
                    throw $e;
            }
        }
    }
}
