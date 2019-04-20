<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Connection;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\helpers\StringHelper;

/**
 * QueryBuilder 是 SQLite 数据库的查询构建器。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * @var array 从抽象列类型（键）到物理列类型（值）的映射。
     */
    public $typeMap = [
        Schema::TYPE_PK => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
        Schema::TYPE_UPK => 'integer UNSIGNED PRIMARY KEY AUTOINCREMENT NOT NULL',
        Schema::TYPE_BIGPK => 'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
        Schema::TYPE_UBIGPK => 'integer UNSIGNED PRIMARY KEY AUTOINCREMENT NOT NULL',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_TINYINT => 'tinyint',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'integer',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DOUBLE => 'double',
        Schema::TYPE_DECIMAL => 'decimal(10,0)',
        Schema::TYPE_DATETIME => 'datetime',
        Schema::TYPE_TIMESTAMP => 'timestamp',
        Schema::TYPE_TIME => 'time',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'blob',
        Schema::TYPE_BOOLEAN => 'boolean',
        Schema::TYPE_MONEY => 'decimal(19,4)',
    ];


    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\conditions\LikeCondition' => 'yii\db\sqlite\conditions\LikeConditionBuilder',
            'yii\db\conditions\InCondition' => 'yii\db\sqlite\conditions\InConditionBuilder',
        ]);
    }

    /**
     * {@inheritdoc}
     * @see https://stackoverflow.com/questions/15277373/sqlite-upsert-update-or-insert/15277374#15277374
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        /** @var Constraint[] $constraints */
        list($uniqueNames, $insertNames, $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns, $constraints);
        if (empty($uniqueNames)) {
            return $this->insert($table, $insertColumns, $params);
        }

        list(, $placeholders, $values, $params) = $this->prepareInsertValues($table, $insertColumns, $params);
        $insertSql = 'INSERT OR IGNORE INTO ' . $this->db->quoteTableName($table)
            . (!empty($insertNames) ? ' (' . implode(', ', $insertNames) . ')' : '')
            . (!empty($placeholders) ? ' VALUES (' . implode(', ', $placeholders) . ')' : $values);
        if ($updateColumns === false) {
            return $insertSql;
        }

        $updateCondition = ['or'];
        $quotedTableName = $this->db->quoteTableName($table);
        foreach ($constraints as $constraint) {
            $constraintCondition = ['and'];
            foreach ($constraint->columnNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                $constraintCondition[] = "$quotedTableName.$quotedName=(SELECT $quotedName FROM `EXCLUDED`)";
            }
            $updateCondition[] = $constraintCondition;
        }
        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                if (strrpos($quotedName, '.') === false) {
                    $quotedName = "(SELECT $quotedName FROM `EXCLUDED`)";
                }
                $updateColumns[$name] = new Expression($quotedName);
            }
        }
        $updateSql = 'WITH "EXCLUDED" (' . implode(', ', $insertNames)
            . ') AS (' . (!empty($placeholders) ? 'VALUES (' . implode(', ', $placeholders) . ')' : ltrim($values, ' ')) . ') '
            . $this->update($table, $updateColumns, $updateCondition, $params);
        return "$updateSql; $insertSql;";
    }

    /**
     * 生成批量插入的 SQL 语句。
     *
     * 例如，
     *
     * ```php
     * $connection->createCommand()->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ])->execute();
     * ```
     *
     * 注意，每行中的值必须与相对应的列名称匹配。
     *
     * @param string $table 将插入新行的表。
     * @param array $columns 列名
     * @param array|\Generator $rows 要批量插入表中的行
     * @return string 用于批量插入的 SQL 语句
     */
    public function batchInsert($table, $columns, $rows, &$params = [])
    {
        if (empty($rows)) {
            return '';
        }

        // SQLite supports batch insert natively since 3.7.11
        // http://www.sqlite.org/releaselog/3_7_11.html
        $this->db->open(); // ensure pdo is not null
        if (version_compare($this->db->getServerVersion(), '3.7.11', '>=')) {
            return parent::batchInsert($table, $columns, $rows, $params);
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
                if (isset($columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = StringHelper::floatToString($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                } elseif ($value instanceof ExpressionInterface) {
                    $value = $this->buildExpression($value, $params);
                }
                $vs[] = $value;
            }
            $values[] = implode(', ', $vs);
        }
        if (empty($values)) {
            return '';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') SELECT ' . implode(' UNION SELECT ', $values);
    }

    /**
     * 创建用于重置表的主键序列的 SQL 语句。
     * 序列将被重置，
     * 以便插入的下一个新行的主键具有指定的值或其值为 1 。
     * @param string $tableName 将被重置主键序列的表名
     * @param mixed $value 插入下一新行的主键的值。如果 $value 未设置，
     * 则插入下一新行的主键序列值将指定为 1 。
     * @return string 用于重置序列的 SQL 语句
     * @throws InvalidArgumentException 如果表不存在，或没有与表关联的序列，则抛出异常。
     */
    public function resetSequence($tableName, $value = null)
    {
        $db = $this->db;
        $table = $db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            $tableName = $db->quoteTableName($tableName);
            if ($value === null) {
                $key = $this->db->quoteColumnName(reset($table->primaryKey));
                $value = $this->db->useMaster(function (Connection $db) use ($key, $tableName) {
                    return $db->createCommand("SELECT MAX($key) FROM $tableName")->queryScalar();
                });
            } else {
                $value = (int) $value - 1;
            }

            return "UPDATE sqlite_sequence SET seq='$value' WHERE name='{$table->name}'";
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is not sequence associated with table '$tableName'.'");
    }

    /**
     * 启用或禁用完整性检查。
     * @param bool $check 是否开打或关闭完整性检查。
     * @param string $schema 表的数据库结构。对 SQLite 毫无意义。
     * @param string $table 表名。对 SQLite 毫无意义。
     * @return string 用于完整性检查的 SQL 语句
     * @throws NotSupportedException 如果 SQLite 不支持完整性检查，则抛出异常。
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        return 'PRAGMA foreign_keys=' . (int) $check;
    }

    /**
     * 构建用于截断数据库表的 SQL 语句。
     * @param string $table 要截断的表。该方法将正确引用该名称。
     * @return string 用于截断数据库表的 SQL 语句。
     */
    public function truncateTable($table)
    {
        return 'DELETE FROM ' . $this->db->quoteTableName($table);
    }

    /**
     * 构建用于删除索引的 SQL 语句。
     * @param string $name 要删除的索引的名称。该方法将正确引用该名称。
     * @param string $table 要删除其索引的表的名称。该方法将正确引用该名称。
     * @return string 用于删除索引的 SQL 语句。
     */
    public function dropIndex($name, $table)
    {
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }

    /**
     * 构建用于删除 DB 列的 SQL 语句。
     * @param string $table 要删除其列的表名。该方法将正确引用该名称。
     * @param string $column 要删除的列的名称。该方法将正确引用该名称。
     * @return string 用于删除 DB 列的 SQL 语句。
     * @throws NotSupportedException 当 SQLite 不支持时抛出异常
     */
    public function dropColumn($table, $column)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建用于重命名列的 SQL 语句。
     * @param string $table 要重命名其列的表名。该方法将正确引用该名称。
     * @param string $oldName 要重命名的列的名称。该方法将正确引用该名称。
     * @param string $newName 要重命名的列的新的名称。该方法将正确引用该名称。
     * @return string 用于重命名 DB 列的 SQL 语句。
     * @throws NotSupportedException 当 SQLite 不支持时抛出异常
     */
    public function renameColumn($table, $oldName, $newName)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建用于将外键约束添加到已知的表的 SQL 语句。
     * 该方法将确保正确引用表名和列名。
     * @param string $name 外键约束名称。
     * @param string $table 要添加外键约束的表名。
     * @param string|array $columns 要添加约束的列名。
     * 如果有多个列，请使用英文逗号分割它们或使用数组来表示它们。
     * @param string $refTable 外键引用的表。
     * @param string|array $refColumns 外键引用的列名。
     * 如果有多个列，请使用英文逗号分隔它们或使用数组来表示它们。
     * @param string $delete ON DELETE 选项。大多数 DBMS 支持这些选项：RESTRICT，CASCADE，NO ACTION，SET DEFAULT，SET NULL
     * @param string $update ON UPDATE 选项。大多数 DBMS 支持这些选项：RESTRICT，CASCADE，NO ACTION，SET DEFAULT，SET NULL
     * @return string 用于将外键约束添加到已知的表的 SQL 语句。
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建用于删除外键约束的 SQL 语句。
     * @param string $name 要被删除的外键约束的名称。该方法将正确引用该名称。
     * @param string $table 外删除其外键约束的表的表名。该方法将正确引用该名称。
     * @return string 构建用于删除外键约束的 SQL 语句。
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常
     */
    public function dropForeignKey($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建用于重命名 DB 表名的 SQL 语句。
     *
     * @param string $table 要重命名的表的名称。该方法将正确引用该名称。
     * @param string $newName 新的表名称。该方法将正确引用该名称。
     * @return string 用于重命名 DB 表名的 SQL 语句。
     */
    public function renameTable($table, $newName)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' RENAME TO ' . $this->db->quoteTableName($newName);
    }

    /**
     * 构建用于更改列定义的 SQL 语句。
     * @param string $table 要更改其列的表。表名将由该方法正确引用。
     * @param string $column 要更改的列的名称。该方法将正确引用该名称。
     * @param string $type 新的列类型。将调用 [[getColumnType()]] 方法将抽象列类型（如果有）转换为物理列类型。
     * 任何未被识别为抽象类型的内容都将保存在生成的 SQL 语句中。
     * 例如，`string` 将变为 `varchar(255)`，
     * 而 `string not null` 将变为 `varchar(255) not null`。
     * @return string 用于更改列定义的 SQL 语句。
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常
     */
    public function alterColumn($table, $column, $type)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建用于将主键约束添加到现有表的 SQL 语句。
     * @param string $name 主键约束名称。
     * @param string $table 将主键约束添加到其中的表的表名。
     * @param string|array $columns 英文逗号分隔的字符串或主键所包含的l列数组。
     * @return string 将主键约束添加到现有表的 SQL 语句。
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * 构建一个将主键约束从现有表移除的 SQL 语句。
     * @param string $name 要被移除的主键约束的名称。
     * @param string $table 从中要移除主键约束的表的名称。
     * @return string 将主键约束从现有表移除的 SQL 语句。
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常
     */
    public function dropPrimaryKey($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function addUnique($name, $table, $columns)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function dropUnique($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function addCheck($name, $table, $expression)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function dropCheck($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function addDefaultValue($name, $table, $column, $value)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException SQLite 不支持这一点时抛出异常。
     */
    public function dropDefaultValue($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     * @since 2.0.8
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     * @since 2.0.8
     */
    public function addCommentOnTable($table, $comment)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     * @since 2.0.8
     */
    public function dropCommentFromColumn($table, $column)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     * @since 2.0.8
     */
    public function dropCommentFromTable($table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by SQLite.');
    }

    /**
     * {@inheritdoc}
     */
    public function buildLimit($limit, $offset)
    {
        $sql = '';
        if ($this->hasLimit($limit)) {
            $sql = 'LIMIT ' . $limit;
            if ($this->hasOffset($offset)) {
                $sql .= ' OFFSET ' . $offset;
            }
        } elseif ($this->hasOffset($offset)) {
            // limit is not optional in SQLite
            // http://www.sqlite.org/syntaxdiagrams.html#select-stmt
            $sql = "LIMIT 9223372036854775807 OFFSET $offset"; // 2^63-1
        }

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    public function build($query, $params = [])
    {
        $query = $query->prepare($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = [
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        $sql = $this->buildOrderByAndLimit($sql, $query->orderBy, $query->limit, $query->offset);

        if (!empty($query->orderBy)) {
            foreach ($query->orderBy as $expression) {
                if ($expression instanceof ExpressionInterface) {
                    $this->buildExpression($expression, $params);
                }
            }
        }
        if (!empty($query->groupBy)) {
            foreach ($query->groupBy as $expression) {
                if ($expression instanceof ExpressionInterface) {
                    $this->buildExpression($expression, $params);
                }
            }
        }

        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "$sql{$this->separator}$union";
        }

        return [$sql, $params];
    }

    /**
     * {@inheritdoc}
     */
    public function buildUnion($unions, &$params)
    {
        if (empty($unions)) {
            return '';
        }

        $result = '';

        foreach ($unions as $i => $union) {
            $query = $union['query'];
            if ($query instanceof Query) {
                list($unions[$i]['query'], $params) = $this->build($query, $params);
            }

            $result .= ' UNION ' . ($union['all'] ? 'ALL ' : '') . ' ' . $unions[$i]['query'];
        }

        return trim($result);
    }
}
