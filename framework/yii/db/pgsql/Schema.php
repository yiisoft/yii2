<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\TableSchema;
use yii\db\ColumnSchema;

/**
 * Schema is the class for retrieving metadata from a PostgreSQL database (version 9.x and above).
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema|null driver dependent table metadata. Null if the table does not exist.
	 */
	public function loadTableSchema($name)
	{
		$table = new TableSchema();
		$this->resolveTableNames($table, $name);
		$this->findPrimaryKeys($table);
		if ($this->findColumns($table)) {
			$this->findForeignKeys($table);
			return $table;
		}
	} 
}