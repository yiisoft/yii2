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

    public function testGetAttributeReturnsServerVersion(): void
    {
        $db = $this->getConnection();

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

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

        $pdo = new DBLibPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            <<<SQL
            INSERT INTO [dbo].[profile] ([description]) VALUES ('dblib last insert id')
            SQL,
        );

        $id = $pdo->lastInsertId();

        $expectedId = $pdo
            ->query(
                <<<SQL
                SELECT [id] FROM [dbo].[profile] WHERE [description] = 'dblib last insert id'
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
