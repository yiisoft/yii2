<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * DefaultValueConstraint 表示表 `DEFAULT` 约束的元数据。
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class DefaultValueConstraint extends Constraint
{
    /**
     * @var mixed DBMS 返回的默认值。
     */
    public $value;
}
