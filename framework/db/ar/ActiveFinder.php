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

	public function __construct($connection, $config = array())
	{
		$this->connection = $connection;
		parent::__construct($config);
	}

	/**
	 * @param ActiveQuery $query
	 */
	public function find($query, $returnScalar = false)
	{
		if (!empty($query->with)) {
			return $this->findWithRelations($query, $returnScalar);
		}

		if ($query->sql !== null) {
			$sql = $query->sql;
		} else {
			$modelClass = $query->modelClass;
			$tableName = $modelClass::tableName();
			if ($query->from === null) {
				if ($query->tableAlias !== null) {
					$tableName .= ' ' . $query->tableAlias;
				}
				$query->from = array($tableName);
			}
			$this->applyScopes($query);
			$sql = $this->connection->getQueryBuilder()->build($query);

			if ($query->tableAlias !== null) {
				$alias = $this->connection->quoteTableName($query->tableAlias) . '.';
			} else {
				$alias = $this->connection->quoteTableName($tableName) . '.';
			}
			$tokens = array(
				'@.' => $alias,
				$this->connection->quoteTableName('@', true) . '.' => $alias,
			);
			$sql = strtr($sql, $tokens);
		}
		$command = $this->connection->createCommand($sql, $query->params);

		if ($returnScalar) {
			return $command->queryScalar();
		} else {
			$rows = $command->queryAll();
			return $this->createRecords($query, $rows);
		}
	}

	private $_joinCount;
	private $_tableAliases;
	private $_hasMany;

	/**
	 * @param ActiveQuery $query
	 * @return array
	 */
	protected function findWithRelations($query, $returnScalar = false)
	{
		$this->_joinCount = 0;
		$this->_tableAliases = array();
		$this->_hasMany = false;
		$joinTree = new JoinElement($this->_joinCount++, $query, null, null);

		if ($query->sql !== null) {
			$command = $this->connection->createCommand($query->sql, $query->params);
			if ($returnScalar) {
				return $command->queryScalar();
			}
			$rows = $command->queryAll();
			$records = $this->createRecords($query, $rows);
			$modelClass = $query->modelClass;
			$table = $modelClass::getMetaData()->table;
			foreach ($records as $record) {
				$pk = array();
				foreach ($table->primaryKey as $name) {
					$pk[] = $record[$name];
				}
				$pk = count($pk) === 1 ? $pk[0] : serialize($pk);
				$joinTree->records[$pk] = $record;
			}

			$q = new ActiveQuery($modelClass);
			$q->with = $query->with;
			$q->tableAlias = 't';
			$q->asArray = $query->asArray;
			$q->index = $query->index;
			$q->select = $table->primaryKey;
			$this->addPkCondition($q, $table, $rows, 't.');
			$joinTree->query = $query = $q;
		}

		$this->buildJoinTree($joinTree, $query->with);
		$this->initJoinTree($joinTree);

		$q = new Query;
		$this->buildJoinQuery($joinTree, $q, $returnScalar);

		if ($returnScalar) {
			return $q->createCommand($this->connection)->queryScalar();
		} else {
			if ($this->_hasMany && ($query->limit > 0 || $query->offset > 0)) {
				$this->limitQuery($query, $q);
			}
			$command = $q->createCommand($this->connection);
			$rows = $command->queryAll();
			$joinTree->populateData($rows);
			return $query->index === null ? array_values($joinTree->records) : $joinTree->records;
		}
	}

	/**
	 * @param ActiveRecord $record
	 * @param ActiveRelation $relation
	 * @return array
	 */
	public function findWithRecord($record, $relation)
	{
		$this->_joinCount = 0;
		$this->_tableAliases = array();
		$this->_hasMany = false;
		$query = new ActiveQuery(get_class($record));
		$modelClass = $query->modelClass;
		$table = $modelClass::getMetaData()->table;
		$query->select = $table->primaryKey;
		$query->limit = $relation->limit;
		$query->offset = $relation->offset;
		$joinTree = new JoinElement($this->_joinCount++, $query, null, null);
		$child = $this->buildJoinTree($joinTree, $relation->name);
		$child->query = $relation;
		$child->container = null;
		$this->buildJoinTree($child, $relation->with);
		$this->initJoinTree($joinTree);

		$pk = $record->getPrimaryKey(true);
		$this->addPkCondition($query, $table, array($pk), $query->tableAlias . '.');

		$q = new Query;
		$this->buildJoinQuery($joinTree, $q);

		if ($this->_hasMany && ($query->limit > 0 || $query->offset > 0)) {
			$this->limitQuery($query, $q);
		}

		$rows = $q->createCommand($this->connection)->queryAll();
		$child->populateData($rows);

		$records = $relation->index === null ? array_values($child->records) : $child->records;
		if ($relation->hasMany) {
			return $records;
		} else {
			return $records === array() ? null : reset($records);
		}
	}

	protected function createRecords($query, $rows)
	{
		$records = array();
		if ($query->asArray) {
			if ($query->index === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$records[$row[$query->index]] = $row;
			}
		} else {
			/** @var $class ActiveRecord */
			$class = $query->modelClass;
			if ($query->index === null) {
				foreach ($rows as $row) {
					$records[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$records[$row[$query->index]] = $class::create($row);
				}
			}
		}
		return $records;
	}

	protected function applyScopes($query)
	{
		/** @var $class ActiveRecord */
		$class = $query->modelClass;
		$class::defaultScope($query);
		if (is_array($query->scopes)) {
			$model = new $class;
			foreach ($query->scopes as $name => $params) {
				if (is_string($params)) {
					// scope name only without parameters
					$name = $params;
					$params = array();
				}
				if (method_exists($class, $name)) {
					array_unshift($params, $query);
					call_user_func_array(array($model, $name), $params);
				} else {
					throw new Exception("$class has no scope named '$name'.");
				}
			}
		}
	}

	/**
	 * @param JoinElement $parent
	 * @param array|string $with
	 * @param array|\Closure $config
	 * @return null|JoinElement
	 * @throws \yii\db\Exception
	 */
	protected function buildJoinTree($parent, $with, $config = array())
	{
		if (empty($with)) {
			return null;
		}
		if (is_array($with)) {
			foreach ($with as $name => $value) {
				if (is_array($value) || $value instanceof \Closure) {
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
		} else {
			$modelClass = $parent->query->modelClass;
			$relations = $modelClass::getMetaData()->relations;
			if (!isset($relations[$with])) {
				throw new Exception("$modelClass has no relation named '$with'.");
			}
			$relation = clone $relations[$with];
			if (is_string($relation->via)) {
				// join via an existing relation
				$parent2 = $this->buildJoinTree($parent, $relation->via);
				if ($parent2->query->select === null) {
					$parent2->query->select = false;
					unset($parent2->container->relations[$parent2->query->name]);
				}
				$child = new JoinElement($this->_joinCount++, $relation, $parent2, $parent);
			} elseif (is_array($relation->via)) {
				// join via a pivoting table
				$r = new ActiveRelation;
				$r->name = 'vt' . $this->_joinCount;
				$r->hasMany = $relation->hasMany;

				foreach ($relation->via as $name => $value) {
					$r->$name = $value;
				}

				$r->select = false;
				if ($r->joinType === null) {
					$r->joinType = $relation->joinType;
				}

				$parent2 = new JoinElement($this->_joinCount++, $r, $parent, $parent);
				$child = new JoinElement($this->_joinCount++, $relation, $parent2, $parent);

			} else {
				$child = new JoinElement($this->_joinCount++, $relation, $parent, $parent);
			}
		}

		if ($config instanceof \Closure) {
			$config($child->query);
		} else {
			foreach ($config as $name => $value) {
				$child->query->$name = $value;
			}
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
		if ($element->query instanceof ActiveRelation) {
			if ($element->query->hasMany) {
				$this->_hasMany = true;
			}
			if ($element->parent->query->asArray !== null && $element->query->asArray === null) {
				$element->query->asArray = $element->parent->query->asArray;
			}
		}
		$count = 0;
		while (isset($this->_tableAliases[$alias])) {
			$alias = 't' . $count++;
		}
		$this->_tableAliases[$alias] = true;
		$element->query->tableAlias = $alias;

		if ($element->records !== array()) {
			$this->applyScopes($element->query);
		}

		if ($element->container !== null && $element->query->asArray === null) {
			$element->query->asArray = $element->container->query->asArray;
		}

		foreach ($element->children as $child) {
			$this->initJoinTree($child);
		}
	}

	/**
	 * @param JoinElement $element
	 * @param \yii\db\dao\Query $query
	 */
	protected function buildJoinQuery($element, $query, $keepSelect = false)
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
			$query->limit = $element->query->limit;
			$query->offset = $element->query->offset;
		}

		$qb = $this->connection->getQueryBuilder();

		if ($keepSelect) {
			if (!empty($element->query->select)) {
				$select = $element->query->select;
				if (is_string($select)) {
					$select = explode(',', $select);
				}
				foreach ($select as $column) {
					$query->select[] = strtr(trim($column), $prefixes);
				}
			}
		} else {
			foreach ($this->buildSelect($element, $element->query->select) as $column) {
				$query->select[] = strtr($column, $prefixes);
			}
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
			$joinType = $element->query->joinType ?: 'LEFT JOIN';
			if ($element->query->modelClass !== null) {
				$modelClass = $element->query->modelClass;
				$tableName = $this->connection->quoteTableName($modelClass::tableName());
			} else {
				$tableName = $this->connection->quoteTableName($element->query->table);
			}
			$tableAlias = $this->connection->quoteTableName($element->query->tableAlias);
			$join = "$joinType $tableName $tableAlias";
			$on = '';
			if (is_array($element->query->link)) {
				foreach ($element->query->link as $pk => $fk) {
					$pk = $quotedPrefixes['@.'] . $this->connection->quoteColumnName($pk, true);
					$fk = $quotedPrefixes['?.'] . $this->connection->quoteColumnName($fk, true);
					if ($on !== '') {
						$on .= ' AND ';
					}
					$on .= "$pk = $fk";
				}
			}
			if ($element->query->on !== null) {
				$condition = strtr($qb->buildCondition($element->query->on), $quotedPrefixes);
				if ($on !== '') {
					$on .= " AND ($condition)";
				} else {
					$on = $condition;
				}
			}
			if ($on !== '') {
				$join .= ' ON ' . $on;
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

		if ($element->query->order !== null) {
			if (!is_array($element->query->order)) {
				$element->query->order = preg_split('/\s*,\s*/', trim($element->query->order), -1, PREG_SPLIT_NO_EMPTY);
			}
			foreach ($element->query->order as $order) {
				$query->order[] = strtr($order, $prefixes);
			}
		}

		if ($element->query->group !== null) {
			if (!is_array($element->query->group)) {
				$element->query->group = preg_split('/\s*,\s*/', trim($element->query->group), -1, PREG_SPLIT_NO_EMPTY);
			}
			foreach ($element->query->group as $group) {
				$query->group[] = strtr($group, $prefixes);
			}
		}

		if ($element->query->params !== null) {
			$query->addParams($element->query->params);
		}

		foreach ($element->children as $child) {
			$this->buildJoinQuery($child, $query, $keepSelect);
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

		foreach ($table->primaryKey as $column) {
			$alias = "c{$element->id}_" . ($columnCount++);
			$columns[] = "$prefix.$column AS $alias";
			$element->pkAlias[$column] = $alias;
			$element->columnAliases[$alias] = $column;
		}

		if (empty($select) || $select === '*') {
			foreach ($table->columns as $column) {
				if (!isset($element->pkAlias[$column->name])) {
					$alias = "c{$element->id}_" . ($columnCount++);
					$columns[] = "$prefix.{$column->name} AS $alias";
					$element->columnAliases[$alias] = $column->name;
				}
			}
		} else {
			if (is_string($select)) {
				$select = explode(',', $select);
			}
			foreach ($select as $column) {
				$column = trim($column);
				if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $matches)) {
					// if the column is already aliased
					$element->columnAliases[$matches[2]] = $matches[2];
					$columns[] = $column;
				} elseif (!isset($element->pkAlias[$column])) {
					$alias = "c{$element->id}_" . ($columnCount++);
					if (strpos($column, '(') !== false) {
						$columns[] = "$column AS $alias";
					} else {
						$columns[] = "$prefix.$column AS $alias";
					}
					$element->columnAliases[$alias] = $column;
				}
			}
		}

		// determine the actual index column(s)
		if ($element->query->index !== null) {
			$index = array_search($element->query->index, $element->columnAliases);
		}
		if (empty($index)) {
			$index = $element->pkAlias;
			if (count($index) === 1) {
				$index = reset($element->pkAlias);
			}
		}
		$element->key = $index;

		return $columns;
	}

	protected function limitQuery($activeQuery, $query)
	{
		$q = clone $query;
		$modelClass = $activeQuery->modelClass;
		$table = $modelClass::getMetaData()->table;
		$q->select = array();
		foreach ($table->primaryKey as $name) {
			$q->select[] = $alias = $activeQuery->tableAlias . '.' . $name;
		}
		$q->distinct = true;
		$rows = $q->createCommand($this->connection)->queryAll();
		$prefix = $activeQuery->tableAlias . '.';
		$this->addPkCondition($query, $table, $rows, $prefix);
		$query->limit = $query->offset = null;
	}

	protected function addPkCondition($query, $table, $rows, $prefix)
	{
		if (count($table->primaryKey) === 1 && count($rows) > 1) {
			$name = $table->primaryKey[0];
			$values = array();
			foreach ($rows as $row) {
				$values[] = $table->columns[$name]->typecast($row[$name]);
			}
			$query->andWhere(array('in', $prefix . $name, $values));
		} else {
			$ors = array('or');
			foreach ($rows as $row) {
				$hash = array();
				foreach ($table->primaryKey as $name) {
					$value = $table->columns[$name]->typecast($row[$name]);
					if (is_string($value)) {
						$value = $this->connection->quoteValue($value);
					}
					$hash[$prefix . $name] = $value;
				}
				$ors[] = $hash;
			}
			$query->andWhere($ors);
		}
	}
}