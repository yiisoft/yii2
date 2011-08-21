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
	/**
	 * @var array the abstract column types mapped to physical column types.
	 * @since 1.1.6
	 */
    public $columnTypes = array();

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
	 * @return CDbConnection database connection. The connection is active.
	 */
	public function getDbConnection()
	{
		return $this->_connection;
	}

	/**
	 * Obtains the metadata for the named table.
	 * @param string $name table name
	 * @return CDbTableSchema table metadata. Null if the named table does not exist.
	 */
	public function getTable($name)
	{
		if (isset($this->_tables[$name]))
			return $this->_tables[$name];
		else
		{
			if ($this->_connection->tablePrefix !== null && strpos($name, '{{') !== false)
				$realName = preg_replace('/\{\{(.*?)\}\}/', $this->_connection->tablePrefix . '$1', $name);
			else
				$realName = $name;

			// temporarily disable query caching
			if ($this->_connection->queryCachingDuration > 0)
			{
				$qcDuration = $this->_connection->queryCachingDuration;
				$this->_connection->queryCachingDuration = 0;
			}

			if (!isset($this->_cacheExclude[$name]) && ($duration = $this->_connection->schemaCachingDuration) > 0 && $this->_connection->schemaCacheID !== false && ($cache = Yii::app()->getComponent($this->_connection->schemaCacheID)) !== null)
			{
				$key = 'yii:dbschema' . $this->_connection->connectionString . ':' . $this->_connection->username . ':' . $name;
				if (($table = $cache->get($key)) === false)
				{
					$table = $this->loadTable($realName);
					if ($table !== null)
						$cache->set($key, $table, $duration);
				}
				$this->_tables[$name] = $table;
			}
			else
				$this->_tables[$name] = $table = $this->loadTable($realName);

			if (isset($qcDuration))  // re-enable query caching
				$this->_connection->queryCachingDuration = $qcDuration;

			return $table;
		}
	}

	/**
	 * Returns the metadata for all tables in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return array the metadata for all tables in the database.
	 * Each array element is an instance of {@link CDbTableSchema} (or its child class).
	 * The array keys are table names.
	 * @since 1.0.2
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
	 * @since 1.0.2
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
		if (($duration = $this->_connection->schemaCachingDuration) > 0 && $this->_connection->schemaCacheID !== false && ($cache = Yii::app()->getComponent($this->_connection->schemaCacheID)) !== null)
		{
			foreach (array_keys($this->_tables) as $name)
			{
				if (!isset($this->_cacheExclude[$name]))
				{
					$key = 'yii:dbschema' . $this->_connection->connectionString . ':' . $this->_connection->username . ':' . $name;
					$cache->delete($key);
				}
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
	 * @since 1.1.6
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
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1
	 */
	public function resetSequence($table, $value = null)
	{
	}

	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @since 1.1
	 */
	public function checkIntegrity($check = true, $schema = '')
	{
	}

	/**
	 * Creates a command builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific command builder.
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CDbCommandBuilder($this);
	}

	/**
	 * Returns all table names in the database.
	 * This method should be overridden by child classes in order to support this feature
	 * because the default implementation simply throws an exception.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 * @since 1.0.2
	 */
	protected function findTableNames($schema = '')
	{
		throw new CDbException(Yii::t('yii', '{class} does not support fetching all table names.',
			array('{class}' => get_class($this))));
	}

	/**
	 * Converts an abstract column type into a physical column type.
	 * The conversion is done using the type map specified in {@link columnTypes}.
	 * These abstract column types are supported (using MySQL as example to explain the corresponding
	 * physical types):
	 * <ul>
	 * <li>pk: an auto-incremental primary key type, will be converted into "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"</li>
	 * <li>string: string type, will be converted into "varchar(255)"</li>
	 * <li>text: a long string type, will be converted into "text"</li>
	 * <li>integer: integer type, will be converted into "int(11)"</li>
	 * <li>boolean: boolean type, will be converted into "tinyint(1)"</li>
	 * <li>float: float number type, will be converted into "float"</li>
	 * <li>decimal: decimal number type, will be converted into "decimal"</li>
	 * <li>datetime: datetime type, will be converted into "datetime"</li>
	 * <li>timestamp: timestamp type, will be converted into "timestamp"</li>
	 * <li>time: time type, will be converted into "time"</li>
	 * <li>date: date type, will be converted into "date"</li>
	 * <li>binary: binary data type, will be converted into "blob"</li>
	 * </ul>
	 *
	 * If the abstract type contains two or more parts separated by spaces (e.g. "string NOT NULL"), then only
	 * the first part will be converted, and the rest of the parts will be appended to the conversion result.
	 * For example, 'string NOT NULL' is converted to 'varchar(255) NOT NULL'.
	 * @param string $type abstract column type
	 * @return string physical column type.
	 * @since 1.1.6
	 */
    public function getColumnType($type)
    {
    	if (isset($this->columnTypes[$type]))
    		return $this->columnTypes[$type];
    	elseif (($pos = strpos($type, ' ')) !== false)
    	{
    		$t = substr($type, 0, $pos);
    		return (isset($this->columnTypes[$t]) ? $this->columnTypes[$t] : $t) . substr($type, $pos);
    	}
    	else
    		return $type;
    }

}
