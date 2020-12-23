<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\CheckConstraint;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\DefaultValueConstraint;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\ViewFinderTrait;
use yii\helpers\ArrayHelper;

/**
 * Schema is the class for retrieving metadata from MS SQL Server databases (version 2008 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{
    use ViewFinderTrait;
    use ConstraintFinderTrait;

    /**
     * {@inheritdoc}
     */
    public $columnSchemaClass = 'yii\db\mssql\ColumnSchema';
    /**
     * @var string the default schema used for the current session.
     */
    public $defaultSchema = 'dbo';
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
        'tinyint' => self::TYPE_TINYINT,
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
        'char' => self::TYPE_CHAR,
        'varchar' => self::TYPE_STRING,
        'text' => self::TYPE_TEXT,
        // unicode character strings
        'nchar' => self::TYPE_CHAR,
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
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = ['[', ']'];
    /**
     * {@inheritdoc}
     */
    protected $columnQuoteCharacter = ['[', ']'];


    /**
     * Resolves the table name and schema name (if any).
     * @param string $name the table name
     * @return TableSchema resolved table, schema, etc. names.
     */
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $parts = $this->getTableNameParts($name);
        $partCount = count($parts);
        if ($partCount === 4) {
            // server name, catalog name, schema name and table name passed
            $resolvedName->catalogName = $parts[1];
            $resolvedName->schemaName = $parts[2];
            $resolvedName->name = $parts[3];
            $resolvedName->fullName = $resolvedName->catalogName . '.' . $resolvedName->schemaName . '.' . $resolvedName->name;
        } elseif ($partCount === 3) {
            // catalog name, schema name and table name passed
            $resolvedName->catalogName = $parts[0];
            $resolvedName->schemaName = $parts[1];
            $resolvedName->name = $parts[2];
            $resolvedName->fullName = $resolvedName->catalogName . '.' . $resolvedName->schemaName . '.' . $resolvedName->name;
        } elseif ($partCount === 2) {
            // only schema name and table name passed
            $resolvedName->schemaName = $parts[0];
            $resolvedName->name = $parts[1];
            $resolvedName->fullName = ($resolvedName->schemaName !== $this->defaultSchema ? $resolvedName->schemaName . '.' : '') . $resolvedName->name;
        } else {
            // only table name passed
            $resolvedName->schemaName = $this->defaultSchema;
            $resolvedName->fullName = $resolvedName->name = $parts[0];
        }

        return $resolvedName;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     * @return array
     * @since 2.0.22
     */
    protected function getTableNameParts($name)
    {
        $parts = [$name];
        preg_match_all('/([^.\[\]]+)|\[([^\[\]]+)\]/', $name, $matches);
        if (isset($matches[0]) && is_array($matches[0]) && !empty($matches[0])) {
            $parts = $matches[0];
        }

        $parts = str_replace(['[', ']'], '', $parts);

        return $parts;
    }

    /**
     * {@inheritdoc}
     * @see https://docs.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-database-principals-transact-sql
     */
    protected function findSchemaNames()
    {
        static $sql = <<<'SQL'
SELECT [s].[name]
FROM [sys].[schemas] AS [s]
INNER JOIN [sys].[database_principals] AS [p] ON [p].[principal_id] = [s].[principal_id]
WHERE [p].[is_fixed_role] = 0 AND [p].[sid] IS NOT NULL
ORDER BY [s].[name] ASC
SQL;

        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }

        $sql = <<<'SQL'
SELECT [t].[table_name]
FROM [INFORMATION_SCHEMA].[TABLES] AS [t]
WHERE [t].[table_schema] = :schema AND [t].[table_type] IN ('BASE TABLE', 'VIEW')
ORDER BY [t].[table_name]
SQL;
        return $this->db->createCommand($sql, [':schema' => $schema])->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);
        $this->findPrimaryKeys($table);
        if ($this->findColumns($table)) {
            $this->findForeignKeys($table);
            return $table;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSchemaMetadata($schema, $type, $refresh)
    {
        $metadata = [];
        $methodName = 'getTable' . ucfirst($type);
        $tableNames = array_map(function ($table) {
            return $this->quoteSimpleTableName($table);
        }, $this->getTableNames($schema, $refresh));
        foreach ($tableNames as $name) {
            if ($schema !== '') {
                $name = $schema . '.' . $name;
            }
            $tableMetadata = $this->$methodName($name, $refresh);
            if ($tableMetadata !== null) {
                $metadata[] = $tableMetadata;
            }
        }

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTablePrimaryKey($tableName)
    {
        return $this->loadTableConstraints($tableName, 'primaryKey');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableForeignKeys($tableName)
    {
        return $this->loadTableConstraints($tableName, 'foreignKeys');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableIndexes($tableName)
    {
        static $sql = <<<'SQL'
SELECT
    [i].[name] AS [name],
    [iccol].[name] AS [column_name],
    [i].[is_unique] AS [index_is_unique],
    [i].[is_primary_key] AS [index_is_primary]
FROM [sys].[indexes] AS [i]
INNER JOIN [sys].[index_columns] AS [ic]
    ON [ic].[object_id] = [i].[object_id] AND [ic].[index_id] = [i].[index_id]
INNER JOIN [sys].[columns] AS [iccol]
    ON [iccol].[object_id] = [ic].[object_id] AND [iccol].[column_id] = [ic].[column_id]
WHERE [i].[object_id] = OBJECT_ID(:fullName)
ORDER BY [ic].[key_ordinal] ASC
SQL;

        $resolvedName = $this->resolveTableName($tableName);
        $indexes = $this->db->createCommand($sql, [
            ':fullName' => $resolvedName->fullName,
        ])->queryAll();
        $indexes = $this->normalizePdoRowKeyCase($indexes, true);
        $indexes = ArrayHelper::index($indexes, null, 'name');
        $result = [];
        foreach ($indexes as $name => $index) {
            $result[] = new IndexConstraint([
                'isPrimary' => (bool)$index[0]['index_is_primary'],
                'isUnique' => (bool)$index[0]['index_is_unique'],
                'name' => $name,
                'columnNames' => ArrayHelper::getColumn($index, 'column_name'),
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableUniques($tableName)
    {
        return $this->loadTableConstraints($tableName, 'uniques');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableChecks($tableName)
    {
        return $this->loadTableConstraints($tableName, 'checks');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableDefaultValues($tableName)
    {
        return $this->loadTableConstraints($tableName, 'defaults');
    }

    /**
     * {@inheritdoc}
     */
    public function createSavepoint($name)
    {
        $this->db->createCommand("SAVE TRANSACTION $name")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function releaseSavepoint($name)
    {
        // does nothing as MSSQL does not support this
    }

    /**
     * {@inheritdoc}
     */
    public function rollBackSavepoint($name)
    {
        $this->db->createCommand("ROLLBACK TRANSACTION $name")->execute();
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
     * Resolves the table name and schema name (if any).
     * @param TableSchema $table the table metadata object
     * @param string $name the table name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = $this->getTableNameParts($name);
        $partCount = count($parts);
        if ($partCount === 4) {
            // server name, catalog name, schema name and table name passed
            $table->catalogName = $parts[1];
            $table->schemaName = $parts[2];
            $table->name = $parts[3];
            $table->fullName = $table->catalogName . '.' . $table->schemaName . '.' . $table->name;
        } elseif ($partCount === 3) {
            // catalog name, schema name and table name passed
            $table->catalogName = $parts[0];
            $table->schemaName = $parts[1];
            $table->name = $parts[2];
            $table->fullName = $table->catalogName . '.' . $table->schemaName . '.' . $table->name;
        } elseif ($partCount === 2) {
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
        $column->allowNull = $info['is_nullable'] === 'YES';
        $column->dbType = $info['data_type'];
        $column->enumValues = []; // mssql has only vague equivalents to enum
        $column->isPrimaryKey = null; // primary key will be determined in findColumns() method
        $column->autoIncrement = $info['is_identity'] == 1;
        $column->isComputed = (bool)$info['is_computed'];
        $column->unsigned = stripos($column->dbType, 'unsigned') !== false;
        $column->comment = $info['comment'] === null ? '' : $info['comment'];

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = $matches[1];
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }
            if (!empty($matches[2])) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int)$values[0];
                if (isset($values[1])) {
                    $column->scale = (int)$values[1];
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

        if ($info['column_default'] === '(NULL)') {
            $info['column_default'] = null;
        }
        if (!$column->isPrimaryKey && ($column->type !== 'timestamp' || $info['column_default'] !== 'CURRENT_TIMESTAMP')) {
            $column->defaultValue = $column->defaultPhpTypecast($info['column_default']);
        }

        return $column;
    }

    /**
     * Collects the metadata of table columns.
     * @param TableSchema $table the table metadata
     * @return bool whether the table exists in the database
     */
    protected function findColumns($table)
    {
        $columnsTableName = 'INFORMATION_SCHEMA.COLUMNS';
        $whereSql = "[t1].[table_name] = " . $this->db->quoteValue($table->name);
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
 [t1].[column_name],
 [t1].[is_nullable],
 CASE WHEN [t1].[data_type] IN ('char','varchar','nchar','nvarchar','binary','varbinary') THEN
    CASE WHEN [t1].[character_maximum_length] = NULL OR [t1].[character_maximum_length] = -1 THEN
        [t1].[data_type]
    ELSE
        [t1].[data_type] + '(' + LTRIM(RTRIM(CONVERT(CHAR,[t1].[character_maximum_length]))) + ')'
    END
 ELSE
    [t1].[data_type]
 END AS 'data_type',
 [t1].[column_default],
 COLUMNPROPERTY(OBJECT_ID([t1].[table_schema] + '.' + [t1].[table_name]), [t1].[column_name], 'IsIdentity') AS is_identity,
 COLUMNPROPERTY(OBJECT_ID([t1].[table_schema] + '.' + [t1].[table_name]), [t1].[column_name], 'IsComputed') AS is_computed,
 (
    SELECT CONVERT(VARCHAR, [t2].[value])
		FROM [sys].[extended_properties] AS [t2]
		WHERE
			[t2].[class] = 1 AND
			[t2].[class_desc] = 'OBJECT_OR_COLUMN' AND
			[t2].[name] = 'MS_Description' AND
			[t2].[major_id] = OBJECT_ID([t1].[TABLE_SCHEMA] + '.' + [t1].[table_name]) AND
			[t2].[minor_id] = COLUMNPROPERTY(OBJECT_ID([t1].[TABLE_SCHEMA] + '.' + [t1].[TABLE_NAME]), [t1].[COLUMN_NAME], 'ColumnID')
 ) as comment
FROM {$columnsTableName} AS [t1]
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
        $object = $table->name;
        if ($table->schemaName !== null) {
            $object = $table->schemaName . '.' . $object;
        }
        if ($table->catalogName !== null) {
            $object = $table->catalogName . '.' . $object;
        }

        // please refer to the following page for more details:
        // http://msdn2.microsoft.com/en-us/library/aa175805(SQL.80).aspx
        $sql = <<<'SQL'
SELECT
	[fk].[name] AS [fk_name],
	[cp].[name] AS [fk_column_name],
	OBJECT_NAME([fk].[referenced_object_id]) AS [uq_table_name],
	[cr].[name] AS [uq_column_name]
FROM
	[sys].[foreign_keys] AS [fk]
	INNER JOIN [sys].[foreign_key_columns] AS [fkc] ON
		[fk].[object_id] = [fkc].[constraint_object_id]
	INNER JOIN [sys].[columns] AS [cp] ON
		[fk].[parent_object_id] = [cp].[object_id] AND
		[fkc].[parent_column_id] = [cp].[column_id]
	INNER JOIN [sys].[columns] AS [cr] ON
		[fk].[referenced_object_id] = [cr].[object_id] AND
		[fkc].[referenced_column_id] = [cr].[column_id]
WHERE
	[fk].[parent_object_id] = OBJECT_ID(:object)
SQL;

        $rows = $this->db->createCommand($sql, [
            ':object' => $object,
        ])->queryAll();

        $table->foreignKeys = [];
        foreach ($rows as $row) {
            if (!isset($table->foreignKeys[$row['fk_name']])) {
                $table->foreignKeys[$row['fk_name']][] = $row['uq_table_name'];
            }
            $table->foreignKeys[$row['fk_name']][$row['fk_column_name']] = $row['uq_column_name'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findViewNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }

        $sql = <<<'SQL'
SELECT [t].[table_name]
FROM [INFORMATION_SCHEMA].[TABLES] AS [t]
WHERE [t].[table_schema] = :schema AND [t].[table_type] = 'VIEW'
ORDER BY [t].[table_name]
SQL;

        return $this->db->createCommand($sql, [':schema' => $schema])->queryColumn();
    }

    /**
     * Returns all unique indexes for the given table.
     *
     * Each array element is of the following structure:
     *
     * ```php
     * [
     *     'IndexName1' => ['col1' [, ...]],
     *     'IndexName2' => ['col2' [, ...]],
     * ]
     * ```
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
     * Loads multiple types of constraints and returns the specified ones.
     * @param string $tableName table name.
     * @param string $returnType return type:
     * - primaryKey
     * - foreignKeys
     * - uniques
     * - checks
     * - defaults
     * @return mixed constraints.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        static $sql = <<<'SQL'
SELECT
    [o].[name] AS [name],
    COALESCE([ccol].[name], [dcol].[name], [fccol].[name], [kiccol].[name]) AS [column_name],
    RTRIM([o].[type]) AS [type],
    OBJECT_SCHEMA_NAME([f].[referenced_object_id]) AS [foreign_table_schema],
    OBJECT_NAME([f].[referenced_object_id]) AS [foreign_table_name],
    [ffccol].[name] AS [foreign_column_name],
    [f].[update_referential_action_desc] AS [on_update],
    [f].[delete_referential_action_desc] AS [on_delete],
    [c].[definition] AS [check_expr],
    [d].[definition] AS [default_expr]
FROM (SELECT OBJECT_ID(:fullName) AS [object_id]) AS [t]
INNER JOIN [sys].[objects] AS [o]
    ON [o].[parent_object_id] = [t].[object_id] AND [o].[type] IN ('PK', 'UQ', 'C', 'D', 'F')
LEFT JOIN [sys].[check_constraints] AS [c]
    ON [c].[object_id] = [o].[object_id]
LEFT JOIN [sys].[columns] AS [ccol]
    ON [ccol].[object_id] = [c].[parent_object_id] AND [ccol].[column_id] = [c].[parent_column_id]
LEFT JOIN [sys].[default_constraints] AS [d]
    ON [d].[object_id] = [o].[object_id]
LEFT JOIN [sys].[columns] AS [dcol]
    ON [dcol].[object_id] = [d].[parent_object_id] AND [dcol].[column_id] = [d].[parent_column_id]
LEFT JOIN [sys].[key_constraints] AS [k]
    ON [k].[object_id] = [o].[object_id]
LEFT JOIN [sys].[index_columns] AS [kic]
    ON [kic].[object_id] = [k].[parent_object_id] AND [kic].[index_id] = [k].[unique_index_id]
LEFT JOIN [sys].[columns] AS [kiccol]
    ON [kiccol].[object_id] = [kic].[object_id] AND [kiccol].[column_id] = [kic].[column_id]
LEFT JOIN [sys].[foreign_keys] AS [f]
    ON [f].[object_id] = [o].[object_id]
LEFT JOIN [sys].[foreign_key_columns] AS [fc]
    ON [fc].[constraint_object_id] = [o].[object_id]
LEFT JOIN [sys].[columns] AS [fccol]
    ON [fccol].[object_id] = [fc].[parent_object_id] AND [fccol].[column_id] = [fc].[parent_column_id]
LEFT JOIN [sys].[columns] AS [ffccol]
    ON [ffccol].[object_id] = [fc].[referenced_object_id] AND [ffccol].[column_id] = [fc].[referenced_column_id]
ORDER BY [kic].[key_ordinal] ASC, [fc].[constraint_column_id] ASC
SQL;

        $resolvedName = $this->resolveTableName($tableName);
        $constraints = $this->db->createCommand($sql, [
            ':fullName' => $resolvedName->fullName,
        ])->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = ArrayHelper::index($constraints, null, ['type', 'name']);
        $result = [
            'primaryKey' => null,
            'foreignKeys' => [],
            'uniques' => [],
            'checks' => [],
            'defaults' => [],
        ];
        foreach ($constraints as $type => $names) {
            foreach ($names as $name => $constraint) {
                switch ($type) {
                    case 'PK':
                        $result['primaryKey'] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'F':
                        $result['foreignKeys'][] = new ForeignKeyConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'foreignSchemaName' => $constraint[0]['foreign_table_schema'],
                            'foreignTableName' => $constraint[0]['foreign_table_name'],
                            'foreignColumnNames' => ArrayHelper::getColumn($constraint, 'foreign_column_name'),
                            'onDelete' => str_replace('_', '', $constraint[0]['on_delete']),
                            'onUpdate' => str_replace('_', '', $constraint[0]['on_update']),
                        ]);
                        break;
                    case 'UQ':
                        $result['uniques'][] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'C':
                        $result['checks'][] = new CheckConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'expression' => $constraint[0]['check_expr'],
                        ]);
                        break;
                    case 'D':
                        $result['defaults'][] = new DefaultValueConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'value' => $constraint[0]['default_expr'],
                        ]);
                        break;
                }
            }
        }
        foreach ($result as $type => $data) {
            $this->setTableMetadata($tableName, $type, $data);
        }

        return $result[$returnType];
    }

    /**
     * {@inheritdoc}
     */
    public function quoteColumnName($name)
    {
        if (preg_match('/^\[.*\]$/', $name)) {
            return $name;
        }

        return parent::quoteColumnName($name);
    }

    /**
     * Retrieving inserted data from a primary key request of type uniqueidentifier (for SQL Server 2005 or later)
     * {@inheritdoc}
     */
    public function insert($table, $columns)
    {
        $command = $this->db->createCommand()->insert($table, $columns);
        if (!$command->execute()) {
            return false;
        }

        $isVersion2005orLater = version_compare($this->db->getSchema()->getServerVersion(), '9', '>=');
        $inserted = $isVersion2005orLater ? $command->pdoStatement->fetch() : [];

        $tableSchema = $this->getTableSchema($table);
        $result = [];
        foreach ($tableSchema->primaryKey as $name) {
            if ($tableSchema->columns[$name]->autoIncrement) {
                $result[$name] = $this->getLastInsertID($tableSchema->sequenceName);
                break;
            }
            // @see https://github.com/yiisoft/yii2/issues/13828 & https://github.com/yiisoft/yii2/issues/17474
            if (isset($inserted[$name])) {
                $result[$name] = $inserted[$name];
            } elseif (isset($columns[$name])) {
                $result[$name] = $columns[$name];
            } else {
                $result[$name] = $tableSchema->columns[$name]->defaultValue;
            }
        }

        return $result;
    }
}
