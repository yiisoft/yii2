<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\sqlite;

use Yii;
use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\ColumnSchema;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\ConstraintFinderTrait;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\SqlToken;
use yii\db\TableSchema;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\db\Schema as BaseSchema;

/**
 * Schema is the class for retrieving metadata from a SQLite (2/3) database.
 *
 * @property-write string $transactionIsolationLevel The transaction isolation level to use for this
 * transaction. This can be either [[Transaction::READ_UNCOMMITTED]] or [[Transaction::SERIALIZABLE]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @template T of ColumnSchema = ColumnSchema
 * @extends BaseSchema<T, QueryBuilder>
 */
class Schema extends BaseSchema implements ConstraintFinderInterface
{
    use ConstraintFinderTrait;

    /**
     * @var array mapping from physical column types (keys) to abstract column types (values)
     */
    public $typeMap = [
        'tinyint' => self::TYPE_TINYINT,
        'bit' => self::TYPE_SMALLINT,
        'boolean' => self::TYPE_BOOLEAN,
        'bool' => self::TYPE_BOOLEAN,
        'smallint' => self::TYPE_SMALLINT,
        'mediumint' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'real' => self::TYPE_FLOAT,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'tinytext' => self::TYPE_TEXT,
        'mediumtext' => self::TYPE_TEXT,
        'longtext' => self::TYPE_TEXT,
        'text' => self::TYPE_TEXT,
        'varchar' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        'char' => self::TYPE_CHAR,
        'blob' => self::TYPE_BINARY,
        'datetime' => self::TYPE_DATETIME,
        'year' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'enum' => self::TYPE_STRING,
    ];

    /**
     * @var string the default schema name. In SQLite the schema name is the name of an
     * [attached database](https://www.sqlite.org/lang_attach.html), `main` being the one the connection was
     * opened with.
     * @since 2.0.56
     */
    public $defaultSchema = 'main';

    /**
     * {@inheritdoc}
     */
    protected $tableQuoteCharacter = '`';
    /**
     * {@inheritdoc}
     */
    protected $columnQuoteCharacter = '`';


    /**
     * {@inheritdoc}
     *
     * The returned names are the names of the databases attached to the connection, `main` included.
     * The `temp` database holding temporary tables is skipped as a system schema.
     *
     * @since 2.0.56
     */
    protected function findSchemaNames()
    {
        $databases = $this->db->createCommand('PRAGMA DATABASE_LIST')->queryAll();
        $databases = $this->normalizePdoRowKeyCase($databases, true);

        $schemaNames = [];
        foreach ($databases as $database) {
            if ($database['name'] !== 'temp') {
                $schemaNames[] = $database['name'];
            }
        }

        return $schemaNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function findTableNames($schema = '')
    {
        $sql = 'SELECT DISTINCT tbl_name FROM ' . $this->quoteSchemaPrefix($schema) . 'sqlite_master'
            . " WHERE tbl_name<>'sqlite_sequence' ORDER BY tbl_name";

        return $this->db->createCommand($sql)->queryColumn();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.56
     */
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $this->resolveTableNames($resolvedName, $name);

        return $resolvedName;
    }

    /**
     * Resolves the table name and schema name (if any) of the given table.
     * @param TableSchema $table the table metadata object.
     * @param string $name the table name.
     * @since 2.0.56
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = $this->getTableNameParts($name);
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
        } else {
            $table->schemaName = $this->defaultSchema;
            $table->name = $parts[0];
        }

        $table->fullName = $this->composeFullName($table->name, $table->schemaName);
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
        $resolvedName = $this->resolveTableName($tableName);
        $foreignKeys = $this->db->createCommand(
            $this->pragma('FOREIGN_KEY_LIST', $resolvedName->name, $resolvedName->schemaName)
        )->queryAll();
        $foreignKeys = $this->normalizePdoRowKeyCase($foreignKeys, true);
        $foreignKeys = ArrayHelper::index($foreignKeys, null, 'table');
        ArrayHelper::multisort($foreignKeys, 'seq', SORT_ASC, SORT_NUMERIC);
        $result = [];
        foreach ($foreignKeys as $table => $foreignKey) {
            $result[] = new ForeignKeyConstraint([
                'columnNames' => ArrayHelper::getColumn($foreignKey, 'from'),
                // SQLite foreign keys never cross databases, so the referenced table lives in the same schema
                'foreignSchemaName' => $resolvedName->schemaName,
                'foreignTableName' => $table,
                'foreignColumnNames' => ArrayHelper::getColumn($foreignKey, 'to'),
                'onDelete' => isset($foreignKey[0]['on_delete']) ? $foreignKey[0]['on_delete'] : null,
                'onUpdate' => isset($foreignKey[0]['on_update']) ? $foreignKey[0]['on_update'] : null,
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTableIndexes($tableName)
    {
        return $this->loadTableConstraints($tableName, 'indexes');
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
        $resolvedName = $this->resolveTableName($tableName);
        $sql = $this->db->createCommand(
            'SELECT `sql` FROM ' . $this->quoteSchemaPrefix($resolvedName->schemaName) . '`sqlite_master`'
            . ' WHERE name = :tableName',
            [':tableName' => $resolvedName->name]
        )->queryScalar();
        /** @var SqlToken[]|SqlToken[][]|SqlToken[][][] $code */
        $code = (new SqlTokenizer($sql))->tokenize();
        $pattern = (new SqlTokenizer('any CREATE any TABLE any()'))->tokenize();
        if (!$code[0]->matches($pattern, 0, $firstMatchIndex, $lastMatchIndex)) {
            return [];
        }

        $createTableToken = $code[0][$lastMatchIndex - 1];
        $result = [];
        $offset = 0;
        while (true) {
            $pattern = (new SqlTokenizer('any CHECK()'))->tokenize();
            if (!$createTableToken->matches($pattern, $offset, $firstMatchIndex, $offset)) {
                break;
            }

            $checkSql = $createTableToken[$offset - 1]->getSql();
            $name = null;
            $pattern = (new SqlTokenizer('CONSTRAINT any'))->tokenize();
            if (isset($createTableToken[$firstMatchIndex - 2]) && $createTableToken->matches($pattern, $firstMatchIndex - 2)) {
                $name = $createTableToken[$firstMatchIndex - 1]->content;
            }
            $result[] = new CheckConstraint([
                'name' => $name,
                'expression' => $checkSql,
            ]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException if this method is called.
     */
    protected function loadTableDefaultValues($tableName)
    {
        throw new NotSupportedException('SQLite does not support default value constraints.');
    }

    /**
     * Creates a query builder for the MySQL database.
     * This method may be overridden by child classes to create a DBMS-specific query builder.
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return Yii::createObject(QueryBuilder::className(), [$this->db]);
    }

    /**
     * {@inheritdoc}
     * @return ColumnSchemaBuilder column schema builder instance
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return Yii::createObject(ColumnSchemaBuilder::className(), [$type, $length]);
    }

    /**
     * Collects the table column metadata.
     * @param TableSchema $table the table metadata
     * @return bool whether the table exists in the database
     */
    protected function findColumns($table)
    {
        $sql = $this->pragma('table_info', $table->name, $table->schemaName);
        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
            }
        }
        if (count($table->primaryKey) === 1 && !strncasecmp($table->columns[$table->primaryKey[0]]->dbType, 'int', 3)) {
            $table->sequenceName = '';
            $table->columns[$table->primaryKey[0]]->autoIncrement = true;
        }

        return true;
    }

    /**
     * Collects the foreign key column details for the given table.
     * @param TableSchema $table the table metadata
     */
    protected function findConstraints($table)
    {
        $sql = $this->pragma('foreign_key_list', $table->name, $table->schemaName);
        $keys = $this->db->createCommand($sql)->queryAll();
        foreach ($keys as $key) {
            $id = (int) $key['id'];
            if (!isset($table->foreignKeys[$id])) {
                // SQLite foreign keys never cross databases, so the referenced table lives in the same schema
                $foreignTableName = $this->composeFullName($key['table'], $table->schemaName);
                $table->foreignKeys[$id] = [$foreignTableName, $key['from'] => $key['to']];
            } else {
                // composite FK
                $table->foreignKeys[$id][$key['from']] = $key['to'];
            }
        }
    }

    /**
     * Returns all unique indexes for the given table.
     *
     * Each array element is of the following structure:
     *
     * ```
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
        $sql = $this->pragma('index_list', $table->name, $table->schemaName);
        $indexes = $this->db->createCommand($sql)->queryAll();
        $uniqueIndexes = [];

        foreach ($indexes as $index) {
            $indexName = $index['name'];
            $indexInfo = $this->db->createCommand(
                $this->pragma('index_info', $index['name'], $table->schemaName)
            )->queryAll();

            if ($index['unique']) {
                $uniqueIndexes[$indexName] = [];
                foreach ($indexInfo as $row) {
                    $uniqueIndexes[$indexName][] = $row['name'];
                }
            }
        }

        return $uniqueIndexes;
    }

    /**
     * Loads the column information into a [[ColumnSchema]] object.
     * @param array $info column information
     * @return T the column schema object
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();
        $column->name = $info['name'];
        $column->allowNull = !$info['notnull'];
        $column->isPrimaryKey = $info['pk'] != 0;

        $column->dbType = strtolower($info['type']);
        $column->unsigned = strpos($column->dbType, 'unsigned') !== false;

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = strtolower($matches[1]);
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }

            if (!empty($matches[2])) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int) $values[0];
                if (isset($values[1])) {
                    $column->scale = (int) $values[1];
                }
                if ($column->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
                    $column->type = 'boolean';
                } elseif ($type === 'bit') {
                    if ($column->size > 32) {
                        $column->type = 'bigint';
                    } elseif ($column->size === 32) {
                        $column->type = 'integer';
                    }
                }
            }
        }
        $column->phpType = $this->getColumnPhpType($column);

        if (!$column->isPrimaryKey) {
            if ($info['dflt_value'] === 'null' || $info['dflt_value'] === '' || $info['dflt_value'] === null) {
                $column->defaultValue = null;
            } elseif ($column->type === 'timestamp' && $info['dflt_value'] === 'CURRENT_TIMESTAMP') {
                $column->defaultValue = new Expression('CURRENT_TIMESTAMP');
            } else {
                $value = trim($info['dflt_value'], "'\"");
                $column->defaultValue = $column->phpTypecast($value);
            }
        }

        return $column;
    }

    /**
     * Sets the isolation level of the current transaction.
     * @param string $level The transaction isolation level to use for this transaction.
     * This can be either [[Transaction::READ_UNCOMMITTED]] or [[Transaction::SERIALIZABLE]].
     * @throws NotSupportedException when unsupported isolation levels are used.
     * SQLite only supports SERIALIZABLE and READ UNCOMMITTED.
     * @see https://www.sqlite.org/pragma.html#pragma_read_uncommitted
     */
    public function setTransactionIsolationLevel($level)
    {
        switch ($level) {
            case Transaction::SERIALIZABLE:
                $this->db->createCommand('PRAGMA read_uncommitted = False;')->execute();
                break;
            case Transaction::READ_UNCOMMITTED:
                $this->db->createCommand('PRAGMA read_uncommitted = True;')->execute();
                break;
            default:
                throw new NotSupportedException(get_class($this) . ' only supports transaction isolation levels READ UNCOMMITTED and SERIALIZABLE.');
        }
    }

    /**
     * Returns table columns info.
     * @param string $tableName table name
     * @param string|null $schemaName the schema (attached database) the table belongs to.
     * @return array
     */
    private function loadTableColumnsInfo($tableName, $schemaName = null)
    {
        $tableColumns = $this->db->createCommand($this->pragma('TABLE_INFO', $tableName, $schemaName))->queryAll();
        $tableColumns = $this->normalizePdoRowKeyCase($tableColumns, true);

        return ArrayHelper::index($tableColumns, 'cid');
    }

    /**
     * Loads multiple types of constraints and returns the specified ones.
     * @param string $tableName table name.
     * @param string $returnType return type:
     * - primaryKey
     * - indexes
     * - uniques
     * @return mixed constraints.
     */
    private function loadTableConstraints($tableName, $returnType)
    {
        $resolvedName = $this->resolveTableName($tableName);
        $schemaName = $resolvedName->schemaName;
        $indexes = $this->db->createCommand(
            $this->pragma('INDEX_LIST', $resolvedName->name, $schemaName)
        )->queryAll();
        $indexes = $this->normalizePdoRowKeyCase($indexes, true);
        $tableColumns = null;
        if (!empty($indexes) && !isset($indexes[0]['origin'])) {
            /*
             * SQLite may not have an "origin" column in INDEX_LIST
             * See https://www.sqlite.org/src/info/2743846cdba572f6
             */
            $tableColumns = $this->loadTableColumnsInfo($resolvedName->name, $schemaName);
        }
        $result = [
            'primaryKey' => null,
            'indexes' => [],
            'uniques' => [],
        ];
        foreach ($indexes as $index) {
            $columns = $this->db->createCommand(
                $this->pragma('INDEX_INFO', $index['name'], $schemaName)
            )->queryAll();
            $columns = $this->normalizePdoRowKeyCase($columns, true);
            ArrayHelper::multisort($columns, 'seqno', SORT_ASC, SORT_NUMERIC);
            if ($tableColumns !== null) {
                // SQLite may not have an "origin" column in INDEX_LIST
                $index['origin'] = 'c';
                if (!empty($columns) && $tableColumns[$columns[0]['cid']]['pk'] > 0) {
                    $index['origin'] = 'pk';
                } elseif ($index['unique'] && $this->isSystemIdentifier($index['name'])) {
                    $index['origin'] = 'u';
                }
            }
            $result['indexes'][] = new IndexConstraint([
                'isPrimary' => $index['origin'] === 'pk',
                'isUnique' => (bool) $index['unique'],
                'name' => $index['name'],
                'columnNames' => ArrayHelper::getColumn($columns, 'name'),
            ]);
            if ($index['origin'] === 'u') {
                $result['uniques'][] = new Constraint([
                    'name' => $index['name'],
                    'columnNames' => ArrayHelper::getColumn($columns, 'name'),
                ]);
            } elseif ($index['origin'] === 'pk') {
                $result['primaryKey'] = new Constraint([
                    'columnNames' => ArrayHelper::getColumn($columns, 'name'),
                ]);
            }
        }

        if ($result['primaryKey'] === null) {
            /*
             * Additional check for PK in case of INTEGER PRIMARY KEY with ROWID
             * See https://www.sqlite.org/lang_createtable.html#primkeyconst
             */
            if ($tableColumns === null) {
                $tableColumns = $this->loadTableColumnsInfo($resolvedName->name, $schemaName);
            }
            foreach ($tableColumns as $tableColumn) {
                if ($tableColumn['pk'] > 0) {
                    $result['primaryKey'] = new Constraint([
                        'columnNames' => [$tableColumn['name']],
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

    /**
     * {@inheritdoc}
     *
     * Only the dots that separate the identifiers are split on, so a schema or a table name that holds a dot keeps
     * it as long as it is quoted. The quotes are stripped off the parts that are returned, a doubled quote character
     * standing for a literal one.
     *
     * @since 2.0.56
     */
    protected function getTableNameParts($name)
    {
        $parts = [];
        $part = '';
        $quoteCharacter = null;
        for ($i = 0, $length = strlen($name); $i < $length; $i++) {
            $character = $name[$i];
            if ($quoteCharacter !== null) {
                if ($character !== $quoteCharacter) {
                    $part .= $character;
                } elseif (isset($name[$i + 1]) && $name[$i + 1] === $quoteCharacter) {
                    $part .= $character;
                    $i++;
                } else {
                    $quoteCharacter = null;
                }
            } elseif ($character === '`' || $character === '"') {
                $quoteCharacter = $character;
            } elseif ($character === '.') {
                $parts[] = $part;
                $part = '';
            } else {
                $part .= $character;
            }
        }
        $parts[] = $part;

        return $parts;
    }

    /**
     * Builds a `PRAGMA` statement, qualifying it with the schema name when it is not the default one.
     * @param string $name the pragma name, e.g. `TABLE_INFO`.
     * @param string $argument the pragma argument, e.g. a table or an index name.
     * @param string|null $schemaName the schema (attached database) name. `null`, an empty string or
     * [[defaultSchema]] means no qualification is needed.
     * @return string the `PRAGMA` statement.
     * @since 2.0.56
     */
    private function pragma($name, $argument, $schemaName = null)
    {
        return 'PRAGMA ' . $this->quoteSchemaPrefix($schemaName) . $name . ' (' . $this->quoteValue($argument) . ')';
    }

    /**
     * Returns the quoted schema name followed by a dot, to be used as a prefix of a table name or a pragma name.
     * @param string|null $schemaName the schema (attached database) name. `null`, an empty string or
     * [[defaultSchema]] result in an empty prefix, since those all refer to the default schema.
     * @return string the prefix, an empty string when no qualification is needed.
     * @since 2.0.56
     */
    private function quoteSchemaPrefix($schemaName)
    {
        if ($schemaName === null || $schemaName === '' || $schemaName === $this->defaultSchema) {
            return '';
        }

        return $this->quoteIdentifier($schemaName) . '.';
    }

    /**
     * Quotes an identifier, doubling the quote characters it holds.
     *
     * Unlike [[quoteSimpleTableName()]] this does not leave a name that already holds a quote character alone,
     * which would let it break out of the quoting.
     *
     * @param string $identifier the identifier to quote.
     * @return string the quoted identifier.
     * @since 2.0.56
     */
    private function quoteIdentifier($identifier)
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Composes the full name of a table out of its name and the schema it belongs to, the default schema being
     * left out.
     *
     * A part holding a dot or a quote character is quoted, so that the result can be handed back to
     * [[getTableSchema()]] and its like without being split at the wrong dot.
     *
     * @param string $tableName the table name without a schema prefix.
     * @param string|null $schemaName the schema (attached database) name.
     * @return string the full name of the table.
     * @since 2.0.56
     */
    private function composeFullName($tableName, $schemaName)
    {
        $fullName = $this->quoteAmbiguousIdentifier($tableName);
        if ($schemaName !== null && $schemaName !== '' && $schemaName !== $this->defaultSchema) {
            $fullName = $this->quoteAmbiguousIdentifier($schemaName) . '.' . $fullName;
        }

        return $fullName;
    }

    /**
     * Quotes an identifier that [[getTableNameParts()]] would otherwise not read back as a single part, leaving
     * the ordinary ones untouched.
     * @param string $identifier the identifier to quote.
     * @return string the identifier, quoted only when it holds a dot or a quote character.
     * @since 2.0.56
     */
    private function quoteAmbiguousIdentifier($identifier)
    {
        if (strpbrk($identifier, '.`"') === false) {
            return $identifier;
        }

        return $this->quoteIdentifier($identifier);
    }

    /**
     * Return whether the specified identifier is a SQLite system identifier.
     * @param string $identifier
     * @return bool
     * @see https://www.sqlite.org/src/artifact/74108007d286232f
     */
    private function isSystemIdentifier($identifier)
    {
        return strncmp($identifier, 'sqlite_', 7) === 0;
    }

    /**
     * @inheritdoc
     *
     * Since PHP 8.5, `PDO::quote()` throws a ValueError when the string contains null bytes ("\0").
     *
     * This method sanitizes such bytes before calling the parent implementation to avoid exceptions while maintaining
     * backward compatibility.
     *
     * @link https://github.com/php/php-src/commit/0a10f6db26875e0f1d0f867307cee591d29a43c7
     */
    public function quoteValue($value)
    {
        if (PHP_VERSION_ID >= 80500 && is_string($value) && str_contains($value, "\0")) {
            // Sanitize null bytes to prevent PDO ValueError on PHP 8.5+
            $value = str_replace("\0", '', $value);
        }

        return parent::quoteValue($value);
    }
}
