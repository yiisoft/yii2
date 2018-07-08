<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\Component;
use yii\base\Event;
use yiiunit\TestCase;

/**
 * @group base
 */
class EventTest extends TestCase
{
    public $counter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->counter = 0;
        Event::offAll();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        Event::offAll();
    }

    public function testSetupName()
    {
        $event = new Event();
        $event->setName('some.event');
        $this->assertSame('some.event', $event->getName());

        $event = new Event();
        $this->assertSame('yii.base.event', $event->getName());
    }

    public function testOn()
    {
        Event::on(Post::class, 'save', function ($event) {
            $this->counter += 1;
        });
        Event::on(ActiveRecord::class, 'save', function ($event) {
            $this->counter += 3;
        });
        Event::on('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT, function ($event) {
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

    public function testOff()
    {
        $handler = function ($event) {
            $this->counter++;
        };
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        Event::on(Post::class, 'save', $handler);
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        Event::off(Post::class, 'save', $handler);
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }

    public function testHasHandlers()
    {
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        $this->assertFalse(Event::hasHandlers(ActiveRecord::class, 'save'));
        $this->assertFalse(Event::hasHandlers('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT));
        Event::on(Post::class, 'save', function ($event) {
            $this->counter += 1;
        });
        Event::on('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT, function ($event) {
            $this->counter++;
        });
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        $this->assertFalse(Event::hasHandlers(ActiveRecord::class, 'save'));

        $this->assertFalse(Event::hasHandlers(User::class, 'save'));
        Event::on(ActiveRecord::class, 'save', function ($event) {
            $this->counter += 1;
        });
        $this->assertTrue(Event::hasHandlers(User::class, 'save'));
        $this->assertTrue(Event::hasHandlers(ActiveRecord::class, 'save'));
        $this->assertTrue(Event::hasHandlers('yiiunit\framework\base\SomeInterface', SomeInterface::EVENT_SUPER_EVENT));
    }

    /**
     * @depends testOn
     * @depends testHasHandlers
     */
    public function testOnWildcard()
    {
        Event::on(Post::class, '*', function ($event) {
            $this->counter += 1;
        });
        Event::on('*\Post', 'save', function ($event) {
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
    public function testOffWildcard()
    {
        $handler = function ($event) {
            $this->counter++;
        };
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
        Event::on('*\Post', 'save', $handler);
        $this->assertTrue(Event::hasHandlers(Post::class, 'save'));
        Event::off('*\Post', 'save', $handler);
        $this->assertFalse(Event::hasHandlers(Post::class, 'save'));
    }
}

class ActiveRecord extends Component
{
    public function save()
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
    const EVENT_SUPER_EVENT = 'superEvent';
}

class SomeClass extends Component implements SomeInterface
{
    public function emitEvent()
    {
        $this->trigger(self::EVENT_SUPER_EVENT);
    }
}

class SomeSubclass extends SomeClass
{
    public function emitEventInSubclass()
    {
        $this->trigger(self::EVENT_SUPER_EVENT);
    }
}
