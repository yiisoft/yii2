<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\TableSchema;

/**
 * QueryBuilder is the query builder for MS SQL Server databases (version 2008 and above).
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
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\conditions\InCondition' => 'yii\db\mssql\conditions\InConditionBuilder',
            'yii\db\conditions\LikeCondition' => 'yii\db\mssql\conditions\LikeConditionBuilder',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        if (!$this->hasOffset($offset) && !$this->hasLimit($limit)) {
            $orderBy = $this->buildOrderBy($orderBy);
            return $orderBy === '' ? $sql : $sql . $this->separator . $orderBy;
        }

        if (version_compare($this->db->getSchema()->getServerVersion(), '11', '<')) {
            return $this->oldBuildOrderByAndLimit($sql, $orderBy, $limit, $offset);
        }

        return $this->newBuildOrderByAndLimit($sql, $orderBy, $limit, $offset);
    }

    /**
     * Builds the ORDER BY/LIMIT/OFFSET clauses for SQL SERVER 2012 or newer.
     * @param string $sql the existing SQL (without ORDER BY/LIMIT/OFFSET)
     * @param array $orderBy the order by columns. See [[\yii\db\Query::orderBy]] for more details on how to specify this parameter.
     * @param int $limit the limit number. See [[\yii\db\Query::limit]] for more details.
     * @param int $offset the offset number. See [[\yii\db\Query::offset]] for more details.
     * @return string the SQL completed with ORDER BY/LIMIT/OFFSET (if any)
     */
    protected function newBuildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy === '') {
            // ORDER BY clause is required when FETCH and OFFSET are in the SQL
            $orderBy = 'ORDER BY (SELECT NULL)';
        }
        $sql .= $this->separator . $orderBy;

        // http://technet.microsoft.com/en-us/library/gg699618.aspx
        $offset = $this->hasOffset($offset) ? $offset : '0';
        $sql .= $this->separator . "OFFSET $offset ROWS";
        if ($this->hasLimit($limit)) {
            $sql .= $this->separator . "FETCH NEXT $limit ROWS ONLY";
        }

        return $sql;
    }

    /**
     * Builds the ORDER BY/LIMIT/OFFSET clauses for SQL SERVER 2005 to 2008.
     * @param string $sql the existing SQL (without ORDER BY/LIMIT/OFFSET)
     * @param array $orderBy the order by columns. See [[\yii\db\Query::orderBy]] for more details on how to specify this parameter.
     * @param int|Expression $limit the limit number. See [[\yii\db\Query::limit]] for more details.
     * @param int $offset the offset number. See [[\yii\db\Query::offset]] for more details.
     * @return string the SQL completed with ORDER BY/LIMIT/OFFSET (if any)
     */
    protected function oldBuildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy === '') {
            // ROW_NUMBER() requires an ORDER BY clause
            $orderBy = 'ORDER BY (SELECT NULL)';
        }

        $sql = preg_replace('/^([\s(])*SELECT(\s+DISTINCT)?(?!\s*TOP\s*\()/i', "\\1SELECT\\2 rowNum = ROW_NUMBER() over ($orderBy),", $sql);

        if ($this->hasLimit($limit)) {
            if ($limit instanceof Expression) {
                $limit = '('. (string)$limit . ')';
            }
            $sql = "SELECT TOP $limit * FROM ($sql) sub";
        } else {
            $sql = "SELECT * FROM ($sql) sub";
        }
        if ($this->hasOffset($offset)) {
            $sql .= $this->separator . "WHERE rowNum > $offset";
        }

        return $sql;
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $oldName the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable($oldName, $newName)
    {
        return 'sp_rename ' . $this->db->quoteTableName($oldName) . ', ' . $this->db->quoteTableName($newName);
    }

    /**
     * Builds a SQL statement for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $oldName the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB column.
     */
    public function renameColumn($table, $oldName, $newName)
    {
        $table = $this->db->quoteTableName($table);
        $oldName = $this->db->quoteColumnName($oldName);
        $newName = $this->db->quoteColumnName($newName);
        return "sp_rename '{$table}.{$oldName}', {$newName}, 'COLUMN'";
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[getColumnType]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     * @throws NotSupportedException if this is not supported by the underlying DBMS.
     */
    public function alterColumn($table, $column, $type)
    {
        $sqlAfter = [$this->dropConstraintsForColumn($table, $column, 'D')];

        $columnName = $this->db->quoteColumnName($column);
        $tableName = $this->db->quoteTableName($table);
        $constraintBase = preg_replace('/[^a-z0-9_]/i', '', $table . '_' . $column);

        if ($type instanceof \yii\db\mssql\ColumnSchemaBuilder) {
            $type->setAlterColumnFormat();


            $defaultValue = $type->getDefaultValue();
            if ($defaultValue !== null) {
                $sqlAfter[] = $this->addDefaultValue(
                    "DF_{$constraintBase}",
                    $table,
                    $column,
                    $defaultValue instanceof Expression ? $defaultValue : new Expression($defaultValue)
                );
            }

            $checkValue = $type->getCheckValue();
            if ($checkValue !== null) {
                $sqlAfter[] = "ALTER TABLE {$tableName} ADD CONSTRAINT " .
                    $this->db->quoteColumnName("CK_{$constraintBase}") .
                    " CHECK (" . ($defaultValue instanceof Expression ?  $checkValue : new Expression($checkValue)) . ")";
            }

            if ($type->isUnique()) {
                $sqlAfter[] = "ALTER TABLE {$tableName} ADD CONSTRAINT " . $this->db->quoteColumnName("UQ_{$constraintBase}") . " UNIQUE ({$columnName})";
            }
        }

        return 'ALTER TABLE ' . $tableName . ' ALTER COLUMN '
            . $columnName . ' '
            . $this->getColumnType($type) . "\n"
            . implode("\n", $sqlAfter);
    }

    /**
     * {@inheritdoc}
     */
    public function addDefaultValue($name, $table, $column, $value)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ADD CONSTRAINT '
            . $this->db->quoteColumnName($name) . ' DEFAULT ' . $this->db->quoteValue($value) . ' FOR '
            . $this->db->quoteColumnName($column);
    }

    /**
     * {@inheritdoc}
     */
    public function dropDefaultValue($name, $table)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP CONSTRAINT ' . $this->db->quoteColumnName($name);
    }

    /**
     * Creates a SQL statement for resetting the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or 1.
     * @param string $tableName the name of the table whose primary key sequence will be reset
     * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have a value 1.
     * @return string the SQL statement for resetting sequence
     * @throws InvalidArgumentException if the table does not exist or there is no sequence associated with the table.
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = $this->db->quoteColumnName(reset($table->primaryKey));
                $value = "(SELECT COALESCE(MAX({$key}),0) FROM {$tableName})+1";
            } else {
                $value = (int) $value;
            }

            return "DBCC CHECKIDENT ('{$tableName}', RESEED, {$value})";
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is not sequence associated with table '$tableName'.");
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     * @param string $schema the schema of the tables.
     * @param string $table the table name.
     * @return string the SQL statement for checking integrity
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $enable = $check ? 'CHECK' : 'NOCHECK';
        $schema = $schema ?: $this->db->getSchema()->defaultSchema;
        $tableNames = $this->db->getTableSchema($table) ? [$table] : $this->db->getSchema()->getTableNames($schema);
        $viewNames = $this->db->getSchema()->getViewNames($schema);
        $tableNames = array_diff($tableNames, $viewNames);
        $command = '';

        foreach ($tableNames as $tableName) {
            $tableName = $this->db->quoteTableName("{$schema}.{$tableName}");
            $command .= "ALTER TABLE $tableName $enable CONSTRAINT ALL; ";
        }

        return $command;
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

        $schemaName = $tableSchema->schemaName ? "N'" . $tableSchema->schemaName . "'": 'SCHEMA_NAME()';
        $tableName = "N" . $this->db->quoteValue($tableSchema->name);
        $columnName = $column ? "N" . $this->db->quoteValue($column) : null;
        $comment = "N" . $this->db->quoteValue($comment);

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

        $schemaName = $tableSchema->schemaName ? "N'" . $tableSchema->schemaName . "'": 'SCHEMA_NAME()';
        $tableName = "N" . $this->db->quoteValue($tableSchema->name);
        $columnName = $column ? "N" . $this->db->quoteValue($column) : null;

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
        /* @var $modelClass \yii\db\ActiveRecord */
        $schema = $modelClass::getTableSchema();
        return array_keys($schema->columns);
    }

    /**
     * @return bool whether the version of the MSSQL being used is older than 2012.
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @deprecated 2.0.14 Use [[Schema::getServerVersion]] with [[\version_compare()]].
     */
    protected function isOldMssql()
    {
        return version_compare($this->db->getSchema()->getServerVersion(), '11', '<');
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
     * Normalizes data to be saved into the table, performing extra preparations and type converting, if necessary.
     * @param string $table the table that data will be saved into.
     * @param array $columns the column data (name => value) to be saved into the table.
     * @return array normalized columns
     */
    private function normalizeTableRowData($table, $columns, &$params)
    {
        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                // @see https://github.com/yiisoft/yii2/issues/12599
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && $columnSchemas[$name]->dbType === 'varbinary' && (is_string($value) || $value === null)) {
                    $phName = $this->bindParam($value, $params);
                    // @see https://github.com/yiisoft/yii2/issues/12599
                    $columns[$name] = new Expression("CONVERT(VARBINARY(MAX), $phName)", $params);
                }
            }
        }

        return $columns;
    }

    /**
     * {@inheritdoc}
     * Added OUTPUT construction for getting inserted data (for SQL Server 2005 or later)
     * OUTPUT clause - The OUTPUT clause is new to SQL Server 2005 and has the ability to access
     * the INSERTED and DELETED tables as is the case with a trigger.
     */
    public function insert($table, $columns, &$params)
    {
        $columns = $this->normalizeTableRowData($table, $columns, $params);

        $version2005orLater = version_compare($this->db->getSchema()->getServerVersion(), '9', '>=');

        list($names, $placeholders, $values, $params) = $this->prepareInsertValues($table, $columns, $params);
        $cols = [];
        $outputColumns = [];
        if ($version2005orLater) {
            /* @var $schema TableSchema */
            $schema = $this->db->getTableSchema($table);
            foreach ($schema->columns as $column) {
                if ($column->isComputed) {
                    continue;
                }

                $dbType = $column->dbType;
                if (in_array($dbType, ['char', 'varchar', 'nchar', 'nvarchar', 'binary', 'varbinary'])) {
                    $dbType .= '(MAX)';
                }
                if ($column->dbType === Schema::TYPE_TIMESTAMP) {
                    $dbType = $column->allowNull ? 'varbinary(8)' : 'binary(8)';
                }

                $quoteColumnName = $this->db->quoteColumnName($column->name);
                $cols[] = $quoteColumnName . ' ' . $dbType . ' ' . ($column->allowNull ? "NULL" : "");
                $outputColumns[] = 'INSERTED.' . $quoteColumnName;
            }
        }

        $countColumns = count($outputColumns);

        $sql = 'INSERT INTO ' . $this->db->quoteTableName($table)
            . (!empty($names) ? ' (' . implode(', ', $names) . ')' : '')
            . (($version2005orLater && $countColumns) ? ' OUTPUT ' . implode(',', $outputColumns) . ' INTO @temporary_inserted' : '')
            . (!empty($placeholders) ? ' VALUES (' . implode(', ', $placeholders) . ')' : $values);

        if ($version2005orLater && $countColumns) {
            $sql = 'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE (' . implode(', ', $cols) . ');' . $sql .
                ';SELECT * FROM @temporary_inserted';
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
        $insertColumns = $this->normalizeTableRowData($table, $insertColumns, $params);

        /** @var Constraint[] $constraints */
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

        /**
         * Fix number of select query params for old MSSQL version that does not support offset correctly.
         * @see QueryBuilder::oldBuildOrderByAndLimit
         */
        $insertNamesUsing = $insertNames;
        if (strstr($values, 'rowNum = ROW_NUMBER()') !== false) {
            $insertNamesUsing = array_merge(['[rowNum]'], $insertNames);
        }

        $mergeSql = 'MERGE ' . $this->db->quoteTableName($table) . ' WITH (HOLDLOCK) '
            . 'USING (' . (!empty($placeholders) ? 'VALUES (' . implode(', ', $placeholders) . ')' : ltrim($values, ' ')) . ') AS [EXCLUDED] (' . implode(', ', $insertNamesUsing) . ') '
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
        $updateColumns = $this->normalizeTableRowData($table, $updateColumns, $params);

        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        $updateSql = 'UPDATE SET ' . implode(', ', $updates);
        return "$mergeSql WHEN MATCHED THEN $updateSql WHEN NOT MATCHED THEN $insertSql;";
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns, $params), $condition, $params);
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
     * {@inheritdoc}
     */
    protected function extractAlias($table)
    {
        if (preg_match('/^\[.*\]$/', $table)) {
            return false;
        }

        return parent::extractAlias($table);
    }

    /**
     * Builds a SQL statement for dropping constraints for column of table.
     *
     * @param string $table the table whose constraint is to be dropped. The name will be properly quoted by the method.
     * @param string $column the column whose constraint is to be dropped. The name will be properly quoted by the method.
     * @param string $type type of constraint, leave empty for all type of constraints(for example: D - default, 'UQ' - unique, 'C' - check)
     * @see https://docs.microsoft.com/sql/relational-databases/system-catalog-views/sys-objects-transact-sql
     * @return string the DROP CONSTRAINTS SQL
     */
    private function dropConstraintsForColumn($table, $column, $type='')
    {
        return "DECLARE @tableName VARCHAR(MAX) = '" . $this->db->quoteTableName($table) . "'
DECLARE @columnName VARCHAR(MAX) = '{$column}'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
        " . (!empty($type) ? " WHERE so.[type]='{$type}'" : "") . ")
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END";
    }

    /**
     * Drop all constraints before column delete
     * {@inheritdoc}
     */
    public function dropColumn($table, $column)
    {
        return $this->dropConstraintsForColumn($table, $column) . "\nALTER TABLE " . $this->db->quoteTableName($table)
            . " DROP COLUMN " . $this->db->quoteColumnName($column);
    }
}
