<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ForeignKeyConstraint represents the metadata of a table `FOREIGN KEY` constraint.
 *
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0.13
 */
class ForeignKeyConstraint extends Constraint
{
    /**
     * @var string|null referenced table schema name.
     */
    public $foreignSchemaName;
    /**
     * @var string referenced table name.
     */
    public $foreignTableName;
    /**
     * @var string[] list of referenced table column names.
     */
    public $foreignColumnNames;
    /**
     * @var string|null referential action if rows in a referenced table are to be updated.
     */
    public $onUpdate;
    /**
     * @var string|null referential action if rows in a referenced table are to be deleted.
     */
    public $onDelete;
}
