<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use yii\base\Component;
use yii\base\Event;
use yiiunit\data\base\ActiveRecord;
use yiiunit\data\base\Post;
use yiiunit\data\base\SomeClass;
use yiiunit\data\base\SomeInterface;
use yiiunit\data\base\SomeSubclass;
use yiiunit\data\base\User;
use yiiunit\TestCase;

/**
 * Unit tests for the {@see Event} class managing class-level event handlers.
 */
#[Group('base')]
final class EventTest extends TestCase
{
    public int $counter = 0;

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
        Event::on(
            Post::class,
            'save',
            function ($event): void {
                $this->counter += 1;
            },
        );
        Event::on(
            ActiveRecord::class,
            'save',
            function ($event): void {
                $this->counter += 3;
            },
        );
        Event::on(
            'yiiunit\data\base\SomeInterface',
            SomeInterface::EVENT_SUPER_EVENT,
            function ($event) {
                $this->counter += 5;
            },
        );

        self::assertSame(
            0,
            $this->counter,
            'Should be 0 before any event is triggered.',
        );

        $post = new Post();

        $post->save();

        self::assertSame(
            4,
            $this->counter,
            'Should trigger both Post and ActiveRecord handlers.',
        );

        $user = new User();

        $user->save();

        self::assertSame(
            7,
            $this->counter,
            'Should trigger only the ActiveRecord handler.',
        );

        $someClass = new SomeClass();

        $someClass->emitEvent();

        self::assertSame(
            12,
            $this->counter,
            'Should trigger the interface handler.',
        );

        $childClass = new SomeSubclass();

        $childClass->emitEventInSubclass();

        self::assertSame(
            17,
            $this->counter,
            'Should trigger the inherited interface handler.',
        );
    }

    public function testOff(): void
    {
        $handler = function ($event) {
            $this->counter++;
        };

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            'Should not be registered before on.',
        );

        Event::on(
            Post::class,
            'save',
            $handler,
        );

        self::assertTrue(
            Event::hasHandlers(Post::class, 'save'),
            'Should be registered after on.',
        );

        Event::off(
            Post::class,
            'save',
            $handler,
        );

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            'Should be removed after off.',
        );
    }

    public function testHasHandlers(): void
    {
        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            'Should have no handlers initially.',
        );
        self::assertFalse(
            Event::hasHandlers(ActiveRecord::class, 'save'),
            'Should have no handlers initially.',
        );
        self::assertFalse(
            Event::hasHandlers(
                'yiiunit\data\base\SomeInterface',
                SomeInterface::EVENT_SUPER_EVENT
            ),
            'Should have no handlers initially.',
        );

        Event::on(
            Post::class,
            'save',
            function ($event): void {
                $this->counter += 1;
            },
        );
        Event::on(
            'yiiunit\data\base\SomeInterface',
            SomeInterface::EVENT_SUPER_EVENT,
            function ($event): void {
                $this->counter++;
            },
        );

        self::assertTrue(
            Event::hasHandlers(Post::class, 'save'),
            'Should have a handler after on.',
        );
        self::assertFalse(
            Event::hasHandlers(ActiveRecord::class, 'save'),
            'Should not inherit Post handler.',
        );

        self::assertFalse(
            Event::hasHandlers(User::class, 'save'),
            'Should have no handlers before ActiveRecord on.',
        );

        Event::on(
            ActiveRecord::class,
            'save',
            function ($event): void {
                $this->counter += 1;
            },
        );

        self::assertTrue(
            Event::hasHandlers(User::class, 'save'),
            'Should resolve via parent ActiveRecord handler.',
        );
        self::assertTrue(
            Event::hasHandlers(ActiveRecord::class, 'save'),
            'Should have a handler after on.',
        );
        self::assertTrue(
            Event::hasHandlers(
                'yiiunit\data\base\SomeInterface',
                SomeInterface::EVENT_SUPER_EVENT
            ),
            'Should have a handler after on.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17336
     */
    public function testHasHandlersWithWildcard(): void
    {
        Event::on(
            '\yiiunit\data\base\*',
            'save.*',
            static function (): void {
            },
        );

        self::assertTrue(
            Event::hasHandlers('yiiunit\data\base\SomeInterface', 'save.it'),
            "Should have a handler for 'save.it'.",
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17300
     */
    public function testRunHandlersWithWildcard(): void
    {
        $triggered = false;

        Event::on(
            '\yiiunit\data\base\*',
            'super*',
            function ($event) use (&$triggered): void {
                $triggered = true;
            },
        );

        // instance-level
        self::assertFalse(
            $triggered,
            'Should not have triggered before emitEvent.',
        );

        $someClass = new SomeClass();

        $someClass->emitEvent();

        self::assertTrue(
            $triggered,
            'Should trigger on instance-level event.',
        );

        // reset
        $triggered = false;

        // class-level
        self::assertFalse(
            $triggered,
            'Should not have triggered after reset.',
        );

        Event::trigger(SomeClass::class, 'super.test');

        self::assertTrue(
            $triggered,
            'Should trigger on class-level event.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17377
     */
    public function testNoFalsePositivesWithHasHandlers(): void
    {
        self::assertFalse(
            Event::hasHandlers(new stdClass(), 'foobar'),
            'Should have no handlers for unregistered event.',
        );

        $component = new Component();

        self::assertFalse(
            $component->hasEventHandlers('foobar'),
            'Should have no handlers for unregistered event.',
        );
    }

    public function testOffUnmatchedHandler(): void
    {
        self::assertFalse(
            Event::hasHandlers(Post::class, 'afterSave'),
            'Should not exist before registration.',
        );

        Event::on(
            Post::class,
            'afterSave',
            [$this, 'bla-bla'],
        );

        self::assertFalse(
            Event::off(Post::class, 'afterSave', [$this, 'bla-bla-bla']),
            "Should return 'false' for unmatched handler.",
        );
        self::assertTrue(
            Event::off(Post::class, 'afterSave', [$this, 'bla-bla']),
            "Should return 'true' for matched handler.",
        );
    }

    #[Depends('testOn')]
    #[Depends('testHasHandlers')]
    public function testOnWildcard(): void
    {
        Event::on(
            Post::class,
            '*',
            function ($event): void {
                $this->counter += 1;
            },
        );
        Event::on(
            '*\Post',
            'save',
            function ($event): void {
                $this->counter += 3;
            },
        );

        $post = new Post();

        $post->save();

        self::assertSame(
            4,
            $this->counter,
            'Both wildcard class and wildcard name handlers should trigger.',
        );
        self::assertTrue(
            Event::hasHandlers(Post::class, 'save'),
            'Should be detected by hasHandlers.',
        );
    }

    #[Depends('testOnWildcard')]
    #[Depends('testOff')]
    public function testOffWildcard(): void
    {
        $handler = function ($event): void {
            $this->counter++;
        };

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            'Should not exist before registration.',
        );

        Event::on(
            '*\Post',
            'save',
            $handler,
        );

        self::assertTrue(
            Event::hasHandlers(Post::class, 'save'),
            'Should be registered after on.',
        );

        Event::off(
            '*\Post',
            'save',
            $handler,
        );

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            'Should be removed after off.',
        );
    }

    public function testOnPrependPlainHandler(): void
    {
        /** @var string[] $order */
        $order = [];

        Event::on(
            Post::class,
            'save',
            static function () use (&$order): void {
                $order[] = 'first';
            },
        );
        Event::on(
            Post::class,
            'save',
            static function () use (&$order): void {
                $order[] = 'prepended';
            },
            null,
            false
        );

        $post = new Post();

        $post->save();

        self::assertSame(
            [
                'prepended',
                'first',
            ],
            $order,
            'Should execute before the first registered handler.',
        );
    }

    public function testOnPrependWildcardHandler(): void
    {
        /** @var string[] $order */
        $order = [];

        Event::on(
            '*\Post',
            'save',
            static function () use (&$order): void {
                $order[] = 'first';
            },
        );
        Event::on(
            '*\Post',
            'save',
            static function () use (&$order): void {
                $order[] = 'prepended';
            },
            null,
            false,
        );

        $post = new Post();

        $post->save();

        self::assertSame(
            ['prepended', 'first'],
            $order,
            'Should execute prepended wildcard handler before the first registered handler.',
        );
    }

    public function testOffWildcardWithNullHandler(): void
    {
        Event::on(
            '*\Post',
            'save',
            static function (): void {
            },
        );
        Event::on(
            '*\Post',
            'save',
            static function (): void {
            },
        );

        self::assertTrue(
            Event::hasHandlers(Post::class, 'save'),
            'Should exist after registration.',
        );
        self::assertTrue(
            Event::off('*\Post', 'save'),
            "Should return 'true' and remove all wildcard handlers when off is called with 'null' handler.",
        );
        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            "Should return 'false' when no handlers remain after off with 'null' handler.",
        );
    }

    public function testOffReturnsFalseForEmptyWildcard(): void
    {
        self::assertFalse(
            Event::off('*\Post', 'nonexistent'),
            "Should return 'false' when no wildcard handlers are registered for the event.",
        );
    }

    public function testHasHandlersWithObject(): void
    {
        Event::on(
            Post::class,
            'save',
            static function (): void {
            },
        );

        $post = new Post();

        self::assertTrue(
            Event::hasHandlers($post, 'save'),
            'Should resolve an object instance to its class name.',
        );
    }

    public function testTriggerWithHandledEvent(): void
    {
        /** @var string[] $order */
        $order = [];

        Event::on(
            Post::class,
            'save',
            static function (Event $event) use (&$order): void {
                $order[] = 'first';
                $event->handled = true;
            },
        );
        Event::on(
            Post::class,
            'save',
            static function () use (&$order): void {
                $order[] = 'second';
            },
        );

        Event::trigger(Post::class, 'save');

        self::assertSame(
            ['first'],
            $order,
            "Should not execute when the first handler sets handled to 'true'.",
        );
    }

    public function testTriggerWithNoHandlers(): void
    {
        Event::trigger(Post::class, 'nonexistent');

        self::assertSame(
            0,
            $this->counter,
            'Should remain at zero when triggering an event with no handlers.',
        );
    }

    public function testTriggerWithWildcardNameNoMatch(): void
    {
        Event::on(
            '*\Post',
            'other*',
            function (): void {
                $this->counter++;
            },
        );
        Event::trigger(Post::class, 'save');

        self::assertSame(
            0,
            $this->counter,
            'Should not fire when the event name does not match the pattern.',
        );
    }

    public function testHasHandlersWildcardNameNoMatch(): void
    {
        Event::on(
            '*\Post',
            'delete*',
            static function (): void {
            },
        );

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            "Should return 'false' when no wildcard name pattern matches.",
        );
    }

    public function testHasHandlersSkipsEmptyWildcardHandlers(): void
    {
        $this->setInaccessibleProperty(new Event(), '_eventWildcards', ['save' => ['*\Post' => []]]);

        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            "Should return 'false' when wildcard handler array is empty.",
        );
    }

    public function testOffWildcardSpecificHandlerReturnValue(): void
    {
        $handler = function (): void {
            $this->counter++;
        };

        Event::on(
            '*\Post',
            'save',
            $handler,
        );

        self::assertTrue(
            Event::off('*\Post', 'save', $handler),
            "Should return 'true' when removing a specific registered handler.",
        );
        self::assertFalse(
            Event::hasHandlers(Post::class, 'save'),
            "Should return 'false' when no handlers remain after removing the only handler.",
        );
    }

    public function testHasHandlersEarlyReturnNoEventsNoWildcards(): void
    {
        self::assertFalse(
            Event::hasHandlers(Post::class, 'nonexistent'),
            "Should return 'false' when no events or wildcards are registered.",
        );
    }

    public function testTriggerWithObjectSetsSender(): void
    {
        /** @var object|null $capturedSender */
        $capturedSender = null;

        Event::on(
            Post::class,
            'save',
            static function (Event $event) use (&$capturedSender): void {
                $capturedSender = $event->sender;
            },
        );

        $post = new Post();

        Event::trigger($post, 'save');

        self::assertSame(
            $post,
            $capturedSender,
            "Should be the object instance passed to 'trigger()'.",
        );
    }

    public function testTriggerWithBothPlainAndWildcardHandlers(): void
    {
        /** @var string[] $order */
        $order = [];

        Event::on(
            Post::class,
            'save',
            static function () use (&$order): void {
                $order[] = 'plain';
            },
        );
        Event::on(
            '*\Post',
            'save',
            static function () use (&$order): void {
                $order[] = 'wildcard';
            },
        );

        $post = new Post();

        $post->save();

        self::assertContains(
            'plain',
            $order,
            'Should have been triggered.',
        );
        self::assertContains(
            'wildcard',
            $order,
            'Should have been triggered.',
        );
    }

    public function testHasHandlersWithMultipleWildcardNamePatterns(): void
    {
        Event::on(
            '*\Post',
            'save*',
            static function (): void {
            },
        );
        Event::on(
            '*\Post',
            'delete*',
            static function (): void {
            },
        );

        self::assertTrue(
            Event::hasHandlers(Post::class, 'save.draft'),
            "Should fire for 'save.draft'.",
        );
        self::assertTrue(
            Event::hasHandlers(Post::class, 'delete.all'),
            "Should fire for 'delete.all'.",
        );
        self::assertFalse(
            Event::hasHandlers(Post::class, 'update'),
            'Should not fire for update.',
        );
    }

    public function testTriggerWithMultipleWildcardNamePatterns(): void
    {
        /** @var string[] $triggered */
        $triggered = [];

        Event::on(
            '*\Post',
            'save*',
            static function () use (&$triggered): void {
                $triggered[] = 'save';
            },
        );
        Event::on(
            '*\Post',
            'delete*',
            static function () use (&$triggered): void {
                $triggered[] = 'delete';
            },
        );
        Event::trigger(Post::class, 'save.draft');

        self::assertSame(
            ['save'],
            $triggered,
            "Should fire for 'save.draft'.",
        );
    }

    public function testHasHandlersWithLeadingBackslash(): void
    {
        Event::on(
            Post::class,
            'save',
            static function (): void {
            },
        );

        self::assertTrue(
            Event::hasHandlers('\yiiunit\data\base\Post', 'save'),
            'Should be stripped when checking handlers.',
        );
    }

    public function testTriggerWithLeadingBackslash(): void
    {
        $triggered = false;

        Event::on(
            Post::class,
            'save',
            static function () use (&$triggered): void {
                $triggered = true;
            },
        );
        Event::trigger('\yiiunit\data\base\Post', 'save');

        self::assertTrue(
            $triggered,
            'Should be stripped when triggering events.',
        );
    }
}
