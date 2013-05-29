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
class Schema extends \yii\db\Schema {

	/**
	 * Resolves the table name and schema name (if any).
	 * @param TableSchema $table the table metadata object
	 * @param string $name the table name
	 */
	protected function resolveTableNames($table, $name) {
		$parts = explode('.', str_replace('"', '', $name));
		if (isset($parts[1])) {
			$table->schemaName = $parts[0];
			$table->name = $parts[1];
		} else {
			$table->name = $parts[0];
		}
	}

	/**
	 * Quotes a table name for use in a query.
	 * A simple table name has no schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteSimpleTableName($name) {
		return strpos($name, '"') !== false ? $name : '"' . $name . '"';
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema|null driver dependent table metadata. Null if the table does not exist.
	 */
	public function loadTableSchema($name) {
		$table = new TableSchema();
		$this->resolveTableNames($table, $name);
		$this->findPrimaryKeys($table);
		if ($this->findColumns($table)) {
			$this->findForeignKeys($table);
			return $table;
		}
	}

}