<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

/**
 * DataProviderInterface是数据提供器类必须实现的接口。
 *
 * 数据提供器是对数据进行排序和分页的组件，并将其提供给小部件
 * 如 [[\yii\grid\GridView]]，[[\yii\widgets\ListView]]。
 *
 * 有关 DataProviderInterface 的详细信息和使用信息，请参阅 [guide article on data providers](guide:output-data-providers)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface DataProviderInterface
{
    /**
     * 准备数据模型和键。
     *
     * 此方法将准备可通过 [[getModels()]] 和 [[getKeys()]] 检索的
     * 数据模型和密钥。
     *
     * 如果没有调用此方法，则它将由 [[getModels()]] 和 [[getKeys()]] 隐式调用。
     *
     * @param bool $forcePrepare 是否强制进行数据准备，即使之前已经进行过。
     */
    public function prepare($forcePrepare = false);

    /**
     * 返回当前页中的数据模型数。
     * 这相当于 `count($provider->getModels())`。
     * 当 [[getPagination|pagination]] 为 false 时，这相当于 [[getTotalCount|totalCount]]。
     * @return int 当前页中的数据模型数。
     */
    public function getCount();

    /**
     * 返回数据模型总数。
     * 当 [[getPagination|pagination]] 为 false 时，这相当于 [[getCount|count]]。
     * @return int 数据模型总数。
     */
    public function getTotalCount();

    /**
     * 返回当前页中的数据模型。
     * @return array 当前页中的数据模型列表。
     */
    public function getModels();

    /**
     * 返回与数据模型关联的键值。
     * @return array 与 [[getModels|models]] 对应的键值列表。[[getModels|models]] 中的每个数据模型
     * 都由该数组中相应的键值唯一标识。
     */
    public function getKeys();

    /**
     * @return Sort 排序对象。如果为 false，则表示排序被禁用。
     */
    public function getSort();

    /**
     * @return Pagination|false 分页对象。如果为 false，则表示禁用分页。
     */
    public function getPagination();
}
