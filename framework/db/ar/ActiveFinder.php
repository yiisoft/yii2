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
 * todo: base limited with has_many, bySQL, lazy loading
 * todo: quoting column names in 'on' clause
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

	public function __construct($connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @param ActiveQuery $query
	 */
	public function findRecords($query)
	{
		if (!empty($query->with)) {
			return $this->findRecordsWithRelations($query);
		}

		if ($query->sql !== null) {
			$sql = $query->sql;
		} else {
			if ($query->from === null) {
				$modelClass = $query->modelClass;
				$tableName = $modelClass::tableName();
				if ($query->tableAlias !== null) {
					$tableName .= ' ' . $query->tableAlias;
				}
				$query->from = array($tableName);
			}
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
					$records[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$records[$row[$query->indexBy]] = $class::create($row);
				}
			}
		}
		return $records;
	}


	public function findRelatedRecords($record, $relation, $params)
	{

	}

	private $_joinCount;
	private $_tableAliases;

	/**
	 * @param ActiveQuery $query
	 * @return array
	 */
	public function findRecordsWithRelations($query)
	{
		$this->_joinCount = 0;
		$this->_tableAliases = array();
		$joinTree = new JoinElement($this->_joinCount++, $query, null, null);
		$this->buildJoinTree($joinTree, $query->with);
		$this->initJoinTree($joinTree);

		$q = new Query;
		$this->buildJoinQuery($joinTree, $q);
		$rows = $q->createCommand($this->connection)->queryAll();
		foreach ($rows as $row) {
			$joinTree->createRecord($row);
		}

		return $query->indexBy !== null ? $joinTree->records : array_values($joinTree->records);
	}

	protected function applyScopes($query)
	{
		$class = $query->modelClass;
		$class::defaultScope($query);
		if (is_array($query->scopes)) {
			$scopes = $class::scopes();
			foreach ($query->scopes as $name => $params) {
				if (is_integer($name)) {
					$name = $params;
					$params = array();
				}
				if (isset($scopes[$name])) {
					array_unshift($params, $query);
					call_user_func_array($scopes[$name], $params);
				} else {
					throw new Exception("$class has no scope named '$name'.");
				}
			}
		}
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
	protected function initJoinTree($element)
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

		$this->applyScopes($element->query);

		foreach ($element->children as $child) {
			$this->initJoinTree($child, $count);
		}
	}

	/**
	 * @param JoinElement $element
	 * @param \yii\db\dao\Query $query
	 */
	protected function buildJoinQuery($element, $query)
	{
		if ($element->parent) {
			$prefixes = array(
				'@.' => $element->query->tableAlias . '.',
				'?.' => $element->parent->query->tableAlias . '.',
			);
			$quotedPrefixes = array(
				'@.' => $this->connection->quoteTableName($element->query->tableAlias, true) . '.',
				'?.' => $this->connection->quoteTableName($element->parent->query->tableAlias, true) . '.',
			);
		} else {
			$prefixes = array(
				'@.' => $element->query->tableAlias . '.',
			);
			$quotedPrefixes = array(
				'@.' => $this->connection->quoteTableName($element->query->tableAlias, true) . '.',
			);
		}

		$qb = $this->connection->getQueryBuilder();

		foreach ($this->buildSelect($element, $element->query->select) as $column) {
			$query->select[] = strtr($column, $prefixes);
		}

		if ($element->query instanceof ActiveQuery) {
			if ($element->query->from === null) {
				$modelClass = $element->query->modelClass;
				$tableName = $modelClass::tableName();
				if ($element->query->tableAlias !== null) {
					$tableName .= ' ' . $element->query->tableAlias;
				}
				$query->from = array($tableName);
			} else {
				$query->from = $element->query->from;
			}
		}

		if (($where = $qb->buildCondition($element->query->where)) !== '') {
			$query->andWhere(strtr($where, $quotedPrefixes));
		}

		if (($having = $qb->buildCondition($element->query->having)) !== '') {
			$query->andHaving(strtr($having, $quotedPrefixes));
		}

		if ($element->query instanceof ActiveRelation) {
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
				$join .= ' ON ' . strtr($qb->buildCondition($element->query->on), $quotedPrefixes);
			}
			$query->join[] = $join;
		}

		if ($element->query->join !== null) {
			if (is_array($element->query->join)) {
				foreach ($element->query->join as $join) {
					if (is_array($join) && isset($join[2])) {
						$join[2] = strtr($join[2], $quotedPrefixes);
					}
					$query->join[] = $join;
				}
			} else {
				$query->join[] = strtr($element->query->join, $quotedPrefixes);
			}
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
			$this->buildJoinQuery($child, $query);
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
				$alias = "c{$element->id}_" . ($columnCount++);
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
				$alias = "c{$element->id}_" . ($columnCount++);
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
					$alias = "c{$element->id}_" . ($columnCount++);
					$columns[] = "$prefix.$column AS $alias";
					$element->columnAliases[$alias] = $column;
				}
			}
		}

		return $columns;
	}
}