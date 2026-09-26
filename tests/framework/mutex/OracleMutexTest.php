<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\mutex;

use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\mutex\OracleMutex;
use yiiunit\TestCase;

/**
 * @group mutex
 */
class OracleMutexTest extends TestCase
{
    public function testInitSucceedsWithOciDriver(): void
    {
        $db = $this->createMock(Connection::class);
        $db->method('__get')->willReturnMap([
            ['driverName', 'oci'],
        ]);

        $this->mockApplication([
            'components' => [
                'db' => $db,
            ],
        ]);

        $mutex = new OracleMutex(['db' => $db]);
        $this->assertSame($db, $mutex->db);
    }

    public function testInitSucceedsWithOdbcDriver(): void
    {
        $db = $this->createMock(Connection::class);
        $db->method('__get')->willReturnMap([
            ['driverName', 'odbc'],
        ]);

        $this->mockApplication([
            'components' => [
                'db' => $db,
            ],
        ]);

        $mutex = new OracleMutex(['db' => $db]);
        $this->assertSame($db, $mutex->db);
    }

    public function testDefaultProperties(): void
    {
        $db = $this->createMock(Connection::class);
        $db->method('__get')->willReturnMap([
            ['driverName', 'oci'],
        ]);

        $this->mockApplication();

        $mutex = new OracleMutex(['db' => $db]);
        $this->assertSame(OracleMutex::MODE_X, $mutex->lockMode);
        $this->assertFalse($mutex->releaseOnCommit);
    }

    public function testCustomProperties(): void
    {
        $db = $this->createMock(Connection::class);
        $db->method('__get')->willReturnMap([
            ['driverName', 'oci'],
        ]);

        $this->mockApplication();

        $mutex = new OracleMutex([
            'db' => $db,
            'lockMode' => OracleMutex::MODE_NL,
            'releaseOnCommit' => true,
        ]);
        $this->assertSame(OracleMutex::MODE_NL, $mutex->lockMode);
        $this->assertTrue($mutex->releaseOnCommit);
    }

    /**
     * @dataProvider invalidDriverProvider
     */
    public function testThrowsExceptionForInvalidDrivers(string $driverName): void
    {
        $db = $this->createMock(Connection::class);
        $db->method('__get')->willReturnMap([
            ['driverName', $driverName],
        ]);

        $this->mockApplication();

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('In order to use OracleMutex connection must be configured to use Oracle database.');
        new OracleMutex(['db' => $db]);
    }

    public static function invalidDriverProvider(): array
    {
        return [
            'mysql' => ['mysql'],
            'pgsql' => ['pgsql'],
            'sqlite' => ['sqlite'],
            'sqlsrv' => ['sqlsrv'],
        ];
    }
}
