<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\db\TableSchema;
use yii\db\ColumnSchema;
use yii\helpers\ArrayHelper;

/**
 * Schema is the class for retrieving metadata from a MS SQL database (version 2008 and above).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @author Timur Ruziev <resurtm@gmail.com>
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
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	public function loadTableSchema($name)
	{
		$table = new TableSchema();
		$this->resolveTableNames($table, $name);
		$this->findPrimaryKeys($table);

		if ($this->findColumns($table)) {
			$this->findConstraints($table);
			return $table;
		} else {
			return null;
		}
	}

	/**
	 * Collects the metadata of table columns.
	 * @param TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$columnsTableName = 'INFORMATION_SCHEMA.COLUMNS';
		$whereSql = "t1.TABLE_NAME = '" . $table->name . "'";
		if ($table->catalogName !== null) {
			$columnsTableName = $table->catalogName . '.' . $columnsTableName;
			$whereSql .= " AND t1.TABLE_CATALOG = '" . $table->catalogName . "'";
		}
		if ($table->schemaName !== null) {
			$whereSql .= " AND t1.TABLE_SCHEMA = '" . $table->schemaName . "'";
		}
		$columnsTableName = $this->quoteTableName($columnsTableName);

		$sql = <<<SQL
SELECT
	t1.*,
	columnproperty(object_id(t1.table_schema + '.' + t1.table_name), t1.column_name, 'IsIdentity') AS IsIdentity,
	CONVERT(VARCHAR, t2.value) AS Comment
FROM {$columnsTableName} AS t1
LEFT OUTER JOIN sys.extended_properties AS t2 ON
	t1.ORDINAL_POSITION = t2.minor_id AND
	object_name(t2.major_id) = t1.TABLE_NAME AND
	t2.class = 1 AND
	t2.class_desc = 'OBJECT_OR_COLUMN' AND
	t2.name = 'MS_Description'
WHERE {$whereSql}
SQL;

		try {
			$columns = $this->db->createCommand($sql)->queryAll();
		} catch (\Exception $e) {
			return false;
		}
		foreach ($columns as $column) {
			$column = $this->loadColumnSchema($column);
			if (is_array($table->primaryKey)) {
				$column->isPrimaryKey = count(preg_grep('/' . preg_quote($column->name) . '/i', $table->primaryKey)) > 0;
			} else {
				$column->isPrimaryKey = strcasecmp($column->name, $table->primaryKey) === 0;
			}
			$table->columns[$column->name] = $column;
			if ($column->isPrimaryKey && $column->autoIncrement) {
				$table->sequenceName = '';
			}
		}
		return true;
	}

	/**
	 * Collects the primary key column details for the given table.
	 * @param TableSchema $table the table metadata
	 */
	protected function findPrimaryKeys($table)
	{
		$keyColumnUsageTableName = 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		$tableConstraintsTableName = 'INFORMATION_SCHEMA.TABLE_CONSTRAINTS';
		if ($table->catalogName !== null) {
			$keyColumnUsageTableName = $table->catalogName . '.' . $keyColumnUsageTableName;
			$tableConstraintsTableName = $table->catalogName . '.' . $tableConstraintsTableName;
		}
		$keyColumnUsageTableName = $this->quoteTableName($keyColumnUsageTableName);
		$tableConstraintsTableName = $this->quoteTableName($tableConstraintsTableName);

		$sql = <<<SQL
SELECT
	kcu.column_name AS field_name
FROM {$keyColumnUsageTableName} AS kcu
LEFT JOIN {$tableConstraintsTableName} AS tc ON
	kcu.table_name = tc.table_name AND
	kcu.constraint_name = tc.constraint_name
WHERE
	tc.constraint_type = 'PRIMARY KEY' AND
	kcu.table_name = :tableName AND
	kcu.table_schema = :schemaName
SQL;

		$table->primaryKey = $this->db
			->createCommand($sql, array(':tableName' => $table->name, ':schemaName' => $table->schemaName))
			->queryColumn();
		if (count($table->primaryKey) == 0) {
			// table does not have primary key
			$table->primaryKey = null;
		} elseif (count($table->primaryKey) == 1) {
			// table have one primary key
			$table->primaryKey = $table->primaryKey[0];
		}
	}

	/**
	 * Loads the column information into a [[ColumnSchema]] object.
	 * @param array $info column information
	 * @return ColumnSchema the column schema object
	 */
	protected function loadColumnSchema($info)
	{
		$column = new ColumnSchema();
		$column->name = $info['COLUMN_NAME'];
		$column->comment = $info['Comment'] === null ? '' : $column['Comment'];

		$column->dbType = $info['DATA_TYPE'];
		$column->unsigned = stripos($column->dbType, 'unsigned') !== false;
		$column->allowNull = $info['IS_NULLABLE'] == 'YES';

		$column->isPrimaryKey = null; // primary key is determined in findColumns() method
		$column->autoIncrement = $info['IsIdentity'] == 1;

		$column->type = self::TYPE_STRING;
		// TODO: better type infer

		$column->phpType = $this->getColumnPhpType($column);
		return $column;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param TableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$referentialConstraintsTableName = 'INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS';
		$keyColumnUsageTableName = 'INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		if ($table->catalogName !== null) {
			$referentialConstraintsTableName = $table->catalogName . '.' . $referentialConstraintsTableName;
			$keyColumnUsageTableName = $table->catalogName . '.' . $keyColumnUsageTableName;
		}
		$referentialConstraintsTableName = $this->quoteTableName($referentialConstraintsTableName);
		$keyColumnUsageTableName = $this->quoteTableName($keyColumnUsageTableName);

		// please refer to the following page for more details:
		// http://msdn2.microsoft.com/en-us/library/aa175805(SQL.80).aspx
		$sql = <<<SQL
SELECT
	kcu1.COLUMN_NAME AS fk_column_name,
	kcu2.TABLE_NAME AS uq_table_name,
	kcu2.COLUMN_NAME AS uq_column_name
FROM {$referentialConstraintsTableName} AS rc
JOIN {$keyColumnUsageTableName} AS kcu1 ON
	kcu1.CONSTRAINT_CATALOG = rc.CONSTRAINT_CATALOG AND
	kcu1.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA AND
	kcu1.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
JOIN {$keyColumnUsageTableName} AS kcu2 ON
	kcu2.CONSTRAINT_CATALOG = rc.UNIQUE_CONSTRAINT_CATALOG AND
	kcu2.CONSTRAINT_SCHEMA = rc.UNIQUE_CONSTRAINT_SCHEMA AND
	kcu2.CONSTRAINT_NAME = rc.UNIQUE_CONSTRAINT_NAME AND
	kcu2.ORDINAL_POSITION = kcu1.ORDINAL_POSITION
WHERE kcu1.TABLE_NAME = :tableName
SQL;

		$rows = $this->db->createCommand($sql, array(':tableName' => $table->name))->queryAll();
		$table->foreignKeys = array();
		foreach ($rows as $row) {
			$table->foreignKeys[] = array($row['uq_table_name'], $row['fk_column_name'] => $row['uq_column_name']);
		}
	}

	/**
	 * Resolves the table name and schema name (if any).
	 * @param TableSchema $table the table metadata object
	 * @param string $name the table name
	 */
	protected function resolveTableNames($table, $name)
	{
		$parts = explode('.', str_replace(array('[', ']'), '', $name));
		$partCount = count($parts);
		if ($partCount == 3) {
			// catalog name, schema name and table name provided
			$table->catalogName = $parts[0];
			$table->schemaName = $parts[1];
			$table->name = $parts[2];
		} elseif ($partCount == 2) {
			// only schema name and table name provided
			$table->schemaName = $parts[0];
			$table->name = $parts[1];
		} else {
			// only schema name provided
			$table->schemaName = static::DEFAULT_SCHEMA;
			$table->name = $parts[0];
		}
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
