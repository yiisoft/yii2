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
 * Schema is the class for retrieving metadata from a PostgreSQL database
 * (version 9.x and above).
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{
	/**
	 * @var string the default schema used for the current session.
	 */
	public $defaultSchema = 'public';
	/**
	 * @var array mapping from physical column types (keys) to abstract
	 * column types (values)
	 */
	public $typeMap = [
		'abstime' => self::TYPE_TIMESTAMP,
		'bit' => self::TYPE_STRING,
		'bool' => self::TYPE_BOOLEAN,
		'boolean' => self::TYPE_BOOLEAN,
		'box' => self::TYPE_STRING,
		'character' => self::TYPE_STRING,
		'bytea' => self::TYPE_BINARY,
		'char' => self::TYPE_STRING,
		'cidr' => self::TYPE_STRING,
		'circle' => self::TYPE_STRING,
		'date' => self::TYPE_DATE,
		'real' => self::TYPE_FLOAT,
		'decimal' => self::TYPE_DECIMAL,
		'double precision' => self::TYPE_DECIMAL,
		'inet' => self::TYPE_STRING,
		'smallint' => self::TYPE_SMALLINT,
		'int4' => self::TYPE_INTEGER,
		'int8' => self::TYPE_BIGINT,
		'integer' => self::TYPE_INTEGER,
		'bigint' => self::TYPE_BIGINT,
		'interval' => self::TYPE_STRING,
		'json' => self::TYPE_STRING,
		'line' => self::TYPE_STRING,
		'macaddr' => self::TYPE_STRING,
		'money' => self::TYPE_MONEY,
		'name' => self::TYPE_STRING,
		'numeric' => self::TYPE_STRING,
		'oid' => self::TYPE_BIGINT, // should not be used. it's pg internal!
		'path' => self::TYPE_STRING,
		'point' => self::TYPE_STRING,
		'polygon' => self::TYPE_STRING,
		'text' => self::TYPE_TEXT,
		'time without time zone' => self::TYPE_TIME,
		'timestamp without time zone' => self::TYPE_TIMESTAMP,
		'timestamp with time zone' => self::TYPE_TIMESTAMP,
		'time with time zone' => self::TYPE_TIMESTAMP,
		'unknown' => self::TYPE_STRING,
		'uuid' => self::TYPE_STRING,
		'bit varying' => self::TYPE_STRING,
		'character varying' => self::TYPE_STRING,
		'xml' => self::TYPE_STRING
	];

	/**
	 * Creates a query builder for the PostgreSQL database.
	 * @return QueryBuilder query builder instance
	 */
	public function createQueryBuilder()
	{
		return new QueryBuilder($this->db);
	}

	/**
	 * Resolves the table name and schema name (if any).
	 * @param TableSchema $table the table metadata object
	 * @param string $name the table name
	 */
	protected function resolveTableNames($table, $name)
	{
		$parts = explode('.', str_replace('"', '', $name));

		if (isset($parts[1])) {
			$table->schemaName = $parts[0];
			$table->name = $parts[1];
		} else {
			$table->schemaName = $this->defaultSchema;
			$table->name = $name;
		}

		$table->fullName = $table->schemaName !== $this->defaultSchema ? $table->schemaName . '.' . $table->name : $table->name;
	}

	/**
	 * Quotes a table name for use in a query.
	 * A simple table name has no schema prefix.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteSimpleTableName($name)
	{
		return strpos($name, '"') !== false ? $name : '"' . $name . '"';
	}

	/**
	 * Loads the metadata for the specified table.
	 * @param string $name table name
	 * @return TableSchema|null driver dependent table metadata. Null if the table does not exist.
	 */
	public function loadTableSchema($name)
	{
		$table = new TableSchema();
		$this->resolveTableNames($table, $name);
		if ($this->findColumns($table)) {
			$this->findConstraints($table);
			return $table;
		} else {
			return null;
		}
	}

	/**
	 * Determines the PDO type for the given PHP data value.
	 * @param mixed $data the data whose PDO type is to be determined
	 * @return integer the PDO type
	 * @see http://www.php.net/manual/en/pdo.constants.php
	 */
	public function getPdoType($data)
	{
		// php type => PDO type
		static $typeMap = [
			// https://github.com/yiisoft/yii2/issues/1115
			// Cast boolean to integer values to work around problems with PDO casting false to string '' https://bugs.php.net/bug.php?id=33876
			'boolean' => \PDO::PARAM_INT,
			'integer' => \PDO::PARAM_INT,
			'string' => \PDO::PARAM_STR,
			'resource' => \PDO::PARAM_LOB,
			'NULL' => \PDO::PARAM_NULL,
		];
		$type = gettype($data);
		return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @return array all table names in the database. The names have NO schema name prefix.
	 */
	protected function findTableNames($schema = '')
	{
		if ($schema === '') {
			$schema = $this->defaultSchema;
		}
		$sql = <<<EOD
SELECT table_name, table_schema FROM information_schema.tables
WHERE table_schema=:schema AND table_type='BASE TABLE'
EOD;
		$command = $this->db->createCommand($sql);
		$command->bindParam(':schema', $schema);
		$rows = $command->queryAll();
		$names = [];
		foreach ($rows as $row) {
			$names[] = $row['table_name'];
		}
		return $names;
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param TableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{

		$tableName = $this->quoteValue($table->name);
		$tableSchema = $this->quoteValue($table->schemaName);

		//We need to extract the constraints de hard way since:
		//http://www.postgresql.org/message-id/26677.1086673982@sss.pgh.pa.us

		$sql = <<<SQL
select
	(select string_agg(attname,',') attname from pg_attribute where attrelid=ct.conrelid and attnum = any(ct.conkey)) as columns,
	fc.relname as foreign_table_name,
	fns.nspname as foreign_table_schema,
	(select string_agg(attname,',') attname from pg_attribute where attrelid=ct.confrelid and attnum = any(ct.confkey)) as foreign_columns
from
	pg_constraint ct
	inner join pg_class c on c.oid=ct.conrelid
	inner join pg_namespace ns on c.relnamespace=ns.oid
	left join pg_class fc on fc.oid=ct.confrelid
	left join pg_namespace fns on fc.relnamespace=fns.oid
	
where
	ct.contype='f'
	and c.relname={$tableName}
	and ns.nspname={$tableSchema}
SQL;

		$constraints = $this->db->createCommand($sql)->queryAll();
		foreach ($constraints as $constraint) {
			$columns = explode(',', $constraint['columns']);
			$fcolumns = explode(',', $constraint['foreign_columns']);
			if ($constraint['foreign_table_schema'] !== $this->defaultSchema) {
				$foreignTable = $constraint['foreign_table_schema'] . '.' . $constraint['foreign_table_name'];
			} else {
				$foreignTable = $constraint['foreign_table_name'];
			}
			$citem = [$foreignTable];
			foreach ($columns as $idx => $column) {
				$citem[$column] = $fcolumns[$idx];
			}
			$table->foreignKeys[] = $citem;
		}
	}

	/**
	 * Gets information about given table unique indexes.
	 * @param TableSchema $table the table metadata
	 * @return array with index names, columns and if it is an expression tree
	 */
	protected function getUniqueIndexInformation($table)
	{
		$tableName = $this->quoteValue($table->name);
		$tableSchema = $this->quoteValue($table->schemaName);

		$sql = <<<SQL
SELECT
	i.relname as indexname,
	ARRAY(
		SELECT pg_get_indexdef(idx.indexrelid, k + 1, True)
		FROM generate_subscripts(idx.indkey, 1) AS k
		ORDER BY k
	) AS indexcolumns,
	idx.indexprs IS NOT NULL AS indexprs
FROM pg_index idx
INNER JOIN pg_class i ON i.oid = idx.indexrelid
INNER JOIN pg_class c ON c.oid = idx.indrelid
INNER JOIN pg_namespace ns ON c.relnamespace = ns.oid
WHERE idx.indisprimary != True
AND idx.indisunique = True
AND c.relname = {$tableName}
AND ns.nspname = {$tableSchema}
;
SQL;

		return $this->db->createCommand($sql)->queryAll();
	}

	/**
	 * Returns all unique indexes for the given table.
	 * Each array element is of the following structure:
	 *
	 * ~~~
	 * [
	 *	 'IndexName1' => ['col1' [, ...]],
	 *	 'IndexName2' => ['col2' [, ...]],
	 * ]
	 * ~~~
	 *
	 * @param TableSchema $table the table metadata
	 * @return array all unique indexes for the given table.
	 */
	public function findUniqueIndexes($table)
	{
		$indexes = $this->getUniqueIndexInformation($table);
		$uniqueIndexes = [];

		foreach ($indexes as $index) {
			$indexName = $index['indexname'];

			if ($index['indexprs']) {
				// Index is an expression like "lower(colname::text)"
				$indexColumns = preg_replace("/.*\(([^\:]+).*/mi", "$1", $index['indexcolumns']);
			} else {
				$indexColumns = array_map('trim', explode(',', str_replace(['{', '}'], '', $index['indexcolumns'])));
			}

			$uniqueIndexes[$indexName] = $indexColumns;

		}
		return $uniqueIndexes;
	}

	/**
	 * Collects the metadata of table columns.
	 * @param TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$tableName = $this->db->quoteValue($table->name);
		$schemaName = $this->db->quoteValue($table->schemaName);
		$sql = <<<SQL
SELECT
	d.nspname AS table_schema,
	c.relname AS table_name,
	a.attname AS column_name,
	t.typname AS data_type,
	a.attlen AS character_maximum_length,
	pg_catalog.col_description(c.oid, a.attnum) AS column_comment,
	a.atttypmod AS modifier,
	a.attnotnull = false AS is_nullable,
	CAST(pg_get_expr(ad.adbin, ad.adrelid) AS varchar) AS column_default,
	coalesce(pg_get_expr(ad.adbin, ad.adrelid) ~ 'nextval',false) AS is_autoinc,
	array_to_string((select array_agg(enumlabel) from pg_enum where enumtypid=a.atttypid)::varchar[],',') as enum_values,
	CASE atttypid
		 WHEN 21 /*int2*/ THEN 16
		 WHEN 23 /*int4*/ THEN 32
		 WHEN 20 /*int8*/ THEN 64
		 WHEN 1700 /*numeric*/ THEN
		      CASE WHEN atttypmod = -1
			   THEN null
			   ELSE ((atttypmod - 4) >> 16) & 65535
			   END
		 WHEN 700 /*float4*/ THEN 24 /*FLT_MANT_DIG*/
		 WHEN 701 /*float8*/ THEN 53 /*DBL_MANT_DIG*/
		 ELSE null
	  END   AS numeric_precision,
	  CASE
	    WHEN atttypid IN (21, 23, 20) THEN 0
	    WHEN atttypid IN (1700) THEN
		CASE
		    WHEN atttypmod = -1 THEN null
		    ELSE (atttypmod - 4) & 65535
		END
	       ELSE null
	  END AS numeric_scale,
	CAST(
             information_schema._pg_char_max_length(information_schema._pg_truetypid(a, t), information_schema._pg_truetypmod(a, t))
             AS numeric
	) AS size,
	a.attnum = any (ct.conkey) as is_pkey
FROM
	pg_class c
	LEFT JOIN pg_attribute a ON a.attrelid = c.oid
	LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
	LEFT JOIN pg_type t ON a.atttypid = t.oid
	LEFT JOIN pg_namespace d ON d.oid = c.relnamespace
	LEFT join pg_constraint ct on ct.conrelid=c.oid and ct.contype='p'
WHERE
	a.attnum > 0 and t.typname != ''
	and c.relname = {$tableName}
	and d.nspname = {$schemaName}
ORDER BY
	a.attnum;
SQL;

		$columns = $this->db->createCommand($sql)->queryAll();
		if (empty($columns)) {
			return false;
		}
		foreach ($columns as $column) {
			$column = $this->loadColumnSchema($column);
			$table->columns[$column->name] = $column;
			if ($column->isPrimaryKey === true) {
				$table->primaryKey[] = $column->name;
				if ($table->sequenceName === null && preg_match("/nextval\\('\"?\\w+\"?\.?\"?\\w+\"?'(::regclass)?\\)/", $column->defaultValue) === 1) {
					$table->sequenceName = preg_replace(['/nextval/', '/::/', '/regclass/', '/\'\)/', '/\(\'/'], '', $column->defaultValue);
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
		$column = new ColumnSchema();
		$column->allowNull = $info['is_nullable'];
		$column->autoIncrement = $info['is_autoinc'];
		$column->comment = $info['column_comment'];
		$column->dbType = $info['data_type'];
		$column->defaultValue = $info['column_default'];
		$column->enumValues = explode(',', str_replace(["''"], ["'"], $info['enum_values']));
		$column->unsigned = false; // has no meaning in PG
		$column->isPrimaryKey = $info['is_pkey'];
		$column->name = $info['column_name'];
		$column->precision = $info['numeric_precision'];
		$column->scale = $info['numeric_scale'];
		$column->size = $info['size'];

		if (isset($this->typeMap[$column->dbType])) {
			$column->type = $this->typeMap[$column->dbType];
		} else {
			$column->type = self::TYPE_STRING;
		}
		$column->phpType = $this->getColumnPhpType($column);
		return $column;
	}
}
