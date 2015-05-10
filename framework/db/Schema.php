<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Object;
use yii\base\NotSupportedException;
use yii\base\InvalidCallException;
use yii\caching\Cache;
use yii\caching\TagDependency;

/**
 * Schema is the base class for concrete DBMS-specific schema classes.
 *
 * Schema represents the database schema information that is DBMS specific.
 *
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the
 * sequence object. This property is read-only.
 * @property QueryBuilder $queryBuilder The query builder for this connection. This property is read-only.
 * @property string[] $schemaNames All schema names in the database, except system schemas. This property is
 * read-only.
 * @property string[] $tableNames All table names in the database. This property is read-only.
 * @property TableSchema[] $tableSchemas The metadata for all tables in the database. Each array element is an
 * instance of [[TableSchema]] or its child class. This property is read-only.
 * @property string $transactionIsolationLevel The transaction isolation level to use for this transaction.
 * This can be one of [[Transaction::READ_UNCOMMITTED]], [[Transaction::READ_COMMITTED]],
 * [[Transaction::REPEATABLE_READ]] and [[Transaction::SERIALIZABLE]] but also a string containing DBMS specific
 * syntax to be used after `SET TRANSACTION ISOLATION LEVEL`. This property is write-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Schema extends Object
{
    /**
     * The following are the supported abstract column data types.
     */
    const TYPE_PK = 'pk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';

    /**
     * @var Connection the database connection
     */
    public $db;
    /**
     * @var string the default schema name used for the current session.
     */
    public $defaultSchema;
    /**
     * @var array map of DB errors and corresponding exceptions
     * If left part is found in DB error message exception class from the right part is used.
     */
    public $exceptionMap = [
        'SQLSTATE[23' => 'yii\db\IntegrityException',
    ];

    /**
     * @var array list of ALL schema names in the database, except system schemas
     */
    private $_schemaNames;
    /**
     * @var array list of ALL table names in the database
     */
    private $_tableNames = [];
    /**
     * @var array list of loaded table metadata (table name => TableSchema)
     */
    private $_tables = [];
    /**
     * @var QueryBuilder the query builder for this database
     */
    private $_builder;


    /**
     * @return \yii\db\ColumnSchema
     * @throws \yii\base\InvalidConfigException
     */
    protected function createColumnSchema()
    {
        return Yii::createObject('yii\db\ColumnSchema');
    }

    /**
     * Loads the metadata for the specified table.
     * @param string $name table name
     * @return TableSchema DBMS-dependent table metadata, null if the table does not exist.
     */
    abstract protected function loadTableSchema($name);

    /**
     * Obtains the metadata for the named table.
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
     * @return TableSchema table metadata. Null if the named table does not exist.
     */
    public function getTableSchema($name, $refresh = false)
    {
        if (array_key_exists($name, $this->_tables) && !$refresh) {
            return $this->_tables[$name];
        }

        $db = $this->db;
        $realName = $this->getRawTableName($name);

        if ($db->enableSchemaCache && !in_array($name, $db->schemaCacheExclude, true)) {
            /* @var $cache Cache */
            $cache = is_string($db->schemaCache) ? Yii::$app->get($db->schemaCache, false) : $db->schemaCache;
            if ($cache instanceof Cache) {
                $key = $this->getCacheKey($name);
                if ($refresh || ($table = $cache->get($key)) === false) {
                    $this->_tables[$name] = $table = $this->loadTableSchema($realName);
                    if ($table !== null) {
                        $cache->set($key, $table, $db->schemaCacheDuration, new TagDependency([
                            'tags' => $this->getCacheTag(),
                        ]));
                    }
                } else {
                    $this->_tables[$name] = $table;
                }

                return $this->_tables[$name];
            }
        }

        return $this->_tables[$name] = $this->loadTableSchema($realName);
    }

    /**
     * Returns the cache key for the specified table name.
     * @param string $name the table name
     * @return mixed the cache key
     */
    protected function getCacheKey($name)
    {
        return [
            __CLASS__,
            $this->db->dsn,
            $this->db->username,
            $name,
        ];
    }

    /**
     * Returns the cache tag name.
     * This allows [[refresh()]] to invalidate all cached table schemas.
     * @return string the cache tag name
     */
    protected function getCacheTag()
    {
        return md5(serialize([
            __CLASS__,
            $this->db->dsn,
            $this->db->username,
        ]));
    }

    /**
     * Returns the metadata for all tables in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * @param boolean $refresh whether to fetch the latest available table schemas. If this is false,
     * cached data may be returned if available.
     * @return TableSchema[] the metadata for all tables in the database.
     * Each array element is an instance of [[TableSchema]] or its child class.
     */
    public function getTableSchemas($schema = '', $refresh = false)
    {
        $tables = [];
        foreach ($this->getTableNames($schema, $refresh) as $name) {
            if ($schema !== '') {
                $name = $schema . '.' . $name;
            }
            if (($table = $this->getTableSchema($name, $refresh)) !== null) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * Returns all schema names in the database, except system schemas.
     * @param boolean $refresh whether to fetch the latest available schema names. If this is false,
     * schema names fetched previously (if available) will be returned.
     * @return string[] all schema names in the database, except system schemas.
     * @since 2.0.4
     */
    public function getSchemaNames($refresh = false)
    {
        if ($this->_schemaNames === null || $refresh) {
            $this->_schemaNames = $this->findSchemaNames();
        }

        return $this->_schemaNames;
    }

    /**
     * Returns all table names in the database.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema name.
     * If not empty, the returned table names will be prefixed with the schema name.
     * @param boolean $refresh whether to fetch the latest available table names. If this is false,
     * table names fetched previously (if available) will be returned.
     * @return string[] all table names in the database.
     */
    public function getTableNames($schema = '', $refresh = false)
    {
        if (!isset($this->_tableNames[$schema]) || $refresh) {
            $this->_tableNames[$schema] = $this->findTableNames($schema);
        }

        return $this->_tableNames[$schema];
    }

    /**
     * @return QueryBuilder the query builder for this connection.
     */
    public function getQueryBuilder()
    {
        if ($this->_builder === null) {
            $this->_builder = $this->createQueryBuilder();
        }

        return $this->_builder;
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
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL' => \PDO::PARAM_NULL,
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    /**
     * Refreshes the schema.
     * This method cleans up all cached table schemas so that they can be re-created later
     * to reflect the database schema change.
     */
    public function refresh()
    {
        /* @var $cache Cache */
        $cache = is_string($this->db->schemaCache) ? Yii::$app->get($this->db->schemaCache, false) : $this->db->schemaCache;
        if ($this->db->enableSchemaCache && $cache instanceof Cache) {
            TagDependency::invalidate($cache, $this->getCacheTag());
        }
        $this->_tableNames = [];
        $this->_tables = [];
    }

    /**
     * Creates a query builder for the database.
     * This method may be overridden by child classes to create a DBMS-specific query builder.
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * Returns all schema names in the database, including the default one but not system schemas.
     * This method should be overridden by child classes in order to support this feature
     * because the default implementation simply throws an exception.
     * @return array all schema names in the database, except system schemas
     * @throws NotSupportedException if this method is called
     * @since 2.0.4
     */
    protected function findSchemaNames()
    {
        throw new NotSupportedException(get_class($this) . ' does not support fetching all schema names.');
    }

    /**
     * Returns all table names in the database.
     * This method should be overridden by child classes in order to support this feature
     * because the default implementation simply throws an exception.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
     * @return array all table names in the database. The names have NO schema name prefix.
     * @throws NotSupportedException if this method is called
     */
    protected function findTableNames($schema = '')
    {
        throw new NotSupportedException(get_class($this) . ' does not support fetching all table names.');
    }

    /**
     * Returns all unique indexes for the given table.
     * Each array element is of the following structure:
     *
     * ~~~
     * [
     *  'IndexName1' => ['col1' [, ...]],
     *  'IndexName2' => ['col2' [, ...]],
     * ]
     * ~~~
     *
     * This method should be overridden by child classes in order to support this feature
     * because the default implementation simply throws an exception
     * @param TableSchema $table the table metadata
     * @return array all unique indexes for the given table.
     * @throws NotSupportedException if this method is called
     */
    public function findUniqueIndexes($table)
    {
        throw new NotSupportedException(get_class($this) . ' does not support getting unique indexes information.');
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @throws InvalidCallException if the DB connection is not active
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID($sequenceName = '')
    {
        if ($this->db->isActive) {
            return $this->db->pdo->lastInsertId($sequenceName === '' ? null : $this->quoteSimpleTableName($sequenceName));
        } else {
            throw new InvalidCallException('DB Connection is not active.');
        }
    }

    /**
     * @return boolean whether this DBMS supports [savepoint](http://en.wikipedia.org/wiki/Savepoint).
     */
    public function supportsSavepoint()
    {
        return $this->db->enableSavepoint;
    }

    /**
     * Creates a new savepoint.
     * @param string $name the savepoint name
     */
    public function createSavepoint($name)
    {
        $this->db->createCommand("SAVEPOINT $name")->execute();
    }

    /**
     * Releases an existing savepoint.
     * @param string $name the savepoint name
     */
    public function releaseSavepoint($name)
    {
        $this->db->createCommand("RELEASE SAVEPOINT $name")->execute();
    }

    /**
     * Rolls back to a previously created savepoint.
     * @param string $name the savepoint name
     */
    public function rollBackSavepoint($name)
    {
        $this->db->createCommand("ROLLBACK TO SAVEPOINT $name")->execute();
    }

    /**
     * Sets the isolation level of the current transaction.
     * @param string $level The transaction isolation level to use for this transaction.
     * This can be one of [[Transaction::READ_UNCOMMITTED]], [[Transaction::READ_COMMITTED]], [[Transaction::REPEATABLE_READ]]
     * and [[Transaction::SERIALIZABLE]] but also a string containing DBMS specific syntax to be used
     * after `SET TRANSACTION ISOLATION LEVEL`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    public function setTransactionIsolationLevel($level)
    {
        $this->db->createCommand("SET TRANSACTION ISOLATION LEVEL $level;")->execute();
    }

    /**
     * Executes the INSERT command, returning primary key values.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     * @return array primary key values or false if the command fails
     * @since 2.0.4
     */
    public function insert($table, $columns)
    {
        $command = $this->db->createCommand()->insert($table, $columns);
        if (!$command->execute()) {
            return false;
        }
        $tableSchema = $this->getTableSchema($table);
        $result = [];
        foreach ($tableSchema->primaryKey as $name) {
            if ($tableSchema->columns[$name]->autoIncrement) {
                $result[$name] = $this->getLastInsertID($tableSchema->sequenceName);
                break;
            } else {
                $result[$name] = isset($columns[$name]) ? $columns[$name] : $tableSchema->columns[$name]->defaultValue;
            }
        }
        return $result;
    }

    /**
     * Quotes a string value for use in a query.
     * Note that if the parameter is not a string, it will be returned without change.
     * @param string $str string to be quoted
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValue($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        if (($value = $this->db->getSlavePdo()->quote($str)) !== false) {
            return $value;
        } else {
            // the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
        }
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains '(' or '{{',
     * then this method will do nothing.
     * @param string $name table name
     * @return string the properly quoted table name
     * @see quoteSimpleTableName()
     */
    public function quoteTableName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }

        return implode('.', $parts);

    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains '(', '[[' or '{{',
     * then this method will do nothing.
     * @param string $name column name
     * @return string the properly quoted column name
     * @see quoteSimpleColumnName()
     */
    public function quoteColumnName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '[[') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }

        return $prefix . $this->quoteSimpleColumnName($name);
    }

    /**
     * Quotes a simple table name for use in a query.
     * A simple table name should contain the table name only without any schema prefix.
     * If the table name is already quoted, this method will do nothing.
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, "'") !== false ? $name : "'" . $name . "'";
    }

    /**
     * Quotes a simple column name for use in a query.
     * A simple column name should contain the column name only without any prefix.
     * If the column name is already quoted or is the asterisk character '*', this method will do nothing.
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '"') !== false || $name === '*' ? $name : '"' . $name . '"';
    }

    /**
     * Returns the actual name of a given table name.
     * This method will strip off curly brackets from the given table name
     * and replace the percentage character '%' with [[Connection::tablePrefix]].
     * @param string $name the table name to be converted
     * @return string the real name of the given table name
     */
    public function getRawTableName($name)
    {
        if (strpos($name, '{{') !== false) {
            $name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);

            return str_replace('%', $this->db->tablePrefix, $name);
        } else {
            return $name;
        }
    }

    /**
     * Extracts the PHP type from abstract DB type.
     * @param ColumnSchema $column the column schema information
     * @return string PHP type name
     */
    protected function getColumnPhpType($column)
    {
        static $typeMap = [
            // abstract type => php type
            'smallint' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        if (isset($typeMap[$column->type])) {
            if ($column->type === 'bigint') {
                return PHP_INT_SIZE == 8 && !$column->unsigned ? 'integer' : 'string';
            } elseif ($column->type === 'integer') {
                return PHP_INT_SIZE == 4 && $column->unsigned ? 'string' : 'integer';
            } else {
                return $typeMap[$column->type];
            }
        } else {
            return 'string';
        }
    }

    /**
     * Converts a DB exception to a more concrete one if possible.
     *
     * @param \Exception $e
     * @param string $rawSql SQL that produced exception
     * @return Exception
     */
    public function convertException(\Exception $e, $rawSql)
    {
        if ($e instanceof Exception) {
            return $e;
        }

        $exceptionClass = '\yii\db\Exception';
        foreach ($this->exceptionMap as $error => $class) {
            if (strpos($e->getMessage(), $error) !== false) {
                $exceptionClass = $class;
            }
        }
        $message = $e->getMessage()  . "\nThe SQL being executed was: $rawSql";
        $errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
        return new $exceptionClass($message, $errorInfo, (int) $e->getCode(), $e);
    }

    /**
     * Returns a value indicating whether a SQL statement is for read purpose.
     * @param string $sql the SQL statement
     * @return boolean whether a SQL statement is for read purpose.
     */
    public function isReadQuery($sql)
    {
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE)\b/i';
        return preg_match($pattern, $sql) > 0;
    }
}
