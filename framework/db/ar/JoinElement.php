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
	 * @param integer $id
	 * @param ActiveRelation|ActiveQuery $query
	 * @param null|JoinElement $parent
	 * @param null|JoinElement $container
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
		// todo: asArray
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
				if ($child->query->select !== false && !$child->joinOnly) {
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
				if ($child->query->index !== null) {
					$hash = $childRecord[$child->query->index];
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
}