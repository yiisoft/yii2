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
 * MS SQL Server（版本要求 2008 以及 2008 以上版本）数据库查询构建器。
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * @var array 从抽象列类型（键）到物理列类型（值）的映射。
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
     * 为 SQL SERVER 2012 或更高版本构建的 BY/LIMIT/OFFSET 子句。
     * @param string $sql 现有的 SQL 语句（语句中不包含 ORDER BY/LIMIT/OFFSET）
     * @param array $orderBy 按列排序。有关如何指定此参数的详细信息，参阅 [[\yii\db\Query::orderBy]]。
     * @param int $limit 限制量。更多细节，请参阅 [[\yii\db\Query::limit]]。
     * @param int $offset 偏移量。更多细节，请参阅 [[\yii\db\Query::offset]]。
     * @return string 包含 ORDER BY/LIMIT/OFFSET 的 SQL 语句（假如有的话）
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
     * 为 SQL SERVER 2005 到 2008 版本构建的 BY/LIMIT/OFFSET 子句。
     * @param string $sql 现有的 SQL 语句（语句中不包含 ORDER BY/LIMIT/OFFSET）
     * @param array $orderBy 按列排序。有关如何指定此参数的详细信息，参阅 [[\yii\db\Query::orderBy]]。
     * @param int $limit 限制量。更多细节，请参阅 [[\yii\db\Query::limit]]。
     * @param int $offset 偏移量。更多细节，请参阅 [[\yii\db\Query::offset]]。
     * @return string 包含 ORDER BY/LIMIT/OFFSET 的 SQL 语句（假如有的话）
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
     * 构建用于重新命名数据库表名的 SQL 语句。
     * @param string $oldName 要重命名的表名。该方法将正确引用表名。
     * @param string $newName 表的新名称。该方法将正确引用表名。
     * @return string 用于重命名数据库表名的 SQL 语句。
     */
    public function renameTable($oldName, $newName)
    {
        return 'sp_rename ' . $this->db->quoteTableName($oldName) . ', ' . $this->db->quoteTableName($newName);
    }

    /**
     * 构建对列名重命名的 SQL 语句。
     * @param string $table 要重命名的列所在表的名称。该方法将正确引用表名。
     * @param string $oldName 旧的列名. 该方法将正确引用列名。
     * @param string $newName 新的列名。该方法将正确引用列名。
     * @return string 用于重命名数据库表列名的 SQL 语句。
     */
    public function renameColumn($table, $oldName, $newName)
    {
        $table = $this->db->quoteTableName($table);
        $oldName = $this->db->quoteColumnName($oldName);
        $newName = $this->db->quoteColumnName($newName);
        return "sp_rename '{$table}.{$oldName}', {$newName}, 'COLUMN'";
    }

    /**
     * 构建用于更改列定义的 SQL 语句。
     * @param string $table 要更改列的表名。该方法将正确引用表名。
     * @param string $column 要更改的列的名称。该方法将正确引用列名
     * @param string $type 新的列类型。将调用 [[getColumnType]] 方法将抽象列类型（存在的话）转换为物理列类型。
     * 任何未被识别未抽象类型的内容都将保留在生成的 SQL 语句中。例如，`string` 将被转换为 `varchar(255)`，
     * 而 `string not null` 会被转换成 `varchar(255) not null`。
     * @return string 用于更改列而定义的 SQL 语句。
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
     * 创建用于重置表主键的序列值的 SQL 语句。
     * 序列将被重置，
     * 以便于下一个新行插入的主键具有指定的值或者为 1。
     * @param string $tableName 要重建主键序列的数据库表名。
     * @param mixed $value 插入的下一个新行的主键的值。如果未设置此值，
     * 则下一个新行的主键值将为 1。
     * @return string 用于重置主键序列的 SQL 语句
     * @throws InvalidArgumentException 如果该表不存在，或没有与之关联的序列，则抛出异常。
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
     * 构建用于启用或禁用数据完整性检查的 SQL 语句。
     * @param bool $check 是否启用或禁用数据完整性检查。
     * @param string $schema 表架构。
     * @param string $table 表名。
     * @return string 用于检查数据完整性的 SQL 语句
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
     * 返回给定模型的列名数组。
     *
     * @param string $modelClass 模型类名
     * @return array|null 模型列名数组
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
     * @return bool 所使用的 MSSQL 版本是否早于 MSSQL 2012。
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
     * 将要保存在表中的数据规范化，如果有必要，则执行额外的准备工作和类型转换。
     * @param string $table 将数据保存到其中的表。
     * @param array $columns 要保存到表中的列数据（name => value）。
     * @return array 标准化列
     */
    private function normalizeTableRowData($table, $columns, &$params)
    {
        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                // @see https://github.com/yiisoft/yii2/issues/12599
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && $columnSchemas[$name]->dbType === 'varbinary' && (is_string($value) || $value === null)) {
                    $phName = $this->bindParam($value, $params);
                    $columns[$name] = new Expression("CONVERT(VARBINARY, $phName)", $params);
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
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns, $params), $condition, $params);
    }
}
