<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use stdClass;
use yii\base\Component;
use yii\base\Event;
use yiiunit\TestCase;

/**
 * @group base
 */
class EventTest extends TestCase
{
    public $counter;

    protected function setUp(): void
    {
        $this->counter = 0;
        Event::offAll();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Event::offAll();
    }

    public function testOn(): void
    {
        Event::on(Post::class, 'save', function ($event): void {
            $this->counter += 1;
        });
        Event::on(ActiveRecord::class, 'save', function ($event): void {
            $this->counter += 3;
        });
        Event::on('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT, function ($event): void {
            $this->counter += 5;
        });
        $this->assertEquals(0, $this->counter);
        $post = new Post();
        $post->save();
        $this->assertEquals(4, $this->counter);
        $user = new User();
        $user->save();
        $this->assertEquals(7, $this->counter);
        $someClass = new SomeClass();
        $someClass->emitEvent();
        $this->assertEquals(12, $this->counter);
        $childClass = new SomeSubclass();
        $childClass->emitEventInSubclass();
        $this->assertEquals(17, $this->counter);
    }

    public function testOff(): void
    {
        $handler = function ($event): void {
            $this->counter++;
        };
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        Event::on(Post::class, 'save', $handler);
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        Event::off(Post::class, 'save', $handler);
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testHasHandlers(): void
    {
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        $this->assertFalse(Event::hasHandlers(ActiveRecord::class, 'save'));
        $this->assertFalse(Event::hasHandlers('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT));
        Event::on(Post::class, 'save', function ($event): void {
            $this->counter += 1;
        });
        Event::on('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT, function ($event): void {
            $this->counter++;
        });
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        $this->assertFalse(Event::hasHandlers(ActiveRecord::class, 'save'));

        $this->assertFalse(Event::hasHandlers(User::class, 'save'));
        Event::on(ActiveRecord::class, 'save', function ($event): void {
            $this->counter += 1;
        });
        $this->assertTrue(Event::hasHandlers(User::class, 'save'));
        $this->assertTrue(Event::hasHandlers(ActiveRecord::class, 'save'));
        $this->assertTrue(Event::hasHandlers('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17336
     */
    public function testHasHandlersWithWildcard(): void
    {
        Event::on('\yiiunit\framework\base\*', 'save.*', function ($event): void {
            // do nothing
        });

        $this->assertTrue(Event::hasHandlers('yiiunit\framework\base\SomeInterface', 'save.it'), 'save.it');
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17300
     */
    public function testRunHandlersWithWildcard(): void
    {
        $triggered = false;

        Event::on('\yiiunit\framework\base\*', 'super*', function ($event) use (&$triggered): void {
            $triggered = true;
        });

        // instance-level
        $this->assertFalse($triggered);
        $someClass = new SomeClass();
        $someClass->emitEvent();
        $this->assertTrue($triggered);

        // reset
        $triggered = false;

        // class-level
        $this->assertFalse($triggered);
        Event::trigger(SomeClass::class, 'super.test');
        $this->assertTrue($triggered);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17377
     */
    public function testNoFalsePositivesWithHasHandlers(): void
    {
        $this->assertFalse(Event::hasHandlers(new stdClass(), 'foobar'));

        $component = new Component();
        $this->assertFalse($component->hasEventHandlers('foobar'));
    }

    public function testOffUnmatchedHandler(): void
    {
        $this->assertFalse(Event::hasHandlers(Post::class, 'afterSave'));
        Event::on(Post::class, 'afterSave', [$this, 'bla-bla']);
        $this->assertFalse(Event::off(Post::class, 'afterSave', [$this, 'bla-bla-bla']));
        $this->assertTrue(Event::off(Post::class, 'afterSave', [$this, 'bla-bla']));
    }

    /**
     * @depends testOn
     * @depends testHasHandlers
     */
    public function testOnWildcard(): void
    {
        Event::on(Post::class, '*', function ($event): void {
            $this->counter += 1;
        });
        Event::on('*\Post', 'save', function ($event): void {
            $this->counter += 3;
        });

        $post = new Post();
        $post->save();
        $this->assertEquals(4, $this->counter);

        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
    }

    /**
     * @depends testOnWildcard
     * @depends testOff
     */
    public function testOffWildcard(): void
    {
        $handler = function ($event): void {
            $this->counter++;
        };
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        Event::on('*\Post', 'save', $handler);
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        Event::off('*\Post', 'save', $handler);
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testOnPrependPlainHandler(): void
    {
        $order = [];
        Event::on(Post::class, 'save', function () use (&$order) {
            $order[] = 'first';
        });
        Event::on(Post::class, 'save', function () use (&$order) {
            $order[] = 'prepended';
        }, null, false);

        $post = new Post();
        $post->save();
        $this->assertSame(['prepended', 'first'], $order);
    }

    public function testOnPrependWildcardHandler(): void
    {
        $order = [];
        Event::on('*\Post', 'save', function () use (&$order) {
            $order[] = 'first';
        });
        Event::on('*\Post', 'save', function () use (&$order) {
            $order[] = 'prepended';
        }, null, false);

        $post = new Post();
        $post->save();
        $this->assertSame(['prepended', 'first'], $order);
    }

    public function testOffWildcardWithNullHandler(): void
    {
        Event::on('*\Post', 'save', function () {
        });
        Event::on('*\Post', 'save', function () {
        });
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        $this->assertTrue(Event::off('*\Post', 'save'));
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testOffReturnsFalseForEmptyWildcard(): void
    {
        $this->assertFalse(Event::off('*\Post', 'nonexistent'));
    }

    public function testHasHandlersWithObject(): void
    {
        Event::on(Post::class, 'save', function () {
        });
        $post = new Post();
        $this->assertTrue(Event::hasHandlers($post, 'save'));
    }

    public function testTriggerWithHandledEvent(): void
    {
        $order = [];
        Event::on(Post::class, 'save', function ($event) use (&$order) {
            $order[] = 'first';
            $event->handled = true;
        });
        Event::on(Post::class, 'save', function () use (&$order) {
            $order[] = 'second';
        });

        Event::trigger(Post::class, 'save');
        $this->assertSame(['first'], $order);
    }

    public function testTriggerWithNoHandlers(): void
    {
        Event::trigger(Post::class, 'nonexistent');
        $this->assertSame(0, $this->counter);
    }

    public function testTriggerWithWildcardNameNoMatch(): void
    {
        Event::on('*\Post', 'other*', function () {
            $this->counter++;
        });
        Event::trigger(Post::class, 'save');
        $this->assertSame(0, $this->counter);
    }

    public function testHasHandlersWildcardNameNoMatch(): void
    {
        Event::on('*\Post', 'delete*', function () {
        });
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testHasHandlersSkipsEmptyWildcardHandlers(): void
    {
        $this->setInaccessibleProperty(new Event(), '_eventWildcards', [
            'save' => ['*\Post' => []],
        ]);

        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testOffWildcardSpecificHandlerReturnValue(): void
    {
        $handler = function () {
            $this->counter++;
        };
        Event::on('*\Post', 'save', $handler);
        $this->assertTrue(Event::off('*\Post', 'save', $handler));
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testTriggerWithObjectSetsSender(): void
    {
        $capturedSender = null;
        Event::on(Post::class, 'save', function ($event) use (&$capturedSender) {
            $capturedSender = $event->sender;
        });
        $post = new Post();
        Event::trigger($post, 'save');
        $this->assertSame($post, $capturedSender);
    }

    public function testTriggerWithBothPlainAndWildcardHandlers(): void
    {
        $order = [];
        Event::on(Post::class, 'save', function () use (&$order) {
            $order[] = 'plain';
        });
        Event::on('*\Post', 'save', function () use (&$order) {
            $order[] = 'wildcard';
        });

        $post = new Post();
        $post->save();
        $this->assertSame(['wildcard', 'plain'], $order);
    }

    public function testHasHandlersWithMultipleWildcardNamePatterns(): void
    {
        Event::on('*\Post', 'save*', function () {
        });
        Event::on('*\Post', 'delete*', function () {
        });

        $this->assertTrue(Event::hasHandlers(Post::class, 'save.draft'));
        $this->assertTrue(Event::hasHandlers(Post::class, 'delete.all'));
        $this->assertFalse(Event::hasHandlers(Post::class, 'update'));
    }

    public function testTriggerWithMultipleWildcardNamePatterns(): void
    {
        $triggered = [];
        Event::on('*\Post', 'save*', function () use (&$triggered) {
            $triggered[] = 'save';
        });
        Event::on('*\Post', 'delete*', function () use (&$triggered) {
            $triggered[] = 'delete';
        });

        Event::trigger(Post::class, 'save.draft');
        $this->assertSame(['save'], $triggered);
    }

    public function testHasHandlersWithLeadingBackslash(): void
    {
        Event::on(Post::class, 'save', function () {
        });
        $this->assertTrue(Event::hasHandlers('\yiiunit\framework\base\Post', 'save'));
    }

    public function testTriggerWithLeadingBackslash(): void
    {
        $triggered = false;
        Event::on(Post::class, 'save', function () use (&$triggered) {
            $triggered = true;
        });
        Event::trigger('\yiiunit\framework\base\Post', 'save');
        $this->assertTrue($triggered);
    }
}

class ActiveRecord extends Component
{
    public function save(): void
    {
        $this->trigger('save');
    }
}

class Post extends ActiveRecord
{
}

class User extends ActiveRecord
{
}

interface SomeInterface
{
    public const EVENT_SUPER_EVENT = 'superEvent';
}

class SomeClass extends Component implements SomeInterface
{
    public function emitEvent(): void
    {
        $this->trigger(self::EVENT_SUPER_EVENT);
    }
}

class SomeSubclass extends SomeClass
{
    public function emitEventInSubclass(): void
    {
        $this->trigger(self::EVENT_SUPER_EVENT);
    }
}
