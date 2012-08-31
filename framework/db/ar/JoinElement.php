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


class JoinElement
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
	public $container;
	/**
	 * @var JoinElement[] the child elements that need to join with this element
	 */
	public $children = array();
	/**
	 * @var JoinElement[] the child elements that have corresponding relations declared in the AR class of this element
	 */
	public $relations = array();
	/**
	 * @var array column aliases (alias => original name)
	 */
	public $columnAliases = array();
	/**
	 * @var array primary key column aliases (original name => alias)
	 */
	public $pkAlias = array();
	/**
	 * @var string|array the column(s) used for index the query results
	 */
	public $key;
	/**
	 * @var array query results for this element (PK value => AR instance or data array)
	 */
	public $records = array();
	public $related = array();

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
			$this->container = $container;
			$parent->children[$query->name] = $this;
			if ($query->select !== false) {
				$container->relations[$query->name] = $this;
			}
		}
	}

	public function populateData($rows)
	{
		if ($this->container === null) {
			foreach ($rows as $row) {
				if (($key = $this->getKeyValue($row)) !== null && !isset($this->records[$key])) {
					$this->records[$key] = $this->createRecord($row);
				}
			}
		} else {
			foreach ($rows as $row) {
				$key = $this->getKeyValue($row);
				$containerKey = $this->container->getKeyValue($row);
				if ($key === null || $containerKey === null || isset($this->related[$containerKey][$key])) {
					continue;
				}
				$this->related[$containerKey][$key] = true;
				if ($this->query->asArray) {
					if (isset($this->records[$key])) {
						if ($this->query->hasMany) {
							if ($this->query->index !== null) {
								$this->container->records[$containerKey][$this->query->name][$key] =& $this->records[$key];
							} else {
								$this->container->records[$containerKey][$this->query->name][] =& $this->records[$key];
							}
						} else {
							$this->container->records[$containerKey][$this->query->name] =& $this->records[$key];
						}
					} else {
						$record = $this->createRecord($row);
						if ($this->query->hasMany) {
							if ($this->query->index !== null) {
								$this->container->records[$containerKey][$this->query->name][$key] = $record;
								$this->records[$key] =& $this->container->records[$containerKey][$this->query->name][$key];
							} else {
								$count = count($this->container->records[$containerKey][$this->query->name]);
								$this->container->records[$containerKey][$this->query->name][] = $record;
								$this->records[$key] =& $this->container->records[$containerKey][$this->query->name][$count];
							}
						} else {
							$this->container->records[$containerKey][$this->query->name] = $record;
							$this->records[$key] =& $this->container->records[$containerKey][$this->query->name];
						}
					}
				} else {
					if (isset($this->records[$key])) {
						$record = $this->records[$key];
					} else {
						$this->records[$key] = $record = $this->createRecord($row);
					}
					$this->container->records[$containerKey]->addRelatedRecord($this->query, $record);
				}
			}
		}

		foreach ($this->relations as $child) {
			$child->populateData($rows);
		}
	}

	protected function getKeyValue($row)
	{
		if (is_array($this->key)) {
			$key = array();
			foreach ($this->key as $alias) {
				if (!isset($row[$alias])) {
					return null;
				}
				$key[] = $row[$alias];
			}
			return serialize($key);
		} else {
			return $row[$this->key];
		}
	}

	protected function createRecord($row)
	{
		$record = array();
		foreach ($this->columnAliases as $alias => $name) {
			$record[$name] = $row[$alias];
		}
		if ($this->query->asArray) {
			foreach ($this->relations as $child) {
				$record[$child->query->name] = $child->query->hasMany ? array() : null;
			}
		} else {
			$modelClass = $this->query->modelClass;
			$record = $modelClass::create($record);
			foreach ($this->relations as $child) {
				$record->{$child->query->name} = $child->query->hasMany ? array() : null;
			}
		}
		return $record;
	}
}