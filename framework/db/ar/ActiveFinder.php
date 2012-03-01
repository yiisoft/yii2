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
 * todo: count, sum, exists
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
	public function findRecords($query, $all = true)
	{
		if ($query->sql !== null) {
			$sql = $query->sql;
		} else {
			$this->initFrom($query);
			$this->applyScopes($query);
			// todo: filter
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

		if ($all) {
			$rows = $command->queryAll();
		} else {
			$row = $command->queryRow();
			if ($row === false) {
				return array();
			}
			$rows = array($row);
		}

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

	public function findRecordsWithRelations()
	{
		if (!empty($this->with)) {
			// todo: handle findBySql() and limit cases
			$joinTree = $this->buildRelationalQuery();
		}

		if ($this->sql === null) {
			$this->initFrom($this->query);
			$command = $this->query->createCommand($this->getDbConnection());
			$this->sql = $command->getSql();
		} else {
			$command = $this->getDbConnection()->createCommand($this->sql);
			$command->bindValues($this->query->params);
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
			foreach ($query->scopes as $scope => $params) {
				if (is_integer($scope)) {
					$scope = $params;
					$params = array();
				}
				array_unshift($params, $query);
				call_user_func_array($scope, $params);
			}
		}
	}

	protected function buildRelationalQuery()
	{
		$joinTree = new JoinElement($this, null, null);
		$this->buildJoinTree($joinTree, $this->with);
		$this->buildTableAlias($joinTree);
		$query = new Query;
		foreach ($joinTree->children as $child) {
			$child->buildQuery($query);
		}

		$select = $joinTree->buildSelect($this->query->select);
		if (!empty($query->select)) {
			$this->query->select = array_merge($select, $query->select);
		} else {
			$this->query->select = $select;
		}
		if (!empty($query->where)) {
			$this->query->andWhere('(' . implode(') AND (', $query->where) . ')');
		}
		if (!empty($query->having)) {
			$this->query->andHaving('(' . implode(') AND (', $query->having) . ')');
		}
		if (!empty($query->join)) {
			if ($this->query->join === null) {
				$this->query->join = $query->join;
			} else {
				$this->query->join = array_merge($this->query->join, $query->join);
			}
		}
		if (!empty($query->orderBy)) {
			$this->query->addOrderBy($query->orderBy);
		}
		if (!empty($query->groupBy)) {
			$this->query->addGroupBy($query->groupBy);
		}
		if (!empty($query->params)) {
			$this->query->addParams($query->params);
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
				if (is_string($value)) {
					$this->buildJoinTree($parent, $value);
				} elseif (is_string($name) && is_array($value)) {
					$this->buildJoinTree($parent, $name, $value);
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
			$modelClass = $parent->relation->modelClass;
			$relations = $modelClass::getMetaData()->relations;
			if (!isset($relations[$with])) {
				throw new Exception("$modelClass has no relation named '$with'.");
			}
			$relation = clone $relations[$with];
			if ($relation->via !== null && isset($relations[$relation->via])) {
				$relation->via = null;
				$parent2 = $this->buildJoinTree($parent, $relation->via);
				if ($parent2->joinOnly === null) {
					$parent2->joinOnly = true;
				}
				$child = new JoinElement($relation, $parent2, $parent);
			} else {
				$child = new JoinElement($relation, $parent, $parent);
			}
		}

		foreach ($config as $name => $value) {
			$child->relation->$name = $value;
		}

		return $child;
	}

	protected function buildTableAlias($element, &$count = 0)
	{
		if ($element->relation->tableAlias === null) {
			$element->relation->tableAlias = 't' . ($count++);
		}
		foreach ($element->children as $child) {
			$this->buildTableAlias($child, $count);
		}
	}
}
