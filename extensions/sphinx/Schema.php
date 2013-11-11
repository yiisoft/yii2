<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use yii\db\ColumnSchema;
use yii\db\TableSchema;

/**
 * Class Schema
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\mysql\Schema
{
	/**
	 * Creates a query builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific query builder.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTableSchema($name)
	{
		$table = new TableSchema;
		$this->resolveTableNames($table, $name);

		if ($this->findColumns($table)) {
			return $table;
		} else {
			return null;
		}
	}

	/**
	 * Collects the metadata of table columns.
	 * @param TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 * @throws \Exception if DB query fails
	 */
	protected function findColumns($table)
	{
		$sql = 'DESCRIBE ' . $this->quoteSimpleTableName($table->name);
		try {
			$columns = $this->db->createCommand($sql)->queryAll();
		} catch (\Exception $e) {
			$previous = $e->getPrevious();
			if ($previous instanceof \PDOException && $previous->getCode() == '42S02') {
				// index does not exist
				return false;
			}
			throw $e;
		}
		foreach ($columns as $info) {
			$column = $this->loadColumnSchema($info);
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
	 * Loads the column information into a [[ColumnSchema]] object.
	 * @param array $info column information
	 * @return ColumnSchema the column schema object
	 */
	protected function loadColumnSchema($info)
	{
		$column = new ColumnSchema;

		$column->name = $info['Field'];
		// Not supported :
		//$column->allowNull = $info['Null'] === 'YES';
		//$column->isPrimaryKey = strpos($info['Key'], 'PRI') !== false;
		//$column->autoIncrement = stripos($info['Extra'], 'auto_increment') !== false;
		//$column->comment = $info['Comment'];


		$column->dbType = $info['Type'];
		//$column->unsigned = strpos($column->dbType, 'unsigned') !== false;

		$type = $info['Type'];
		if (isset($this->typeMap[$type])) {
			$column->type = $this->typeMap[$type];
		} else {
			$column->type = self::TYPE_STRING;
		}

		$column->phpType = $this->getColumnPhpType($column);

		/*if ($column->type !== 'timestamp' || $info['Default'] !== 'CURRENT_TIMESTAMP') {
			$column->defaultValue = $column->typecast($info['Default']);
		}*/

		return $column;
	}
}