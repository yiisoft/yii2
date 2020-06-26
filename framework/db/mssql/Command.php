<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;


/**
 * Command represents an MS SQL statement to be executed against a database.
 *
 * {@inheritdoc}
 *
 * @since 2.0.36
 */
class Command extends \yii\db\Command
{
    /**
     * Builds a SQL statement for dropping constraints for column of table.
     *
     * @param string $table the table whose constraint is to be dropped. The name will be properly quoted by the method.
     * @param string $column the column whose constraint is to be dropped. The name will be properly quoted by the method.
     * @param string $type type of constraint, leave empty for all type of constraints(for example: D - default, 'UQ' - unique, 'C' - check)
     * @see https://docs.microsoft.com/sql/relational-databases/system-catalog-views/sys-objects-transact-sql
     * @return string the DROP CONSTRAINTS SQL
     * @since 2.0.36
     */
    public function dropConstraintsForColumn($table, $column, $type='')
    {
        $sql = $this->db->getQueryBuilder()->dropConstraintsForColumn($table, $column, $type='');
        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }
}
