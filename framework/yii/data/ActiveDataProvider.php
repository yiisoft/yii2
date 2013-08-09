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
 * $posts = $provider->getModels();
 * ~~~
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ~~~
 * $query = new Query;
 * $provider = new ActiveDataProvider(array(
 *     'query' => $query->from('tbl_post'),
 *     'pagination' => array(
 *         'pageSize' => 20,
 *     ),
 * ));
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveDataProvider extends DataProvider
{
	/**
	 * @var Query the query that is used to fetch data models and [[totalCount]]
	 * if it is not explicitly set.
	 */
	public $query;
	/**
	 * @var string|callable the column that is used as the key of the data models.
	 * This can be either a column name, or a callable that returns the key value of a given data model.
	 *
	 * If this is not set, the following rules will be used to determine the keys of the data models:
	 *
	 * - If [[query]] is an [[ActiveQuery]] instance, the primary keys of [[ActiveQuery::modelClass]] will be used.
	 * - Otherwise, the keys of the [[models]] array will be used.
	 *
	 * @see getKeys()
	 */
	public $key;
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * If not set, the default DB connection will be used.
	 */
	public $db;

	private $_models;
	private $_keys;
	private $_totalCount;

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
	 * Returns the number of data models in the current page.
	 * This is equivalent to `count($provider->models)`.
	 * When [[pagination]] is false, this is the same as [[totalCount]].
	 * @return integer the number of data models in the current page.
	 */
	public function getCount()
	{
		return count($this->getModels());
	}

	/**
	 * Returns the total number of data models.
	 * When [[pagination]] is false, this returns the same value as [[count]].
	 * If [[totalCount]] is not explicitly set, it will be calculated
	 * using [[query]] with a COUNT query.
	 * @return integer total number of possible data models.
	 * @throws InvalidConfigException
	 */
	public function getTotalCount()
	{
		if ($this->getPagination() === false) {
			return $this->getCount();
		} elseif ($this->_totalCount === null) {
			if (!$this->query instanceof Query) {
				throw new InvalidConfigException('The "query" property must be an instance of Query or its subclass.');
			}
			$query = clone $this->query;
			$this->_totalCount = $query->limit(-1)->offset(-1)->count('*', $this->db);
		}
		return $this->_totalCount;
	}

	/**
	 * Sets the total number of data models.
	 * @param integer $value the total number of data models.
	 */
	public function setTotalCount($value)
	{
		$this->_totalCount = $value;
	}

	/**
	 * Returns the data models in the current page.
	 * @return array the list of data models in the current page.
	 * @throws InvalidConfigException if [[query]] is not set or invalid.
	 */
	public function getModels()
	{
		if ($this->_models === null) {
			if (!$this->query instanceof Query) {
				throw new InvalidConfigException('The "query" property must be an instance of Query or its subclass.');
			}
			if (($pagination = $this->getPagination()) !== false) {
				$pagination->totalCount = $this->getTotalCount();
				$this->query->limit($pagination->getLimit())->offset($pagination->getOffset());
			}
			if (($sort = $this->getSort()) !== false) {
				$this->query->orderBy($sort->getOrders());
			}
			$this->_models = $this->query->all($this->db);
		}
		return $this->_models;
	}

	/**
	 * Returns the key values associated with the data models.
	 * @return array the list of key values corresponding to [[models]]. Each data model in [[models]]
	 * is uniquely identified by the corresponding key value in this array.
	 */
	public function getKeys()
	{
		if ($this->_keys === null) {
			$this->_keys = array();
			$models = $this->getModels();
			if ($this->key !== null) {
				foreach ($models as $model) {
					if (is_string($this->key)) {
						$this->_keys[] = $model[$this->key];
					} else {
						$this->_keys[] = call_user_func($this->key, $model);
					}
				}
			} elseif ($this->query instanceof ActiveQuery) {
				/** @var \yii\db\ActiveRecord $class */
				$class = $this->query->modelClass;
				$pks = $class::primaryKey();
				if (count($pks) === 1) {
					$pk = $pks[0];
					foreach ($models as $model) {
						$this->_keys[] = $model[$pk];
					}
				} else {
					foreach ($models as $model) {
						$keys = array();
						foreach ($pks as $pk) {
							$keys[] = $model[$pk];
						}
						$this->_keys[] = json_encode($keys);
					}
				}
			} else {
				$this->_keys = array_keys($models);
			}
		}
		return $this->_keys;
	}

	/**
	 * Refreshes the data provider.
	 * After calling this method, if [[getModels()]], [[getKeys()]] or [[getTotalCount()]] is called again,
	 * they will re-execute the query and return the latest data available.
	 */
	public function refresh()
	{
		$this->_models = null;
		$this->_totalCount = null;
		$this->_keys = null;
	}
}
