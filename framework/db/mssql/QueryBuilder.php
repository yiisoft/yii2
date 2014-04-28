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

//	public function update($table, $columns, $condition, &$params)
//	{
//		return '';
//	}

//	public function delete($table, $condition, &$params)
//	{
//		return '';
//	}

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

//	public function resetSequence($table, $value = null)
//	{
//		return '';
//	}

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
     * @param string $type the new column type. The {@link getColumnType} method will be invoked to convert abstract column type (if any)
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
     * Generates a SELECT SQL statement from a [[Query]] object for SQL Server early than 2012.
     * @param Query $query the [[Query]] object from which the SQL statement will be generated.
     * @param array $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array the generated SQL statement (the first array element) and the corresponding
     * parameters to be bound to the SQL statement (the second array element). The parameters returned
     * include those provided in `$params`.
     */
    public function build($query, $params = [])
    {
        $query->prepareBuild($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = [
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
            $this->buildOrderBy($query->orderBy),
            $this->olderMssql()?'':$this->buildLimit($query->limit, $query->offset),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        if ($this->olderMssql()) $sql = $this->applyLimit($sql, $query->limit, $query->offset);

        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "($sql){$this->separator}$union";
        }


        
        return [$sql, $params];
    }
    
    /**
     * This is a port from Prado Framework.
     *
     * Overrides parent implementation. Alters the sql to apply $limit and $offset.
     * The idea for limit with offset is done by modifying the sql on the fly
     * with numerous assumptions on the structure of the sql string.
     * The modification is done with reference to the notes from
     * http://troels.arvin.dk/db/rdbms/#select-limit-offset
     *
     * <code>
     * SELECT * FROM (
     *  SELECT TOP n * FROM (
     *    SELECT TOP z columns      -- (z=n+skip)
     *    FROM tablename
     *    ORDER BY key ASC
     *  ) AS FOO ORDER BY key DESC -- ('FOO' may be anything)
     * ) AS BAR ORDER BY key ASC    -- ('BAR' may be anything)
     * </code>
     *
     * <b>Regular expressions are used to alter the SQL query. The resulting SQL query
     * may be malformed for complex queries.</b> The following restrictions apply
     *
     * <ul>
     *   <li>
     * In particular, <b>commas</b> should <b>NOT</b>
     * be used as part of the ordering expression or identifier. Commas must only be
     * used for separating the ordering clauses.
     *   </li>
     *   <li>
     * In the ORDER BY clause, the column name should NOT be be qualified
     * with a table name or view name. Alias the column names or use column index.
     *   </li>
     *   <li>
     * No clauses should follow the ORDER BY clause, e.g. no COMPUTE or FOR clauses.
     *   </li>
     * </ul>
     *
     * @param string $sql SQL query string.
     * @param integer $limit maximum number of rows, -1 to ignore limit.
     * @param integer $offset row offset, -1 to ignore offset.
     * @return string SQL with limit and offset.
     *
     * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
     */
    public function applyLimit($sql, $limit, $offset)
    {
        $limit = $limit!==null ? (int)$limit : -1;
        $offset = $offset!==null ? (int)$offset : -1;
        if ($limit > 0 && $offset <= 0) //just limit
            $sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 TOP $limit", $sql);
        elseif($limit > 0 && $offset > 0)
            $sql = $this->rewriteLimitOffsetSql($sql, $limit,$offset);
        return $sql;
    }

    /**
     * Rewrite sql to apply $limit > and $offset > 0 for MSSQL database.
     * See http://troels.arvin.dk/db/rdbms/#select-limit-offset
     * @param string $sql sql query
     * @param integer $limit $limit > 0
     * @param integer $offset $offset > 0
     * @return string modified sql query applied with limit and offset.
     *
     * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
     */
    protected function rewriteLimitOffsetSql($sql, $limit, $offset)
    {
        $fetch = $limit+$offset;
        $sql = preg_replace('/^([\s(])*SELECT( DISTINCT)?(?!\s*TOP\s*\()/i',"\\1SELECT\\2 TOP $fetch", $sql);
        $ordering = $this->findOrdering($sql);
        $orginalOrdering = $this->joinOrdering($ordering, '[__outer__]');
        $reverseOrdering = $this->joinOrdering($this->reverseDirection($ordering), '[__inner__]');
        $sql = "SELECT * FROM (SELECT TOP {$limit} * FROM ($sql) as [__inner__] {$reverseOrdering}) as [__outer__] {$orginalOrdering}";
        return $sql;
    }

    /**
     * Base on simplified syntax http://msdn2.microsoft.com/en-us/library/aa259187(SQL.80).aspx
     *
     * @param string $sql $sql
     * @return array ordering expression as key and ordering direction as value
     *
     * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
     */
    protected function findOrdering($sql)
    {
        if(!preg_match('/ORDER BY/i', $sql))
            return array();
        $matches=array();
        $ordering=array();
        preg_match_all('/(ORDER BY)[\s"\[](.*)(ASC|DESC)?(?:[\s"\[]|$|COMPUTE|FOR)/i', $sql, $matches);
        if(count($matches)>1 && count($matches[2]) > 0)
        {
            $parts = explode(',', $matches[2][0]);
            foreach($parts as $part)
            {
                $subs=array();
                if(preg_match_all('/(.*)[\s"\]](ASC|DESC)$/i', trim($part), $subs))
                {
                    if(count($subs) > 1 && count($subs[2]) > 0)
                    {
                        $name='';
                        foreach(explode('.', $subs[1][0]) as $p)
                        {
                            if($name!=='')
                                $name.='.';
                            $name.='[' . trim($p, '[]') . ']';
                        }
                        $ordering[$name] = $subs[2][0];
                    }
                    //else what?
                }
                else
                    $ordering[trim($part)] = 'ASC';
            }
        }

        // replacing column names with their alias names
        foreach($ordering as $name => $direction)
        {
            $matches = array();
            $pattern = '/\s+'.str_replace(array('[',']'), array('\[','\]'), $name).'\s+AS\s+(\[[^\]]+\])/i';
            preg_match($pattern, $sql, $matches);
            if(isset($matches[1]))
            {
                $ordering[$matches[1]] = $ordering[$name];
                unset($ordering[$name]);
            }
        }

        return $ordering;
    }

    /**
     * @param array $orders ordering obtained from findOrdering()
     * @param string $newPrefix new table prefix to the ordering columns
     * @return string concat the orderings
     *
     * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
     */
    protected function joinOrdering($orders, $newPrefix)
    {
        if(count($orders)>0)
        {
            $str=array();
            foreach($orders as $column => $direction)
                $str[] = $column.' '.$direction;
            $orderBy = 'ORDER BY '.implode(', ', $str);
            return preg_replace('/\s+\[[^\]]+\]\.(\[[^\]]+\])/i', ' '.$newPrefix.'.\1', $orderBy);
        }
    }

    /**
     * @param array $orders original ordering
     * @return array ordering with reversed direction.
     *
     * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
     */
    protected function reverseDirection($orders)
    {
        foreach($orders as $column => $direction)
            $orders[$column] = strtolower(trim($direction))==='desc' ? 'ASC' : 'DESC';
        return $orders;
    }
    
    /**
     * Returns if MSSQL version has Limit/Offset support (versions older than 2012).
     * How to determine the version and edition of SQL Server (http://support.microsoft.com/kb/321185)
     * @return boolean if mssql support limit/offset.
     *
     * @author Henrique Dias <heukirne[at]gmail[dot]com>
     */
    protected function olderMssql()
    {
        $this->db->open();
        $version = preg_split("/\./",$this->db->pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
        return $version[0] < 11;
    }
    
}
