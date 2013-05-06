<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\TableSchema;

/**
 * Schema is the class for retrieving metadata from a MS SQL database (version 2008 and above).
 *
 * @author Timur Ruziev <qiang.xue@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
	/**
	 * Default schema name to be used.
	 */
	const DEFAULT_SCHEMA = 'dbo';

	/**
	 * @var array mapping from physical column types (keys) to abstract column types (values)
	 */
	public $typeMap = array(
		// TODO: mssql driver
	);

	/**
	 * @param string $name
	 * @return TableSchema
	 */
	public function loadTableSchema($name)
	{
		return null;
	}

	/**
	 * Quotes a table name for use in a query.
	 * A simple table name has no schema prefix.
	 * @param string $name table name.
	 * @return string the properly quoted table name.
	 */
	public function quoteSimpleTableName($name)
	{
		return strpos($name, '[') !== false ? $name : '[' . $name . ']';
	}

	/**
	 * Quotes a column name for use in a query.
	 * A simple column name has no prefix.
	 * @param string $name column name.
	 * @return string the properly quoted column name.
	 */
	public function quoteSimpleColumnName($name)
	{
		return strpos($name, '[') !== false || $name === '*' ? $name : '[' . $name . ']';
	}

	/**
	 * Creates a query builder for the MSSQL database.
	 * @return QueryBuilder query builder interface.
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}

	/**
	 * Returns all table names in the database.
	 * This method should be overridden by child classes in order to support this feature
	 * because the default implementation simply throws an exception.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return array all table names in the database. The names have NO the schema name prefix.
	 */
	protected function findTableNames($schema = '')
	{
		if ('' === $schema) {
			$schema = self::DEFAULT_SCHEMA;
		}
		$sql = "SELECT TABLE_NAME FROM [INFORMATION_SCHEMA].[TABLES] WHERE TABLE_SCHEMA = :schema AND TABLE_TYPE = 'BASE TABLE'";
		$names = $this->db->createCommand($sql, array(':schema' => $schema))->queryColumn();
		if (self::DEFAULT_SCHEMA !== $schema) {
			foreach ($names as $index => $name) {
				$names[$index] = $schema . '.' . $name;
			}
		}
		return $names;
	}
}
