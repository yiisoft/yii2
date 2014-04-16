<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use PDO;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\caching\Cache;

/**
 * Connection represents a connection to a database via [PDO](http://www.php.net/manual/en/ref.pdo.php).
 *
 * Connection works together with [[Command]], [[DataReader]] and [[Transaction]]
 * to provide data access to various DBMS in a common set of APIs. They are a thin wrapper
 * of the [[PDO PHP extension]](http://www.php.net/manual/en/ref.pdo.php).
 *
 * To establish a DB connection, set [[dsn]], [[username]] and [[password]], and then
 * call [[open()]] to be true.
 *
 * The following example shows how to create a Connection instance and establish
 * the DB connection:
 *
 * ~~~
 * $connection = new \yii\db\Connection([
 *     'dsn' => $dsn,
 *     'username' => $username,
 *     'password' => $password,
 * ]);
 * $connection->open();
 * ~~~
 *
 * After the DB connection is established, one can execute SQL statements like the following:
 *
 * ~~~
 * $command = $connection->createCommand('SELECT * FROM post');
 * $posts = $command->queryAll();
 * $command = $connection->createCommand('UPDATE post SET status=1');
 * $command->execute();
 * ~~~
 *
 * One can also do prepared SQL execution and bind parameters to the prepared SQL.
 * When the parameters are coming from user input, you should use this approach
 * to prevent SQL injection attacks. The following is an example:
 *
 * ~~~
 * $command = $connection->createCommand('SELECT * FROM post WHERE id=:id');
 * $command->bindValue(':id', $_GET['id']);
 * $post = $command->query();
 * ~~~
 *
 * For more information about how to perform various DB queries, please refer to [[Command]].
 *
 * If the underlying DBMS supports transactions, you can perform transactional SQL queries
 * like the following:
 *
 * ~~~
 * $transaction = $connection->beginTransaction();
 * try {
 *     $connection->createCommand($sql1)->execute();
 *     $connection->createCommand($sql2)->execute();
 *     // ... executing other SQL statements ...
 *     $transaction->commit();
 * } catch (Exception $e) {
 *     $transaction->rollBack();
 * }
 * ~~~
 *
 * Connection is often used as an application component and configured in the application
 * configuration like the following:
 *
 * ~~~
 * [
 *	 'components' => [
 *		 'db' => [
 *			 'class' => '\yii\db\Connection',
 *			 'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
 *			 'username' => 'root',
 *			 'password' => '',
 *			 'charset' => 'utf8',
 *		 ],
 *	 ],
 * ]
 * ~~~
 *
 * @property string $driverName Name of the DB driver.
 * @property boolean $isActive Whether the DB connection is established. This property is read-only.
 * @property string $lastInsertID The row ID of the last row inserted, or the last value retrieved from the
 * sequence object. This property is read-only.
 * @property QueryBuilder $queryBuilder The query builder for the current DB connection. This property is
 * read-only.
 * @property Schema $schema The schema information for the database opened by this connection. This property
 * is read-only.
 * @property Transaction $transaction The currently active transaction. Null if no active transaction. This
 * property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Connection extends Component
{
    /**
     * @event Event an event that is triggered after a DB connection is established
     */
    const EVENT_AFTER_OPEN = 'afterOpen';

    /**
     * @var string the Data Source Name, or DSN, contains the information required to connect to the database.
     * Please refer to the [PHP manual](http://www.php.net/manual/en/function.PDO-construct.php) on
     * the format of the DSN string.
     * @see charset
     */
    public $dsn;
    /**
     * @var string the username for establishing DB connection. Defaults to `null` meaning no username to use.
     */
    public $username;
    /**
     * @var string the password for establishing DB connection. Defaults to `null` meaning no password to use.
     */
    public $password;
    /**
     * @var array PDO attributes (name => value) that should be set when calling [[open()]]
     * to establish a DB connection. Please refer to the
     * [PHP manual](http://www.php.net/manual/en/function.PDO-setAttribute.php) for
     * details about available attributes.
     */
    public $attributes;
    /**
     * @var PDO the PHP PDO instance associated with this DB connection.
     * This property is mainly managed by [[open()]] and [[close()]] methods.
     * When a DB connection is active, this property will represent a PDO instance;
     * otherwise, it will be null.
     */
    public $pdo;
    /**
     * @var boolean whether to enable schema caching.
     * Note that in order to enable truly schema caching, a valid cache component as specified
     * by [[schemaCache]] must be enabled and [[enableSchemaCache]] must be set true.
     * @see schemaCacheDuration
     * @see schemaCacheExclude
     * @see schemaCache
     */
    public $enableSchemaCache = false;
    /**
     * @var integer number of seconds that table metadata can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableSchemaCache
     */
    public $schemaCacheDuration = 3600;
    /**
     * @var array list of tables whose metadata should NOT be cached. Defaults to empty array.
     * The table names may contain schema prefix, if any. Do not quote the table names.
     * @see enableSchemaCache
     */
    public $schemaCacheExclude = [];
    /**
     * @var Cache|string the cache object or the ID of the cache application component that
     * is used to cache the table metadata.
     * @see enableSchemaCache
     */
    public $schemaCache = 'cache';
    /**
     * @var boolean whether to enable query caching.
     * Note that in order to enable query caching, a valid cache component as specified
     * by [[queryCache]] must be enabled and [[enableQueryCache]] must be set true.
     *
     * Methods [[beginCache()]] and [[endCache()]] can be used as shortcuts to turn on
     * and off query caching on the fly.
     * @see queryCacheDuration
     * @see queryCache
     * @see queryCacheDependency
     * @see beginCache()
     * @see endCache()
     */
    public $enableQueryCache = false;
    /**
     * @var integer number of seconds that query results can remain valid in cache.
     * Defaults to 3600, meaning 3600 seconds, or one hour.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableQueryCache
     */
    public $queryCacheDuration = 3600;
    /**
     * @var \yii\caching\Dependency the dependency that will be used when saving query results into cache.
     * Defaults to null, meaning no dependency.
     * @see enableQueryCache
     */
    public $queryCacheDependency;
    /**
     * @var Cache|string the cache object or the ID of the cache application component
     * that is used for query caching.
     * @see enableQueryCache
     */
    public $queryCache = 'cache';
    /**
     * @var string the charset used for database connection. The property is only used
     * for MySQL, PostgreSQL and CUBRID databases. Defaults to null, meaning using default charset
     * as specified by the database.
     *
     * Note that if you're using GBK or BIG5 then it's highly recommended to
     * specify charset via DSN like 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
     */
    public $charset;
    /**
     * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
     * will use the native prepare support if available. For some databases (such as MySQL),
     * this may need to be set true so that PDO can emulate the prepare support to bypass
     * the buggy native prepare support.
     * The default value is null, which means the PDO ATTR_EMULATE_PREPARES value will not be changed.
     */
    public $emulatePrepare;
    /**
     * @var string the common prefix or suffix for table names. If a table name is given
     * as `{{%TableName}}`, then the percentage character `%` will be replaced with this
     * property value. For example, `{{%post}}` becomes `{{tbl_post}}`.
     */
    public $tablePrefix = '';
    /**
     * @var array mapping between PDO driver names and [[Schema]] classes.
     * The keys of the array are PDO driver names while the values the corresponding
     * schema class name or configuration. Please refer to [[Yii::createObject()]] for
     * details on how to specify a configuration.
     *
     * This property is mainly used by [[getSchema()]] when fetching the database schema information.
     * You normally do not need to set this property unless you want to use your own
     * [[Schema]] class to support DBMS that is not supported by Yii.
     */
    public $schemaMap = [
        'pgsql' => 'yii\db\pgsql\Schema',    // PostgreSQL
        'mysqli' => 'yii\db\mysql\Schema',   // MySQL
        'mysql' => 'yii\db\mysql\Schema',    // MySQL
        'sqlite' => 'yii\db\sqlite\Schema',  // sqlite 3
        'sqlite2' => 'yii\db\sqlite\Schema', // sqlite 2
        'sqlsrv' => 'yii\db\mssql\Schema',   // newer MSSQL driver on MS Windows hosts
        'oci' => 'yii\db\oci\Schema',        // Oracle driver
        'mssql' => 'yii\db\mssql\Schema',    // older MSSQL driver on MS Windows hosts
        'dblib' => 'yii\db\mssql\Schema',    // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => 'yii\db\cubrid\Schema',  // CUBRID
    ];
    /**
     * @var string Custom PDO wrapper class. If not set, it will use "PDO" or "yii\db\mssql\PDO" when MSSQL is used.
     */
    public $pdoClass;
    /**
     * @var boolean whether to enable [savepoint](http://en.wikipedia.org/wiki/Savepoint).
     * Note that if the underlying DBMS does not support savepoint, setting this property to be true will have no effect.
     */
    public $enableSavepoint = true;
    /**
     * @var Transaction the currently active transaction
     */
    private $_transaction;
    /**
     * @var Schema the database schema
     */
    private $_schema;
    /**
     * @var string driver name
     */
    private $_driverName;

    /**
     * Returns a value indicating whether the DB connection is established.
     * @return boolean whether the DB connection is established
     */
    public function getIsActive()
    {
        return $this->pdo !== null;
    }

    /**
     * Turns on query caching.
     * This method is provided as a shortcut to setting two properties that are related
     * with query caching: [[queryCacheDuration]] and [[queryCacheDependency]].
     * @param integer $duration the number of seconds that query results may remain valid in cache.
     * If not set, it will use the value of [[queryCacheDuration]]. See [[queryCacheDuration]] for more details.
     * @param \yii\caching\Dependency $dependency the dependency for the cached query result.
     * See [[queryCacheDependency]] for more details.
     */
    public function beginCache($duration = null, $dependency = null)
    {
        $this->enableQueryCache = true;
        if ($duration !== null) {
            $this->queryCacheDuration = $duration;
        }
        $this->queryCacheDependency = $dependency;
    }

    /**
     * Turns off query caching.
     */
    public function endCache()
    {
        $this->enableQueryCache = false;
    }

    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     * @throws Exception if connection fails
     */
    public function open()
    {
        if ($this->pdo === null) {
            if (empty($this->dsn)) {
                throw new InvalidConfigException('Connection::dsn cannot be empty.');
            }
            $token = 'Opening DB connection: ' . $this->dsn;
            try {
                Yii::trace($token, __METHOD__);
                Yii::beginProfile($token, __METHOD__);
                $this->pdo = $this->createPdoInstance();
                $this->initConnection();
                Yii::endProfile($token, __METHOD__);
            } catch (\PDOException $e) {
                Yii::endProfile($token, __METHOD__);
                throw new Exception($e->getMessage(), $e->errorInfo, (int) $e->getCode(), $e);
            }
        }
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        if ($this->pdo !== null) {
            Yii::trace('Closing DB connection: ' . $this->dsn, __METHOD__);
            $this->pdo = null;
            $this->_schema = null;
            $this->_transaction = null;
        }
    }

    /**
     * Creates the PDO instance.
     * This method is called by [[open]] to establish a DB connection.
     * The default implementation will create a PHP PDO instance.
     * You may override this method if the default PDO needs to be adapted for certain DBMS.
     * @return PDO the pdo instance
     */
    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if ($pdoClass === null) {
            $pdoClass = 'PDO';
            if ($this->_driverName !== null) {
                $driver = $this->_driverName;
            } elseif (($pos = strpos($this->dsn, ':')) !== false) {
                $driver = strtolower(substr($this->dsn, 0, $pos));
            }
            if (isset($driver) && ($driver === 'mssql' || $driver === 'dblib' || $driver === 'sqlsrv')) {
                $pdoClass = 'yii\db\mssql\PDO';
            }
        }

        return new $pdoClass($this->dsn, $this->username, $this->password, $this->attributes);
    }

    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation turns on `PDO::ATTR_EMULATE_PREPARES`
     * if [[emulatePrepare]] is true, and sets the database [[charset]] if it is not empty.
     * It then triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->emulatePrepare !== null && constant('PDO::ATTR_EMULATE_PREPARES')) {
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        }
        if ($this->charset !== null && in_array($this->getDriverName(), ['pgsql', 'mysql', 'mysqli', 'cubrid'])) {
            $this->pdo->exec('SET NAMES ' . $this->pdo->quote($this->charset));
        }
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the DB command
     */
    public function createCommand($sql = null, $params = [])
    {
        $this->open();
        $command = new Command([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

    /**
     * Returns the currently active transaction.
     * @return Transaction the currently active transaction. Null if no active transaction.
     */
    public function getTransaction()
    {
        return $this->_transaction && $this->_transaction->getIsActive() ? $this->_transaction : null;
    }

    /**
     * Starts a transaction.
     * @return Transaction the transaction initiated
     */
    public function beginTransaction()
    {
        $this->open();

        if (($transaction = $this->getTransaction()) === null) {
            $transaction = $this->_transaction = new Transaction(['db' => $this]);
        }
        $transaction->begin();

        return $transaction;
    }

    /**
     * Returns the schema information for the database opened by this connection.
     * @return Schema the schema information for the database opened by this connection.
     * @throws NotSupportedException if there is no support for the current driver type
     */
    public function getSchema()
    {
        if ($this->_schema !== null) {
            return $this->_schema;
        } else {
            $driver = $this->getDriverName();
            if (isset($this->schemaMap[$driver])) {
                $config = !is_array($this->schemaMap[$driver]) ? ['class' => $this->schemaMap[$driver]] : $this->schemaMap[$driver];
                $config['db'] = $this;

                return $this->_schema = Yii::createObject($config);
            } else {
                throw new NotSupportedException("Connection does not support reading schema information for '$driver' DBMS.");
            }
        }
    }

    /**
     * Returns the query builder for the current DB connection.
     * @return QueryBuilder the query builder for the current DB connection.
     */
    public function getQueryBuilder()
    {
        return $this->getSchema()->getQueryBuilder();
    }

    /**
     * Obtains the schema information for the named table.
     * @param string $name table name.
     * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
     * @return TableSchema table schema information. Null if the named table does not exist.
     */
    public function getTableSchema($name, $refresh = false)
    {
        return $this->getSchema()->getTableSchema($name, $refresh);
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID($sequenceName = '')
    {
        return $this->getSchema()->getLastInsertID($sequenceName);
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
        return $this->getSchema()->quoteValue($str);
    }

    /**
     * Quotes a table name for use in a query.
     * If the table name contains schema prefix, the prefix will also be properly quoted.
     * If the table name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     * @param string $name table name
     * @return string the properly quoted table name
     */
    public function quoteTableName($name)
    {
        return $this->getSchema()->quoteTableName($name);
    }

    /**
     * Quotes a column name for use in a query.
     * If the column name contains prefix, the prefix will also be properly quoted.
     * If the column name is already quoted or contains special characters including '(', '[[' and '{{',
     * then this method will do nothing.
     * @param string $name column name
     * @return string the properly quoted column name
     */
    public function quoteColumnName($name)
    {
        return $this->getSchema()->quoteColumnName($name);
    }

    /**
     * Processes a SQL statement by quoting table and column names that are enclosed within double brackets.
     * Tokens enclosed within double curly brackets are treated as table names, while
     * tokens enclosed within double square brackets are column names. They will be quoted accordingly.
     * Also, the percentage character "%" at the beginning or ending of a table name will be replaced
     * with [[tablePrefix]].
     * @param string $sql the SQL to be quoted
     * @return string the quoted SQL
     */
    public function quoteSql($sql)
    {
        return preg_replace_callback('/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/',
            function ($matches) {
                if (isset($matches[3])) {
                    return $this->quoteColumnName($matches[3]);
                } else {
                    return str_replace('%', $this->tablePrefix, $this->quoteTableName($matches[2]));
                }
            }, $sql);
    }

    /**
     * Returns the name of the DB driver. Based on the the current [[dsn]], in case it was not set explicitly
     * by an end user.
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        if ($this->_driverName === null) {
            if (($pos = strpos($this->dsn, ':')) !== false) {
                $this->_driverName = strtolower(substr($this->dsn, 0, $pos));
            } else {
                $this->open();
                $this->_driverName = strtolower($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
            }
        }
        return $this->_driverName;
    }

    /**
     * Changes the current driver name.
     * @param string $driverName name of the DB driver
     */
    public function setDriverName($driverName)
    {
        $this->_driverName = strtolower($driverName);
    }
}
