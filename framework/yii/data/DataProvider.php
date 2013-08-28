<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

/**
 * DataProvider is the base class of data provider classes.
 *
 * It implements the [[getPagination()]] and [[getSort()]] methods as specified by the [[IDataProvider]] interface.
 *
 * @property integer $count The number of data models in the current page. This property is read-only.
 * @property Pagination|boolean $pagination The pagination object. If this is false, it means the pagination
 * is disabled. Note that the type of this property differs in getter and setter. See [[getPagination()]] and
 * [[setPagination()]] for details.
 * @property Sort|boolean $sort The sorting object. If this is false, it means that sorting is disabled. Note
 * that the type of this property differs in getter and setter. See [[getSort()]] and [[setSort()]] for details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class DataProvider extends Component implements IDataProvider
{
	/**
	 * @var string an ID that uniquely identifies the data provider among all data providers.
	 * You should set this property if the same page contains two or more different data providers.
	 * Otherwise, the [[pagination]] and [[sort]] mainly not work properly.
	 */
	public $id;

	private $_sort;
	private $_pagination;

	/**
	 * @return Pagination|boolean the pagination object. If this is false, it means the pagination is disabled.
	 */
	public function getPagination()
	{
		if ($this->_pagination === null) {
			$this->_pagination = new Pagination;
			if ($this->id !== null) {
				$this->_pagination->pageVar = $this->id . '-page';
			}
		}
		return $this->_pagination;
	}

	/**
	 * Sets the pagination for this data provider.
	 * @param array|Pagination|boolean $value the pagination to be used by this data provider.
	 * This can be one of the following:
	 *
	 * - a configuration array for creating the pagination object. The "class" element defaults
	 *   to 'yii\data\Pagination'
	 * - an instance of [[Pagination]] or its subclass
	 * - false, if pagination needs to be disabled.
	 *
	 * @throws InvalidParamException
	 */
	public function setPagination($value)
	{
		if (is_array($value)) {
			$config = array(
				'class' => Pagination::className(),
			);
			if ($this->id !== null) {
				$config['pageVar'] = $this->id . '-page';
			}
			$this->_pagination = Yii::createObject(array_merge($config, $value));
		} elseif ($value instanceof Pagination || $value === false) {
			$this->_pagination = $value;
		} else {
			throw new InvalidParamException('Only Pagination instance, configuration array or false is allowed.');
		}
	}

	/**
	 * @return Sort|boolean the sorting object. If this is false, it means the sorting is disabled.
	 */
	public function getSort()
	{
		if ($this->_sort === null) {
			$this->setSort(array());
		}
		return $this->_sort;
	}

	/**
	 * Sets the sort definition for this data provider.
	 * @param array|Sort|boolean $value the sort definition to be used by this data provider.
	 * This can be one of the following:
	 *
	 * - a configuration array for creating the sort definition object. The "class" element defaults
	 *   to 'yii\data\Sort'
	 * - an instance of [[Sort]] or its subclass
	 * - false, if sorting needs to be disabled.
	 *
	 * @throws InvalidParamException
	 */
	public function setSort($value)
	{
		if (is_array($value)) {
			$config = array(
				'class' => Sort::className(),
			);
			if ($this->id !== null) {
				$config['sortVar'] = $this->id . '-sort';
			}
			$this->_sort = Yii::createObject(array_merge($config, $value));
		} elseif ($value instanceof Sort || $value === false) {
			$this->_sort = $value;
		} else {
			throw new InvalidParamException('Only Sort instance, configuration array or false is allowed.');
		}
	}

	/**
	 * Returns the number of data models in the current page.
	 * @return integer the number of data models in the current page.
	 */
	public function getCount()
	{
		return count($this->getModels());
	}
}
