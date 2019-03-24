<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\conditions;

/**
 * 使用 `AND` 运算符连接两个或多个 SQL 表达式的条件。
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
class AndCondition extends ConjunctionCondition
{
    /**
     * 返回由此条件类表示的运算符，比如：`AND`，`OR`。
     *
     * @return string
     */
    public function getOperator()
    {
        return 'AND';
    }
}
