<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use yii\db\mssql\PDO;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;

/**
 * Unit tests for {@see \yii\db\mssql\PDO} last-insert-id, transaction, attribute workarounds, and SQLSRV encoding
 * constants for the MSSQL driver.
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

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLastInsertIdReturnsFalseWhenNoIdentityGenerated(): void
    {
        $db = $this->getConnection();

        $pdo = new PDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        self::assertFalse(
            $pdo->lastInsertId(),
            "No identity value must yield 'false'.",
        );
    }

    public function testThrowPDOExceptionWhenAttributeUnsupported(): void
    {
        $db = $this->getConnection();

        $pdo = new PDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches(
            '/^SQLSTATE\[IM001\]: Driver does not support this function: driver does not support that attribute/',
        );

        // An unsupported attribute other than `ATTR_SERVER_VERSION` must propagate through the `default` branch.
        $pdo->getAttribute(\PDO::ATTR_AUTOCOMMIT);
    }

    public function testBeginTransactionAndCommitPersistsInsert(): void
    {
        $db = $this->getConnection();

        // The raw `BEGIN TRANSACTION` workaround targets the non-MARS `mssql`/`dblib` drivers; `sqlsrv` enables MARS by
        // default, which forbids an open transaction at the end of a batch, so disable MARS for this handle.
        $pdo = new PDO($db->dsn . ';MultipleActiveResultSets=false', $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $started = $pdo->beginTransaction();

        self::assertTrue(
            $started,
            "Start must report 'true'.",
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
        $pdo = new PDO("{$db->dsn};MultipleActiveResultSets=false", $db->username, $db->password);

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

    /**
     * @link https://github.com/yiisoft/yii2/issues/12121
     */
    public function testSqlsrvEncodingSystemAttributeReturnsCorrectRowsFromCharColumn(): void
    {
        $config = $this->database;

        unset($config['fixture']);

        $config['attributes'] = [
            PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_SYSTEM,
        ];

        $db = $this->prepareDatabase($config, null);

        $db->createCommand(
            <<<SQL
            CREATE TABLE [dbo].[encoding_char_test] (
                [id]   INT IDENTITY PRIMARY KEY,
                [code] CHAR(10) NOT NULL
            )
            SQL,
        )->execute();
        $db->createCommand(
            <<<SQL
            CREATE INDEX [idx_encoding_char_test_code] ON [dbo].[encoding_char_test] ([code])
            SQL,
        )->execute();
        $db->createCommand(
            <<<SQL
            INSERT INTO [dbo].[encoding_char_test] ([code]) VALUES ('ABC'), ('DEF'), ('GHI')
            SQL,
        )->execute();
        $rows = $db->createCommand(
            'SELECT [code] FROM [dbo].[encoding_char_test] WHERE [code] IN (:c1, :c2)',
            [':c1' => 'ABC', ':c2' => 'GHI'],
        )->queryAll();

        self::assertCount(
            2,
            $rows,
            'ANSI-encoded bound params must match both CHAR column rows.',
        );
        self::assertSame(
            'ABC       ',
            $rows[0]['code'],
            'First matched code must be CHAR-padded ABC.',
        );
        self::assertSame(
            'GHI       ',
            $rows[1]['code'],
            'Second matched code must be CHAR-padded GHI.',
        );

        DbHelper::dropTablesIfExist($db, ['encoding_char_test']);
    }

    public function testGetAttributeReturnsServerVersion(): void
    {
        $db = $this->getConnection();

        $pdo = new PDO($db->dsn, $db->username, $db->password);

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

        $pdo = new PDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // `OUTPUT INSERTED.[id]` returns the new id from the `INSERT` itself, so the assertion is independent of the
        // table contents.
        $expectedId = $pdo
            ->query(
                <<<SQL
                INSERT INTO [dbo].[profile] ([description]) OUTPUT INSERTED.[id] VALUES ('pdo last insert id')
                SQL,
            )
            ->fetchColumn();

        $id = $pdo->lastInsertId();

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
