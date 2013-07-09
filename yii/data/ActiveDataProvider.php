<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\db\ActiveQuery;
use yii\db\Connection;

/**
 * ActiveDataProvider implements a data provider based on [[Query]] and [[ActiveQuery]].
 *
 * ActiveDataProvider provides data by performing DB queries using [[query]].
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
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
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ~~~
 * $provider = new ActiveDataProvider(array(
 *     'query' => new Query(array(
 *         'from' => 'tbl_post',
 *     )),
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
	 * @var Query the query that is used to fetch data items and [[totalItemCount]]
	 * if it is not explicitly set.
	 */
	public $query;
	/**
	 * @var string|callable the column that is used as the key of the data items.
	 * This can be either a column name, or a callable that returns the key value of a given data item.
	 *
	 * If this is not set, the following rules will be used to determine the keys of the data items:
	 *
	 * - If [[query]] is an [[ActiveQuery]] instance, the primary keys of [[ActiveQuery::modelClass]] will be used.
	 * - Otherwise, the keys of the [[items]] array will be used.
	 *
	 * @see getKeys()
	 */
	public $key;
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * If not set, the default DB connection will be used.
	 */
	public $db;

	private $_items;
	private $_keys;
	private $_count;

	/**
	 * Initializes the DbCache component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
			if (!$this->db instanceof Connection) {
				throw new InvalidConfigException('The "db" property must be a valid DB Connection application component.');
			}
		}
	}

	/**
	 * Returns the number of data items in the current page.
	 * This is equivalent to `count($provider->items)`.
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
			if (!$this->query instanceof Query) {
				throw new InvalidConfigException('The "query" property must be an instance of Query or its subclass.');
			}
			$query = clone $this->query;
			$this->_count = $query->limit(-1)->offset(-1)->count('*', $this->db);
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
			if (!$this->query instanceof Query) {
				throw new InvalidConfigException('The "query" property must be an instance of Query or its subclass.');
			}
			if (($pagination = $this->getPagination()) !== false) {
				$pagination->itemCount = $this->getTotalItemCount();
				$this->query->limit($pagination->getLimit())->offset($pagination->getOffset());
			}
			if (($sort = $this->getSort()) !== false) {
				$this->query->orderBy($sort->getOrders());
			}
			$this->_items = $this->query->all($this->db);
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
			if ($this->key !== null) {
				foreach ($items as $item) {
					if (is_string($this->key)) {
						$this->_keys[] = $item[$this->key];
					} else {
						$this->_keys[] = call_user_func($this->key, $item);
					}
				}
			} elseif ($this->query instanceof ActiveQuery) {
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
				$this->_keys = array_keys($items);
			}
		}
		return $this->_keys;
	}
}
