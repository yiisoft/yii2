<?php
/**
 * \yii\web\QueryDataProvider class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * Class QueryDataProvider
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Veaceslav Medvedev <slavcopost@gmail.com>
 * @version 0.1
 * @since 2.0
 */
class QueryDataProvider extends DataProvider
{
	/**
	 * @var \yii\db\Query
	 */
	private $_query;
	/**
	 * @var array
	 */
	private $_data;

	/**
	 * Constructor
	 *
	 * @param \yii\db\Query $query
	 * @param array $config
	 */
	public function __construct($query, $config = array())
	{
		$this->_query = $query;
		parent:: __construct($config);
	}

	/**
	 * Refresh the data from the persistent data storage.
	 */
	public function refresh()
	{
		parent::refresh();
		$query = clone $this->_query;

		$sort = $this->getSort();
		if ($sort) {
			$query->addOrderBy($sort->getOrders());
		}

		$pagination = $this->getPagination();
		if ($pagination) {
			$query->offset($pagination->getOffset());
			$query->limit($pagination->getLimit());
		}

		if ($query instanceof ActiveQuery) {
			$this->_data = $query->all();
		} else {
			$this->_data = $query->createCommand()->queryAll();
		}
		unset($query);
	}

	/**
	 * Returns the total number of data items.
	 *
	 * @return integer the total number of data items.
	 */
	public function getTotal()
	{
		$query = clone $this->_query;
		$query->select(array('COUNT(*)'));
		$count = $query->createCommand()->queryScalar();
		unset($query);
		return $count;
	}

	/**
	 * Returns the data items currently available.
	 *
	 * @return array
	 */
	public function getData()
	{
		if ($this->_data === null) {
			$this->refresh();
		}
		return $this->_data;
	}
}
