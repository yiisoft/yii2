<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * CheckConstraint represents the metadata of a table `CHECK` constraint.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class CheckConstraint extends Constraint
{
    /**
     * @var string the SQL of the `CHECK` constraint.
     */
    public $expression;
}
