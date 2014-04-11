<?php

namespace yiiunit\framework\caching;

use yii\caching\DbCache;

/**
 * Class for testing file cache backend
 * @group db
 * @group caching
 */
class DbCacheTest extends CacheTestCase
{
    private $_cacheInstance;
    private $_connection;

    protected function setUp()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo and pdo_mysql extensions are required.');
        }

        parent::setUp();

        $this->getConnection()->createCommand("
            CREATE TABLE IF NOT EXISTS cache (
                id char(128) NOT NULL,
                expire int(11) DEFAULT NULL,
                data LONGBLOB,
                PRIMARY KEY (id),
                KEY expire (expire)
            );
        ")->execute();
    }

    /**
     * @param  boolean            $reset whether to clean up the test database
     * @return \yii\db\Connection
     */
    public function getConnection($reset = true)
    {
        if ($this->_connection === null) {
            $databases = $this->getParam('databases');
            $params = $databases['mysql'];
            $db = new \yii\db\Connection;
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

    /**
     * @return DbCache
     */
    protected function getCacheInstance()
    {
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new DbCache(['db' => $this->getConnection()]);
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        static::$time++;
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        static::$time++;
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testExpireAdd()
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->add('expire_testa', 'expire_testa', 2));
        static::$time++;
        $this->assertEquals('expire_testa', $cache->get('expire_testa'));
        static::$time++;
        $this->assertFalse($cache->get('expire_testa'));
    }
}
