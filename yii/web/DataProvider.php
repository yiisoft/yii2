<?php
/**
 * \yii\web\DataProvider class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use IteratorAggregate;
use Countable;
use Yii;
use yii\base\DictionaryIterator;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class DataProvider
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Veaceslav Medvedev <slavcopost@gmail.com>
 * @version 0.1
 * @since 2.0
 */
abstract class DataProvider extends Object implements IteratorAggregate, Countable
{
	/**
	 * @var string
	 */
	public $id = '';
	/**
	 * @var string
	 */
	public $indexBy = 'id';

	/**
	 * @var array
	 */
	private $_keys;
	/**
	 * @var \yii\web\Pagination
	 */
	private $_pagination;
	/**
	 * @var \yii\web\Sort
	 */
	private $_sort;

	abstract public function getData();
	abstract public function getTotal();

	/**
	 * Refresh the data from the persistent data storage.
	 */
	public function refresh()
	{
		$this->_keys = null;
	}

	/**
	 * Returns the key values associated with the data items.
	 *
	 * @return array the list of key values corresponding to data.
	 */
	public function getKeys()
	{
		if ($this->_keys === null) {
			if ($this->indexBy === null) {
				$this->_keys = array_keys($this->getData());
			} else {
				$this->_keys = ArrayHelper::index($this->getData(), $this->indexBy);
			}
		}
		return $this->_keys;
	}


	/**
	 * Returns the number of data items in the current page.
	 *
	 * @return integer the number of data items in the current page.
	 */
	public function count()
	{
		return count($this->getData());
	}

	public function getIterator()
	{
		return new DictionaryIterator($this->getData());
	}

	/**
	 * Returns the pagination object.
	 *
	 * @return \yii\web\Pagination
	 */
	public function getPagination()
	{
		if ($this->_pagination === null) {
			$this->setPagination(array());
		}
		return $this->_pagination;
	}

	/**
	 * Sets the pagination for this data provider.
	 *
	 * @param  mixed $pagination the pagination to be used by this data provider. This could be a [[Pagination]] object
	 * or an array used to configure the pagination object. If this is false, it means the pagination should be disabled.
	 *
	 * You can configure this property same way as a component:
	 * ~~~
	 * array(
	 *     'class' => 'MyPagination',
	 *     'pageSize' => 10,
	 * ),
	 * ~~~
	 *
	 * @throws \yii\base\InvalidConfigException if the configuration is invalid.
	 */
	public function setPagination($pagination)
	{
		if (is_array($pagination)) {
			$pagination = array_merge(array(
					'class' => 'yii\web\Pagination',
					'pageVar' => $this->id . '_page',
				), $pagination);
			$this->_pagination = Yii::createObject($pagination, $this->getTotal());
		} elseif ($pagination instanceof Pagination || $pagination === false) {
			$this->_pagination = $pagination;
		} else {
			throw new InvalidConfigException('Invalid pagination configuration.');
		}
	}

	/**
	 * Returns the sort object.
	 *
	 * @return \yii\web\Sort
	 */
	public function getSort()
	{
		if ($this->_sort === null) {
			$this->setSort(array());
		}
		return $this->_sort;
	}

	/**
	 * Sets the sorting for this data provider.
	 *
	 * @param mixed $sort the sorting to be used by this data provider. This could be a [[Sort]] object
	 * or an array used to configure the sorting object. If this is false, it means the sorting should be disabled.
	 *
	 * You can configure this property same way as a component:
	 * ~~~
	 * array(
	 *     'class' => 'MySort',
	 *     'attributes' => array('name', 'weight'),
	 * ),
	 * ~~~
	 *
	 * @throws \yii\base\InvalidConfigException if the configuration is invalid.
	 */
	public function setSort($sort)
	{
		if (is_array($sort)) {
			$sort = array_merge(array(
					'class' => 'yii\web\Sort',
					'sortVar' => $this->id . '_sort',
				), $sort);
			$this->_sort = Yii::createObject($sort);
		} elseif ($sort instanceof Sort || $sort === false) {
			$this->_sort = $sort;
		} else {
			throw new InvalidConfigException('Invalid sort configuration.');
		}
	}
}
