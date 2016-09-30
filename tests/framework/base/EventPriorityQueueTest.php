<?php
namespace yiiunit\framework\base;

use yii\base\EventPriorityQueue;
use yiiunit\TestCase;

/**
 * @group base
 */
class EventPriorityQueueTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Test that you can iterate EventPriorityQueue
     * more than one time
     */
    public function testDoubleIteration()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', null];
        $eventPriorityQueue->insert($data);
        $data = ['bar', [1, 2, 3, 4]];
        $eventPriorityQueue->insert($data);

        foreach ($eventPriorityQueue as $item) {
            // void
        }

        $this->assertEquals(2, count($eventPriorityQueue));
    }

    /**
     * Test that EventPriorityQueue behaviors
     * as a queue by default
     */
    public function testDefaultFIFOBehavior()
    {
        $eventPriorityQueue = $this->getQueue();
        $restoredItems = [];
        $items = [
            ['foo', null],
            ['bar', null],
            ['baz', null]
        ];

        foreach ($items as $item) {
            $eventPriorityQueue->insert($item);
        }

        foreach ($eventPriorityQueue as $item) {
            $restoredItems[] = $item;
        }

        $this->assertEquals($items, $restoredItems);
    }

    /**
     * Test int priorities on EventPriorityQueue
     */
    public function testPriority()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', null];
        $eventPriorityQueue->insert($data, 2);
        $data = ['bar', null];
        $eventPriorityQueue->insert($data, 1);
        $data = ['me', null];
        $eventPriorityQueue->insert($data, 3);

        $counter = 0;
        foreach ($eventPriorityQueue as $item) {

            if (0 == $counter) {
                $this->assertEquals(['me', null], $item);
            } else if (1 == $counter) {
                $this->assertEquals(['foo', null], $item);
            } else {
                $this->assertEquals(['bar', null], $item);
            }

            $counter++;
        }
    }

    /**
     * Test array priorities on EventPriorityQueue
     */
    public function testPriorityMixed()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', 'fooData'];
        $eventPriorityQueue->insert($data, [2, 2]);
        $data = ['bar', 'barData'];
        $eventPriorityQueue->insert($data, [2, 1]);
        $data = ['me', 'meData'];
        $eventPriorityQueue->insert($data, [2, 3]);

        $counter = 0;
        foreach ($eventPriorityQueue as $item) {

            if (0 == $counter) {
                $this->assertEquals(['me', 'meData'], $item);
            } else if (1 == $counter) {
                $this->assertEquals(['foo', 'fooData'], $item);
            } else {
                $this->assertEquals(['bar', 'barData'], $item);
            }

            $counter++;
        }
    }

    /**
     * Test EventPriorityQueue::remove()
     */
    public function testRemove()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', null];
        $eventPriorityQueue->insert($data, 2);
        $data = ['bar', null];
        $eventPriorityQueue->insert($data, 1);
        $data = ['me', null];
        $eventPriorityQueue->insert($data, 3);

        $eventPriorityQueue->remove('me');

        foreach ($eventPriorityQueue as $item) {
            $this->assertNotEquals(['me', null], $item);
        }

        $this->assertEquals(2, count($eventPriorityQueue));
    }

    /**
     * Test EventPriorityQueue::count()
     */
    public function testCount()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', null];
        $eventPriorityQueue->insert($data, 2);
        $data = ['bar', null];
        $eventPriorityQueue->insert($data, 1);
        $data = ['me', null];
        $eventPriorityQueue->insert($data, 3);

        $this->assertEquals(3, count($eventPriorityQueue));
    }

    /**
     * Test EventPriorityQueue::getMaxPriority()
     */
    public function testMaxPriority()
    {
        $eventPriorityQueue = $this->getQueue();
        $data = ['foo', null];
        $eventPriorityQueue->insert($data, 1);
        $data = ['bar', null];
        $eventPriorityQueue->insert($data, 23);
        $data = ['me', null];
        $eventPriorityQueue->insert($data, 15);

        $this->assertEquals(23, $eventPriorityQueue->getMaxPriority());
        $eventPriorityQueue->remove('bar');
        $this->assertEquals(15, $eventPriorityQueue->getMaxPriority());

        $data = ['bart', null];
        $eventPriorityQueue->insert($data, 13);
        $this->assertEquals(15, $eventPriorityQueue->getMaxPriority());

        $data = ['foot', null];
        $eventPriorityQueue->insert($data, 103);
        $this->assertEquals(103, $eventPriorityQueue->getMaxPriority());
    }

    /**
     * @return EventPriorityQueue
     */
    protected function getQueue()
    {
        return new EventPriorityQueue();
    }
}
