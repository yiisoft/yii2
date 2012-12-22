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

use yii\db\dao\BaseQuery;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseActiveQuery extends BaseQuery
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var array list of relations that this query should be performed with
	 */
	public $with;
	/**
	 * @var string the table alias to be used for query
	 */
	public $tableAlias;
	/**
	 * @var string the name of the column that the result should be indexed by.
	 * This is only useful when the query result is returned as an array.
	 */
	public $index;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var array list of scopes that should be applied to this query
	 */
	public $scopes;

	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	public function with()
	{
		$this->with = func_get_args();
		if (isset($this->with[0]) && is_array($this->with[0])) {
			// the parameter is given as an array
			$this->with = $this->with[0];
		}
		return $this;
	}

	public function index($column)
	{
		$this->index = $column;
		return $this;
	}

	public function tableAlias($value)
	{
		$this->tableAlias = $value;
		return $this;
	}

	public function scopes($names)
	{
		$this->scopes = $names;
		return $this;
	}

	protected function createModels($rows)
	{
		$models = array();
		if ($this->asArray) {
			if ($this->index === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$models[$row[$this->index]] = $row;
			}
		} else {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			if ($this->index === null) {
				foreach ($rows as $row) {
					$models[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$models[$row[$this->index]] = $class::create($row);
				}
			}
		}
		return $models;
	}
}
