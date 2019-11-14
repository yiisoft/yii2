<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\ColumnSchema;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\SqlToken;
use yii\db\TableSchema;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;

/**
 * Schema 是从 SQLite（SQLite 2 或 SQLite 3）数据库中检索元数据的类。
 *
 * @property string $transactionIsolationLevel 用于此事务的事务隔离级别。
 * 这可以是 [[Transaction::READ_UNCOMMITTED]] 或 [[Transaction::SERIALIZABLE]]。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{
    use ConstraintFinderTrait;

    /**
     * @var array 从物理列类型（键）到抽象列类型（值）的映射
     */
    public $typeMap = [
        'tinyint' => self::TYPE_TINYINT,
        'bit' => self::TYPE_SMALLINT,
        'boolean' => self::TYPE_BOOLEAN,
        'bool' => self::TYPE_BOOLEAN,
        'smallint' => self::TYPE_SMALLINT,
        'mediumint' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'real' => self::TYPE_FLOAT,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'tinytext' => self::TYPE_TEXT,
        'mediumtext' => self::TYPE_TEXT,
        'longtext' => self::TYPE_TEXT,
        'text' => self::TYPE_TEXT,
        'varchar' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        'char' => self::TYPE_CHAR,
        'blob' => self::TYPE_BINARY,
        'datetime' => self::TYPE_DATETIME,
        'year' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'enum' => self::TYPE_STRING,
    ];

    /**
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = '`';
    /**
     * {@inheritdoc}
     */
    protected $columnQuoteCharacter = '`';


    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        $sql = "SELECT DISTINCT tbl_name FROM sqlite_master WHERE tbl_name<>'sqlite_sequence' ORDER BY tbl_name";
        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $table->name = $name;
        $table->fullName = $name;

        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            return $table;
        }

        return null;
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
        $foreignKeys = $this->db->createCommand('PRAGMA FOREIGN_KEY_LIST (' . $this->quoteValue($tableName) . ')')->queryAll();
        $foreignKeys = $this->normalizePdoRowKeyCase($foreignKeys, true);
        $foreignKeys = ArrayHelper::index($foreignKeys, null, 'table');
        ArrayHelper::multisort($foreignKeys, 'seq', SORT_ASC, SORT_NUMERIC);
        $result = [];
        foreach ($foreignKeys as $table => $foreignKey) {
            $result[] = new ForeignKeyConstraint([
                'columnNames' => ArrayHelper::getColumn($foreignKey, 'from'),
                'foreignTableName' => $table,
                'foreignColumnNames' => ArrayHelper::getColumn($foreignKey, 'to'),
                'onDelete' => isset($foreignKey[0]['on_delete']) ? $foreignKey[0]['on_delete'] : null,
                'onUpdate' => isset($foreignKey[0]['on_update']) ? $foreignKey[0]['on_update'] : null,
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableIndexes($tableName)
    {
        return $this->loadTableConstraints($tableName, 'indexes');
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
        $sql = $this->db->createCommand('SELECT `sql` FROM `sqlite_master` WHERE name = :tableName', [
            ':tableName' => $tableName,
        ])->queryScalar();
        /** @var $code SqlToken[]|SqlToken[][]|SqlToken[][][] */
        $code = (new SqlTokenizer($sql))->tokenize();
        $pattern = (new SqlTokenizer('any CREATE any TABLE any()'))->tokenize();
        if (!$code[0]->matches($pattern, 0, $firstMatchIndex, $lastMatchIndex)) {
            return [];
        }

        $createTableToken = $code[0][$lastMatchIndex - 1];
        $result = [];
        $offset = 0;
        while (true) {
            $pattern = (new SqlTokenizer('any CHECK()'))->tokenize();
            if (!$createTableToken->matches($pattern, $offset, $firstMatchIndex, $offset)) {
                break;
            }

            $checkSql = $createTableToken[$offset - 1]->getSql();
            $name = null;
            $pattern = (new SqlTokenizer('CONSTRAINT any'))->tokenize();
            if (isset($createTableToken[$firstMatchIndex - 2]) && $createTableToken->matches($pattern, $firstMatchIndex - 2)) {
                $name = $createTableToken[$firstMatchIndex - 1]->content;
            }
            $result[] = new CheckConstraint([
                'name' => $name,
                'expression' => $checkSql,
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException 如果调用此方法，则抛出异常。
     */
    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('SQLite does not support default value constraints.');
    }

    /**
     * 创建 MySQL 数据库查询构建器。
     * 子类可以重写此方法以创建特定于 DBMS 的查询构建器。
     * @return QueryBuilder 查询构建器实例
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * {@inheritdoc}
     * @return ColumnSchemaBuilder 列结构构建器实例
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }

    /**
     * 搜集表列的元数据。
     * @param TableSchema $table 表元数据
     * @return bool 表是否存在于数据库中
     */
    protected function findColumns($table)
    {
        $sql = 'PRAGMA table_info(' . $this->quoteSimpleTableName($table->name) . ')';
        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
            }
        }
        if (count($table->primaryKey) === 1 && !strncasecmp($table->columns[$table->primaryKey[0]]->dbType, 'int', 3)) {
            $table->sequenceName = '';
            $table->columns[$table->primaryKey[0]]->autoIncrement = true;
        }

        return true;
    }

    /**
     * 在给定表中搜集外键列的详细信息。
     * @param TableSchema $table 表元数据
     */
    protected function findConstraints($table)
    {
        $sql = 'PRAGMA foreign_key_list(' . $this->quoteSimpleTableName($table->name) . ')';
        $keys = $this->db->createCommand($sql)->queryAll();
        foreach ($keys as $key) {
            $id = (int) $key['id'];
            if (!isset($table->foreignKeys[$id])) {
                $table->foreignKeys[$id] = [$key['table'], $key['from'] => $key['to']];
            } else {
                // composite FK
                $table->foreignKeys[$id][$key['from']] = $key['to'];
            }
        }
    }

    /**
     * 返回给定表的所有唯一索引。
     *
     * 每个数组元素都具有如下结构：
     *
     * ```php
     * [
     *     'IndexName1' => ['col1' [, ...]],
     *     'IndexName2' => ['col2' [, ...]],
     * ]
     * ```
     *
     * @param TableSchema $table 表元数据
     * @return array 给定表的所有唯一索引。
     */
    public function findUniqueIndexes($table)
    {
        $sql = 'PRAGMA index_list(' . $this->quoteSimpleTableName($table->name) . ')';
        $indexes = $this->db->createCommand($sql)->queryAll();
        $uniqueIndexes = [];

        foreach ($indexes as $index) {
            $indexName = $index['name'];
            $indexInfo = $this->db->createCommand('PRAGMA index_info(' . $this->quoteValue($index['name']) . ')')->queryAll();

            if ($index['unique']) {
                $uniqueIndexes[$indexName] = [];
                foreach ($indexInfo as $row) {
                    $uniqueIndexes[$indexName][] = $row['name'];
                }
            }
        }

        return $uniqueIndexes;
    }

    /**
     * 加载列信息到 [[ColumnSchema]] 对象。
     * @param array $info 列信息
     * @return ColumnSchema 列结构对象
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();
        $column->name = $info['name'];
        $column->allowNull = !$info['notnull'];
        $column->isPrimaryKey = $info['pk'] != 0;

        $column->dbType = strtolower($info['type']);
        $column->unsigned = strpos($column->dbType, 'unsigned') !== false;

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = strtolower($matches[1]);
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

        if (!$column->isPrimaryKey) {
            if ($info['dflt_value'] === 'null' || $info['dflt_value'] === '' || $info['dflt_value'] === null) {
                $column->defaultValue = null;
            } elseif ($column->type === 'timestamp' && $info['dflt_value'] === 'CURRENT_TIMESTAMP') {
                $column->defaultValue = new Expression('CURRENT_TIMESTAMP');
            } else {
                $value = trim($info['dflt_value'], "'\"");
                $column->defaultValue = $column->phpTypecast($value);
            }
        }

        return $column;
    }

    /**
     * 设置当前事务的隔离级别。
     * @param string $level 用于此事务的隔离级别。
     * 这可以是 [[Transaction::READ_UNCOMMITTED]] 或 [[Transaction::SERIALIZABLE]]。
     * @throws NotSupportedException 当使用不支持的隔离级别时，抛出异常。
     * SQLite 仅仅支持 SERIALIZABLE 和 READ UNCOMMITTED。
     * @see http://www.sqlite.org/pragma.html#pragma_read_uncommitted
     */
    public function setTransactionIsolationLevel($level)
    {
        switch ($level) {
            case Transaction::SERIALIZABLE:
                $this->db->createCommand('PRAGMA read_uncommitted = False;')->execute();
                break;
            case Transaction::READ_UNCOMMITTED:
                $this->db->createCommand('PRAGMA read_uncommitted = True;')->execute();
                break;
            default:
                throw new NotSupportedException(get_class($this) . ' only supports transaction isolation levels READ UNCOMMITTED and SERIALIZABLE.');
        }
    }

    /**
     * 返回表列信息。
     * @param string $tableName 表名
     * @return array
     */
    private function loadTableColumnsInfo($tableName)
    {
        $tableColumns = $this->db->createCommand('PRAGMA TABLE_INFO (' . $this->quoteValue($tableName) . ')')->queryAll();
        $tableColumns = $this->normalizePdoRowKeyCase($tableColumns, true);

        return ArrayHelper::index($tableColumns, 'cid');
    }

    /**
     * 加载多种类型的约束，并返回指定的约束。
     * @param string $tableName 表名。
     * @param string $returnType 返回的约束类型：
     * - primaryKey
     * - indexes
     * - uniques
     * @return mixed constraints.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        $indexes = $this->db->createCommand('PRAGMA INDEX_LIST (' . $this->quoteValue($tableName) . ')')->queryAll();
        $indexes = $this->normalizePdoRowKeyCase($indexes, true);
        $tableColumns = null;
        if (!empty($indexes) && !isset($indexes[0]['origin'])) {
            /*
             * SQLite may not have an "origin" column in INDEX_LIST
             * See https://www.sqlite.org/src/info/2743846cdba572f6
             */
            $tableColumns = $this->loadTableColumnsInfo($tableName);
        }
        $result = [
            'primaryKey' => null,
            'indexes' => [],
            'uniques' => [],
        ];
        foreach ($indexes as $index) {
            $columns = $this->db->createCommand('PRAGMA INDEX_INFO (' . $this->quoteValue($index['name']) . ')')->queryAll();
            $columns = $this->normalizePdoRowKeyCase($columns, true);
            ArrayHelper::multisort($columns, 'seqno', SORT_ASC, SORT_NUMERIC);
            if ($tableColumns !== null) {
                // SQLite may not have an "origin" column in INDEX_LIST
                $index['origin'] = 'c';
                if (!empty($columns) && $tableColumns[$columns[0]['cid']]['pk'] > 0) {
                    $index['origin'] = 'pk';
                } elseif ($index['unique'] && $this->isSystemIdentifier($index['name'])) {
                    $index['origin'] = 'u';
                }
            }
            $result['indexes'][] = new IndexConstraint([
                'isPrimary' => $index['origin'] === 'pk',
                'isUnique' => (bool) $index['unique'],
                'name' => $index['name'],
                'columnNames' => ArrayHelper::getColumn($columns, 'name'),
            ]);
            if ($index['origin'] === 'u') {
                $result['uniques'][] = new Constraint([
                    'name' => $index['name'],
                    'columnNames' => ArrayHelper::getColumn($columns, 'name'),
                ]);
            } elseif ($index['origin'] === 'pk') {
                $result['primaryKey'] = new Constraint([
                    'columnNames' => ArrayHelper::getColumn($columns, 'name'),
                ]);
            }
        }

        if ($result['primaryKey'] === null) {
            /*
             * Additional check for PK in case of INTEGER PRIMARY KEY with ROWID
             * See https://www.sqlite.org/lang_createtable.html#primkeyconst
             */
            if ($tableColumns === null) {
                $tableColumns = $this->loadTableColumnsInfo($tableName);
            }
            foreach ($tableColumns as $tableColumn) {
                if ($tableColumn['pk'] > 0) {
                    $result['primaryKey'] = new Constraint([
                        'columnNames' => [$tableColumn['name']],
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
     * 返回指定的标识符是否为 SQLite 系统标识符。
     * @param string $identifier
     * @return bool
     * @see https://www.sqlite.org/src/artifact/74108007d286232f
     */
    private function isSystemIdentifier($identifier)
    {
        return strncmp($identifier, 'sqlite_', 7) === 0;
    }
}
