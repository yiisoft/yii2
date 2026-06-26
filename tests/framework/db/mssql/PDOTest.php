<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\mssql\PDO as MssqlPdo;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Unit tests for {@see \yii\db\mssql\PDO} last-insert-id, transaction, and attribute workarounds for the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('pdo')]
final class PDOTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testBeginTransactionAndCommitPersistsInsert(): void
    {
        $db = $this->getConnection();

        // The raw `BEGIN TRANSACTION` workaround targets the non-MARS `mssql`/`dblib` drivers; `sqlsrv` enables MARS by
        // default, which forbids an open transaction at the end of a batch, so disable MARS for this handle.
        $pdo = new MssqlPdo($db->dsn . ';MultipleActiveResultSets=false', $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $started = $pdo->beginTransaction();

        self::assertTrue(
            $started,
            "Start must report 'true0'.",
        );

        $pdo->exec(
            <<<SQL
            INSERT INTO [dbo].[profile] ([description]) VALUES ('pdo commit row')
            SQL,
        );

        $committed = $pdo->commit();

        self::assertTrue(
            $committed,
            "Commit must report 'true'.",
        );

        $count = (int) $pdo
            ->query(
                <<<SQL
                SELECT COUNT(*) FROM [dbo].[profile] WHERE [description] = 'pdo commit row'
                SQL,
            )
            ->fetchColumn();

        self::assertSame(
            1,
            $count,
            'Committed row must persist.',
        );

        $pdo->exec(
            <<<SQL
            DELETE FROM [dbo].[profile] WHERE [description] = 'pdo commit row'
            SQL,
        );
    }

    public function testBeginTransactionAndRollBackRevertsInsert(): void
    {
        $db = $this->getConnection();

        // The raw `BEGIN TRANSACTION` workaround targets the non-MARS `mssql`/`dblib` drivers; `sqlsrv` enables MARS by
        // default, which forbids an open transaction at the end of a batch, so disable MARS for this handle.
        $pdo = new MssqlPdo($db->dsn . ';MultipleActiveResultSets=false', $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $started = $pdo->beginTransaction();

        self::assertTrue(
            $started,
            "Start must report 'true'.",
        );

        $pdo->exec(
            <<<SQL
            INSERT INTO [dbo].[profile] ([description]) VALUES ('pdo rollback row')
            SQL,
        );

        $rolledBack = $pdo->rollBack();

        self::assertTrue(
            $rolledBack,
            "Roll back must report 'true'.",
        );

        $count = (int) $pdo
            ->query(
                <<<SQL
                SELECT COUNT(*) FROM [dbo].[profile] WHERE [description] = 'pdo rollback row'
                SQL,
            )
            ->fetchColumn();

        self::assertSame(
            0,
            $count,
            'Rolled-back row must not persist.',
        );
    }

    public function testGetAttributeReturnsServerVersion(): void
    {
        $db = $this->getConnection();

        $pdo = new MssqlPdo($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

        self::assertIsString(
            $version,
            "Version must be a 'string'.",
        );
        self::assertNotEmpty(
            $version,
            'Version must not be empty.',
        );
    }

    public function testLastInsertIdReturnsInsertedRowId(): void
    {
        $db = $this->getConnection();

        $pdo = new MssqlPdo($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec(
            <<<SQL
            INSERT INTO [dbo].[profile] ([description]) VALUES ('pdo last insert id')
            SQL,
        );

        $id = $pdo->lastInsertId();

        $expectedId = $pdo
            ->query(
                <<<SQL
                SELECT [id] FROM [dbo].[profile] WHERE [description] = 'pdo last insert id'
                SQL,
            )
            ->fetchColumn();

        self::assertIsString(
            $id,
            "Result must be a 'string'.",
        );
        self::assertSame(
            (string) $expectedId,
            $id,
            'Returned id must match the inserted row id.',
        );
    }
}
