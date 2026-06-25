<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\support;

use yii\db\Connection;

/**
 * Common database utilities for test support code.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class DbHelper
{
    /**
     * Drops the given tables when they exist, skipping the ones that are absent.
     *
     * @param Connection $db Database connection.
     * @param array $tables Table names to drop, in dependency-safe order.
     */
    public static function dropTablesIfExist(Connection $db, array $tables): void
    {
        foreach ($tables as $table) {
            if ($db->getTableSchema($table, true) !== null) {
                $db->createCommand()->dropTable($table)->execute();
            }
        }
    }
}
