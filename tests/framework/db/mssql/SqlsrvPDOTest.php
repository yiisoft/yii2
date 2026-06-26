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

    public function testLastInsertIdReturnsInsertedRowId(): void
    {
        $db = $this->getConnection();

        $pdo = new SqlsrvPDO($db->dsn, $db->username, $db->password);

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $pdo->exec(
            <<<SQL
            INSERT INTO [dbo].[profile] ([description]) VALUES ('sqlsrv last insert id')
            SQL,
        );

        $id = $pdo->lastInsertId();

        $expectedId = $pdo
            ->query(
                <<<SQL
                SELECT [id] FROM [dbo].[profile] WHERE [description] = 'sqlsrv last insert id'
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
