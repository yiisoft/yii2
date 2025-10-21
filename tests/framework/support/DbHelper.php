<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\support;

use function preg_replace;
use function str_replace;

final class DbHelper
{
    /**
     * Adjust dbms specific escaping.
     *
     * @param array|string $sql SQL statement to adjust.
     * @param string $driverName DBMS name.
     *
     * @return array|string the adjusted SQL statement.
     */
    public static function replaceQuotes($sql, string $driverName)
    {
        switch ($driverName) {
            case 'mysql':
            case 'sqlite':
                return str_replace(['[[', ']]'], '`', $sql);
            case 'oci':
                return str_replace(['[[', ']]'], '"', $sql);
            case 'pgsql':
                // more complex replacement needed to not conflict with postgres array syntax
                return str_replace(['\\[', '\\]'], ['[', ']'], preg_replace('/(\[\[)|((?<!(\[))\]\])/', '"', $sql));
            case 'sqlsrv':
                return str_replace(['[[', ']]'], ['[', ']'], $sql);
            default:
                return $sql;
        }
    }
}
