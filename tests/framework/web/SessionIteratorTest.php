<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web;

use yii\web\SessionIterator;
use yiiunit\TestCase;

/**
 * @group web
 */
class SessionIteratorTest extends TestCase
{
    private $originalSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalSession = $_SESSION ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->originalSession === null) {
            unset($_SESSION);
        } else {
            $_SESSION = $this->originalSession;
        }
        parent::tearDown();
    }

    public function testEmptySession(): void
    {
        $_SESSION = [];
        $iterator = new SessionIterator();

        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->key());
        $this->assertNull($iterator->current());
    }

    public function testUndefinedSession(): void
    {
        unset($_SESSION);
        $iterator = new SessionIterator();

        $this->assertFalse($iterator->valid());
    }

    public function testIteratesOverSessionData(): void
    {
        $_SESSION = ['a' => 1, 'b' => 2, 'c' => 3];
        $iterator = new SessionIterator();

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    public function testRewind(): void
    {
        $_SESSION = ['x' => 10, 'y' => 20];
        $iterator = new SessionIterator();

        $iterator->next();
        $this->assertSame('y', $iterator->key());

        $iterator->rewind();
        $this->assertSame('x', $iterator->key());
        $this->assertSame(10, $iterator->current());
    }

    public function testKeyDeletedMidIteration(): void
    {
        $_SESSION = ['a' => 1, 'b' => 2, 'c' => 3];
        $iterator = new SessionIterator();

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
            if ($key === 'a') {
                unset($_SESSION['b']);
            }
        }

        $this->assertSame(['a' => 1, 'c' => 3], $result);
    }

    public function testCurrentReturnsNullForDeletedKey(): void
    {
        $_SESSION = ['a' => 1];
        $iterator = new SessionIterator();

        unset($_SESSION['a']);

        $this->assertNull($iterator->current());
    }

    public function testWithIntegerKeys(): void
    {
        $_SESSION = [0 => 'zero', 1 => 'one', 2 => 'two'];
        $iterator = new SessionIterator();

        $result = [];
        foreach ($iterator as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame([0 => 'zero', 1 => 'one', 2 => 'two'], $result);
    }

    public function testSingleElement(): void
    {
        $_SESSION = ['only' => 'value'];
        $iterator = new SessionIterator();

        $this->assertTrue($iterator->valid());
        $this->assertSame('only', $iterator->key());
        $this->assertSame('value', $iterator->current());

        $iterator->next();
        $this->assertFalse($iterator->valid());
        $this->assertNull($iterator->key());
    }
}
