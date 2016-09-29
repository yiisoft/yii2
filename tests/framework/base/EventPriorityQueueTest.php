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

	public function testDoubleIteration()
	{
		$handler = 'foo';
		$this->eventPriorityQueue->insert($handler);
		$handler = 'bar';
		$this->eventPriorityQueue->insert($handler);

		foreach ($this->eventPriorityQueue as $item) {}
		$this->assertEquals(2, count($this->eventPriorityQueue));
	}

	public function testDefaultFIFOBehavior()
	{
		$handler = 'foo';
		$this->eventPriorityQueue->insert($handler);
		$handler = 'bar';
		$this->eventPriorityQueue->insert($handler);
		$handler = 'me';
		$this->eventPriorityQueue->insert($handler);

		$counter = 0;
		foreach ($this->eventPriorityQueue as $item) {

			if (0 == $counter) {
				$this->assertEquals('foo', $item);
 			} else if (1 == $counter) {
				$this->assertEquals('bar', $item);
			} else {
				$this->assertEquals('me', $item);
			}

			$counter++;
		}
	}
}
