<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * This is an extension of the default PDO class of SQLSRV driver.
 *
 * It provides workarounds for improperly implemented functionalities of the SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class SqlsrvPDO extends \PDO
{
    /**
     * A constant for the pdo_sqlsrv encoding attribute. Pass as a key in [[yii\db\Connection::$attributes]].
     *
     * @see https://learn.microsoft.com/en-us/sql/connect/php/constants-microsoft-drivers-for-php-for-sql-server
     */
    public const int SQLSRV_ATTR_ENCODING = 1000;
    /**
     * A constant for ANSI (system code page) string encoding. Use with [[SQLSRV_ATTR_ENCODING]] to avoid the implicit
     * `nvarchar`-to-`varchar` conversion that degrades performance on `char`/`varchar` indexed columns.
     */
    public const int SQLSRV_ENCODING_SYSTEM = 3;
    /**
     * A constant for UTF-8 string encoding. This is the pdo_sqlsrv default for [[PDO::PARAM_STR]] parameters.
     */
    public const int SQLSRV_ENCODING_UTF8 = 65001;

    /**
     * Returns value of the last inserted ID.
     *
     * SQLSRV driver implements [[PDO::lastInsertId()]] method but with a single peculiarity:
     * - when `$sequence` value is a null or an empty string it returns an empty string.
     * - but when parameter is not specified it works as expected and returns actual
     * - last inserted ID (like the other PDO drivers).
     *
     * @param string|null $sequence The sequence name. Defaults to `null`.
     *
     * @return string|false Last inserted ID value.
     */
    public function lastInsertId(string|null $sequence = null): string|false
    {
        if ($sequence === null || $sequence === '') {
            return parent::lastInsertId();
        }

        return parent::lastInsertId($sequence);
    }
}
