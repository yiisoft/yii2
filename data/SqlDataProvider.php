<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;

/**
 * SqlDataProvider implements a data provider based on a plain SQL statement.
 *
 * SqlDataProvider provides data in terms of arrays, each representing a row of query result.
 *
 * Like other data providers, SqlDataProvider also supports sorting and pagination.
 * It does so by modifying the given [[sql]] statement with "ORDER BY" and "LIMIT"
 * clauses. You may configure the [[sort]] and [[pagination]] properties to
 * customize sorting and pagination behaviors.
 *
 * SqlDataProvider may be used in the following way:
 *
 * ~~~
 * $count = Yii::$app->db->createCommand('
 *     SELECT COUNT(*) FROM tbl_user WHERE status=:status
 * ', [':status' => 1])->queryScalar();
 *
 * $dataProvider = new SqlDataProvider([
 *     'sql' => 'SELECT * FROM tbl_user WHERE status=:status',
 *     'params' => [':status' => 1],
 *     'totalCount' => $count,
 *     'sort' => [
 *         'attributes' => [
 *             'age',
 *             'name' => [
 *                 'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
 *                 'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
 *                 'default' => SORT_DESC,
 *                 'label' => 'Name',
 *             ],
 *         ],
 *     ],
 *     'pagination' => [
 *         'pageSize' => 20,
 *     ],
 * ]);
 *
 * // get the user records in the current page
 * $models = $dataProvider->getModels();
 * ~~~
 *
 * Note: if you want to use the pagination feature, you must configure the [[totalCount]] property
 * to be the total number of rows (without pagination). And if you want to use the sorting feature,
 * you must configure the [[sort]] property so that the provider knows which columns can be sorted.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SqlDataProvider extends BaseDataProvider
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 */
	public $db = 'db';
	/**
	 * @var string the SQL statement to be used for fetching data rows.
	 */
	public $sql;
	/**
	 * @var array parameters (name=>value) to be bound to the SQL statement.
	 */
	public $params = [];
	/**
	 * @var string|callable the column that is used as the key of the data models.
	 * This can be either a column name, or a callable that returns the key value of a given data model.
	 *
	 * If this is not set, the keys of the [[models]] array will be used.
	 */
	public $key;


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
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException('The "db" property must be a valid DB Connection application component.');
		}
		if ($this->sql === null) {
			throw new InvalidConfigException('The "sql" property must be set.');
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareModels()
	{
		$sql = $this->sql;
		$qb = $this->db->getQueryBuilder();
		if (($sort = $this->getSort()) !== false) {
			$orderBy = $qb->buildOrderBy($sort->getOrders());
			if (!empty($orderBy)) {
				$orderBy = substr($orderBy, 9);
				if (preg_match('/\s+order\s+by\s+[\w\s,\.]+$/i', $sql)) {
					$sql .= ', ' . $orderBy;
				} else {
					$sql .= ' ORDER BY ' . $orderBy;
				}
			}
		}

		if (($pagination = $this->getPagination()) !== false) {
			$pagination->totalCount = $this->getTotalCount();
			$sql .= ' ' . $qb->buildLimit($pagination->getLimit(), $pagination->getOffset());
		}

		return $this->db->createCommand($sql, $this->params)->queryAll();
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
		} else {
			return array_keys($models);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareTotalCount()
	{
		return 0;
	}
}
