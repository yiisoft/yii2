<?php
namespace yii\db\oci;

use yii\db\Exception;
use yii\base\InvalidParamException;

/**
 * QueryBuilder is the query builder for Oracle databases.
 * 
 */
class QueryBuilder extends \yii\db\QueryBuilder
{

    private $sql;

    public function build($query)
    {
        // var_dump($query);exit;
        $params = $query->params;
		$clauses = [ 
				$this->buildSelect ( $query->select, $query->distinct, $query->selectOption ),
				$this->buildFrom ( $query->from ),
				$this->buildJoin ( $query->join, $params ),
				$this->buildWhere ( $query->where, $params ),
				$this->buildGroupBy ( $query->groupBy ),
				$this->buildHaving ( $query->having, $params ),
				$this->buildUnion ( $query->union, $params ),
				$this->buildOrderBy ( $query->orderBy ) 
		// $this->buildLimit($query->limit, $query->offset),
		
        ;
        // var_dump( [implode($this->separator, array_filter($clauses)), $params]);exit;
        $this->sql = implode($this->separator, array_filter($clauses));
        
        if (! is_null($query->limit) && ! is_null($query->offset)) {
            $this->sql = $this->buildLimit($query->limit, $query->offset);
        }
        return [
            $this->sql,
            $params
        ];
        // return [implode($this->separator, array_filter($clauses)), $params];
    }

    public function buildLimit($limit, $offset)
    {
        // var_dump($limit >= 0);
        // var_dump($offset);exit;
        // var_dump($limit, $offset);
        if (($limit < 0) && ($offset < 0)) {
            return $this->sql;
        }
        $filters = array();
        if ($offset > 0) {
            $filters[] = 'rowNumId > ' . (int) $offset;
        }
        
        if ($limit >= 0) {
            $filters[] = 'rownum <= ' . (int) $limit;
        }
        
        if (count($filters) > 0) {
            $filter = implode(' and ', $filters);
            $filter = " WHERE " . $filter;
        } else {
            $filter = '';
        }
        
        $sql = <<<EOD
WITH USER_SQL AS ({$this->sql}),
	PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
{$filter}
EOD;
        return $sql;
    }
}
