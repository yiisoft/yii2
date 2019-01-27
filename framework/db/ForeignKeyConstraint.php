<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ForeignKeyConstraint 表示表 `FOREIGN KEY` 约束的元数据。
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class ForeignKeyConstraint extends Constraint
{
    /**
     * @var string|null 引用的表结构名称。
     */
    public $foreignSchemaName;
    /**
     * @var string 引用的表名。
     */
    public $foreignTableName;
    /**
     * @var string[] 引用的表列名列表。
     */
    public $foreignColumnNames;
    /**
     * @var string|null 如果要更新被引用表中的行，则执行引用操作。
     */
    public $onUpdate;
    /**
     * @var string|null 如果要删除被引用表中的行，则执行引用操作。
     */
    public $onDelete;
}
