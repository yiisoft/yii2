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

        $restoredItems = [];

        $items =  [
            2 => ['foo', null],
            3 => ['bar', null],
            1 => ['me', null],
        ];

        $expectedData = [
            ['bar', null],
            ['foo', null],
            ['me', null],
        ];

        foreach ($items as $priority => $item) {
            $eventPriorityQueue->insert($item, $priority);
        }

        foreach ($eventPriorityQueue as $item) {
            $restoredItems[] = $item;
        }

        $this->assertEquals($expectedData, $restoredItems);
    }

    /**
     * Test array priorities on EventPriorityQueue
     */
    public function testPriorityMixed()
    {
        $eventPriorityQueue = $this->getQueue();

        $restoredItems = [];

        $items =  [
            ['data' => ['foo', 'fooData'], 'priority' => [2, 2]],
            ['data' => ['bar', 'barData'], 'priority' => [2, 1]],
            ['data' => ['me', 'meData'], 'priority' => [2, 3]],
        ];

        $expectedData = [
            ['me', 'meData'],
            ['foo', 'fooData'],
            ['bar', 'barData'],
        ];

        foreach ($items as $item) {
            $eventPriorityQueue->insert($item['data'], $item['priority']);
        }

        foreach ($eventPriorityQueue as $item) {
            $restoredItems[] = $item;
        }

        $this->assertEquals($expectedData, $restoredItems);
    }

    /**
     * Test EventPriorityQueue::remove()
     */
    public function testRemove()
    {
        $eventPriorityQueue = $this->getQueue();

        $items =  [
            2 => ['foo', null],
            3 => ['bar', null],
            1 => ['me', null],
        ];

        foreach ($items as $priority => $item) {
            $eventPriorityQueue->insert($item, $priority);
        }

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

        $items =  [
            2 => ['foo', null],
            3 => ['bar', null],
            1 => ['me', null],
        ];

        foreach ($items as $priority => $item) {
            $eventPriorityQueue->insert($item, $priority);
        }

        $this->assertEquals(3, count($eventPriorityQueue));
    }

    /**
     * Test EventPriorityQueue::getMaxPriority()
     */
    public function testMaxPriority()
    {
        $eventPriorityQueue = $this->getQueue();

        $items =  [
            1 => ['foo', null],
            23 => ['bar', null],
            15 => ['me', null],
        ];
        foreach ($items as $priority => $item) {
            $eventPriorityQueue->insert($item, $priority);
        }

        $this->assertEquals(23, $eventPriorityQueue->getMaxPriority());

        $eventPriorityQueue->remove('bar');
        $this->assertEquals(15, $eventPriorityQueue->getMaxPriority());

        $eventPriorityQueue->insert(['bar', null], 13);
        $this->assertEquals(15, $eventPriorityQueue->getMaxPriority());

        $eventPriorityQueue->insert(['foot', null], 103);
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
