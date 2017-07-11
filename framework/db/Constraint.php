<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Object;

/**
 * Constraint represents the metadata of a table constraint.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class Constraint extends Object
{
    /**
     * @var string[]|null list of column names the constraint belongs to.
     */
    public $columnNames;
    /**
     * @var string|null the constraint name.
     */
    public $name;
}
