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
<<<<<<< HEAD
=======
     * @var array map of DB errors and corresponding exceptions
     * If left part is found in DB error message exception class from the right part is used.
     */
    public $exceptionMap = [
        'ORA-00001: unique constraint' => 'yii\db\IntegrityException',
    ];

    /**
>>>>>>> yiichina/master
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
<<<<<<< HEAD
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
=======
        $sql = <<<SQL
SELECT a.column_name, a.data_type, a.data_precision, a.data_scale, a.data_length,
>>>>>>> yiichina/master
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
<<<<<<< HEAD
    a.owner = '{$schemaName}'
    and (b.object_type = 'TABLE' or b.object_type = 'VIEW')
    and b.object_name = '{$tableName}'
ORDER by a.column_id
EOD;

        try {
            $columns = $this->db->createCommand($sql)->queryAll();
=======
    a.owner = :schemaName
    and b.object_type IN ('TABLE', 'VIEW', 'MATERIALIZED VIEW')
    and b.object_name = :tableName
ORDER by a.column_id
SQL;

        try {
            $columns = $this->db->createCommand($sql, [
                ':tableName' => $table->name,
                ':schemaName' => $table->schemaName,
            ])->queryAll();
>>>>>>> yiichina/master
        } catch (\Exception $e) {
            return false;
        }

        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $column) {
<<<<<<< HEAD
=======
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $column = array_change_key_case($column, CASE_UPPER);
            }
>>>>>>> yiichina/master
            $c = $this->createColumn($column);
            $table->columns[$c->name] = $c;
            if ($c->isPrimaryKey) {
                $table->primaryKey[] = $c->name;
                $table->sequenceName = $this->getTableSequenceName($table->name);
<<<<<<< HEAD
                $c->autoIncrement = true;
=======
>>>>>>> yiichina/master
            }
        }
        return true;
    }

    /**
     * Sequence name of table
     *
<<<<<<< HEAD
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
=======
     * @param $tableName
     * @internal param \yii\db\TableSchema $table ->name the table schema
     * @return string whether the sequence exists
     */
    protected function getTableSequenceName($tableName)
    {

        $seq_name_sql = <<<SQL
SELECT ud.referenced_name as sequence_name
FROM user_dependencies ud
JOIN user_triggers ut on (ut.trigger_name = ud.name)
WHERE ut.table_name = :tableName
AND ud.type='TRIGGER'
AND ud.referenced_type='SEQUENCE'
SQL;
        $sequenceName = $this->db->createCommand($seq_name_sql, [':tableName' => $tableName])->queryScalar();
        return $sequenceName === false ? null : $sequenceName;
>>>>>>> yiichina/master
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
<<<<<<< HEAD
=======
            $sequenceName = $this->quoteSimpleTableName($sequenceName);
>>>>>>> yiichina/master
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
        $c = $this->createColumnSchema();
        $c->name = $column['COLUMN_NAME'];
        $c->allowNull = $column['NULLABLE'] === 'Y';
        $c->isPrimaryKey = strpos($column['KEY'], 'P') !== false;
        $c->comment = $column['COLUMN_COMMENT'] === null ? '' : $column['COLUMN_COMMENT'];

<<<<<<< HEAD
        $this->extractColumnType($c, $column['DATA_TYPE']);
        $this->extractColumnSize($c, $column['DATA_TYPE']);
=======
        $this->extractColumnType($c, $column['DATA_TYPE'], $column['DATA_PRECISION'], $column['DATA_SCALE'], $column['DATA_LENGTH']);
        $this->extractColumnSize($c, $column['DATA_TYPE'], $column['DATA_PRECISION'], $column['DATA_SCALE'], $column['DATA_LENGTH']);
>>>>>>> yiichina/master

        $c->phpType = $this->getColumnPhpType($c);

        if (!$c->isPrimaryKey) {
            if (stripos($column['DATA_DEFAULT'], 'timestamp') !== false) {
                $c->defaultValue = null;
            } else {
<<<<<<< HEAD
                $c->defaultValue = $c->phpTypecast($column['DATA_DEFAULT']);
=======
                $defaultValue = $column['DATA_DEFAULT'];
                if ($c->type === 'timestamp' && $defaultValue === 'CURRENT_TIMESTAMP') {
                    $c->defaultValue = new Expression('CURRENT_TIMESTAMP');
                } else {
                    if ($defaultValue !== null) {
                        if (($len = strlen($defaultValue)) > 2 && $defaultValue[0] === "'"
                            && $defaultValue[$len - 1] === "'"
                        ) {
                            $defaultValue = substr($column['DATA_DEFAULT'], 1, -1);
                        } else {
                            $defaultValue = trim($defaultValue);
                        }
                    }
                    $c->defaultValue = $c->phpTypecast($defaultValue);
                }
>>>>>>> yiichina/master
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
<<<<<<< HEAD
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
=======
        $sql = <<<SQL
SELECT D.CONSTRAINT_NAME, C.COLUMN_NAME, C.POSITION, D.R_CONSTRAINT_NAME,
        E.TABLE_NAME AS TABLE_REF, F.COLUMN_NAME AS COLUMN_REF,
        C.TABLE_NAME
FROM ALL_CONS_COLUMNS C
INNER JOIN ALL_CONSTRAINTS D ON D.OWNER = C.OWNER AND D.CONSTRAINT_NAME = C.CONSTRAINT_NAME
LEFT JOIN ALL_CONSTRAINTS E ON E.OWNER = D.R_OWNER AND E.CONSTRAINT_NAME = D.R_CONSTRAINT_NAME
LEFT JOIN ALL_CONS_COLUMNS F ON F.OWNER = E.OWNER AND F.CONSTRAINT_NAME = E.CONSTRAINT_NAME AND F.POSITION = C.POSITION
WHERE C.OWNER = :schemaName
   AND C.TABLE_NAME = :tableName
   AND D.CONSTRAINT_TYPE = 'R'
ORDER BY D.CONSTRAINT_NAME, C.POSITION
SQL;
        $command = $this->db->createCommand($sql, [
            ':tableName' => $table->name,
            ':schemaName' => $table->schemaName,
        ]);
        $constraints = [];
        foreach ($command->queryAll() as $row) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $row = array_change_key_case($row, CASE_UPPER);
            }
            $name = $row['CONSTRAINT_NAME'];
            if (!isset($constraints[$name])) {
                $constraints[$name] = [
                    'tableName' => $row["TABLE_REF"],
                    'columns' => [],
                ];
            }
            $constraints[$name]['columns'][$row["COLUMN_NAME"]] = $row["COLUMN_REF"];
        }
        foreach ($constraints as $constraint) {
            $table->foreignKeys[] = array_merge([$constraint['tableName']], $constraint['columns']);
>>>>>>> yiichina/master
        }
    }

    /**
     * @inheritdoc
     */
<<<<<<< HEAD
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
=======
    protected function findSchemaNames()
    {
        $sql = <<<SQL
SELECT username
  FROM dba_users u
 WHERE EXISTS (
    SELECT 1
      FROM dba_objects o
     WHERE o.owner = u.username )
   AND default_tablespace not in ('SYSTEM','SYSAUX')
SQL;
        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * @inheritdoc
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $sql = <<<SQL
SELECT table_name FROM user_tables
UNION ALL
SELECT view_name AS table_name FROM user_views
UNION ALL
SELECT mview_name AS table_name FROM user_mviews
ORDER BY table_name
SQL;
            $command = $this->db->createCommand($sql);
        } else {
            $sql = <<<SQL
SELECT object_name AS table_name
FROM all_objects
WHERE object_type IN ('TABLE', 'VIEW', 'MATERIALIZED VIEW') AND owner=:schema
ORDER BY object_name
SQL;
            $command = $this->db->createCommand($sql, [':schema' => $schema]);
>>>>>>> yiichina/master
        }

        $rows = $command->queryAll();
        $names = [];
        foreach ($rows as $row) {
<<<<<<< HEAD
            $names[] = $row['TABLE_NAME'];
        }

=======
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $row = array_change_key_case($row, CASE_UPPER);
            }
            $names[] = $row['TABLE_NAME'];
        }
>>>>>>> yiichina/master
        return $names;
    }

    /**
<<<<<<< HEAD
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
=======
     * Returns all unique indexes for the given table.
     * Each array element is of the following structure:
     *
     * ~~~
     * [
     *  'IndexName1' => ['col1' [, ...]],
     *  'IndexName2' => ['col2' [, ...]],
     * ]
     * ~~~
     *
     * @param TableSchema $table the table metadata
     * @return array all unique indexes for the given table.
     * @since 2.0.4
     */
    public function findUniqueIndexes($table)
    {
        $query = <<<SQL
SELECT dic.INDEX_NAME, dic.COLUMN_NAME
FROM ALL_INDEXES di
INNER JOIN ALL_IND_COLUMNS dic ON di.TABLE_NAME = dic.TABLE_NAME AND di.INDEX_NAME = dic.INDEX_NAME
WHERE di.UNIQUENESS = 'UNIQUE'
AND dic.TABLE_OWNER = :schemaName
AND dic.TABLE_NAME = :tableName
ORDER BY dic.TABLE_NAME, dic.INDEX_NAME, dic.COLUMN_POSITION
SQL;
        $result = [];
        $command = $this->db->createCommand($query, [
            ':tableName' => $table->name,
            ':schemaName' => $table->schemaName,
        ]);
        foreach ($command->queryAll() as $row) {
            $result[$row['INDEX_NAME']][] = $row['COLUMN_NAME'];
        }
        return $result;
    }

    /**
     * Extracts the data types for the given column
     * @param ColumnSchema $column
     * @param string $dbType DB type
     * @param string $precision total number of digits.
     * This parameter is available since version 2.0.4.
     * @param string $scale number of digits on the right of the decimal separator.
     * This parameter is available since version 2.0.4.
     * @param string $length length for character types.
     * This parameter is available since version 2.0.4.
     */
    protected function extractColumnType($column, $dbType, $precision, $scale, $length)
    {
        $column->dbType = $dbType;

        if (strpos($dbType, 'FLOAT') !== false || strpos($dbType, 'DOUBLE') !== false) {
            $column->type = 'double';
        } elseif ($dbType == 'NUMBER' || strpos($dbType, 'INTEGER') !== false) {
            if ($scale !== null && $scale > 0) {
                $column->type = 'decimal';
            } else {
                $column->type = 'integer';
>>>>>>> yiichina/master
            }
        } elseif (strpos($dbType, 'BLOB') !== false) {
            $column->type = 'binary';
        } elseif (strpos($dbType, 'CLOB') !== false) {
            $column->type = 'text';
<<<<<<< HEAD
=======
        } elseif (strpos($dbType, 'TIMESTAMP') !== false) {
            $column->type = 'timestamp';
>>>>>>> yiichina/master
        } else {
            $column->type = 'string';
        }
    }

    /**
     * Extracts size, precision and scale information from column's DB type.
     * @param ColumnSchema $column
     * @param string $dbType the column's DB type
<<<<<<< HEAD
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
=======
     * @param string $precision total number of digits.
     * This parameter is available since version 2.0.4.
     * @param string $scale number of digits on the right of the decimal separator.
     * This parameter is available since version 2.0.4.
     * @param string $length length for character types.
     * This parameter is available since version 2.0.4.
     */
    protected function extractColumnSize($column, $dbType, $precision, $scale, $length)
    {
        $column->size = trim($length) == '' ? null : (int)$length;
        $column->precision = trim($precision) == '' ? null : (int)$precision;
        $column->scale = trim($scale) == '' ? null : (int)$scale;
    }

    /**
     * @inheritdoc
     */
    public function insert($table, $columns)
    {
        $params = [];
        $returnParams = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);
        $tableSchema = $this->getTableSchema($table);
        $returnColumns = $tableSchema->primaryKey;
        if (!empty($returnColumns)) {
            $columnSchemas = $tableSchema->columns;
            $returning = [];
            foreach ((array)$returnColumns as $name) {
                $phName = QueryBuilder::PARAM_PREFIX . (count($params) + count($returnParams));
                $returnParams[$phName] = [
                    'column' => $name,
                    'value' => null,
                ];
                if (!isset($columnSchemas[$name]) || $columnSchemas[$name]->phpType !== 'integer') {
                    $returnParams[$phName]['dataType'] = \PDO::PARAM_STR;
                } else {
                    $returnParams[$phName]['dataType'] = \PDO::PARAM_INT;
                }
                $returnParams[$phName]['size'] = isset($columnSchemas[$name]) && isset($columnSchemas[$name]->size) ? $columnSchemas[$name]->size : -1;
                $returning[] = $this->quoteColumnName($name);
            }
            $sql .= ' RETURNING ' . implode(', ', $returning) . ' INTO ' . implode(', ', array_keys($returnParams));
        }

        $command = $this->db->createCommand($sql, $params);
        $command->prepare(false);

        foreach ($returnParams as $name => &$value) {
            $command->pdoStatement->bindParam($name, $value['value'], $value['dataType'], $value['size'] );
        }

        if (!$command->execute()) {
            return false;
        }

        $result = [];
        foreach ($returnParams as $value) {
            $result[$value['column']] = $value['value'];
        }

        return $result;
>>>>>>> yiichina/master
    }
}
