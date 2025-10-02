<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\data;

use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\di\Instance;

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
 * ```
 * $count = Yii::$app->db->createCommand('
 *     SELECT COUNT(*) FROM user WHERE status=:status
 * ', [':status' => 1])->queryScalar();
 *
 * $dataProvider = new SqlDataProvider([
 *     'sql' => 'SELECT * FROM user WHERE status=:status',
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
 * ```
 *
 * Note: if you want to use the pagination feature, you must configure the [[totalCount]] property
 * to be the total number of rows (without pagination). And if you want to use the sorting feature,
 * you must configure the [[sort]] property so that the provider knows which columns can be sorted.
 *
 * For more details and usage information on SqlDataProvider, see the [guide article on data providers](guide:output-data-providers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SqlDataProvider extends BaseDataProvider
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
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
     * @var string|callable|null the column that is used as the key of the data models.
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
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->sql === null) {
            throw new InvalidConfigException('The "sql" property must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $sort = $this->getSort();
        $pagination = $this->getPagination();
        if ($pagination === false && $sort === false) {
            return $this->db->createCommand($this->sql, $this->params)->queryAll();
        }

        $sql = $this->sql;
        $orders = [];
        $limit = $offset = null;

        if ($sort !== false) {
            $orders = $sort->getOrders();
            $pattern = '/\s+order\s+by\s+([\w\s,\."`\[\]]+)$/i';
            if (preg_match($pattern, $sql, $matches)) {
                array_unshift($orders, new Expression($matches[1]));
                $sql = preg_replace($pattern, '', $sql);
            }
        }

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $limit = $pagination->getLimit();
            $offset = $pagination->getOffset();
        }

        $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($sql, $orders, $limit, $offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    /**
     * {@inheritdoc}
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
        }

        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        return (new Query([
            'from' => ['sub' => "({$this->sql})"],
            'params' => $this->params,
        ]))->count('*', $this->db);
    }
}
