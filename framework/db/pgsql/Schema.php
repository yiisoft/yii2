<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\Expression;
use yii\db\TableSchema;
use yii\db\ColumnSchema;

/**
 * Schema is the class for retrieving metadata from a PostgreSQL database
 * (version 9.x and above).
 *
 * @property string[] $viewNames All view names in the database. This property is read-only.
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
     * @see http://www.postgresql.org/docs/current/static/datatype.html#DATATYPE-TABLE
     */
    public $typeMap = [
        'bit' => self::TYPE_INTEGER,
        'bit varying' => self::TYPE_INTEGER,
        'varbit' => self::TYPE_INTEGER,

        'bool' => self::TYPE_BOOLEAN,
        'boolean' => self::TYPE_BOOLEAN,

        'box' => self::TYPE_STRING,
        'circle' => self::TYPE_STRING,
        'point' => self::TYPE_STRING,
        'line' => self::TYPE_STRING,
        'lseg' => self::TYPE_STRING,
        'polygon' => self::TYPE_STRING,
        'path' => self::TYPE_STRING,

        'character' => self::TYPE_CHAR,
        'char' => self::TYPE_CHAR,
        'bpchar' => self::TYPE_CHAR,
        'character varying' => self::TYPE_STRING,
        'varchar' => self::TYPE_STRING,
        'text' => self::TYPE_TEXT,

        'bytea' => self::TYPE_BINARY,

        'cidr' => self::TYPE_STRING,
        'inet' => self::TYPE_STRING,
        'macaddr' => self::TYPE_STRING,

        'real' => self::TYPE_FLOAT,
        'float4' => self::TYPE_FLOAT,
        'double precision' => self::TYPE_DOUBLE,
        'float8' => self::TYPE_DOUBLE,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,

        'money' => self::TYPE_MONEY,

        'smallint' => self::TYPE_SMALLINT,
        'int2' => self::TYPE_SMALLINT,
        'int4' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'int8' => self::TYPE_BIGINT,
        'oid' => self::TYPE_BIGINT, // should not be used. it's pg internal!

        'smallserial' => self::TYPE_SMALLINT,
        'serial2' => self::TYPE_SMALLINT,
        'serial4' => self::TYPE_INTEGER,
        'serial' => self::TYPE_INTEGER,
        'bigserial' => self::TYPE_BIGINT,
        'serial8' => self::TYPE_BIGINT,
        'pg_lsn' => self::TYPE_BIGINT,

        'date' => self::TYPE_DATE,
        'interval' => self::TYPE_STRING,
        'time without time zone' => self::TYPE_TIME,
        'time' => self::TYPE_TIME,
        'time with time zone' => self::TYPE_TIME,
        'timetz' => self::TYPE_TIME,
        'timestamp without time zone' => self::TYPE_TIMESTAMP,
        'timestamp' => self::TYPE_TIMESTAMP,
        'timestamp with time zone' => self::TYPE_TIMESTAMP,
        'timestamptz' => self::TYPE_TIMESTAMP,
        'abstime' => self::TYPE_TIMESTAMP,

        'tsquery' => self::TYPE_STRING,
        'tsvector' => self::TYPE_STRING,
        'txid_snapshot' => self::TYPE_STRING,

        'unknown' => self::TYPE_STRING,

        'uuid' => self::TYPE_STRING,
        'json' => self::TYPE_STRING,
        'jsonb' => self::TYPE_STRING,
        'xml' => self::TYPE_STRING,
    ];

    /**
     * @var array list of ALL view names in the database
     */
    private $_viewNames = [];


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
     * Returns all schema names in the database, including the default one but not system schemas.
     * This method should be overridden by child classes in order to support this feature
     * because the default implementation simply throws an exception.
     * @return array all schema names in the database, except system schemas
     * @since 2.0.4
     */
    protected function findSchemaNames()
    {
        $sql = <<<SQL
SELECT ns.nspname AS schema_name
FROM pg_namespace ns
WHERE ns.nspname != 'information_schema' AND ns.nspname NOT LIKE 'pg_%'
ORDER BY ns.nspname
SQL;
        return $this->db->createCommand($sql)->queryColumn();
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
        $sql = <<<SQL
SELECT c.relname AS table_name
FROM pg_class c
INNER JOIN pg_namespace ns ON ns.oid = c.relnamespace
WHERE ns.nspname = :schemaName AND c.relkind IN ('r','v','m','f')
ORDER BY c.relname
SQL;
        $command = $this->db->createCommand($sql, [':schemaName' => $schema]);
        $rows = $command->queryAll();
        $names = [];
        foreach ($rows as $row) {
            $names[] = $row['table_name'];
        }

        return $names;
    }

    /**
     * Returns all views names in the database.
     * @param string $schema the schema of the views. Defaults to empty string, meaning the current or default schema.
     * @return array all views names in the database. The names have NO schema name prefix.
     * @since 2.0.9
     */
    protected function findViewNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }
        $sql = <<<SQL
SELECT c.relname AS table_name
FROM pg_class c
INNER JOIN pg_namespace ns ON ns.oid = c.relnamespace
WHERE ns.nspname = :schemaName AND c.relkind = 'v'
ORDER BY c.relname
SQL;
        $command = $this->db->createCommand($sql, [':schemaName' => $schema]);
        $rows = $command->queryAll();
        $names = [];
        foreach ($rows as $row) {
            $names[] = $row['table_name'];
        }

        return $names;
    }

    /**
     * Returns all view names in the database.
     * @param string $schema the schema of the views. Defaults to empty string, meaning the current or default schema name.
     * If not empty, the returned view names will be prefixed with the schema name.
     * @param boolean $refresh whether to fetch the latest available view names. If this is false,
     * view names fetched previously (if available) will be returned.
     * @return string[] all view names in the database.
     * @since 2.0.9
     */
    public function getViewNames($schema = '', $refresh = false)
    {
        if (!isset($this->_viewNames[$schema]) || $refresh) {
            $this->_viewNames[$schema] = $this->findViewNames($schema);
        }

        return $this->_viewNames[$schema];
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
    ct.conname as constraint_name,
    a.attname as column_name,
    fc.relname as foreign_table_name,
    fns.nspname as foreign_table_schema,
    fa.attname as foreign_column_name
from
    (SELECT ct.conname, ct.conrelid, ct.confrelid, ct.conkey, ct.contype, ct.confkey, generate_subscripts(ct.conkey, 1) AS s
       FROM pg_constraint ct
    ) AS ct
    inner join pg_class c on c.oid=ct.conrelid
    inner join pg_namespace ns on c.relnamespace=ns.oid
    inner join pg_attribute a on a.attrelid=ct.conrelid and a.attnum = ct.conkey[ct.s]
    left join pg_class fc on fc.oid=ct.confrelid
    left join pg_namespace fns on fc.relnamespace=fns.oid
    left join pg_attribute fa on fa.attrelid=ct.confrelid and fa.attnum = ct.confkey[ct.s]
where
    ct.contype='f'
    and c.relname={$tableName}
    and ns.nspname={$tableSchema}
order by
    fns.nspname, fc.relname, a.attnum
SQL;

        $constraints = [];
        foreach ($this->db->createCommand($sql)->queryAll() as $constraint) {
            if ($constraint['foreign_table_schema'] !== $this->defaultSchema) {
                $foreignTable = $constraint['foreign_table_schema'] . '.' . $constraint['foreign_table_name'];
            } else {
                $foreignTable = $constraint['foreign_table_name'];
            }
            $name = $constraint['constraint_name'];
            if (!isset($constraints[$name])) {
                $constraints[$name] = [
                    'tableName' => $foreignTable,
                    'columns' => [],
                ];
            }
            $constraints[$name]['columns'][$constraint['column_name']] = $constraint['foreign_column_name'];
        }
        foreach ($constraints as $constraint) {
            $table->foreignKeys[] = array_merge([$constraint['tableName']], $constraint['columns']);
        }
    }

    /**
     * Gets information about given table unique indexes.
     * @param TableSchema $table the table metadata
     * @return array with index and column names
     */
    protected function getUniqueIndexInformation($table)
    {
        $sql = <<<SQL
SELECT
    i.relname as indexname,
    pg_get_indexdef(idx.indexrelid, k + 1, TRUE) AS columnname
FROM (
  SELECT *, generate_subscripts(indkey, 1) AS k
  FROM pg_index
) idx
INNER JOIN pg_class i ON i.oid = idx.indexrelid
INNER JOIN pg_class c ON c.oid = idx.indrelid
INNER JOIN pg_namespace ns ON c.relnamespace = ns.oid
WHERE idx.indisprimary = FALSE AND idx.indisunique = TRUE
AND c.relname = :tableName AND ns.nspname = :schemaName
ORDER BY i.relname, k
SQL;

        return $this->db->createCommand($sql, [
            ':schemaName' => $table->schemaName,
            ':tableName' => $table->name,
        ])->queryAll();
    }

    /**
     * Returns all unique indexes for the given table.
     * Each array element is of the following structure:
     *
     * ```php
     * [
     *     'IndexName1' => ['col1' [, ...]],
     *     'IndexName2' => ['col2' [, ...]],
     * ]
     * ```
     *
     * @param TableSchema $table the table metadata
     * @return array all unique indexes for the given table.
     */
    public function findUniqueIndexes($table)
    {
        $uniqueIndexes = [];

        $rows = $this->getUniqueIndexInformation($table);
        foreach ($rows as $row) {
            $column = $row['columnname'];
            if (!empty($column) && $column[0] === '"') {
                // postgres will quote names that are not lowercase-only
                // https://github.com/yiisoft/yii2/issues/10613
                $column = substr($column, 1, -1);
            }
            $uniqueIndexes[$row['indexname']][] = $column;
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
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($table->sequenceName === null && preg_match("/nextval\\('\"?\\w+\"?\.?\"?\\w+\"?'(::regclass)?\\)/", $column->defaultValue) === 1) {
                    $table->sequenceName = preg_replace(['/nextval/', '/::/', '/regclass/', '/\'\)/', '/\(\'/'], '', $column->defaultValue);
                }
                $column->defaultValue = null;
            } elseif ($column->defaultValue) {
                if ($column->type === 'timestamp' && $column->defaultValue === 'now()') {
                    $column->defaultValue = new Expression($column->defaultValue);
                } elseif ($column->type === 'boolean') {
                        $column->defaultValue = ($column->defaultValue === 'true');
                } elseif (stripos($column->dbType, 'bit') === 0 || stripos($column->dbType, 'varbit') === 0) {
                    $column->defaultValue = bindec(trim($column->defaultValue, 'B\''));
                } elseif (preg_match("/^'(.*?)'::/", $column->defaultValue, $matches)) {
                    $column->defaultValue = $matches[1];
                } elseif (preg_match('/^(?:\()?(.*?)(?(1)\))(?:::.+)?$/', $column->defaultValue, $matches)) {
                    if ($matches[1] === 'NULL') {
                        $column->defaultValue = null;
                    } else {
                        $column->defaultValue = $column->phpTypecast($matches[1]);
                    }
                } else {
                    $column->defaultValue = $column->phpTypecast($column->defaultValue);
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
        $column = $this->createColumnSchema();
        $column->allowNull = $info['is_nullable'];
        $column->autoIncrement = $info['is_autoinc'];
        $column->comment = $info['column_comment'];
        $column->dbType = $info['data_type'];
        $column->defaultValue = $info['column_default'];
        $column->enumValues = ($info['enum_values'] !== null) ? explode(',', str_replace(["''"], ["'"], $info['enum_values'])) : null;
        $column->unsigned = false; // has no meaning in PG
        $column->isPrimaryKey = $info['is_pkey'];
        $column->name = $info['column_name'];
        $column->precision = $info['numeric_precision'];
        $column->scale = $info['numeric_scale'];
        $column->size = $info['size'] === null ? null : (int) $info['size'];
        if (isset($this->typeMap[$column->dbType])) {
            $column->type = $this->typeMap[$column->dbType];
        } else {
            $column->type = self::TYPE_STRING;
        }
        $column->phpType = $this->getColumnPhpType($column);

        return $column;
    }

    /**
     * @inheritdoc
     */
    public function insert($table, $columns)
    {
        $params = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);
        $returnColumns = $this->getTableSchema($table)->primaryKey;
        if (!empty($returnColumns)) {
            $returning = [];
            foreach ((array) $returnColumns as $name) {
                $returning[] = $this->quoteColumnName($name);
            }
            $sql .= ' RETURNING ' . implode(', ', $returning);
        }

        $command = $this->db->createCommand($sql, $params);
        $command->prepare(false);
        $result = $command->queryOne();

        return !$command->pdoStatement->rowCount() ? false : $result;
    }
}
