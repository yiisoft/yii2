<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\TableSchema;
use yii\db\ViewFinderTrait;
use yii\helpers\ArrayHelper;

/**
 * Schema is the class for retrieving metadata from a PostgreSQL database
 * (version 9.x and above).
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema implements ConstraintFinderInterface
{
    use ViewFinderTrait;
    use ConstraintFinderTrait;

    const TYPE_JSONB = 'jsonb';

    /**
     * @var string the default schema used for the current session.
     */
    public $defaultSchema = 'public';
    /**
     * {@inheritdoc}
     */
    public $columnSchemaClass = 'yii\db\pgsql\ColumnSchema';
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
        'json' => self::TYPE_JSON,
        'jsonb' => self::TYPE_JSON,
        'xml' => self::TYPE_STRING,
    ];

    /**
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = '"';


    /**
     * {@inheritdoc}
     */
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $resolvedName->schemaName = $parts[0];
            $resolvedName->name = $parts[1];
        } else {
            $resolvedName->schemaName = $this->defaultSchema;
            $resolvedName->name = $name;
        }
        $resolvedName->fullName = ($resolvedName->schemaName !== $this->defaultSchema ? $resolvedName->schemaName . '.' : '') . $resolvedName->name;
        return $resolvedName;
    }

    /**
     * {@inheritdoc}
     */
    protected function findSchemaNames()
    {
        static $sql = <<<'SQL'
SELECT "ns"."nspname"
FROM "pg_namespace" AS "ns"
WHERE "ns"."nspname" != 'information_schema' AND "ns"."nspname" NOT LIKE 'pg_%'
ORDER BY "ns"."nspname" ASC
SQL;

        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }
        $sql = <<<'SQL'
SELECT c.relname AS table_name
FROM pg_class c
INNER JOIN pg_namespace ns ON ns.oid = c.relnamespace
WHERE ns.nspname = :schemaName AND c.relkind IN ('r','v','m','f')
ORDER BY c.relname
SQL;
        return $this->db->createCommand($sql, [':schemaName' => $schema])->queryColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);
        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            return $table;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTablePrimaryKey($tableName)
    {
        return $this->loadTableConstraints($tableName, 'primaryKey');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableForeignKeys($tableName)
    {
        return $this->loadTableConstraints($tableName, 'foreignKeys');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableIndexes($tableName)
    {
        static $sql = <<<'SQL'
SELECT
    "ic"."relname" AS "name",
    "ia"."attname" AS "column_name",
    "i"."indisunique" AS "index_is_unique",
    "i"."indisprimary" AS "index_is_primary"
FROM "pg_class" AS "tc"
INNER JOIN "pg_namespace" AS "tcns"
    ON "tcns"."oid" = "tc"."relnamespace"
INNER JOIN "pg_index" AS "i"
    ON "i"."indrelid" = "tc"."oid"
INNER JOIN "pg_class" AS "ic"
    ON "ic"."oid" = "i"."indexrelid"
INNER JOIN "pg_attribute" AS "ia"
    ON "ia"."attrelid" = "i"."indrelid" AND "ia"."attnum" = ANY ("i"."indkey")
WHERE "tcns"."nspname" = :schemaName AND "tc"."relname" = :tableName
ORDER BY "ia"."attnum" ASC
SQL;

        $resolvedName = $this->resolveTableName($tableName);
        $indexes = $this->db->createCommand($sql, [
            ':schemaName' => $resolvedName->schemaName,
            ':tableName' => $resolvedName->name,
        ])->queryAll();
        $indexes = $this->normalizePdoRowKeyCase($indexes, true);
        $indexes = ArrayHelper::index($indexes, null, 'name');
        $result = [];
        foreach ($indexes as $name => $index) {
            $result[] = new IndexConstraint([
                'isPrimary' => (bool) $index[0]['index_is_primary'],
                'isUnique' => (bool) $index[0]['index_is_unique'],
                'name' => $name,
                'columnNames' => ArrayHelper::getColumn($index, 'column_name'),
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableUniques($tableName)
    {
        return $this->loadTableConstraints($tableName, 'uniques');
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableChecks($tableName)
    {
        return $this->loadTableConstraints($tableName, 'checks');
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException if this method is called.
     */
    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('PostgreSQL does not support default value constraints.');
    }

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
     * {@inheritdoc]
     */
    protected function findViewNames($schema = '')
    {
        if ($schema === '') {
            $schema = $this->defaultSchema;
        }
        $sql = <<<'SQL'
SELECT c.relname AS table_name
FROM pg_class c
INNER JOIN pg_namespace ns ON ns.oid = c.relnamespace
WHERE ns.nspname = :schemaName AND (c.relkind = 'v' OR c.relkind = 'm')
ORDER BY c.relname
SQL;
        return $this->db->createCommand($sql, [':schemaName' => $schema])->queryColumn();
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
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_UPPER) {
                $constraint = array_change_key_case($constraint, CASE_LOWER);
            }
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
        foreach ($constraints as $name => $constraint) {
            $table->foreignKeys[$name] = array_merge([$constraint['tableName']], $constraint['columns']);
        }
    }

    /**
     * Gets information about given table unique indexes.
     * @param TableSchema $table the table metadata
     * @return array with index and column names
     */
    protected function getUniqueIndexInformation($table)
    {
        $sql = <<<'SQL'
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
     *
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
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_UPPER) {
                $row = array_change_key_case($row, CASE_LOWER);
            }
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
     * @return bool whether the table exists in the database
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
    COALESCE(td.typname, tb.typname, t.typname) AS data_type,
    COALESCE(td.typtype, tb.typtype, t.typtype) AS type_type,
    a.attlen AS character_maximum_length,
    pg_catalog.col_description(c.oid, a.attnum) AS column_comment,
    a.atttypmod AS modifier,
    a.attnotnull = false AS is_nullable,
    CAST(pg_get_expr(ad.adbin, ad.adrelid) AS varchar) AS column_default,
    coalesce(pg_get_expr(ad.adbin, ad.adrelid) ~ 'nextval',false) AS is_autoinc,
    CASE WHEN COALESCE(td.typtype, tb.typtype, t.typtype) = 'e'::char
        THEN array_to_string((SELECT array_agg(enumlabel) FROM pg_enum WHERE enumtypid = COALESCE(td.oid, tb.oid, a.atttypid))::varchar[], ',')
        ELSE NULL
    END AS enum_values,
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
    a.attnum = any (ct.conkey) as is_pkey,
    COALESCE(NULLIF(a.attndims, 0), NULLIF(t.typndims, 0), (t.typcategory='A')::int) AS dimension
FROM
    pg_class c
    LEFT JOIN pg_attribute a ON a.attrelid = c.oid
    LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
    LEFT JOIN pg_type t ON a.atttypid = t.oid
    LEFT JOIN pg_type tb ON (a.attndims > 0 OR t.typcategory='A') AND t.typelem > 0 AND t.typelem = tb.oid OR t.typbasetype > 0 AND t.typbasetype = tb.oid
    LEFT JOIN pg_type td ON t.typndims > 0 AND t.typbasetype > 0 AND tb.typelem = td.oid
    LEFT JOIN pg_namespace d ON d.oid = c.relnamespace
    LEFT JOIN pg_constraint ct ON ct.conrelid = c.oid AND ct.contype = 'p'
WHERE
    a.attnum > 0 AND t.typname != ''
    AND c.relname = {$tableName}
    AND d.nspname = {$schemaName}
ORDER BY
    a.attnum;
SQL;
        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }
        foreach ($columns as $column) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_UPPER) {
                $column = array_change_key_case($column, CASE_LOWER);
            }
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
                    $column->defaultValue = $column->phpTypecast($matches[1]);
                } elseif (preg_match('/^(\()?(.*?)(?(1)\))(?:::.+)?$/', $column->defaultValue, $matches)) {
                    if ($matches[2] === 'NULL') {
                        $column->defaultValue = null;
                    } else {
                        $column->defaultValue = $column->phpTypecast($matches[2]);
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
        /** @var ColumnSchema $column */
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
        $column->dimension = (int)$info['dimension'];
        if (isset($this->typeMap[$column->dbType])) {
            $column->type = $this->typeMap[$column->dbType];
        } else {
            $column->type = self::TYPE_STRING;
        }
        $column->phpType = $this->getColumnPhpType($column);

        return $column;
    }

    /**
     * {@inheritdoc}
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

    /**
     * Loads multiple types of constraints and returns the specified ones.
     * @param string $tableName table name.
     * @param string $returnType return type:
     * - primaryKey
     * - foreignKeys
     * - uniques
     * - checks
     * @return mixed constraints.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        static $sql = <<<'SQL'
SELECT
    "c"."conname" AS "name",
    "a"."attname" AS "column_name",
    "c"."contype" AS "type",
    "ftcns"."nspname" AS "foreign_table_schema",
    "ftc"."relname" AS "foreign_table_name",
    "fa"."attname" AS "foreign_column_name",
    "c"."confupdtype" AS "on_update",
    "c"."confdeltype" AS "on_delete",
    "c"."consrc" AS "check_expr"
FROM "pg_class" AS "tc"
INNER JOIN "pg_namespace" AS "tcns"
    ON "tcns"."oid" = "tc"."relnamespace"
INNER JOIN "pg_constraint" AS "c"
    ON "c"."conrelid" = "tc"."oid"
INNER JOIN "pg_attribute" AS "a"
    ON "a"."attrelid" = "c"."conrelid" AND "a"."attnum" = ANY ("c"."conkey")
LEFT JOIN "pg_class" AS "ftc"
    ON "ftc"."oid" = "c"."confrelid"
LEFT JOIN "pg_namespace" AS "ftcns"
    ON "ftcns"."oid" = "ftc"."relnamespace"
LEFT JOIN "pg_attribute" "fa"
    ON "fa"."attrelid" = "c"."confrelid" AND "fa"."attnum" = ANY ("c"."confkey")
WHERE "tcns"."nspname" = :schemaName AND "tc"."relname" = :tableName
ORDER BY "a"."attnum" ASC, "fa"."attnum" ASC
SQL;
        static $actionTypes = [
            'a' => 'NO ACTION',
            'r' => 'RESTRICT',
            'c' => 'CASCADE',
            'n' => 'SET NULL',
            'd' => 'SET DEFAULT',
        ];

        $resolvedName = $this->resolveTableName($tableName);
        $constraints = $this->db->createCommand($sql, [
            ':schemaName' => $resolvedName->schemaName,
            ':tableName' => $resolvedName->name,
        ])->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = ArrayHelper::index($constraints, null, ['type', 'name']);
        $result = [
            'primaryKey' => null,
            'foreignKeys' => [],
            'uniques' => [],
            'checks' => [],
        ];
        foreach ($constraints as $type => $names) {
            foreach ($names as $name => $constraint) {
                switch ($type) {
                    case 'p':
                        $result['primaryKey'] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'f':
                        $result['foreignKeys'][] = new ForeignKeyConstraint([
                            'name' => $name,
                            'columnNames' => array_keys(array_count_values(ArrayHelper::getColumn($constraint, 'column_name'))),
                            'foreignSchemaName' => $constraint[0]['foreign_table_schema'],
                            'foreignTableName' => $constraint[0]['foreign_table_name'],
                            'foreignColumnNames' => array_keys(array_count_values(ArrayHelper::getColumn($constraint, 'foreign_column_name'))),
                            'onDelete' => isset($actionTypes[$constraint[0]['on_delete']]) ? $actionTypes[$constraint[0]['on_delete']] : null,
                            'onUpdate' => isset($actionTypes[$constraint[0]['on_update']]) ? $actionTypes[$constraint[0]['on_update']] : null,
                        ]);
                        break;
                    case 'u':
                        $result['uniques'][] = new Constraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                        ]);
                        break;
                    case 'c':
                        $result['checks'][] = new CheckConstraint([
                            'name' => $name,
                            'columnNames' => ArrayHelper::getColumn($constraint, 'column_name'),
                            'expression' => $constraint[0]['check_expr'],
                        ]);
                        break;
                }
            }
        }
        foreach ($result as $type => $data) {
            $this->setTableMetadata($tableName, $type, $data);
        }

        return $result[$returnType];
    }
}
