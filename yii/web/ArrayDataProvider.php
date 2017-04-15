<?php
/**
 * \yii\web\ArrayDataProvider class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\helpers\ArrayHelper;

/**
 * Class ArrayDataProvider
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Veaceslav Medvedev <slavcopost@gmail.com>
 * @version 0.1
 * @since 2.0
 */
class ArrayDataProvider extends DataProvider
{
	/**
	 * @var array
	 */
	private $_rawData = array();
	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param array $rawData
	 * @param array $config
	 */
	public function __construct($rawData, $config = array())
	{
		$this->_rawData = $rawData;
		parent:: __construct($config);
	}

	/**
	 * Refresh the data from the persistent data storage.
	 */
	public function refresh()
	{
		parent::refresh();

		$sort = $this->getSort();
		if ($sort) {
			$orders = $sort->getOrders();
			ArrayHelper::multisort($this->_rawData, array_keys($orders), array_values($orders));
		}

		$pagination = $this->getPagination();
		if ($pagination) {
			$this->_data = array_slice($this->_rawData, $pagination->getOffset(), $pagination->getLimit());
		} else {
			$this->_data = $this->_rawData;
		}
	}

	/**
	 * Returns the total number of data items.
	 *
	 * @return integer the total number of data items.
	 */
	public function getTotal()
	{
		return count($this->_rawData);
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
