<?php
namespace yii\db\oci;

use yii\db\TableSchema;
use yii\db\oci\ColumnSchema;

/**
 * Schema is the class for retrieving metadata information from an Oracle database.
 * 
 */

class Schema extends \yii\db\Schema
{

    private $_defaultSchema = '';

    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    public function quoteTableName($name)
    {
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }
        return implode('.', $parts);
    }

    public function quoteSimpleTableName($name)
    {
        return '"' . $name . '"';
        // return $name;
    }

    public function quoteColumnName($name)
    {
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
    }

    public function quoteSimpleColumnName($name)
    {
        return '"' . $name . '"';
        // return $name;
    }

    public function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);
        // $this->findPrimaryKeys($table);
        if ($this->findColumns($table)) {
            // $this->findForeignKeys($table);
            $this->findConstraints($table);
            return $table;
        }
    }

    /**
     * Resolves the table name and schema name (if any).
     *
     * @param TableSchema $table
     *            the table metadata object
     * @param string $name
     *            the table name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
        } else {
            $table->name = $parts[0];
        }
        
        if ($table->schemaName === null) {
            $table->schemaName = $this->getDefaultSchema();
        }
    }

    /**
     *
     * @return string default schema.
     */
    public function getDefaultSchema()
    {
        if (! strlen($this->_defaultSchema)) {
            $this->setDefaultSchema(strtoupper($this->db->username));
        }
        
        return $this->_defaultSchema;
    }

    /**
     *
     * @param string $schema
     *            default schema.
     */
    public function setDefaultSchema($schema)
    {
        $this->_defaultSchema = $schema;
    }

    public function getLastInsertID($sequenceName = '')
    {
        if ($this->db->isActive) {
            $sql = "select {$sequenceName}.currval from dual";
            return $this->db->createCommand($sql)->queryScalar();
        } else {
            throw new InvalidCallException('DB Connection is not active.');
        }
    }

    /**
     * Collects the table column metadata.
     *     
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
LEFT JOIN user_col_comments com ON (A.table_name = com.table_name AND A.column_name = com.column_name)
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
                if ($table->primaryKey === null) {
                    $table->primaryKey = $c->name;
                } elseif (is_string($table->primaryKey)) {
                    $table->primaryKey = array(
                        $table->primaryKey,
                        $c->name
                    );
                } else {
                    $table->primaryKey[] = $c->name;
                }
                $sql = "select sequence_name
from user_tables tabs
join user_triggers trigs
  on trigs.table_name = tabs.table_name
join user_dependencies deps
  on deps.name = trigs.trigger_name
join user_sequences seqs
  on seqs.sequence_name = deps.referenced_name
where tabs.table_name = '{$tableName}'
 and trigs.triggering_event = 'INSERT'";
                try {
                    $seqName = $this->db->createCommand($sql)->queryScalar();
                } catch (\Exception $e) {
                    return false;
                }
                $table->sequenceName = $seqName;
                $c->autoIncrement = true;
            }
        }
        return true;
    }

    protected function createColumn($column)
    {
        $c = new ColumnSchema();
        $c->name = $column['COLUMN_NAME'];
        $c->allowNull = $column['NULLABLE'] === 'Y';
        $c->isPrimaryKey = strpos($column['KEY'], 'P') !== false;
        $c->extract($column['DATA_TYPE'], $column['DATA_DEFAULT']);
        $c->comment = $column['COLUMN_COMMENT'] === null ? '' : $column['COLUMN_COMMENT'];
        
        return $c;
    }

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
            if ($row['CONSTRAINT_TYPE'] === 'R') { // foreign key
                $name = $row["COLUMN_NAME"];
                $table->foreignKeys[$name] = array(
                    $row["TABLE_REF"],
                    $row["COLUMN_REF"]
                );
                /*
                 * if (isset($table->columns[$name])) { $table->columns[$name]->isForeignKey = true; }
                 */
            }
        }
    }

    /**
     * Returns all table names in the database.
     * 
     * @param string $schema
     *            the schema of the tables. Defaults to empty string, meaning the current or default schema.
     *            If not empty, the returned table names will be prefixed with the schema name.
     * @return array all table names in the database.
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
            $command = $this->db > createCommand($sql);
            $command->bindParam(':schema', $schema);
        }
        
        $rows = $command->queryAll();
        $names = array();
        foreach ($rows as $row) {
            if ($schema === $this->getDefaultSchema() || $schema === '') {
                $names[] = $row['TABLE_NAME'];
            } else {
                $names[] = $row['TABLE_SCHEMA'] . '.' . $row['TABLE_NAME'];
            }
        }
        return $names;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * 
     * @param string $table
     *            the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName
     *            the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     * @since 1.1.6
     */
    public function renameTable($table, $newName)
    {
        return 'ALTER TABLE ' . $this->quoteTableName($table) . ' RENAME TO ' . $this->quoteTableName($newName);
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * 
     * @param string $table
     *            the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column
     *            the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type
     *            the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
     *            into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     *            For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     * @since 1.1.6
     */
    public function alterColumn($table, $column, $type)
    {
        $type = $this->getColumnType($type);
        $sql = 'ALTER TABLE ' . $this->quoteTableName($table) . ' MODIFY ' . $this->quoteColumnName($column) . ' ' . $this->getColumnType($type);
        return $sql;
    }

    /**
     * Builds a SQL statement for dropping an index.
     * 
     * @param string $name
     *            the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table
     *            the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an index.
     * @since 1.1.6
     */
    public function dropIndex($name, $table)
    {
        return 'DROP INDEX ' . $this->quoteTableName($name);
    }

    /**
     * Resets the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or 1.
     * 
     * @param CDbTableSchema $table
     *            the table schema whose primary key sequence will be reset
     * @param mixed $value
     *            the value for the primary key of the next new row inserted. If this is not set,
     *            the next new row's primary key will have a value 1.
     * @since 1.1.13
     */
    public function resetSequence($table, $value = 1)
    {
        $seq = $table->name . "_SEQ";
        if ($table->sequenceName !== null) {
            $this->db->createCommand("DROP SEQUENCE " . $seq)->execute();
            
            $createSequenceSql = <<< SQL
create sequence $seq
start with $value
increment by 1
nomaxvalue
nocache
SQL;
            $this->db->createCommand($createSequenceSql)->execute();
        }
    }
}
