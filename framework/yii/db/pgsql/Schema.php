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
class Schema extends \yii\db\Schema {

	/**
	 * The default schema used for the current session. This value is 
	 * automatically set to "public" by the PDO driver. 
	 * @var string 
	 */
	public static $DEFAULT_SCHEMA;

	/**
	 * @var array mapping from physical column types (keys) to abstract 
	 * column types (values)
	 */
	public $typeMap = array(
	    'abstime' => self::TYPE_TIMESTAMP,
	    //'aclitem' => self::TYPE_STRING,
	    'bit' => self::TYPE_STRING,
	    'boolean' => self::TYPE_BOOLEAN,
	    'box' => self::TYPE_STRING,
	    'character' => self::TYPE_STRING,
	    'bytea' => self::TYPE_BINARY,
	    'char' => self::TYPE_STRING,
	    //'cid' => self::TYPE_STRING,
	    'cidr' => self::TYPE_STRING,
	    'circle' => self::TYPE_STRING,
	    'date' => self::TYPE_DATE,
	    //'daterange' => self::TYPE_STRING,	    
	    'real' => self::TYPE_FLOAT,
	    'double precision' => self::TYPE_DECIMAL,
	    //'gtsvector' => self::TYPE_STRING,	    
	    'inet' => self::TYPE_STRING,
	    'smallint' => self::TYPE_SMALLINT,
	    'integer' => self::TYPE_INTEGER,
	    //'int4range' => self::TYPE_STRING, //unknown	    
	    'bigint' => self::TYPE_BIGINT,
	    //'int8range' => self::TYPE_STRING, // unknown	    
	    'interval' => self::TYPE_STRING,
	    'json' => self::TYPE_STRING,
	    'line' => self::TYPE_STRING,
	    //'lseg' => self::TYPE_STRING,
	    'macaddr' => self::TYPE_STRING,
	    'money' => self::TYPE_MONEY,
	    'name' => self::TYPE_STRING,
	    'numeric' => self::TYPE_STRING,
	    'numrange' => self::TYPE_DECIMAL,
	    'oid' => self::TYPE_BIGINT, // should not be used. it's pg internal!
	    'path' => self::TYPE_STRING,
	    //'pg_node_tree' => self::TYPE_STRING, 	    
	    'point' => self::TYPE_STRING,
	    'polygon' => self::TYPE_STRING,
	    //'refcursor' => self::TYPE_STRING,
	    //'regclass' => self::TYPE_STRING,
	    //'regconfig' => self::TYPE_STRING,
	    //'regdictionary' => self::TYPE_STRING,
	    //'regoper' => self::TYPE_STRING,
	    //'regoperator' => self::TYPE_STRING,
	    //'regproc' => self::TYPE_STRING,
	    //'regprocedure' => self::TYPE_STRING,
	    //'regtype' => self::TYPE_STRING,
	    //'reltime' => self::TYPE_STRING,
	    //'smgr' => self::TYPE_STRING,
	    'text' => self::TYPE_TEXT,
	    //'tid' => self::TYPE_STRING,
	    'time without time zone' => self::TYPE_TIME,
	    'timestamp without time zone' => self::TYPE_TIMESTAMP,
	    'timestamp with time zone' => self::TYPE_TIMESTAMP,
	    'time with time zone' => self::TYPE_TIMESTAMP,
	    //'tinterval' => self::TYPE_STRING,
	    //'tsquery' => self::TYPE_STRING,
	    //'tsrange' => self::TYPE_STRING,
	    //'tstzrange' => self::TYPE_STRING,
	    //'tsvector' => self::TYPE_STRING,
	    //'txid_snapshot' => self::TYPE_STRING,
	    'unknown' => self::TYPE_STRING,
	    'uuid' => self::TYPE_STRING,
	    'bit varying' => self::TYPE_STRING,
	    'character varying' => self::TYPE_STRING,
	    //'xid' => self::TYPE_STRING,
	    'xml' => self::TYPE_STRING
	);

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
		if ($table->schemaName === null) {
			$table->schemaName = self::$DEFAULT_SCHEMA;
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
		if ($this->findColumns($table)) {
			$this->findForeignKeys($table);
			return $table;
		}
	}

	/**
	 * Collects the metadata of table columns.
	 * @param TableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table) {
		$dbname = $this->db->quoteValue($this->db->pdo->getCurrentDatabase());
		$tableName = $this->db->quoteValue($table->name);
		$schemaName = $this->db->quoteValue($table->schemaName);
		$sql = <<<SQL
SELECT 
	current_database() as table_catalog,
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
        array_to_string((select array_agg(enumlabel) from pg_enum where enumtypid=a.atttypid)::varchar[],',') as enum_values
FROM
	pg_class c
	LEFT JOIN pg_attribute a ON a.attrelid = c.oid
	LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
	LEFT JOIN pg_type t ON a.atttypid = t.oid
	LEFT JOIN pg_namespace d ON d.oid = c.relnamespace         
WHERE
	a.attnum > 0
	and c.relname = {$tableName}
	and d.nspname = {$schemaName}
	and current_database() = {$dbname}
ORDER BY
	a.attnum;
SQL;

		try {
			$columns = $this->db->createCommand($sql)->queryAll();
		} catch (\Exception $e) {
			return false;
		}
		foreach ($columns as $column) {
			$column = $this->loadColumnSchema($column);
			if ($column->name == 'numbers')
				print_r($column);
		}
		die();
	}

	/**
	 * Loads the column information into a [[ColumnSchema]] object.
	 * @param array $info column information
	 * @return ColumnSchema the column schema object
	 */
	protected function loadColumnSchema($info) {
		$column = new ColumnSchema();
		$column->allowNull = $info['is_nullable'];
		$column->autoIncrement = $info['is_autoinc'];
		$column->comment = $info['column_comment'];
		$column->dbType = $info['data_type'];
		$column->defaultValue = $info['column_default'];
		$column->enumValues = explode(',', str_replace(array("''"), array("'"), $info['enum_values']));
//$column->isPrimaryKey
		$column->name = $info['column_name'];
		//$column->phpType
//$column->precision
//$column->scale
//$column->size;
//$column->type
//$column->unsigned
		return $column;
	}

}