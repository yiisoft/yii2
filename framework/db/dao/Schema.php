<?php
/**
 * Schema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

use yii\db\Exception;

/**
 * Schema is the base class for retrieving metadata information.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Schema extends \yii\base\Object
{
	public $connection;

	private $_tableNames = array();
	private $_tables = array();
	private $_builder;

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema driver dependent table metadata, null if the table does not exist.
	 */
	abstract protected function loadTableSchema($name);

	/**
	 * Constructor.
	 * @param CDbConnection $conn database connection.
	 */
	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Obtains the metadata for the named table.
	 * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
	 * @return CDbTableSchema table metadata. Null if the named table does not exist.
	 */
	public function getTableSchema($name)
	{
		if (isset($this->_tables[$name])) {
			return $this->_tables[$name];
		}

		if (strpos($name, '{{') !== false) {
			$realName = preg_replace('/\{\{(.*?)\}\}/', $this->connection->tablePrefix . '$1', $name);
		}
		else {
			$realName = $name;
		}

		$db = $this->connection;

		// temporarily disable query caching
		if ($db->queryCachingDuration >= 0) {
			$qcDuration = $db->queryCachingDuration;
			$db->queryCachingDuration = -1;
		}

		if (!in_array($name, $db->schemaCachingExclude) && $db->schemaCachingDuration >= 0 && ($cache = \Yii::app()->getComponent($db->schemaCacheID)) !== null) {
			$key = __CLASS__ . "/{$db->dsn}/{$db->username}/{$name}";
			if (($table = $cache->get($key)) === false) {
				$table = $this->loadTableSchema($realName);
				if ($table !== null) {
					$cache->set($key, $table, $db->schemaCachingDuration);
				}
			}
			$this->_tables[$name] = $table;
		}
		else {
			$this->_tables[$name] = $table = $this->loadTableSchema($realName);
		}

		if (isset($qcDuration)) { // re-enable query caching
			$db->queryCachingDuration = $qcDuration;
		}

		return $table;
	}

	/**
	 * Returns the metadata for all tables in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return array the metadata for all tables in the database.
	 * Each array element is an instance of {@link CDbTableSchema} (or its child class).
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
	 * @return array all table names in the database.
	 */
	public function getTableNames($schema = '')
	{
		if (!isset($this->_tableNames[$schema])) {
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
	 * This method resets the loaded table metadata and command builder
	 * so that they can be recreated to reflect the change of schema.
	 */
	public function refresh()
	{
		$db = $this->connection;
		if ($db->schemaCachingDuration >= 0 && ($cache = \Yii::app()->getComponent($db->schemaCacheID)) !== null) {
			foreach ($this->_tables as $name => $table) {
				$key = __CLASS__ . ":{$db->dsn}/{$db->username}/{$name}";
				$cache->delete($key);
			}
		}
		$this->_tables = array();
		$this->_tableNames = array();
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
		}
		else
			$prefix = '';
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
		return new QueryBuilder($this);
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
