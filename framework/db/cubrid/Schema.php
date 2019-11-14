<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\cubrid;

use yii\base\NotSupportedException;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\TableSchema;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;

/**
 * Schema 是用于从 CUBRID 数据库（版本要求 version 9.3.x 以及更高）检索元数据的类。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{
    use ConstraintFinderTrait;

    /**
     * @var array 从物理列类型（键）映射到抽象列类型（值）
     * 有关数据类型的详细信息，
     * 请查阅 [CUBRID 手册](http://www.cubrid.org/manual/91/en/sql/datatype.html) 。
     */
    public $typeMap = [
        // Numeric data types
        'short' => self::TYPE_SMALLINT,
        'smallint' => self::TYPE_SMALLINT,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'numeric' => self::TYPE_DECIMAL,
        'decimal' => self::TYPE_DECIMAL,
        'float' => self::TYPE_FLOAT,
        'real' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'double precision' => self::TYPE_DOUBLE,
        'monetary' => self::TYPE_MONEY,
        // Date/Time data types
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'datetime' => self::TYPE_DATETIME,
        // String data types
        'char' => self::TYPE_CHAR,
        'varchar' => self::TYPE_STRING,
        'char varying' => self::TYPE_STRING,
        'nchar' => self::TYPE_CHAR,
        'nchar varying' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        // BLOB/CLOB data types
        'blob' => self::TYPE_BINARY,
        'clob' => self::TYPE_BINARY,
        // Bit string data types
        'bit' => self::TYPE_INTEGER,
        'bit varying' => self::TYPE_INTEGER,
        // Collection data types (considered strings for now)
        'set' => self::TYPE_STRING,
        'multiset' => self::TYPE_STRING,
        'list' => self::TYPE_STRING,
        'sequence' => self::TYPE_STRING,
        'enum' => self::TYPE_STRING,
    ];
    /**
     * @var array DB 错误和相应异常的映射
     * 如果在 DB 错误消息中找到左侧部分，则使用右侧部分的异常类。
     */
    public $exceptionMap = [
        'Operation would have caused one or more unique constraint violations' => 'yii\db\IntegrityException',
    ];

    /**
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = '"';


    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        $pdo = $this->db->getSlavePdo();
        $tables = $pdo->cubrid_schema(\PDO::CUBRID_SCH_TABLE);
        $tableNames = [];
        foreach ($tables as $table) {
            // do not list system tables
            if ($table['TYPE'] != 0) {
                $tableNames[] = $table['NAME'];
            }
        }

        return $tableNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $pdo = $this->db->getSlavePdo();

        $tableInfo = $pdo->cubrid_schema(\PDO::CUBRID_SCH_TABLE, $name);

        if (!isset($tableInfo[0]['NAME'])) {
            return null;
        }

        $table = new TableSchema();
        $table->fullName = $table->name = $tableInfo[0]['NAME'];

        $sql = 'SHOW FULL COLUMNS FROM ' . $this->quoteSimpleTableName($table->name);
        $columns = $this->db->createCommand($sql)->queryAll();

        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
        }

        $primaryKeys = $pdo->cubrid_schema(\PDO::CUBRID_SCH_PRIMARY_KEY, $table->name);
        foreach ($primaryKeys as $key) {
            $column = $table->columns[$key['ATTR_NAME']];
            $column->isPrimaryKey = true;
            $table->primaryKey[] = $column->name;
            if ($column->autoIncrement) {
                $table->sequenceName = '';
            }
        }

        $foreignKeys = $pdo->cubrid_schema(\PDO::CUBRID_SCH_IMPORTED_KEYS, $table->name);
        foreach ($foreignKeys as $key) {
            if (isset($table->foreignKeys[$key['FK_NAME']])) {
                $table->foreignKeys[$key['FK_NAME']][$key['FKCOLUMN_NAME']] = $key['PKCOLUMN_NAME'];
            } else {
                $table->foreignKeys[$key['FK_NAME']] = [
                    $key['PKTABLE_NAME'],
                    $key['FKCOLUMN_NAME'] => $key['PKCOLUMN_NAME'],
                ];
            }
        }

        return $table;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTablePrimaryKey($tableName)
    {
        $primaryKey = $this->db->getSlavePdo()->cubrid_schema(\PDO::CUBRID_SCH_PRIMARY_KEY, $tableName);
        if (empty($primaryKey)) {
            return null;
        }

        ArrayHelper::multisort($primaryKey, 'KEY_SEQ', SORT_ASC, SORT_NUMERIC);
        return new Constraint([
            'name' => $primaryKey[0]['KEY_NAME'],
            'columnNames' => ArrayHelper::getColumn($primaryKey, 'ATTR_NAME'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableForeignKeys($tableName)
    {
        static $actionTypes = [
            0 => 'CASCADE',
            1 => 'RESTRICT',
            2 => 'NO ACTION',
            3 => 'SET NULL',
        ];

        $foreignKeys = $this->db->getSlavePdo()->cubrid_schema(\PDO::CUBRID_SCH_IMPORTED_KEYS, $tableName);
        $foreignKeys = ArrayHelper::index($foreignKeys, null, 'FK_NAME');
        ArrayHelper::multisort($foreignKeys, 'KEY_SEQ', SORT_ASC, SORT_NUMERIC);
        $result = [];
        foreach ($foreignKeys as $name => $foreignKey) {
            $result[] = new ForeignKeyConstraint([
                'name' => $name,
                'columnNames' => ArrayHelper::getColumn($foreignKey, 'FKCOLUMN_NAME'),
                'foreignTableName' => $foreignKey[0]['PKTABLE_NAME'],
                'foreignColumnNames' => ArrayHelper::getColumn($foreignKey, 'PKCOLUMN_NAME'),
                'onDelete' => isset($actionTypes[$foreignKey[0]['DELETE_RULE']]) ? $actionTypes[$foreignKey[0]['DELETE_RULE']] : null,
                'onUpdate' => isset($actionTypes[$foreignKey[0]['UPDATE_RULE']]) ? $actionTypes[$foreignKey[0]['UPDATE_RULE']] : null,
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
     * @throws NotSupportedException 如果此方法被调用，则可以抛出异常。
     */
    protected function loadTableChecks($tableName)
    {
        throw new NotSupportedException('CUBRID does not support check constraints.');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException 如果此方法被调用，则可以抛出异常。
     */
    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('CUBRID does not support default value constraints.');
    }

    /**
     * {@inheritdoc}
     */
    public function releaseSavepoint($name)
    {
        // does nothing as cubrid does not support this
    }

    /**
     * 创建 CUBRID 数据库查询构建器。
     * @return QueryBuilder 查询构建器实例
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * 将列信息加载到 [[ColumnSchema]] 对象中。
     * @param array $info 列信息
     * @return \yii\db\ColumnSchema 列架构对象
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['Field'];
        $column->allowNull = $info['Null'] === 'YES';
        $column->isPrimaryKey = false; // primary key will be set by loadTableSchema() later
        $column->autoIncrement = stripos($info['Extra'], 'auto_increment') !== false;

        $column->dbType = $info['Type'];
        $column->unsigned = strpos($column->dbType, 'unsigned') !== false;

        $column->type = self::TYPE_STRING;
        if (preg_match('/^([\w ]+)(?:\(([^\)]+)\))?$/', $column->dbType, $matches)) {
            $type = strtolower($matches[1]);
            $column->dbType = $type . (isset($matches[2]) ? "({$matches[2]})" : '');
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }
            if (!empty($matches[2])) {
                if ($type === 'enum') {
                    $values = preg_split('/\s*,\s*/', $matches[2]);
                    foreach ($values as $i => $value) {
                        $values[$i] = trim($value, "'");
                    }
                    $column->enumValues = $values;
                } else {
                    $values = explode(',', $matches[2]);
                    $column->size = $column->precision = (int) $values[0];
                    if (isset($values[1])) {
                        $column->scale = (int) $values[1];
                    }
                    if ($column->size === 1 && $type === 'bit') {
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
        }

        $column->phpType = $this->getColumnPhpType($column);

        if ($column->isPrimaryKey) {
            return $column;
        }

        if ($column->type === 'timestamp' && $info['Default'] === 'SYS_TIMESTAMP' ||
            $column->type === 'datetime' && $info['Default'] === 'SYS_DATETIME' ||
            $column->type === 'date' && $info['Default'] === 'SYS_DATE' ||
            $column->type === 'time' && $info['Default'] === 'SYS_TIME'
        ) {
            $column->defaultValue = new Expression($info['Default']);
        } elseif (isset($type) && $type === 'bit') {
            $column->defaultValue = hexdec(trim($info['Default'], 'X\''));
        } else {
            $column->defaultValue = $column->phpTypecast($info['Default']);
        }

        return $column;
    }

    /**
     * 确定给定的 PHP 数据值的 PDO 类型。
     * @param mixed $data 要确定其 PDO 类型的数据
     * @return int PDO 类型
     * @see http://www.php.net/manual/en/pdo.constants.php
     */
    public function getPdoType($data)
    {
        static $typeMap = [
            // php type => PDO type
            'boolean' => \PDO::PARAM_INT, // PARAM_BOOL is not supported by CUBRID PDO
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL' => \PDO::PARAM_NULL,
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    /**
     * {@inheritdoc}
     * @see http://www.cubrid.org/manual/91/en/sql/transaction.html#database-concurrency
     */
    public function setTransactionIsolationLevel($level)
    {
        // translate SQL92 levels to CUBRID levels:
        switch ($level) {
            case Transaction::SERIALIZABLE:
                $level = '6'; // SERIALIZABLE
                break;
            case Transaction::REPEATABLE_READ:
                $level = '5'; // REPEATABLE READ CLASS with REPEATABLE READ INSTANCES
                break;
            case Transaction::READ_COMMITTED:
                $level = '4'; // REPEATABLE READ CLASS with READ COMMITTED INSTANCES
                break;
            case Transaction::READ_UNCOMMITTED:
                $level = '3'; // REPEATABLE READ CLASS with READ UNCOMMITTED INSTANCES
                break;
        }
        parent::setTransactionIsolationLevel($level);
    }

    /**
     * {@inheritdoc}
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length, $this->db);
    }

    /**
     * 加载多种类型的约束并返回指定的约束。
     * @param string $tableName 表名。
     * @param string $returnType 返回类型：
     * - indexes
     * - uniques
     * @return mixed 约束。
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        $constraints = $this->db->getSlavePdo()->cubrid_schema(\PDO::CUBRID_SCH_CONSTRAINT, $tableName);
        $constraints = ArrayHelper::index($constraints, null, ['TYPE', 'NAME']);
        ArrayHelper::multisort($constraints, 'KEY_ORDER', SORT_ASC, SORT_NUMERIC);
        $result = [
            'indexes' => [],
            'uniques' => [],
        ];
        foreach ($constraints as $type => $names) {
            foreach ($names as $name => $constraint) {
                $isUnique = in_array((int) $type, [0, 2], true);
                $result['indexes'][] = new IndexConstraint([
                    'isPrimary' => (bool) $constraint[0]['PRIMARY_KEY'],
                    'isUnique' => $isUnique,
                    'name' => $name,
                    'columnNames' => ArrayHelper::getColumn($constraint, 'ATTR_NAME'),
                ]);
                if ($isUnique) {
                    $result['uniques'][] = new Constraint([
                        'name' => $name,
                        'columnNames' => ArrayHelper::getColumn($constraint, 'ATTR_NAME'),
                    ]);
                }
            }
        }
        foreach ($result as $type => $data) {
            $this->setTableMetadata($tableName, $type, $data);
        }

        return $result[$returnType];
    }
}
