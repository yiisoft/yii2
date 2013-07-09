<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * ArrayDataProvider implements a data provider based on a data array.
 *
 * The [[allItems]] property contains all data items that may be sorted and/or paginated.
 * ArrayDataProvider will provide the data after sorting and/or pagination.
 * You may configure the [[sort]] and [[pagination]] properties to
 * customize the sorting and pagination behaviors.
 *
 * Elements in the [[allItems]] array may be either objects (e.g. model objects)
 * or associative arrays (e.g. query results of DAO).
 * Make sure to set the [[key]] property to the name of the field that uniquely
 * identifies a data record or false if you do not have such a field.
 *
 * Compared to [[ActiveDataProvider]], ArrayDataProvider could be less efficient
 * because it needs to have [[allItems]] ready.
 *
 * ArrayDataProvider may be used in the following way:
 *
 * ~~~
 * $query = new Query;
 * $provider = new ArrayDataProvider(array(
 *     'allItems' => $query->from('tbl_post')->all(),
 *     'sort' => array(
 *         'attributes' => array(
 *              'id', 'username', 'email',
 *         ),
 *     ),
 *     'pagination' => array(
 *         'pageSize' => 10,
 *     ),
 * ));
 * // get the posts in the current page
 * $posts = $provider->getItems();
 * ~~~
 *
 * Note: if you want to use the sorting feature, you must configure the [[sort]] property
 * so that the provider knows which columns can be sorted.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ArrayDataProvider extends DataProvider
{
	/**
	 * @var string|callable the column that is used as the key of the data items.
	 * This can be either a column name, or a callable that returns the key value of a given data item.
	 * If this is not set, the index of the [[items]] array will be used.
	 * @see getKeys()
	 */
	public $key;
	/**
	 * @var array the data that is not paginated or sorted. When pagination is enabled,
	 * this property usually contains more elements than [[items]].
	 * The array elements must use zero-based integer keys.
	 */
	public $allItems;

	private $_count;

	/**
	 * Returns the total number of data items.
	 * @return integer total number of possible data items.
	 * @throws InvalidConfigException
	 */
	public function getTotalItemCount()
	{
		if ($this->getPagination() === false) {
			return $this->getItemCount();
		} elseif ($this->_count === null) {
			if ($this->allItems !== null) {
				$this->_count = count($this->allItems);
			} else {
				throw new InvalidConfigException('Unable to determine total item count: either "allItems" or "totalItemCount" must be set.');
			}
		}
		return $this->_count;
	}

	/**
	 * Sets the total number of data items.
	 * @param integer $value the total number of data items.
	 */
	public function setTotalItemCount($value)
	{
		$this->_count = $value;
	}

	private $_items;

	/**
	 * Returns the data items in the current page.
	 * @return array the list of data items in the current page.
	 * @throws InvalidConfigException
	 */
	public function getItems()
	{
		if ($this->_items === null) {
			if (($items = $this->allItems) === null) {
				throw new InvalidConfigException('Either "items" or "allItems" must be set.');
			}

			if (($sort = $this->getSort()) !== false) {
				$items = $this->sortItems($items, $sort);
			}

			if (($pagination = $this->getPagination()) !== false) {
				$pagination->itemCount = $this->getTotalItemCount();
				$items = array_slice($items, $pagination->getOffset(), $pagination->getLimit());
			}

			$this->_items = $items;
		}
		return $this->_items;
	}

	/**
	 * Sets the data items in the current page.
	 * @param array $items the items in the current page
	 */
	public function setItems($items)
	{
		$this->_items = $items;
	}

	private $_keys;

	/**
	 * Returns the key values associated with the data items.
	 * @return array the list of key values corresponding to [[items]]. Each data item in [[items]]
	 * is uniquely identified by the corresponding key value in this array.
	 */
	public function getKeys()
	{
		if ($this->_keys === null) {
			$this->_keys = array();
			$items = $this->getItems();
			if ($this->key !== null) {
				foreach ($items as $item) {
					if (is_string($this->key)) {
						$this->_keys[] = $item[$this->key];
					} else {
						$this->_keys[] = call_user_func($this->key, $item);
					}
				}
			} else {
				$this->_keys = array_keys($items);
			}
		}
		return $this->_keys;
	}

	/**
	 * Sets the key values associated with the data items.
	 * @param array $keys the list of key values corresponding to [[items]].
	 */
	public function setKeys($keys)
	{
		$this->_keys = $keys;
	}

	/**
	 * Sorts the data items according to the given sort definition
	 * @param array $items the items to be sorted
	 * @param Sort $sort the sort definition
	 * @return array the sorted data items
	 */
	protected function sortItems($items, $sort)
	{
		$orders = $sort->getOrders();
		if (!empty($orders)) {
			ArrayHelper::multisort($items, array_keys($orders), array_values($orders));
		}
		return $items;
	}
}
