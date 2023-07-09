<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * Command represents a SQL statement to be executed against a database.
 *
 * A command object is usually created by calling [[Connection::createCommand()]].
 * The SQL statement it represents can be set via the [[sql]] property.
 *
 * To execute a non-query SQL (such as INSERT, DELETE, UPDATE), call [[execute()]].
 * To execute a SQL statement that returns a result data set (such as SELECT),
 * use [[queryAll()]], [[queryOne()]], [[queryColumn()]], [[queryScalar()]], or [[query()]].
 *
 * For example,
 *
 * ```php
 * $users = $connection->createCommand('SELECT * FROM user')->queryAll();
 * ```
 *
 * Command supports SQL statement preparation and parameter binding.
 * Call [[bindValue()]] to bind a value to a SQL parameter;
 * Call [[bindParam()]] to bind a PHP variable to a SQL parameter.
 * When binding a parameter, the SQL statement is automatically prepared.
 * You may also call [[prepare()]] explicitly to prepare a SQL statement.
 *
 * Command also supports building SQL statements by providing methods such as [[insert()]],
 * [[update()]], etc. For example, the following code will create and execute an INSERT SQL statement:
 *
 * ```php
 * $connection->createCommand()->insert('user', [
 *     'name' => 'Sam',
 *     'age' => 30,
 * ])->execute();
 * ```
 *
 * To build SELECT SQL statements, please use [[Query]] instead.
 *
 * For more details and usage information on Command, see the [guide article on Database Access Objects](guide:db-dao).
 *
 * @property string $rawSql The raw SQL with parameter values inserted into the corresponding placeholders in
 * [[sql]].
 * @property string $sql The SQL statement to be executed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Command extends Component
{
    /**
     * @var Connection the DB connection that this command is associated with
     */
    public $db;
    /**
     * @var \PDOStatement the PDOStatement object that this command is associated with
     */
    public $pdoStatement;
    /**
     * @var int the default fetch mode for this command.
     * @see https://www.php.net/manual/en/pdostatement.setfetchmode.php
     */
    public $fetchMode = \PDO::FETCH_ASSOC;
    /**
     * @var array the parameters (name => value) that are bound to the current PDO statement.
     * This property is maintained by methods such as [[bindValue()]]. It is mainly provided for logging purpose
     * and is used to generate [[rawSql]]. Do not modify it directly.
     */
    public $params = [];
    /**
     * @var int the default number of seconds that query results can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire. And use a negative number to indicate
     * query cache should not be used.
     * @see cache()
     */
    public $queryCacheDuration;
    /**
     * @var \yii\caching\Dependency the dependency to be associated with the cached query result for this command
     * @see cache()
     */
    public $queryCacheDependency;

    /**
     * @var array pending parameters to be bound to the current PDO statement.
     * @since 2.0.33
     */
    protected $pendingParams = [];

    /**
     * @var string the SQL statement that this command represents
     */
    private $_sql;
    /**
     * @var string name of the table, which schema, should be refreshed after command execution.
     */
    private $_refreshTableName;
    /**
     * @var string|null|false the isolation level to use for this transaction.
     * See [[Transaction::begin()]] for details.
     */
    private $_isolationLevel = false;
    /**
     * @var callable a callable (e.g. anonymous function) that is called when [[\yii\db\Exception]] is thrown
     * when executing the command.
     */
    private $_retryHandler;


    /**
     * Enables query cache for this command.
     * @param int|null $duration the number of seconds that query result of this command can remain valid in the cache.
     * If this is not set, the value of [[Connection::queryCacheDuration]] will be used instead.
     * Use 0 to indicate that the cached data will never expire.
     * @param \yii\caching\Dependency|null $dependency the cache dependency associated with the cached query result.
     * @return $this the command object itself
     */
    public function cache($duration = null, $dependency = null)
    {
        $this->queryCacheDuration = $duration === null ? $this->db->queryCacheDuration : $duration;
        $this->queryCacheDependency = $dependency;
        return $this;
    }

    /**
     * Disables query cache for this command.
     * @return $this the command object itself
     */
    public function noCache()
    {
        $this->queryCacheDuration = -1;
        return $this;
    }

    /**
     * Returns the SQL statement for this command.
     * @return string the SQL statement to be executed
     */
    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * Specifies the SQL statement to be executed. The SQL statement will be quoted using [[Connection::quoteSql()]].
     * The previous SQL (if any) will be discarded, and [[params]] will be cleared as well. See [[reset()]]
     * for details.
     *
     * @param string $sql the SQL statement to be set.
     * @return $this this command instance
     * @see reset()
     * @see cancel()
     */
    public function setSql($sql)
    {
        if ($sql !== $this->_sql) {
            $this->cancel();
            $this->reset();
            $this->_sql = $this->db->quoteSql($sql);
        }

        return $this;
    }

    /**
     * Specifies the SQL statement to be executed. The SQL statement will not be modified in any way.
     * The previous SQL (if any) will be discarded, and [[params]] will be cleared as well. See [[reset()]]
     * for details.
     *
     * @param string $sql the SQL statement to be set.
     * @return $this this command instance
     * @since 2.0.13
     * @see reset()
     * @see cancel()
     */
    public function setRawSql($sql)
    {
        if ($sql !== $this->_sql) {
            $this->cancel();
            $this->reset();
            $this->_sql = $sql;
        }

        return $this;
    }

    /**
     * Returns the raw SQL by inserting parameter values into the corresponding placeholders in [[sql]].
     * Note that the return value of this method should mainly be used for logging purpose.
     * It is likely that this method returns an invalid SQL due to improper replacement of parameter placeholders.
     * @return string the raw SQL with parameter values inserted into the corresponding placeholders in [[sql]].
     */
    public function getRawSql()
    {
        if (empty($this->params)) {
            return $this->_sql;
        }
        $params = [];
        foreach ($this->params as $name => $value) {
            if (is_string($name) && strncmp(':', $name, 1)) {
                $name = ':' . $name;
            }
            if (is_string($value) || $value instanceof Expression) {
                $params[$name] = $this->db->quoteValue((string)$value);
            } elseif (is_bool($value)) {
                $params[$name] = ($value ? 'TRUE' : 'FALSE');
            } elseif ($value === null) {
                $params[$name] = 'NULL';
            } elseif (!is_object($value) && !is_resource($value)) {
                $params[$name] = $value;
            }
        }
        if (!isset($params[1])) {
            return preg_replace_callback('#(:\w+)#', function($matches) use ($params) {
                $m = $matches[1];
                return isset($params[$m]) ? $params[$m] : $m;
            }, $this->_sql);
        }
        $sql = '';
        foreach (explode('?', $this->_sql) as $i => $part) {
            $sql .= (isset($params[$i]) ? $params[$i] : '') . $part;
        }

        return $sql;
    }

    /**
     * Prepares the SQL statement to be executed.
     * For complex SQL statement that is to be executed multiple times,
     * this may improve performance.
     * For SQL statement with binding parameters, this method is invoked
     * automatically.
     * @param bool|null $forRead whether this method is called for a read query. If null, it means
     * the SQL statement should be used to determine whether it is for read or write.
     * @throws Exception if there is any DB error
     */
    public function prepare($forRead = null)
    {
        if ($this->pdoStatement) {
            $this->bindPendingParams();
            return;
        }

        $sql = $this->getSql();
        if ($sql === '') {
            return;
        }

        if ($this->db->getTransaction()) {
            // master is in a transaction. use the same connection.
            $forRead = false;
        }
        if ($forRead || $forRead === null && $this->db->getSchema()->isReadQuery($sql)) {
            $pdo = $this->db->getSlavePdo(true);
        } else {
            $pdo = $this->db->getMasterPdo();
        }

        try {
            $this->pdoStatement = $pdo->prepare($sql);
            $this->bindPendingParams();
        } catch (\Exception $e) {
            $message = $e->getMessage() . "\nFailed to prepare SQL: $sql";
            $errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
            throw new Exception($message, $errorInfo, $e->getCode(), $e);
        } catch (\Throwable $e) {
            $message = $e->getMessage() . "\nFailed to prepare SQL: $sql";
            throw new Exception($message, null, $e->getCode(), $e);
        }
    }

    /**
     * Cancels the execution of the SQL statement.
     * This method mainly sets [[pdoStatement]] to be null.
     */
    public function cancel()
    {
        $this->pdoStatement = null;
    }

    /**
     * Binds a parameter to the SQL statement to be executed.
     * @param string|int $name parameter identifier. For a prepared statement
     * using named placeholders, this will be a parameter name of
     * the form `:name`. For a prepared statement using question mark
     * placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $value the PHP variable to bind to the SQL statement parameter (passed by reference)
     * @param int|null $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
     * @param int|null $length length of the data type
     * @param mixed $driverOptions the driver-specific options
     * @return $this the current command being executed
     * @see https://www.php.net/manual/en/function.PDOStatement-bindParam.php
     */
    public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null)
    {
        $this->prepare();

        if ($dataType === null) {
            $dataType = $this->db->getSchema()->getPdoType($value);
        }
        if ($length === null) {
            $this->pdoStatement->bindParam($name, $value, $dataType);
        } elseif ($driverOptions === null) {
            $this->pdoStatement->bindParam($name, $value, $dataType, $length);
        } else {
            $this->pdoStatement->bindParam($name, $value, $dataType, $length, $driverOptions);
        }
        $this->params[$name] = &$value;

        return $this;
    }

    /**
     * Binds pending parameters that were registered via [[bindValue()]] and [[bindValues()]].
     * Note that this method requires an active [[pdoStatement]].
     */
    protected function bindPendingParams()
    {
        foreach ($this->pendingParams as $name => $value) {
            $this->pdoStatement->bindValue($name, $value[0], $value[1]);
        }
        $this->pendingParams = [];
    }

    /**
     * Binds a value to a parameter.
     * @param string|int $name Parameter identifier. For a prepared statement
     * using named placeholders, this will be a parameter name of
     * the form `:name`. For a prepared statement using question mark
     * placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $value The value to bind to the parameter
     * @param int|null $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
     * @return $this the current command being executed
     * @see https://www.php.net/manual/en/function.PDOStatement-bindValue.php
     */
    public function bindValue($name, $value, $dataType = null)
    {
        if ($dataType === null) {
            $dataType = $this->db->getSchema()->getPdoType($value);
        }
        $this->pendingParams[$name] = [$value, $dataType];
        $this->params[$name] = $value;

        return $this;
    }

    /**
     * Binds a list of values to the corresponding parameters.
     * This is similar to [[bindValue()]] except that it binds multiple values at a time.
     * Note that the SQL data type of each value is determined by its PHP type.
     * @param array $values the values to be bound. This must be given in terms of an associative
     * array with array keys being the parameter names, and array values the corresponding parameter values,
     * e.g. `[':name' => 'John', ':age' => 25]`. By default, the PDO type of each value is determined
     * by its PHP type. You may explicitly specify the PDO type by using a [[yii\db\PdoValue]] class: `new PdoValue(value, type)`,
     * e.g. `[':name' => 'John', ':profile' => new PdoValue($profile, \PDO::PARAM_LOB)]`.
     * @return $this the current command being executed
     */
    public function bindValues($values)
    {
        if (empty($values)) {
            return $this;
        }

        $schema = $this->db->getSchema();
        foreach ($values as $name => $value) {
            if (is_array($value)) { // TODO: Drop in Yii 2.1
                $this->pendingParams[$name] = $value;
                $this->params[$name] = $value[0];
            } elseif ($value instanceof PdoValue) {
                $this->pendingParams[$name] = [$value->getValue(), $value->getType()];
                $this->params[$name] = $value->getValue();
            } else {
                if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
                    if ($value instanceof \BackedEnum) {
                        $value = $value->value;
                    } elseif ($value instanceof \UnitEnum) {
                        $value = $value->name;
                    }
                }
                $type = $schema->getPdoType($value);
                $this->pendingParams[$name] = [$value, $type];
                $this->params[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * Executes the SQL statement and returns query result.
     * This method is for executing a SQL query that returns result set, such as `SELECT`.
     * @return DataReader the reader object for fetching the query result
     * @throws Exception execution failed
     */
    public function query()
    {
        return $this->queryInternal('');
    }

    /**
     * Executes the SQL statement and returns ALL rows at once.
     * @param int|null $fetchMode the result fetch mode. Please refer to [PHP manual](https://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array all rows of the query result. Each array element is an array representing a row of data.
     * An empty array is returned if the query results in nothing.
     * @throws Exception execution failed
     */
    public function queryAll($fetchMode = null)
    {
        return $this->queryInternal('fetchAll', $fetchMode);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     * This method is best used when only the first row of result is needed for a query.
     * @param int|null $fetchMode the result fetch mode. Please refer to [PHP manual](https://www.php.net/manual/en/pdostatement.setfetchmode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return array|false the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     * @throws Exception execution failed
     */
    public function queryOne($fetchMode = null)
    {
        return $this->queryInternal('fetch', $fetchMode);
    }

    /**
     * Executes the SQL statement and returns the value of the first column in the first row of data.
     * This method is best used when only a single value is needed for a query.
     * @return string|int|null|false the value of the first column in the first row of the query result.
     * False is returned if there is no value.
     * @throws Exception execution failed
     */
    public function queryScalar()
    {
        $result = $this->queryInternal('fetchColumn', 0);
        if (is_resource($result) && get_resource_type($result) === 'stream') {
            return stream_get_contents($result);
        }

        return $result;
    }

    /**
     * Executes the SQL statement and returns the first column of the result.
     * This method is best used when only the first column of result (i.e. the first element in each row)
     * is needed for a query.
     * @return array the first column of the query result. Empty array is returned if the query results in nothing.
     * @throws Exception execution failed
     */
    public function queryColumn()
    {
        return $this->queryInternal('fetchAll', \PDO::FETCH_COLUMN);
    }

    /**
     * Creates an INSERT command.
     *
     * For example,
     *
     * ```php
     * $connection->createCommand()->insert('user', [
     *     'name' => 'Sam',
     *     'age' => 30,
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and bind the values to be inserted.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array|\yii\db\Query $columns the column data (name => value) to be inserted into the table or instance
     * of [[yii\db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * Passing of [[yii\db\Query|Query]] is available since version 2.0.11.
     * @return $this the command object itself
     */
    public function insert($table, $columns)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a batch INSERT command.
     *
     * For example,
     *
     * ```php
     * $connection->createCommand()->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ])->execute();
     * ```
     *
     * The method will properly escape the column names, and quote the values to be inserted.
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * Also note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array|\Generator $rows the rows to be batch inserted into the table
     * @return $this the command object itself
     */
    public function batchInsert($table, $columns, $rows)
    {
        $table = $this->db->quoteSql($table);
        $columns = array_map(function ($column) {
            return $this->db->quoteSql($column);
        }, $columns);

        $params = [];
        $sql = $this->db->getQueryBuilder()->batchInsert($table, $columns, $rows, $params);

        $this->setRawSql($sql);
        $this->bindValues($params);

        return $this;
    }

    /**
     * Creates a command to insert rows into a database table if
     * they do not already exist (matching unique constraints),
     * or update them if they do.
     *
     * For example,
     *
     * ```php
     * $sql = $queryBuilder->upsert('pages', [
     *     'name' => 'Front page',
     *     'url' => 'https://example.com/', // url is unique
     *     'visits' => 0,
     * ], [
     *     'visits' => new \yii\db\Expression('visits + 1'),
     * ], $params);
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * @param string $table the table that new rows will be inserted into/updated in.
     * @param array|Query $insertColumns the column data (name => value) to be inserted into the table or instance
     * of [[Query]] to perform `INSERT INTO ... SELECT` SQL statement.
     * @param array|bool $updateColumns the column data (name => value) to be updated if they already exist.
     * If `true` is passed, the column data will be updated to match the insert column data.
     * If `false` is passed, no update will be performed if the column data already exists.
     * @param array $params the parameters to be bound to the command.
     * @return $this the command object itself.
     * @since 2.0.14
     */
    public function upsert($table, $insertColumns, $updateColumns = true, $params = [])
    {
        $sql = $this->db->getQueryBuilder()->upsert($table, $insertColumns, $updateColumns, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates an UPDATE command.
     *
     * For example,
     *
     * ```php
     * $connection->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();
     * ```
     *
     * or with using parameter binding for the condition:
     *
     * ```php
     * $minAge = 30;
     * $connection->createCommand()->update('user', ['status' => 1], 'age > :minAge', [':minAge' => $minAge])->execute();
     * ```
     *
     * The method will properly escape the column names and bind the values to be updated.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the parameters to be bound to the command
     * @return $this the command object itself
     */
    public function update($table, $columns, $condition = '', $params = [])
    {
        $sql = $this->db->getQueryBuilder()->update($table, $columns, $condition, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a DELETE command.
     *
     * For example,
     *
     * ```php
     * $connection->createCommand()->delete('user', 'status = 0')->execute();
     * ```
     *
     * or with using parameter binding for the condition:
     *
     * ```php
     * $status = 0;
     * $connection->createCommand()->delete('user', 'status = :status', [':status' => $status])->execute();
     * ```
     *
     * The method will properly escape the table and column names.
     *
     * Note that the created command is not executed until [[execute()]] is called.
     *
     * @param string $table the table where the data will be deleted from.
     * @param string|array $condition the condition that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify condition.
     * @param array $params the parameters to be bound to the command
     * @return $this the command object itself
     */
    public function delete($table, $condition = '', $params = [])
    {
        $sql = $this->db->getQueryBuilder()->delete($table, $condition, $params);

        return $this->setSql($sql)->bindValues($params);
    }

    /**
     * Creates a SQL command for creating a new DB table.
     *
     * The columns in the new table should be specified as name-definition pairs (e.g. 'name' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which must contain an abstract DB type.
     *
     * The method [[QueryBuilder::getColumnType()]] will be called
     * to convert the abstract column types to physical ones. For example, `string` will be converted
     * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * inserted into the generated SQL.
     *
     * Example usage:
     * ```php
     * Yii::$app->db->createCommand()->createTable('post', [
     *     'id' => 'pk',
     *     'title' => 'string',
     *     'text' => 'text',
     *     'column_name double precision null default null',
     * ]);
     * ```
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string|null $options additional SQL fragment that will be appended to the generated SQL.
     * @return $this the command object itself
     */
    public function createTable($table, $columns, $options = null)
    {
        $sql = $this->db->getQueryBuilder()->createTable($table, $columns, $options);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for renaming a DB table.
     * @param string $table the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function renameTable($table, $newName)
    {
        $sql = $this->db->getQueryBuilder()->renameTable($table, $newName);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function dropTable($table)
    {
        $sql = $this->db->getQueryBuilder()->dropTable($table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for truncating a DB table.
     * @param string $table the table to be truncated. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function truncateTable($table)
    {
        $sql = $this->db->getQueryBuilder()->truncateTable($table);

        return $this->setSql($sql);
    }

    /**
     * Creates a SQL command for adding a new DB column.
     * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the given column type to the physical one. For example, `string` will be converted
     * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
     * @return $this the command object itself
     */
    public function addColumn($table, $column, $type)
    {
        $sql = $this->db->getQueryBuilder()->addColumn($table, $column, $type);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping a DB column.
     * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
     * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function dropColumn($table, $column)
    {
        $sql = $this->db->getQueryBuilder()->dropColumn($table, $column);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $oldName the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function renameColumn($table, $oldName, $newName)
    {
        $sql = $this->db->getQueryBuilder()->renameColumn($table, $oldName, $newName);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
     * @return $this the command object itself
     */
    public function alterColumn($table, $column, $type)
    {
        $sql = $this->db->getQueryBuilder()->alterColumn($table, $column, $type);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for adding a primary key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the primary key constraint.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     * @return $this the command object itself.
     */
    public function addPrimaryKey($name, $table, $columns)
    {
        $sql = $this->db->getQueryBuilder()->addPrimaryKey($name, $table, $columns);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for removing a primary key constraint to an existing table.
     * @param string $name the name of the primary key constraint to be removed.
     * @param string $table the table that the primary key constraint will be removed from.
     * @return $this the command object itself
     */
    public function dropPrimaryKey($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropPrimaryKey($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
     * @param string|null $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string|null $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @return $this the command object itself
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $sql = $this->db->getQueryBuilder()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping a foreign key constraint.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function dropForeignKey($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropForeignKey($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas. The column names will be properly quoted by the method.
     * @param bool $unique whether to add UNIQUE constraint on the created index.
     * @return $this the command object itself
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        $sql = $this->db->getQueryBuilder()->createIndex($name, $table, $columns, $unique);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return $this the command object itself
     */
    public function dropIndex($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropIndex($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for adding an unique constraint to an existing table.
     * @param string $name the name of the unique constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the unique constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string|array $columns the name of the column to that the constraint will be added on.
     * If there are multiple columns, separate them with commas.
     * The name will be properly quoted by the method.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function addUnique($name, $table, $columns)
    {
        $sql = $this->db->getQueryBuilder()->addUnique($name, $table, $columns);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping an unique constraint.
     * @param string $name the name of the unique constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose unique constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function dropUnique($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropUnique($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for adding a check constraint to an existing table.
     * @param string $name the name of the check constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the check constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string $expression the SQL of the `CHECK` constraint.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function addCheck($name, $table, $expression)
    {
        $sql = $this->db->getQueryBuilder()->addCheck($name, $table, $expression);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping a check constraint.
     * @param string $name the name of the check constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose check constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function dropCheck($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropCheck($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for adding a default value constraint to an existing table.
     * @param string $name the name of the default value constraint.
     * The name will be properly quoted by the method.
     * @param string $table the table that the default value constraint will be added to.
     * The name will be properly quoted by the method.
     * @param string $column the name of the column to that the constraint will be added on.
     * The name will be properly quoted by the method.
     * @param mixed $value default value.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function addDefaultValue($name, $table, $column, $value)
    {
        $sql = $this->db->getQueryBuilder()->addDefaultValue($name, $table, $column, $value);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for dropping a default value constraint.
     * @param string $name the name of the default value constraint to be dropped.
     * The name will be properly quoted by the method.
     * @param string $table the table whose default value constraint is to be dropped.
     * The name will be properly quoted by the method.
     * @return $this the command object itself.
     * @since 2.0.13
     */
    public function dropDefaultValue($name, $table)
    {
        $sql = $this->db->getQueryBuilder()->dropDefaultValue($name, $table);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Creates a SQL command for resetting the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or the maximum existing value +1.
     * @param string $table the name of the table whose primary key sequence will be reset
     * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have the maximum existing value +1.
     * @return $this the command object itself
     * @throws NotSupportedException if this is not supported by the underlying DBMS
     */
    public function resetSequence($table, $value = null)
    {
        $sql = $this->db->getQueryBuilder()->resetSequence($table, $value);

        return $this->setSql($sql);
    }

    /**
     * Executes a db command resetting the sequence value of a table's primary key.
     * Reason for execute is that some databases (Oracle) need several queries to do so.
     * The sequence is reset such that the primary key of the next new row inserted
     * will have the specified value or the maximum existing value +1.
     * @param string $table the name of the table whose primary key sequence is reset
     * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have the maximum existing value +1.
     * @throws NotSupportedException if this is not supported by the underlying DBMS
     * @since 2.0.16
     */
    public function executeResetSequence($table, $value = null)
    {
        return $this->db->getQueryBuilder()->executeResetSequence($table, $value);
    }

    /**
     * Builds a SQL command for enabling or disabling integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     * @param string $schema the schema name of the tables. Defaults to empty string, meaning the current
     * or default schema.
     * @param string $table the table name.
     * @return $this the command object itself
     * @throws NotSupportedException if this is not supported by the underlying DBMS
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $sql = $this->db->getQueryBuilder()->checkIntegrity($check, $schema, $table);

        return $this->setSql($sql);
    }

    /**
     * Builds a SQL command for adding comment to column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @return $this the command object itself
     * @since 2.0.8
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        $sql = $this->db->getQueryBuilder()->addCommentOnColumn($table, $column, $comment);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Builds a SQL command for adding comment to table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @return $this the command object itself
     * @since 2.0.8
     */
    public function addCommentOnTable($table, $comment)
    {
        $sql = $this->db->getQueryBuilder()->addCommentOnTable($table, $comment);

        return $this->setSql($sql);
    }

    /**
     * Builds a SQL command for dropping comment from column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @return $this the command object itself
     * @since 2.0.8
     */
    public function dropCommentFromColumn($table, $column)
    {
        $sql = $this->db->getQueryBuilder()->dropCommentFromColumn($table, $column);

        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }

    /**
     * Builds a SQL command for dropping comment from table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @return $this the command object itself
     * @since 2.0.8
     */
    public function dropCommentFromTable($table)
    {
        $sql = $this->db->getQueryBuilder()->dropCommentFromTable($table);

        return $this->setSql($sql);
    }

    /**
     * Creates a SQL View.
     *
     * @param string $viewName the name of the view to be created.
     * @param string|Query $subquery the select statement which defines the view.
     * This can be either a string or a [[Query]] object.
     * @return $this the command object itself.
     * @since 2.0.14
     */
    public function createView($viewName, $subquery)
    {
        $sql = $this->db->getQueryBuilder()->createView($viewName, $subquery);

        return $this->setSql($sql)->requireTableSchemaRefresh($viewName);
    }

    /**
     * Drops a SQL View.
     *
     * @param string $viewName the name of the view to be dropped.
     * @return $this the command object itself.
     * @since 2.0.14
     */
    public function dropView($viewName)
    {
        $sql = $this->db->getQueryBuilder()->dropView($viewName);

        return $this->setSql($sql)->requireTableSchemaRefresh($viewName);
    }

    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return int number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function execute()
    {
        $sql = $this->getSql();
        list($profile, $rawSql) = $this->logQuery(__METHOD__);

        if ($sql == '') {
            return 0;
        }

        $this->prepare(false);

        try {
            $profile and Yii::beginProfile($rawSql, __METHOD__);

            $this->internalExecute($rawSql);
            $n = $this->pdoStatement->rowCount();

            $profile and Yii::endProfile($rawSql, __METHOD__);

            $this->refreshTableSchema();

            return $n;
        } catch (Exception $e) {
            $profile and Yii::endProfile($rawSql, __METHOD__);
            throw $e;
        }
    }

    /**
     * Logs the current database query if query logging is enabled and returns
     * the profiling token if profiling is enabled.
     * @param string $category the log category.
     * @return array array of two elements, the first is boolean of whether profiling is enabled or not.
     * The second is the rawSql if it has been created.
     */
    protected function logQuery($category)
    {
        if ($this->db->enableLogging) {
            $rawSql = $this->getRawSql();
            Yii::info($rawSql, $category);
        }
        if (!$this->db->enableProfiling) {
            return [false, isset($rawSql) ? $rawSql : null];
        }

        return [true, isset($rawSql) ? $rawSql : $this->getRawSql()];
    }

    /**
     * Performs the actual DB query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param int|null $fetchMode the result fetch mode. Please refer to [PHP manual](https://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws Exception if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        list($profile, $rawSql) = $this->logQuery('yii\db\Command::query');

        if ($method !== '') {
            $info = $this->db->getQueryCacheInfo($this->queryCacheDuration, $this->queryCacheDependency);
            if (is_array($info)) {
                /* @var $cache \yii\caching\CacheInterface */
                $cache = $info[0];
                $cacheKey = $this->getCacheKey($method, $fetchMode, '');
                $result = $cache->get($cacheKey);
                if (is_array($result) && array_key_exists(0, $result)) {
                    Yii::debug('Query result served from cache', 'yii\db\Command::query');
                    return $result[0];
                }
            }
        }

        $this->prepare(true);

        try {
            $profile and Yii::beginProfile($rawSql, 'yii\db\Command::query');

            $this->internalExecute($rawSql);

            if ($method === '') {
                $result = new DataReader($this);
            } else {
                if ($fetchMode === null) {
                    $fetchMode = $this->fetchMode;
                }
                $result = call_user_func_array([$this->pdoStatement, $method], (array) $fetchMode);
                $this->pdoStatement->closeCursor();
            }

            $profile and Yii::endProfile($rawSql, 'yii\db\Command::query');
        } catch (Exception $e) {
            $profile and Yii::endProfile($rawSql, 'yii\db\Command::query');
            throw $e;
        }

        if (isset($cache, $cacheKey, $info)) {
            $cache->set($cacheKey, [$result], $info[1], $info[2]);
            Yii::debug('Saved query result in cache', 'yii\db\Command::query');
        }

        return $result;
    }

    /**
     * Returns the cache key for the query.
     *
     * @param string $method method of PDOStatement to be called
     * @param int $fetchMode the result fetch mode. Please refer to [PHP manual](https://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes.
     * @return array the cache key
     * @since 2.0.16
     */
    protected function getCacheKey($method, $fetchMode, $rawSql)
    {
        $params = $this->params;
        ksort($params);
        return [
            __CLASS__,
            $method,
            $fetchMode,
            $this->db->dsn,
            $this->db->username,
            $this->getSql(),
            json_encode($params),
        ];
    }

    /**
     * Marks a specified table schema to be refreshed after command execution.
     * @param string $name name of the table, which schema should be refreshed.
     * @return $this this command instance
     * @since 2.0.6
     */
    protected function requireTableSchemaRefresh($name)
    {
        $this->_refreshTableName = $name;
        return $this;
    }

    /**
     * Refreshes table schema, which was marked by [[requireTableSchemaRefresh()]].
     * @since 2.0.6
     */
    protected function refreshTableSchema()
    {
        if ($this->_refreshTableName !== null) {
            $this->db->getSchema()->refreshTableSchema($this->_refreshTableName);
        }
    }

    /**
     * Marks the command to be executed in transaction.
     * @param string|null $isolationLevel The isolation level to use for this transaction.
     * See [[Transaction::begin()]] for details.
     * @return $this this command instance.
     * @since 2.0.14
     */
    protected function requireTransaction($isolationLevel = null)
    {
        $this->_isolationLevel = $isolationLevel;
        return $this;
    }

    /**
     * Sets a callable (e.g. anonymous function) that is called when [[Exception]] is thrown
     * when executing the command. The signature of the callable should be:
     *
     * ```php
     * function (\yii\db\Exception $e, $attempt)
     * {
     *     // return true or false (whether to retry the command or rethrow $e)
     * }
     * ```
     *
     * The callable will recieve a database exception thrown and a current attempt
     * (to execute the command) number starting from 1.
     *
     * @param callable $handler a PHP callback to handle database exceptions.
     * @return $this this command instance.
     * @since 2.0.14
     */
    protected function setRetryHandler(callable $handler)
    {
        $this->_retryHandler = $handler;
        return $this;
    }

    /**
     * Executes a prepared statement.
     *
     * It's a wrapper around [[\PDOStatement::execute()]] to support transactions
     * and retry handlers.
     *
     * @param string|null $rawSql the rawSql if it has been created.
     * @throws Exception if execution failed.
     * @since 2.0.14
     */
    protected function internalExecute($rawSql)
    {
        $attempt = 0;
        while (true) {
            try {
                if (
                    ++$attempt === 1
                    && $this->_isolationLevel !== false
                    && $this->db->getTransaction() === null
                ) {
                    $this->db->transaction(function () use ($rawSql) {
                        $this->internalExecute($rawSql);
                    }, $this->_isolationLevel);
                } else {
                    $this->pdoStatement->execute();
                }
                break;
            } catch (\Exception $e) {
                $rawSql = $rawSql ?: $this->getRawSql();
                $e = $this->db->getSchema()->convertException($e, $rawSql);
                if ($this->_retryHandler === null || !call_user_func($this->_retryHandler, $e, $attempt)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Resets command properties to their initial state.
     *
     * @since 2.0.13
     */
    protected function reset()
    {
        $this->_sql = null;
        $this->pendingParams = [];
        $this->params = [];
        $this->_refreshTableName = null;
        $this->_isolationLevel = false;
    }
}
