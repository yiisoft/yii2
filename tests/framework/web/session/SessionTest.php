<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web\session;

use yii\base\InvalidArgumentException;
use yii\web\Session;
use yiiunit\TestCase;

class MockSession extends Session
{
    public function open()
    {
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }

    public function close()
    {
        $this->_forceRegenerateId = null;
    }

    public function getIsActive()
    {
        return isset($_SESSION);
    }

    protected function freeze()
    {
    }
    protected function unfreeze()
    {
    }
}

/**
 * @group web
 */
class SessionTest extends TestCase
{
    use SessionTestTrait;

    private $savedSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedSession = $_SESSION ?? null;
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->savedSession;
        parent::tearDown();
    }

    public function testGetUseCustomStorage(): void
    {
        $session = new MockSession();

        $this->assertFalse($session->getUseCustomStorage());
    }

    public function testSetAndGet(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('key1', 'value1');
        $this->assertSame('value1', $session->get('key1'));
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertNull($session->get('nonexistent'));
        $this->assertSame('fallback', $session->get('nonexistent', 'fallback'));
    }

    public function testRemoveExistingKey(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('key1', 'value1');
        $removed = $session->remove('key1');

        $this->assertSame('value1', $removed);
        $this->assertNull($session->get('key1'));
    }

    public function testRemoveNonexistentKeyReturnsNull(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertNull($session->remove('nonexistent'));
    }

    public function testRemoveAll(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('a', 1);
        $session->set('b', 2);
        $session->removeAll();

        $this->assertFalse($session->has('a'));
        $this->assertFalse($session->has('b'));
    }

    public function testHas(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertFalse($session->has('key1'));

        $session->set('key1', 'value1');
        $this->assertTrue($session->has('key1'));
    }

    public function testGetCount(): void
    {
        $session = new MockSession();
        $_SESSION = [];
        $session->open();

        $countBefore = $session->getCount();

        $session->set('a', 1);
        $session->set('b', 2);
        $this->assertSame($countBefore + 2, $session->getCount());
    }

    public function testCountable(): void
    {
        $session = new MockSession();
        $_SESSION = [];
        $session->open();

        $countBefore = count($session);

        $session->set('x', 'y');
        $this->assertSame($countBefore + 1, count($session));
    }

    public function testOffsetExists(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertFalse($session->offsetExists('key'));

        $session->set('key', 'val');
        $this->assertTrue($session->offsetExists('key'));
    }

    public function testOffsetGet(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertNull($session->offsetGet('key'));

        $session->set('key', 'val');
        $this->assertSame('val', $session->offsetGet('key'));
    }

    public function testOffsetSet(): void
    {
        $session = new MockSession();
        $session->open();

        $session->offsetSet('key', 'val');
        $this->assertSame('val', $session->get('key'));
    }

    public function testOffsetUnset(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('key', 'val');
        $session->offsetUnset('key');
        $this->assertFalse($session->has('key'));
    }

    public function testArrayAccessBracketSyntax(): void
    {
        $session = new MockSession();
        $session->open();

        $session['foo'] = 'bar';
        $this->assertSame('bar', $session['foo']);
        $this->assertTrue(isset($session['foo']));

        unset($session['foo']);
        $this->assertFalse(isset($session['foo']));
    }

    public function testSetFlashAndGetFlash(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('success', 'Done!');
        $this->assertSame('Done!', $session->getFlash('success'));
    }

    public function testGetFlashReturnsDefaultWhenMissing(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertNull($session->getFlash('nonexistent'));
        $this->assertSame('default', $session->getFlash('nonexistent', 'default'));
    }

    public function testGetFlashWithDelete(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('msg', 'hello');
        $value = $session->getFlash('msg', null, true);

        $this->assertSame('hello', $value);
        $this->assertFalse($session->hasFlash('msg'));
    }

    public function testGetFlashMarksForDeletionOnNextRequest(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'message');

        $session->getFlash('info');

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(1, $counters['info']);
    }

    public function testSetFlashRemoveAfterAccessFalse(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'message', false);

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(0, $counters['info']);
    }

    public function testHasFlash(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertFalse($session->hasFlash('key'));

        $session->setFlash('key', 'val');
        $this->assertTrue($session->hasFlash('key'));
    }

    public function testAddFlashCreatesArray(): void
    {
        $session = new MockSession();
        $session->open();

        $session->addFlash('notices', 'first');
        $this->assertSame(['first'], $session->getFlash('notices'));
    }

    public function testAddFlashAppendsToExistingArray(): void
    {
        $session = new MockSession();
        $session->open();

        $session->addFlash('notices', 'first');
        $session->addFlash('notices', 'second');

        $this->assertSame(['first', 'second'], $session->getFlash('notices'));
    }

    public function testAddFlashConvertsScalarToArray(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('key', 'scalar');
        $session->addFlash('key', 'appended');

        $this->assertSame(['scalar', 'appended'], $session->getFlash('key'));
    }

    public function testRemoveFlash(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('key', 'value');
        $removed = $session->removeFlash('key');

        $this->assertSame('value', $removed);
        $this->assertFalse($session->hasFlash('key'));
    }

    public function testRemoveFlashReturnsNullWhenMissing(): void
    {
        $session = new MockSession();
        $session->open();

        $this->assertNull($session->removeFlash('nonexistent'));
    }

    public function testRemoveAllFlashes(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('a', '1');
        $session->setFlash('b', '2');
        $session->removeAllFlashes();

        $this->assertFalse($session->hasFlash('a'));
        $this->assertFalse($session->hasFlash('b'));
        $this->assertFalse(isset($_SESSION[$session->flashParam]));
    }

    public function testGetAllFlashes(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('success', 'ok');
        $session->setFlash('error', 'fail');

        $all = $session->getAllFlashes();

        $this->assertSame('ok', $all['success']);
        $this->assertSame('fail', $all['error']);
        $this->assertCount(2, $all);
    }

    public function testGetAllFlashesWithDelete(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('a', '1');
        $session->setFlash('b', '2');

        $all = $session->getAllFlashes(true);

        $this->assertCount(2, $all);
        $this->assertFalse($session->hasFlash('a'));
        $this->assertFalse($session->hasFlash('b'));
    }

    public function testGetAllFlashesMarksForDeletion(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'msg');

        $session->getAllFlashes();

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(1, $counters['info']);
    }

    public function testGetAllFlashesCleansOrphanedCounters(): void
    {
        $session = new MockSession();
        $session->open();

        $_SESSION[$session->flashParam] = ['orphan' => 0];

        $all = $session->getAllFlashes();

        $this->assertEmpty($all);
        $counters = $_SESSION[$session->flashParam];
        $this->assertArrayNotHasKey('orphan', $counters);
    }

    public function testUpdateFlashCountersRemovesExpired(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('old', 'data');

        $_SESSION[$session->flashParam]['old'] = 1;

        $this->invokeMethod($session, 'updateFlashCounters');

        $this->assertFalse($session->has('old'));
        $counters = $_SESSION[$session->flashParam];
        $this->assertArrayNotHasKey('old', $counters);
    }

    public function testUpdateFlashCountersIncrementsZero(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('keep', 'data', false);

        $this->invokeMethod($session, 'updateFlashCounters');

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(1, $counters['keep']);
        $this->assertTrue($session->has('keep'));
    }

    public function testUpdateFlashCountersHandlesNonArrayParam(): void
    {
        $session = new MockSession();
        $session->open();

        $_SESSION[$session->flashParam] = 'corrupted';

        $this->invokeMethod($session, 'updateFlashCounters');

        $this->assertFalse(isset($_SESSION[$session->flashParam]));
    }

    public function testSetSavePathWithInvalidDirectoryThrows(): void
    {
        $session = new MockSession();

        $this->expectException(InvalidArgumentException::class);
        $session->setSavePath('/nonexistent/path/that/does/not/exist');
    }

    public function testSetAndGetCookieParams(): void
    {
        $session = new MockSession();

        $params = ['lifetime' => 3600, 'path' => '/', 'domain' => '.example.com', 'secure' => true, 'httponly' => true];
        $session->setCookieParams($params);

        $result = $session->getCookieParams();
        $this->assertSame(3600, $result['lifetime']);
        $this->assertSame('/', $result['path']);
        $this->assertSame('.example.com', $result['domain']);
        $this->assertTrue($result['secure']);
        $this->assertTrue($result['httponly']);
    }

    public function testCookieParamsDefaultHttponly(): void
    {
        $session = new MockSession();

        $params = $session->getCookieParams();
        $this->assertTrue($params['httponly']);
    }

    public function testSetGCProbabilityWithInvalidValueThrows(): void
    {
        $session = new Session();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('GCProbability must be a value between 0 and 100.');
        $session->setGCProbability(101);
    }

    public function testSetGCProbabilityNegativeThrows(): void
    {
        $session = new Session();

        $this->expectException(InvalidArgumentException::class);
        $session->setGCProbability(-1);
    }

    public function testSetHasSessionId(): void
    {
        $session = new MockSession();

        $session->setHasSessionId(true);
        $this->assertTrue($session->getHasSessionId());

        $session->setHasSessionId(false);
        $this->assertFalse($session->getHasSessionId());
    }

    public function testFlashParamDefault(): void
    {
        $session = new MockSession();

        $this->assertSame('__flash', $session->flashParam);
    }

    public function testSetFlashDefaultValueIsTrue(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('key');
        $this->assertTrue($session->getFlash('key'));
    }

    public function testAddFlashDefaultValueIsTrue(): void
    {
        $session = new MockSession();
        $session->open();

        $session->addFlash('key');
        $this->assertSame([true], $session->getFlash('key'));
    }

    public function testSetFlashOverwritesPreviousValue(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('key', 'first');
        $session->setFlash('key', 'second');

        $this->assertSame('second', $session->getFlash('key'));
    }

    public function testGetIteratorReturnsSessionIterator(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('a', 1);

        $iterator = $session->getIterator();
        $this->assertInstanceOf(\yii\web\SessionIterator::class, $iterator);

        $data = iterator_to_array($iterator);
        $this->assertArrayHasKey('a', $data);
        $this->assertSame(1, $data['a']);
    }

    public function testOpenSessionReturnTrue(): void
    {
        $session = new Session();

        $this->assertTrue($session->openSession('/tmp', 'test'));
    }

    public function testCloseSessionReturnTrue(): void
    {
        $session = new Session();

        $this->assertTrue($session->closeSession());
    }

    public function testReadSessionReturnsEmptyString(): void
    {
        $session = new Session();

        $this->assertSame('', $session->readSession('test-id'));
    }

    public function testWriteSessionReturnTrue(): void
    {
        $session = new Session();

        $this->assertTrue($session->writeSession('test-id', 'data'));
    }

    public function testDestroySessionReturnTrue(): void
    {
        $session = new Session();

        $this->assertTrue($session->destroySession('test-id'));
    }

    public function testGcSessionReturnsZero(): void
    {
        $session = new Session();

        $this->assertSame(0, $session->gcSession(1440));
    }

    public function testMultipleFlashKeysIndependent(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('a', 'val_a');
        $session->setFlash('b', 'val_b');
        $session->removeFlash('a');

        $this->assertFalse($session->hasFlash('a'));
        $this->assertTrue($session->hasFlash('b'));
        $this->assertSame('val_b', $session->getFlash('b'));
    }

    public function testSetOverwritesExistingValue(): void
    {
        $session = new MockSession();
        $session->open();

        $session->set('key', 'first');
        $session->set('key', 'second');

        $this->assertSame('second', $session->get('key'));
    }

    public function testRemoveAllPreservesFlashParam(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'msg');
        $session->set('normal', 'data');
        $session->removeAll();

        $this->assertFalse($session->has('normal'));
        $this->assertFalse($session->has('info'));
    }

    public function testAddFlashRemoveAfterAccessFalse(): void
    {
        $session = new MockSession();
        $session->open();

        $session->addFlash('key', 'val', false);

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(0, $counters['key']);
    }

    public function testSetFlashCounterIsExactlyMinusOne(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('key', 'val');

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(-1, $counters['key']);
    }

    public function testAddFlashCounterIsExactlyMinusOne(): void
    {
        $session = new MockSession();
        $session->open();

        $session->addFlash('key', 'val');

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(-1, $counters['key']);
    }

    public function testGetFlashWithCounterZeroDoesNotMarkForDeletion(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'msg', false);

        $counterBefore = $_SESSION[$session->flashParam]['info'];
        $this->assertSame(0, $counterBefore);

        $session->getFlash('info');

        $counterAfter = $_SESSION[$session->flashParam]['info'];
        $this->assertSame(0, $counterAfter);
    }

    public function testGetAllFlashesWithCounterZeroDoesNotMarkForDeletion(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('info', 'msg', false);

        $session->getAllFlashes();

        $counters = $_SESSION[$session->flashParam];
        $this->assertSame(0, $counters['info']);
    }

    public function testRemoveAllFlashesActuallyRemovesValues(): void
    {
        $session = new MockSession();
        $session->open();

        $session->setFlash('x', 'data_x');
        $session->setFlash('y', 'data_y');

        $this->assertTrue(isset($_SESSION['x']));
        $this->assertTrue(isset($_SESSION['y']));

        $session->removeAllFlashes();

        $this->assertFalse(isset($_SESSION['x']));
        $this->assertFalse(isset($_SESSION['y']));
    }

    public function testGetCookieParamsMergesWithSessionDefaults(): void
    {
        $session = new MockSession();

        $defaults = session_get_cookie_params();
        $result = $session->getCookieParams();

        $this->assertArrayHasKey('lifetime', $result);
        $this->assertSame($defaults['lifetime'], $result['lifetime']);
    }

    public function testGetCookieParamsCaseInsensitive(): void
    {
        $session = new MockSession();
        $session->setCookieParams(['HttpOnly' => false, 'Secure' => true]);

        $result = $session->getCookieParams();
        $this->assertFalse($result['httponly']);
        $this->assertTrue($result['secure']);
    }
}
