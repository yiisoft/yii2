<?php
/**
 * Driver class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mysql;

use yii\db\TableSchema;
use yii\db\ColumnSchema;

/**
 * Driver is the class for retrieving metadata from a MySQL database (version 4.1.x and 5.x).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
	/**
	 * @var array mapping from physical column types (keys) to abstract column types (values)
	 */
	public $typeMap = array(
		'tinyint' => self::TYPE_SMALLINT,
		'bit' => self::TYPE_SMALLINT,
		'smallint' => self::TYPE_SMALLINT,
		'mediumint' => self::TYPE_INTEGER,
		'int' => self::TYPE_INTEGER,
		'integer' => self::TYPE_INTEGER,
		'bigint' => self::TYPE_BIGINT,
		'float' => self::TYPE_FLOAT,
		'double' => self::TYPE_FLOAT,
		'real' => self::TYPE_FLOAT,
		'decimal' => self::TYPE_DECIMAL,
		'numeric' => self::TYPE_DECIMAL,
		'tinytext' => self::TYPE_TEXT,
		'mediumtext' => self::TYPE_TEXT,
		'longtext' => self::TYPE_TEXT,
		'text' => self::TYPE_TEXT,
		'varchar' => self::TYPE_STRING,
		'string' => self::TYPE_STRING,
		'char' => self::TYPE_STRING,
		'datetime' => self::TYPE_DATETIME,
		'year' => self::TYPE_DATE,
		'date' => self::TYPE_DATE,
		'time' => self::TYPE_TIME,
		'timestamp' => self::TYPE_TIMESTAMP,
		'enum' => self::TYPE_STRING,
	);

	/**
	 * Quotes a table name for use in a query.
	 * A simple table name has no schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteSimpleTableName($name)
	{
		return strpos($name, "`") !== false ? $name : "`" . $name . "`";
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name has no prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteSimpleColumnName($name)
	{
		return strpos($name, '`') !== false || $name === '*' ? $name : '`' . $name . '`';
	}

	/**
	 * Creates a query builder for the MySQL database.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->connection);
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return \yii\db\TableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTableSchema($name)
	{
		$table = new TableSchema;
		$this->resolveTableNames($table, $name);

		if ($this->findColumns($table)) {
			$this->findConstraints($table);
			return $table;
		}
	}

	/**
	 * Resolves the table name and schema name (if any).
	 * @param \yii\db\TableSchema $table the table metadata object
	 * @param string $name the table name
	 */
	protected function resolveTableNames($table, $name)
	{
		$parts = explode('.', str_replace('`', '', $name));
		if (isset($parts[1])) {
			$table->schemaName = $parts[0];
			$table->name = $parts[1];
			$table->quotedName = $this->quoteSimpleTableName($table->schemaName) . '.' . $this->quoteSimpleTableName($table->name);
		} else {
			$table->name = $parts[0];
			$table->quotedName = $this->quoteSimpleTableName($table->name);
		}
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return ColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c = new ColumnSchema;

		$c->name = $column['Field'];
		$c->quotedName = $this->quoteSimpleColumnName($c->name);
		$c->allowNull = $column['Null'] === 'YES';
		$c->isPrimaryKey = strpos($column['Key'], 'PRI') !== false;
		$c->autoIncrement = stripos($column['Extra'], 'auto_increment') !== false;

		$c->dbType = $column['Type'];
		$this->resolveColumnType($c);
		$c->resolvePhpType();

		$this->resolveColumnDefault($c, $column['Default']);

		return $c;
	}

	/**
	 * Resolves the default value for the column.
	 * @param \yii\db\ColumnSchema $column the column metadata object
	 * @param string $value the default value fetched from database
	 */
	protected function resolveColumnDefault($column, $value)
	{
		if ($column->type !== 'timestamp' || $value !== 'CURRENT_TIMESTAMP') {
			$column->defaultValue = $column->typecast($value);
		}
	}

	/**
	 * Resolves the abstract data type for the column.
	 * @param \yii\db\ColumnSchema $column the column metadata object
	 */
	public function resolveColumnType($column)
	{
		$column->type = self::TYPE_STRING;
		$column->unsigned = strpos($column->dbType, 'unsigned') !== false;

		if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
			$type = $matches[1];
			if (isset($this->typeMap[$type])) {
				$column->type = $this->typeMap[$type];
			}

			if (!empty($matches[2])) {
				if ($type === 'enum') {
					$values = explode(',', $matches[2]);
					foreach ($values as $i => $value) {
						$values[$i] = trim($value, "'");
					}
					$column->enumValues = $values;
				} else {
					$values = explode(',', $matches[2]);
					$column->size = $column->precision = (int)$values[0];
					if (isset($values[1])) {
						$column->scale = (int)$values[1];
					}
					if ($column->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
						$column->type = 'boolean';
					} elseif ($type === 'bit') {
						if ($column->size > 32) {
							$column->type = 'bigint';
						} elseif ($column->size === 32) {
							$column->type = 'integer';
						}
					}
				}
			}
		}
	}

	/**
	 * Collects the metadata of table columns.
	 * @param \yii\db\TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql = 'SHOW COLUMNS FROM ' . $table->quotedName;
		try {
			$columns = $this->connection->createCommand($sql)->queryAll();
		} catch (\Exception $e) {
			return false;
		}
		foreach ($columns as $column) {
			$column = $this->createColumn($column);
			$table->columns[$column->name] = $column;
			if ($column->isPrimaryKey) {
				$table->primaryKey[] = $column->name;
				if ($column->autoIncrement) {
					$table->sequenceName = '';
				}
			}
		}
		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param \yii\db\TableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$row = $this->connection->createCommand('SHOW CREATE TABLE ' . $table->quotedName)->queryRow();
		if (isset($row['Create Table'])) {
			$sql = $row['Create Table'];
		} else {
			$row = array_values($row);
			$sql = $row[1];
		}

		$regexp = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
		if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$fks = array_map('trim', explode(',', str_replace('`', '', $match[1])));
				$pks = array_map('trim', explode(',', str_replace('`', '', $match[3])));
				$constraint = array(str_replace('`', '', $match[2]));
				foreach ($fks as $k => $name) {
					$constraint[$name] = $pks[$k];
				}
				$table->foreignKeys[] = $constraint;
			}
		}
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema = '')
	{
		if ($schema === '') {
			return $this->connection->createCommand('SHOW TABLES')->queryColumn();
		}
		$sql = 'SHOW TABLES FROM ' . $this->quoteSimpleTableName($schema);
		$names = $this->connection->createCommand($sql)->queryColumn();
		foreach ($names as $i => $name) {
			$names[$i] = $schema . '.' . $name;
		}
		return $names;
	}
}
