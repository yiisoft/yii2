<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\base\InvalidArgumentException;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\Query;
use yii\db\PdoValue;
use yii\helpers\StringHelper;

/**
 * QueryBuilder is the query builder for PostgreSQL databases.
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * Defines a UNIQUE index for [[createIndex()]].
     * @since 2.0.6
     */
    const INDEX_UNIQUE = 'unique';
    /**
     * Defines a B-tree index for [[createIndex()]].
     * @since 2.0.6
     */
    const INDEX_B_TREE = 'btree';
    /**
     * Defines a hash index for [[createIndex()]].
     * @since 2.0.6
     */
    const INDEX_HASH = 'hash';
    /**
     * Defines a GiST index for [[createIndex()]].
     * @since 2.0.6
     */
    const INDEX_GIST = 'gist';
    /**
     * Defines a GIN index for [[createIndex()]].
     * @since 2.0.6
     */
    const INDEX_GIN = 'gin';

    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_UPK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_TINYINT => 'smallint',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'integer',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'double precision',
        Schema::TYPE_DOUBLE => 'double precision',
        Schema::TYPE_DECIMAL => 'numeric(10,0)',
        Schema::TYPE_DATETIME => 'timestamp(0)',
        Schema::TYPE_TIMESTAMP => 'timestamp(0)',
        Schema::TYPE_TIME => 'time(0)',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'bytea',
        Schema::TYPE_BOOLEAN => 'boolean',
        Schema::TYPE_MONEY => 'numeric(19,4)',
        Schema::TYPE_JSON => 'jsonb',
    ];


    /**
     * {@inheritdoc}
     */
    protected function defaultConditionClasses()
    {
        return array_merge(parent::defaultConditionClasses(), [
            'ILIKE' => 'yii\db\conditions\LikeCondition',
            'NOT ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR ILIKE' => 'yii\db\conditions\LikeCondition',
            'OR NOT ILIKE' => 'yii\db\conditions\LikeCondition',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return array_merge(parent::defaultExpressionBuilders(), [
            'yii\db\ArrayExpression' => 'yii\db\pgsql\ArrayExpressionBuilder',
            'yii\db\JsonExpression' => 'yii\db\pgsql\JsonExpressionBuilder',
        ]);
    }

    /**
     * Builds a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns,
     * separate them with commas or use an array to represent them. Each column name will be properly quoted
     * by the method, unless a parenthesis is found in the name.
     * @param bool|string $unique whether to make this a UNIQUE index constraint. You can pass `true` or [[INDEX_UNIQUE]] to create
     * a unique index, `false` to make a non-unique index using the default index type, or one of the following constants to specify
     * the index method to use: [[INDEX_B_TREE]], [[INDEX_HASH]], [[INDEX_GIST]], [[INDEX_GIN]].
     * @return string the SQL statement for creating a new index.
     * @see https://www.postgresql.org/docs/8.2/sql-createindex.html
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($unique === self::INDEX_UNIQUE || $unique === true) {
            $index = false;
            $unique = true;
        } else {
            $index = $unique;
            $unique = false;
        }

        return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ') .
        $this->db->quoteTableName($name) . ' ON ' .
        $this->db->quoteTableName($table) .
        ($index !== false ? " USING $index" : '') .
        ' (' . $this->buildColumns($columns) . ')';
    }

    /**
     * Builds a SQL statement for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @return string the SQL statement for dropping an index.
     */
    public function dropIndex($name, $table)
    {
        if (strpos($table, '.') !== false && strpos($name, '.') === false) {
            if (strpos($table, '{{') !== false) {
                $table = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $table);
                list($schema, $table) = explode('.', $table);
                if (strpos($schema, '%') === false)
                    $name = $schema.'.'.$name;
                else
                    $name = '{{'.$schema.'.'.$name.'}}';
            } else {
                list($schema) = explode('.', $table);
                $name = $schema.'.'.$name;
            }
        }
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $oldName the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable($oldName, $newName)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($oldName) . ' RENAME TO ' . $this->db->quoteTableName($newName);
    }

    /**
     * Creates a SQL statement for resetting the sequence value of a table's primary key.
     * The sequence will be reset such that the primary key of the next new row inserted
     * will have the specified value or 1.
     * @param string $tableName the name of the table whose primary key sequence will be reset
     * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have a value 1.
     * @return string the SQL statement for resetting sequence
     * @throws InvalidArgumentException if the table does not exist or there is no sequence associated with the table.
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            // c.f. https://www.postgresql.org/docs/8.1/functions-sequence.html
            $sequence = $this->db->quoteTableName($table->sequenceName);
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = $this->db->quoteColumnName(reset($table->primaryKey));
                $value = "(SELECT COALESCE(MAX({$key}),0) FROM {$tableName})+1";
            } else {
                $value = (int) $value;
            }

            return "SELECT SETVAL('$sequence',$value,false)";
        } elseif ($table === null) {
            throw new InvalidArgumentException("Table not found: $tableName");
        }

        throw new InvalidArgumentException("There is not sequence associated with table '$tableName'.");
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     * @param string $schema the schema of the tables.
     * @param string $table the table name.
     * @return string the SQL statement for checking integrity
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $enable = $check ? 'ENABLE' : 'DISABLE';
        $schema = $schema ?: $this->db->getSchema()->defaultSchema;
        $tableNames = $table ? [$table] : $this->db->getSchema()->getTableNames($schema);
        $viewNames = $this->db->getSchema()->getViewNames($schema);
        $tableNames = array_diff($tableNames, $viewNames);
        $command = '';

        foreach ($tableNames as $tableName) {
            $tableName = $this->db->quoteTableName("{$schema}.{$tableName}");
            $command .= "ALTER TABLE $tableName $enable TRIGGER ALL; ";
        }

        // enable to have ability to alter several tables
        $this->db->getMasterPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        return $command;
    }

    /**
     * Builds a SQL statement for truncating a DB table.
     * Explicitly restarts identity for PGSQL to be consistent with other databases which all do this by default.
     * @param string $table the table to be truncated. The name will be properly quoted by the method.
     * @return string the SQL statement for truncating a DB table.
     */
    public function truncateTable($table)
    {
        return 'TRUNCATE TABLE ' . $this->db->quoteTableName($table) . ' RESTART IDENTITY';
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[getColumnType()]] method will be invoked to convert abstract
     * column type (if any) into the physical one. Anything that is not recognized as abstract type will be kept
     * in the generated SQL. For example, 'string' will be turned into 'varchar(255)', while 'string not null'
     * will become 'varchar(255) not null'. You can also use PostgreSQL-specific syntax such as `SET NOT NULL`.
     * @return string the SQL statement for changing the definition of a column.
     */
    public function alterColumn($table, $column, $type)
    {
        $columnName = $this->db->quoteColumnName($column);
        $tableName = $this->db->quoteTableName($table);

        // https://github.com/yiisoft/yii2/issues/4492
        // https://www.postgresql.org/docs/9.1/sql-altertable.html
        if (preg_match('/^(DROP|SET|RESET)\s+/i', $type)) {
            return "ALTER TABLE {$tableName} ALTER COLUMN {$columnName} {$type}";
        }

        $type = 'TYPE ' . $this->getColumnType($type);

        $multiAlterStatement = [];
        $constraintPrefix = preg_replace('/[^a-z0-9_]/i', '', $table . '_' . $column);

        if (preg_match('/\s+DEFAULT\s+(["\']?\w*["\']?)/i', $type, $matches)) {
            $type = preg_replace('/\s+DEFAULT\s+(["\']?\w*["\']?)/i', '', $type);
            $multiAlterStatement[] = "ALTER COLUMN {$columnName} SET DEFAULT {$matches[1]}";
        } else {
            // safe to drop default even if there was none in the first place
            $multiAlterStatement[] = "ALTER COLUMN {$columnName} DROP DEFAULT";
        }

        $type = preg_replace('/\s+NOT\s+NULL/i', '', $type, -1, $count);
        if ($count) {
            $multiAlterStatement[] = "ALTER COLUMN {$columnName} SET NOT NULL";
        } else {
            // remove additional null if any
            $type = preg_replace('/\s+NULL/i', '', $type);
            // safe to drop not null even if there was none in the first place
            $multiAlterStatement[] = "ALTER COLUMN {$columnName} DROP NOT NULL";
        }

        if (preg_match('/\s+CHECK\s+\((.+)\)/i', $type, $matches)) {
            $type = preg_replace('/\s+CHECK\s+\((.+)\)/i', '', $type);
            $multiAlterStatement[] = "ADD CONSTRAINT {$constraintPrefix}_check CHECK ({$matches[1]})";
        }

        $type = preg_replace('/\s+UNIQUE/i', '', $type, -1, $count);
        if ($count) {
            $multiAlterStatement[] = "ADD UNIQUE ({$columnName})";
        }

        // add what's left at the beginning
        array_unshift($multiAlterStatement, "ALTER COLUMN {$columnName} {$type}");

        return 'ALTER TABLE ' . $tableName . ' ' . implode(', ', $multiAlterStatement);
    }

    /**
     * {@inheritdoc}
     */
    public function insert($table, $columns, &$params)
    {
        return parent::insert($table, $this->normalizeTableRowData($table, $columns), $params);
    }

    /**
     * {@inheritdoc}
     * @see https://www.postgresql.org/docs/9.5/static/sql-insert.html#SQL-ON-CONFLICT
     * @see https://stackoverflow.com/questions/1109061/insert-on-duplicate-update-in-postgresql/8702291#8702291
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        $insertColumns = $this->normalizeTableRowData($table, $insertColumns);
        if (!is_bool($updateColumns)) {
            $updateColumns = $this->normalizeTableRowData($table, $updateColumns);
        }
        if (version_compare($this->db->getServerVersion(), '9.5', '<')) {
            return $this->oldUpsert($table, $insertColumns, $updateColumns, $params);
        }

        return $this->newUpsert($table, $insertColumns, $updateColumns, $params);
    }

    /**
     * [[upsert()]] implementation for PostgreSQL 9.5 or higher.
     * @param string $table
     * @param array|Query $insertColumns
     * @param array|bool $updateColumns
     * @param array $params
     * @return string
     */
    private function newUpsert($table, $insertColumns, $updateColumns, &$params)
    {
        $insertSql = $this->insert($table, $insertColumns, $params);
        list($uniqueNames, , $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns);
        if (empty($uniqueNames)) {
            return $insertSql;
        }
        if ($updateNames === []) {
            // there are no columns to update
            $updateColumns = false;
        }

        if ($updateColumns === false) {
            return "$insertSql ON CONFLICT DO NOTHING";
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $updateColumns[$name] = new Expression('EXCLUDED.' . $this->db->quoteColumnName($name));
            }
        }
        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        return $insertSql . ' ON CONFLICT (' . implode(', ', $uniqueNames) . ') DO UPDATE SET ' . implode(', ', $updates);
    }

    /**
     * [[upsert()]] implementation for PostgreSQL older than 9.5.
     * @param string $table
     * @param array|Query $insertColumns
     * @param array|bool $updateColumns
     * @param array $params
     * @return string
     */
    private function oldUpsert($table, $insertColumns, $updateColumns, &$params)
    {
        /** @var Constraint[] $constraints */
        list($uniqueNames, $insertNames, $updateNames) = $this->prepareUpsertColumns($table, $insertColumns, $updateColumns, $constraints);
        if (empty($uniqueNames)) {
            return $this->insert($table, $insertColumns, $params);
        }
        if ($updateNames === []) {
            // there are no columns to update
            $updateColumns = false;
        }

        /** @var Schema $schema */
        $schema = $this->db->getSchema();
        if (!$insertColumns instanceof Query) {
            $tableSchema = $schema->getTableSchema($table);
            $columnSchemas = $tableSchema !== null ? $tableSchema->columns : [];
            foreach ($insertColumns as $name => $value) {
                // NULLs and numeric values must be type hinted in order to be used in SET assigments
                // NVM, let's cast them all
                if (isset($columnSchemas[$name])) {
                    $phName = self::PARAM_PREFIX . count($params);
                    $params[$phName] = $value;
                    $insertColumns[$name] = new Expression("CAST($phName AS {$columnSchemas[$name]->dbType})");
                }
            }
        }
        list(, $placeholders, $values, $params) = $this->prepareInsertValues($table, $insertColumns, $params);
        $updateCondition = ['or'];
        $insertCondition = ['or'];
        $quotedTableName = $schema->quoteTableName($table);
        foreach ($constraints as $constraint) {
            $constraintUpdateCondition = ['and'];
            $constraintInsertCondition = ['and'];
            foreach ($constraint->columnNames as $name) {
                $quotedName = $schema->quoteColumnName($name);
                $constraintUpdateCondition[] = "$quotedTableName.$quotedName=\"EXCLUDED\".$quotedName";
                $constraintInsertCondition[] = "\"upsert\".$quotedName=\"EXCLUDED\".$quotedName";
            }
            $updateCondition[] = $constraintUpdateCondition;
            $insertCondition[] = $constraintInsertCondition;
        }
        $withSql = 'WITH "EXCLUDED" (' . implode(', ', $insertNames)
            . ') AS (' . (!empty($placeholders) ? 'VALUES (' . implode(', ', $placeholders) . ')' : ltrim($values, ' ')) . ')';
        if ($updateColumns === false) {
            $selectSubQuery = (new Query())
                ->select(new Expression('1'))
                ->from($table)
                ->where($updateCondition);
            $insertSelectSubQuery = (new Query())
                ->select($insertNames)
                ->from('EXCLUDED')
                ->where(['not exists', $selectSubQuery]);
            $insertSql = $this->insert($table, $insertSelectSubQuery, $params);
            return "$withSql $insertSql";
        }

        if ($updateColumns === true) {
            $updateColumns = [];
            foreach ($updateNames as $name) {
                $quotedName = $this->db->quoteColumnName($name);
                if (strrpos($quotedName, '.') === false) {
                    $quotedName = '"EXCLUDED".' . $quotedName;
                }
                $updateColumns[$name] = new Expression($quotedName);
            }
        }
        list($updates, $params) = $this->prepareUpdateSets($table, $updateColumns, $params);
        $updateSql = 'UPDATE ' . $this->db->quoteTableName($table) . ' SET ' . implode(', ', $updates)
            . ' FROM "EXCLUDED" ' . $this->buildWhere($updateCondition, $params)
            . ' RETURNING ' . $this->db->quoteTableName($table) .'.*';
        $selectUpsertSubQuery = (new Query())
            ->select(new Expression('1'))
            ->from('upsert')
            ->where($insertCondition);
        $insertSelectSubQuery = (new Query())
            ->select($insertNames)
            ->from('EXCLUDED')
            ->where(['not exists', $selectUpsertSubQuery]);
        $insertSql = $this->insert($table, $insertSelectSubQuery, $params);
        return "$withSql, \"upsert\" AS ($updateSql) $insertSql";
    }

    /**
     * {@inheritdoc}
     */
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns), $condition, $params);
    }

    /**
     * Normalizes data to be saved into the table, performing extra preparations and type converting, if necessary.
     *
     * @param string $table the table that data will be saved into.
     * @param array|Query $columns the column data (name => value) to be saved into the table or instance
     * of [[yii\db\Query|Query]] to perform INSERT INTO ... SELECT SQL statement.
     * Passing of [[yii\db\Query|Query]] is available since version 2.0.11.
     * @return array|Query normalized columns
     * @since 2.0.9
     */
    private function normalizeTableRowData($table, $columns)
    {
        if ($columns instanceof Query) {
            return $columns;
        }

        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && is_string($value)) {
                    $columns[$name] = new PdoValue($value, \PDO::PARAM_LOB); // explicitly setup PDO param type for binary column
                }
            }
        }

        return $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function batchInsert($table, $columns, $rows, &$params = [])
    {
        if (empty($rows)) {
            return '';
        }

        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]])) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    $value = StringHelper::floatToString($value);
                } elseif ($value === true) {
                    $value = 'TRUE';
                } elseif ($value === false) {
                    $value = 'FALSE';
                } elseif ($value === null) {
                    $value = 'NULL';
                } elseif ($value instanceof ExpressionInterface) {
                    $value = $this->buildExpression($value, $params);
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }
        if (empty($values)) {
            return '';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }
}
