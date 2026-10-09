<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web\session;

use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;
use yii\web\Session;
use yii\web\SessionIterator;
use yiiunit\TestCase;

use function basename;
use function ini_get;
use function ini_set;
use function is_file;
use function iterator_to_array;
use function session_get_cookie_params;
use function session_id;
use function session_save_path;
use function session_status;
use function session_write_close;
use function uniqid;

use const PHP_SESSION_ACTIVE;

/**
 * Unit tests for {@see \yii\web\Session} data access and flash message lifecycle across simulated requests backed by
 * a native PHP session.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 * @group web
 */
final class SessionLifecycleTest extends TestCase
{
    /**
     * `session.*` ini directives that the component or the tests may change; captured before and restored after each
     * test.
     */
    private const INI_DIRECTIVES = [
        'session.cookie_domain',
        'session.cookie_httponly',
        'session.cookie_lifetime',
        'session.cookie_partitioned',
        'session.cookie_path',
        'session.cookie_samesite',
        'session.cookie_secure',
        'session.gc_maxlifetime',
        'session.save_path',
        'session.use_cookies',
        'session.use_strict_mode',
    ];

    /**
     * @var array<string, string|false> ini values captured by {@see setUp()}.
     */
    private array $iniBackup = [];
    private string $savePath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->closeNativeSession();

        foreach (self::INI_DIRECTIVES as $directive) {
            $this->iniBackup[$directive] = ini_get($directive);
        }

        $this->savePath = Yii::getAlias('@yiiunit/runtime') . '/session-lifecycle-' . uniqid('', true);

        FileHelper::createDirectory($this->savePath);

        session_id('');
        session_save_path($this->savePath);
        ini_set('session.use_cookies', '1');
        ini_set('session.use_strict_mode', '0');
    }

    protected function tearDown(): void
    {
        $this->closeNativeSession();

        session_id('');

        foreach ($this->iniBackup as $directive => $value) {
            if ($value !== false) {
                ini_set($directive, $value);
            }
        }

        FileHelper::removeDirectory($this->savePath);

        parent::tearDown();
    }

    public function testSetOpensSessionAndValueSurvivesNextRequest(): void
    {
        $session = new Session();

        $this->assertFalse(
            $session->getIsActive(),
            'Session must start inactive.',
        );

        $session->set('key', 'value');

        $this->assertTrue(
            $session->getIsActive(),
            'Write must open the session.',
        );
        $this->assertSame(
            'value',
            $session->get('key'),
            'Value must be readable in the same request.',
        );

        $next = $this->nextRequest($session);

        $this->assertSame(
            'value',
            $next->get('key'),
            'Value must be read back from storage.',
        );
    }

    public function testGetReturnsDefaultValueWhenKeyIsMissing(): void
    {
        $session = new Session();

        $this->assertNull(
            $session->get('missing'),
            "Missing key must yield 'null'.",
        );
        $this->assertSame(
            'fallback',
            $session->get('missing', 'fallback'),
            'Missing key must yield the default.',
        );
    }

    public function testRemoveReturnsValueForExistingKeyAndNullOtherwise(): void
    {
        $session = new Session();

        $session->set('key', 'value');

        $this->assertSame(
            'value',
            $session->remove('key'),
            'Removed value must be returned.',
        );
        $this->assertFalse(
            $session->has('key'),
            'Key must be gone after removal.',
        );
        $this->assertNull(
            $session->remove('key'),
            'Second removal must yield `null`.',
        );
    }

    public function testRemoveAllClearsDataAndFlashMessages(): void
    {
        $session = new Session();

        $session->set('key', 'value');
        $session->setFlash('info', 'message');

        $session->removeAll();

        $this->assertFalse(
            $session->has('key'),
            'Plain key must be removed.',
        );
        $this->assertFalse(
            $session->hasFlash('info'),
            'Flash message must be removed.',
        );
        $this->assertSame(
            0,
            $session->getCount(),
            'No key may remain.',
        );
    }

    public function testCountGrowsWithStoredKeys(): void
    {
        $session = new Session();

        $session->open();
        $baseline = $session->getCount();

        $session->set('a', 1);
        $session->set('b', 2);

        $this->assertSame(
            $baseline + 2,
            $session->getCount(),
            'Count must grow by the number of stored keys.',
        );
        $this->assertCount(
            $baseline + 2,
            $session,
            'Countable must match the stored keys.',
        );
    }

    public function testArrayAccessMapsToSessionData(): void
    {
        $session = new Session();

        $this->assertFalse(
            isset($session['key']),
            'Unset offset must not exist.',
        );
        $this->assertNull(
            $session['key'],
            "Unset offset must yield 'null'.",
        );

        $session['key'] = 'value';

        $this->assertTrue(
            isset($session['key']),
            'Assigned offset must exist.',
        );
        $this->assertSame(
            'value',
            $session['key'],
            'Assigned offset must return the value.',
        );
        $this->assertSame(
            'value',
            $session->get('key'),
            'Offset and key access must share storage.',
        );

        unset($session['key']);

        $this->assertFalse(
            $session->has('key'),
            'Unset offset must remove the key.',
        );
    }

    public function testIteratorTraversesSessionData(): void
    {
        $session = new Session();

        $session->set('a', 1);
        $session->set('b', 2);

        $iterator = $session->getIterator();

        $this->assertInstanceOf(
            SessionIterator::class,
            $iterator,
            'Iterator type must be the session iterator.',
        );

        $data = iterator_to_array($iterator);

        $this->assertSame(
            1,
            $data['a'] ?? null,
            'First stored pair must be yielded.',
        );
        $this->assertSame(
            2,
            $data['b'] ?? null,
            'Second stored pair must be yielded.',
        );
    }

    public function testSetFlashStoresValueAndOverwritesPreviousOne(): void
    {
        $session = new Session();

        $session->setFlash('key');

        $this->assertTrue(
            $session->getFlash('key'),
            "Default flash value must be 'true'.",
        );

        $session->setFlash('key', 'first');
        $session->setFlash('key', 'second');

        $this->assertSame(
            'second',
            $session->getFlash('key'),
            'Latest value must win.',
        );
        $this->assertTrue(
            $session->hasFlash('key'),
            'Key must be reported as present.',
        );
    }

    public function testGetFlashReturnsDefaultValueWhenKeyIsMissing(): void
    {
        $session = new Session();

        $this->assertNull(
            $session->getFlash('missing'),
            "Missing flash must yield 'null'.",
        );
        $this->assertSame(
            'default',
            $session->getFlash('missing', 'default'),
            'Missing flash must yield the default.',
        );
        $this->assertFalse(
            $session->hasFlash('missing'),
            'Missing flash must not be reported.',
        );
    }

    public function testGetFlashWithDeleteRemovesMessageImmediately(): void
    {
        $session = new Session();

        $session->setFlash('key', 'value');

        $this->assertSame(
            'value',
            $session->getFlash('key', null, true),
            'Deleted flash must still return its value.',
        );
        $this->assertFalse(
            $session->hasFlash('key'),
            'Flash must be gone in the same request.',
        );
        $this->assertFalse(
            $session->has('key'),
            'Underlying key must be removed.',
        );
    }

    public function testAddFlashCreatesArrayAppendsAndWrapsExistingScalar(): void
    {
        $session = new Session();

        $session->addFlash('notices');

        $this->assertSame(
            [true],
            $session->getFlash('notices'),
            'First message must be wrapped in an array.',
        );

        $session->addFlash('notices', 'second');

        $this->assertSame(
            [true, 'second'],
            $session->getFlash('notices'),
            'Messages must be appended in order.',
        );

        $session->setFlash('scalar', 'first');
        $session->addFlash('scalar', 'second');

        $this->assertSame(
            ['first', 'second'],
            $session->getFlash('scalar'),
            'Existing scalar must be wrapped.',
        );
    }

    public function testRemoveFlashReturnsValueAndLeavesOtherFlashesIntact(): void
    {
        $session = new Session();

        $session->setFlash('a', 'value_a');
        $session->setFlash('b', 'value_b');

        $this->assertSame(
            'value_a',
            $session->removeFlash('a'),
            'Removed flash must be returned.',
        );
        $this->assertFalse(
            $session->hasFlash('a'),
            'Removed flash must be gone.',
        );
        $this->assertFalse(
            $session->has('a'),
            'Underlying key must be removed.',
        );
        $this->assertSame(
            'value_b',
            $session->getFlash('b'),
            'Other flashes must be untouched.',
        );
        $this->assertNull(
            $session->removeFlash('missing'),
            'Missing flash must yield `null`.',
        );
    }

    public function testRemoveAllFlashesClearsMessagesAndCountersOnly(): void
    {
        $session = new Session();

        $session->set('key', 'value');
        $session->setFlash('a', 'value_a');
        $session->addFlash('b', 'value_b');

        $session->removeAllFlashes();

        $this->assertFalse(
            $session->has('a'),
            'Scalar flash value must be removed.',
        );
        $this->assertFalse(
            $session->has('b'),
            'Array flash value must be removed.',
        );
        $this->assertFalse(
            $session->has($session->flashParam),
            'Counter storage must be removed.',
        );
        $this->assertSame(
            'value',
            $session->get('key'),
            'Plain keys must be preserved.',
        );
    }

    public function testGetAllFlashesReturnsMessagesAndDropsOrphanedCounters(): void
    {
        $session = new Session();

        $session->setFlash('success', 'ok');
        $session->addFlash('errors', 'fail');
        $session->setFlash('orphan', 'gone');
        $session->remove('orphan');

        $flashes = $session->getAllFlashes();

        $this->assertSame(
            ['success' => 'ok', 'errors' => ['fail']],
            $flashes,
            'Every stored flash must be returned by key.',
        );
        $this->assertFalse(
            $session->hasFlash('orphan'),
            'Counter without a value must be dropped.',
        );
    }

    public function testGetAllFlashesWithDeleteRemovesEveryMessage(): void
    {
        $session = new Session();

        $session->setFlash('a', '1');
        $session->setFlash('b', '2');

        $this->assertSame(
            ['a' => '1', 'b' => '2'],
            $session->getAllFlashes(true),
            'Deleted flashes must be returned.',
        );
        $this->assertSame(
            [],
            $session->getAllFlashes(),
            'No flash may remain.',
        );
        $this->assertFalse(
            $session->has('a'),
            'Underlying keys must be removed.',
        );
    }

    public function testUnreadFlashSurvivesSubsequentRequests(): void
    {
        $session = new Session();

        $session->setFlash('info', 'message');

        $second = $this->nextRequest($session);

        $this->assertSame(
            'message',
            $second->get('info'),
            'Flash must be present in the next request.',
        );

        $third = $this->nextRequest($second);

        $this->assertSame(
            'message',
            $third->getFlash('info'),
            'Flash must still be readable two requests later.',
        );
    }

    public function testReadFlashIsRemovedInNextRequest(): void
    {
        $session = new Session();

        $session->setFlash('info', 'message');

        $second = $this->nextRequest($session);

        $this->assertSame(
            'message',
            $second->getFlash('info'),
            'Flash must be readable in the next request.',
        );

        $third = $this->nextRequest($second);

        $this->assertFalse(
            $third->hasFlash('info'),
            'Flash must be gone after the request that read it.',
        );
        $this->assertFalse(
            $third->has('info'),
            'Underlying key must be removed.',
        );
    }

    public function testFlashWithoutRemoveAfterAccessExpiresAfterNextRequest(): void
    {
        $session = new Session();

        $session->setFlash('info', 'message', false);

        $this->assertSame(
            'message',
            $session->getFlash('info'),
            'Flash must be readable in the current request.',
        );

        $second = $this->nextRequest($session);

        $this->assertSame(
            'message',
            $second->get('info'),
            'Reading it earlier must not shorten its lifetime.',
        );

        $third = $this->nextRequest($second);

        $this->assertFalse(
            $third->has('info'),
            'Flash must expire without being read again.',
        );
    }

    public function testOpenOnActiveSessionDoesNotAdvanceFlashExpiry(): void
    {
        $session = new Session();
        $session->setFlash('read', 'a');
        $session->setFlash('unread', 'b', false);
        $session->getFlash('read');

        $session->open();
        $session->open();

        $this->assertSame(
            'a',
            $session->get('read'),
            'Read flash must remain in the current request.',
        );
        $this->assertSame(
            'b',
            $session->get('unread'),
            'One-request flash must remain in the current request.',
        );

        $second = $this->nextRequest($session);

        $this->assertFalse(
            $second->has('read'),
            'Read flash must expire exactly one request later.',
        );
        $this->assertSame(
            'b',
            $second->get('unread'),
            'One-request flash must still be present one request later.',
        );

        $third = $this->nextRequest($second);

        $this->assertFalse(
            $third->has('unread'),
            'One-request flash must expire two requests later.',
        );
    }

    public function testOpenDropsCorruptedFlashCounters(): void
    {
        $session = new Session();

        $session->set($session->flashParam, 'corrupted');

        $next = $this->nextRequest($session);

        $this->assertFalse(
            $next->has($next->flashParam),
            'Non-array counter storage must be discarded.',
        );
        $this->assertSame(
            [],
            $next->getAllFlashes(),
            'No flash may be reported.',
        );
    }

    public function testChangingTimeoutWhileActiveKeepsDataAndSessionId(): void
    {
        $session = new Session();

        $session->set('key', 'value');
        $session->setFlash('info', 'message');
        $id = $session->getId();

        $session->setTimeout(600);

        $this->assertTrue(
            $session->getIsActive(),
            'Session must be active again.',
        );
        $this->assertSame(
            $id,
            $session->getId(),
            'Session ID must be preserved.',
        );
        $this->assertSame(
            600,
            $session->getTimeout(),
            'New timeout must be applied.',
        );
        $this->assertSame(
            'value',
            $session->get('key'),
            'Data must survive the reconfiguration.',
        );
        $this->assertSame(
            'message',
            $session->getFlash('info'),
            'Flash must survive the reconfiguration.',
        );
    }

    public function testDestroyClearsDataForTheNextRequest(): void
    {
        $session = new Session();

        $session->set('key', 'value');

        $session->destroy();

        $this->assertFalse(
            $session->getIsActive(),
            'Destroyed session must be closed.',
        );

        $next = $this->nextRequest($session);

        $this->assertFalse(
            $next->has('key'),
            'Destroyed data must not be read back.',
        );
    }

    public function testSetSavePathResolvesAliasAndStoresSessionFileThere(): void
    {
        $path = $this->savePath . '/custom';

        FileHelper::createDirectory($path);

        $session = new Session();

        $session->setSavePath('@yiiunit/runtime/' . basename($this->savePath) . '/custom');

        $this->assertSame(
            $path,
            $session->getSavePath(),
            'Alias must be resolved to the directory.',
        );

        $session->set('key', 'value');
        $session->close();

        $this->assertTrue(
            is_file($path . '/sess_' . $session->getId()),
            'Session file must land in the custom path.',
        );
    }

    public function testCookieParamsMergeNativeDefaultsWithCaseInsensitiveOverrides(): void
    {
        $session = new Session();

        $defaults = session_get_cookie_params();

        $params = $session->getCookieParams();

        $this->assertTrue(
            $params['httponly'],
            "Default must force 'httponly'.",
        );
        $this->assertSame(
            $defaults['lifetime'],
            $params['lifetime'],
            'Native lifetime must be inherited.',
        );
        $this->assertSame(
            $defaults['path'],
            $params['path'],
            'Native path must be inherited.',
        );

        $session->setCookieParams(['Lifetime' => 3600, 'Secure' => true]);
        $params = $session->getCookieParams();

        $this->assertSame(
            3600,
            $params['lifetime'],
            'Mixed-case `lifetime` key must be normalized.',
        );
        $this->assertTrue(
            $params['secure'],
            'Mixed-case `secure` key must be normalized.',
        );
        $this->assertSame(
            $defaults['httponly'],
            $params['httponly'],
            'Explicit parameters replace the initial set.',
        );
    }

    public function testOpenAppliesCookieParamsToNativeSession(): void
    {
        $session = new Session();

        $session->setCookieParams(['lifetime' => 3600, 'path' => '/app', 'secure' => true, 'httponly' => true]);

        $session->open();

        $params = session_get_cookie_params();

        $this->assertSame(
            3600,
            $params['lifetime'],
            'Lifetime must reach the native cookie parameters.',
        );
        $this->assertSame(
            '/app',
            $params['path'],
            'Path must reach the native cookie parameters.',
        );
        $this->assertTrue(
            $params['secure'],
            'Secure flag must reach the native cookie parameters.',
        );
        $this->assertTrue(
            $params['httponly'],
            'HttpOnly flag must reach the native cookie parameters.',
        );
    }

    public function testHasSessionIdDetectsSessionCookieAndHonorsExplicitValue(): void
    {
        $this->mockApplication();

        $session = new Session();

        $this->assertFalse(
            $session->getHasSessionId(),
            'No cookie means no session ID.',
        );

        $_COOKIE[$session->getName()] = 'abc';

        $withCookie = new Session();

        $this->assertTrue(
            $withCookie->getHasSessionId(),
            'Session cookie must be detected.',
        );

        $withCookie->setHasSessionId(false);

        $this->assertFalse(
            $withCookie->getHasSessionId(),
            'Explicit value must override detection.',
        );

        unset($_COOKIE[$session->getName()]);
    }

    public function testDefaultStorageHandlersAreNoOps(): void
    {
        $session = new Session();

        $this->assertFalse(
            $session->getUseCustomStorage(),
            'Native storage must be the default.',
        );
        $this->assertTrue(
            $session->openSession($this->savePath, 'name'),
            'Open handler must succeed.',
        );
        $this->assertSame(
            '',
            $session->readSession('id'),
            'Read handler must yield an empty string.',
        );
        $this->assertTrue(
            $session->writeSession('id', 'data'),
            'Write handler must succeed.',
        );
        $this->assertTrue(
            $session->closeSession(),
            'Close handler must succeed.',
        );
        $this->assertTrue(
            $session->destroySession('id'),
            'Destroy handler must succeed.',
        );
        $this->assertSame(
            0,
            $session->gcSession(1440),
            'GC handler must report zero deletions.',
        );
    }

    public function testThrowInvalidArgumentExceptionForGcProbabilityOutOfRange(): void
    {
        $session = new Session();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('GCProbability must be a value between 0 and 100.');

        $session->setGCProbability(101);
    }

    public function testThrowInvalidArgumentExceptionForSavePathThatIsNotADirectory(): void
    {
        $path = $this->savePath . '/missing';
        $session = new Session();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Session save path is not a valid directory: $path");

        $session->setSavePath($path);
    }

    /**
     * Ends the current request for `$session` and starts the next one: a fresh component bound to the same session ID.
     */
    private function nextRequest(Session $session): Session
    {
        $id = $session->getId();
        $session->close();

        $next = new Session();

        $next->setId($id);
        $next->open();

        return $next;
    }

    private function closeNativeSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
}
