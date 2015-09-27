<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\ColumnSchema;

/**
 * Schema is the class for retrieving metadata from a MS SQL Server databases (version 2008 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
    /**
     * @var string the default schema used for the current session.
     */
    public $defaultSchema = 'dbo';
    /**
     * @var array map of DB errors and corresponding exceptions
     * If left part is found in DB error message exception class from the right part is used.
     */
    public $exceptionMap = [
        'SQLSTATE[23' => 'yii\db\IntegrityException',
        'SQLSTATE[HY000]: General error: 20018 The INSERT statement conflicted with the FOREIGN KEY constraint' => 'yii\db\IntegrityException',
        'SQLSTATE[HY000]: General error: 20018 Violation of PRIMARY KEY constraint' => 'yii\db\IntegrityException',
        'SQLSTATE[HY000]: General error: 20018 Violation of UNIQUE KEY constraint' => 'yii\db\IntegrityException',
    ];
    /**
     * @var array mapping from physical column types (keys) to abstract column types (values)
     */
    public $typeMap = [
        // exact numbers
        'bigint' => self::TYPE_BIGINT,
        'numeric' => self::TYPE_DECIMAL,
        'bit' => self::TYPE_SMALLINT,
        'smallint' => self::TYPE_SMALLINT,
        'decimal' => self::TYPE_DECIMAL,
        'smallmoney' => self::TYPE_MONEY,
        'int' => self::TYPE_INTEGER,
        'tinyint' => self::TYPE_SMALLINT,
        'money' => self::TYPE_MONEY,
        // approximate numbers
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'real' => self::TYPE_FLOAT,
        // date and time
        'date' => self::TYPE_DATE,
        'datetimeoffset' => self::TYPE_DATETIME,
        'datetime2' => self::TYPE_DATETIME,
        'smalldatetime' => self::TYPE_DATETIME,
        'datetime' => self::TYPE_DATETIME,
        'time' => self::TYPE_TIME,
        // character strings
        'char' => self::TYPE_STRING,
        'varchar' => self::TYPE_STRING,
        'text' => self::TYPE_TEXT,
        // unicode character strings
        'nchar' => self::TYPE_STRING,
        'nvarchar' => self::TYPE_STRING,
        'ntext' => self::TYPE_TEXT,
        // binary strings
        'binary' => self::TYPE_BINARY,
        'varbinary' => self::TYPE_BINARY,
        'image' => self::TYPE_BINARY,
        // other data types
        // 'cursor' type cannot be used with tables
        'timestamp' => self::TYPE_TIMESTAMP,
        'hierarchyid' => self::TYPE_STRING,
        'uniqueidentifier' => self::TYPE_STRING,
        'sql_variant' => self::TYPE_STRING,
        'xml' => self::TYPE_STRING,
        'table' => self::TYPE_STRING,
    ];


    /**
     * @inheritdoc
     */
    public function createSavepoint($name)
    {
        $this->db->createCommand("SAVE TRANSACTION $name")->execute();
    }

    /**
     * @inheritdoc
     */
    public function releaseSavepoint($name)
    {
        // does nothing as MSSQL does not support this
    }

    /**
     * @inheritdoc
     */
    public function rollBackSavepoint($name)
    {
        $this->db->createCommand("ROLLBACK TRANSACTION $name")->execute();
    }

    /**
     * Quotes a table name for use in a query.
     * A simple table name has no schema prefix.
     * @param string $name table name.
     * @return string the properly quoted table name.
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '[') === false ? "[{$name}]" : $name;
    }

    /**
     * Quotes a column name for use in a query.
     * A simple column name has no prefix.
     * @param string $name column name.
     * @return string the properly quoted column name.
     */
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '[') === false && $name !== '*' ? "[{$name}]" : $name;
    }

    /**
     * Creates a query builder for the MSSQL database.
     * @return QueryBuilder query builder interface.
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * Loads the metadata for the specified table.
     * @param string $name table name
     * @return TableSchema|null driver dependent table metadata. Null if the table does not exist.
     */
    public function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);
        $this->findPrimaryKeys($table);
        if ($this->findColumns($table)) {
            $this->findForeignKeys($table);

            return $table;
        } else {
            return null;
        }
    }

    /**
     * Resolves the table name and schema name (if any).
     * @param TableSchema $table the table metadata object
     * @param string $name the table name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace(['[', ']'], '', $name));
        $partCount = count($parts);
        if ($partCount == 3) {
            // catalog name, schema name and table name passed
            $table->catalogName = $parts[0];
            $table->schemaName = $parts[1];
            $table->name = $parts[2];
            $table->fullName = $table->catalogName . '.' . $table->schemaName . '.' . $table->name;
        } elseif ($partCount == 2) {
            // only schema name and table name passed
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
            $table->fullName = $table->schemaName !== $this->defaultSchema ? $table->schemaName . '.' . $table->name : $table->name;
        } else {
            // only table name passed
            $table->schemaName = $this->defaultSchema;
            $table->fullName = $table->name = $parts[0];
        }
    }

    /**
     * Loads the column information into a [[ColumnSchema]] object.
     * @param array $info column information
     * @return ColumnSchema the column schema object
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['column_name'];
        $column->allowNull = $info['is_nullable'] == 'YES';
        $column->dbType = $info['data_type'];
        $column->enumValues = null; // mssql has only vague equivalents to enum
        $column->isPrimaryKey = null; // primary key will be determined in findColumns() method
        $column->autoIncrement = $info['is_identity'] == 1;
        $column->unsigned = stripos($column->dbType, 'unsigned') !== false;
        $column->comment = $info['comment'] === null ? '' : $info['comment'];

        $column->type = self::TYPE_STRING;
        $column->precision = $info['numeric_precision'] !== null ? (int)$info['numeric_precision'] : null;
        $column->scale = !empty($info['numeric_scale']) ? (int)$info['numeric_scale'] : null;
        $column->size = $info['character_maximum_length'] > 0 ? (int)$info['character_maximum_length'] : null;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = $matches[1];
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }
            if (!empty($matches[2])) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int) $values[0];
                if (isset($values[1])) {
                    $column->scale = (int) $values[1];
                }
                if ($column->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
                    $column->type = 'boolean';
                } elseif ($type === 'bit') {
                    if ($column->size > 32) {
                        $column->type = 'bigint';
                    } elseif ($column->size === 32) {
                        $column->type = 'integer';
                    }
                }
            }
        }

        $column->phpType = $this->getColumnPhpType($column);
        $column->defaultValue = $this->getColumnDefaultValue($column, $info['column_default']);

        return $column;
    }

    /**
     * Extracts column default value.
     * @param ColumnSchema $column
     * @param string $value default value expression
     * @return mixed null value, a number, string or \yii\db\Expression object
     */
    private function getColumnDefaultValue($column, $value)
    {
        if ($value !== null && !empty($value) && $value{0} === '(' && strrpos($value, ')') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }

        if ($value === '' || $value === null) {
            return null;
        }
        if ($value{0} === '(' && strpos($value, '(', 1) === false) {
            // trim parenthesis only if there is only a single pair surrounding a literal value,
            // used for negative numbers
            $value = substr($value, 1, -1);
        }
        $firstChar = strtolower($value{0});
        $secondChar = strlen($value) > 1 ? strtolower($value{1}) : null;
        $isQuoted = $firstChar === "'"
            || ($firstChar === 'x' && $secondChar === "'")
            || ($firstChar === 'b' && $secondChar === "'");
        $isNumeric = $firstChar === '.' || ('0' <= $firstChar && $firstChar <= '9')
            || ($firstChar === '-' && '0' <= $secondChar && $secondChar <= '9');
        if (!$isQuoted && !$isNumeric) {
            return new \yii\db\Expression($value);
        }
        // widechar functions are not required here because only single chars are replaced
        if ($firstChar === 'x') {
            $value = hex2bin(substr($value, 2, -1));
        } elseif ($firstChar === "'") {
            $value = str_replace("''", "'", substr($value, 1, -1));
        }
        return $column->phpTypecast($value);
    }

    /**
     * Collects the metadata of table columns.
     * @param TableSchema $table the table metadata
     * @return boolean whether the table exists in the database
     */
    protected function findColumns($table)
    {
        $columnsTableName = 'INFORMATION_SCHEMA.COLUMNS';
        $whereSql = "[t1].[table_name] = '{$table->name}'";
        if ($table->catalogName !== null) {
            $columnsTableName = "{$table->catalogName}.{$columnsTableName}";
            $whereSql .= " AND [t1].[table_catalog] = '{$table->catalogName}'";
        }
        if ($table->schemaName !== null) {
            $whereSql .= " AND [t1].[table_schema] = '{$table->schemaName}'";
        }
        $columnsTableName = $this->quoteTableName($columnsTableName);

        $sql = <<<SQL
SELECT
    [t1].[column_name], [t1].[is_nullable], [t1].[data_type], [t1].[column_default],
    [t1].[character_maximum_length], [t1].[numeric_precision], [t1].[numeric_scale],
    COLUMNPROPERTY(OBJECT_ID([t1].[table_schema] + '.' + [t1].[table_name]), [t1].[column_name], 'IsIdentity') AS is_identity,
    CONVERT(VARCHAR, [t2].[value]) AS comment
FROM {$columnsTableName} AS [t1]
LEFT OUTER JOIN [sys].[extended_properties] AS [t2] ON
    [t2].[minor_id] = COLUMNPROPERTY(OBJECT_ID([t1].[TABLE_SCHEMA] + '.' + [t1].[TABLE_NAME]), [t1].[COLUMN_NAME], 'ColumnID') AND
    OBJECT_NAME([t2].[major_id]) = [t1].[table_name] AND
    [t2].[class] = 1 AND
    [t2].[class_desc] = 'OBJECT_OR_COLUMN' AND
    [t2].[name] = 'MS_Description'
WHERE {$whereSql}
SQL;

        try {
            $columns = $this->db->createCommand($sql)->queryAll();
            if (empty($columns)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        foreach ($columns as $column) {
            $column = $this->loadColumnSchema($column);
            foreach ($table->primaryKey as $primaryKey) {
                if (strcasecmp($column->name, $primaryKey) === 0) {
                    $column->isPrimaryKey = true;
                    break;
                }
            }
            if ($column->isPrimaryKey && $column->autoIncrement) {
                $table->sequenceName = '';
            }
            $table->columns[$column->name] = $column;
        }

        return true;
    }

    /**
     * Collects the constraint details for the given table and constraint type.
     * @param TableSchema $table
     * @param string $type either PRIMARY KEY or UNIQUE
     * @return array each entry contains index_name and field_name
     * @since 2.0.4
     */
    protected function findTableConstraints($table, $type)
    {
        $keyColumnUsageTableName = 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
        $tableConstraintsTableName = 'INFORMATION_SCHEMA.TABLE_CONSTRAINTS';
        if ($table->catalogName !== null) {
            $keyColumnUsageTableName = $table->catalogName . '.' . $keyColumnUsageTableName;
            $tableConstraintsTableName = $table->catalogName . '.' . $tableConstraintsTableName;
        }
        $keyColumnUsageTableName = $this->quoteTableName($keyColumnUsageTableName);
        $tableConstraintsTableName = $this->quoteTableName($tableConstraintsTableName);

        $sql = <<<SQL
SELECT
    [kcu].[constraint_name] AS [index_name],
    [kcu].[column_name] AS [field_name]
FROM {$keyColumnUsageTableName} AS [kcu]
LEFT JOIN {$tableConstraintsTableName} AS [tc] ON
    [kcu].[table_schema] = [tc].[table_schema] AND
    [kcu].[table_name] = [tc].[table_name] AND
    [kcu].[constraint_name] = [tc].[constraint_name]
WHERE
    [tc].[constraint_type] = :type AND
    [kcu].[table_name] = :tableName AND
    [kcu].[table_schema] = :schemaName
SQL;

        return $this->db
            ->createCommand($sql, [
                ':tableName' => $table->name,
                ':schemaName' => $table->schemaName,
                ':type' => $type,
            ])
            ->queryAll();
    }

    /**
     * Collects the primary key column details for the given table.
     * @param TableSchema $table the table metadata
     */
    protected function findPrimaryKeys($table)
    {
        $result = [];
        foreach ($this->findTableConstraints($table, 'PRIMARY KEY') as $row) {
            $result[] = $row['field_name'];
        }
        $table->primaryKey = $result;
    }

    /**
     * Collects the foreign key column details for the given table.
     * @param TableSchema $table the table metadata
     */
    protected function findForeignKeys($table)
    {
        $referentialConstraintsTableName = 'INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS';
        $keyColumnUsageTableName = 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
        if ($table->catalogName !== null) {
            $referentialConstraintsTableName = $table->catalogName . '.' . $referentialConstraintsTableName;
            $keyColumnUsageTableName = $table->catalogName . '.' . $keyColumnUsageTableName;
        }
        $referentialConstraintsTableName = $this->quoteTableName($referentialConstraintsTableName);
        $keyColumnUsageTableName = $this->quoteTableName($keyColumnUsageTableName);

        // please refer to the following page for more details:
        // http://msdn2.microsoft.com/en-us/library/aa175805(SQL.80).aspx
        $sql = <<<SQL
SELECT
    [kcu1].[constraint_name] AS [constraint_name],
    [kcu1].[column_name] AS [fk_column_name],
    [kcu2].[table_name] AS [uq_table_name],
    [kcu2].[column_name] AS [uq_column_name]
FROM {$referentialConstraintsTableName} AS [rc]
JOIN {$keyColumnUsageTableName} AS [kcu1] ON
    [kcu1].[constraint_catalog] = [rc].[constraint_catalog] AND
    [kcu1].[constraint_schema] = [rc].[constraint_schema] AND
    [kcu1].[constraint_name] = [rc].[constraint_name]
JOIN {$keyColumnUsageTableName} AS [kcu2] ON
    [kcu2].[constraint_catalog] = [rc].[constraint_catalog] AND
    [kcu2].[constraint_schema] = [rc].[constraint_schema] AND
    [kcu2].[constraint_name] = [rc].[unique_constraint_name] AND
    [kcu2].[ordinal_position] = [kcu1].[ordinal_position]
WHERE [kcu1].[table_name] = :tableName AND [kcu1].[table_schema] = :schemaName
SQL;

        $rows = $this->db->createCommand($sql, [
            ':tableName' => $table->name,
            ':schemaName' => $table->schemaName,
        ])->queryAll();
        $constraints = [];
        foreach ($rows as $row) {
            $name = $row['constraint_name'];
            if (!isset($constraints[$name])) {
                $constraints[$name] = [
                    'tableName' => $row['uq_table_name'],
                    'columns' => [],
                ];
            }
            $constraints[$name]['columns'][$row['fk_column_name']] = $row['uq_column_name'];
        }
        $table->foreignKeys = [];
        foreach ($constraints as $constraint) {
            $table->foreignKeys[] = array_merge([$constraint['tableName']], $constraint['columns']);
        }
    }

    /**
     * Returns all table names in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
     * @return array all table names in the database. The names have NO schema name prefix.
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }

        $sql = <<<SQL
SELECT [t].[table_name]
FROM [INFORMATION_SCHEMA].[TABLES] AS [t]
WHERE [t].[table_schema] = :schema AND [t].[table_type] IN ('BASE TABLE', 'VIEW')
ORDER BY [t].[table_name]
SQL;

        return $this->db->createCommand($sql, [':schema' => $schema])->queryColumn();
    }

    /**
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
        $result = [];
        foreach ($this->findTableConstraints($table, 'UNIQUE') as $row) {
            $result[$row['index_name']][] = $row['field_name'];
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function insert($table, $columns)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->insertWithOutput($table, $columns, $params, $this->getTableSchema($table)->primaryKey);

        $command = $this->db->createCommand($sql, $params);
        $command->prepare(false);
        $result = $command->queryOne();

        return !$command->pdoStatement->rowCount() ? false : $result;
    }
}
