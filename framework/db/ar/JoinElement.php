<?php
/**
 * JoinElement class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\base\VectorIterator;
use yii\db\dao\Query;
use yii\db\Exception;


class JoinElement extends \yii\base\Object
{
	/**
	 * @var integer ID of this join element
	 */
	public $id;
	/**
	 * @var BaseActiveQuery
	 */
	public $query;
	/**
	 * @var JoinElement the parent element that this element needs to join with
	 */
	public $parent;
	/**
	 * @var JoinElement[] the child elements that need to join with this element
	 */
	public $children = array();
	/**
	 * @var JoinElement[] the child elements that have relations declared in the AR class of this element
	 */
	public $relations = array();
	/**
	 * @var boolean whether this element is only for join purpose. If false, data will also be populated into the AR of this element.
	 */
	public $joinOnly;

	public $columnAliases = array(); // alias => original name
	public $pkAlias = array(); // original name => alias

	public $records;
	public $relatedRecords;

	/**
	 * @param ActiveRelation|ActiveQuery $query
	 * @param JoinElement $parent
	 * @param JoinElement $container
	 */
	public function __construct($id, $query, $parent, $container)
	{
		$this->id = $id;
		$this->query = $query;
		if ($parent !== null) {
			$this->parent = $parent;
			$parent->children[$query->name] = $this;
			$container->relations[$query->name] = $this;
		}
	}

	/**
	 * @param array $row
	 * @return null|ActiveRecord
	 */
	public function createRecord($row)
	{
		$pk = array();
		foreach ($this->pkAlias as $alias) {
			if (isset($row[$alias])) {
				$pk[] = $row[$alias];
			} else {
				return null;
			}
		}
		$pk = count($pk) === 1 ? $pk[0] : serialize($pk);

		// create record
		if (isset($this->records[$pk])) {
			$record = $this->records[$pk];
		} else {
			$attributes = array();
			foreach ($row as $alias => $value) {
				if (isset($this->columnAliases[$alias])) {
					$attributes[$this->columnAliases[$alias]] = $value;
				}
			}
			$modelClass = $this->query->modelClass;
			$this->records[$pk] = $record = $modelClass::create($attributes);
			foreach ($this->children as $child) {
				if ($child->query->select !== false || $child->joinOnly) {
					$record->initRelation($child->query);
				}
			}
		}

		// add related records
		foreach ($this->relations as $child) {
			if ($child->query->select === false || $child->joinOnly) {
				continue;
			}
			$childRecord = $child->createRecord($row);
			if ($childRecord === null) {
				continue;
			}
			if ($child->query->hasMany) {
				if ($child->query->indexBy !== null) {
					$hash = $childRecord->{$child->query->indexBy};
				} else {
					$hash = serialize($childRecord->getPrimaryKey());
				}
				if (!isset($this->relatedRecords[$pk][$child->query->name][$hash])) {
					$this->relatedRecords[$pk][$child->query->name][$hash] = true;
					$record->addRelatedRecord($child->query, $childRecord);
				}
			} else {
				$record->addRelatedRecord($child->query, $childRecord);
			}
		}

		return $record;
	}

	public function buildQuery($query)
	{
		$prefixes = array(
			'@.' => $this->query->tableAlias . '.',
			'?.' => $this->parent->query->tableAlias . '.',
		);
		$quotedPrefixes = '';
		foreach ($this->buildSelect($this->query->select) as $column) {
			$query->select[] = strtr($column, $prefixes);
		}

		if ($this->query->where !== null) {
			$query->where[] = strtr($this->query->where, $prefixes);
		}

		if ($this->query->having !== null) {
			$query->having[] = strtr($this->query->having, $prefixes);
		}

		if ($this->query->via !== null) {
			$query->join[] = $this->query->via;
		}

		$modelClass = $this->query->modelClass;
		$tableName = $modelClass::tableName();
		$joinType = $this->query->joinType === null ? 'LEFT JOIN' : $this->query->joinType;
		$join = "$joinType $tableName {$this->query->tableAlias}";
		if ($this->query->on !== null) {
			$join .= ' ON ' . strtr($this->query->on, $prefixes);
		}
		$query->join[] = $join;


		if ($this->query->join !== null) {
			$query->join[] = strtr($this->query->join, $prefixes);
		}

		// todo: convert orderBy to array first
		if ($this->query->orderBy !== null) {
			$query->orderBy[] = strtr($this->query->orderBy, $prefixes);
		}

		// todo: convert groupBy to array first
		if ($this->query->groupBy !== null) {
			$query->groupBy[] = strtr($this->query->groupBy, $prefixes);
		}

		if ($this->query->params !== null) {
			foreach ($this->query->params as $name => $value) {
				if (is_integer($name)) {
					$query->params[] = $value;
				} else {
					$query->params[$name] = $value;
				}
			}
		}

		foreach ($this->children as $child) {
			$child->buildQuery($query);
		}
	}

	public function buildSelect($select)
	{
		if ($select === false) {
			return array();
		}
		$modelClass = $this->query->modelClass;
		$table = $modelClass::getMetaData()->table;
		$columns = array();
		$columnCount = 0;
		$prefix = $this->query->tableAlias;
		if (empty($select) || $select === '*') {
			foreach ($table->columns as $column) {
				$alias = "t{$this->id}c" . ($columnCount++);
				$columns[] = "$prefix.{$column->name} AS $alias";
				$this->columnAliases[$alias] = $column->name;
				if ($column->isPrimaryKey) {
					$this->pkAlias[$column->name] = $alias;
				}
			}
		} else {
			if (is_string($select)) {
				$select = explode(',', $select);
			}
			foreach ($table->primaryKey as $column) {
				$alias = "t{$this->id}c" . ($columnCount++);
				$columns[] = "$prefix.$column AS $alias";
				$this->pkAlias[$column] = $alias;
			}
			foreach ($select as $column) {
				$column = trim($column);
				if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $matches)) {
					// if the column is already aliased
					$this->columnAliases[$matches[2]] = $matches[2];
					$columns[] = $column;
				} elseif (!isset($this->pkAlias[$column])) {
					$alias = "t{$this->id}c" . ($columnCount++);
					$columns[] = "$prefix.$column AS $alias";
					$this->columnAliases[$alias] = $column;
				}
			}
		}

		return $columns;
	}
}