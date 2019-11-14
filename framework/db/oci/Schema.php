<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\base\InvalidCallException;
use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;

/**
 * Schema 类用于从 Oracle 数据库检索元数据。
 *
 * @property string $lastInsertID 插入的最后一行的行 ID，或从序列对象检索的最后一个值。
 * 此属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{
    use ConstraintFinderTrait;

    /**
     * @var array 数据库错误和相应异常的映射
     * 如果在 DB 错误消息中找到映射关系的左侧部分，则使用右侧部分的异常类。
     */
    public $exceptionMap = [
        'ORA-00001: unique constraint' => 'yii\db\IntegrityException',
    ];

    /**
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = '"';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->defaultSchema === null) {
            $username = $this->db->username;
            if (empty($username)) {
                $username = isset($this->db->masters[0]['username']) ? $this->db->masters[0]['username'] : '';
            }
            $this->defaultSchema = strtoupper($username);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $resolvedName->schemaName = $parts[0];
            $resolvedName->name = $parts[1];
        } else {
            $resolvedName->schemaName = $this->defaultSchema;
            $resolvedName->name = $name;
        }
        $resolvedName->fullName = ($resolvedName->schemaName !== $this->defaultSchema ? $resolvedName->schemaName . '.' : '') . $resolvedName->name;
        return $resolvedName;
    }

    /**
     * {@inheritdoc}
     * @see https://docs.oracle.com/cd/B28359_01/server.111/b28337/tdpsg_user_accounts.htm
     */
    protected function findSchemaNames()
    {
        static $sql = <<<'SQL'
SELECT "u"."USERNAME"
FROM "DBA_USERS" "u"
WHERE "u"."DEFAULT_TABLESPACE" NOT IN ('SYSTEM', 'SYSAUX')
ORDER BY "u"."USERNAME" ASC
SQL;

        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $sql = <<<'SQL'
SELECT
    TABLE_NAME
FROM USER_TABLES
UNION ALL
SELECT
    VIEW_NAME AS TABLE_NAME
FROM USER_VIEWS
UNION ALL
SELECT
    MVIEW_NAME AS TABLE_NAME
FROM USER_MVIEWS
ORDER BY TABLE_NAME
SQL;
            $command = $this->db->createCommand($sql);
        } else {
            $sql = <<<'SQL'
SELECT
    OBJECT_NAME AS TABLE_NAME
FROM ALL_OBJECTS
WHERE
    OBJECT_TYPE IN ('TABLE', 'VIEW', 'MATERIALIZED VIEW')
    AND OWNER = :schema
ORDER BY OBJECT_NAME
SQL;
            $command = $this->db->createCommand($sql, [':schema' => $schema]);
        }

        $rows = $command->queryAll();
        $names = [];
        foreach ($rows as $row) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $row = array_change_key_case($row, CASE_UPPER);
            }
            $names[] = $row['TABLE_NAME'];
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);
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
        return $this->loadTableConstraints($tableName, 'foreignKeys');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableIndexes($tableName)
    {
        static $sql = <<<'SQL'
SELECT
    /*+ PUSH_PRED("ui") PUSH_PRED("uicol") PUSH_PRED("uc") */
    "ui"."INDEX_NAME" AS "name",
    "uicol"."COLUMN_NAME" AS "column_name",
    CASE "ui"."UNIQUENESS" WHEN 'UNIQUE' THEN 1 ELSE 0 END AS "index_is_unique",
    CASE WHEN "uc"."CONSTRAINT_NAME" IS NOT NULL THEN 1 ELSE 0 END AS "index_is_primary"
FROM "SYS"."USER_INDEXES" "ui"
LEFT JOIN "SYS"."USER_IND_COLUMNS" "uicol"
    ON "uicol"."INDEX_NAME" = "ui"."INDEX_NAME"
LEFT JOIN "SYS"."USER_CONSTRAINTS" "uc"
    ON "uc"."OWNER" = "ui"."TABLE_OWNER" AND "uc"."CONSTRAINT_NAME" = "ui"."INDEX_NAME" AND "uc"."CONSTRAINT_TYPE" = 'P'
WHERE "ui"."TABLE_OWNER" = :schemaName AND "ui"."TABLE_NAME" = :tableName
ORDER BY "uicol"."COLUMN_POSITION" ASC
SQL;

        $resolvedName = $this->resolveTableName($tableName);
        $indexes = $this->db->createCommand($sql, [
            ':schemaName' => $resolvedName->schemaName,
            ':tableName' => $resolvedName->name,
        ])->queryAll();
        $indexes = $this->normalizePdoRowKeyCase($indexes, true);
        $indexes = ArrayHelper::index($indexes, null, 'name');
        $result = [];
        foreach ($indexes as $name => $index) {
            $result[] = new IndexConstraint([
                'isPrimary' => (bool) $index[0]['index_is_primary'],
                'isUnique' => (bool) $index[0]['index_is_unique'],
                'name' => $name,
                'columnNames' => ArrayHelper::getColumn($index, 'column_name'),
            ]);
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
     * @throws NotSupportedException 如果调用此方法，则抛出异常。
     */
    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('Oracle does not support default value constraints.');
    }

    /**
     * {@inheritdoc}
     */
    public function releaseSavepoint($name)
    {
        // does nothing as Oracle does not support this
    }

    /**
     * {@inheritdoc}
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '"') !== false ? $name : '"' . $name . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * {@inheritdoc}
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length, $this->db);
    }

    /**
     * 解析表名和数据库结构名称（如果有的话）。
     *
     * @param TableSchema $table 表元数据对象
     * @param string $name 表名
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
        } else {
            $table->schemaName = $this->defaultSchema;
            $table->name = $name;
        }

        $table->fullName = $table->schemaName !== $this->defaultSchema ? $table->schemaName . '.' . $table->name : $table->name;
    }

    /**
     * 搜集表元数据。
     * @param TableSchema $table 表结构
     * @return bool 表是否存在
     */
    protected function findColumns($table)
    {
        $sql = <<<'SQL'
SELECT
    A.COLUMN_NAME,
    A.DATA_TYPE,
    A.DATA_PRECISION,
    A.DATA_SCALE,
    (
      CASE A.CHAR_USED WHEN 'C' THEN A.CHAR_LENGTH
        ELSE A.DATA_LENGTH
      END
    ) AS DATA_LENGTH,
    A.NULLABLE,
    A.DATA_DEFAULT,
    COM.COMMENTS AS COLUMN_COMMENT
FROM ALL_TAB_COLUMNS A
    INNER JOIN ALL_OBJECTS B ON B.OWNER = A.OWNER AND LTRIM(B.OBJECT_NAME) = LTRIM(A.TABLE_NAME)
    LEFT JOIN ALL_COL_COMMENTS COM ON (A.OWNER = COM.OWNER AND A.TABLE_NAME = COM.TABLE_NAME AND A.COLUMN_NAME = COM.COLUMN_NAME)
WHERE
    A.OWNER = :schemaName
    AND B.OBJECT_TYPE IN ('TABLE', 'VIEW', 'MATERIALIZED VIEW')
    AND B.OBJECT_NAME = :tableName
ORDER BY A.COLUMN_ID
SQL;

        try {
            $columns = $this->db->createCommand($sql, [
                ':tableName' => $table->name,
                ':schemaName' => $table->schemaName,
            ])->queryAll();
        } catch (\Exception $e) {
            return false;
        }

        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $column) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $column = array_change_key_case($column, CASE_UPPER);
            }
            $c = $this->createColumn($column);
            $table->columns[$c->name] = $c;
        }

        return true;
    }

    /**
     * 表的序列名称。
     *
     * @param string $tableName
     * @internal param \yii\db\TableSchema $table->name 表结构
     * @return string|null 表序列是否存在
     */
    protected function getTableSequenceName($tableName)
    {
        $sequenceNameSql = <<<'SQL'
SELECT
    UD.REFERENCED_NAME AS SEQUENCE_NAME
FROM USER_DEPENDENCIES UD
    JOIN USER_TRIGGERS UT ON (UT.TRIGGER_NAME = UD.NAME)
WHERE
    UT.TABLE_NAME = :tableName
    AND UD.TYPE = 'TRIGGER'
    AND UD.REFERENCED_TYPE = 'SEQUENCE'
SQL;
        $sequenceName = $this->db->createCommand($sequenceNameSql, [':tableName' => $tableName])->queryScalar();
        return $sequenceName === false ? null : $sequenceName;
    }

    /**
     * @Overrides 类 'Schema' 中的方法
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php -> Oracle 不支持这一点
     *
     * 返回最后插入的行或序列的 ID。
     * @param string $sequenceName 序列对象的名称 (required by some DBMS)
     * @return string 插入的最后一行的行 ID，或从序列对象检索的最后一个值
     * @throws InvalidCallException 如果 DB 连接处于非活动状态，则抛出异常
     */
    public function getLastInsertID($sequenceName = '')
    {
        if ($this->db->isActive) {
            // get the last insert id from the master connection
            $sequenceName = $this->quoteSimpleTableName($sequenceName);
            return $this->db->useMaster(function (Connection $db) use ($sequenceName) {
                return $db->createCommand("SELECT {$sequenceName}.CURRVAL FROM DUAL")->queryScalar();
            });
        } else {
            throw new InvalidCallException('DB Connection is not active.');
        }
    }

    /**
     * 创建 ColumnSchema 实例。
     *
     * @param array $column
     * @return ColumnSchema
     */
    protected function createColumn($column)
    {
        $c = $this->createColumnSchema();
        $c->name = $column['COLUMN_NAME'];
        $c->allowNull = $column['NULLABLE'] === 'Y';
        $c->comment = $column['COLUMN_COMMENT'] === null ? '' : $column['COLUMN_COMMENT'];
        $c->isPrimaryKey = false;
        $this->extractColumnType($c, $column['DATA_TYPE'], $column['DATA_PRECISION'], $column['DATA_SCALE'], $column['DATA_LENGTH']);
        $this->extractColumnSize($c, $column['DATA_TYPE'], $column['DATA_PRECISION'], $column['DATA_SCALE'], $column['DATA_LENGTH']);

        $c->phpType = $this->getColumnPhpType($c);

        if (!$c->isPrimaryKey) {
            if (stripos($column['DATA_DEFAULT'], 'timestamp') !== false) {
                $c->defaultValue = null;
            } else {
                $defaultValue = $column['DATA_DEFAULT'];
                if ($c->type === 'timestamp' && $defaultValue === 'CURRENT_TIMESTAMP') {
                    $c->defaultValue = new Expression('CURRENT_TIMESTAMP');
                } else {
                    if ($defaultValue !== null) {
                        if (($len = strlen($defaultValue)) > 2 && $defaultValue[0] === "'"
                            && $defaultValue[$len - 1] === "'"
                        ) {
                            $defaultValue = substr($column['DATA_DEFAULT'], 1, -1);
                        } else {
                            $defaultValue = trim($defaultValue);
                        }
                    }
                    $c->defaultValue = $c->phpTypecast($defaultValue);
                }
            }
        }

        return $c;
    }

    /**
     * 查找约束，并将它们填充到传递的 TableSchema 对象中。
     * @param TableSchema $table
     */
    protected function findConstraints($table)
    {
        $sql = <<<'SQL'
SELECT
    /*+ PUSH_PRED(C) PUSH_PRED(D) PUSH_PRED(E) */
    D.CONSTRAINT_NAME,
    D.CONSTRAINT_TYPE,
    C.COLUMN_NAME,
    C.POSITION,
    D.R_CONSTRAINT_NAME,
    E.TABLE_NAME AS TABLE_REF,
    F.COLUMN_NAME AS COLUMN_REF,
    C.TABLE_NAME
FROM ALL_CONS_COLUMNS C
    INNER JOIN ALL_CONSTRAINTS D ON D.OWNER = C.OWNER AND D.CONSTRAINT_NAME = C.CONSTRAINT_NAME
    LEFT JOIN ALL_CONSTRAINTS E ON E.OWNER = D.R_OWNER AND E.CONSTRAINT_NAME = D.R_CONSTRAINT_NAME
    LEFT JOIN ALL_CONS_COLUMNS F ON F.OWNER = E.OWNER AND F.CONSTRAINT_NAME = E.CONSTRAINT_NAME AND F.POSITION = C.POSITION
WHERE
    C.OWNER = :schemaName
    AND C.TABLE_NAME = :tableName
ORDER BY D.CONSTRAINT_NAME, C.POSITION
SQL;
        $command = $this->db->createCommand($sql, [
            ':tableName' => $table->name,
            ':schemaName' => $table->schemaName,
        ]);
        $constraints = [];
        foreach ($command->queryAll() as $row) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
                $row = array_change_key_case($row, CASE_UPPER);
            }

            if ($row['CONSTRAINT_TYPE'] === 'P') {
                $table->columns[$row['COLUMN_NAME']]->isPrimaryKey = true;
                $table->primaryKey[] = $row['COLUMN_NAME'];
                if (empty($table->sequenceName)) {
                    $table->sequenceName = $this->getTableSequenceName($table->name);
                }
            }

            if ($row['CONSTRAINT_TYPE'] !== 'R') {
                // this condition is not checked in SQL WHERE because of an Oracle Bug:
                // see https://github.com/yiisoft/yii2/pull/8844
                continue;
            }

            $name = $row['CONSTRAINT_NAME'];
            if (!isset($constraints[$name])) {
                $constraints[$name] = [
                    'tableName' => $row['TABLE_REF'],
                    'columns' => [],
                ];
            }
            $constraints[$name]['columns'][$row['COLUMN_NAME']] = $row['COLUMN_REF'];
        }

        foreach ($constraints as $constraint) {
            $name = current(array_keys($constraint));

            $table->foreignKeys[$name] = array_merge([$constraint['tableName']], $constraint['columns']);
        }
    }

    /**
     * 返回给定表的所有唯一索引。
     * 每个数组元素都类似如下结构：。
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
     * @since 2.0.4
     */
    public function findUniqueIndexes($table)
    {
        $query = <<<'SQL'
SELECT
    DIC.INDEX_NAME,
    DIC.COLUMN_NAME
FROM ALL_INDEXES DI
    INNER JOIN ALL_IND_COLUMNS DIC ON DI.TABLE_NAME = DIC.TABLE_NAME AND DI.INDEX_NAME = DIC.INDEX_NAME
WHERE
    DI.UNIQUENESS = 'UNIQUE'
    AND DIC.TABLE_OWNER = :schemaName
    AND DIC.TABLE_NAME = :tableName
ORDER BY DIC.TABLE_NAME, DIC.INDEX_NAME, DIC.COLUMN_POSITION
SQL;
        $result = [];
        $command = $this->db->createCommand($query, [
            ':tableName' => $table->name,
            ':schemaName' => $table->schemaName,
        ]);
        foreach ($command->queryAll() as $row) {
            $result[$row['INDEX_NAME']][] = $row['COLUMN_NAME'];
        }

        return $result;
    }

    /**
     * 提取给定列的数据类型。
     * @param ColumnSchema $column
     * @param string $dbType DB 类型
     * @param string $precision 总位数。
     * 此参数从 2.0.4 版本开始支持。
     * @param string $scale 小数点分隔符右侧的位数。
     * 此参数从 2.0.4 版本开始支持。
     * @param string $length 字符类型的长度。
     * 此参数从 2.0.4 版本开始支持。
     */
    protected function extractColumnType($column, $dbType, $precision, $scale, $length)
    {
        $column->dbType = $dbType;

        if (strpos($dbType, 'FLOAT') !== false || strpos($dbType, 'DOUBLE') !== false) {
            $column->type = 'double';
        } elseif (strpos($dbType, 'NUMBER') !== false) {
            if ($scale === null || $scale > 0) {
                $column->type = 'decimal';
            } else {
                $column->type = 'integer';
            }
        } elseif (strpos($dbType, 'INTEGER') !== false) {
            $column->type = 'integer';
        } elseif (strpos($dbType, 'BLOB') !== false) {
            $column->type = 'binary';
        } elseif (strpos($dbType, 'CLOB') !== false) {
            $column->type = 'text';
        } elseif (strpos($dbType, 'TIMESTAMP') !== false) {
            $column->type = 'timestamp';
        } else {
            $column->type = 'string';
        }
    }

    /**
     * 从列的 DB 类型中提取大小、精度和比例信息。
     * @param ColumnSchema $column
     * @param string $dbType 列的 DB 类型
     * @param string $precision 总位数。
     * 此参数从 2.0.4 版本开始支持。
     * @param string $scale 小数点分隔符右侧的位数。
     * 此参数从 2.0.4 版本开始支持。
     * @param string $length 字符类型的长度。
     * 此参数从 2.0.4 版本开始支持。
     */
    protected function extractColumnSize($column, $dbType, $precision, $scale, $length)
    {
        $column->size = trim($length) === '' ? null : (int) $length;
        $column->precision = trim($precision) === '' ? null : (int) $precision;
        $column->scale = trim($scale) === '' ? null : (int) $scale;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $columns)
    {
        $params = [];
        $returnParams = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);
        $tableSchema = $this->getTableSchema($table);
        $returnColumns = $tableSchema->primaryKey;
        if (!empty($returnColumns)) {
            $columnSchemas = $tableSchema->columns;
            $returning = [];
            foreach ((array) $returnColumns as $name) {
                $phName = QueryBuilder::PARAM_PREFIX . (count($params) + count($returnParams));
                $returnParams[$phName] = [
                    'column' => $name,
                    'value' => null,
                ];
                if (!isset($columnSchemas[$name]) || $columnSchemas[$name]->phpType !== 'integer') {
                    $returnParams[$phName]['dataType'] = \PDO::PARAM_STR;
                } else {
                    $returnParams[$phName]['dataType'] = \PDO::PARAM_INT;
                }
                $returnParams[$phName]['size'] = isset($columnSchemas[$name]->size) ? $columnSchemas[$name]->size : -1;
                $returning[] = $this->quoteColumnName($name);
            }
            $sql .= ' RETURNING ' . implode(', ', $returning) . ' INTO ' . implode(', ', array_keys($returnParams));
        }

        $command = $this->db->createCommand($sql, $params);
        $command->prepare(false);

        foreach ($returnParams as $name => &$value) {
            $command->pdoStatement->bindParam($name, $value['value'], $value['dataType'], $value['size']);
        }

        if (!$command->execute()) {
            return false;
        }

        $result = [];
        foreach ($returnParams as $value) {
            $result[$value['column']] = $value['value'];
        }

        return $result;
    }

    /**
     * 加载多种类型的约束，并返回指定的约束。
     * @param string $tableName 表名。
     * @param string $returnType 返回约束类型：
     * - primaryKey
     * - foreignKeys
     * - uniques
     * - checks
     * @return mixed constraints.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        static $sql = <<<'SQL'
SELECT
    /*+ PUSH_PRED("uc") PUSH_PRED("uccol") PUSH_PRED("fuc") */
    "uc"."CONSTRAINT_NAME" AS "name",
    "uccol"."COLUMN_NAME" AS "column_name",
    "uc"."CONSTRAINT_TYPE" AS "type",
    "fuc"."OWNER" AS "foreign_table_schema",
    "fuc"."TABLE_NAME" AS "foreign_table_name",
    "fuccol"."COLUMN_NAME" AS "foreign_column_name",
    "uc"."DELETE_RULE" AS "on_delete",
    "uc"."SEARCH_CONDITION" AS "check_expr"
FROM "USER_CONSTRAINTS" "uc"
INNER JOIN "USER_CONS_COLUMNS" "uccol"
    ON "uccol"."OWNER" = "uc"."OWNER" AND "uccol"."CONSTRAINT_NAME" = "uc"."CONSTRAINT_NAME"
LEFT JOIN "USER_CONSTRAINTS" "fuc"
    ON "fuc"."OWNER" = "uc"."R_OWNER" AND "fuc"."CONSTRAINT_NAME" = "uc"."R_CONSTRAINT_NAME"
LEFT JOIN "USER_CONS_COLUMNS" "fuccol"
    ON "fuccol"."OWNER" = "fuc"."OWNER" AND "fuccol"."CONSTRAINT_NAME" = "fuc"."CONSTRAINT_NAME" AND "fuccol"."POSITION" = "uccol"."POSITION"
WHERE "uc"."OWNER" = :schemaName AND "uc"."TABLE_NAME" = :tableName
ORDER BY "uccol"."POSITION" ASC
SQL;

        $resolvedName = $this->resolveTableName($tableName);
        $constraints = $this->db->createCommand($sql, [
            ':schemaName' => $resolvedName->schemaName,
            ':tableName' => $resolvedName->name,
        ])->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = ArrayHelper::index($constraints, null, ['type', 'name']);
        $result = [
            'primaryKey' => null,
            'foreignKeys' => [],
            'uniques' => [],
            'checks' => [],
        ];
        foreach ($constraints as $type => $names) {
            foreach ($names as $name => $constraint) {
                switch ($type) {
                    case 'P':
                        $result['primaryKey'] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'R':
                        $result['foreignKeys'][] = new ForeignKeyConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'foreignSchemaName' => $constraint[0]['foreign_table_schema'],
                            'foreignTableName' => $constraint[0]['foreign_table_name'],
                            'foreignColumnNames' => ArrayHelper::getColumn($constraint, 'foreign_column_name'),
                            'onDelete' => $constraint[0]['on_delete'],
                            'onUpdate' => null,
                        ]);
                        break;
                    case 'U':
                        $result['uniques'][] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'C':
                        $result['checks'][] = new CheckConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'expression' => $constraint[0]['check_expr'],
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
}
