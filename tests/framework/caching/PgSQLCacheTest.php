<?php
/**
 * @link      https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\db\Connection;

/**
 * Class for testing file cache backend.
 * @group db
 * @group caching
 * @group pgsql
 */
class PgSQLCacheTest extends DbCacheTest
{
    protected static $driverName = 'pgsql';
    private $_connection;

    protected function setUp(): void
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('pdo and pdo_pgsql extensions are required.');
        }
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVMs PgSQL implementation does not seem to support blob colums in the way they are used here.');
        }

        $this->getConnection()->createCommand('
CREATE TABLE IF NOT EXISTS "cache"
(
    "id"  varchar(128) not null,
    "expire" integer,
    "data"   bytea,
    primary key ("id")
);
        ')->execute();
    }

    /**
     * @param  bool            $reset whether to clean up the test database
     * @return Connection
     */
    public function getConnection($reset = true)
    {
        if ($this->_connection === null) {
            $databases = self::getParam('databases');
            $params = $databases[static::$driverName];
            $db = new Connection();
            $db->dsn = $params['dsn'];
            $db->username = $params['username'];
            $db->password = $params['password'];
            if ($reset) {
                $db->open();
                $lines = explode(';', file_get_contents($params['fixture']));
                foreach ($lines as $line) {
                    if (trim($line) !== '') {
                        $db->pdo->exec($line);
                    }
                }
            }
            $this->_connection = $db;
        }

        return $this->_connection;
    }
}
