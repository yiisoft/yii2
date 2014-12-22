<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

/**
 * DataProviderInterface is the interface that must be implemented by data provider classes.
 *
 * Data providers are components that sort and paginate data, and provide them to widgets
 * such as [[\yii\grid\GridView]], [[\yii\widgets\ListView]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface DataProviderInterface
{
    /**
     * Prepares the data models and keys.
     *
     * This method will prepare the data models and keys that can be retrieved via
     * [[getModels()]] and [[getKeys()]].
     *
     * This method will be implicitly called by [[getModels()]] and [[getKeys()]] if it has not been called before.
     *
     * @param boolean $forcePrepare whether to force data preparation even if it has been done before.
     */
    public function prepare($forcePrepare = false);

    /**
     * Returns the number of data models in the current page.
     * This is equivalent to `count($provider->getModels())`.
     * When [[getPagination|pagination]] is false, this is the same as [[getTotalCount|totalCount]].
     * @return integer the number of data models in the current page.
     */
    public function getCount();

    /**
     * Returns the total number of data models.
     * When [[getPagination|pagination]] is false, this is the same as [[getCount|count]].
     * @return integer total number of possible data models.
     */
    public function getTotalCount();

    /**
     * Returns the data models in the current page.
     * @return array the list of data models in the current page.
     */
    public function getModels();

    /**
     * Returns the key values associated with the data models.
     * @return array the list of key values corresponding to [[getModels|models]]. Each data model in [[getModels|models]]
     * is uniquely identified by the corresponding key value in this array.
     */
    public function getKeys();

    /**
     * @return Sort the sorting object. If this is false, it means the sorting is disabled.
     */
    public function getSort();

    /**
     * @return Pagination the pagination object. If this is false, it means the pagination is disabled.
     */
    public function getPagination();
}
