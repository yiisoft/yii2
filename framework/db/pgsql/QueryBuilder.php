<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\base\InvalidArgumentException;
use yii\db\ArrayExpression;
use yii\db\ColumnSchemaBuilder;
use yii\db\conditions\LikeCondition;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\db\IndexConstraint;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\db\PdoValue;
use yii\helpers\StringHelper;

use function array_diff;
use function array_map;
use function count;
use function gettype;
use function implode;
use function is_bool;
use function is_string;
use function preg_match;
use function preg_replace;
use function str_contains;
use function usort;

/**
 * QueryBuilder is the query builder for PostgreSQL databases (version 14 and above).
 *
 * @author Gevik Babakhani <gevikb@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * Anchored dispatch pattern for standalone PostgreSQL `ALTER COLUMN` action strings emitted verbatim.
     */
    private const string ALTER_COLUMN_ACTION_REGEX = <<<REGEX
    /^(?:
        SET (?: \s+ | \s*\( )
      | DROP \s+
      | RESET \s*\(
      | RESTART (?: \s+ | $ )
      | ADD \s+ GENERATED \s+
    )/ix
    REGEX;
    /**
     * Defines a UNIQUE index for [[createIndex()]].
     * @since 2.0.6
     */
    public const INDEX_UNIQUE = 'unique';
    /**
     * Defines a B-tree index for [[createIndex()]].
     * @since 2.0.6
     */
    public const INDEX_B_TREE = 'btree';
    /**
     * Defines a hash index for [[createIndex()]].
     * @since 2.0.6
     */
    public const INDEX_HASH = 'hash';
    /**
     * Defines a GiST index for [[createIndex()]].
     * @since 2.0.6
     */
    public const INDEX_GIST = 'gist';
    /**
     * Defines a GIN index for [[createIndex()]].
     * @since 2.0.6
     */
    public const INDEX_GIN = 'gin';
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
        return [
            ...parent::defaultConditionClasses(),
            'ILIKE' => LikeCondition::class,
            'NOT ILIKE' => LikeCondition::class,
            'OR ILIKE' => LikeCondition::class,
            'OR NOT ILIKE' => LikeCondition::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultExpressionBuilders()
    {
        return [
            ...parent::defaultExpressionBuilders(),
            ArrayExpression::class => ArrayExpressionBuilder::class,
            JsonExpression::class => JsonExpressionBuilder::class,
        ];
    }

    /**
     * Builds the LIMIT and OFFSET clauses for a SELECT query using the PostgreSQL `OFFSET ... FETCH` syntax.
     *
     * @param int|ExpressionInterface|null $limit the LIMIT value. `null` or a negative value means no limit; `0` is
     * valid and emits `FETCH NEXT 0 ROWS ONLY`.
     * @param int|ExpressionInterface|null $offset the OFFSET value. `null` or `0` means no offset.
     * @return string the LIMIT and OFFSET clauses built for PostgreSQL `14+`.
     */
    public function buildLimit($limit, $offset)
    {
        $sql = '';

        if ($this->hasOffset($offset)) {
            $sql = 'OFFSET ' . $this->buildLimitOffsetValue($offset) . ' ROWS';
        }

        if ($this->hasLimit($limit)) {
            $sql .= ($sql !== '' ? ' ' : '') . 'FETCH NEXT ' . $this->buildLimitOffsetValue($limit) . ' ROWS ONLY';
        }

        return $sql;
    }

    /**
     * Builds a PostgreSQL row-limiting value for `OFFSET` / `FETCH` clauses.
     *
     * PostgreSQL allows scalar expressions in `OFFSET` and `FETCH`, but arithmetic expressions must be enclosed in
     * parentheses to avoid syntax ambiguity (for example, `FETCH NEXT (1 + 1) ROWS ONLY`).
     *
     * Only `int` and {@see Expression} are supported. Other {@see ExpressionInterface} implementations
     * ({@see \yii\db\JsonExpression}, {@see \yii\db\ArrayExpression}, {@see PdoValue}) are not valid row-count values
     * and are not handled here; passing them produces undefined SQL.
     *
     * @param int|Expression $value The row-limiting value.
     * @return string The value SQL.
     */
    protected function buildLimitOffsetValue($value)
    {
        if ($value instanceof Expression) {
            return '(' . (string) $value . ')';
        }

        return (string) $value;
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
     * @see https://www.postgresql.org/docs/current/sql-createindex.html
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
                if (strpos($schema, '%') === false) {
                    $name = $schema . '.' . $name;
                } else {
                    $name = '{{' . $schema . '.' . $name . '}}';
                }
            } else {
                list($schema) = explode('.', $table);
                $name = $schema . '.' . $name;
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
     *
     * The sequence will be reset such that the primary key of the next new row inserted will have the specified value
     * or `1`.
     *
     * @param string $tableName The name of the table whose primary key sequence will be reset.
     * @param int|null $value The integer value for the primary key of the next new row inserted. If this is not set,
     * the next new row's primary key will have a value `1`.
     *
     * @throws InvalidArgumentException if the table does not exist or there is no sequence associated with the table.
     *
     * @return string the SQL statement for resetting sequence.
     */
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            // c.f. https://www.postgresql.org/docs/current/functions-sequence.html
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
     * Builds a SQL statement for enabling or disabling integrity checks.
     *
     * @param bool $check Whether to enable or disable integrity checks.
     * @param string $schema The schema containing the tables.
     * @param string $table The table name. An empty string applies the operation to all tables in the schema.
     *
     * @return string The SQL statement for changing the integrity-check state.
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        /** @var Schema $dbSchema */
        $dbSchema = $this->db->getSchema();

        $action = $check ? 'ENABLE' : 'DISABLE';
        $schema = $schema === '' ? $dbSchema->defaultSchema : $schema;

        $tableNames = $table === '' ? $dbSchema->getTableNames($schema) : [$table];
        $viewNames = $dbSchema->getViewNames($schema);
        $tableNames = array_diff($tableNames, $viewNames);

        $commands = [];

        foreach ($tableNames as $tableName) {
            $tableName = $this->db->quoteTableName("{$schema}.{$tableName}");

            $commands[] = "ALTER TABLE {$tableName} {$action} TRIGGER ALL;";
        }

        if ($commands === []) {
            return '';
        }

        $command = implode("\n", $commands);

        $tag = 'yii';
        $delimiter = "\$$tag\$";

        while (str_contains($command, $delimiter)) {
            $tag .= '_';
            $delimiter = "\$$tag\$";
        }

        return <<<SQL
        DO $delimiter
        BEGIN
        $command
        END;
        $delimiter;
        SQL;
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
     *
     * Supports three input modes for `$type`.
     * - A {@see ColumnSchemaBuilder} produces a structured, multi-action `ALTER TABLE` statement that changes the
     *   column type and applies any nullability, default, `CHECK`, and `UNIQUE` state carried by the builder; `CHECK`
     *   and `UNIQUE` constraints are recreated under stable names derived from the table and column, replacing
     *   same-named constraints.
     * - A string starting with a PostgreSQL per-column action keyword; `SET ...`, `DROP ...`, `RESET (...)`,
     *   `RESTART [WITH n]`, or `ADD GENERATED ...`, is emitted verbatim as a single action; PostgreSQL parses its
     *   contents.
     * - Any other string changes only the column type and no longer implicitly drops `DEFAULT` or `NOT NULL`.
     *
     * @param string $table The table whose column is to be changed. The table name will be properly quoted by the
     * method.
     * @param string $column The name of the column to be changed. The name will be properly quoted by the method.
     * @param ColumnSchemaBuilder|string $type The new column definition. A {@see ColumnSchemaBuilder} carries the
     * structured column state; a string is resolved through {@see getColumnType()} to the physical column type unless
     * it is a `SET`/`DROP`/`RESET`/`RESTART`/`ADD GENERATED` action, which is emitted verbatim.
     *
     * @return string The SQL statement for changing the definition of a column.
     */
    public function alterColumn($table, $column, $type)
    {
        $tableName = $this->db->quoteTableName($table);
        $columnName = $this->db->quoteColumnName($column);

        // https://github.com/yiisoft/yii2/issues/4492
        // https://www.postgresql.org/docs/current/sql-altertable.html
        if (is_string($type) && preg_match(self::ALTER_COLUMN_ACTION_REGEX, $type)) {
            return <<<SQL
            ALTER TABLE {$tableName} ALTER COLUMN {$columnName} {$type}
            SQL;
        }

        if ($type instanceof ColumnSchemaBuilder) {
            return $this->buildAlterColumnFromBuilder($tableName, $columnName, $table, $column, $type);
        }

        $columnType = $this->getColumnType($type);

        return <<<SQL
        ALTER TABLE {$tableName} ALTER COLUMN {$columnName} TYPE {$columnType}
        SQL;
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
     *
     * @see https://www.postgresql.org/docs/current/sql-insert.html#SQL-ON-CONFLICT
     */
    public function upsert($table, $insertColumns, $updateColumns, &$params)
    {
        if (!is_bool($updateColumns)) {
            $updateColumns = $this->normalizeTableRowData($table, $updateColumns);
        }

        $insertSql = $this->insert($table, $insertColumns, $params);

        [$uniqueNames, , $updateNames] = $this->prepareUpsertColumns(
            $table,
            $insertColumns,
            $updateColumns,
            $constraints,
        );

        if ($uniqueNames === []) {
            return $insertSql;
        }

        if ($updateColumns === false || $updateColumns === [] || $updateNames === []) {
            return <<<SQL
            {$insertSql} ON CONFLICT DO NOTHING
            SQL;
        }

        [$updates, $params] = $this->prepareUpsertSets(
            $table,
            $updateColumns,
            $updateNames,
            $params,
            static fn(string $quotedName): string => "EXCLUDED.{$quotedName}",
        );

        $conflictTarget = $this->resolveUpsertConflictTarget($constraints);
        $updateSql = implode(', ', $updates);

        return <<<SQL
        {$insertSql} ON CONFLICT ({$conflictTarget}) DO UPDATE SET {$updateSql}
        SQL;
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

    /**
     * Builds the multi-action `ALTER TABLE` statement for a {@see ColumnSchemaBuilder} column definition.
     *
     * @param string $tableName The quoted table name.
     * @param string $columnName The quoted column name.
     * @param string $table The unquoted table name.
     * @param string $column The unquoted column name.
     * @param ColumnSchemaBuilder $type The column definition builder.
     *
     * @return string The SQL statement for changing the definition of a column.
     */
    private function buildAlterColumnFromBuilder(
        string $tableName,
        string $columnName,
        string $table,
        string $column,
        ColumnSchemaBuilder $type,
    ): string {
        $constraintPrefix = null;

        $default = $type->getDefault();
        $check = $type->getCheck();
        $isUnique = $type->isUnique();

        if ($check !== null || $isUnique) {
            $constraintPrefix = preg_replace('/[^a-z0-9_]/i', '', "{$table}_$column");
        }

        $actions = [];

        // Drop the existing default first so an incompatible old default cannot fail the TYPE change.
        if ($default !== null) {
            $actions[] = <<<SQL
            ALTER COLUMN {$columnName} DROP DEFAULT
            SQL;
        }

        // Drop stale same-named constraints first so they cannot fail the TYPE change.
        if ($check !== null) {
            $actions[] = <<<SQL
            DROP CONSTRAINT IF EXISTS {$constraintPrefix}_check
            SQL;
        }

        if ($isUnique) {
            $actions[] = <<<SQL
            DROP CONSTRAINT IF EXISTS {$constraintPrefix}_key
            SQL;
        }

        $columnType = $this->getColumnType($type->getTypeDefinition());

        $typeAction = <<<SQL
        ALTER COLUMN {$columnName} TYPE {$columnType}
        SQL;

        $append = $type->getAppend();

        if ($append !== null && $append !== '') {
            $typeAction .= " {$append}";
        }

        $actions[] = $typeAction;

        $notNull = $type->isNotNull();

        if ($notNull === true) {
            $actions[] = <<<SQL
            ALTER COLUMN {$columnName} SET NOT NULL
            SQL;
        } elseif ($notNull === false) {
            $actions[] = <<<SQL
            ALTER COLUMN {$columnName} DROP NOT NULL
            SQL;
        }

        if ($default !== null) {
            $defaultValue = $this->buildAlterColumnDefault($default);

            $actions[] = <<<SQL
            ALTER COLUMN {$columnName} SET DEFAULT {$defaultValue}
            SQL;
        }

        if ($check !== null) {
            $actions[] = <<<SQL
            ADD CONSTRAINT {$constraintPrefix}_check CHECK ({$check})
            SQL;
        }

        if ($isUnique) {
            $actions[] = <<<SQL
            ADD CONSTRAINT {$constraintPrefix}_key UNIQUE ({$columnName})
            SQL;
        }

        $actionsSql = implode(', ', $actions);

        return <<<SQL
        ALTER TABLE {$tableName} {$actionsSql}
        SQL;
    }

    /**
     * Renders a {@see ColumnSchemaBuilder} default value as a literal SQL fragment for `SET DEFAULT`.
     *
     * @param mixed $default The default value to render.
     *
     * @return string The SQL fragment for the default value.
     */
    private function buildAlterColumnDefault(mixed $default): string
    {
        if ($default instanceof Expression) {
            return (string) $default;
        }

        return match (gettype($default)) {
            'boolean' => $default ? 'TRUE' : 'FALSE',
            'integer' => (string) $default,
            'double' => StringHelper::floatToString($default),
            default => $this->db->quoteValue((string) $default),
        };
    }

    /**
     * Resolves the `ON CONFLICT` target from the uniqueness constraints matching the inserted columns.
     *
     * @param Constraint[] $constraints the matched uniqueness constraints. Must contain at least one entry.
     * @return string the comma-separated, quoted column list of the selected arbiter constraint.
     *
     * @since 22.0
     */
    private function resolveUpsertConflictTarget(array $constraints): string
    {
        usort(
            $constraints,
            static fn(Constraint $a, Constraint $b): int => [
                !($a instanceof IndexConstraint && $a->isPrimary),
                $a instanceof IndexConstraint,
                count($a->columnNames),
                $a->name,
            ] <=> [
                !($b instanceof IndexConstraint && $b->isPrimary),
                $b instanceof IndexConstraint,
                count($b->columnNames),
                $b->name,
            ],
        );

        return implode(
            ', ',
            array_map(
                [$this->db, 'quoteColumnName'],
                $constraints[0]->columnNames,
            ),
        );
    }
}
