<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\db\Query;
use yii\rbac\DbManager;

/**
 * Fix MSSQL trigger.
 *
 * @see https://github.com/yiisoft/yii2/pull/17966
 *
 * @author Aurelien Chretien <chretien.aurelien@gmail.com>
 * @since 2.0.35
 */
class m200409_110543_rbac_update_mssql_trigger extends Migration
{
    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }

    protected function findForeignKeyName($table, $column, $referenceTable, $referenceColumn)
    {
        return (new Query())
            ->select(['OBJECT_NAME(fkc.constraint_object_id)'])
            ->from(['fkc' => 'sys.foreign_key_columns'])
            ->innerJoin(['c' => 'sys.columns'], 'fkc.parent_object_id = c.object_id AND fkc.parent_column_id = c.column_id')
            ->innerJoin(['r' => 'sys.columns'], 'fkc.referenced_object_id = r.object_id AND fkc.referenced_column_id = r.column_id')
            ->where(
                [
                    'AND',
                    ['fkc.parent_object_id' => $this->db->schema->getRawTableName($table)],
                    ['fkc.referenced_object_id' => $this->db->schema->getRawTableName($referenceTable)],
                    ['c.name' => $column],
                    ['r.name' => $referenceColumn],
                ]
            )->scalar($this->db);
    }

    /**
     * @return bool
     */
    protected function isMSSQL()
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->isMSSQL()) {
            $authManager = $this->getAuthManager();
            $this->db = $authManager->db;
            $schema = $this->db->getSchema()->defaultSchema;
            $triggerSuffix = $this->db->schema->getRawTableName($authManager->itemChildTable);

            $this->execute("DROP TRIGGER {$schema}.trigger_{$triggerSuffix};");

            $this->execute("CREATE TRIGGER {$schema}.trigger_delete_{$triggerSuffix}
            ON {$schema}.{$authManager->itemTable}
            INSTEAD OF DELETE
            AS
            BEGIN
                  DELETE FROM {$schema}.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                  DELETE FROM {$schema}.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
            END;"
            );

            $foreignKey = $this->findForeignKeyName($authManager->itemChildTable, 'child', $authManager->itemTable, 'name');
            $this->execute("CREATE TRIGGER {$schema}.trigger_update_{$triggerSuffix}
            ON {$schema}.{$authManager->itemTable}
            INSTEAD OF UPDATE
            AS
                DECLARE @old_name NVARCHAR(64) = (SELECT name FROM deleted)
                DECLARE @new_name NVARCHAR(64) = (SELECT name FROM inserted)
            BEGIN
                IF @old_name <> @new_name
                BEGIN
                    ALTER TABLE {$authManager->itemChildTable} NOCHECK CONSTRAINT {$foreignKey};
                    UPDATE {$authManager->itemChildTable} SET child = @new_name WHERE child = @old_name;
                END
            UPDATE {$authManager->itemTable}
            SET name = (SELECT name FROM inserted),
            type = (SELECT type FROM inserted),
            description = (SELECT description FROM inserted),
            rule_name = (SELECT rule_name FROM inserted),
            data = (SELECT data FROM inserted),
            created_at = (SELECT created_at FROM inserted),
            updated_at = (SELECT updated_at FROM inserted)
            WHERE name IN (SELECT name FROM deleted)
            IF @old_name <> @new_name
                BEGIN
                    ALTER TABLE {$authManager->itemChildTable} CHECK CONSTRAINT {$foreignKey};
                END
            END;"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        if ($this->isMSSQL()) {
            $authManager = $this->getAuthManager();
            $this->db = $authManager->db;
            $schema = $this->db->getSchema()->defaultSchema;
            $triggerSuffix = $this->db->schema->getRawTableName($authManager->itemChildTable);

            $this->execute("DROP TRIGGER {$schema}.trigger_update_{$triggerSuffix};");
            $this->execute("DROP TRIGGER {$schema}.trigger_delete_{$triggerSuffix};");

            $this->execute("CREATE TRIGGER {$schema}.trigger_auth_item_child
            ON {$schema}.{$authManager->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$authManager->itemChildTable} NOCHECK CONSTRAINT FK__auth_item__child;
                        UPDATE {$authManager->itemChildTable} SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE {$authManager->itemTable}
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$authManager->itemChildTable} CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM {$schema}.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM {$schema}.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
        }
    }
}
