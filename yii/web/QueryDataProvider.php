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
	private $_query;
	private $_data;

	public function __construct(Query $query, array $config = array())
	{
		$this->_query = $query;
		parent:: __construct($config);
	}

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

	public function getTotal()
	{
		$query = clone $this->_query;
		$query->select(array('COUNT(*)'));
		$count = (int)$query->createCommand()->queryScalar();
		unset($query);
		return $count;
	}

	public function getData()
	{
		if ($this->_data === null) {
			$this->refresh();
		}
		return $this->_data;
	}
}
