<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\mysql;

use Yii;
use yiiunit\base\web\session\BaseDbSession;

/**
 * Class DbSessionTest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group db-session
 * @group mysql
 */
class DbSessionTest extends BaseDbSession
{
    protected function getDriverNames()
    {
        return ['mysql'];
    }

    public function testMigrationUsesDefaultConnectionCharsetAndCollation(): void
    {
        $db = Yii::$app->db;

        $connectionCollation = $db->createCommand(
            <<<SQL
            SELECT @@collation_connection
            SQL,
        )->queryScalar();
        $tableCollation = $db->createCommand(
            <<<SQL
            SELECT TABLE_COLLATION
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'session'
            SQL,
        )->queryScalar();

        self::assertStringStartsWith(
            'utf8mb4_',
            $tableCollation,
            "Collation must belong to 'utf8mb4'.",
        );
        self::assertSame(
            $connectionCollation,
            $tableCollation,
            'Table must inherit the connection collation.',
        );
    }

    public function testMigrationUsesConfiguredConnectionCharsetAndCollation(): void
    {
        $db = Yii::$app->db;

        $db->close();

        $db->charset = 'latin1';

        $this->dropTableSession();
        $this->createTableSession();

        $connectionCollation = $db->createCommand(
            <<<SQL
            SELECT @@collation_connection
            SQL,
        )->queryScalar();
        $tableCollation = $db->createCommand(
            <<<SQL
            SELECT TABLE_COLLATION
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'session'
            SQL,
        )->queryScalar();

        self::assertSame(
            $connectionCollation,
            $tableCollation,
            "Configured 'latin1' must drive the table collation.",
        );
    }
}
