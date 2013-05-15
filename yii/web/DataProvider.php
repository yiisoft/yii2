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
	public $id = '';
	public $indexBy = 'id';

	private $_keys;
	private $_pagination;
	private $_sort;

	abstract public function getData();
	abstract public function getTotal();

	public function refresh()
	{
		$this->_keys = null;
	}

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

	public function count()
	{
		return count($this->getData());
	}

	public function getIterator()
	{
		return new DictionaryIterator($this->getData());
	}

	/**
	 * @return \yii\web\Pagination
	 */
	public function getPagination()
	{
		if ($this->_pagination === null) {
			$this->setPagination(array());
		}
		return $this->_pagination;
	}

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
	 * @return \yii\web\Sort
	 */
	public function getSort()
	{
		if ($this->_sort === null) {
			$this->setSort(array());
		}
		return $this->_sort;
	}

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
