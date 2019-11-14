<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;

/**
 * IndexAction 实现一个 API 端点，用于返回模型列表。
 *
 * 关于 IndexAction 的更多使用参考，请查看 [Rest 控制器指南](guide:rest-controllers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class IndexAction extends Action
{
    /**
     * @var callable PHP 回调，用于返回一个包含了所查找模型数据集合的数据提供器（Data Provider），
     * 如果未设置，默认为 [[prepareDataProvider()]] 方法。
     * 这个回调的形式如下：
     *
     * ```php
     * function (IndexAction $action) {
     *     // $action 当前运行的动作对象
     * }
     * ```
     *
     * 这个回调应当返回 [[ActiveDataProvider]] 的实例。
     *
     * 如果设置了 [[dataFilter]] 属性 ，[[DataFilter::build()]] 的结果将作为第二个参数传递给回调。
     * 在这种情况下，这个回调的形式如下：
     *
     * ```php
     * function (IndexAction $action, mixed $filter) {
     *     // $action 当前运行的动作对象
     *     // $filter 建立的过滤条件
     * }
     * ```
     */
    public $prepareDataProvider;
    /**
     * @var DataFilter|null 数据过滤器，用于搜索过滤条件组合。
     * 您必须明确设置此字段才能启用过滤器处理。
     * 例如：
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
     * 准备包含了所查找模型数据集合的数据提供器。
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

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }
}
