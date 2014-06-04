<?php

namespace yiiunit\extensions\mongodb;

use yii\helpers\FileHelper;
use yii\mongodb\Connection;
use Yii;
use yii\mongodb\Exception;
use yiiunit\TestCase;

class MongoDbTestCase extends TestCase
{
    /**
     * @var array Mongo connection configuration.
     */
    protected $mongoDbConfig = [
        'dsn' => 'mongodb://localhost:27017',
        'defaultDatabaseName' => 'yii2test',
        'options' => [],
    ];
    /**
     * @var Connection Mongo connection instance.
     */
    protected $mongodb;

    public static function setUpBeforeClass()
    {
        static::loadClassMap();
    }

    protected function setUp()
    {
        parent::setUp();
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('mongo extension required.');
        }
        $config = self::getParam('mongodb');
        if (!empty($config)) {
            $this->mongoDbConfig = $config;
        }
        $this->mockApplication();
        static::loadClassMap();
    }

    protected function tearDown()
    {
        if ($this->mongodb) {
            $this->mongodb->close();
        }
        $this->destroyApplication();
    }

    /**
     * Adds sphinx extension files to [[Yii::$classPath]],
     * avoiding the necessity of usage Composer autoloader.
     */
    protected static function loadClassMap()
    {
        $baseNameSpace = 'yii/mongodb';
        $basePath = realpath(__DIR__. '/../../../../extensions/mongodb');
        $files = FileHelper::findFiles($basePath);
        foreach ($files as $file) {
            $classRelativePath = str_replace($basePath, '', $file);
            $classFullName = str_replace(['/', '.php'], ['\\', ''], $baseNameSpace . $classRelativePath);
            Yii::$classMap[$classFullName] = $file;
        }
    }

    /**
     * @param  boolean                 $reset whether to clean up the test database
     * @param  boolean                 $open  whether to open test database
     * @return \yii\mongodb\Connection
     */
    public function getConnection($reset = false, $open = true)
    {
        if (!$reset && $this->mongodb) {
            return $this->mongodb;
        }
        $db = new Connection;
        $db->dsn = $this->mongoDbConfig['dsn'];
        $db->defaultDatabaseName = $this->mongoDbConfig['defaultDatabaseName'];
        if (isset($this->mongoDbConfig['options'])) {
            $db->options = $this->mongoDbConfig['options'];
        }
        if ($open) {
            $db->open();
        }
        $this->mongodb = $db;

        return $db;
    }

    /**
     * Drops the specified collection.
     * @param string $name collection name.
     */
    protected function dropCollection($name)
    {
        if ($this->mongodb) {
            try {
                $this->mongodb->getCollection($name)->drop();
            } catch (Exception $e) {
                // shut down exception
            }
        }
    }

    /**
     * Drops the specified file collection.
     * @param string $name file collection name.
     */
    protected function dropFileCollection($name = 'fs')
    {
        if ($this->mongodb) {
            try {
                $this->mongodb->getFileCollection($name)->drop();
            } catch (Exception $e) {
                // shut down exception
            }
        }
    }

    /**
     * Finds all records in collection.
     * @param  \yii\mongodb\Collection $collection
     * @param  array                   $condition
     * @param  array                   $fields
     * @return array                   rows
     */
    protected function findAll($collection, $condition = [], $fields = [])
    {
        $cursor = $collection->find($condition, $fields);
        $result = [];
        foreach ($cursor as $data) {
            $result[] = $data;
        }

        return $result;
    }

    /**
     * Returns the Mongo server version.
     * @return string Mongo server version.
     */
    protected function getServerVersion()
    {
        $connection = $this->getConnection();
        $buildInfo = $connection->getDatabase()->executeCommand(['buildinfo' => true]);

        return $buildInfo['version'];
    }
}
