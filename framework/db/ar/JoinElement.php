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
	 * @var ActiveRelation
	 */
	public $relation;
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
	public $relatedChildren = array();
	/**
	 * @var boolean whether this element is only for join purpose. If true, data will also be populated into the AR of this element.
	 */
	public $joinOnly;

	public $columnAliases = array(); // alias => original name
	public $pkAlias = array(); // original name => alias

	public $records;
	public $relatedRecords;

	public function __construct($relation, $parent, $relatedParent)
	{
		$this->relation = $relation;
		if ($parent !== null) {
			$this->parent = $parent;
			$parent->children[$relation->name] = $this;
			$relatedParent->relatedChildren[$relation->name] = $this;
		}
	}

	public function populateData($row)
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

		// create active record
		if (isset($this->records[$pk])) {
			$record = $this->records[$pk];
		} else {
			$attributes = array();
			foreach ($row as $alias => $value) {
				if (isset($this->columnAliases[$alias])) {
					$attributes[$this->columnAliases[$alias]] = $value;
				}
			}
			$modelClass = $this->relation->modelClass;
			$record = $modelClass::populateData($attributes);
			foreach ($this->children as $child) {
				if ($child->relation->select !== false) {
					$record->initRelation($child->relation);
				}
			}
			$this->records[$pk] = $record;
		}

		// populate child records
		foreach ($this->relatedChildren as $child) {
			if ($child->relation->select === false || $child->joinOnly) {
				continue;
			}
			$childRecord = $child->populateData($row);
			if ($childRecord === null) {
				continue;
			}
			if ($child->relation->hasMany) {
				$fpk = serialize($childRecord->getPrimaryKey());
				if (isset($this->relatedRecords[$pk][$child->relation->name][$fpk])) {
					continue;
				}
				$this->relatedRecords[$pk][$child->relation->name][$fpk] = true;
			}
			$record->addRelatedRecord($child->relation, $childRecord);
		}

		return $record;
	}

	public function buildQuery($query)
	{
		$tokens = array(
			'@.' => $this->relation->tableAlias . '.',
			'?.' => $this->parent->relation->tableAlias . '.',
		);
		foreach ($this->buildSelect($this->relation->select) as $column) {
			$query->select[] = strtr($column, $tokens);
		}

		if ($this->relation->where !== null) {
			$query->where[] = strtr($this->relation->where, $tokens);
		}

		if ($this->relation->having !== null) {
			$query->having[] = strtr($this->relation->having, $tokens);
		}

		if ($this->relation->via !== null) {
			$query->join[] = $this->relation->via;
		}

		$modelClass = $this->relation->modelClass;
		$tableName = $modelClass::tableName();
		$joinType = $this->relation->joinType === null ? 'LEFT JOIN' : $this->relation->joinType;
		$join = "$joinType $tableName {$this->relation->tableAlias}";
		if ($this->relation->on !== null) {
			$join .= ' ON ' . strtr($this->relation->on, $tokens);
		}
		$query->join[] = $join;


		if ($this->relation->join !== null) {
			$query->join[] = strtr($this->relation->join, $tokens);
		}

		// todo: convert orderBy to array first
		if ($this->relation->orderBy !== null) {
			$query->orderBy[] = strtr($this->relation->orderBy, $tokens);
		}

		// todo: convert groupBy to array first
		if ($this->relation->groupBy !== null) {
			$query->groupBy[] = strtr($this->relation->groupBy, $tokens);
		}

		if ($this->relation->params !== null) {
			foreach ($this->relation->params as $name => $value) {
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
		$modelClass = $this->relation->modelClass;
		$tableSchema = $modelClass::getMetaData()->table;
		$columns = array();
		$columnCount = 0;
		$prefix = $this->relation->tableAlias;
		if (empty($select) || $select === '*') {
			foreach ($tableSchema->columns as $column) {
				$alias = $this->relation->tableAlias . '_' . ($columnCount++);
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
			foreach ($tableSchema->primaryKey as $column) {
				$alias = $this->relation->tableAlias . '_' . ($columnCount++);
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
					$alias = $this->relation->tableAlias . '_' . ($columnCount++);
					$columns[] = "$prefix.$column AS $alias";
					$this->columnAliases[$alias] = $column;
				}
			}
		}

		return $columns;
	}
}