<?php
/**
 * CDbSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbSchema is the base class for retrieving metadata information.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbSchema.php 3359 2011-07-18 11:25:17Z qiang.xue $
 * @package system.db.schema
 * @since 1.0
 */
abstract class CDbSchema extends CComponent
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
	public function __construct($conn)
	{
		$this->_connection = $conn;
		foreach ($conn->schemaCachingExclude as $name)
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

	/**
	 * Builds a SQL statement for creating a new DB table.
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name'=>'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The {@link getColumnType} method will be invoked to convert any abstract type into a physical one.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name=>definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return string the SQL statement for creating a new DB table.
	 * @since 1.1.6
	 */
	public function createTable($table, $columns, $options = null)
	{
		$cols = array();
		foreach ($columns as $name => $type)
		{
			if (is_string($name))
				$cols[] = "\t" . $this->quoteColumnName($name) . ' ' . $this->getColumnType($type);
			else
				$cols[] = "\t" . $type;
		}
		$sql = "CREATE TABLE " . $this->quoteTableName($table) . " (\n" . implode(",\n", $cols) . "\n)";
		return $options === null ? $sql : $sql . ' ' . $options;
	}

	/**
	 * Builds a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB table.
	 * @since 1.1.6
	 */
	public function renameTable($table, $newName)
	{
		return 'RENAME TABLE ' . $this->quoteTableName($table) . ' TO ' . $this->quoteTableName($newName);
	}

	/**
	 * Builds a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB table.
	 * @since 1.1.6
	 */
	public function dropTable($table)
	{
		return "DROP TABLE " . $this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return string the SQL statement for truncating a DB table.
	 * @since 1.1.6
	 */
	public function truncateTable($table)
	{
		return "TRUNCATE TABLE " . $this->quoteTableName($table);
	}

	/**
	 * Builds a SQL statement for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for adding a new column.
	 * @since 1.1.6
	 */
	public function addColumn($table, $column, $type)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' ADD ' . $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
	}

	/**
	 * Builds a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a DB column.
	 * @since 1.1.6
	 */
	public function dropColumn($table, $column)
	{
		return "ALTER TABLE " . $this->quoteTableName($table)
			. " DROP COLUMN " . $this->quoteColumnName($column);
	}

	/**
	 * Builds a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return string the SQL statement for renaming a DB column.
	 * @since 1.1.6
	 */
	public function renameColumn($table, $name, $newName)
	{
		return "ALTER TABLE " . $this->quoteTableName($table)
			. " RENAME COLUMN " . $this->quoteColumnName($name)
			. " TO " . $this->quoteColumnName($newName);
	}

	/**
	 * Builds a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
	 * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
	 * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
	 * @return string the SQL statement for changing the definition of a column.
	 * @since 1.1.6
	 */
	public function alterColumn($table, $column, $type)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table) . ' CHANGE '
			. $this->quoteColumnName($column) . ' '
			. $this->quoteColumnName($column) . ' '
			. $this->getColumnType($type);
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return string the SQL statement for adding a foreign key constraint to an existing table.
	 * @since 1.1.6
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($columns as $i => $col)
			$columns[$i] = $this->quoteColumnName($col);
		$refColumns = preg_split('/\s*,\s*/', $refColumns, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($refColumns as $i => $col)
			$refColumns[$i] = $this->quoteColumnName($col);
		$sql = 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' ADD CONSTRAINT ' . $this->quoteColumnName($name)
			. ' FOREIGN KEY (' . implode(', ', $columns) . ')'
			. ' REFERENCES ' . $this->quoteTableName($refTable)
			. ' (' . implode(', ', $refColumns) . ')';
		if ($delete !== null)
			$sql .= ' ON DELETE ' . $delete;
		if ($update !== null)
			$sql .= ' ON UPDATE ' . $update;
		return $sql;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping a foreign key constraint.
	 * @since 1.1.6
	 */
	public function dropForeignKey($name, $table)
	{
		return 'ALTER TABLE ' . $this->quoteTableName($table)
			. ' DROP CONSTRAINT ' . $this->quoteColumnName($name);
	}

	/**
	 * Builds a SQL statement for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $column the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. Each column name will be properly quoted by the method, unless a parenthesis is found in the name.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 * @return string the SQL statement for creating a new index.
	 * @since 1.1.6
	 */
	public function createIndex($name, $table, $column, $unique = false)
	{
		$cols = array();
		$columns = preg_split('/\s*,\s*/', $column, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($columns as $col)
		{
			if (strpos($col, '(') !== false)
				$cols[] = $col;
			else
				$cols[] = $this->quoteColumnName($col);
		}
		return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ')
			. $this->quoteTableName($name) . ' ON '
			. $this->quoteTableName($table) . ' (' . implode(', ', $cols) . ')';
	}

	/**
	 * Builds a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return string the SQL statement for dropping an index.
	 * @since 1.1.6
	 */
	public function dropIndex($name, $table)
	{
		return 'DROP INDEX ' . $this->quoteTableName($name) . ' ON ' . $this->quoteTableName($table);
	}
}
