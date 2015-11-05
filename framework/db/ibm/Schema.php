<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ibm;

use PDO;
use yii\db\Expression;
use yii\db\TableSchema;
use yii\db\Transaction;

/**
 * Schema is the class for retrieving metadata from a DB2 database.
 *
 * @author Nikita Verkhovin <vernik91@gmail.com>
 */
class Schema extends \yii\db\Schema
{
    public $typeMap = [
        'character' => self::TYPE_STRING,
        'varchar' => self::TYPE_STRING,
        'clob' => self::TYPE_TEXT,
        'graphic' => self::TYPE_STRING,
        'vargraphic' => self::TYPE_STRING,
        'dbclob' => self::TYPE_TEXT,
        'nchar' => self::TYPE_STRING,
        'nvarchar' => self::TYPE_STRING,
        'nclob' => self::TYPE_TEXT,
        'binary' => self::TYPE_BINARY,
        'varbinary' => self::TYPE_BINARY,
        'blob' => self::TYPE_BINARY,
        'smallint' => self::TYPE_SMALLINT,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'real' => self::TYPE_FLOAT,
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'decfloat' => self::TYPE_FLOAT,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (isset($this->defaultSchema)) {
            $this->db->createCommand('SET SCHEMA ' . $this->quoteSimpleTableName($this->defaultSchema))->execute();
        }
    }

    /**
     * @inheritdoc
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '"') !== false ? $name : '"' . $name . '"';
    }

    /**
     * @inheritdoc
     */
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '"') !== false || $name === '*' ? $name : '"' . $name . '"';
    }

    /**
     * @inheritdoc
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * @inheritdoc
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);

        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            return $table;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
            $table->fullName = $table->schemaName . '.' . $table->name;
        } else {
            $table->fullName = $table->name = $parts[0];
        }
    }

    /**
     * Determines the PDO type for the given PHP data value.
     * @param mixed $data the data whose PDO type is to be determined
     * @return integer the PDO type
     * @see http://www.php.net/manual/en/pdo.constants.php
     */
    public function getPdoType($data)
    {
        static $typeMap = [
            // php type => PDO type
            'boolean' => \PDO::PARAM_INT, // PARAM_BOOL is not supported by DB2 PDO
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL' => \PDO::PARAM_INT, // PDO IBM doesn't support PARAM_NULL
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    /**
     * @inheritdoc
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['name'];
        $column->dbType = $info['dbtype'];
        $column->defaultValue = isset($info['defaultvalue']) ? trim($info['defaultvalue'], "''") : null;
        $column->scale = (int) $info['scale'];
        $column->size = (int) $info['size'];
        $column->precision = (int) $info['size'];
        $column->allowNull = $info['allownull'] === '1';
        $column->isPrimaryKey = $info['isprimarykey'] === '1';
        $column->autoIncrement = $info['autoincrement'] === '1';
        $column->unsigned = false;
        $column->type = $this->typeMap[strtolower($info['dbtype'])];
        $column->enumValues = null;
        $column->comment = isset($info['comment']) ? $info['comment'] : null;

        if (preg_match('/(varchar|character|clob|graphic|binary|blob)/i', $info['dbtype'])) {
            $column->dbType .= '(' . $info['size'] . ')';
        } elseif (preg_match('/(decimal|double|real)/i', $info['dbtype'])) {
            $column->dbType .= '(' . $info['size'] . ',' . $info['scale'] . ')';
        }

        if ($column->defaultValue) {
            if ($column->type === 'timestamp' && $column->defaultValue === 'CURRENT TIMESTAMP') {
                $column->defaultValue = new Expression($column->defaultValue);
            }
        }

        $column->phpType = $this->getColumnPhpType($column);

        return $column;
    }

    /**
     * @inheritdoc
     */
    protected function findColumns($table)
    {
        $sql = <<<SQL
            SELECT
                c.colname AS name,
                c.typename AS dbtype,
                cast(c.default as varchar(254)) AS defaultvalue,
                c.scale AS scale,
                c.length AS size,
                CASE WHEN c.nulls = 'Y'         THEN 1 ELSE 0 END AS allownull,
                CASE WHEN c.keyseq IS NOT NULL  THEN 1 ELSE 0 END AS isprimarykey,
                CASE WHEN c.identity = 'Y'      THEN 1 ELSE 0 END AS autoincrement,
                c.remarks AS comment
            FROM
                syscat.columns AS c
            WHERE
                c.tabname = :table
SQL;

        if (isset($table->schemaName)) {
            $sql .= ' AND c.tabschema = :schema';
        }

        $sql .= ' ORDER BY c.colno';

        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);

        if (isset($table->schemaName)) {
            $command->bindValue(':schema', $table->schemaName);
        }
        $columns = $command->queryAll();
        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $info) {
            if ($this->db->slavePdo->getAttribute(PDO::ATTR_CASE) !== PDO::CASE_LOWER) {
                $info = array_change_key_case($info, CASE_LOWER);
            }
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($column->autoIncrement) {
                    $table->sequenceName = $column->name;
                }
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function findConstraints($table)
    {
        $sql = <<<SQL
            SELECT
                pk.tabname AS tablename,
                fk.colname AS fk,
                pk.colname AS pk
            FROM
                syscat.references AS ref
            INNER JOIN
                syscat.keycoluse AS fk ON ref.constname = fk.constname
            INNER JOIN
                syscat.keycoluse AS pk ON ref.refkeyname = pk.constname AND pk.colseq = fk.colseq
            WHERE
                fk.tabname = :table
SQL;

        if (isset($table->schemaName)) {
            $sql .= ' AND fk.tabschema = :schema';
        }

        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);

        if (isset($table->schemaName)) {
            $command->bindValue(':schema', $table->schemaName);
        }

        $results = $command->queryAll();
        $foreignKeys = [];
        foreach ($results as $result) {
            if ($this->db->slavePdo->getAttribute(PDO::ATTR_CASE) !== PDO::CASE_LOWER) {
                $result = array_change_key_case($result, CASE_LOWER);
            }
            $tablename = $result['tablename'];
            $fk = $result['fk'];
            $pk = $result['pk'];
            $foreignKeys[$tablename][$fk] = $pk;
        }
        foreach ($foreignKeys as $tablename => $keymap) {
            $constraint = [$tablename];
            foreach ($keymap as $fk => $pk) {
                $constraint[$fk] = $pk;
            }
            $table->foreignKeys[] = $constraint;
        }
    }

    /**
     * @inheritdoc
     */
    public function findUniqueIndexes($table)
    {
        $sql = <<<SQL
            SELECT
                i.indname AS indexname,
                ic.colname AS column
            FROM
                syscat.indexes AS i
            INNER JOIN
                syscat.indexcoluse AS ic ON i.indname = ic.indname
            WHERE
                i.tabname = :table
SQL;

        if (isset($table->schemaName)) {
            $sql .= ' AND tabschema = :schema';
        }

        $sql .= ' ORDER BY ic.colseq';

        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);

        if (isset($table->schemaName)) {
            $command->bindValue(':schema', $table->schemaName);
        }

        $results = $command->queryAll();
        $indexes = [];
        foreach ($results as $result) {
            if ($this->db->slavePdo->getAttribute(PDO::ATTR_CASE) !== PDO::CASE_LOWER) {
                $result = array_change_key_case($result, CASE_LOWER);
            }
            $indexes[$result['indexname']][] = $result['column'];
        }
        return $indexes;
    }

    /**
     * @inheritdoc
     */
    protected function findTableNames($schema = '')
    {
        $sql = <<<SQL
            SELECT
                t.tabname
            FROM
                syscat.tables AS t
            WHERE
                t.type in ('T', 'V') AND
                t.ownertype != 'S'
SQL;

        if ($schema !== '') {
            $sql .= ' AND t.tabschema = :schema';
        }

        $command = $this->db->createCommand($sql);

        if ($schema !== '') {
            $command->bindValue(':schema', $schema);
        }

        return $command->queryColumn();
    }

    /**
     * Sets the isolation level of the current transaction.
     * @param string $level The transaction isolation level to use for this transaction.
     */
    public function setTransactionIsolationLevel($level)
    {
        switch ($level) {
            case Transaction::READ_UNCOMMITTED:
                $this->db->createCommand('SET CURRENT ISOLATION UR')->execute();
                break;
            case Transaction::READ_COMMITTED:
                $this->db->createCommand('SET CURRENT ISOLATION CS')->execute();
                break;
            case Transaction::REPEATABLE_READ:
                $this->db->createCommand('SET CURRENT ISOLATION RS')->execute();
                break;
            case Transaction::SERIALIZABLE:
                $this->db->createCommand('SET CURRENT ISOLATION RR')->execute();
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function refreshTableSchema($name)
    {
        try {
            $sql = "CALL ADMIN_CMD ('REORG TABLE " . $this->db->quoteTableName($name) . "')";
            $this->db->createCommand($sql)->execute();
        } catch (\Exception $ex) {
            // Do not throw error on table which doesn't exist
            if (!(isset($ex->errorInfo[1]) && $ex->errorInfo[1] === -2211)) {
                throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
            }
        }

        parent::refreshTableSchema($name);
    }
}
