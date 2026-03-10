<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\caching\ArrayCache;
use yii\console\controllers\CacheController;
use yii\console\ExitCode;
use yiiunit\TestCase;

/**
 * @group console
 */
class CacheControllerSqliteTest extends TestCase
{
    /**
     * @var BufferedCacheController
     */
    private $_controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication([
            'components' => [
                'arrayCache' => 'yii\caching\ArrayCache',
                'closureCache' => function () {
                    return new ArrayCache();
                },
                'interfaceCache' => 'yii\caching\CacheInterface',
                'configCache' => [
                    'class' => 'yii\caching\ArrayCache',
                ],
                'session' => 'yii\web\CacheSession',
            ],
            'container' => [
                'singletons' => [
                    'yii\caching\CacheInterface' => [
                        'class' => 'yii\caching\ArrayCache',
                    ],
                ],
            ],
        ]);

        $this->_controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);
    }

    public function testActionIndexWithCaches(): void
    {
        $this->_controller->actionIndex();
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('The following caches were found in the system:', $output);
        $this->assertStringContainsString('arrayCache', $output);
        $this->assertStringContainsString('closureCache', $output);
        $this->assertStringContainsString('configCache', $output);
    }

    public function testActionIndexWithNoCaches(): void
    {
        $this->mockApplication([
            'components' => [
                'session' => 'yii\web\CacheSession',
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $controller->actionIndex();
        $output = $controller->flushStdOutBuffer();

        $this->assertStringContainsString('No cache components were found in the system.', $output);
    }

    public function testActionFlushWithNonExistingCache(): void
    {
        $this->_controller->actionFlush('nonExistingCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('The following cache components were NOT found:', $output);
        $this->assertStringContainsString('nonExistingCache', $output);
    }

    public function testActionFlushWithAllNonExistingCachesReturnsOk(): void
    {
        $result = $this->_controller->actionFlush('noSuchCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('No cache components were found in the system.', $output);
    }

    public function testActionFlushThrowsExceptionWithNoArguments(): void
    {
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage('You should specify cache components names');

        $this->_controller->actionFlush();
    }

    public function testActionFlushSuccessfullyFlushesCache(): void
    {
        Yii::$app->arrayCache->set('testKey', 'testValue');

        $this->_controller->actionFlush('arrayCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertFalse(Yii::$app->arrayCache->get('testKey'));
        $this->assertStringContainsString('The following cache components were processed:', $output);
        $this->assertStringContainsString('arrayCache', $output);
        $this->assertStringNotContainsString('not flushed', $output);
    }

    public function testActionFlushNotifiesNotFoundAndFlushesFound(): void
    {
        Yii::$app->arrayCache->set('key', 'value');

        $this->_controller->actionFlush('arrayCache', 'nonExistingCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertFalse(Yii::$app->arrayCache->get('key'));
        $this->assertStringContainsString('The following cache components were NOT found:', $output);
        $this->assertStringContainsString('nonExistingCache', $output);
        $this->assertStringContainsString('The following cache components were processed:', $output);
    }

    public function testActionFlushAllWithNoCaches(): void
    {
        $this->mockApplication([
            'components' => [
                'session' => 'yii\web\CacheSession',
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $result = $controller->actionFlushAll();
        $output = $controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('No cache components were found in the system.', $output);
    }

    public function testActionFlushAllFlushesAllCaches(): void
    {
        Yii::$app->arrayCache->set('k1', 'v1');
        Yii::$app->closureCache->set('k2', 'v2');

        $this->_controller->actionFlushAll();
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertFalse(Yii::$app->arrayCache->get('k1'));
        $this->assertFalse(Yii::$app->closureCache->get('k2'));
        $this->assertStringContainsString('The following cache components were processed:', $output);
        $this->assertStringNotContainsString('not flushed', $output);
    }

    public function testActionFlushAllWithApcCacheShowsNotFlushed(): void
    {
        $this->mockApplication([
            'components' => [
                'apcCache' => [
                    'class' => 'yii\caching\ApcCache',
                ],
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $controller->actionFlushAll();
        $output = $controller->flushStdOutBuffer();

        $this->assertStringContainsString('apcCache', $output);
        $this->assertStringContainsString('not flushed', $output);
    }

    public function testActionIndexShowsApcCacheAsNotFlushableViaCli(): void
    {
        $this->mockApplication([
            'components' => [
                'apcCache' => [
                    'class' => 'yii\caching\ApcCache',
                ],
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $controller->actionIndex();
        $output = $controller->flushStdOutBuffer();

        $this->assertStringContainsString('apcCache', $output);
        $this->assertStringContainsString('can not be flushed via console', $output);
    }

    public function testActionFlushSchemaWithUnknownComponent(): void
    {
        $result = $this->_controller->actionFlushSchema('unknownDb');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $result);
        $this->assertStringContainsString('Unknown component "unknownDb"', $output);
    }

    public function testActionFlushSchemaWithNonConnectionComponent(): void
    {
        $result = $this->_controller->actionFlushSchema('arrayCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::UNSPECIFIED_ERROR, $result);
        $this->assertStringContainsString("doesn't inherit \\yii\\db\\Connection", $output);
    }

    public function testActionFlushSchemaSuccess(): void
    {
        $schema = $this->getMockBuilder('yii\db\Schema')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMockForAbstractClass();
        $schema->expects($this->once())->method('refresh');

        $connection = $this->getMockBuilder('yii\db\Connection')
            ->disableOriginalConstructor()
            ->onlyMethods(['getSchema'])
            ->getMock();
        $connection->method('getSchema')->willReturn($schema);

        Yii::$app->set('db', $connection);

        $result = $this->_controller->actionFlushSchema('db');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('Schema cache for component "db", was flushed.', $output);
    }

    public function testActionFlushSchemaWithException(): void
    {
        $schema = $this->getMockBuilder('yii\db\Schema')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMockForAbstractClass();
        $schema->method('refresh')->willThrowException(new \Exception('Schema refresh failed'));

        $connection = $this->getMockBuilder('yii\db\Connection')
            ->disableOriginalConstructor()
            ->onlyMethods(['getSchema'])
            ->getMock();
        $connection->method('getSchema')->willReturn($schema);

        Yii::$app->set('mockDb', $connection);

        $result = $this->_controller->actionFlushSchema('mockDb');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('Schema refresh failed', $output);
    }

    public function testActionFlushSchemaUsesDefaultDbParameter(): void
    {
        $schema = $this->getMockBuilder('yii\db\Schema')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMockForAbstractClass();
        $schema->expects($this->once())->method('refresh');

        $connection = $this->getMockBuilder('yii\db\Connection')
            ->disableOriginalConstructor()
            ->onlyMethods(['getSchema'])
            ->getMock();
        $connection->method('getSchema')->willReturn($schema);

        Yii::$app->set('db', $connection);

        $result = $this->_controller->actionFlushSchema();
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('Schema cache for component "db", was flushed.', $output);
    }

    public function testFindCachesDiscoversArrayConfigCache(): void
    {
        $this->_controller->actionFlush('configCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('configCache', $output);
        $this->assertStringContainsString('yii\caching\ArrayCache', $output);
    }

    public function testFindCachesDiscoversStringConfigCache(): void
    {
        $this->_controller->actionFlush('arrayCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('arrayCache', $output);
        $this->assertStringContainsString('yii\caching\ArrayCache', $output);
    }

    public function testFindCachesDiscoversClosureConfigCache(): void
    {
        $this->_controller->actionFlush('closureCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('closureCache', $output);
        $this->assertStringContainsString('yii\caching\ArrayCache', $output);
    }

    public function testFindCachesDiscoversInterfaceConfigCache(): void
    {
        $this->_controller->actionFlush('interfaceCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('interfaceCache', $output);
    }

    public function testFindCachesDiscoversCacheInterfaceInstance(): void
    {
        $cache = new ArrayCache();
        $this->mockApplication([
            'components' => [
                'instanceCache' => $cache,
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $controller->actionFlush('instanceCache');
        $output = $controller->flushStdOutBuffer();

        $this->assertStringContainsString('instanceCache', $output);
        $this->assertStringContainsString('yii\caching\ArrayCache', $output);
    }

    public function testFindCachesIgnoresNonCacheComponents(): void
    {
        $this->_controller->actionIndex();
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringNotContainsString('session', $output);
        $this->assertStringNotContainsString('CacheSession', $output);
    }

    public function testFindCachesIgnoresNonCacheClosure(): void
    {
        $this->mockApplication([
            'components' => [
                'notACacheClosure' => function () {
                    return new \stdClass();
                },
                'arrayCache' => 'yii\caching\ArrayCache',
            ],
        ]);

        $controller = Yii::createObject([
            'class' => BufferedCacheController::class,
            'interactive' => false,
        ], [null, null]);

        $controller->actionIndex();
        $output = $controller->flushStdOutBuffer();

        $this->assertStringNotContainsString('notACacheClosure', $output);
        $this->assertStringContainsString('arrayCache', $output);
    }

    public function testNotifyFlushedShowsSuccessForFlushedCache(): void
    {
        Yii::$app->arrayCache->set('key', 'value');
        $this->_controller->actionFlush('arrayCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('arrayCache (yii\caching\ArrayCache)', $output);
        $this->assertStringNotContainsString('not flushed', $output);
    }

    public function testConfirmFlushOutputListsCacheNames(): void
    {
        $this->_controller->actionFlush('arrayCache', 'configCache');
        $output = $this->_controller->flushStdOutBuffer();

        $this->assertStringContainsString('The following cache components will be flushed:', $output);
        $this->assertStringContainsString('arrayCache', $output);
        $this->assertStringContainsString('configCache', $output);
    }

    public function testActionFlushReturnsOkWhenConfirmDeclined(): void
    {
        $controller = $this->getMockBuilder(BufferedCacheController::class)
            ->setConstructorArgs([null, null])
            ->onlyMethods(['confirm'])
            ->getMock();
        $controller->interactive = false;
        $controller->method('confirm')->willReturn(false);

        $result = $controller->actionFlush('arrayCache');
        $output = $controller->flushStdOutBuffer();

        $this->assertSame(ExitCode::OK, $result);
        $this->assertStringContainsString('The following cache components will be flushed:', $output);
        $this->assertStringNotContainsString('The following cache components were processed:', $output);
    }

    public function testActionFlushSchemaReturnsOkWhenConfirmDeclined(): void
    {
        $schema = $this->getMockBuilder('yii\db\Schema')
            ->disableOriginalConstructor()
            ->onlyMethods(['refresh'])
            ->getMockForAbstractClass();
        $schema->expects($this->never())->method('refresh');

        $connection = $this->getMockBuilder('yii\db\Connection')
            ->disableOriginalConstructor()
            ->onlyMethods(['getSchema'])
            ->getMock();
        $connection->method('getSchema')->willReturn($schema);

        Yii::$app->set('db', $connection);

        $controller = $this->getMockBuilder(BufferedCacheController::class)
            ->setConstructorArgs([null, null])
            ->onlyMethods(['confirm'])
            ->getMock();
        $controller->interactive = false;
        $controller->method('confirm')->willReturn(false);

        $result = $controller->actionFlushSchema('db');

        $this->assertSame(ExitCode::OK, $result);
    }
}

class BufferedCacheController extends CacheController
{
    use StdOutBufferControllerTrait;
}
