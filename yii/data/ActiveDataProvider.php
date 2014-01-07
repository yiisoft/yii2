<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\db\ActiveQueryInterface;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Connection;
use yii\db\QueryInterface;

/**
 * ActiveDataProvider implements a data provider based on [[\yii\db\Query]] and [[\yii\db\ActiveQuery]].
 *
 * ActiveDataProvider provides data by performing DB queries using [[query]].
 *
 * The following is an example of using ActiveDataProvider to provide ActiveRecord instances:
 *
 * ~~~
 * $provider = new ActiveDataProvider([
 *     'query' => Post::find(),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ~~~
 *
 * And the following example shows how to use ActiveDataProvider without ActiveRecord:
 *
 * ~~~
 * $query = new Query;
 * $provider = new ActiveDataProvider([
 *     'query' => $query->from('tbl_post'),
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the posts in the current page
 * $posts = $provider->getModels();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveDataProvider extends BaseDataProvider
{
	/**
	 * @var QueryInterface the query that is used to fetch data models and [[totalCount]]
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

	/**
	 * Initializes the DB connection component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
			if ($this->db === null) {
				throw new InvalidConfigException('The "db" property must be a valid DB Connection application component.');
			}
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareModels()
	{
		if (!$this->query instanceof QueryInterface) {
			throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
		}
		if (($pagination = $this->getPagination()) !== false) {
			$pagination->totalCount = $this->getTotalCount();
			$this->query->limit($pagination->getLimit())->offset($pagination->getOffset());
		}
		if (($sort = $this->getSort()) !== false) {
			$this->query->addOrderBy($sort->getOrders());
		}
		return $this->query->all($this->db);
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareKeys($models)
	{
		$keys = [];
		if ($this->key !== null) {
			foreach ($models as $model) {
				if (is_string($this->key)) {
					$keys[] = $model[$this->key];
				} else {
					$keys[] = call_user_func($this->key, $model);
				}
			}
			return $keys;
		} elseif ($this->query instanceof ActiveQueryInterface) {
			/** @var \yii\db\ActiveRecord $class */
			$class = $this->query->modelClass;
			$pks = $class::primaryKey();
			if (count($pks) === 1) {
				$pk = $pks[0];
				foreach ($models as $model) {
					$keys[] = $model[$pk];
				}
			} else {
				foreach ($models as $model) {
					$kk = [];
					foreach ($pks as $pk) {
						$kk[$pk] = $model[$pk];
					}
					$keys[] = $kk;
				}
			}
			return $keys;
		} else {
			return array_keys($models);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareTotalCount()
	{
		if (!$this->query instanceof QueryInterface) {
			throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
		}
		$query = clone $this->query;
		return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
	}

	/**
	 * @inheritdoc
	 */
	public function setSort($value)
	{
		parent::setSort($value);
		if (($sort = $this->getSort()) !== false && empty($sort->attributes) && $this->query instanceof ActiveQueryInterface) {
			/** @var Model $model */
			$model = new $this->query->modelClass;
			foreach ($model->attributes() as $attribute) {
				$sort->attributes[$attribute] = [
					'asc' => [$attribute => SORT_ASC],
					'desc' => [$attribute => SORT_DESC],
					'label' => $model->getAttributeLabel($attribute),
				];
			}
		}
	}
}
