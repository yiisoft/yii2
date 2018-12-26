<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * The MigrationInterface 定义了要求数据库前已实现的最小方案集。
 *
 * 每个迁移类都应该提供 [[up()]] 方法用于 "upgrading" 数据库的逻辑，
 * 以及用于 "downgrading" 逻辑的 [[down()]] 方法。
 *
 * @author Klimov Paul <klimov@zfort.com>
 * @since 2.0
 */
interface MigrationInterface
{
    /**
     * 此方法包含应用此迁移时要执行的逻辑。
     * @return bool 迁移失败返回 false，并且不应该继续执行。
     * 所有其他返回值表示迁移成功。
     */
    public function up();

    /**
     * 此方法包含删除此迁移时要执行的逻辑。
     * 默认实现会引发异常，指示无法删除迁移。
     * @return bool 迁移失败返回 false，并且不应该继续执行。
     * 所有其他返回值表示迁移成功。
     */
    public function down();
}
