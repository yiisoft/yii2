<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\mssql\SqlsrvPDO;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Unit tests for {@see \yii\db\mssql\SqlsrvPDO} last-insert-id workaround for the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('pdo')]
final class SqlsrvPDOTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testLastInsertIdForwardsSequenceNameToParent(): void
    {
        $db = $this->getConnection();

        $pdo = new SqlsrvPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // A non-empty sequence name is forwarded to the native driver, which reports an empty string for `sqlsrv`.
        self::assertSame(
            '',
            $pdo->lastInsertId('non_existent_sequence'),
            'Explicit sequence name must be forwarded to the parent driver.',
        );
    }

    public function testLastInsertIdReturnsInsertedRowId(): void
    {
        $db = $this->getConnection();

        $pdo = new SqlsrvPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // `OUTPUT INSERTED.[id]` returns the new id from the `INSERT` itself, so the assertion is independent of the
        // table contents.
        $expectedId = $pdo
            ->query(
                <<<SQL
                INSERT INTO [dbo].[profile] ([description]) OUTPUT INSERTED.[id] VALUES ('sqlsrv last insert id')
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
