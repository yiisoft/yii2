<?php

namespace yiiunit\framework\console\controllers;

use Yii;
use yiiunit\TestCase;
use yii\console\controllers\CacheController;

/**
 * Unit test for [[\yii\console\controllers\CacheController]].
 * @see CacheController
 *
 * @group console
 */
class CacheControllerTest extends TestCase
{

    /**
     * @var \yiiunit\framework\console\controllers\CacheConsoledController
     */
    private $_cacheController;

    private $driverName = 'mysql';

    protected function setUp()
    {
        parent::setUp();

        $this->_cacheController = Yii::createObject([
            'class' => 'yiiunit\framework\console\controllers\CacheConsoledController',
            'interactive' => false,
        ],[null, null]); //id and module are null

        $databases = self::getParam('databases');
        $config = $databases[$this->driverName];
        $pdo_database = 'pdo_'.$this->driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            $this->markTestSkipped('pdo and '.$pdo_database.' extension are required.');
        }

        $this->mockApplication([
            'components' => [
                'firstCache' => 'yii\caching\ArrayCache',
                'secondCache' => 'yii\caching\ArrayCache',
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
        ]);

        if(isset($config['fixture'])) {
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

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Yii::$app->firstCache->get('secondKey'),'first cache data should be flushed');
        $this->assertEquals('thirdValue', Yii::$app->secondCache->get('thirdKey'), 'second cache data should not be flushed');
    }

    public function testFlushBoth()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->firstCache->set('secondKey', 'secondValue');
        Yii::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionFlush('firstCache', 'secondCache');

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Yii::$app->firstCache->get('secondKey'),'first cache data should be flushed');
        $this->assertFalse(Yii::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

    public function testNotFoundFlush()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');

        $this->_cacheController->actionFlush('notExistingCache');

        $this->assertEquals('firstValue', Yii::$app->firstCache->get('firstKey'), 'first cache data should not be flushed');
    }

    /**
     * @expectedException yii\console\Exception
     */
    public function testNothingToFlushException()
    {
        $this->_cacheController->actionFlush();
    }

    public function testFlushAll()
    {
        Yii::$app->firstCache->set('firstKey', 'firstValue');
        Yii::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionFlushAll();

        $this->assertFalse(Yii::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Yii::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

    public function testClearSchema()
    {
        $schema = Yii::$app->db->schema;
        Yii::$app->db->createCommand()->createTable('test_schema_cache', ['id' => 'pk'])->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $cacheSchema = $schema->getTableSchemas('', false);

        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Scheme should not be modified');

        Yii::$app->db->createCommand()->dropTable('test_schema_cache')->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $this->assertNotEquals($noCacheSchemas, $cacheSchema, 'Schemes should be different');

        $this->_cacheController->actionClearSchema('db');
        $cacheSchema = $schema->getTableSchemas('', false);
        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Scheme cache should be cleared');

    }

}

class CacheConsoledController extends CacheController
{

    public function stdout($string)
    {
    }

}
