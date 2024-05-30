<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;
use yii\data\Pagination;
use yii\data\Sort;
use yii\helpers\ArrayHelper;

/**
 * IndexAction implements the API endpoint for listing multiple models.
 *
 * For more details and usage information on IndexAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class IndexAction extends Action
{
    /**
     * @var callable|null a PHP callable that will be called to prepare a data provider that
     * should return a collection of the models. If not set, [[prepareDataProvider()]] will be used instead.
     * The signature of the callable should be:
     *
     * ```php
     * function (IndexAction $action) {
     *     // $action is the action object currently running
     * }
     * ```
     *
     * The callable should return an instance of [[ActiveDataProvider]].
     *
     * If [[dataFilter]] is set the result of [[DataFilter::build()]] will be passed to the callable as a second parameter.
     * In this case the signature of the callable should be the following:
     *
     * ```php
     * function (IndexAction $action, mixed $filter) {
     *     // $action is the action object currently running
     *     // $filter the built filter condition
     * }
     * ```
     */
    public $prepareDataProvider;
    /**
     * @var callable a PHP callable that will be called to prepare query in prepareDataProvider.
     * Should return $query.
     * For example:
     *
     * ```php
     * function ($query, $requestParams) {
     *     $query->andFilterWhere(['id' => 1]);
     *     ...
     *     return $query;
     * }
     * ```
     *
     * @since 2.0.42
     */
    public $prepareSearchQuery;
    /**
     * @var DataFilter|null data filter to be used for the search filter composition.
     * You must set up this field explicitly in order to enable filter processing.
     * For example:
     *
     * ```php
     * [
     *     'class' => 'yii\data\ActiveDataFilter',
     *     'searchModel' => function () {
     *         return (new \yii\base\DynamicModel(['id' => null, 'name' => null, 'price' => null]))
     *             ->addRule('id', 'integer')
     *             ->addRule('name', 'trim')
     *             ->addRule('name', 'string')
     *             ->addRule('price', 'number');
     *     },
     * ]
     * ```
     *
     * @see DataFilter
     *
     * @since 2.0.13
     */
    public $dataFilter;
    /**
     * @var array|Pagination|false The pagination to be used by [[prepareDataProvider()]].
     * If this is `false`, it means pagination is disabled.
     * Note: if a Pagination object is passed, it's `params` will be set to the request parameters.
     * @see Pagination
     * @since 2.0.45
     */
    public $pagination = [];
    /**
     * @var array|Sort|false The sorting to be used by [[prepareDataProvider()]].
     * If this is `false`, it means sorting is disabled.
     * Note: if a Sort object is passed, it's `params` will be set to the request parameters.
     * @see Sort
     * @since 2.0.45
     */
    public $sort = [];


    /**
     * @return ActiveDataProvider
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        return $this->prepareDataProvider();
    }

    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @return ActiveDataProvider
     */
    protected function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }

        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }

        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        $query = $modelClass::find();
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        if (is_callable($this->prepareSearchQuery)) {
            $query = call_user_func($this->prepareSearchQuery, $query, $requestParams);
        }

        if (is_array($this->pagination)) {
            $pagination = ArrayHelper::merge(
                [
                    'params' => $requestParams,
                ],
                $this->pagination
            );
        } else {
            $pagination = $this->pagination;
            if ($this->pagination instanceof Pagination) {
                $pagination->params = $requestParams;
            }
        }

        if (is_array($this->sort)) {
            $sort = ArrayHelper::merge(
                [
                    'params' => $requestParams,
                ],
                $this->sort
            );
        } else {
            $sort = $this->sort;
            if ($this->sort instanceof Sort) {
                $sort->params = $requestParams;
            }
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => $pagination,
            'sort' => $sort,
        ]);
    }
}
