<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidArgumentException;
use yii\db\Constraint;
use yii\db\Expression;

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
     * @param int $limit the limit number. See [[\yii\db\Query::limit]] for more details.
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
     */
    public function alterColumn($table, $column, $type)
    {
        $type = $this->getColumnType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
            . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type);

        return $sql;
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
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        return "sp_updateextendedproperty @name = N'MS_Description', @value = {$this->db->quoteValue($comment)}, @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}, @level2type = N'Column', @level2name = {$this->db->quoteColumnName($column)}";
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnTable($table, $comment)
    {
        return "sp_updateextendedproperty @name = N'MS_Description', @value = {$this->db->quoteValue($comment)}, @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}";
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromColumn($table, $column)
    {
        return "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}, @level2type = N'Column', @level2name = {$this->db->quoteColumnName($column)}";
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromTable($table)
    {
        return "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}";
    }

    /**
     * Returns an array of column names given model name.
     *
     * @param string $modelClass name of the model class
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
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && $columnSchemas[$name]->dbType === 'varbinary' && is_string($value)) {
                    $exParams = [];
                    $phName = $this->bindParam($value, $exParams);
                    $columns[$name] = new Expression("CONVERT(VARBINARY, $phName)", $exParams);
                }
            }
        }

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $columns, &$params)
    {
        return parent::insert($table, $this->normalizeTableRowData($table, $columns, $params), $params);
    }

    /**
     * {@inheritdoc}
     * @see https://docs.microsoft.com/en-us/sql/t-sql/statements/merge-transact-sql
     * @see http://weblogs.sqlteam.com/dang/archive/2009/01/31/UPSERT-Race-Condition-With-MERGE.aspx
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        /** @var Constraint[] $constraints */
        list($uniqueNames, $insertNames, $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns, $constraints);
        if (empty($uniqueNames)) {
            return $this->insert($table, $insertColumns, $params);
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
            . 'ON ' . $on;
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
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns, $params), $condition, $params);
    }
}
