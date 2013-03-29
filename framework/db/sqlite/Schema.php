<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use yii\db\TableSchema;
use yii\db\ColumnSchema;

/**
 * Schema is the class for retrieving metadata from a SQLite (2/3) database.
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
	 * Creates a query builder for the MySQL database.
	 * This method may be overridden by child classes to create a DBMS-specific query builder.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema = '')
	{
		$sql = "SELECT DISTINCT tbl_name FROM sqlite_master WHERE tbl_name<>'sqlite_sequence'";
		return $this->db->createCommand($sql)->queryColumn();
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTableSchema($name)
	{
		$table = new TableSchema;
		$table->name = $name;

		if ($this->findColumns($table)) {
			$this->findConstraints($table);
			return $table;
		} else {
			return null;
		}
	}

	/**
	 * Collects the table column metadata.
	 * @param TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql = "PRAGMA table_info(" . $this->quoteSimpleTableName($table->name) . ')';
		$columns = $this->db->createCommand($sql)->queryAll();
		if (empty($columns)) {
			return false;
		}

		foreach ($columns as $info) {
			$column = $this->loadColumnSchema($info);
			$table->columns[$column->name] = $column;
			if ($column->isPrimaryKey) {
				$table->primaryKey[] = $column->name;
			}
		}
		if (count($table->primaryKey) === 1 && !strncasecmp($table->columns[$table->primaryKey[0]]->dbType, 'int', 3)) {
			$table->sequenceName = '';
			$table->columns[$table->primaryKey[0]]->autoIncrement = true;
		}

		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param TableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$sql = "PRAGMA foreign_key_list(" . $this->quoteSimpleTableName($table->name) . ')';
		$keys = $this->db->createCommand($sql)->queryAll();
		foreach ($keys as $key) {
			$table->foreignKeys[] = array($key['table'], $key['from'] => $key['to']);
		}
	}

	/**
	 * Loads the column information into a [[ColumnSchema]] object.
	 * @param array $info column information
	 * @return ColumnSchema the column schema object
	 */
	protected function loadColumnSchema($info)
	{
		$column = new ColumnSchema;
		$column->name = $info['name'];
		$column->allowNull = !$info['notnull'];
		$column->isPrimaryKey = $info['pk'] != 0;

		$column->dbType = $info['type'];
		$column->unsigned = strpos($column->dbType, 'unsigned') !== false;

		$column->type = self::TYPE_STRING;
		if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
			$type = $matches[1];
			if (isset($this->typeMap[$type])) {
				$column->type = $this->typeMap[$type];
			}

			if (!empty($matches[2])) {
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
		$column->phpType = $this->getColumnPhpType($this->type);

		$value = $info['dflt_value'];
		if ($column->type === 'string') {
			$column->defaultValue = trim($value, "'\"");
		} else {
			$column->defaultValue = $column->typecast(strcasecmp($value, 'null') ? $value : null);
		}

		return $column;
	}
}
