<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

/**
 * ColumnSchema class describes the metadata of a column in a PosgreSQL database table.
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var integer the dimension of an array (the number of indices needed to select an element), 0 if not array
     */
    public $dimension;

    /**
     * @var string the delimiter character to be used between values in arrays made of this type.
     */
    public $delimiter;
}
