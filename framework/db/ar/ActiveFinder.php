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
			if ($query->index === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$records[$row[$query->index]] = $row;
			}
		} else {
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


	public function findRelatedRecords($record, $relation, $params)
	{

	}

	private $_joinCount;
	private $_tableAliases;
	private $_hasMany;

	/**
	 * @param ActiveQuery $query
	 * @return array
	 */
	public function findRecordsWithRelations($query)
	{
		$this->_joinCount = 0;
		$this->_tableAliases = array();
		$this->_hasMany = false;
		$joinTree = new JoinElement($this->_joinCount++, $query, null, null);
		$this->buildJoinTree($joinTree, $query->with);
		$this->initJoinTree($joinTree);

		$q = new Query;
		$this->buildJoinQuery($joinTree, $q);
		$rows = $q->createCommand($this->connection)->queryAll();
		foreach ($rows as $row) {
			$joinTree->createRecord($row);
		}

		if ($query->index !== null) {
			$records = array();
			foreach ($joinTree->records as $record) {
				$records[$record[$query->index]] = $record;
			}
			return $records;
		} else {
			return array_values($joinTree->records);
		}
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
			if (is_string($relation->via)) {
				// join via an existing relation
				$parent2 = $this->buildJoinTree($parent, $relation->via);
				if ($parent2->joinOnly === null) {
					$parent2->joinOnly = true;
				}
				$child = new JoinElement($this->_joinCount++, $relation, $parent2, $parent);
			} elseif (is_array($relation->via)) {
				// join via a pivoting table
				$r = new ActiveRelation;
				$r->name = 'pt' . $this->_joinCount;
				$r->hasMany = $relation->hasMany;

				foreach ($relation->via as $name => $value) {
					$r->$name = $value;
				}

				$r->select = false;
				if ($r->joinType === null) {
					$r->joinType = $relation->joinType;
				}

				$parent2 = new JoinElement($this->_joinCount++, $r, $parent, $parent);
				$parent2->joinOnly = true;
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

		if ($element->query->modelClass !== null) {
			$this->applyScopes($element->query);
		}

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