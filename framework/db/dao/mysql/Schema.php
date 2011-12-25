<?php
/**
 * Schema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao\mysql;

use yii\db\dao\TableSchema;

/**
 * Schema is the class for retrieving metadata information from a MySQL database (version 4.1.x and 5.x).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\dao\Schema
{
	/**
	 * Quotes a table name for use in a query.
	 * A simple table name does not schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteSimpleTableName($name)
	{
		return strpos($name, "`") !== false ? $name : "`" . $name . "`";
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name does not contain prefix.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteSimpleColumnName($name)
	{
		return strpos($name, '`') !== false || $name === '*' ? $name : '`' . $name . '`';
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return \yii\db\dao\TableSchema driver dependent table metadata. Null if the table does not exist.
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
	 * Generates various kinds of table names.
	 * @param \yii\db\dao\TableSchema $table the table instance
	 * @param string $name the unquoted table name
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
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c = new ColumnSchema;

		$c->name = $column['Field'];
		$c->quotedName = $this->quoteSimpleColumnName($c->name);
		$c->allowNull = $column['Null'] === 'YES';
		$c->isPrimaryKey = strpos($column['Key'], 'PRI') !== false;
		$c->autoIncrement = stripos($column['Extra'], 'auto_increment') !== false;
		$c->initTypes($column['Type']);
		$c->initDefaultValue($column['Default']);

		return $c;
	}

	/**
	 * Collects the table column metadata.
	 * @param \yii\db\dao\TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql = 'SHOW COLUMNS FROM ' . $table->quotedName;
		try {
			$columns = $this->connection->createCommand($sql)->queryAll();
		}
		catch(\Exception $e) {
			return false;
		}
		foreach ($columns as $column) {
			$table->columns[$c->name] = $c = $this->createColumn($column);
			if ($c->isPrimaryKey) {
				if ($table->primaryKey === null) {
					$table->primaryKey = $c->name;
				} elseif (is_string($table->primaryKey)) {
					$table->primaryKey = array($table->primaryKey, $c->name);
				} else {
					$table->primaryKey[] = $c->name;
				}
				if ($c->autoIncrement) {
					$table->sequenceName = '';
				}
			}
		}
		return true;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param \yii\db\dao\TableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$row = $this->connection->createCommand('SHOW CREATE TABLE ' . $table->quotedName)->queryRow();
		$matches = array();
		$regexp = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
		foreach ($row as $sql) {
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
				break;
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
		$names = $this->connection->createCommand('SHOW TABLES FROM ' . $this->quoteSimpleTableName($schema))->queryColumn();
		foreach ($names as &$name) {
			$name = $schema . '.' . $name;
		}
		return $names;
	}
}
