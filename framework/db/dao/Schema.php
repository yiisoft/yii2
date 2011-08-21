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

/**
 * Schema is the base class for retrieving metadata information.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Schema extends \yii\base\Component
{
	private $_tableNames = array();
	private $_tables = array();
	private $_connection;
	private $_builder;
	private $_cacheExclude = array();

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return CDbTableSchema driver dependent table metadata, null if the table does not exist.
	 */
	abstract protected function loadTable($name);

	/**
	 * Constructor.
	 * @param CDbConnection $conn database connection.
	 */
	public function __construct($connection)
	{
		$this->_connection = $connection;
		foreach ($connection->schemaCachingExclude as $name)
			$this->_cacheExclude[$name] = true;
	}

	/**
	 * @return Connection database connection. The connection is active.
	 */
	public function getConnection()
	{
		return $this->_connection;
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
			$realName = preg_replace('/\{\{(.*?)\}\}/', $this->_connection->tablePrefix . '$1', $name);
		}
		else {
			$realName = $name;
		}
		
		$db = $this->_connection;

		// temporarily disable query caching
		if ($db->queryCachingDuration >= 0) {
			$qcDuration = $db->queryCachingDuration;
			$db->queryCachingDuration = -1;
		}

		if (!in_array($name, $db->schemaCachingExclude) && $db->schemaCachingDuration >= 0 && ($cache = \Yii::app()->getComponent($db->schemaCacheID)) !== null) {
			$key = __CLASS__ . ":{$db->dsn}/{$db->username}/{$name}";
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
	 * The array keys are table names.
	 */
	public function getTables($schema = '')
	{
		$tables = array();
		foreach ($this->getTableNames($schema) as $name)
		{
			if (($table = $this->getTable($name)) !== null)
				$tables[$name] = $table;
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
		if (!isset($this->_tableNames[$schema]))
			$this->_tableNames[$schema] = $this->findTableNames($schema);
		return $this->_tableNames[$schema];
	}

	/**
	 * @return CDbCommandBuilder the SQL command builder for this connection.
	 */
	public function getCommandBuilder()
	{
		if ($this->_builder !== null)
			return $this->_builder;
		else
			return $this->_builder = $this->createCommandBuilder();
	}

	/**
	 * Refreshes the schema.
	 * This method resets the loaded table metadata and command builder
	 * so that they can be recreated to reflect the change of schema.
	 */
	public function refresh()
	{
		$db = $this->_connection;
		if ($db->schemaCachingDuration >= 0 && ($cache = \Yii::app()->getComponent($db->schemaCacheID)) !== null) {
			foreach ($this->_tables as $name => $table) {
				$key = __CLASS__ . ":{$db->dsn}/{$db->username}/{$name}";
				$cache->delete($key);
			}
		}
		$this->_tables = array();
		$this->_tableNames = array();
		$this->_builder = null;
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
		if (strpos($name, '.') === false)
			return $this->quoteSimpleTableName($name);
		$parts = explode('.', $name);
		foreach ($parts as $i => $part)
			$parts[$i] = $this->quoteSimpleTableName($part);
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
		return "'" . $name . "'";
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
		if (($pos = strrpos($name, '.')) !== false)
		{
			$prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
			$name = substr($name, $pos + 1);
		}
		else
			$prefix = '';
		return $prefix . ($name === '*' ? $name : $this->quoteSimpleColumnName($name));
	}

	/**
	 * Quotes a simple column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 * @since 1.1.6
	 */
	public function quoteSimpleColumnName($name)
	{
		return '"' . $name . '"';
	}

	/**
	 * Compares two table names.
	 * The table names can be either quoted or unquoted. This method
	 * will consider both cases.
	 * @param string $name1 table name 1
	 * @param string $name2 table name 2
	 * @return boolean whether the two table names refer to the same table.
	 */
	public function compareTableNames($name1, $name2)
	{
		$name1 = str_replace(array('"', '`', "'"), '', $name1);
		$name2 = str_replace(array('"', '`', "'"), '', $name2);
		if (($pos = strrpos($name1, '.')) !== false)
			$name1 = substr($name1, $pos + 1);
		if (($pos = strrpos($name2, '.')) !== false)
			$name2 = substr($name2, $pos + 1);
		if ($this->_connection->tablePrefix !== null)
		{
			if (strpos($name1, '{') !== false)
				$name1 = $this->_connection->tablePrefix . str_replace(array('{', '}'), '', $name1);
			if (strpos($name2, '{') !== false)
				$name2 = $this->_connection->tablePrefix . str_replace(array('{', '}'), '', $name2);
		}
		return $name1 === $name2;
	}

	/**
	 * Creates a command builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific command builder.
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createQueryBuilder()
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
