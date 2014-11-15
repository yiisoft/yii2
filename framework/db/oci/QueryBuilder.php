<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\base\InvalidParamException;
use yii\db\Connection;

/**
 * QueryBuilder is the query builder for Oracle databases.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK => 'NUMBER(10) NOT NULL PRIMARY KEY',
        Schema::TYPE_BIGPK => 'NUMBER(20) NOT NULL PRIMARY KEY',
        Schema::TYPE_STRING => 'VARCHAR2(255)',
        Schema::TYPE_TEXT => 'CLOB',
        Schema::TYPE_SMALLINT => 'NUMBER(5)',
        Schema::TYPE_INTEGER => 'NUMBER(10)',
        Schema::TYPE_BIGINT => 'NUMBER(20)',
        Schema::TYPE_FLOAT => 'NUMBER',
        Schema::TYPE_DECIMAL => 'NUMBER',
        Schema::TYPE_DATETIME => 'TIMESTAMP',
        Schema::TYPE_TIMESTAMP => 'TIMESTAMP',
        Schema::TYPE_TIME => 'TIMESTAMP',
        Schema::TYPE_DATE => 'DATE',
        Schema::TYPE_BINARY => 'BLOB',
        Schema::TYPE_BOOLEAN => 'NUMBER(1)',
        Schema::TYPE_MONEY => 'NUMBER(19,4)',
    ];


    /**
     * @inheritdoc
     */
    public function buildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy !== '') {
            $sql .= $this->separator . $orderBy;
        }

        $filters = [];
        if ($this->hasOffset($offset)) {
            $filters[] = 'rowNumId > ' . $offset;
        }
        if ($this->hasLimit($limit)) {
            $filters[] = 'rownum <= ' . $limit;
        }
        if (empty($filters)) {
            return $sql;
        }

        $filter = implode(' AND ', $filters);
        return <<<EOD
WITH USER_SQL AS ($sql),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE $filter
EOD;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     *
     * @param string $table the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable($table, $newName)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' RENAME TO ' . $this->db->quoteTableName($newName);
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     *
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[getColumnType]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     */
    public function alterColumn($table, $column, $type)
    {
        $type = $this->getColumnType($type);

        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' MODIFY ' . $this->db->quoteColumnName($column) . ' ' . $this->getColumnType($type);
    }

    /**
     * Builds a SQL statement for dropping an index.
     *
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an index.
     */
    public function dropIndex($name, $table)
    {
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }

    /**
     * @inheritdoc
     */
    public function resetSequence($table, $value = null)
    {
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            throw new InvalidParamException("Unknown table: $table");
        }
        if ($tableSchema->sequenceName === null) {
            return '';
        }

        if ($value !== null) {
            $value = (int) $value;
        } else {
            // use master connection to get the biggest PK value
            $value = $this->db->useMaster(function (Connection $db) use ($tableSchema) {
                return $db->createCommand("SELECT MAX(\"{$tableSchema->primaryKey}\") FROM \"{$tableSchema->name}\"")->queryScalar();
            }) + 1;
        }

        return "DROP SEQUENCE \"{$tableSchema->name}_SEQ\";"
            . "CREATE SEQUENCE \"{$tableSchema->name}_SEQ\" START WITH {$value} INCREMENT BY 1 NOMAXVALUE NOCACHE";
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' ADD CONSTRAINT ' . $this->db->quoteColumnName($name)
            . ' FOREIGN KEY (' . $this->buildColumns($columns) . ')'
            . ' REFERENCES ' . $this->db->quoteTableName($refTable)
            . ' (' . $this->buildColumns($refColumns) . ')';
        if ($delete !== null) {
            $sql .= ' ON DELETE ' . $delete;
        }

        return $sql;
    }

    /**
     * @inheritdoc
     */
    public function batchInsert($table, $columns, $rows)
    {
        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (!is_array($value) && isset($columns[$i]) && isset($columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                }
                $vs[] = $value;
            }
            $values[] = 'SELECT ' . implode(', ', $vs) . ' FROM DUAL';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') ' . implode(' UNION ', $values);
    }
}
