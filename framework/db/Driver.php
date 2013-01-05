<?php
/**
 * Driver class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\db\Exception;

/**
 * Driver is the base class for all database driver classes.
 *
 * Driver implements the DBMS-specific methods to support retrieving metadata of a database.
 *
 * @property QueryBuilder $queryBuilder the query builder for this connection.
 * @property array $tableNames the names of all tables in this database.
 * @property array $tableSchemas the metadata for all tables in this database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Driver extends \yii\base\Object
{
	/**
	 * The followings are the supported abstract column data types.
	 */
	const TYPE_PK = 'pk';
	const TYPE_STRING = 'string';
	const TYPE_TEXT = 'text';
	const TYPE_SMALLINT = 'smallint';
	const TYPE_INTEGER = 'integer';
	const TYPE_BIGINT = 'bigint';
	const TYPE_FLOAT = 'float';
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
	public $connection;
	/**
	 * @var array list of ALL table names in the database
	 */
	private $_tableNames = array();
	/**
	 * @var array list of loaded table metadata (table name => TableSchema)
	 */
	private $_tables = array();
	/**
	 * @var QueryBuilder the query builder for this database
	 */
	private $_builder;

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema DBMS-dependent table metadata, null if the table does not exist.
	 */
	abstract protected function loadTableSchema($name);

	/**
	 * Constructor.
	 * @param Connection $connection database connection.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($connection, $config = array())
	{
		$this->connection = $connection;
		parent::__construct($config);
	}

	/**
	 * Obtains the metadata for the named table.
	 * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return TableSchema table metadata. Null if the named table does not exist.
	 */
	public function getTableSchema($name, $refresh = false)
	{
		if (isset($this->_tables[$name]) && !$refresh) {
			return $this->_tables[$name];
		}

		$db = $this->connection;

		$realName = $db->expandTablePrefix($name);

		// temporarily disable query caching
		if ($db->queryCachingDuration >= 0) {
			$qcDuration = $db->queryCachingDuration;
			$db->queryCachingDuration = -1;
		}

		if (!in_array($name, $db->schemaCachingExclude, true) && $db->schemaCachingDuration >= 0 && ($cache = \Yii::$application->getComponent($db->schemaCacheID)) !== null) {
			$key = $this->getCacheKey($name);
			if ($refresh || ($table = $cache->get($key)) === false) {
				$table = $this->loadTableSchema($realName);
				if ($table !== null) {
					$cache->set($key, $table, $db->schemaCachingDuration);
				}
			}
			$this->_tables[$name] = $table;
		} else {
			$this->_tables[$name] = $table = $this->loadTableSchema($realName);
		}

		if (isset($qcDuration)) { // re-enable query caching
			$db->queryCachingDuration = $qcDuration;
		}

		return $table;
	}

	/**
	 * Returns the cache key for the specified table name.
	 * @param string $name the table name
	 * @return string the cache key
	 */
	public function getCacheKey($name)
	{
		return  __CLASS__ . "/{$this->connection->dsn}/{$this->connection->username}/{$name}";
	}

	/**
	 * Returns the metadata for all tables in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return array the metadata for all tables in the database.
	 * Each array element is an instance of [[TableSchema]] (or its child class).
	 */
	public function getTableSchemas($schema = '')
	{
		$tables = array();
		foreach ($this->getTableNames($schema) as $name) {
			if (($table = $this->getTableSchema($name)) !== null) {
				$tables[] = $table;
			}
		}
		return $tables;
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @param boolean $refresh whether to fetch the latest available table names. If this is false,
	 * table names fetched previously (if available) will be returned.
	 * @return array all table names in the database.
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
	 * Refreshes the schema.
	 * This method cleans up the cached table schema and names
	 * so that they can be recreated to reflect the database schema change.
	 * @param string $tableName the name of the table that needs to be refreshed.
	 * If null, all currently loaded tables will be refreshed.
	 */
	public function refresh($tableName = null)
	{
		$db = $this->connection;
		if ($db->schemaCachingDuration >= 0 && ($cache = \Yii::$application->getComponent($db->schemaCacheID)) !== null) {
			if ($tableName === null) {
				foreach ($this->_tables as $name => $table) {
					$cache->delete($this->getCacheKey($name));
				}
				$this->_tables = array();
			} else {
				$cache->delete($this->getCacheKey($tableName));
				unset($this->_tables[$tableName]);
			}
		}
	}

	/**
	 * Quotes a table name for use in a query.
	 * If the table name contains schema prefix, the prefix will also be properly quoted.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 * @see quoteSimpleTableName
	 */
	public function quoteTableName($name)
	{
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
	 * Quotes a simple table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteSimpleTableName($name)
	{
		return strpos($name, "'") !== false ? $name : "'" . $name . "'";
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains prefix, the prefix will also be properly quoted.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @see quoteSimpleColumnName
	 */
	public function quoteColumnName($name)
	{
		if (($pos = strrpos($name, '.')) !== false) {
			$prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
			$name = substr($name, $pos + 1);
		} else {
			$prefix = '';
		}
		return $prefix . $this->quoteSimpleColumnName($name);
	}

	/**
	 * Quotes a simple column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteSimpleColumnName($name)
	{
		return strpos($name, '"') !== false || $name === '*' ? $name : '"' . $name . '"';
	}

	/**
	 * Creates a query builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific query builder.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->connection);
	}

	/**
	 * Returns all table names in the database.
	 * This method should be overridden by child classes in order to support this feature
	 * because the default implementation simply throws an exception.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema = '')
	{
		throw new Exception(get_class($this) . 'does not support fetching all table names.');
	}
}
