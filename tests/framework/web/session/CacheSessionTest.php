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
use yii\helpers\FileHelper;
use yii\web\CacheSession;
use yiiunit\data\web\session\LegacyStrictModeSession;

/**
 * @group web
 */
class CacheSessionTest extends TestCase
{
    use SessionTestTrait;

    /**
     * @var string per-test directory of the `cache` component.
     */
    private $cachePath;
    /**
     * @var string|false `session.use_strict_mode` value captured by {@see setUp()}.
     */
    private $useStrictModeBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->closeNativeSession();

        $this->useStrictModeBackup = ini_get('session.use_strict_mode');

        session_id('');

        $this->cachePath = Yii::getAlias('@yiiunit/runtime') . '/cache-session-' . uniqid('', true);

        $this->mockApplication();

        Yii::$app->set('cache', new FileCache(['cachePath' => $this->cachePath]));
    }

    protected function tearDown(): void
    {
        $this->closeNativeSession();

        session_id('');

        if ($this->useStrictModeBackup !== false) {
            ini_set('session.use_strict_mode', $this->useStrictModeBackup);
        }

        FileHelper::removeDirectory($this->cachePath);

        parent::tearDown();
    }

    public function testCacheSession(): void
    {
        $session = new CacheSession();

        $session->writeSession('test', 'sessionData');

        $this->assertEquals(
            'sessionData',
            $session->readSession('test'),
            'Written data must be read back.',
        );

        $session->destroySession('test');

        $this->assertEquals(
            '',
            $session->readSession('test'),
            'Destroyed session must read as empty.',
        );
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

        $this->assertEquals(
            'bar',
            $session->get('foo'),
            'Value must be readable in the open session.',
        );
        $this->assertTrue(
            $session->destroySession($session->getId()),
            'Missing cache entry must count as destroyed.',
        );
    }

    public function testInitUseStrictMode(): void
    {
        $this->initStrictModeTest(CacheSession::class);
    }

    public function testUseStrictMode(): void
    {
        $this->useStrictModeTest(CacheSession::class);
    }

    public function testStrictModeReplacesUnknownIdWithNewSessionId(): void
    {
        $session = new CacheSession(['useStrictMode' => true]);

        $session->setId('unknown-id');

        $session->open();

        $id = $session->getId();

        $session->close();

        $this->assertNotSame(
            'unknown-id',
            $id,
            'Unknown ID must be replaced.',
        );
        $this->assertSame(
            [$this->cacheKeyFor($id)],
            $this->storedCacheKeys(),
            'Session must be stored under the new ID only.',
        );
    }

    public function testStrictModeKeepsKnownIdWithEmptyPayload(): void
    {
        $session = new CacheSession(['useStrictMode' => true]);

        $session->writeSession('known-id', '');
        $session->setId('known-id');

        $session->open();

        $this->assertSame(
            'known-id',
            $session->getId(),
            'Stored ID must be kept even without data.',
        );
    }

    public function testRegenerateIdInStrictModeDestroysOldSession(): void
    {
        $session = new CacheSession(['useStrictMode' => true]);

        $session->writeSession('known-id', '');
        $session->setId('known-id');

        $session->open();

        $session->regenerateID(true);

        $id = $session->getId();

        $session->close();

        $this->assertNotSame(
            'known-id',
            $id,
            'Old ID must be replaced.',
        );
        $this->assertSame(
            [$this->cacheKeyFor($id)],
            $this->storedCacheKeys(),
            'Old session must be deleted, with no intermediate ID.',
        );
    }

    public function testStrictModeRegeneratesIdFlaggedByDeprecatedOpenSessionCheck(): void
    {
        $session = new LegacyStrictModeSession(['useStrictMode' => true]);

        $session->setId('unknown-id');

        $session->open();

        $id = $session->getId();

        $session->close();

        $this->assertNotSame(
            'unknown-id',
            $id,
            'Flagged ID must be regenerated.',
        );
        $this->assertSame(
            [$this->cacheKeyFor($id)],
            $this->storedCacheKeys(),
            'Session must be stored under the new ID only.',
        );
    }

    public function testStrictModeSkipsDeprecatedRegenerationWhenIdWasAlreadyReplaced(): void
    {
        $session = new LegacyStrictModeSession(['nativeIdValidation' => true, 'useStrictMode' => true]);

        $session->setId('unknown-id');

        $session->open();

        $id = $session->getId();

        $session->close();

        $this->assertNotSame(
            'unknown-id',
            $id,
            'Unknown ID must be replaced.',
        );
        $this->assertSame(
            [$this->cacheKeyFor($id)],
            $this->storedCacheKeys(),
            'Replaced ID must not be regenerated a second time.',
        );
    }

    private function closeNativeSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    /**
     * Returns the keys of the entries stored in the per-test cache directory.
     *
     * @return string[]
     */
    private function storedCacheKeys(): array
    {
        return array_map(
            static function (string $file): string {
                return basename($file, '.bin');
            },
            FileHelper::findFiles($this->cachePath),
        );
    }

    /**
     * Returns the cache key under which {@see CacheSession} stores the given session ID.
     */
    private function cacheKeyFor(string $id): string
    {
        return Yii::$app->cache->buildKey([CacheSession::class, $id]);
    }
}
