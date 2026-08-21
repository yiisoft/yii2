<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session;

use yiiunit\TestCase;
use Yii;
use yii\caching\FileCache;
use yii\web\CacheSession;
use yii\web\SessionHandler;

/**
 * @group web
 */
class CacheSessionTest extends TestCase
{
    use SessionTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        Yii::$app->set('cache', new FileCache());
    }

    public function testCacheSession(): void
    {
        $session = new CacheSession();

        $session->writeSession('test', 'sessionData');
        $this->assertEquals('sessionData', $session->readSession('test'));
        $session->destroySession('test');
        $this->assertEquals('', $session->readSession('test'));
    }

    public function testInvalidCache(): void
    {
        $this->expectException('\Exception');
        new CacheSession(['cache' => 'invalid']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13537
     */
    public function testNotWrittenSessionDestroying(): void
    {
        $session = new CacheSession();

        $session->set('foo', 'bar');
        $this->assertEquals('bar', $session->get('foo'));

        $this->assertTrue($session->destroySession($session->getId()));
    }

    public function testInitUseStrictMode(): void
    {
        $this->initStrictModeTest(CacheSession::class);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/21068
     */
    public function testSessionHandlerCreateSid(): void
    {
        $handler = new SessionHandler(new CacheSession());

        $sid = $handler->create_sid();
        $this->assertNotEmpty($sid);
        $this->assertNotSame($sid, $handler->create_sid());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/21068
     */
    public function testSessionHandlerValidateId(): void
    {
        $session = new CacheSession();
        $handler = new SessionHandler($session);

        $session->writeSession('test', 'sessionData');
        $this->assertTrue($handler->validateId('test'));
        $this->assertFalse($handler->validateId('not-exists'));
        $session->destroySession('test');
    }

    public function testUseStrictMode(): void
    {
        $this->useStrictModeTest(CacheSession::class);
    }
}
