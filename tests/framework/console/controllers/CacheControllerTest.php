<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\caching\ArrayCache;
use yii\console\controllers\CacheController;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\CacheController]].
 * @see CacheController
 *
 * @group console
 * @group db
 * @group mysql
 */
class CacheControllerTest extends TestCase
{
    /**
     * @var SilencedCacheController
     */
    private $_cacheController;

    private $driverName = 'mysql';

    protected function setUp()
    {
        parent::setUp();

        $this->_cacheController = Yii::createObject([
            '__class' => \yiiunit\framework\console\controllers\SilencedCacheController::class,
            'interactive' => false,
        ], [null, null]); //id and module are null

        $databases = self::getParam('databases');
        $config = $databases[$this->driverName];
        $pdoDriver = 'pdo_' . $this->driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdoDriver)) {
            $this->markTestSkipped('pdo and ' . $pdoDriver . ' extensions are required.');
        }


        $this->mockApplication([
            'components' => [
                'firstCache' => 'yii\caching\ArrayCache',
                'secondCache' => function () {
                    return new ArrayCache();
                },
                'session' => 'yii\web\CacheSession', // should be ignored at `actionFlushAll()`
                'db' => [
                    '__class' => $config['__class'] ?? \yii\db\Connection::class,
                    'dsn' => $config['dsn'],
                    'username' => $config['username'] ?? null,
                    'password' => $config['password'] ?? null,
                    'enableSchemaCache' => true,
                    'schemaCache' => 'firstCache',
                ],
            ],
        ]);

        if (isset($config['fixture'])) {
            Yii::$app->db->open();
            $lines = explode(';', file_get_contents($config['fixture']));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    Yii::$app->db->pdo->exec($line);
                }
            }
        }
    }

    public function testFlushOne()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->firstCache->set('secondKey', 'secondValue');
        Yii::$app->secondCache->set('thirdKey', 'thirdValue');

        $this->_cacheController->actionClear('firstCache');

        $this->assertNull(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertNull(Yii::$app->firstCache->get('secondKey'), 'first cache data should be flushed');
        $this->assertEquals('thirdValue', Yii::$app->secondCache->get('thirdKey'), 'second cache data should not be flushed');
    }

    public function testClearSchema()
    {
        $schema = Yii::$app->db->schema;
        Yii::$app->db->createCommand()->createTable('test_schema_cache', ['id' => 'pk'])->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $cacheSchema = $schema->getTableSchemas('', false);

        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Schema should not be modified.');

        Yii::$app->db->createCommand()->dropTable('test_schema_cache')->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $this->assertNotEquals($noCacheSchemas, $cacheSchema, 'Schemas should be different.');

        $this->_cacheController->actionClearSchema('db');
        $cacheSchema = $schema->getTableSchemas('', false);
        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Schema cache should be flushed.');
    }

    public function testFlushBoth()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->firstCache->set('secondKey', 'secondValue');
        Yii::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionClear('firstCache', 'secondCache');

        $this->assertNull(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertNull(Yii::$app->firstCache->get('secondKey'), 'first cache data should be flushed');
        $this->assertNull(Yii::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

    public function testNotFoundClear()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');

        $this->_cacheController->actionClear('notExistingCache');

        $this->assertEquals('firstValue', Yii::$app->firstCache->get('firstKey'), 'first cache data should not be flushed');
    }

    /**
     * @expectedException \yii\console\Exception
     */
    public function testNothingToFlushException()
    {
        $this->_cacheController->actionClear();
    }

    public function testFlushAll()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionClearAll();

        $this->assertNull(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertNull(Yii::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }
}
