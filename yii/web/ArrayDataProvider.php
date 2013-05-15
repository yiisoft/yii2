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
	private $_rawData = array();
	private $_data;

	public function __construct(array $rawData, array $config = array())
	{
		$this->_rawData = $rawData;
		parent:: __construct($config);
	}

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

	public function getTotal()
	{
		return count($this->_rawData);
	}

	public function getData()
	{
		if ($this->_data === null) {
			$this->refresh();
		}
		return $this->_data;
	}
}