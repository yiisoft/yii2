<?php
/**
 * ActiveFinder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\base\Object;
use yii\base\VectorIterator;
use yii\db\dao\Query;
use yii\db\Exception;

/**
 * ActiveFinder.php is ...
 * todo: add SQL monitor
 * todo: better handling on join() support in QueryBuilder: use regexp to detect table name and quote it
 * todo: do not support anonymous parameter binding
 * todo: add ActiveFinderBuilder
 * todo: quote join/on part of the relational query
 * todo: modify QueryBuilder about join() methods
 * todo: unify ActiveFinder and ActiveRelation in query building process
 * todo: intelligent table aliasing (first table name, then relation name, finally t?)
 * todo: allow using tokens in primary query fragments
 * todo: findBySql
 * todo: base limited
 * todo: lazy loading
 * todo: scope
 * todo: test via option
 todo: inner join with one or multiple relations as filters
 joinType should default to inner join in this case
 *
 * @property integer $count
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFinder extends \yii\base\Object
{
	/**
	 * @var \yii\db\dao\Connection
	 */
	public $connection;
	/**
	 * @var ActiveQuery
	 */
	public $query;

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @param \yii\db\ar\ActiveQuery $query
	 * @param bool $all
	 * @return array
	 */
	public function findRecords($query)
	{
		if ($query->sql !== null) {
			$sql = $query->sql;
		} else {
			$this->initFrom($query);
			$this->applyScopes($query);
			$sql = $this->connection->getQueryBuilder()->build($query);
			if (strpos($sql, '@.') !== false) {
				if ($query->tableAlias !== null) {
					$alias = $this->connection->quoteTableName($query->tableAlias) . '.';
				} else {
					$class = $query->modelClass;
					$alias = $this->connection->quoteTableName($class::tableName()) . '.';
				}
				$sql = str_replace('@.', $alias, $sql);
			}
		}
		$command = $this->connection->createCommand($sql, $query->params);

		$rows = $command->queryAll();
		$records = array();
		if ($query->asArray) {
			if ($query->indexBy === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$records[$row[$query->indexBy]] = $row;
			}
		} else {
			$class = $query->modelClass;
			if ($query->indexBy === null) {
				foreach ($rows as $row) {
					$records[] = $class::createRecord($row);
				}
			} else {
				foreach ($rows as $row) {
					$records[$row[$query->indexBy]] = $class::createRecord($row);
				}
			}
		}
		return $records;
	}


	public function findRelatedRecords($record, $relation, $params)
	{

	}

	public function findRecordsWithRelations($query)
	{
		// todo: handle findBySql() and limit cases
		$joinTree = $this->buildRelationalQuery();

		if ($this->sql === null) {
			$this->initFrom($element->query);
			$command = $element->query->createCommand($this->getDbConnection());
			$this->sql = $command->getSql();
		} else {
			$command = $this->getDbConnection()->createCommand($this->sql);
			$command->bindValues($element->query->params);
		}

		$rows = $command->queryAll();

		if (isset($joinTree)) {
			foreach ($rows as $row) {
				$joinTree->populateData($row);
			}
			return array_values($joinTree->records);
		}

		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			$records = array();
			foreach ($rows as $row) {
				$records[$row[$this->indexBy]] = $row;
			}
			return $records;
		} else {
			$records = array();
			$class = $this->modelClass;
			if ($this->indexBy === null) {
				foreach ($rows as $row) {
					$records[] = $class::populateData($row);
				}
			} else {
				$attribute = $this->indexBy;
				foreach ($rows as $row) {
					$record = $class::populateData($row);
					$records[$record->$attribute] = $record;
				}
			}
			return $records;
		}
	}

	protected function initFrom($query)
	{
		if ($query->from === null) {
			$modelClass = $query->modelClass;
			$tableName = $modelClass::tableName();
			if ($query->tableAlias !== null) {
				$tableName .= ' ' . $query->tableAlias;
			}
			$query->from = array($tableName);
		}
	}

	protected function applyScopes($query)
	{
		if (is_array($query->scopes)) {
			$class = $query->modelClass;
			$class::defaultScope($query);
			$scopes = $class::scopes();
			foreach ($query->scopes as $name => $params) {
				if (is_integer($name)) {
					$name = $params;
					$params = array();
				}
				if (!isset($scopes[$name])) {
					throw new Exception("$class has no scope named '$name'.");
				}
				array_unshift($params, $query);
				call_user_func_array($scopes[$name], $params);
			}
		}
	}

	private $_joinCount;
	private $_tableAliases;

	protected function buildQuery()
	{
		$this->_joinCount = 0;
		$joinTree = new JoinElement($this->_joinCount++, $element->query, null, null);
		$this->buildJoinTree($joinTree, $element->query->with);
		$this->_tableAliases = array();
		$this->buildTableAlias($joinTree);

		$query = new Query;
		foreach ($joinTree->children as $child) {
			$child->buildQuery($query);
		}

		$select = $joinTree->buildSelect($element, $element->query->select);
		if (!empty($query->select)) {
			$element->query->select = array_merge($select, $query->select);
		} else {
			$element->query->select = $select;
		}
		if (!empty($query->where)) {
			$element->query->andWhere('(' . implode(') AND (', $query->where) . ')');
		}
		if (!empty($query->having)) {
			$element->query->andHaving('(' . implode(') AND (', $query->having) . ')');
		}
		if (!empty($query->join)) {
			if ($element->query->join === null) {
				$element->query->join = $query->join;
			} else {
				$element->query->join = array_merge($element->query->join, $query->join);
			}
		}
		if (!empty($query->orderBy)) {
			$element->query->addOrderBy($query->orderBy);
		}
		if (!empty($query->groupBy)) {
			$element->query->addGroupBy($query->groupBy);
		}
		if (!empty($query->params)) {
			$element->query->addParams($query->params);
		}

		return $joinTree;
	}

	/**
	 * @param JoinElement $parent
	 * @param array|string $with
	 * @param array $config
	 * @return null|JoinElement
	 * @throws \yii\db\Exception
	 */
	protected function buildJoinTree($parent, $with, $config = array())
	{
		if (is_array($with)) {
			foreach ($with as $name => $value) {
				if (is_array($value)) {
					$this->buildJoinTree($parent, $name, $value);
				} else {
					$this->buildJoinTree($parent, $value);
				}
			}
			return null;
		}

		if (($pos = strrpos($with, '.')) !== false) {
			$parent = $this->buildJoinTree($parent, substr($with, 0, $pos));
			$with = substr($with, $pos + 1);
		}

		if (isset($parent->children[$with])) {
			$child = $parent->children[$with];
			$child->joinOnly = false;
		} else {
			$modelClass = $parent->query->modelClass;
			$relations = $modelClass::getMetaData()->relations;
			if (!isset($relations[$with])) {
				throw new Exception("$modelClass has no relation named '$with'.");
			}
			$relation = clone $relations[$with];
			if ($relation->via !== null && isset($relations[$relation->via])) {
				$parent2 = $this->buildJoinTree($parent, $relation->via);
				$relation->via = null;
				if ($parent2->joinOnly === null) {
					$parent2->joinOnly = true;
				}
				$child = new JoinElement($this->_joinCount++, $relation, $parent2, $parent);
			} else {
				$child = new JoinElement($this->_joinCount++, $relation, $parent, $parent);
			}
		}

		foreach ($config as $name => $value) {
			$child->query->$name = $value;
		}

		return $child;
	}

	/**
	 * @param JoinElement $element
	 */
	protected function buildTableAlias($element)
	{
		if ($element->query->tableAlias !== null) {
			$alias = $element->query->tableAlias;
		} elseif ($element->query instanceof ActiveRelation) {
			$alias = $element->query->name;
		} else {
			$alias = 't';
		}
		$count = 0;
		while (isset($this->_tableAliases[$alias])) {
			$alias = 't' . $count++;
		}
		$this->_tableAliases[$alias] = true;
		$element->query->tableAlias = $alias;

		foreach ($element->children as $child) {
			$this->buildTableAlias($child, $count);
		}
	}

	/**
	 * @param JoinElement $element
	 * @param Query $query
	 */
	protected function buildJoinQuery($element, $query)
	{
		$prefixes = array(
			'@.' => $element->query->tableAlias . '.',
			'?.' => $element->parent->query->tableAlias . '.',
		);
		$quotedPrefixes = array(
			'@.' => $this->connection->quoteTableName($element->query->tableAlias, true) . '.',
			'?.' => $this->connection->quoteTableName($element->parent->query->tableAlias, true) . '.',
		);

		foreach ($this->buildSelect($element, $element->query->select) as $column) {
			$query->select[] = strtr($column, $prefixes);
		}

		if ($element->query->where !== null) {
			$query->where[] = strtr($element->query->where, $quotedPrefixes);
		}

		if ($element->query->having !== null) {
			$query->having[] = strtr($element->query->having, $quotedPrefixes);
		}

		if ($element->query->via !== null) {
			$query->join[] = strtr($element->query->via, $quotedPrefixes);
		}

		if ($element->query->joinType === null) {
			$joinType = $element->query->select === false ? 'INNER JOIN' : 'LEFT JOIN';
		} else {
			$joinType = $element->query->joinType;
		}
		$modelClass = $element->query->modelClass;
		$tableName = $this->connection->quoteTableName($modelClass::tableName());
		$tableAlias = $this->connection->quoteTableName($element->query->tableAlias);
		$join = "$joinType $tableName $tableAlias";
		if ($element->query->on !== null) {
			$join .= ' ON ' . strtr($element->query->on, $quotedPrefixes);
		}
		$query->join[] = $join;

		if ($element->query->join !== null) {
			$query->join[] = strtr($element->query->join, $quotedPrefixes);
		}

		if ($element->query->orderBy !== null) {
			if (!is_array($element->query->orderBy)) {
				$element->query->orderBy = preg_split('/\s*,\s*/', trim($element->query->orderBy), -1, PREG_SPLIT_NO_EMPTY);
			}
			foreach ($element->query->orderBy as $orderBy) {
				$query->orderBy[] = strtr($orderBy, $prefixes);
			}
		}

		if ($element->query->groupBy !== null) {
			if (!is_array($element->query->groupBy)) {
				$element->query->groupBy = preg_split('/\s*,\s*/', trim($element->query->groupBy), -1, PREG_SPLIT_NO_EMPTY);
			}
			foreach ($element->query->groupBy as $groupBy) {
				$query->groupBy[] = strtr($groupBy, $prefixes);
			}
		}

		if ($element->query->params !== null) {
			$query->addParams($element->query->params);
		}

		foreach ($element->children as $child) {
			$this->buildQuery($child, $query);
		}
	}

	protected function buildSelect($element, $select)
	{
		if ($select === false) {
			return array();
		}
		$modelClass = $element->query->modelClass;
		$table = $modelClass::getMetaData()->table;
		$columns = array();
		$columnCount = 0;
		$prefix = $element->query->tableAlias;
		if (empty($select) || $select === '*') {
			foreach ($table->columns as $column) {
				$alias = "t{$element->id}c" . ($columnCount++);
				$columns[] = "$prefix.{$column->name} AS $alias";
				$element->columnAliases[$alias] = $column->name;
				if ($column->isPrimaryKey) {
					$element->pkAlias[$column->name] = $alias;
				}
			}
		} else {
			if (is_string($select)) {
				$select = explode(',', $select);
			}
			foreach ($table->primaryKey as $column) {
				$alias = "t{$element->id}c" . ($columnCount++);
				$columns[] = "$prefix.$column AS $alias";
				$element->pkAlias[$column] = $alias;
			}
			foreach ($select as $column) {
				$column = trim($column);
				if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $matches)) {
					// if the column is already aliased
					$element->columnAliases[$matches[2]] = $matches[2];
					$columns[] = $column;
				} elseif (!isset($element->pkAlias[$column])) {
					$alias = "t{$element->id}c" . ($columnCount++);
					$columns[] = "$prefix.$column AS $alias";
					$element->columnAliases[$alias] = $column;
				}
			}
		}

		return $columns;
	}
}
