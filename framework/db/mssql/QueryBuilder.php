<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for MS SQL Server databases (version 2008 and above).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{
    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK => 'int IDENTITY PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigint IDENTITY PRIMARY KEY',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'int',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DECIMAL => 'decimal',
        Schema::TYPE_DATETIME => 'datetime',
        Schema::TYPE_TIMESTAMP => 'timestamp',
        Schema::TYPE_TIME => 'time',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'binary',
        Schema::TYPE_BOOLEAN => 'bit',
        Schema::TYPE_MONEY => 'decimal(19,4)',
    ];


    /**
     * @param integer $limit
     * @param integer $offset
     * @return string the LIMIT and OFFSET clauses built from [[\yii\db\Query::$limit]].
     */
    public function buildLimit($limit, $offset = 0)
    {
        $hasOffset = $this->hasOffset($offset);
        $hasLimit = $this->hasLimit($limit);
        if ($hasOffset || $hasLimit) {
            // http://technet.microsoft.com/en-us/library/gg699618.aspx
            $sql = 'OFFSET ' . ($hasOffset ? $offset : '0') . ' ROWS';
            if ($hasLimit) {
                $sql .= " FETCH NEXT $limit ROWS ONLY";
            }

            return $sql;
        } else {
            return '';
        }
    }

    /**
     * Builds a SQL statement for renaming a DB table.
     * @param string $table the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB table.
     */
    public function renameTable($table, $newName)
    {
        return "sp_rename '$table', '$newName'";
    }

    /**
     * Builds a SQL statement for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $name the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @return string the SQL statement for renaming a DB column.
     */
    public function renameColumn($table, $name, $newName)
    {
        return "sp_rename '$table.$name', '$newName', 'COLUMN'";
    }

    /**
     * Builds a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[getColumnType]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @return string the SQL statement for changing the definition of a column.
     */
    public function alterColumn($table, $column, $type)
    {
        $type = $this->getColumnType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
            . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type);

        return $sql;
    }

    /**
     * Builds a SQL statement for enabling or disabling integrity check.
     * @param boolean $check whether to turn on or off the integrity check.
     * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
     * @param string $table the table name. Defaults to empty string, meaning that no table will be changed.
     * @return string the SQL statement for checking integrity
     * @throws InvalidParamException if the table does not exist or there is no sequence associated with the table.
     */
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        if ($schema !== '') {
            $table = "{$schema}.{$table}";
        }
        $table = $this->db->quoteTableName($table);
        if ($this->db->getTableSchema($table) === null) {
            throw new InvalidParamException("Table not found: $table");
        }
        $enable = $check ? 'CHECK' : 'NOCHECK';

        return "ALTER TABLE {$table} {$enable} CONSTRAINT ALL";
    }

    /**
     * @inheritdoc
     */
    public function build($query, $params = [])
    {
        $query->prepareBuild($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $orderBy = $this->buildOrderBy($query->orderBy);
        if ($orderBy === '' && ($this->hasOffset($query->offset) || $this->hasLimit($query->limit)) && !$this->isOldMssql()) {
            // ORDER BY clause is required when FETCH and OFFSET are in the SQL
            $orderBy = 'ORDER BY (SELECT NULL)';
        }

        $clauses = [
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
            $orderBy,
            $this->isOldMssql() ? '' : $this->buildLimit($query->limit, $query->offset),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        if ($this->isOldMssql()) {
            $sql = $this->applyLimitAndOffset($sql, $query);
        }
        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "($sql){$this->separator}$union";
        }

        return [$sql, $params];
    }

    /**
     * Applies limit and offset to SQL query
     *
     * @param string $sql SQL query
     * @param \yii\db\Query $query the [[Query]] object from which the SQL statement generated
     * @return string resulting SQL
     */
    private function applyLimitAndOffset($sql, $query)
    {
        $limit = $query->limit !== null ? (int)$query->limit : -1;
        $offset = $query->offset !== null ? (int)$query->offset : -1;
        if ($limit > 0 || $offset >= 0) {
            $sql = $this->rewriteLimitOffsetSql($sql, $limit, $offset, $query);
        }
        return $sql;
    }

    /**
     * Rewrites limit and offset in SQL query
     *
     * @param string $sql SQL query
     * @param integer $limit
     * @param integer $offset
     * @param \yii\db\Query $query the [[Query]] object from which the SQL statement generated
     * @return string resulting SQL query
     */
    private function rewriteLimitOffsetSql($sql, $limit, $offset, $query)
    {
        $originalOrdering = $this->buildOrderBy($query->orderBy);
        if ($query->select) {
            $select = implode(', ', $query->select);
        } else {
            $select = $query->select = '*';
        }
        if ($select === '*') {
            $columns = $this->getAllColumnNames($query->modelClass);
            if ($columns && is_array($columns)) {
                $select = implode(', ', $columns);
            } else {
                $select = $columns;
            }
        }
        $sql = str_replace($originalOrdering, '', $sql);

        if ($originalOrdering === '') {
            // hack so LIMIT will work because ROW_NUMBER requires an ORDER BY clause
            $originalOrdering = 'ORDER BY (SELECT NULL)';
        }

        $sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i', "\\1SELECT\\2 rowNum = ROW_NUMBER() over ({$originalOrdering}),", $sql);
        $sql = "SELECT TOP {$limit} {$select} FROM ($sql) sub WHERE rowNum > {$offset}";
        return $sql;
    }

    /**
     * Returns an array of column names given model name
     *
     * @param string $modelClass name of the model class
     * @return array|null array of column names
     */
    protected function getAllColumnNames($modelClass = null)
    {
        if (!$modelClass) {
            return null;
        }
        /* @var $model \yii\db\ActiveRecord */
        $model = new $modelClass;
        $schema = $model->getTableSchema();
        $columns = array_keys($schema->columns);
        return $columns;
    }

    /**
     * @var boolean whether MSSQL used is old.
     */
    private $_oldMssql;

    /**
     * @return boolean whether MSSQL used is old.
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function isOldMssql()
    {
        if ($this->_oldMssql === null) {
            $pdo = $this->db->getSlavePdo();
            $version = preg_split("/\./", $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION));
            $this->_oldMssql = $version[0] < 11;
        }
        return $this->_oldMssql;
    }
}
