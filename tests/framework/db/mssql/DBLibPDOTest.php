<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\mssql\DBLibPDO;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Unit tests for {@see \yii\db\mssql\DBLibPDO} last-insert-id and attribute workarounds for the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('pdo')]
final class DBLibPDOTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testLastInsertIdReturnsFalseWithoutInsert(): void
    {
        $db = $this->getConnection();

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        self::assertFalse(
            $pdo->lastInsertId(),
            "No prior insert must yield 'false'.",
        );
    }

    public function testThrowPDOExceptionWhenAttributeUnsupported(): void
    {
        $db = $this->getConnection();

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessageMatches(
            '/^SQLSTATE\[IM001\]: Driver does not support this function: driver does not support that attribute/',
        );

        // An unsupported attribute other than `ATTR_SERVER_VERSION` must propagate through the `default` branch.
        $pdo->getAttribute(\PDO::ATTR_AUTOCOMMIT);
    }

    public function testGetAttributeReturnsServerVersion(): void
    {
        $db = $this->getConnection();

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // `sqlsrv` supports `ATTR_SERVER_VERSION` natively, so the dblib `catch` fallback is not reached here.
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

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // `OUTPUT INSERTED.[id]` returns the new id from the `INSERT` itself, so the assertion is independent of the
        // table contents.
        $expectedId = $pdo
            ->query(
                <<<SQL
                INSERT INTO [dbo].[profile] ([description]) OUTPUT INSERTED.[id] VALUES ('dblib last insert id')
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
