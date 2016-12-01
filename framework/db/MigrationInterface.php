<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * The MigrationInterface defines the minimum set of methods to be implemented by a database migration.
 *
 * Each migration class should provide the [[up()]] method containing the logic for "upgrading" the database
 * and the [[down()]] method for the "downgrading" logic.
 *
 * @author Klimov Paul <klimov@zfort.com>
 * @since 2.0
 */
interface MigrationInterface
{
    /**
     * This method contains the logic to be executed when applying this migration.
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function up();

    /**
     * This method contains the logic to be executed when removing this migration.
     * The default implementation throws an exception indicating the migration cannot be removed.
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function down();
}
