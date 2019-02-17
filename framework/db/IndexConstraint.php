<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * IndexConstraint 表示表 `INDEX` 约束的元数据。
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class IndexConstraint extends Constraint
{
    /**
     * @var bool 索引是否唯一。
     */
    public $isUnique;
    /**
     * @var bool 是否为主键创建了索引。
     */
    public $isPrimary;
}
