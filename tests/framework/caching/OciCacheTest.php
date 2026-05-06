<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Connection;

/**
 * Unit test for {@see \yii\caching\DbCache} with Oracle driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('caching')]
class OciCacheTest extends DbCacheTest
{
    private ?Connection $_connection = null;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_oci')) {
            $this->markTestSkipped('pdo and pdo_oci extensions are required.');
        }

        $this->mockApplication();

        $db = $this->getConnection();

        if ($db->schema->getTableSchema('cache') !== null) {
            $db->createCommand()->dropTable('cache')->execute();
        }

        $db->createCommand()->createTable(
            'cache',
            [
                'id' => 'VARCHAR2(128) NOT NULL PRIMARY KEY',
                'expire' => 'NUMBER(10)',
                'data' => 'BLOB',
            ],
        )->execute();
    }

    /**
     * @param bool $reset whether to clean up the test database
     *
     * @return Connection
     */
    public function getConnection($reset = true)
    {
        if ($this->_connection === null) {
            $databases = self::getParam('databases');
            $params = $databases['oci'];
            $db = new Connection();
            $db->dsn = $params['dsn'];
            $db->username = $params['username'];
            $db->password = $params['password'];
            $db->open();

            $this->_connection = $db;
        }

        return $this->_connection;
    }
}
