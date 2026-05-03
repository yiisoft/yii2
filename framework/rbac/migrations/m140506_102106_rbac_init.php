<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * Initializes RBAC tables.
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class m140506_102106_rbac_init extends \yii\db\Migration
{
    /**
     * @throws InvalidConfigException if the "authManager" component is not properly configured to use DbManager.
     */
    protected function getAuthManager(): DbManager
    {
        $authManager = Yii::$app->getAuthManager();

        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException(
                'You should configure "authManager" component to use database before executing this migration.',
            );
        }

        return $authManager;
    }

    protected function isMSSQL(): bool
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    protected function isOracle(): bool
    {
        return $this->db->driverName === 'oci' || $this->db->driverName === 'oci8';
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $authManager = $this->getAuthManager();

        $this->db = $authManager->db;

        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            $authManager->ruleTable,
            [
                'name' => $this->string(64)->notNull(),
                'data' => $this->binary(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'PRIMARY KEY ([[name]])',
            ],
            $tableOptions,
        );

        $this->createTable(
            $authManager->itemTable,
            [
                'name' => $this->string(64)->notNull(),
                'type' => $this->smallInteger()->notNull(),
                'description' => $this->text(),
                'rule_name' => $this->string(64),
                'data' => $this->binary(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'PRIMARY KEY ([[name]])',
                "FOREIGN KEY ([[rule_name]]) REFERENCES {$authManager->ruleTable} ([[name]])"
                . $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
            ],
            $tableOptions,
        );

        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        $this->createTable(
            $authManager->itemChildTable,
            [
                'parent' => $this->string(64)->notNull(),
                'child' => $this->string(64)->notNull(),
                'PRIMARY KEY ([[parent]], [[child]])',
                "FOREIGN KEY ([[parent]]) REFERENCES {$authManager->itemTable} ([[name]])"
                . $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
                "FOREIGN KEY ([[child]]) REFERENCES {$authManager->itemTable} ([[name]])"
                . $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE', false),
            ],
            $tableOptions,
        );

        $this->createTable(
            $authManager->assignmentTable,
            [
                'item_name' => $this->string(64)->notNull(),
                'user_id' => $this->string(64)->notNull(),
                'created_at' => $this->integer(),
                'PRIMARY KEY ([[item_name]], [[user_id]])',
                "FOREIGN KEY ([[item_name]]) REFERENCES {$authManager->itemTable} ([[name]])"
                . $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            ],
            $tableOptions,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $authManager = $this->getAuthManager();

        $this->db = $authManager->db;

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }

    /**
     * Builds the FK clause for the current driver.
     *
     * - MySQL, PostgreSQL: both `ON DELETE` and `ON UPDATE CASCADE`.
     * - Oracle, SQLite: `ON DELETE` only (no `ON UPDATE CASCADE` support).
     * - MSSQL: same actions as MySQL/PostgreSQL when `$mssqlCascadeAllowed` is `true`. When `false` (only the
     *   `auth_item_child.child` FK), no actions are emitted because MSSQL rejects multi-path cascades on
     *   `auth_item_child` (both `parent` and `child` reference `auth_item.name`); the `child` direction is handled in
     *   PHP by {@see \yii\rbac\DbManager}.
     */
    protected function buildFkClause(string $delete = '', string $update = '', bool $mssqlCascadeAllowed = true): string
    {
        if ($this->isMSSQL() && !$mssqlCascadeAllowed) {
            return '';
        }

        if ($this->isOracle()) {
            return " {$delete}";
        }

        return implode(' ', ['', $delete, $update]);
    }
}
