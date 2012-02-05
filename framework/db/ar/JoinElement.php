<?php
/**
 * ActiveQuery class file.
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
	 * @var JoinElement
	 */
	public $parent;
	/**
	 * @var JoinElement[]
	 */
	public $children = array();

	public $columnAliases = array(); // alias => original name
	public $pkAlias = array(); // original name => alias

	public $records;
	public $relatedRecords;

	public function __construct($parent, $relation)
	{
		if ($parent !== null) {
			$this->parent = $parent;
			$this->relation = $relation;
			$parent->children[$relation->name] = $this;
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
			$record = $modelClass::populateRecord($attributes);
			foreach ($this->children as $child) {
				if ($child->relation->select !== false) {
					$record->initRelation($child->relation);
				}
			}
			$this->records[$pk] = $record;
		}

		// populate child records
		foreach ($this->children as $child) {
			if ($child->relation->select === false) {
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
			'@.' => $this->relation->tableAlias,
			'?.' => $this->parent->relation->tableAlias,
		);
		foreach ($this->buildSelect() as $column) {
			$query->select[] = strtr($column, $tokens);
		}

		if ($this->relation->where !== null) {
			$query->where[] = strtr($this->relation->where, $tokens);
		}

		if ($this->relation->having !== null) {
			$query->having[] = strtr($this->relation->having, $tokens);
		}

		/*
		 * 	joinType;
		 	on;
		 	via;
			orderby
			groupby
			join
			params
 		 */

		foreach ($this->children as $child) {
			$child->buildQuery($query);
		}
	}

	public function buildSelect()
	{
		$modelClass = $this->relation->modelClass;
		$tableSchema = $modelClass::getMetaData()->table;
		$select = $this->relation->select;
		$columns = array();
		$columnCount = 0;
		if (empty($select) || $select === '*') {
			foreach ($tableSchema->columns as $column) {
				$alias = $this->tableAlias . '_' . ($columnCount++);
				$columns[] = "{$column->name} AS $alias";
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
				$alias = $this->tableAlias . '_' . ($columnCount++);
				$columns[] = "$column AS $alias";
				$this->pkAlias[$column] = $alias;
			}
			foreach ($select as $column) {
				$column = trim($column);
				if (preg_match('/^(.*?)\s+AS\s+(\w+)$/im', $column, $matches)) {
					// if the column is already aliased
					$this->columnAliases[$matches[2]] = $matches[2];
					$columns[] = $column;
				} elseif (!isset($this->pkAlias[$column])) {
					$alias = $this->tableAlias . '_' . ($columnCount++);
					$columns[] = "$column AS $alias";
					$this->columnAliases[$alias] = $column;
				}
			}
		}

		return $columns;
	}
}