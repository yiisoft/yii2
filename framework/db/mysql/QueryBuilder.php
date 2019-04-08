<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;

/**
 * QueryBuilder 类是 MySQL 数据库查询构建器。
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
        Schema::TYPE_PK => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_UPK => 'int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_TINYINT => 'tinyint(3)',
        Schema::TYPE_SMALLINT => 'smallint(6)',
        Schema::TYPE_INTEGER => 'int(11)',
        Schema::TYPE_BIGINT => 'bigint(20)',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DOUBLE => 'double',
        Schema::TYPE_DECIMAL => 'decimal(10,0)',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'blob',
        Schema::TYPE_BOOLEAN => 'tinyint(1)',
        Schema::TYPE_MONEY => 'decimal(19,4)',
        Schema::TYPE_JSON => 'json'
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->typeMap = array_merge($this->typeMap, $this->defaultTimeTypeMap());
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\JsonExpression' => 'yii\db\mysql\JsonExpressionBuilder',
        ]);
    }

    /**
     * 构建用于重命名列的 SQL 语句。
     * @param string $table 用重命名列的表明。该方法将正确引用表名。
     * @param string $oldName 旧的列名。该方法将正确引用列名。
     * @param string $newName 新的列名。该方法将正确引用列名。
     * @return string 重命名列的 SQL 语句。
     * @throws Exception
     */
    public function renameColumn($table, $oldName, $newName)
    {
        $quotedTable = $this->db->quoteTableName($table);
        $row = $this->db->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryOne();
        if ($row === false) {
            throw new Exception("Unable to find column '$oldName' in table '$table'.");
        }
        if (isset($row['Create Table'])) {
            $sql = $row['Create Table'];
        } else {
            $row = array_values($row);
            $sql = $row[1];
        }
        if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches)) {
            foreach ($matches[1] as $i => $c) {
                if ($c === $oldName) {
                    return "ALTER TABLE $quotedTable CHANGE "
                        . $this->db->quoteColumnName($oldName) . ' '
                        . $this->db->quoteColumnName($newName) . ' '
                        . $matches[2][$i];
                }
            }
        }
        // try to give back a SQL anyway
        return "ALTER TABLE $quotedTable CHANGE "
            . $this->db->quoteColumnName($oldName) . ' '
            . $this->db->quoteColumnName($newName);
    }

    /**
     * {@inheritdoc}
     * @see https://bugs.mysql.com/bug.php?id=48875
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        return 'ALTER TABLE '
        . $this->db->quoteTableName($table)
        . ($unique ? ' ADD UNIQUE INDEX ' : ' ADD INDEX ')
        . $this->db->quoteTableName($name)
        . ' (' . $this->buildColumns($columns) . ')';
    }

    /**
     * 构建用于删除外键约束的 SQL 语句。
     * @param string $name 要删除的外键约束的名称。该方法会确保正确引用该名称。
     * @param string $table 要删除外键约束的表的名称。该方法会确保正确引用该名称。
     * @return string 用于删除外键约束的 SQL 语句。
     */
    public function dropForeignKey($name, $table)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' DROP FOREIGN KEY ' . $this->db->quoteColumnName($name);
    }

    /**
     * 构建一个用于删除现有表的主键约束的 SQL 语句。
     * @param string $name 将被删除的主键约束的名称。
     * @param string $table 将主键约束要从中删除的表的名称。
     * @return string 用于删除现有表的主键约束的 SQL 语句。
     */
    public function dropPrimaryKey($name, $table)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' DROP PRIMARY KEY';
    }

    /**
     * {@inheritdoc}
     */
    public function dropUnique($name, $table)
    {
        return $this->dropIndex($name, $table);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException MySQL 不支持此功能时抛出异常。
     */
    public function addCheck($name, $table, $expression)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by MySQL.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException NySQL 不支持此功能时抛出异常。
     */
    public function dropCheck($name, $table)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported by MySQL.');
    }

    /**
     * 创建用于重置表主键的序列值的 SQL 语句。
     * 序列将被重置，
     * 以便插入的下一个新行的主键具有指定值或者为 1。
     * @param string $tableName 将要重置主键序列的表的名称
     * @param mixed $value 插入的下一个新行的值。如果 $vaule 没设置，
     * 则下一个新行的主键的值为 1。
     * @return string 重置序列的 SQL 语句
     * @throws InvalidArgumentException 如果表不存在，或者没有与表关联的序列，则抛出异常。
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = reset($table->primaryKey);
                $value = $this->db->createCommand("SELECT MAX(`$key`) FROM $tableName")->queryScalar() + 1;
            } else {
                $value = (int) $value;
            }

            return "ALTER TABLE $tableName AUTO_INCREMENT=$value";
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is no sequence associated with table '$tableName'.");
    }

    /**
     * 构建用于启用或禁用数据完整性检查的 SQL 语句。
     * @param bool $check 是否打开或关闭数据完整性检查。
     * @param string $schema 表格的架构。对 MySQL 来说该参数毫无意思。
     * @param string $table 表名。对 MySQL 来说该参数毫无意义。
     * @return string 用于完整性检查的 SQL 语句
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        return 'SET FOREIGN_KEY_CHECKS = ' . ($check ? 1 : 0);
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
            // limit is not optional in MySQL
            // http://stackoverflow.com/a/271650/1106908
            // http://dev.mysql.com/doc/refman/5.0/en/select.html#idm47619502796240
            $sql = "LIMIT $offset, 18446744073709551615"; // 2^64-1
        }

        return $sql;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasLimit($limit)
    {
        // In MySQL limit argument must be nonnegative integer constant
        return ctype_digit((string) $limit);
    }

    /**
     * {@inheritdoc}
     */
    protected function hasOffset($offset)
    {
        // In MySQL offset argument must be nonnegative integer constant
        $offset = (string) $offset;
        return ctype_digit($offset) && $offset !== '0';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareInsertValues($table, $columns, $params = [])
    {
        list($names, $placeholders, $values, $params) = parent::prepareInsertValues($table, $columns, $params);
        if (!$columns instanceof Query && empty($names)) {
            $tableSchema = $this->db->getSchema()->getTableSchema($table);
            if ($tableSchema !== null) {
                $columns = !empty($tableSchema->primaryKey) ? $tableSchema->primaryKey : [reset($tableSchema->columns)->name];
                foreach ($columns as $name) {
                    $names[] = $this->db->quoteColumnName($name);
                    $placeholders[] = 'DEFAULT';
                }
            }
        }
        return [$names, $placeholders, $values, $params];
    }

    /**
     * {@inheritdoc}
     * @see https://downloads.mysql.com/docs/refman-5.1-en.pdf
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        $insertSql = $this->insert($table, $insertColumns, $params);
        list($uniqueNames, , $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns);
        if (empty($uniqueNames)) {
            return $insertSql;
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $updateColumns[$name] = new Expression('VALUES(' . $this->db->quoteColumnName($name) . ')');
            }
        } elseif ($updateColumns === false) {
            $name = $this->db->quoteColumnName(reset($uniqueNames));
            $updateColumns = [$name => new Expression($this->db->quoteTableName($table) . '.' . $name)];
        }
        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        return $insertSql . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        // Strip existing comment which may include escaped quotes
        $definition = trim(preg_replace("/COMMENT '(?:''|[^'])*'/i", '',
            $this->getColumnDefinition($table, $column)));

        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' CHANGE ' . $this->db->quoteColumnName($column)
            . ' ' . $this->db->quoteColumnName($column)
            . (empty($definition) ? '' : ' ' . $definition)
            . ' COMMENT ' . $this->db->quoteValue($comment);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function addCommentOnTable($table, $comment)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' COMMENT ' . $this->db->quoteValue($comment);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromColumn($table, $column)
    {
        return $this->addCommentOnColumn($table, $column, '');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.8
     */
    public function dropCommentFromTable($table)
    {
        return $this->addCommentOnTable($table, '');
    }


    /**
     * 获取列定义。
     *
     * @param string $table 表名
     * @param string $column 列名
     * @return null|string 列定义
     * @throws Exception 如果表不包含列，则抛出异常
     */
    private function getColumnDefinition($table, $column)
    {
        $quotedTable = $this->db->quoteTableName($table);
        $row = $this->db->createCommand('SHOW CREATE TABLE ' . $quotedTable)->queryOne();
        if ($row === false) {
            throw new Exception("Unable to find column '$column' in table '$table'.");
        }
        if (isset($row['Create Table'])) {
            $sql = $row['Create Table'];
        } else {
            $row = array_values($row);
            $sql = $row[1];
        }
        if (preg_match_all('/^\s*`(.*?)`\s+(.*?),?$/m', $sql, $matches)) {
            foreach ($matches[1] as $i => $c) {
                if ($c === $column) {
                    return $matches[2][$i];
                }
            }
        }

        return null;
    }

    /**
     * 检查使用小数秒的能力。
     *
     * @return bool
     * @see https://dev.mysql.com/doc/refman/5.6/en/fractional-seconds.html
     */
    private function supportsFractionalSeconds()
    {
        $version = $this->db->getSlavePdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        return version_compare($version, '5.6.4', '>=');
    }

    /**
     * 返回默认时间类型映射。
     * 当 MySQL 版本低于 5.6.4 时，类型没有小数秒，
     * 不然则使用小数秒。
     *
     * @return array
     */
    private function defaultTimeTypeMap()
    {
        $map = [
            Schema::TYPE_DATETIME => 'datetime',
            Schema::TYPE_TIMESTAMP => 'timestamp',
            Schema::TYPE_TIME => 'time',
        ];

        if ($this->supportsFractionalSeconds()) {
            $map = [
                Schema::TYPE_DATETIME => 'datetime(0)',
                Schema::TYPE_TIMESTAMP => 'timestamp(0)',
                Schema::TYPE_TIME => 'time(0)',
            ];
        }

        return $map;
    }
}
