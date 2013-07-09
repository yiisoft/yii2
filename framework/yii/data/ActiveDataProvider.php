<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * ActiveDataProvider implements a data provider based on [[ActiveQuery]].
 *
 * ActiveDataProvider provides data in terms of [[ActiveRecord]] objects. It uses
 * [[query]] to fetch the data items in a sorted and paginated manner.
 *
 * The following is an example of using ActiveDataProvider:
 *
 * ~~~
 * $provider = new ActiveDataProvider(array(
 *     'query' => Post::find(),
 *     'pagination' => array(
 *         'pageSize' => 20,
 *     ),
 * ));
 *
 * // get the posts in the current page
 * $posts = $provider->getItems();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveDataProvider extends DataProvider
{
	/**
	 * @var ActiveQuery the query that is used to fetch data items and [[totalItemCount]]
	 * if it is not explicitly set.
	 */
	public $query;
	/**
	 * @var string|callable the attribute that is used as the key of the data items.
	 * This can be either the name of the attribute, or a callable that returns the key value
	 * of a given data item. If not set, the primary key of [[ActiveQuery::modelClass]] will be used.
	 */
	public $keyAttribute;

	private $_items;
	private $_keys;
	private $_count;

	/**
	 * Returns the number of data items in the current page.
	 * This is equivalent to `count($provider->getItems())`.
	 * When [[pagination]] is false, this is the same as [[totalItemCount]].
	 * @param boolean $refresh whether to recalculate the item count. If true,
	 * this will cause re-fetching of [[items]].
	 * @return integer the number of data items in the current page.
	 */
	public function getItemCount($refresh = false)
	{
		return count($this->getItems($refresh));
	}

	/**
	 * Returns the total number of data items.
	 * When [[pagination]] is false, this returns the same value as [[itemCount]].
	 * If [[totalItemCount]] is not explicitly set, it will be calculated
	 * using [[query]] with a COUNT query.
	 * @param boolean $refresh whether to recalculate the item count
	 * @return integer total number of possible data items.
	 * @throws InvalidConfigException
	 */
	public function getTotalItemCount($refresh = false)
	{
		if ($this->getPagination() === false) {
			return $this->getItemCount($refresh);
		} elseif ($this->_count === null || $refresh) {
			if (!$this->query instanceof ActiveQuery) {
				throw new InvalidConfigException('The "query" property must be an instance of ActiveQuery or its subclass.');
			}
			$query = clone $this->query;
			$this->_count = $query->limit(-1)->offset(-1)->count();
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

	/**
	 * Returns the data items in the current page.
	 * @param boolean $refresh whether to re-fetch the data items.
	 * @return array the list of data items in the current page.
	 * @throws InvalidConfigException
	 */
	public function getItems($refresh = false)
	{
		if ($this->_items === null || $refresh) {
			if (!$this->query instanceof ActiveQuery) {
				throw new InvalidConfigException('The "query" property must be an instance of ActiveQuery or its subclass.');
			}
			if (($pagination = $this->getPagination()) !== false) {
				$pagination->itemCount = $this->getTotalItemCount();
				$this->query->limit($pagination->getLimit())->offset($pagination->getOffset());
			}
			if (($sort = $this->getSort()) !== false) {
				$this->query->orderBy($sort->getOrders());
			}
			$this->_items = $this->query->all();
		}
		return $this->_items;
	}

	/**
	 * Returns the key values associated with the data items.
	 * @param boolean $refresh whether to re-fetch the data items and re-calculate the keys
	 * @return array the list of key values corresponding to [[items]]. Each data item in [[items]]
	 * is uniquely identified by the corresponding key value in this array.
	 */
	public function getKeys($refresh = false)
	{
		if ($this->_keys === null || $refresh) {
			$this->_keys = array();
			$items = $this->getItems($refresh);
			$keyAttribute = $this->keyAttribute;
			if ($keyAttribute === null) {
				/** @var \yii\db\ActiveRecord $class */
				$class = $this->query->modelClass;
				$pks = $class::primaryKey();
				if (count($pks) === 1) {
					$pk = $pks[0];
					foreach ($items as $item) {
						$this->_keys[] = $item[$pk];
					}
				} else {
					foreach ($items as $item) {
						$keys = array();
						foreach ($pks as $pk) {
							$keys[] = $item[$pk];
						}
						$this->_keys[] = json_encode($keys);
					}
				}
			} else {
				foreach ($items as $item) {
					if (is_string($this->keyAttribute)) {
						$this->_keys[] = $item[$this->keyAttribute];
					} else {
						$this->_keys[] = call_user_func($item, $this->keyAttribute);
					}
				}
			}
		}
		return $this->_keys;
	}
}
