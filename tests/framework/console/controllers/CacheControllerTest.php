<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

    protected function setUp(): void
    {
        parent::setUp();

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
                'thirdCache' => 'yii\caching\CacheInterface',
                'session' => 'yii\web\CacheSession', // should be ignored at `actionFlushAll()`
                'db' => [
                    'class' => isset($config['class']) ? $config['class'] : 'yii\db\Connection',
                    'dsn' => $config['dsn'],
                    'username' => isset($config['username']) ? $config['username'] : null,
                    'password' => isset($config['password']) ? $config['password'] : null,
                    'enableSchemaCache' => true,
                    'schemaCache' => 'firstCache',
                ],
            ],
            'container' => [
                'singletons' => [
                    'yii\caching\CacheInterface' => [
                        'class' => 'yii\caching\ArrayCache',
                    ],
                ]
            ],
        ]);

        $this->_cacheController = Yii::createObject([
            'class' => 'yiiunit\framework\console\controllers\SilencedCacheController',
            'interactive' => false,
        ], [null, null]); //id and module are null

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

        $this->_cacheController->actionFlush('firstCache');

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertFalse(Yii::$app->firstCache->get('secondKey'), 'first cache data should be flushed');
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

        $this->_cacheController->actionFlushSchema('db');
        $cacheSchema = $schema->getTableSchemas('', false);
        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Schema cache should be flushed.');
    }

    public function testFlushBoth()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->firstCache->set('secondKey', 'secondValue');
        Yii::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionFlush('firstCache', 'secondCache');

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertFalse(Yii::$app->firstCache->get('secondKey'), 'first cache data should be flushed');
        $this->assertFalse(Yii::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

    public function testNotFoundFlush()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');

        $this->_cacheController->actionFlush('notExistingCache');

        $this->assertEquals('firstValue', Yii::$app->firstCache->get('firstKey'), 'first cache data should not be flushed');
    }

    public function testNothingToFlushException()
    {
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('You should specify cache components names');
        
        $this->_cacheController->actionFlush();
    }

    public function testFlushAll()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->secondCache->set('secondKey', 'secondValue');
        Yii::$app->thirdCache->set('thirdKey', 'thirdValue');

        $this->_cacheController->actionFlushAll();

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'), 'first cache data should be flushed');
        $this->assertFalse(Yii::$app->secondCache->get('secondKey'), 'second cache data should be flushed');
        $this->assertFalse(Yii::$app->thirdCache->get('thirdKey'), 'third cache data should be flushed');
    }
}
