<?php

namespace yii\db\ar;

class Relation extends ActiveQuery
{
	protected $multiple = false;
	public $parentClass;
	/**
	 * @var array
	 */
	public $link;
	/**
	 * @var ActiveQuery
	 */
	public $via;

	public function findWith($name, &$parentRecords)
	{
		if (empty($this->link) || !is_array($this->link)) {
			throw new \yii\base\Exception('invalid link');
		}
		$this->addLinkCondition($parentRecords);
		$records = $this->find();

		/** @var array $map mapping key(s) to index of $parentRecords */
		$index = $this->buildRecordIndex($parentRecords, array_values($this->link));
		$this->initRecordRelation($parentRecords, $name);
		foreach ($records as $record) {
			$key = $this->getRecordKey($record, array_keys($this->link));
			if (isset($index[$key])) {
				$parentRecords[$map[$key]][$name] = $record;
			}
		}
	}

	protected function getRecordKey($record, $attributes)
	{
		if (isset($attributes[1])) {
			$key = array();
			foreach ($attributes as $attribute) {
				$key[] = is_array($record) ? $record[$attribute] : $record->$attribute;
			}
			return serialize($key);
		} else {
			$attribute = $attributes[0];
			return is_array($record) ? $record[$attribute] : $record->$attribute;
		}
	}

	protected function buildRecordIndex($records, $attributes)
	{
		$map = array();
		foreach ($records as $i => $record) {
			$map[$this->getRecordKey($record, $attributes)] = $i;
		}
		return $map;
	}

	protected function addLinkCondition($parentRecords)
	{
		$attributes = array_keys($this->link);
		$values = array();
		if (isset($links[1])) {
			// composite keys
			foreach ($parentRecords as $record) {
				$v = array();
				foreach ($this->link as $attribute => $link) {
					$v[$attribute] = is_array($record) ? $record[$link] : $record->$link;
				}
				$values[] = $v;
			}
		} else {
			// single key
			$attribute = $this->link[$links[0]];
			foreach ($parentRecords as $record) {
				$values[] = is_array($record) ? $record[$attribute] : $record->$attribute;
			}
		}
		$this->andWhere(array('in', $attributes, $values));
	}
}
