<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\base\InvalidCallException;
use yii\db\Connection;
use yii\db\TableSchema;
use yii\db\ColumnSchema;

/**
 * Schema is the class for retrieving metadata from an Oracle database
 *
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the
 * sequence object. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->defaultSchema === null) {
            $this->defaultSchema = strtoupper($this->db->username);
        }
    }

    /**
     * @inheritdoc
     */
    public function releaseSavepoint($name)
    {
        // does nothing as Oracle does not support this
    }

    /**
     * @inheritdoc
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '"') !== false ? $name : '"' . $name . '"';
    }

    /**
     * @inheritdoc
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * @inheritdoc
     */
    public function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);

        if ($this->findColumns($table)) {
            $this->findConstraints($table);

            return $table;
        } else {
            return null;
        }
    }

    /**
     * Resolves the table name and schema name (if any).
     *
     * @param TableSchema $table the table metadata object
     * @param string $name the table name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
        } else {
            $table->schemaName = $this->defaultSchema;
            $table->name = $name;
        }

        $table->fullName = $table->schemaName !== $this->defaultSchema ? $table->schemaName . '.' . $table->name : $table->name;
    }

    /**
     * Collects the table column metadata.
     * @param TableSchema $table the table schema
     * @return boolean whether the table exists
     */
    protected function findColumns($table)
    {
        $schemaName = $table->schemaName;
        $tableName = $table->name;

        $sql = <<<EOD
SELECT a.column_name, a.data_type ||
    case
        when data_precision is not null
            then '(' || a.data_precision ||
                    case when a.data_scale > 0 then ',' || a.data_scale else '' end
                || ')'
        when data_type = 'DATE' then ''
        when data_type = 'NUMBER' then ''
        else '(' || to_char(a.data_length) || ')'
    end as data_type,
    a.nullable, a.data_default,
    (   SELECT D.constraint_type
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        WHERE C.OWNER = B.OWNER
           and C.table_name = B.object_name
           and C.column_name = A.column_name
           and D.constraint_type = 'P') as Key,
    com.comments as column_comment
FROM ALL_TAB_COLUMNS A
inner join ALL_OBJECTS B ON b.owner = a.owner and ltrim(B.OBJECT_NAME) = ltrim(A.TABLE_NAME)
LEFT JOIN all_col_comments com ON (A.owner = com.owner AND A.table_name = com.table_name AND A.column_name = com.column_name)
WHERE
    a.owner = '{$schemaName}'
    and (b.object_type = 'TABLE' or b.object_type = 'VIEW')
    and b.object_name = '{$tableName}'
ORDER by a.column_id
EOD;

        try {
            $columns = $this->db->createCommand($sql)->queryAll();
        } catch (\Exception $e) {
            return false;
        }

        foreach ($columns as $column) {
            $c = $this->createColumn($column);
            $table->columns[$c->name] = $c;
            if ($c->isPrimaryKey) {
                $table->primaryKey[] = $c->name;
                $table->sequenceName = $this->getTableSequenceName($table->name);
                $c->autoIncrement = true;
            }
        }
        return true;
    }

    /**
     * Sequence name of table
     *
     * @param $tablename
     * @internal param \yii\db\TableSchema $table ->name the table schema
     * @return string whether the sequence exists
     */
    protected function getTableSequenceName($tablename){

        $seq_name_sql="select ud.referenced_name as sequence_name
                        from   user_dependencies ud
                               join user_triggers ut on (ut.trigger_name = ud.name)
                        where ut.table_name='{$tablename}'
                              and ud.type='TRIGGER'
                              and ud.referenced_type='SEQUENCE'";
        return $this->db->createCommand($seq_name_sql)->queryScalar();
    }

    /**
     * @Overrides method in class 'Schema'
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php -> Oracle does not support this
     *
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @throws InvalidCallException if the DB connection is not active
     */
    public function getLastInsertID($sequenceName = '')
    {
        if ($this->db->isActive) {
            // get the last insert id from the master connection
            return $this->db->useMaster(function (Connection $db) use ($sequenceName) {
                return $db->createCommand("SELECT {$sequenceName}.CURRVAL FROM DUAL")->queryScalar();
            });
        } else {
            throw new InvalidCallException('DB Connection is not active.');
        }
    }

    /**
     * Creates ColumnSchema instance
     *
     * @param array $column
     * @return ColumnSchema
     */
    protected function createColumn($column)
    {
        $c = new ColumnSchema();
        $c->name = $column['COLUMN_NAME'];
        $c->allowNull = $column['NULLABLE'] === 'Y';
        $c->isPrimaryKey = strpos($column['KEY'], 'P') !== false;
        $c->comment = $column['COLUMN_COMMENT'] === null ? '' : $column['COLUMN_COMMENT'];

        $this->extractColumnType($c, $column['DATA_TYPE']);
        $this->extractColumnSize($c, $column['DATA_TYPE']);

        if (!$c->isPrimaryKey) {
            if (stripos($column['DATA_DEFAULT'], 'timestamp') !== false) {
                $c->defaultValue = null;
            } else {
                $c->defaultValue = $c->phpTypecast($column['DATA_DEFAULT']);
            }
        }

        return $c;
    }

    /**
     * Finds constraints and fills them into TableSchema object passed
     * @param TableSchema $table
     */
    protected function findConstraints($table)
    {
        $sql = <<<EOD
        SELECT D.constraint_type as CONSTRAINT_TYPE, C.COLUMN_NAME, C.position, D.r_constraint_name,
                E.table_name as table_ref, f.column_name as column_ref,
                C.table_name
        FROM ALL_CONS_COLUMNS C
        inner join ALL_constraints D on D.OWNER = C.OWNER and D.constraint_name = C.constraint_name
        left join ALL_constraints E on E.OWNER = D.r_OWNER and E.constraint_name = D.r_constraint_name
        left join ALL_cons_columns F on F.OWNER = E.OWNER and F.constraint_name = E.constraint_name and F.position = c.position
        WHERE C.OWNER = '{$table->schemaName}'
           and C.table_name = '{$table->name}'
           and D.constraint_type <> 'P'
        order by d.constraint_name, c.position
EOD;
        $command = $this->db->createCommand($sql);
        foreach ($command->queryAll() as $row) {
            if ($row['CONSTRAINT_TYPE'] === 'R') {
                $name = $row["COLUMN_NAME"];
                $table->foreignKeys[$name] = [$row["TABLE_REF"], $row["COLUMN_REF"]];
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $sql = <<<EOD
SELECT table_name, '{$schema}' as table_schema FROM user_tables
EOD;
            $command = $this->db->createCommand($sql);
        } else {
            $sql = <<<EOD
SELECT object_name as table_name, owner as table_schema FROM all_objects
WHERE object_type = 'TABLE' AND owner=:schema
EOD;
            $command = $this->db->createCommand($sql);
            $command->bindParam(':schema', $schema);
        }

        $rows = $command->queryAll();
        $names = [];
        foreach ($rows as $row) {
            $names[] = $row['TABLE_NAME'];
        }

        return $names;
    }

    /**
     * Extracts the data types for the given column
     * @param ColumnSchema $column
     * @param string $dbType DB type
     */
    protected function extractColumnType($column, $dbType)
    {
        $column->dbType = $dbType;

        if (strpos($dbType, 'FLOAT') !== false) {
            $column->type = 'double';
        } elseif (strpos($dbType, 'NUMBER') !== false || strpos($dbType, 'INTEGER') !== false) {
            if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
                $values = explode(',', $matches[1]);
                if (isset($values[1]) && (((int) $values[1]) > 0)) {
                    $column->type = 'double';
                } else {
                    $column->type = 'integer';
                }
            } else {
                $column->type = 'double';
            }
        } else {
            $column->type = 'string';
        }
    }

    /**
     * Extracts size, precision and scale information from column's DB type.
     * @param ColumnSchema $column
     * @param string $dbType the column's DB type
     */
    protected function extractColumnSize($column, $dbType)
    {
        if (strpos($dbType, '(') && preg_match('/\((.*)\)/', $dbType, $matches)) {
            $values = explode(',', $matches[1]);
            $column->size = $column->precision = (int) $values[0];
            if (isset($values[1])) {
                $column->scale = (int) $values[1];
            }
        }
    }
}
