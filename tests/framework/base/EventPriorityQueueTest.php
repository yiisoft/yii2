<?php
namespace yiiunit\framework\base;

use yii\base\EventPriorityQueue;
use yiiunit\TestCase;

/**
 * @group base
 */
class EventPriorityQueueTest extends TestCase
{
	/**
	 * @var EventPriorityQueue
	 */
	protected $eventPriorityQueue;

	protected function setUp()
	{
		parent::setUp();
		$this->eventPriorityQueue = new EventPriorityQueue();
	}

	/**
	 * Test that you can iterate EventPriorityQueue
	 * more than one time
	 */
	public function testDoubleIteration()
	{
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data);
		$data = ['bar', [1,2,3,4]];
		$this->eventPriorityQueue->insert($data);

		foreach ($this->eventPriorityQueue as $item) {
			// void
		}

		$this->assertEquals(2, count($this->eventPriorityQueue));
	}

	/**
	 * Test that EventPriorityQueue behaviors
	 * as a queue by default
	 */
	public function testDefaultFIFOBehavior()
	{
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data);
		$data = ['bar', null];
		$this->eventPriorityQueue->insert($data);
		$data = ['me', null];
		$this->eventPriorityQueue->insert($data);

		$counter = 0;
		foreach ($this->eventPriorityQueue as $item) {

			if (0 == $counter) {
				$this->assertEquals(['foo', null], $item);
 			} else if (1 == $counter) {
				$this->assertEquals(['bar', null], $item);
			} else {
				$this->assertEquals(['me', null], $item);
			}

			$counter++;
		}
	}

	/**
	 * Test int priorities on EventPriorityQueue
	 */
	public function testPriority()
	{
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data, 2);
		$data = ['bar', null];
		$this->eventPriorityQueue->insert($data, 1);
		$data = ['me', null];
		$this->eventPriorityQueue->insert($data, 3);

		$counter = 0;
		foreach ($this->eventPriorityQueue as $item) {

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
		$data = ['foo', 'fooData'];
		$this->eventPriorityQueue->insert($data, [2,2]);
		$data = ['bar', 'barData'];
		$this->eventPriorityQueue->insert($data, [2,1]);
		$data = ['me', 'meData'];
		$this->eventPriorityQueue->insert($data, [2,3]);

		$counter = 0;
		foreach ($this->eventPriorityQueue as $item) {

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
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data, 2);
		$data = ['bar', null];
		$this->eventPriorityQueue->insert($data, 1);
		$data = ['me', null];
		$this->eventPriorityQueue->insert($data, 3);

		$this->eventPriorityQueue->remove('me');

		foreach ($this->eventPriorityQueue as $item) {
			$this->assertNotEquals(['me', null], $item);
		}

		$this->assertEquals(2, count($this->eventPriorityQueue));
	}

	/**
	 * Test EventPriorityQueue::count()
	 */
	public function testCount()
	{
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data, 2);
		$data = ['bar', null];
		$this->eventPriorityQueue->insert($data, 1);
		$data = ['me', null];
		$this->eventPriorityQueue->insert($data, 3);

		$this->assertEquals(3, count($this->eventPriorityQueue));
	}

	/**
	 * Test EventPriorityQueue::getMaxPriority()
	 */
	public function testMaxPriority()
	{
		$data = ['foo', null];
		$this->eventPriorityQueue->insert($data, 1);
		$data = ['bar', null];
		$this->eventPriorityQueue->insert($data, 23);
		$data = ['me', null];
		$this->eventPriorityQueue->insert($data, 15);

		$this->assertEquals(23, $this->eventPriorityQueue->getMaxPriority());
		$this->eventPriorityQueue->remove('bar');
		$this->assertEquals(15, $this->eventPriorityQueue->getMaxPriority());

		$data = ['bart', null];
		$this->eventPriorityQueue->insert($data, 13);
		$this->assertEquals(15, $this->eventPriorityQueue->getMaxPriority());

		$data = ['foot', null];
		$this->eventPriorityQueue->insert($data, 103);
		$this->assertEquals(103, $this->eventPriorityQueue->getMaxPriority());
	}
}
