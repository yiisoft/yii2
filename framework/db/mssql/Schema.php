<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use Yii;
use yii\db\CheckConstraint;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\DefaultValueConstraint;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\ViewFinderTrait;
use yii\helpers\ArrayHelper;
use yii\db\Schema as BaseSchema;

use function count;
use function implode;
use function strcasecmp;

/**
 * Schema is the class for retrieving metadata from MS SQL Server databases (version 2019 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 *
 * @template T of ColumnSchema = ColumnSchema
 * @extends BaseSchema<T>
 */
class Schema extends BaseSchema implements ConstraintFinderInterface
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
     *
     * @param string $name The table name
     *
     * @return TableSchema Resolved table, schema, etc. names.
     */
    protected function resolveTableName($name)
    {
        $parts = $this->getTableNameParts($name);

        $partCount = count($parts);

        $last = $partCount - 1;
        $penultimate = $partCount - 2;
        $catalogIndex = $partCount === 4 ? 1 : 0;
        $tableName = $parts[$last];
        $schemaName = $partCount >= 2 ? $parts[$penultimate] : $this->defaultSchema;
        $catalogName = $partCount >= 3 ? $parts[$catalogIndex] : null;

        $fullName = match (true) {
            $catalogName !== null => "{$catalogName}.{$schemaName}.{$tableName}",
            $schemaName !== $this->defaultSchema => "{$schemaName}.{$tableName}",
            default => $tableName,
        };

        $resolvedName = new TableSchema();

        $resolvedName->name = $tableName;
        $resolvedName->schemaName = $schemaName;
        $resolvedName->catalogName = $catalogName;
        $resolvedName->fullName = $fullName;

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
     *
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-objects-transact-sql
     */
    protected function findTableNames($schema = '')
    {
        [$catalogName, $schemaName] = $this->resolveCatalogSchemaName($schema);

        $systemCatalogName = $this->quoteTableNameParts([$catalogName, 'sys']);

        $sql = <<<SQL
        SELECT [o].[name]
        FROM {$systemCatalogName}.[objects] AS [o]
        INNER JOIN {$systemCatalogName}.[schemas] AS [s] ON [s].[schema_id] = [o].[schema_id]
        WHERE [s].[name] = :schema
            AND [o].[type] IN ('U', 'V')
        ORDER BY [o].[name]
        SQL;

        return $this->db->createCommand(
            $sql,
            [':schema' => $schemaName],
        )->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = $this->resolveTableName($name);
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
        $resolvedName = $this->resolveTableName($tableName);

        $systemCatalogName = $this->quoteSystemCatalogName($resolvedName);

        $sql = <<<SQL
        SELECT
            [i].[name] AS [name],
            [iccol].[name] AS [column_name],
            [i].[is_unique] AS [index_is_unique],
            [i].[is_primary_key] AS [index_is_primary]
        FROM {$systemCatalogName}.[indexes] AS [i]
        INNER JOIN {$systemCatalogName}.[index_columns] AS [ic]
            ON [ic].[object_id] = [i].[object_id] AND [ic].[index_id] = [i].[index_id]
        INNER JOIN {$systemCatalogName}.[columns] AS [iccol]
            ON [iccol].[object_id] = [ic].[object_id] AND [iccol].[column_id] = [ic].[column_id]
        WHERE [i].[object_id] = OBJECT_ID(:fullName)
        ORDER BY [ic].[key_ordinal] ASC
        SQL;

        $indexes = $this->db->createCommand(
            $sql,
            [':fullName' => $this->quoteTableFullName($resolvedName)],
        )->queryAll();

        $indexes = $this->normalizePdoRowKeyCase($indexes, true);

        $indexes = ArrayHelper::index($indexes, null, 'name');

        $result = [];

        foreach ($indexes as $name => $index) {
            $result[] = new IndexConstraint(
                [
                    'isPrimary' => (bool)$index[0]['index_is_primary'],
                    'isUnique' => (bool)$index[0]['index_is_unique'],
                    'name' => $name,
                    'columnNames' => ArrayHelper::getColumn($index, 'column_name'),
                ],
            );
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
        return Yii::createObject(QueryBuilder::class, [$this->db]);
    }

    /**
     * Loads the column information into a [[ColumnSchema]] object.
     * @param array $info column information
     * @return T the column schema object
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

            if ($type === 'bit') {
                $column->type = 'boolean';
            }

            if (!empty($matches[2]) && strcasecmp($matches[2], 'max') !== 0) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int) $values[0];

                if (isset($values[1])) {
                    $column->scale = (int) $values[1];
                }
            }
        }

        $column->phpType = $this->getColumnPhpType($column);

        // store raw default for deferred resolution in `findColumns()`, where isPrimaryKey is known.
        $column->defaultValue = $info['column_default'];

        return $column;
    }

    /**
     * Collects the metadata of table columns.
     *
     * @param TableSchema $table The table metadata.
     *
     * @return bool Whether the table exists in the database.
     */
    protected function findColumns($table)
    {
        $systemCatalogName = $this->quoteSystemCatalogName($table);

        $sql = <<<SQL
        SELECT
            [c].[name] AS [column_name],
            CASE WHEN [c].[is_nullable] = 1 THEN 'YES' ELSE 'NO' END AS [is_nullable],
            CASE
                WHEN [t].[name] IN ('char','varchar','nchar','nvarchar','binary','varbinary') THEN
                    CASE
                        WHEN [c].[max_length] = -1 AND [t].[name] IN ('varchar','nvarchar','varbinary') THEN
                            [t].[name] + '(max)'
                        WHEN [t].[name] IN ('nchar','nvarchar') THEN
                            [t].[name] + '(' + CAST([c].[max_length] / 2 AS VARCHAR) + ')'
                        ELSE
                            [t].[name] + '(' + CAST([c].[max_length] AS VARCHAR) + ')'
                    END
                WHEN [t].[name] IN ('decimal','numeric') THEN
                    [t].[name] + '(' + CAST([c].[precision] AS VARCHAR) + ',' + CAST([c].[scale] AS VARCHAR) + ')'
                ELSE [t].[name]
            END AS [data_type],
            [dc].[definition] AS [column_default],
            [c].[is_identity],
            [c].[is_computed],
            CAST([ep].[value] AS NVARCHAR(MAX)) AS [comment]
        FROM {$systemCatalogName}.[columns] AS [c]
        INNER JOIN {$systemCatalogName}.[types] AS [t]
            ON [c].[system_type_id] = [t].[system_type_id]
            AND [t].[user_type_id] = [t].[system_type_id]
        LEFT JOIN {$systemCatalogName}.[default_constraints] AS [dc]
            ON [dc].[object_id] = [c].[default_object_id]
        LEFT JOIN {$systemCatalogName}.[extended_properties] AS [ep]
            ON [ep].[major_id] = [c].[object_id]
            AND [ep].[minor_id] = [c].[column_id]
            AND [ep].[class] = 1
            AND [ep].[name] = 'MS_Description'
        WHERE [c].[object_id] = OBJECT_ID(:fullName)
        ORDER BY [c].[column_id]
        SQL;

        $columns = $this->db->createCommand(
            $sql,
            [':fullName' => $this->quoteTableFullName($table)],
        )->queryAll();

        if (empty($columns)) {
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

            $column->defaultValue = $column->isPrimaryKey
                ? null
                : $column->defaultPhpTypecast($column->defaultValue);
            $table->columns[$column->name] = $column;
        }

        return true;
    }

    /**
     * Collects the constraint details for the given table and constraint type.
     *
     * @param TableSchema $table The table metadata.
     * @param string $type Either `PK` or `UQ`.
     *
     * @return array Each entry contains index_name and field_name.
     * @since 2.0.4
     *
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-key-constraints-transact-sql
     */
    protected function findTableConstraints($table, $type)
    {
        $systemCatalogName = $this->quoteSystemCatalogName($table);

        $sql = <<<SQL
        SELECT
            [kc].[name] AS [index_name],
            [col].[name] AS [field_name]
        FROM {$systemCatalogName}.[key_constraints] AS [kc]
        INNER JOIN {$systemCatalogName}.[index_columns] AS [ic]
            ON [ic].[object_id] = [kc].[parent_object_id]
            AND [ic].[index_id] = [kc].[unique_index_id]
        INNER JOIN {$systemCatalogName}.[columns] AS [col]
            ON [col].[object_id] = [ic].[object_id]
            AND [col].[column_id] = [ic].[column_id]
        WHERE [kc].[parent_object_id] = OBJECT_ID(:fullName)
            AND [kc].[type] = :type
        ORDER BY [ic].[key_ordinal] ASC
        SQL;

        return $this->db->createCommand(
            $sql,
            [
                ':fullName' => $this->quoteTableFullName($table),
                ':type' => $type,
            ],
        )->queryAll();
    }

    /**
     * Collects the primary key column details for the given table.
     *
     * @param TableSchema $table the table metadata
     */
    protected function findPrimaryKeys($table)
    {
        $result = [];

        foreach ($this->findTableConstraints($table, 'PK') as $row) {
            $result[] = $row['field_name'];
        }

        $table->primaryKey = $result;
    }

    /**
     * Collects the foreign key column details for the given table.
     *
     * @param TableSchema $table The table metadata
     */
    protected function findForeignKeys($table)
    {
        $systemCatalogName = $this->quoteSystemCatalogName($table);

        $databaseId = $this->getDatabaseIdExpression($table);

        $sql = <<<SQL
        SELECT
            [fk].[name] AS [fk_name],
            [cp].[name] AS [fk_column_name],
            OBJECT_NAME([fk].[referenced_object_id], {$databaseId}) AS [uq_table_name],
            [cr].[name] AS [uq_column_name]
        FROM
            {$systemCatalogName}.[foreign_keys] AS [fk]
            INNER JOIN {$systemCatalogName}.[foreign_key_columns] AS [fkc] ON
                [fk].[object_id] = [fkc].[constraint_object_id]
            INNER JOIN {$systemCatalogName}.[columns] AS [cp] ON
                [fk].[parent_object_id] = [cp].[object_id] AND
                [fkc].[parent_column_id] = [cp].[column_id]
            INNER JOIN {$systemCatalogName}.[columns] AS [cr] ON
                [fk].[referenced_object_id] = [cr].[object_id] AND
                [fkc].[referenced_column_id] = [cr].[column_id]
        WHERE
            [fk].[parent_object_id] = OBJECT_ID(:object)
        SQL;

        $rows = $this->db->createCommand(
            $sql,
            [':object' => $this->quoteTableFullName($table)],
        )->queryAll();

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
     *
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-views-transact-sql
     */
    protected function findViewNames($schema = '')
    {
        [$catalogName, $schemaName] = $this->resolveCatalogSchemaName($schema);

        $systemCatalogName = $this->quoteTableNameParts([$catalogName, 'sys']);

        $sql = <<<SQL
        SELECT [v].[name]
        FROM {$systemCatalogName}.[views] AS [v]
        INNER JOIN {$systemCatalogName}.[schemas] AS [s] ON [s].[schema_id] = [v].[schema_id]
        WHERE [s].[name] = :schema
        ORDER BY [v].[name]
        SQL;

        return $this->db->createCommand(
            $sql,
            [':schema' => $schemaName],
        )->queryColumn();
    }

    /**
     * Returns all unique indexes for the given table.
     *
     * Each array element is of the following structure:
     *
     * ```
     * [
     *     'IndexName1' => ['col1' [, ...]],
     *     'IndexName2' => ['col2' [, ...]],
     * ]
     * ```
     *
     * @param TableSchema $table The table metadata.
     *
     * @return array All unique indexes for the given table.
     * @since 2.0.4
     */
    public function findUniqueIndexes($table)
    {
        $result = [];

        foreach ($this->findTableConstraints($table, 'UQ') as $row) {
            $result[$row['index_name']][] = $row['field_name'];
        }

        return $result;
    }

    /**
     * Loads multiple types of constraints and returns the specified ones.
     *
     * @param string $tableName Table name.
     * @param string $returnType Return type:
     * - primaryKey
     * - foreignKeys
     * - uniques
     * - checks
     * - defaults
     *
     * @return mixed Constraint(s) of the specified type.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        $resolvedName = $this->resolveTableName($tableName);

        $systemCatalogName = $this->quoteSystemCatalogName($resolvedName);
        $databaseId = $this->getDatabaseIdExpression($resolvedName);

        $sql = <<<SQL
        SELECT
            [o].[name] AS [name],
            COALESCE([ccol].[name], [dcol].[name], [fccol].[name], [kiccol].[name]) AS [column_name],
            RTRIM([o].[type]) AS [type],
            OBJECT_SCHEMA_NAME([f].[referenced_object_id], {$databaseId}) AS [foreign_table_schema],
            OBJECT_NAME([f].[referenced_object_id], {$databaseId}) AS [foreign_table_name],
            [ffccol].[name] AS [foreign_column_name],
            [f].[update_referential_action_desc] AS [on_update],
            [f].[delete_referential_action_desc] AS [on_delete],
            [c].[definition] AS [check_expr],
            [d].[definition] AS [default_expr]
        FROM (SELECT OBJECT_ID(:fullName) AS [object_id]) AS [t]
        INNER JOIN {$systemCatalogName}.[objects] AS [o]
            ON [o].[parent_object_id] = [t].[object_id] AND [o].[type] IN ('PK', 'UQ', 'C', 'D', 'F')
        LEFT JOIN {$systemCatalogName}.[check_constraints] AS [c]
            ON [c].[object_id] = [o].[object_id]
        LEFT JOIN {$systemCatalogName}.[columns] AS [ccol]
            ON [ccol].[object_id] = [c].[parent_object_id] AND [ccol].[column_id] = [c].[parent_column_id]
        LEFT JOIN {$systemCatalogName}.[default_constraints] AS [d]
            ON [d].[object_id] = [o].[object_id]
        LEFT JOIN {$systemCatalogName}.[columns] AS [dcol]
            ON [dcol].[object_id] = [d].[parent_object_id] AND [dcol].[column_id] = [d].[parent_column_id]
        LEFT JOIN {$systemCatalogName}.[key_constraints] AS [k]
            ON [k].[object_id] = [o].[object_id]
        LEFT JOIN {$systemCatalogName}.[index_columns] AS [kic]
            ON [kic].[object_id] = [k].[parent_object_id] AND [kic].[index_id] = [k].[unique_index_id]
        LEFT JOIN {$systemCatalogName}.[columns] AS [kiccol]
            ON [kiccol].[object_id] = [kic].[object_id] AND [kiccol].[column_id] = [kic].[column_id]
        LEFT JOIN {$systemCatalogName}.[foreign_keys] AS [f]
            ON [f].[object_id] = [o].[object_id]
        LEFT JOIN {$systemCatalogName}.[foreign_key_columns] AS [fc]
            ON [fc].[constraint_object_id] = [o].[object_id]
        LEFT JOIN {$systemCatalogName}.[columns] AS [fccol]
            ON [fccol].[object_id] = [fc].[parent_object_id] AND [fccol].[column_id] = [fc].[parent_column_id]
        LEFT JOIN {$systemCatalogName}.[columns] AS [ffccol]
            ON [ffccol].[object_id] = [fc].[referenced_object_id] AND [ffccol].[column_id] = [fc].[referenced_column_id]
        ORDER BY [kic].[key_ordinal] ASC, [fc].[constraint_column_id] ASC
        SQL;

        $constraints = $this->db->createCommand(
            $sql,
            [':fullName' => $this->quoteTableFullName($resolvedName)],
        )->queryAll();

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
                        $result['primaryKey'] = new Constraint(
                            [
                                'name' => $name,
                                'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            ],
                        );

                        break;
                    case 'F':
                        $result['foreignKeys'][] = new ForeignKeyConstraint(
                            [
                                'name' => $name,
                                'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                                'foreignSchemaName' => $constraint[0]['foreign_table_schema'],
                                'foreignTableName' => $constraint[0]['foreign_table_name'],
                                'foreignColumnNames' => ArrayHelper::getColumn($constraint, 'foreign_column_name'),
                                'onDelete' => str_replace('_', '', $constraint[0]['on_delete']),
                                'onUpdate' => str_replace('_', '', $constraint[0]['on_update']),
                            ],
                        );

                        break;
                    case 'UQ':
                        $result['uniques'][] = new Constraint(
                            [
                                'name' => $name,
                                'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            ],
                        );

                        break;
                    case 'C':
                        $result['checks'][] = new CheckConstraint(
                            [
                                'name' => $name,
                                'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                                'expression' => $constraint[0]['check_expr'],
                            ],
                        );

                        break;
                    case 'D':
                        $result['defaults'][] = new DefaultValueConstraint(
                            [
                                'name' => $name,
                                'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                                'value' => $constraint[0]['default_expr'],
                            ],
                        );

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
     * {@inheritdoc}
     *
     * Retrieves the inserted row via the `OUTPUT INSERTED.*` block emitted by
     * `\yii\db\mssql\QueryBuilder::insert()` so that `IDENTITY` values, computed columns, and
     * `uniqueidentifier` defaults are returned to the caller.
     */
    public function insert($table, $columns)
    {
        $command = $this->db->createCommand()->insert($table, $columns);
        if (!$command->execute()) {
            return false;
        }

        $inserted = $command->pdoStatement->fetch();

        $tableSchema = $this->getTableSchema($table);
        $result = [];
        foreach ($tableSchema->primaryKey as $name) {
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

    /**
     * {@inheritdoc}
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return Yii::createObject(ColumnSchemaBuilder::class, [$type, $length, $this->db]);
    }

    /**
     * Quotes the fully qualified table name from resolved raw table metadata.
     *
     * @param TableSchema $table The table metadata.
     *
     * @return string The quoted fully qualified table name.
     */
    private function quoteTableFullName(TableSchema $table): string
    {
        return $this->quoteTableNameParts([$table->catalogName, $table->schemaName, $table->name]);
    }

    /**
     * Quotes the SQL Server system catalog name for the table catalog context.
     *
     * @param TableSchema $table The table metadata.
     *
     * @return string The quoted system catalog name.
     */
    private function quoteSystemCatalogName(TableSchema $table): string
    {
        return $this->quoteTableNameParts([$table->catalogName, 'sys']);
    }

    /**
     * Builds a SQL Server database ID expression for metadata functions.
     *
     * @param TableSchema $table The table metadata.
     *
     * @return string The database ID expression.
     */
    private function getDatabaseIdExpression(TableSchema $table): string
    {
        if ($table->catalogName === null) {
            return 'DB_ID()';
        }

        return 'DB_ID(' . $this->db->quoteValue($table->catalogName) . ')';
    }

    /**
     * Quotes qualified table name parts without reparsing raw parts that may contain dots.
     *
     * @param array<int, string|null> $parts The raw table name parts.
     *
     * @return string The quoted qualified name.
     */
    private function quoteTableNameParts(array $parts): string
    {
        $quotedParts = [];

        foreach ($parts as $part) {
            if ($part !== null) {
                $quotedParts[] = $this->quoteSimpleTableName($part);
            }
        }

        return implode('.', $quotedParts);
    }

    /**
     * Resolves a schema argument into optional catalog and schema names.
     *
     * @param string $schema The schema argument.
     *
     * @return array{string|null, string} The catalog and schema names.
     */
    private function resolveCatalogSchemaName(string $schema): array
    {
        if ($schema === '') {
            return [null, $this->defaultSchema];
        }

        $parts = $this->getTableNameParts($schema);

        $partCount = count($parts);

        if ($partCount > 1) {
            return [$parts[$partCount - 2], $parts[$partCount - 1]];
        }

        return [null, $parts[0]];
    }
}
