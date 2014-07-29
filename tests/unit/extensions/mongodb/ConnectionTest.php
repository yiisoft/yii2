<?php

namespace yiiunit\extensions\mongodb;

use yii\mongodb\Collection;
use yii\mongodb\file\Collection as FileCollection;
use yii\mongodb\Connection;
use yii\mongodb\Database;

/**
 * @group mongodb
 */
class ConnectionTest extends MongoDbTestCase
{
    public function testConstruct()
    {
        $connection = $this->getConnection(false);
        $params = $this->mongoDbConfig;

        $connection->open();

        $this->assertEquals($params['dsn'], $connection->dsn);
        $this->assertEquals($params['defaultDatabaseName'], $connection->defaultDatabaseName);
        $this->assertEquals($params['options'], $connection->options);
    }

    public function testOpenClose()
    {
        $connection = $this->getConnection(false, false);

        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->mongoClient);

        $connection->open();
        $this->assertTrue($connection->isActive);
        $this->assertTrue(is_object($connection->mongoClient));

        $connection->close();
        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->mongoClient);

        $connection = new Connection;
        $connection->dsn = 'unknown::memory:';
        $this->setExpectedException('yii\mongodb\Exception');
        $connection->open();
    }

    public function testGetDatabase()
    {
        $connection = $this->getConnection();

        $database = $connection->getDatabase($connection->defaultDatabaseName);
        $this->assertTrue($database instanceof Database);
        $this->assertTrue($database->mongoDb instanceof \MongoDB);

        $database2 = $connection->getDatabase($connection->defaultDatabaseName);
        $this->assertTrue($database === $database2);

        $databaseRefreshed = $connection->getDatabase($connection->defaultDatabaseName, true);
        $this->assertFalse($database === $databaseRefreshed);
    }

    /**
     * @depends testGetDatabase
     */
    public function testGetDefaultDatabase()
    {
        $connection = new Connection();
        $connection->dsn = $this->mongoDbConfig['dsn'];
        $connection->defaultDatabaseName = $this->mongoDbConfig['defaultDatabaseName'];
        $database = $connection->getDatabase();
        $this->assertTrue($database instanceof Database, 'Unable to get default database!');

        $connection = new Connection();
        $connection->dsn = $this->mongoDbConfig['dsn'];
        $connection->options = ['db' => $this->mongoDbConfig['defaultDatabaseName']];
        $database = $connection->getDatabase();
        $this->assertTrue($database instanceof Database, 'Unable to determine default database from options!');

        $connection = new Connection();
        $connection->dsn = $this->mongoDbConfig['dsn'] . '/' . $this->mongoDbConfig['defaultDatabaseName'];
        $database = $connection->getDatabase();
        $this->assertTrue($database instanceof Database, 'Unable to determine default database from dsn!');
    }

    /**
     * @depends testGetDefaultDatabase
     */
    public function testGetCollection()
    {
        $connection = $this->getConnection();

        $collection = $connection->getCollection('customer');
        $this->assertTrue($collection instanceof Collection);

        $collection2 = $connection->getCollection('customer');
        $this->assertTrue($collection === $collection2);

        $collection2 = $connection->getCollection('customer', true);
        $this->assertFalse($collection === $collection2);
    }

    /**
     * @depends testGetDefaultDatabase
     */
    public function testGetFileCollection()
    {
        $connection = $this->getConnection();

        $collection = $connection->getFileCollection('testfs');
        $this->assertTrue($collection instanceof FileCollection);

        $collection2 = $connection->getFileCollection('testfs');
        $this->assertTrue($collection === $collection2);

        $collection2 = $connection->getFileCollection('testfs', true);
        $this->assertFalse($collection === $collection2);
    }
}
