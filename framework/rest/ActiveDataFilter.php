<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

/**
 * ActiveDataFilter allows composition of the filter condition in format suitable for [[\yii\db\QueryInterface::where()]].
 *
 * @see DataFilter
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.10
 */
class ActiveDataFilter extends DataFilter
{
    /**
     * @inheritdoc
     */
    protected function buildInternal()
    {
        return $this->getFilter();
    }
}