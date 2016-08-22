<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\data;

/**
 * The SearchInterface must represent the model behind search forms.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.10
 */
interface SearchInterface
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params the parameters (name-value pairs) to be used on filtering results.
     *
     * @return ActiveDataProvider
     */
    public function search($params);
}