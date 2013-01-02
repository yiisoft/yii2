<?php
/**
 * ActiveRelation class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

/**
 * It is used in three scenarios:
 * - eager loading: User::find()->with('posts')->all();
 * - lazy loading: $user->posts;
 * - lazy loading with query options: $user->posts()->where('status=1')->get();
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRelation extends BaseActiveQuery
{
	/**
	 * @var string the class name of the ActiveRecord instances that this relation
	 * should create and populate query results into
	 */
	public $modelClass;
	/**
	 * @var ActiveRecord the primary record that this relation is associated with.
	 * This is used only in lazy loading with dynamic query options.
	 */
	public $primaryModel;
	/**
	 * @var boolean whether this relation is a one-many relation
	 */
	public $hasMany;
	/**
	 * @var array the columns of the primary and foreign tables that establish the relation.
	 * The array keys must be columns of the table for this relation, and the array values
	 * must be the corresponding columns from the primary table.
	 * Do not prefix or quote the column names as they will be done automatically by Yii.
	 */
	public $link;
	/**
	 * @var ActiveRelation
	 */
	public $via;

	public function one()
	{
		$models = $this->all();
		return isset($models[0]) ? $models[0] : null;
	}

	public function all()
	{
		$models = array();
		return $models;
	}

	public function findWith($name, &$primaryRecords)
	{
		if (empty($this->link) || !is_array($this->link)) {
			throw new \yii\base\Exception('invalid link');
		}
		$this->addLinkCondition($primaryRecords);
		$records = $this->find();

		/** @var array $map mapping key(s) to index of $primaryRecords */
		$index = $this->buildRecordIndex($primaryRecords, array_values($this->link));
		$this->initRecordRelation($primaryRecords, $name);
		foreach ($records as $record) {
			$key = $this->getRecordKey($record, array_keys($this->link));
			if (isset($index[$key])) {
				$primaryRecords[$map[$key]][$name] = $record;
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

	protected function addLinkCondition($primaryRecords)
	{
		$attributes = array_keys($this->link);
		$values = array();
		if (isset($links[1])) {
			// composite keys
			foreach ($primaryRecords as $record) {
				$v = array();
				foreach ($this->link as $attribute => $link) {
					$v[$attribute] = is_array($record) ? $record[$link] : $record->$link;
				}
				$values[] = $v;
			}
		} else {
			// single key
			$attribute = $this->link[$links[0]];
			foreach ($primaryRecords as $record) {
				$values[] = is_array($record) ? $record[$attribute] : $record->$attribute;
			}
		}
		$this->andWhere(array('in', $attributes, $values));
	}

}
