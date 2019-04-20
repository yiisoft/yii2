<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * TableSchema 类表示数据库表的元数据。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TableSchema extends \yii\db\TableSchema
{
    /**
     * @var string 此表所属目录（数据库）的名称。
     * 默认为 null，表示没有目录（或当前数据库不存在）。
     */
    public $catalogName;
}
