<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\conditions\InCondition;
use yii\db\conditions\LikeCondition;
use yii\db\Expression;

use function array_flip;
use function array_intersect_key;
use function count;
use function implode;
use function preg_replace;
use function strrpos;

/**
 * QueryBuilder is the query builder for MS SQL Server databases (version 2019 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK => 'int IDENTITY PRIMARY KEY',
        Schema::TYPE_UPK => 'int IDENTITY PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigint IDENTITY PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigint IDENTITY PRIMARY KEY',
        Schema::TYPE_CHAR => 'nchar(1)',
        Schema::TYPE_STRING => 'nvarchar(255)',
        Schema::TYPE_TEXT => 'nvarchar(max)',
        Schema::TYPE_TINYINT => 'tinyint',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'int',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DOUBLE => 'float',
        Schema::TYPE_DECIMAL => 'decimal(18,0)',
        Schema::TYPE_DATETIME => 'datetime',
        Schema::TYPE_TIMESTAMP => 'datetime',
        Schema::TYPE_TIME => 'time',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'varbinary(max)',
        Schema::TYPE_BOOLEAN => 'bit',
        Schema::TYPE_MONEY => 'decimal(19,4)',
    ];

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return [
            ...parent::defaultExpressionBuilders(),
            InCondition::class => conditions\InConditionBuilder::class,
            LikeCondition::class => conditions\LikeConditionBuilder::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        // Preserve limit(0) → zero rows semantics across drivers; SQL Server rejects FETCH NEXT `0`,
        // so wrap with WHERE 1=0 (the optimizer constant-folds this into an empty scan).
        if ((string) $limit === '0') {
            return "SELECT * FROM ({$sql}) sub WHERE 1=0";
        }

        $orderBy = $this->buildOrderBy($orderBy);

        if (!$this->hasOffset($offset) && !$this->hasLimit($limit)) {
            return $orderBy === '' ? $sql : "{$sql}{$this->separator}{$orderBy}";
        }

        if ($orderBy === '') {
            // SELECT DISTINCT requires ORDER BY items to appear in the select list, so use ordinal `1`;
            // otherwise `ORDER BY (SELECT NULL)` is preferred because it tolerates unorderable column types
            // (`text`, `ntext`, `image`, `xml`, `geography`, `geometry`) which `ORDER BY 1` cannot sort.
            $orderBy = str_starts_with($sql, 'SELECT DISTINCT')
                ? 'ORDER BY 1'
                : 'ORDER BY (SELECT NULL)';
        }

        $offset = $this->hasOffset($offset) ? $offset : 0;

        $sql .= "{$this->separator}{$orderBy}{$this->separator}OFFSET {$offset} ROWS";

        if ($this->hasLimit($limit)) {
            $sql .= "{$this->separator}FETCH NEXT {$limit} ROWS ONLY";
        }

        return $sql;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     *
     * @param string $oldName The table to be renamed. The name will be properly quoted by the method.
     * @param string $newName The new table name. The name will be properly quoted by the method.
     *
     * @return string The SQL statement for renaming a DB table.
     */
    public function renameTable($oldName, $newName)
    {
        $schema = $this->db->getSchema();

        $oldTableName = Quoter::escapeLiteralValue($this->db->quoteTableName($oldName));

        $newTableName = $this->db->quoteSql($this->db->quoteTableName($newName));

        $newTableName = Quoter::extractSimpleIdentifier($newTableName);
        $newTableName = Quoter::escapeLiteralValue($schema->unquoteSimpleTableName($newTableName));

        return <<<SQL
        EXEC sp_rename @objname = N'{$oldTableName}', @newname = N'{$newTableName}', @objtype = N'OBJECT'
        SQL;
    }

    /**
     * Builds a SQL statement for renaming a column.
     *
     * @param string $table The table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $oldName The old name of the column. The name will be properly quoted by the method.
     * @param string $newName The new name of the column. The name will be properly quoted by the method.
     *
     * @return string The SQL statement for renaming a DB column.
     */
    public function renameColumn($table, $oldName, $newName)
    {
        $schema = $this->db->getSchema();

        $table = Quoter::escapeLiteralValue($this->db->quoteTableName($table));
        $oldName = Quoter::escapeLiteralValue($this->db->quoteColumnName($oldName));

        $newName = $this->db->quoteSql($this->db->quoteColumnName($newName));

        $newName = Quoter::extractSimpleIdentifier($newName);
        $newName = Quoter::escapeLiteralValue($schema->unquoteSimpleColumnName($newName));

        return <<<SQL
        EXEC sp_rename @objname = N'{$table}.{$oldName}', @newname = N'{$newName}', @objtype = N'COLUMN'
        SQL;
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     *
     * Drops the default, check, and unique constraints attached to the column before the `ALTER COLUMN` statement,
     * because SQL Server rejects altering a column while such constraints are bound to it. Default, check, and unique
     * constraints defined by a {@see ColumnSchemaBuilder} type are re-created after the column is altered.
     *
     * @param string $table The table whose column is to be changed. The table name will be properly quoted by the
     * method.
     * @param string $column The name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type The new column type. The {@see getColumnType} method will be invoked to convert abstract
     * column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept in the
     * generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become
     * 'varchar(255) not null'.
     *
     * @throws NotSupportedException if this is not supported by the underlying DBMS.
     *
     * @return string The SQL statement for changing the definition of a column.
     */
    public function alterColumn($table, $column, $type)
    {
        $tableName = $this->db->quoteTableName($table);
        $columnName = $this->db->quoteColumnName($column);

        $dropConstraintsSql = $this->dropConstraintsForColumn($tableName, $column, ['D', 'C', 'UQ']);

        $sqlAfter = [];

        $constraintBase = preg_replace('/[^a-z0-9_]/i', '', "{$table}_{$column}");

        if ($type instanceof ColumnSchemaBuilder) {
            $type->setAlterColumnFormat();
            $defaultValue = $type->getDefaultValue();

            if ($defaultValue !== null) {
                $sqlAfter[] = $this->addDefaultValue(
                    "DF_{$constraintBase}",
                    $table,
                    $column,
                    $defaultValue instanceof Expression ? $defaultValue : new Expression($defaultValue),
                );
            }

            $checkValue = $type->getCheckValue();

            if ($checkValue !== null) {
                $columnCheckConstraint = $this->db->quoteColumnName("CK_{$constraintBase}");

                $checkSQL = $checkValue instanceof Expression ?  $checkValue : new Expression($checkValue);

                $sqlAfter[] = <<<SQL
                ALTER TABLE {$tableName} ADD CONSTRAINT {$columnCheckConstraint} CHECK ({$checkSQL})
                SQL;
            }

            if ($type->isUnique()) {
                $columnUniqueConstraint = $this->db->quoteColumnName("UQ_{$constraintBase}");

                $sqlAfter[] = <<<SQL
                ALTER TABLE {$tableName} ADD CONSTRAINT {$columnUniqueConstraint} UNIQUE ({$columnName})
                SQL;
            }
        }

        $columnType = $this->getColumnType($type);

        return implode(
            "\n",
            [
                $dropConstraintsSql,
                <<<SQL
                ALTER TABLE {$tableName} ALTER COLUMN {$columnName} {$columnType}
                SQL,
                ...$sqlAfter,
            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addDefaultValue($name, $table, $column, $value)
    {
        $tableName = $this->db->quoteTableName($table);
        $constraintName = $this->db->quoteColumnName($name);

        $defaultValue = match ($value) {
            null => 'NULL',
            false => '0',
            true => '1',
            default => (string) $this->db->quoteValue($value),
        };

        $columnName = $this->db->quoteColumnName($column);

        return <<<SQL
        ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} DEFAULT {$defaultValue} FOR {$columnName}
        SQL;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-default-constraints-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/t-sql/statements/alter-table-transact-sql
     */
    public function dropDefaultValue($name, $table)
    {
        $tableName = Quoter::escapeLiteralValue($this->db->quoteTableName($table));
        $constraintName = Quoter::escapeLiteralValue($name);

        return <<<SQL
        DECLARE @tableName NVARCHAR(MAX) = N'{$tableName}'
        DECLARE @constraintName SYSNAME = N'{$constraintName}'
        DECLARE @dropSql NVARCHAR(MAX)

        SELECT @dropSql = N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name])
        FROM [sys].[default_constraints] AS [dc]
        WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName, N'U')
            AND [dc].[name] = @constraintName

        IF @dropSql IS NULL
        BEGIN
            THROW 50000, 'Default constraint not found on table.', 1;
        END

        EXEC (@dropSql)
        SQL;
    }

    /**
     * Creates a SQL statement for resetting the sequence value of a table's primary key.
     *
     * The sequence will be reset such that the primary key of the next new row inserted will have the specified value
     * or the next value after the current maximum identity value.
     *
     * @param string $tableName The name of the table whose primary key sequence will be reset.
     * @param int|null $value The integer value for the primary key of the next new row inserted.
     * If this is not set, the next new row's primary key will have the next value after the current maximum identity
     * value.
     *
     * @throws InvalidArgumentException if the table does not exist or there is no sequence associated with the table.
     *
     * @return string The SQL statement for resetting sequence.
     *
     * @see https://learn.microsoft.com/en-us/sql/t-sql/database-console-commands/dbcc-checkident-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-identity-columns-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/t-sql/functions/ident-seed-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/t-sql/functions/ident-incr-transact-sql
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);

        if ($table !== null && $table->sequenceName !== null) {
            $quotedTableName = $this->db->quoteTableName($tableName);

            $tableNameLiteral = Quoter::escapeLiteralValue($quotedTableName);

            $requestedNextValue = $value === null ? 'NULL' : (string) (int) $value;

            $systemCatalogName = $this->qualifiedSystemCatalog(
                $table instanceof TableSchema ? $table->catalogName : null,
            );

            return <<<SQL
            DECLARE @tableName NVARCHAR(MAX) = N'{$tableNameLiteral}'
            DECLARE @requestedNextValue DECIMAL(38, 0) = {$requestedNextValue}
            DECLARE @identityColumn SYSNAME
            DECLARE @seedValue DECIMAL(38, 0)
            DECLARE @incrementValue DECIMAL(38, 0)
            DECLARE @lastValue DECIMAL(38, 0)
            DECLARE @maxValue DECIMAL(38, 0)
            DECLARE @reseedValue DECIMAL(38, 0)
            DECLARE @maxSql NVARCHAR(MAX)
            DECLARE @checkIdentSql NVARCHAR(MAX)

            SELECT
                @identityColumn = [name],
                @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
            FROM {$systemCatalogName}.[identity_columns]
            WHERE [object_id] = OBJECT_ID(@tableName, N'U')

            IF @identityColumn IS NULL
            BEGIN
                THROW 50000, 'Identity column not found on table.', 1;
            END

            SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                + QUOTENAME(@identityColumn)
                + N')) FROM '
                + @tableName

            EXEC sp_executesql
                @maxSql,
                N'@maxValue DECIMAL(38, 0) OUTPUT',
                @maxValue OUTPUT

            SET @reseedValue = CASE
                WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                    THEN @requestedNextValue - @incrementValue
                WHEN @requestedNextValue IS NOT NULL
                    THEN @requestedNextValue
                WHEN @maxValue IS NOT NULL
                    THEN @maxValue
                WHEN @lastValue IS NOT NULL
                    THEN @seedValue - @incrementValue
                ELSE @seedValue
            END

            SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                + REPLACE(@tableName, '''', '''''')
                + N''', RESEED, '
                + CONVERT(NVARCHAR(50), @reseedValue)
                + N')'

            EXEC (@checkIdentSql)
            SQL;
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is not sequence associated with table '$tableName'.");
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity checks on table constraints.
     *
     * Resolves the table and schema names without loading metadata, so the statement is built without opening a
     * database connection. A single table yields a direct `ALTER TABLE` statement; an empty table targets every base
     * table in the schema, enumerated server-side from `sys.tables` (views excluded) and run in one batch.
     *
     * Enabling uses `WITH CHECK CHECK CONSTRAINT ALL`, re-validating existing rows and marking the constraints
     * trusted; disabling uses `NOCHECK CONSTRAINT ALL`.
     *
     * @param bool $check Whether to enable (`true`) or disable (`false`) the integrity checks.
     * @param string $schema The schema of the tables. Defaults to an empty string, meaning the default schema.
     * @param string $table The table name. Defaults to an empty string, meaning every table in the schema.
     *
     * @return string The SQL statement for enabling or disabling the integrity checks.
     *
     * @see https://learn.microsoft.com/en-us/sql/t-sql/statements/alter-table-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-tables-transact-sql
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $constraintCheck = $check ? 'WITH CHECK CHECK' : 'NOCHECK';

        if ($table !== '') {
            $tableName = $this->db->quoteTableName($schema === '' ? $table : "{$schema}.{$table}");

            return <<<SQL
            ALTER TABLE {$tableName} {$constraintCheck} CONSTRAINT ALL
            SQL;
        }

        /** @var Schema $dbSchema */
        $dbSchema = $this->db->getSchema();

        [$catalogName, $schemaName] = $dbSchema->resolveRawCatalogSchemaName($schema);
        $systemCatalog = $this->qualifiedSystemCatalog($catalogName);

        $catalogNameLiteral = $catalogName === null ? 'NULL' : "N'" . Quoter::escapeLiteralValue($catalogName) . "'";

        $schemaNameLiteral = "N'" . Quoter::escapeLiteralValue($schemaName) . "'";

        return <<<SQL
        DECLARE @catalogName SYSNAME = {$catalogNameLiteral}
        DECLARE @schemaName SYSNAME = {$schemaNameLiteral}
        DECLARE @sql NVARCHAR(MAX)

        SELECT @sql = STRING_AGG(
            CONVERT(
                NVARCHAR(MAX),
                N'ALTER TABLE '
                    + COALESCE(QUOTENAME(@catalogName) + N'.', N'')
                    + QUOTENAME(@schemaName)
                    + N'.'
                    + QUOTENAME([t].[name])
                    + N' {$constraintCheck} CONSTRAINT ALL'
            ),
            N'; '
        )
        FROM {$systemCatalog}.[tables] AS [t]
        INNER JOIN {$systemCatalog}.[schemas] AS [s] ON [s].[schema_id] = [t].[schema_id]
        WHERE [s].[name] = @schemaName

        IF @sql IS NOT NULL
            EXEC (@sql)
        SQL;
    }

     /**
      * Builds a SQL command for adding or updating a comment to a table or a column. The command built will check if a comment
      * already exists. If so, it will be updated, otherwise, it will be added.
      *
      * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
      * @param string $table the table to be commented or whose column is to be commented. The table name will be
      * properly quoted by the method.
      * @param string|null $column optional. The name of the column to be commented. If empty, the command will add the
      * comment to the table instead. The column name will be properly quoted by the method.
      * @return string the SQL statement for adding a comment.
      * @throws InvalidArgumentException if the table does not exist.
      * @since 2.0.24
      */
    protected function buildAddCommentSql($comment, $table, $column = null)
    {
        $tableSchema = $this->db->schema->getTableSchema($table);

        if ($tableSchema === null) {
            throw new InvalidArgumentException("Table not found: $table");
        }

        $schemaName = $tableSchema->schemaName ? "N'" . $tableSchema->schemaName . "'" : 'SCHEMA_NAME()';
        $tableName = 'N' . $this->db->quoteValue($tableSchema->name);
        $columnName = $column ? 'N' . $this->db->quoteValue($column) : null;
        $comment = 'N' . $this->db->quoteValue($comment);

        $functionParams = "
            @name = N'MS_description',
            @value = $comment,
            @level0type = N'SCHEMA', @level0name = $schemaName,
            @level1type = N'TABLE', @level1name = $tableName"
            . ($column ? ", @level2type = N'COLUMN', @level2name = $columnName" : '') . ';';

        return "
            IF NOT EXISTS (
                    SELECT 1
                    FROM fn_listextendedproperty (
                        N'MS_description',
                        'SCHEMA', $schemaName,
                        'TABLE', $tableName,
                        " . ($column ? "'COLUMN', $columnName " : ' DEFAULT, DEFAULT ') . "
                    )
            )
                EXEC sys.sp_addextendedproperty $functionParams
            ELSE
                EXEC sys.sp_updateextendedproperty $functionParams
        ";
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        return $this->buildAddCommentSql($comment, $table, $column);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnTable($table, $comment)
    {
        return $this->buildAddCommentSql($comment, $table);
    }

    /**
     * Builds a SQL command for removing a comment from a table or a column. The command built will check if a comment
     * already exists before trying to perform the removal.
     *
     * @param string $table the table that will have the comment removed or whose column will have the comment removed.
     * The table name will be properly quoted by the method.
     * @param string|null $column optional. The name of the column whose comment will be removed. If empty, the command
     * will remove the comment from the table instead. The column name will be properly quoted by the method.
     * @return string the SQL statement for removing the comment.
     * @throws InvalidArgumentException if the table does not exist.
     * @since 2.0.24
     */
    protected function buildRemoveCommentSql($table, $column = null)
    {
        $tableSchema = $this->db->schema->getTableSchema($table);

        if ($tableSchema === null) {
            throw new InvalidArgumentException("Table not found: $table");
        }

        $schemaName = $tableSchema->schemaName ? "N'" . $tableSchema->schemaName . "'" : 'SCHEMA_NAME()';
        $tableName = 'N' . $this->db->quoteValue($tableSchema->name);
        $columnName = $column ? 'N' . $this->db->quoteValue($column) : null;

        return "
            IF EXISTS (
                    SELECT 1
                    FROM fn_listextendedproperty (
                        N'MS_description',
                        'SCHEMA', $schemaName,
                        'TABLE', $tableName,
                        " . ($column ? "'COLUMN', $columnName " : ' DEFAULT, DEFAULT ') . "
                    )
            )
                EXEC sys.sp_dropextendedproperty
                    @name = N'MS_description',
                    @level0type = N'SCHEMA', @level0name = $schemaName,
                    @level1type = N'TABLE', @level1name = $tableName"
                    . ($column ? ", @level2type = N'COLUMN', @level2name = $columnName" : '') . ';';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromColumn($table, $column)
    {
        return $this->buildRemoveCommentSql($table, $column);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromTable($table)
    {
        return $this->buildRemoveCommentSql($table);
    }

    /**
     * Returns an array of column names given model name.
     *
     * @param string|null $modelClass name of the model class
     * @return array|null array of column names
     */
    protected function getAllColumnNames($modelClass = null)
    {
        if (!$modelClass) {
            return null;
        }
        /** @var \yii\db\ActiveRecord $modelClass */
        $schema = $modelClass::getTableSchema();
        return array_keys($schema->columns);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function selectExists($rawSql)
    {
        return 'SELECT CASE WHEN EXISTS(' . $rawSql . ') THEN 1 ELSE 0 END';
    }

    /**
     * {@inheritdoc}
     *
     * Wraps the `INSERT` with an `OUTPUT INSERTED.* INTO @temporary_inserted` block so that the inserted row
     * (including computed columns and `IDENTITY` values) can be retrieved by the caller.
     */
    public function insert($table, $columns, &$params)
    {
        $schema = $this->db->getTableSchema($table);

        if ($schema === null) {
            throw new InvalidArgumentException("Table not found: {$table}");
        }

        [$names, $placeholders, $values, $params] = $this->prepareInsertValues($table, $columns, $params);

        $cols = [];
        $outputColumns = [];

        foreach ($schema->columns as $column) {
            if (!$column instanceof ColumnSchema || $column->isComputed) {
                continue;
            }

            $quoteColumnName = $this->db->quoteColumnName($column->name);

            $cols[] = "{$quoteColumnName} {$column->getOutputColumnDeclaration()} " . ($column->allowNull ? 'NULL' : '');
            $outputColumns[] = "INSERTED.{$quoteColumnName}";
        }

        $countColumns = count($outputColumns);

        $sql = 'INSERT INTO ' . $this->db->quoteTableName($table)
            . (!empty($names) ? ' (' . implode(', ', $names) . ')' : '')
            . ($countColumns ? ' OUTPUT ' . implode(',', $outputColumns) . ' INTO @temporary_inserted' : '')
            . (!empty($placeholders) ? ' VALUES (' . implode(', ', $placeholders) . ')' : $values);

        if ($countColumns) {
            $tempTableCols = implode(', ', $cols);

            $sql = <<<SQL
            SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ({$tempTableCols});{$sql};SELECT * FROM @temporary_inserted
            SQL;
        }

        return $sql;
    }

    /**
     * {@inheritdoc}
     * @see https://docs.microsoft.com/en-us/sql/t-sql/statements/merge-transact-sql
     * @see https://weblogs.sqlteam.com/dang/2009/01/31/upsert-race-condition-with-merge/
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        list($uniqueNames, $insertNames, $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns, $constraints);
        if (empty($uniqueNames)) {
            return $this->insert($table, $insertColumns, $params);
        }
        if ($updateNames === []) {
            // there are no columns to update
            $updateColumns = false;
        }

        $onCondition = ['or'];
        $quotedTableName = $this->db->quoteTableName($table);
        foreach ($constraints as $constraint) {
            $constraintCondition = ['and'];
            foreach ($constraint->columnNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                $constraintCondition[] = "$quotedTableName.$quotedName=[EXCLUDED].$quotedName";
            }
            $onCondition[] = $constraintCondition;
        }
        $on = $this->buildCondition($onCondition, $params);
        list(, $placeholders, $values, $params) = $this->prepareInsertValues($table, $insertColumns, $params);

        $mergeSql = 'MERGE ' . $this->db->quoteTableName($table) . ' WITH (HOLDLOCK) '
            . 'USING (' . (!empty($placeholders) ? 'VALUES (' . implode(', ', $placeholders) . ')' : ltrim($values, ' ')) . ') AS [EXCLUDED] (' . implode(', ', $insertNames) . ') '
            . "ON ($on)";
        $insertValues = [];
        foreach ($insertNames as $name) {
            $quotedName = $this->db->quoteColumnName($name);
            if (strrpos($quotedName, '.') === false) {
                $quotedName = '[EXCLUDED].' . $quotedName;
            }
            $insertValues[] = $quotedName;
        }
        $insertSql = 'INSERT (' . implode(', ', $insertNames) . ')'
            . ' VALUES (' . implode(', ', $insertValues) . ')';
        if ($updateColumns === false) {
            return "$mergeSql WHEN NOT MATCHED THEN $insertSql;";
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                if (strrpos($quotedName, '.') === false) {
                    $quotedName = '[EXCLUDED].' . $quotedName;
                }
                $updateColumns[$name] = new Expression($quotedName);
            }
        }

        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        $updateSql = 'UPDATE SET ' . implode(', ', $updates);
        return "$mergeSql WHEN MATCHED THEN $updateSql WHEN NOT MATCHED THEN $insertSql;";
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnType($type)
    {
        $columnType = parent::getColumnType($type);
        // remove unsupported keywords
        $columnType = preg_replace("/\s*comment '.*'/i", '', $columnType);
        $columnType = preg_replace('/ first$/i', '', $columnType);

        return $columnType;
    }

    /**
     * Drop all constraints before column delete.
     *
     * {@inheritdoc}
     */
    public function dropColumn($table, $column)
    {
        $tableName = $this->db->quoteTableName($table);
        $columnName = $this->db->quoteColumnName($column);

        $dropConstraintsSql = $this->dropConstraintsForColumn($tableName, $column);

        return <<<SQL
        {$dropConstraintsSql}
        ALTER TABLE {$tableName} DROP COLUMN {$columnName}
        SQL;
    }

    /**
     * {@inheritdoc}
     */
    protected function extractAlias($table)
    {
        if (Quoter::isIdentifierBracketQuoted($table)) {
            return false;
        }

        return parent::extractAlias($table);
    }

    /**
     * Builds a SQL batch that drops the constraints attached to a column.
     *
     * Generates one `ALTER TABLE ... DROP CONSTRAINT` command per matching constraint and executes them in a single
     * dynamic batch. Foreign key constraints are dropped first because a primary key or unique constraint cannot be
     * dropped while a foreign key references it.
     *
     * @param string $table The table whose constraints are to be dropped. The name must already be quoted by the
     * caller.
     * @param string $column The column whose constraints are to be dropped. The raw, unquoted name is matched verbatim
     * against the system catalog; single quotes are escaped by the method.
     * @param string[] $types The constraint types: `'D'` - default, `'C'` - check, `'PK'` - primary key,
     * `'UQ'` - unique, `'F'` - foreign key. Leave empty to drop the constraints of all these types.
     *
     * @return string The DROP CONSTRAINTS SQL batch.
     *
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-default-constraints-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-check-constraints-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-sql-expression-dependencies-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-key-constraints-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-index-columns-transact-sql
     * @see https://learn.microsoft.com/en-us/sql/relational-databases/system-catalog-views/sys-foreign-key-columns-transact-sql
     */
    private function dropConstraintsForColumn($table, $column, $types = [])
    {
        $columnName = Quoter::escapeLiteralValue($column);

        $subqueries = [
            'F' => <<<SQL
                SELECT DISTINCT N'ALTER TABLE '
                    + QUOTENAME(OBJECT_SCHEMA_NAME([fk].[parent_object_id])) + N'.'
                    + QUOTENAME(OBJECT_NAME([fk].[parent_object_id]))
                    + N' DROP CONSTRAINT ' + QUOTENAME([fk].[name]) AS [sql], 0 AS [ord]
                FROM [sys].[foreign_keys] AS [fk]
                JOIN [sys].[foreign_key_columns] AS [fkc] ON [fkc].[constraint_object_id]=[fk].[object_id]
                JOIN [sys].[columns] AS [pc] ON [pc].[object_id]=[fkc].[parent_object_id] AND [pc].[column_id]=[fkc].[parent_column_id]
                JOIN [sys].[columns] AS [rc] ON [rc].[object_id]=[fkc].[referenced_object_id] AND [rc].[column_id]=[fkc].[referenced_column_id]
                WHERE ([fkc].[parent_object_id]=OBJECT_ID(@tableName) AND [pc].[name]=@columnName)
                    OR ([fkc].[referenced_object_id]=OBJECT_ID(@tableName) AND [rc].[name]=@columnName)
            SQL,
            'D' => <<<SQL
                SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                FROM [sys].[default_constraints] AS [dc]
                JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
            SQL,
            'C' => <<<SQL
                SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                FROM [sys].[check_constraints] AS [cc]
                JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                    AND (
                        [cc].[parent_column_id]=[c].[column_id]
                        OR EXISTS (
                            SELECT 1
                            FROM [sys].[sql_expression_dependencies] AS [sed]
                            WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                AND [sed].[referenced_minor_id]=[c].[column_id]
                        )
                    )
            SQL,
        ];

        foreach (['PK', 'UQ'] as $keyType) {
            $subqueries[$keyType] = <<<SQL
                SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                FROM [sys].[key_constraints] AS [kc]
                JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'{$keyType}'
            SQL;
        }

        $selectedSubqueries = $types === [] ? $subqueries : array_intersect_key($subqueries, array_flip($types));

        $unionSql = implode("\n    UNION\n", $selectedSubqueries);

        return <<<SQL
        DECLARE @tableName NVARCHAR(MAX) = N'{$table}'
        DECLARE @columnName NVARCHAR(MAX) = N'{$columnName}'
        DECLARE @dropCommands NVARCHAR(MAX)

        SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
        FROM (
        {$unionSql}
        ) AS [cons]

        IF @dropCommands IS NOT NULL
            EXEC (@dropCommands)
        SQL;
    }

    /**
     * {@inheritdoc}
     *
     * SQL Server does not support the `RECURSIVE` keyword for CTEs. Recursion is implicit when a CTE references itself.
     */
    public function buildWithQueries($withs, &$params)
    {
        if ($withs === null || $withs === []) {
            return '';
        }

        foreach ($withs as $i => $with) {
            $with['recursive'] = false;
            $withs[$i] = $with;
        }

        return parent::buildWithQueries($withs, $params);
    }

    /**
     * Returns the `[sys]` system catalog name, optionally qualified by the database catalog.
     *
     * @param string|null $catalogName The database catalog name, or `null` to target the current database.
     *
     * @return string The `[sys]` schema name, prefixed with the quoted catalog when one is provided.
     */
    private function qualifiedSystemCatalog(?string $catalogName): string
    {
        return $catalogName === null
            ? '[sys]'
            : $this->db->getSchema()->quoteSimpleTableName($catalogName) . '.[sys]';
    }
}
