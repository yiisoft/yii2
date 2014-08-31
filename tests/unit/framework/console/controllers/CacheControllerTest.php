<?php

namespace yiiunit\framework\console\controllers;

use Yii;
use yiiunit\TestCase;
use yii\console\controllers\CacheController;

class CacheControllerTest extends TestCase
{

    /**
     * @var \yiiunit\framework\console\controllers\CacheConsoledController
     */
    private $_cacheController;

    protected function setUp()
    {
        parent::setUp();

        $this->_cacheController = Yii::createObject([
            'class' => 'yiiunit\framework\console\controllers\CacheConsoledController',
            'interactive' => false,
        ],[null, null]); //id and module are null

        $this->mockApplication([
            'components' => [
                'firstCache' => 'yii\caching\ArrayCache',
                'secondCache' => 'yii\caching\ArrayCache',
            ],
        ]);
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

}

class CacheConsoledController extends CacheController
{

    public function stdout($string)
    {
    }

}
