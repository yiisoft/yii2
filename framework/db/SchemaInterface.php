<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * [[Schema]] interface for finds.
 *
 * @package yii\db
 */
interface SchemaInterface
{
    /**
     * Collects the table column metadata.
     * @param TableSchema $table the table metadata
     * @return bool whether the table exists in the database
     */
    public function findColumns($table);

    /**
     * Collects the foreign key column details for the given table.
     *
     * @param TableSchema $table the table metadata
     * @throws \Exception
     */
    public function findConstraints($table);
}
