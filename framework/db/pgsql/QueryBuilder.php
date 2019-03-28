<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\base\InvalidArgumentException;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\PdoValue;
use yii\helpers\StringHelper;

/**
 * QueryBuilder 是 PostgreSQL 数据库的查询构建器。
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * 为 [[createIndex()]] 定义一个 UNIQUE 索引。
     * @since 2.0.6
     */
    const INDEX_UNIQUE = 'unique';
    /**
     * 为 [[createIndex()]] 定义一个 B-tree 索引。
     * @since 2.0.6
     */
    const INDEX_B_TREE = 'btree';
    /**
     * 为 [[createIndex()]] 定义一个 hash 索引。
     * @since 2.0.6
     */
    const INDEX_HASH = 'hash';
    /**
     * 为 [[createIndex()]] 定义一个 GIST 索引。
     * @since 2.0.6
     */
    const INDEX_GIST = 'gist';
    /**
     * 为 [[createIndex()]] 定义一个 GIN 索引。
     * @since 2.0.6
     */
    const INDEX_GIN = 'gin';

    /**
     * @var array 从抽象列类型（键）到物理列类型（值）的映射。
     */
    public $typeMap = [
        Schema::TYPE_PK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_UPK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_TINYINT => 'smallint',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'integer',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'double precision',
        Schema::TYPE_DOUBLE => 'double precision',
        Schema::TYPE_DECIMAL => 'numeric(10,0)',
        Schema::TYPE_DATETIME => 'timestamp(0)',
        Schema::TYPE_TIMESTAMP => 'timestamp(0)',
        Schema::TYPE_TIME => 'time(0)',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'bytea',
        Schema::TYPE_BOOLEAN => 'boolean',
        Schema::TYPE_MONEY => 'numeric(19,4)',
        Schema::TYPE_JSON => 'jsonb',
    ];


    /**
     * {@inheritdoc}
     */
    protected function defaultConditionClasses()
    {
        return array_merge(parent::defaultConditionClasses(), [
            'ILIKE' => 'yii\db\conditions\LikeCondition',
            'NOT ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR NOT ILIKE' => 'yii\db\conditions\LikeCondition',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\ArrayExpression' => 'yii\db\pgsql\ArrayExpressionBuilder',
            'yii\db\JsonExpression' => 'yii\db\pgsql\JsonExpressionBuilder',
        ]);
    }

    /**
     * 构建用于创建新索引的 SQL 语句。
     * @param string $name 索引的名称。该方法将正确引用该名称。
     * @param string $table 将为其创建新索引的表。表名将由该方法正确引用。
     * @param string|array $columns 应包含在索引中的列。如果有多个列，
     * 请用逗号分隔它们或使用数组来表示他们。除非在名称中找到括号，
     * 否则方法将正确引用每个列名称。
     * @param bool|string $unique 是否使其成为 UNIQUE 索引约束。您可以传递 `true` 或 [[INDEX_UNIQUE]] 来创建唯一索引，
     * 传递 `false` 来使用默认索引类型创建非唯一索引，或者传递下列常量之一来指定要使用的索引方法：
     * [[INDEX_B_TREE]]、[[INDEX_HASH]]、[[INDEX_GIST]]、[[INDEX_GIN]]。
     * @return string 用于创建新索引的 SQL 语句。
     * @see http://www.postgresql.org/docs/8.2/static/sql-createindex.html
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($unique === self::INDEX_UNIQUE || $unique === true) {
            $index = false;
            $unique = true;
        } else {
            $index = $unique;
            $unique = false;
        }

        return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ') .
        $this->db->quoteTableName($name) . ' ON ' .
        $this->db->quoteTableName($table) .
        ($index !== false ? " USING $index" : '') .
        ' (' . $this->buildColumns($columns) . ')';
    }

    /**
     * 构建用于删除索引的 SQL 语句。
     * @param string $name 要删除的索引的名称。该方法将正确引用该名称。
     * @param string $table 要删除其索引的表。该方法将正确引用该名称。
     * @return string 用于删除索引的 SQL 语句。
     */
    public function dropIndex($name, $table)
    {
        if (strpos($table, '.') !== false && strpos($name, '.') === false) {
            if (strpos($table, '{{') !== false) {
                $table = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $table);
                list($schema, $table) = explode('.', $table);
                if (strpos($schema, '%') === false)
                    $name = $schema.'.'.$name;
                else
                    $name = '{{'.$schema.'.'.$name.'}}';
            } else {
                list($schema) = explode('.', $table);
                $name = $schema.'.'.$name;
            }
        }
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }

    /**
     * 构建用于重命名 DB 表名的 SQL 语句。
     * @param string $oldName 要重命名的表。该方法将正确引用该名称。
     * @param string $newName 新的表名。该方法将正确引用该名称。
     * @return string 重命名 DB 表名的 SQL 语句。
     */
    public function renameTable($oldName, $newName)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($oldName) . ' RENAME TO ' . $this->db->quoteTableName($newName);
    }

    /**
     * 创建用于重建数据表主键序列的 SQL 语句。
     * 序列将被重置，
     * 以便插入的下一个新行的主键具有指定值或者其值为 1。
     * @param string $tableName 用于重置主键序列的数据表的表名
     * @param mixed $value 插入的下一新行的主键的值。如果 $value 未设置，
     * 则新行的主键值将设置为 1。
     * @return string 用于重置序列的 SQL 语句。
     * @throws InvalidArgumentException 如果表不存在，或者没有与表关联的序列，则抛出异常。
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            // c.f. http://www.postgresql.org/docs/8.1/static/functions-sequence.html
            $sequence = $this->db->quoteTableName($table->sequenceName);
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = $this->db->quoteColumnName(reset($table->primaryKey));
                $value = "(SELECT COALESCE(MAX({$key}),0) FROM {$tableName})+1";
            } else {
                $value = (int) $value;
            }

            return "SELECT SETVAL('$sequence',$value,false)";
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is not sequence associated with table '$tableName'.");
    }

    /**
     * 构建用于启用或禁用完整性检查的 SQL 语句。
     * @param bool $check 是否打开或关闭完整性检查。
     * @param string $schema 表结构。
     * @param string $table 表名。
     * @return string 用于完整性检查的 SQL 语句
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $enable = $check ? 'ENABLE' : 'DISABLE';
        $schema = $schema ?: $this->db->getSchema()->defaultSchema;
        $tableNames = $table ? [$table] : $this->db->getSchema()->getTableNames($schema);
        $viewNames = $this->db->getSchema()->getViewNames($schema);
        $tableNames = array_diff($tableNames, $viewNames);
        $command = '';

        foreach ($tableNames as $tableName) {
            $tableName = $this->db->quoteTableName("{$schema}.{$tableName}");
            $command .= "ALTER TABLE $tableName $enable TRIGGER ALL; ";
        }

        // enable to have ability to alter several tables
        $this->db->getMasterPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        return $command;
    }

    /**
     * 构建用于截断 DB 表的 SQL 语句。
     * 显式重新启动 PGSQL 的标识，以便与默认情况下都执行此操作的其他数据库保持一致。
     * @param string $table 要被截断的表。该方法会确保正确引用该名称。
     * @return string 用于截断 DB 表的 SQL 语句。
     */
    public function truncateTable($table)
    {
        return 'TRUNCATE TABLE ' . $this->db->quoteTableName($table) . ' RESTART IDENTITY';
    }

    /**
     * 构建用于更改列定义的 SQL 语句。
     * @param string $table 要更改其列的表名。该方法会确保正确引用表名称。
     * @param string $column 要更改的列的名称。该方法会确保正确引用该名称。
     * @param string $type 新列类型。调用 [[getColumnType()]] 方法将抽象列类型（如果有）转换为物理列类型。
     * 任何未被识别为抽象类型的内容都将保留在生成的 SQL 语句中。
     * 例如，`string` 会被转换为 `varchar(255)`，而 `string not null` 转换为 `varchar(255) not null`。
     * 您还可以使用 PostgreSQL 特定的语法，例如 `SET NOT NULL`。
     * @return string 用于更改列定义的 SQL 语句。
     */
    public function alterColumn($table, $column, $type)
    {
        // https://github.com/yiisoft/yii2/issues/4492
        // http://www.postgresql.org/docs/9.1/static/sql-altertable.html
        if (!preg_match('/^(DROP|SET|RESET)\s+/i', $type)) {
            $type = 'TYPE ' . $this->getColumnType($type);
        }

        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
            . $this->db->quoteColumnName($column) . ' ' . $type;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $columns, &$params)
    {
        return parent::insert($table, $this->normalizeTableRowData($table, $columns), $params);
    }

    /**
     * {@inheritdoc}
     * @see https://www.postgresql.org/docs/9.5/static/sql-insert.html#SQL-ON-CONFLICT
     * @see https://stackoverflow.com/questions/1109061/insert-on-duplicate-update-in-postgresql/8702291#8702291
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        $insertColumns = $this->normalizeTableRowData($table, $insertColumns);
        if (!is_bool($updateColumns)) {
            $updateColumns = $this->normalizeTableRowData($table, $updateColumns);
        }
        if (version_compare($this->db->getServerVersion(), '9.5', '<')) {
            return $this->oldUpsert($table, $insertColumns, $updateColumns, $params);
        }

        return $this->newUpsert($table, $insertColumns, $updateColumns, $params);
    }

    /**
     * PostgreSQL 9.5 或更高版本的 [[upsert()]] 实现。
     * @param string $table
     * @param array|Query $insertColumns
     * @param array|bool $updateColumns
     * @param array $params
     * @return string
     */
    private function newUpsert($table, $insertColumns, $updateColumns, &$params)
    {
        $insertSql = $this->insert($table, $insertColumns, $params);
        list($uniqueNames, , $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns);
        if (empty($uniqueNames)) {
            return $insertSql;
        }

        if ($updateColumns === false) {
            return "$insertSql ON CONFLICT DO NOTHING";
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $updateColumns[$name] = new Expression('EXCLUDED.' . $this->db->quoteColumnName($name));
            }
        }
        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        return $insertSql . ' ON CONFLICT (' . implode(', ', $uniqueNames) . ') DO UPDATE SET ' . implode(', ', $updates);
    }

    /**
     * 早于 PostgreSQL 9.5 的 [[upsert()]] 实现。
     * @param string $table
     * @param array|Query $insertColumns
     * @param array|bool $updateColumns
     * @param array $params
     * @return string
     */
    private function oldUpsert($table, $insertColumns, $updateColumns, &$params)
    {
        /** @var Constraint[] $constraints */
        list($uniqueNames, $insertNames, $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns, $constraints);
        if (empty($uniqueNames)) {
            return $this->insert($table, $insertColumns, $params);
        }

        /** @var Schema $schema */
        $schema = $this->db->getSchema();
        if (!$insertColumns instanceof Query) {
            $tableSchema = $schema->getTableSchema($table);
            $columnSchemas = $tableSchema !== null ? $tableSchema->columns : [];
            foreach ($insertColumns as $name => $value) {
                // NULLs and numeric values must be type hinted in order to be used in SET assigments
                // NVM, let's cast them all
                if (isset($columnSchemas[$name])) {
                    $phName = self::PARAM_PREFIX . count($params);
                    $params[$phName] = $value;
                    $insertColumns[$name] = new Expression("CAST($phName AS {$columnSchemas[$name]->dbType})");
                }
            }
        }
        list(, $placeholders, $values, $params) = $this->prepareInsertValues($table, $insertColumns, $params);
        $updateCondition = ['or'];
        $insertCondition = ['or'];
        $quotedTableName = $schema->quoteTableName($table);
        foreach ($constraints as $constraint) {
            $constraintUpdateCondition = ['and'];
            $constraintInsertCondition = ['and'];
            foreach ($constraint->columnNames as $name) {
                $quotedName = $schema->quoteColumnName($name);
                $constraintUpdateCondition[] = "$quotedTableName.$quotedName=\"EXCLUDED\".$quotedName";
                $constraintInsertCondition[] = "\"upsert\".$quotedName=\"EXCLUDED\".$quotedName";
            }
            $updateCondition[] = $constraintUpdateCondition;
            $insertCondition[] = $constraintInsertCondition;
        }
        $withSql = 'WITH "EXCLUDED" (' . implode(', ', $insertNames)
            . ') AS (' . (!empty($placeholders) ? 'VALUES (' . implode(', ', $placeholders) . ')' : ltrim($values, ' ')) . ')';
        if ($updateColumns === false) {
            $selectSubQuery = (new Query())
                ->select(new Expression('1'))
                ->from($table)
                ->where($updateCondition);
            $insertSelectSubQuery = (new Query())
                ->select($insertNames)
                ->from('EXCLUDED')
                ->where(['not exists', $selectSubQuery]);
            $insertSql = $this->insert($table, $insertSelectSubQuery, $params);
            return "$withSql $insertSql";
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                if (strrpos($quotedName, '.') === false) {
                    $quotedName = '"EXCLUDED".' . $quotedName;
                }
                $updateColumns[$name] = new Expression($quotedName);
            }
        }
        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        $updateSql = 'UPDATE ' . $this->db->quoteTableName($table) . ' SET ' . implode(', ', $updates)
            . ' FROM "EXCLUDED" ' . $this->buildWhere($updateCondition, $params)
            . ' RETURNING ' . $this->db->quoteTableName($table) .'.*';
        $selectUpsertSubQuery = (new Query())
            ->select(new Expression('1'))
            ->from('upsert')
            ->where($insertCondition);
        $insertSelectSubQuery = (new Query())
            ->select($insertNames)
            ->from('EXCLUDED')
            ->where(['not exists', $selectUpsertSubQuery]);
        $insertSql = $this->insert($table, $insertSelectSubQuery, $params);
        return "$withSql, \"upsert\" AS ($updateSql) $insertSql";
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns), $condition, $params);
    }

    /**
     * 如果有必要，将要保存到表中的数据标准化，执行额外准备和类型转换。
     *
     * @param string $table 数据将保存到的表。
     * @param array|Query $columns 将要保存到 [[yii\db\Query|Query]] 的表或实例中的列数据（name => value），
     * 执行 INSERT INTO ... SELECT SQL 语句。
     * 自 2.0.11 版本起，[[yii\db\Query|Query]] 的传递可用。
     * @return array 标准化列
     * @since 2.0.9
     */
    private function normalizeTableRowData($table, $columns)
    {
        if ($columns instanceof Query) {
            return $columns;
        }

        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && is_string($value)) {
                    $columns[$name] = new PdoValue($value, \PDO::PARAM_LOB); // explicitly setup PDO param type for binary column
                }
            }
        }

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function batchInsert($table, $columns, $rows, &$params = [])
    {
        if (empty($rows)) {
            return '';
        }

        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = StringHelper::floatToString($value);
                } elseif ($value === true) {
                    $value = 'TRUE';
                } elseif ($value === false) {
                    $value = 'FALSE';
                } elseif ($value === null) {
                    $value = 'NULL';
                } elseif ($value instanceof ExpressionInterface) {
                    $value = $this->buildExpression($value, $params);
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }
        if (empty($values)) {
            return '';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }
}
