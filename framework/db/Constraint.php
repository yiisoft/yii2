<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;

/**
 * Constraint 表示表约束的元数据。
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class Constraint extends BaseObject
{
    /**
     * @var string[]|null 约束所属的列名列表。
     */
    public $columnNames;
    /**
     * @var string|null 约束名。
     */
    public $name;
}
